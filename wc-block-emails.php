<?php
/**
 * Plugin Name: Block Emails for WooCommerce
 * Plugin URI: <https://wordpress.org/plugins/wc-block-emails>
 * Description: A WooCommerce plugin to block specific email addresses during checkout.
 * Version: 1.0.2
 * Author: Con
 * Author URI: <https://conschneider.de>
 * License: GPL-2.0+
 * License URI: <http://www.gnu.org/licenses/gpl-2.0.txt>
 * Text Domain: wc-block-emails
 * Domain Path: /languages
 * Requires at least: 5.2
 * Requires PHP: 7.4
 * WC requires at least: 3.0
 * WC tested up to: 9.2
 *
 * @package   Block Emails for WooCommerce
 */

/** Declare compatibility with WooCommerce HPOS */
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Woob_Woo_Block_Emails' ) ) {

	/**
	 * Class Woob_Woo_Block_Emails
	 *
	 * @package Block Emails for WooCommerce
	 */
	class Woob_Woo_Block_Emails {

		/**
		 * Woob_Woo_Block_Emails constructor.
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'woob_init' ) );
		}

		/**
		 * Initialize the plugin
		 */
		public function woob_init() {
			if ( class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_menu', array( $this, 'woob_add_admin_menu' ) );
				add_action( 'admin_init', array( $this, 'woob_settings_init' ) );
				add_action( 'woocommerce_after_checkout_validation', array( $this, 'woob_block_emails' ), 10, 2 );
				add_action( 'admin_init', array( $this, 'woob_check_reset_counter' ), 1 );
			}
		}

		/**
		 * Add admin menu
		 */
		public function woob_add_admin_menu() {
			add_submenu_page(
				'woocommerce',
				'Block Emails',
				'Block Emails',
				'manage_options',
				'woob_woo_block_emails',
				array( $this, 'woob_settings_page' )
			);
		}

		/**
		 * Initialize settings
		 */
		public function woob_settings_init() {
			register_setting( 'woob_woo_block_emails', 'woob_woo_block_emails_settings' );

			add_settings_section(
				'woob_woo_block_emails_section',
				esc_html__( 'Block Emails Settings', 'wc-block-emails' ),
				array( $this, 'woob_settings_section_callback' ),
				'woob_woo_block_emails'
			);

			add_settings_field(
				'woob_blocked_emails',
				esc_html__( 'Blocked Emails', 'wc-block-emails' ),
				array( $this, 'woob_blocked_emails_render' ),
				'woob_woo_block_emails',
				'woob_woo_block_emails_section'
			);

			add_settings_field(
				'woob_error_message',
				esc_html__( 'Error Message', 'wc-block-emails' ),
				array( $this, 'woob_error_message_render' ),
				'woob_woo_block_emails',
				'woob_woo_block_emails_section'
			);
			add_settings_field(
				'woob_blocked_emails_counter',
				esc_html__( 'Blocked Emails Counter', 'wc-block-emails' ),
				array( $this, 'woob_blocked_emails_counter_render' ),
				'woob_woo_block_emails',
				'woob_woo_block_emails_section'
			);
		}

		/**
		 * Render blocked emails field
		 */
		public function woob_blocked_emails_render() {
			$options        = get_option( 'woob_woo_block_emails_settings' );
			$blocked_emails = isset( $options['blocked_emails'] ) ? $options['blocked_emails'] : '';
			?>
		<textarea name='woob_woo_block_emails_settings[blocked_emails]' rows='5' cols='50'><?php echo esc_textarea( $blocked_emails ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Enter blocked email addresses or domain or TLD, one per line.', 'wc-block-emails' ); ?></p>
		<p class="description"><?php esc_html_e( 'Format: spam@spammy.org - @spammy.org - .org', 'wc-block-emails' ); ?></p>
			<?php
		}

		/**
		 * Render error message field
		 */
		public function woob_error_message_render() {
			$options       = get_option( 'woob_woo_block_emails_settings' );
			$error_message = isset( $options['error_message'] ) ? $options['error_message'] : '';
			?>
		<input type='text' name='woob_woo_block_emails_settings[error_message]' value='<?php echo esc_attr( $error_message ); ?>' size='50'>
		<p class="description"><?php esc_html_e( 'Enter the error message displayed when a blocked email is used.', 'wc-block-emails' ); ?></p>
			<?php
		}

		/**
		 * Render blocked emails counter
		 */
		public function woob_blocked_emails_counter_render() {
			$counter = get_option( 'woob_woo_block_emails_counter', 0 );
			echo '<p>Blocked Emails: ' . esc_html( $counter ) . '</p>';
		}

		/**
		 * Callback for settings section
		 */
		public function woob_settings_section_callback() {
			esc_html_e( 'Configure the email addresses to block and the error message to display.', 'wc-block-emails' );
		}

		/**
		 * Check and reset counter
		 */
		public function woob_check_reset_counter() {
			if ( isset( $_POST['reset_counter'] ) && check_admin_referer( 'woob_woo_block_emails_nonce_action', 'woob_woo_block_emails_nonce_field' ) ) {
				update_option( 'woob_woo_block_emails_counter', 0 );
				// Optionally, add a redirect to avoid form resubmission on page refresh.
				wp_safe_redirect( add_query_arg( 'page', 'woob_woo_block_emails', admin_url( 'admin.php' ) ) );
				exit;
			}
		}

		/**
		 * Render settings page
		 */
		public function woob_settings_page() {
			?>
		<form action='options.php' method='post'>
			<?php wp_nonce_field( 'woob_woo_block_emails_nonce_action', 'woob_woo_block_emails_nonce_field' ); ?>
			<h1><?php esc_html_e( 'Block Emails for WooCommerce', 'wc-block-emails' ); ?></h1>
			<?php
				settings_fields( 'woob_woo_block_emails' );
				do_settings_sections( 'woob_woo_block_emails' );
				submit_button();
			?>
			<input type="submit" name="reset_counter" value="<?php esc_attr_e( 'Reset Counter', 'wc-block-emails' ); ?>" />
		</form>
			<?php
		}

		/**
		 * Block emails
		 *
		 * @param array $data    Form data.
		 * @param array $errors  Errors.
		 */
		public function woob_block_emails( $data, $errors ) {
			$options        = get_option( 'woob_woo_block_emails_settings' );
			$blocked_items  = preg_split( '/\\r\\n|\\r|\\n/', $options['blocked_emails'] );
			$blocked_items  = array_map( 'trim', $blocked_items );
			$email          = $data['billing_email'];
			$email_domain   = substr(strrchr($email, "@"), 1);
			$email_tld      = substr(strrchr($email_domain, "."), 0);

			$is_blocked = false;
			foreach($blocked_items as $item) {
				if ($item[0] === '@') {
					// This is a domain
					if (substr($item, 1) === $email_domain) {
						$is_blocked = true;
						break;
					}
				} elseif ($item[0] === '.') {
					// This is a TLD
					if ($item === $email_tld) {
						$is_blocked = true;
						break;
					}
				} else {
					// This is an email
					if ($item === $email) {
						$is_blocked = true;
						break;
					}
				}
			}

			if ($is_blocked) {
				$errors->add( 'validation', $options['error_message'] );
				$counter = get_option( 'woob_woo_block_emails_counter', 0 );
				update_option( 'woob_woo_block_emails_counter', ++$counter );
			}
		}
	}

	new Woob_Woo_Block_Emails();
}
