<?php
/**
 * Listens to WooCommerce order status changes and triggers SMS sending.
 */
defined( 'ABSPATH' ) || exit;

class WC_ASPSMS_Notifications {

    public static function init() {
        add_action( 'woocommerce_order_status_changed', [ __CLASS__, 'handle_status_change' ], 10, 4 );
    }

    /**
     * Fired every time an order's status changes.
     *
     * @param int      $order_id   WooCommerce order ID.
     * @param string   $old_status Previous status slug (without 'wc-').
     * @param string   $new_status New status slug (without 'wc-').
     * @param WC_Order $order      The order object.
     */
    public static function handle_status_change( $order_id, $old_status, $new_status, $order ) {

        // --- Step B: Check admin settings ---
        $settings = WC_ASPSMS_Settings::get_settings();

        $userkey    = $settings['userkey'] ?? '';
        $password   = $settings['password'] ?? '';
        $originator = $settings['originator'] ?? '';

        // Bail if credentials are not configured.
        if ( empty( $userkey ) || empty( $password ) ) {
            return;
        }

        // Is there a message template for the new status, and is it enabled?
        $status_config = $settings['statuses'][ $new_status ] ?? [];
        if ( empty( $status_config['enabled'] ) || empty( $status_config['message'] ) ) {
            return;
        }

        // Does the customer have a billing phone?
        $phone = $order->get_billing_phone();
        if ( empty( $phone ) ) {
            $order->add_order_note(
                'ASPSMS SMS skipped: customer has no billing phone number.',
                false, // not a customer-facing note
                true   // is system note
            );
            return;
        }

        // --- Step C: Prepare the message ---
        $message = self::replace_placeholders( $status_config['message'], $order );

        // --- Step D: Send via ASPSMS ---
        $result = WC_ASPSMS_API::send_sms( $userkey, $password, $originator, $phone, $message );

        // --- Step E: Record the result as a private order note ---
        $note = $result['success']
            ? '✅ ' . $result['message']
            : '❌ ASPSMS SMS FAILED — ' . $result['message'];

        $order->add_order_note( $note, 0, false );
    }

    /**
     * Replace placeholder tags in a message template with real order data.
     *
     * Supported tags:
     *   {order_id}        — The order ID / number.
     *   {customer_name}   — Billing first + last name.
     *   {order_total}     — Formatted order total with currency symbol.
     *   {billing_phone}   — Customer billing phone.
     *   {site_name}       — WordPress site name.
     *
     * @param string   $template Raw message template.
     * @param WC_Order $order    The WooCommerce order.
     * @return string            Populated message.
     */
    private static function replace_placeholders( $template, $order ) {
        $replacements = [
            '{order_id}'       => $order->get_order_number(),
            '{customer_name}'  => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
            '{order_total}'    => html_entity_decode( wc_price( $order->get_total(), [ 'currency' => $order->get_currency() ] ), ENT_QUOTES, 'UTF-8' ),
            '{billing_phone}'  => $order->get_billing_phone(),
            '{site_name}'      => get_bloginfo( 'name' ),
        ];

        // Strip any remaining HTML from wc_price output so the SMS is plain text.
        $replacements['{order_total}'] = wp_strip_all_tags( $replacements['{order_total}'] );

        return str_replace(
            array_keys( $replacements ),
            array_values( $replacements ),
            $template
        );
    }
}
