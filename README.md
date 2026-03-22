# WooCommerce ASPSMS SMS Notifications

A lightweight WordPress plugin that sends SMS notifications to customers via the [ASPSMS](https://www.aspsms.com) API whenever a WooCommerce order status changes.

> 🤖 This plugin was developed with the assistance of [Claude](https://claude.ai) by Anthropic.

---

## Features

- 📱 **Per-status SMS control** — enable or disable SMS individually for each WooCommerce order status
- ✏️ **Customisable message templates** — write a unique message for every status
- 🔖 **Dynamic placeholders** — personalise messages with live order data (see [Placeholders](#placeholders))
- 📋 **Order note logging** — every SMS attempt is recorded as a private note on the order (✅ sent / ❌ failed)
- 🔌 **Settings page** — neatly tucked under **WooCommerce → SMS Notifications**
- 🧪 **Built-in connection tester** — verify credentials and send a test SMS directly from the settings page

---

## Requirements

| Requirement | Minimum version |
|---|---|
| WordPress | 5.8 |
| WooCommerce | 3.0 |
| PHP | 7.4 |

---

## Installation

1. Download or clone this repository.
2. Upload the `woocommerce-aspsms-sms` folder to `/wp-content/plugins/` on your server.
3. Go to **WordPress Admin → Plugins** and activate **WooCommerce ASPSMS SMS Notifications**.
4. Navigate to **WooCommerce → SMS Notifications** and enter your ASPSMS credentials.
5. Enable the order statuses you want and write a message template for each.
6. Click **Save Settings** — the plugin will now send SMS messages automatically on every matching status change.

---

## Configuration

### API Credentials

| Field | Description |
|---|---|
| **UserKey** | Your ASPSMS UserKey (called "UserName" in the ASPSMS API) |
| **Password** | Your ASPSMS API password |
| **Originator** | Sender name shown on the customer's phone (up to 11 alphanumeric characters, or an unlocked phone number) |

> 💡 Find your credentials by logging in to [aspsms.com](https://www.aspsms.com) and navigating to your account settings.

### Testing the Connection

The settings page includes two test tools — no need to place a real order to verify the setup:

- **Check Credentials & Credits** — verifies your UserKey and Password and shows your remaining SMS credit balance. No SMS is sent.
- **Send Test SMS** — sends a real test message to any phone number you enter.

---

## Placeholders

Use these tags inside any message template — they are replaced automatically with live order data when the SMS is sent:

| Placeholder | Replaced with |
|---|---|
| `{order_id}` | The WooCommerce order number |
| `{customer_name}` | Customer's billing first + last name |
| `{order_total}` | Formatted order total with currency symbol |
| `{billing_phone}` | Customer's billing phone number |
| `{site_name}` | Your WordPress site name |

**Example template:**

```
Hi {customer_name}, your order #{order_id} for {order_total} has been shipped! Thank you for shopping at {site_name}.
```

---

## Phone Number Format

International format is recommended (e.g. `+358XXXXXXX`). The plugin automatically strips spaces, dashes, and parentheses before sending to the API.

---

## Order Note Logging

Every SMS send attempt is recorded as a private order note. To view logs:

1. Go to **WooCommerce → Orders**
2. Open any order
3. Check the **Order notes** panel on the right

Successful sends appear as `✅ SMS Notification sent successfully via ASPSMS.`  
Failed sends appear as `❌ ASPSMS SMS FAILED — ...` with the error detail from the API.

---

## File Structure

```
woocommerce-aspsms-sms/
├── woocommerce-aspsms-sms.php          # Plugin bootstrap & entry point
├── readme.txt                          # WordPress.org readme
├── README.md                           # This file
└── includes/
    ├── class-wc-aspsms-api.php         # ASPSMS JSON REST API client
    ├── class-wc-aspsms-notifications.php  # Order status hook & SMS trigger
    └── class-wc-aspsms-settings.php    # Admin settings page & AJAX handlers
```

---

## FAQ

**What phone number format should customers use?**  
International format is recommended (e.g. `+358XXXXXXX`). Spaces, dashes, and parentheses are stripped automatically.

**Where do I find my ASPSMS UserKey and Password?**  
Log in to your ASPSMS account at [aspsms.com](https://www.aspsms.com) and find the API credentials in your account settings.

**What happens if a customer has no billing phone number?**  
The SMS is skipped and a private note is added to the order explaining why.

**Can I use a custom sender name?**  
Yes — set the **Originator** field to any name up to 11 alphanumeric characters. Alternatively, use an ASPSMS-unlocked phone number as the sender.

**Is the password stored securely?**  
Credentials are stored in the WordPress `wp_options` table using the standard `sanitize_text_field` sanitisation. For enhanced security, consider restricting database access at the server level.

---

## Changelog

### 1.0.0
- Initial release.

---

## License

This plugin is licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).

---

## Credits

- Developed by [Riaz Mahmud](https://github.com/r1azmh)
- Developed with the assistance of [Claude](https://claude.ai) by [Anthropic](https://www.anthropic.com)
- Powered by the [ASPSMS JSON REST API](https://json.aspsms.com)
