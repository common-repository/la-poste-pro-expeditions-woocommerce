<?php
/**
 * Contains code for the order controller class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Order
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Order;

use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Shipping_Api_Util;

/**
 * Controller class.
 *
 * Handles additional info hooks and functions.
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
	}

	/**
	 * Get order tracking.
	 *
	 * @param string $order_id \WC_Order id.
	 * @return object tracking
	 */
	public function get_order_tracking( $order_id ) {
		return Shipping_Api_Util::get_order( $order_id );
	}

	/**
	 * Enqueue tracking styles
	 *
	 * @void
	 */
	public function tracking_styles() {
		wp_enqueue_style( 'laposteproexp_tracking', $this->plugin_url . 'LaPoste/LaPosteProExpeditionsWoocommerce/assets/css/tracking.css', array(), $this->plugin_version );
	}
}
