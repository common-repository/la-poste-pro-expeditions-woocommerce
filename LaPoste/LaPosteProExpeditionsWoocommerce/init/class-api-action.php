<?php
/**
 * Contains code for the environment check class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Init
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Init;

use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Order_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Shipping_Api_Util;

/**
 * Api_Action class.
 *
 * Init parcelpoints and tracking hooks.
 */
class Api_Action {

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
	 * @param Plugin $plugin plugin array.
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
		add_action( 'la_poste_pro_expeditions_woocommerce_get_parcelpoint', array( $this, 'get_order_parcelpoint' ) );
		add_action( 'la_poste_pro_expeditions_woocommerce_print_parcelpoint', array( $this, 'print_order_parcelpoint' ) );
		add_action( 'la_poste_pro_expeditions_woocommerce_get_tracking', array( $this, 'get_tracking' ) );
		add_action( 'la_poste_pro_expeditions_woocommerce_print_tracking_number', array( $this, 'print_tracking_number' ) );
	}

	/**
	 * Order parcelpoint.
	 *
	 * @param array $order plugin array.
	 */
	public function get_order_parcelpoint( $order ) {
		return Order_Util::get_parcelpoint( $order );
	}

	/**
	 * Order parcelpoint with HTML.
	 *
	 * @param array $order plugin array.
	 */
	public function print_order_parcelpoint( $order ) {
		$parcelpoint = Order_Util::get_parcelpoint( $order );
		if ( $parcelpoint ) {
			include_once dirname( __DIR__ ) . '/assets/views/html-order-parcelpoint.php';
		}
	}

	/**
	 * Order tracking information.
	 *
	 * @param int|string|mixed $order order or order id.
	 */
	public function get_tracking( $order ) {
		$order_id = is_string( $order ) || is_integer( $order ) ? $order : Order_Util::get_id( $order );
		return Shipping_Api_Util::get_order( $order_id );
	}


	/**
	 * Order tracking number.
	 *
	 * @param int|string|mixed $order order or order id.
	 */
	public function print_tracking_number( $order ) {
		$tracking = $this->get_tracking( $order );
		if ( null !== $tracking && property_exists( $tracking, 'shipmentsTracking' ) && ! empty( $tracking->shipmentsTracking ) ) {
			include_once dirname( __DIR__ ) . '/assets/views/html-order-tracking.php';

		}
	}

}
