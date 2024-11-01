=== Block Emails for WooCommerce Checkout ===
Contributors: conschneider
Donate link: https://conschneider.de/donate
Tags: woocommerce, block, emails
Requires at least: 5.2
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A WooCommerce plugin to block specific email addresses during checkout.

== Description ==

The Block Emails for WooCommerce plugin allows you to block specific email addresses during the WooCommerce checkout process. It's simple and easy to use. Just enter the email addresses you want to block and the plugin will prevent those emails from being used during checkout. You can also customize the error message that is displayed when a blocked email is used.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wc-block-emails` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the WooCommerce->Settings->Block Emails screen to configure the plugin.

== Frequently Asked Questions ==

= What does this plugin do? =

This plugin allows you to block specific email addresses during the WooCommerce checkout process.

= How do I use the plugin? =

After installing and activating the plugin, go to WooCommerce->Block Emails and enter the email addresses you want to block.

== Screenshots ==

1. The settings page where you can enter the email addresses to block.

== Changelog ==

= 1.0.2 =
* Declare HPOS compatibility.

= 1.0.1 =
* You can now enter TLDs (top-level domains) to block all email addresses from that domain. Example: .org will block all email addresses ending in .org.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.2 =
* HPOS compatibility.

= 1.0.1 =
* TLDs are now supported.

= 1.0.0 =
* Initial release.