<?php
/**
 * Contains code for the parcel point controller class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Shipping_Method\Parcel_Point
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Shipping_Method\Parcel_Point;

use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Configuration_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Auth_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Customer_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Misc_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Shipping_Rate_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Parcelpoint_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Shipping_Api_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Logger_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Frontend_Util;

/**
 * Controller class.
 *
 * Handles setter and getter for parcel points.
 */
class Controller {

	/**
	 * Plugin url.
	 *
	 * @var string
	 */
	private $plugin_url;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $plugin_version;

	/**
	 * Construct function.
	 *
	 * @param array $plugin plugin array.
	 * @void
	 */
	public function __construct( $plugin ) {
		$this->plugin_url     = $plugin['url'];
		$this->plugin_version = $plugin['version'];
	}

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run() {
		add_action( 'wp_ajax_laposteproexp_get_points', array( $this, 'get_points_callback' ) );
		add_action( 'wp_ajax_laposteproexp_set_point', array( $this, 'set_point_callback' ) );
		add_action( 'wp_ajax_laposteproexp_get_shipping_method_extra_label', array( $this, 'get_shipping_method_extra_label_callback' ) );
		add_action( 'wp_ajax_nopriv_laposteproexp_get_points', array( $this, 'get_points_callback' ) );
		add_action( 'wp_ajax_nopriv_laposteproexp_set_point', array( $this, 'set_point_callback' ) );
		add_action( 'wp_ajax_nopriv_laposteproexp_get_shipping_method_extra_label', array( $this, 'get_shipping_method_extra_label_callback' ) );

		if ( Frontend_Util::is_using_woocommerce_blocks() ) {
			add_action( 'woocommerce_blocks_cart_block_registration', array( $this, 'register_parcel_point_block' ) );
			add_action( 'woocommerce_blocks_checkout_block_registration', array( $this, 'register_parcel_point_block' ) );
		} else {
			add_action( 'woocommerce_after_shipping_calculator', array( $this, 'parcel_point_scripts' ) );
			add_action( 'woocommerce_after_checkout_form', array( $this, 'parcel_point_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'parcel_point_styles' ) );
		}
	}

	/**
	 * Check if the current page is on checkout or cart
	 *
	 * @boolean
	 */
	private function is_checkout_or_cart() {
		return ( ! function_exists( 'is_checkout' ) || is_checkout() ) || ( ! function_exists( 'is_cart' ) || is_cart() );
	}

	/**
	 * Register parcel point block class
	 *
	 * @param mixed $integration_registry woocommerce block integration registry.
	 * @void
	 */
	public function register_parcel_point_block( $integration_registry ) {
		$integration_registry->register( new Parcel_Point_Block_Integration() );
	}

	/**
	 * Enqueue pickup point script
	 *
	 * @void
	 */
	public function parcel_point_scripts() {
		if ( $this->is_checkout_or_cart() ) {
			$translations = array(
				'Unable to find carrier'   => __( 'Unable to find carrier', 'la-poste-pro-expeditions-woocommerce' ),
				'Opening hours'            => __( 'Opening hours', 'la-poste-pro-expeditions-woocommerce' ),
				'Choose this parcel point' => __( 'Choose this parcel point', 'la-poste-pro-expeditions-woocommerce' ),
				'Close map'                => __( 'Close map', 'la-poste-pro-expeditions-woocommerce' ),
				'Your parcel point:'       => __( 'Your parcel point:', 'la-poste-pro-expeditions-woocommerce' ),
				/* translators: %s: distance in km */
				'%skm away'                => __( '%skm away', 'la-poste-pro-expeditions-woocommerce' ),
			);
			wp_enqueue_script( 'laposteproexp_polyfills', $this->plugin_url . 'LaPoste/LaPosteProExpeditionsWoocommerce/assets/js/polyfills.min.js', array(), $this->plugin_version, false );
			wp_enqueue_script( 'laposteproexp_mapbox_gl', $this->plugin_url . 'LaPoste/LaPosteProExpeditionsWoocommerce/assets/js/mapbox-gl.js', array( 'laposteproexp_polyfills' ), $this->plugin_version, false );
			wp_enqueue_script( 'laposteproexp_shipping', $this->plugin_url . 'LaPoste/LaPosteProExpeditionsWoocommerce/assets/js/parcel-point.min.js', array( 'laposteproexp_mapbox_gl', 'laposteproexp_polyfills' ), $this->plugin_version, false );
			Frontend_Util::inject_inline_data( 'laposteproexp_shipping', 'laposteproexpData', Frontend_Util::get_frontend_data() );
			wp_localize_script( 'laposteproexp_shipping', 'translations', $translations );
			wp_set_script_translations( 'laposteproexp_translation', 'la-poste-pro-expeditions-woocommerce' );
		}
	}

	/**
	 * Enqueue parcel point styles
	 *
	 * @void
	 */
	public function parcel_point_styles() {
		if ( $this->is_checkout_or_cart() ) {
			wp_enqueue_style( 'laposteproexp_mapbox_gl', $this->plugin_url . 'LaPoste/LaPosteProExpeditionsWoocommerce/assets/css/mapbox-gl.min.css', array(), $this->plugin_version );
			wp_enqueue_style( 'laposteproexp_parcel_point', $this->plugin_url . 'LaPoste/LaPosteProExpeditionsWoocommerce/assets/css/parcel-point.css', array(), $this->plugin_version );
		}
	}

	/**
	 * Get parcel points callback.
	 *
	 * @void
	 */
	public function get_points_callback() {
		check_ajax_referer( Frontend_Util::$get_points_action, '_wpnonce' );
		header( 'Content-Type: application/json; charset=utf-8' );

		if ( ! isset( $_REQUEST['carrier'] ) || ! isset( $_REQUEST['packageKey'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed to get parcel points: unable to find carrier or package key', 'la-poste-pro-expeditions-woocommerce' ) ) );
		}

		$carrier       = sanitize_text_field( wp_unslash( $_REQUEST['carrier'] ) );
		$parcel_points = Frontend_Util::get_shipping_method_parcel_points( $carrier );

		if ( null === $parcel_points ) {
			wp_send_json_error( array( 'message' => __( 'Failed to get parcel points: no response from shipping service', 'la-poste-pro-expeditions-woocommerce' ) ) );
		}

		wp_send_json_success( $parcel_points );
	}

	/**
	 * Set parcel point callback.
	 *
	 * @void
	 */
	public function set_point_callback() {
		check_ajax_referer( Frontend_Util::$set_point_action, '_wpnonce' );
		header( 'Content-Type: application/json; charset=utf-8' );

		if ( ! isset( $_REQUEST['carrier'], $_REQUEST['network'], $_REQUEST['code'], $_REQUEST['name'], $_REQUEST['packageKey'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed to set parcel point: invalid request', 'la-poste-pro-expeditions-woocommerce' ) ) );
		}

		$carrier       = sanitize_text_field( wp_unslash( $_REQUEST['carrier'] ) );
		$package_key   = sanitize_text_field( wp_unslash( $_REQUEST['packageKey'] ) );
		$network       = sanitize_text_field( wp_unslash( $_REQUEST['network'] ) );
		$code          = sanitize_text_field( wp_unslash( $_REQUEST['code'] ) );
		$name          = sanitize_text_field( wp_unslash( $_REQUEST['name'] ) );
		$address       = isset( $_REQUEST['address'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['address'] ) ) : '';
		$zipcode       = isset( $_REQUEST['zipcode'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['zipcode'] ) ) : '';
		$city          = isset( $_REQUEST['city'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['city'] ) ) : '';
		$country       = isset( $_REQUEST['country'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['country'] ) ) : '';
		$opening_hours = isset( $_REQUEST['openingHours'] ) ? json_decode( sanitize_text_field( wp_unslash( $_REQUEST['openingHours'] ) ) ) : null;
		$distance      = isset( $_REQUEST['distance'] ) ? json_decode( sanitize_text_field( wp_unslash( $_REQUEST['distance'] ) ) ) : null;

		$parcel_point = ParcelPoint_Util::create_parcelpoint(
			$network,
			$code,
			$name,
			$address,
			$zipcode,
			$city,
			$country,
			$opening_hours,
			is_numeric( $distance ) ? floatval( $distance ) : null
		);

		if ( WC()->session ) {
			WC()->session->set( 'laposteproexp_chosen_parcel_point_' . $package_key . '_' . Shipping_Rate_Util::get_clean_id( $carrier ), $parcel_point );
		} else {
			wp_send_json_error( array( 'message' => 'could not set point. Woocommerce sessions are not enabled!' ) );
		}

		wp_send_json_success(
			array(
				'label' => Frontend_Util::get_parcel_point_label( $carrier, $package_key ),
			)
		);
	}

	/**
	 * Return extra label for a shipping method and package
	 */
	public function get_shipping_method_extra_label_callback() {
		check_ajax_referer( Frontend_Util::$get_shipping_method_extra_label_action, '_wpnonce' );
		header( 'Content-Type: application/json; charset=utf-8' );

		if ( ! isset( $_REQUEST['shippingMethod'], $_REQUEST['packageKey'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Failed to get shipping method extra label: invalid request', 'la-poste-pro-expeditions-woocommerce' ) ) );
		}
		$shipping_method = sanitize_text_field( wp_unslash( $_REQUEST['shippingMethod'] ) );
		$package_key     = sanitize_text_field( wp_unslash( $_REQUEST['packageKey'] ) );

		$label = Frontend_Util::get_parcel_point_label( $shipping_method, $package_key );

		wp_send_json_success( array( 'label' => $label ) );
	}
}
