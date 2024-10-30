<?php
/**
 * Contains code for environment util class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Util
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Util;

use LaPoste\LaPosteProExpeditionsWoocommerce\Plugin;

/**
 * Environment util class.
 *
 * Helper to check environment.
 */
class Environment_Util {

	/**
	 * Get warning about PHP version, WC version.
	 *
	 * @param Plugin $plugin plugin object.
	 * @return string $message
	 */
	public static function check_errors( $plugin ) {

		if ( version_compare( PHP_VERSION, $plugin['min-php-version'], '<' ) ) {
			/* translators: 1) int version 2) int version */
			$message = __( '%1$s - The minimum PHP version required for this plugin is %2$s. You are running %3$s.', 'la-poste-pro-expeditions-woocommerce' );
			return sprintf( $message, 'La Poste Pro Expéditions WooCommerce', $plugin['min-php-version'], PHP_VERSION );
		}

		if ( ! defined( 'WC_VERSION' ) ) {
			/* translators: 1) Plugin name */
			return sprintf( __( '%s requires WooCommerce to be activated to work.', 'la-poste-pro-expeditions-woocommerce' ), 'La Poste Pro Expéditions WooCommerce' );
		}

		if ( version_compare( WC_VERSION, $plugin['min-wc-version'], '<' ) ) {
			/* translators: 1) Plugin name 2) minimum woocommerce version 3) current woocommerce version */
			$message = __( '%1$s - The minimum WooCommerce version required for this plugin is %2$s. You are running %3$s.', 'la-poste-pro-expeditions-woocommerce' );

			return sprintf( $message, 'La Poste Pro Expéditions WooCommerce', $plugin['min-wc-version'], WC_VERSION );
		}
		return false;
	}
}
