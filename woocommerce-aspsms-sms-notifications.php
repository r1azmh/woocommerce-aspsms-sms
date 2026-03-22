<?php
/**
 * Plugin Name: WooCommerce ASPSMS SMS Notifications
 * Plugin URI:  https://github.com/r1azmh/woocommerce-aspsms-sms-notifications
 * Description: Sends SMS notifications to customers via ASPSMS when a WooCommerce order status changes.
 * Version:     1.0.0
 * Author:      Riaz Mahmud
 * License:     GPL-2.0+
 * Text Domain: wc-aspsms-sms
 */

defined( 'ABSPATH' ) || exit;

define( 'WC_ASPSMS_VERSION', '1.0.0' );
define( 'WC_ASPSMS_FILE', __FILE__ );
define( 'WC_ASPSMS_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Check WooCommerce is active before doing anything.
 */
function wc_aspsms_check_dependencies() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>WooCommerce ASPSMS SMS Notifications</strong> requires WooCommerce to be installed and active.</p></div>';
        } );
        return false;
    }
    return true;
}

/**
 * Bootstrap the plugin.
 */
function wc_aspsms_init() {
    if ( ! wc_aspsms_check_dependencies() ) {
        return;
    }

    require_once WC_ASPSMS_DIR . 'includes/class-wc-aspsms-settings.php';
    require_once WC_ASPSMS_DIR . 'includes/class-wc-aspsms-api.php';
    require_once WC_ASPSMS_DIR . 'includes/class-wc-aspsms-notifications.php';

    WC_ASPSMS_Settings::init();
    WC_ASPSMS_Notifications::init();
}
add_action( 'plugins_loaded', 'wc_aspsms_init' );

/**
 * Add a Settings link on the Plugins page.
 */
function wc_aspsms_plugin_action_links( $links ) {
    $settings_link = '<a href="' . admin_url( 'admin.php?page=wc-aspsms-settings' ) . '">Settings</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_aspsms_plugin_action_links' );
