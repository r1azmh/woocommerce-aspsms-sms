=== WooCommerce ASPSMS SMS Notifications ===
Contributors: r1azmh
Tags: woocommerce, sms, aspsms, notifications, order status
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

A bridge plugin that sends SMS notifications to customers via ASPSMS whenever a WooCommerce order status changes.

== Description ==

This plugin listens for WooCommerce order status changes and sends a customisable SMS to the customer's billing phone number via the ASPSMS API.

**Features:**

* Per-status SMS toggle (enable/disable individually)
* Fully customisable message templates per status
* Dynamic placeholders: {order_id}, {customer_name}, {order_total}, {billing_phone}, {site_name}
* Private order note logging — every send attempt is recorded as Sent ✅ or Failed ❌
* Settings page under WooCommerce → SMS Notifications

== Installation ==

1. Upload the `woocommerce-aspsms-sms` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **WooCommerce → SMS Notifications** and enter your ASPSMS credentials.
4. Enable the statuses you want and write a message template for each.
5. Save. The plugin will now send SMS messages automatically on every matching status change.

== Frequently Asked Questions ==

= What phone number format should customers use? =
International format is recommended (e.g. +358401234567). The plugin strips spaces, dashes, and parentheses automatically.

= Where do I find my ASPSMS UserKey and Password? =
Log in to your ASPSMS account at https://www.aspsms.com and find the API credentials in your account settings.

= Where are SMS results logged? =
Each send attempt is recorded as a private note on the WooCommerce order. Go to **WooCommerce → Orders**, open an order, and check the **Order notes** panel.

== Changelog ==

= 1.0.0 =
* Initial release.
