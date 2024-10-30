<?php
/**
 * Contains code for the configuration util class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Util
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Util;

use LaPoste\LaPosteProExpeditionsWoocommerce\Notice\Notice_Controller;
use LaPoste\LaPosteProExpeditionsWoocommerce\Shipping_Method\Parcel_Point\Controller;

/**
 * Configuration util class.
 *
 * Helper to manage configuration.
 */
class Configuration_Util {

	/**
	 * List of all configuration keys used by the module
	 *
	 * @var mixed
	 */
	private static $all_configs = array(
		'LAPOSTEPROEXP_ACCESS_KEY',
		'LAPOSTEPROEXP_SECRET_KEY',
		'LAPOSTEPROEXP_MAP_BOOTSTRAP_URL',
		'LAPOSTEPROEXP_MAP_TOKEN_URL',
		'LAPOSTEPROEXP_MAP_LOGO_IMAGE_URL',
		'LAPOSTEPROEXP_MAP_LOGO_HREF_URL',
		'LAPOSTEPROEXP_PP_NETWORKS',
		'LAPOSTEPROEXP_TRACKING_EVENTS',
		'LAPOSTEPROEXP_NOTICES',
		'LAPOSTEPROEXP_PAIRING_UPDATE',
		'LAPOSTEPROEXP_ORDER_SHIPPED',
		'LAPOSTEPROEXP_ORDER_DELIVERED',
		'LAPOSTEPROEXP_HELP_CENTER_URL',
		'LAPOSTEPROEXP_TUTO_URL',
		'LAPOSTEPROEXP_SHIPPING_RATES_URL',
		'LAPOSTEPROEXP_HELP_SHIPPING_METHOD_URL',
		'LAPOSTEPROEXP_SHIPPING_RULES_URL',
		'LAPOSTEPROEXP_LOGGING',
	);

	/**
	 * Build onboarding link.
	 *
	 * @return string onboarding link
	 */
	public static function get_onboarding_link() {
		$url    = 'https://app.expeditions-pro.laposte.fr/onboarding';
		$params = array(
			'acceptLanguage' => get_locale(),
			'email'          => get_option( 'admin_email' ),
			'shopUrl'        => get_option( 'siteurl' ),
			'shopType'       => 'woocommerce',
		);

		$query = wp_parse_url( $url, PHP_URL_QUERY );

		return $url . ( $query ? '&' : '?' ) . http_build_query( $params );
	}

	/**
	 * Get help center url
	 *
	 * @return string onboarding link
	 */
	public static function get_help_center_link() {
		$url = get_option( 'LAPOSTEPROEXP_HELP_CENTER_URL' );
		return false !== $url ? $url : null;
	}

	/**
	 * Get map logo href url.
	 *
	 * @return string map logo href url
	 */
	public static function get_map_logo_href_url() {
		$url = get_option( 'LAPOSTEPROEXP_MAP_LOGO_HREF_URL' );
		return false !== $url ? $url : null;
	}

	/**
	 * Get map logo image url.
	 *
	 * @return string map logo image url
	 */
	public static function get_map_logo_image_url() {
		$url = get_option( 'LAPOSTEPROEXP_MAP_LOGO_IMAGE_URL' );
		return false !== $url ? $url : null;
	}

	/**
	 * Get parcel point networks.
	 *
	 * @return array parcel point networks
	 */
	public static function get_parcel_point_networks() {
		$networks = get_option( 'LAPOSTEPROEXP_PP_NETWORKS' );
		return false !== $networks ? $networks : null;
	}

	/**
	 * Get logging states.
	 *
	 * @return bool logging state.
	 */
	public static function get_logging() {
		return get_option( 'LAPOSTEPROEXP_LOGGING', '0' );
	}

	/**
	 * Get shipped order state.
	 *
	 * @return string|null shipped state.
	 */
	public static function get_order_shipped() {
		return get_option( 'LAPOSTEPROEXP_ORDER_SHIPPED', null );
	}

	/**
	 * Get delivered order state.
	 *
	 * @return array network list
	 */
	public static function get_network_list() {
		return get_option( 'LAPOSTEPROEXP_PP_NETWORKS' );
	}

	/**
	 * Get networks list.
	 *
	 * @return string|null delivered state.
	 */
	public static function get_order_delivered() {
		return get_option( 'LAPOSTEPROEXP_ORDER_DELIVERED', null );
	}

	/**
	 * Get all configurations.
	 *
	 * @return array
	 */
	public static function get_all_configs() {
		$configs = array();

		foreach ( self::$all_configs as $config ) {
			$configs[ $config ] = get_option( $config );
		}

		return $configs;
	}

	/**
	 * Has configuration.
	 *
	 * @return boolean
	 */
	public static function has_configuration() {
		return false !== get_option( 'LAPOSTEPROEXP_MAP_BOOTSTRAP_URL' ) && false !== get_option( 'LAPOSTEPROEXP_MAP_TOKEN_URL' ) && false !== self::get_network_list();
	}

	/**
	 * Delete configuration.
	 *
	 * @void
	 */
	public static function delete_configuration() {
		global $wpdb;

		foreach ( self::$all_configs as $config ) {
			delete_option( $config );
		}
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
				'LAPOSTEPROEXP_NOTICE_%'
			)
		);
	}

	/**
	 * Parse configuration.
	 *
	 * @param object $body body.
	 * @return boolean
	 */
	public static function parse_configuration( $body ) {
		return self::parse_parcel_point_networks( $body )
			&& self::parse_map_configuration( $body )
			&& self::parse_links_configuration( $body );
	}

	/**
	 * Is first activation.
	 *
	 * @return boolean
	 */
	public static function is_first_activation() {
		return false === get_option( 'LAPOSTEPROEXP_NOTICES' );
	}

	/**
	 * Parse parcel point networks response.
	 *
	 * @param object $body body.
	 * @return boolean
	 */
	private static function parse_parcel_point_networks( $body ) {
		if ( is_object( $body ) && property_exists( $body, 'parcelPointNetworks' ) ) {

			$stored_networks = self::get_network_list();
			if ( is_array( $stored_networks ) ) {
				$removed_networks = $stored_networks;
				foreach ( $body->parcelPointNetworks as $new_network => $new_network_carriers ) {
					foreach ( $stored_networks as $old_network => $old_network_carriers ) {
						if ( $new_network === $old_network ) {
							unset( $removed_networks[ $old_network ] );
						}
					}
				}

				if ( count( $removed_networks ) > 0 ) {
					Notice_Controller::add_notice(
						Notice_Controller::$custom,
						array(
							'status'  => 'warning',
							'message' => __( 'There\'s been a change in the parcel point network list, we\'ve adapted your shipping method configuration. Please check that everything is in order.', 'la-poste-pro-expeditions-woocommerce' ),
						)
					);
				}

				$added_networks = $body->parcelPointNetworks;
				foreach ( $body->parcelPointNetworks as $new_network => $new_network_carriers ) {
					foreach ( $stored_networks as $old_network => $old_network_carriers ) {
						if ( $new_network === $old_network ) {
							unset( $added_networks[ $old_network ] );
						}
					}
				}
				if ( count( $added_networks ) > 0 ) {
					Notice_Controller::add_notice(
						Notice_Controller::$custom,
						array(
							'status'  => 'info',
							'message' => __( 'There\'s been a change in the parcel point network list, you can add the extra parcel point network(s) to your shipping method configuration.', 'la-poste-pro-expeditions-woocommerce' ),
						)
					);
				}
			}
			update_option( 'LAPOSTEPROEXP_PP_NETWORKS', $body->parcelPointNetworks );
			return true;
		}
		return false;
	}

	/**
	 * Parse map configuration.
	 *
	 * @param object $body body.
	 * @return boolean
	 */
	private static function parse_map_configuration( $body ) {
		if ( is_object( $body ) && property_exists( $body, 'mapsBootstrapUrl' ) && property_exists( $body, 'mapsTokenUrl' )
			&& property_exists( $body, 'mapsLogoImageUrl' ) && property_exists( $body, 'mapsLogoHrefUrl' ) ) {
			update_option( 'LAPOSTEPROEXP_MAP_BOOTSTRAP_URL', $body->mapsBootstrapUrl );
			update_option( 'LAPOSTEPROEXP_MAP_TOKEN_URL', $body->mapsTokenUrl );
			update_option( 'LAPOSTEPROEXP_MAP_LOGO_IMAGE_URL', $body->mapsLogoImageUrl );
			update_option( 'LAPOSTEPROEXP_MAP_LOGO_HREF_URL', $body->mapsLogoHrefUrl );
			return true;
		}
		return false;
	}

	/**
	 * Parse help center configuration.
	 *
	 * @param object $body body.
	 * @return boolean
	 */
	private static function parse_links_configuration( $body ) {
		if ( is_object( $body ) && property_exists( $body, 'helpCenterUrl' ) ) {
			update_option( 'LAPOSTEPROEXP_HELP_CENTER_URL', $body->helpCenterUrl );
		}
		return true;
	}
}
