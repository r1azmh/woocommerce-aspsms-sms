<?php
/**
 * Handles the admin settings page.
 */
defined( 'ABSPATH' ) || exit;

class WC_ASPSMS_Settings {

    const OPTION_KEY = 'wc_aspsms_settings';

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu_page' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'wp_ajax_wc_aspsms_test_sms', [ __CLASS__, 'ajax_test_sms' ] );
    }

    public static function add_menu_page() {
        add_submenu_page(
            'woocommerce',
            'ASPSMS SMS Notifications',
            'SMS Notifications',
            'manage_woocommerce',
            'wc-aspsms-settings',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings() {
        register_setting(
            'wc_aspsms_settings_group',
            self::OPTION_KEY,
            [ __CLASS__, 'sanitize_settings' ]
        );
    }

    /**
     * Return all WooCommerce order statuses (without the 'wc-' prefix).
     */
    public static function get_order_statuses() {
        $statuses = wc_get_order_statuses(); // keys are 'wc-pending', 'wc-processing', etc.
        $clean    = [];
        foreach ( $statuses as $key => $label ) {
            $clean[ str_replace( 'wc-', '', $key ) ] = $label;
        }
        return $clean;
    }

    /**
     * Retrieve saved settings (with defaults).
     */
    public static function get_settings() {
        return get_option( self::OPTION_KEY, [] );
    }

    /**
     * Sanitize before saving.
     */
    public static function sanitize_settings( $input ) {
        $clean = [];

        // API credentials
        $clean['userkey']    = sanitize_text_field( $input['userkey'] ?? '' );
        $clean['password']   = sanitize_text_field( $input['password'] ?? '' );
        $clean['originator'] = sanitize_text_field( $input['originator'] ?? '' );

        // Per-status settings
        $statuses = self::get_order_statuses();
        foreach ( $statuses as $slug => $label ) {
            $clean['statuses'][ $slug ]['enabled'] = ! empty( $input['statuses'][ $slug ]['enabled'] ) ? 1 : 0;
            $clean['statuses'][ $slug ]['message'] = sanitize_textarea_field( $input['statuses'][ $slug ]['message'] ?? '' );
        }

        return $clean;
    }

    /**
     * AJAX handler: send a real test SMS to a given number using saved credentials.
     */
    public static function ajax_test_sms() {
        check_ajax_referer( 'wc_aspsms_test_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied.' ] );
        }

        $action   = sanitize_text_field( $_POST['test_action'] ?? 'credits' );
        $phone    = sanitize_text_field( $_POST['phone'] ?? '' );
        $settings = self::get_settings();

        $userkey    = $settings['userkey'] ?? '';
        $password   = $settings['password'] ?? '';
        $originator = $settings['originator'] ?? '';

        if ( empty( $userkey ) || empty( $password ) ) {
            wp_send_json_error( [ 'message' => 'UserKey and Password are not configured yet. Save your credentials first.' ] );
        }

        if ( $action === 'credits' ) {
            // Just check credentials + credits — no SMS sent.
            $result = WC_ASPSMS_API::check_credits( $userkey, $password );
        } else {
            // Send a real test SMS.
            if ( empty( $phone ) ) {
                wp_send_json_error( [ 'message' => 'Please enter a phone number to send the test SMS to.' ] );
            }
            $result = WC_ASPSMS_API::send_sms(
                $userkey,
                $password,
                $originator,
                $phone,
                'Test SMS from your WooCommerce store. Your ASPSMS plugin is working!'
            );
        }

        if ( $result['success'] ) {
            wp_send_json_success( [ 'message' => '✅ ' . $result['message'] ] );
        } else {
            wp_send_json_error( [ 'message' => '❌ ' . $result['message'] ] );
        }
    }

    /**
     * Render the settings page HTML.
     */
    public static function render_page() {
        $settings = self::get_settings();
        $statuses = self::get_order_statuses();
        ?>
        <div class="wrap">
            <h1>WooCommerce ASPSMS SMS Notifications</h1>

            <form method="post" action="options.php">
                <?php settings_fields( 'wc_aspsms_settings_group' ); ?>

                <!-- API Credentials -->
                <h2>ASPSMS API Credentials</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="aspsms_userkey">UserKey</label></th>
                        <td>
                            <input type="text"
                                   id="aspsms_userkey"
                                   name="<?php echo self::OPTION_KEY; ?>[userkey]"
                                   value="<?php echo esc_attr( $settings['userkey'] ?? '' ); ?>"
                                   class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="aspsms_password">Password</label></th>
                        <td>
                            <input type="password"
                                   id="aspsms_password"
                                   name="<?php echo self::OPTION_KEY; ?>[password]"
                                   value="<?php echo esc_attr( $settings['password'] ?? '' ); ?>"
                                   class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="aspsms_originator">Originator (Sender Name)</label></th>
                        <td>
                            <input type="text"
                                   id="aspsms_originator"
                                   name="<?php echo self::OPTION_KEY; ?>[originator]"
                                   value="<?php echo esc_attr( $settings['originator'] ?? '' ); ?>"
                                   class="regular-text" />
                            <p class="description">The sender name or number shown on the customer's phone.</p>
                        </td>
                    </tr>
                </table>

                <hr />

                <!-- Test Connection -->
                <h2>Test API Connection</h2>
                <p>Use <strong>Check Credentials</strong> to verify your UserKey &amp; Password without spending a credit. Use <strong>Send Test SMS</strong> to send a real message to a phone number.</p>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Check Credentials</th>
                        <td>
                            <button type="button" id="aspsms_check_btn" class="button button-secondary">
                                Check Credentials &amp; Credits
                            </button>
                            <span id="aspsms_check_result" style="margin-left:12px;font-weight:600;"></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="aspsms_test_phone">Send Test SMS</label></th>
                        <td>
                            <input type="text"
                                   id="aspsms_test_phone"
                                   placeholder="+358401234567"
                                   class="regular-text" />
                            <button type="button" id="aspsms_test_btn" class="button button-secondary" style="margin-left:8px;">
                                Send Test SMS
                            </button>
                            <span id="aspsms_test_result" style="margin-left:12px;font-weight:600;"></span>
                        </td>
                    </tr>
                </table>

                <script>
                var aspsmsAjaxUrl  = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
                var aspsmsNonce    = '<?php echo wp_create_nonce( 'wc_aspsms_test_nonce' ); ?>';

                function aspsmsRequest( data, resultEl, btn ) {
                    resultEl.style.color   = '#666';
                    resultEl.textContent   = 'Please wait…';
                    btn.disabled           = true;

                    var fd = new FormData();
                    fd.append( 'action', 'wc_aspsms_test_sms' );
                    fd.append( 'nonce',  aspsmsNonce );
                    for ( var k in data ) { fd.append( k, data[k] ); }

                    fetch( aspsmsAjaxUrl, { method: 'POST', body: fd, credentials: 'same-origin' } )
                        .then( function(r){ return r.json(); } )
                        .then( function(json){
                            resultEl.style.color = json.success ? 'green' : '#cc0000';
                            resultEl.textContent = json.data.message;
                        } )
                        .catch( function(){
                            resultEl.style.color = '#cc0000';
                            resultEl.textContent = '❌ Request failed. Check your browser console.';
                        } )
                        .finally( function(){ btn.disabled = false; } );
                }

                document.getElementById('aspsms_check_btn').addEventListener('click', function(){
                    aspsmsRequest(
                        { test_action: 'credits' },
                        document.getElementById('aspsms_check_result'),
                        this
                    );
                });

                document.getElementById('aspsms_test_btn').addEventListener('click', function(){
                    aspsmsRequest(
                        { test_action: 'sms', phone: document.getElementById('aspsms_test_phone').value.trim() },
                        document.getElementById('aspsms_test_result'),
                        this
                    );
                });
                </script>

                <hr />

                <!-- Status Message Templates -->
                <h2>Order Status SMS Templates</h2>
                <p>
                    <strong>Available placeholders:</strong>
                    <code>{order_id}</code>, <code>{customer_name}</code>, <code>{order_total}</code>,
                    <code>{billing_phone}</code>, <code>{site_name}</code>
                </p>

                <table class="widefat striped" style="margin-top:10px;">
                    <thead>
                        <tr>
                            <th style="width:160px;">Order Status</th>
                            <th style="width:80px; text-align:center;">SMS Enabled</th>
                            <th>Message Template</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $statuses as $slug => $label ) :
                            $enabled = $settings['statuses'][ $slug ]['enabled'] ?? 0;
                            $message = $settings['statuses'][ $slug ]['message'] ?? '';
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html( $label ); ?></strong></td>
                            <td style="text-align:center;">
                                <input type="checkbox"
                                       name="<?php echo self::OPTION_KEY; ?>[statuses][<?php echo esc_attr( $slug ); ?>][enabled]"
                                       value="1"
                                       <?php checked( 1, $enabled ); ?> />
                            </td>
                            <td>
                                <textarea
                                    name="<?php echo self::OPTION_KEY; ?>[statuses][<?php echo esc_attr( $slug ); ?>][message]"
                                    rows="3"
                                    style="width:100%;"
                                    placeholder="Enter SMS message for '<?php echo esc_attr( $label ); ?>' status..."
                                ><?php echo esc_textarea( $message ); ?></textarea>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php submit_button( 'Save Settings' ); ?>
            </form>
        </div>
        <?php
    }
}
