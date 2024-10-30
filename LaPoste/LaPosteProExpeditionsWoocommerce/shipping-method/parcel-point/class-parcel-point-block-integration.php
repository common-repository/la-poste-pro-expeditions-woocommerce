<?php
/**
 * Contains code for the parcel point block integration class
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Shipping_Method\Parcel_Point
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Shipping_Method\Parcel_Point;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Auth_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Shipping_Api_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Configuration_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Frontend_Util;

/**
 * Controller class.
 *
 * Handles setter and getter for parcel points.
 */
class Parcel_Point_Block_Integration implements IntegrationInterface {
	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'la-poste-pro-expeditions-woocommerce-parcel-point';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {

		$assets_path = plugins_url( 'la-poste-pro-expeditions-woocommerce/LaPoste/LaPosteProExpeditionsWoocommerce/assets', 'la-poste-pro-expeditions-woocommerce.php' );

		wp_enqueue_script( 'laposteproexp_polyfills', $assets_path . '/js/polyfills.min.js', array(), '1.0.0', false );
		wp_enqueue_script( 'laposteproexp_mapbox_gl', $assets_path . '/js/mapbox-gl.js', array( 'laposteproexp_polyfills' ), '1.0.0', false );
		wp_enqueue_script( 'laposteproexp_shipping', $assets_path . '/js/parcel-point.min.js', array( 'laposteproexp_mapbox_gl', 'laposteproexp_polyfills', 'jquery-core', 'wp-hooks' ), '1.0.0', false );
		wp_enqueue_style( 'laposteproexp_mapbox_gl', $assets_path . '/css/mapbox-gl.min.css', array(), '1.0.0' );
		wp_enqueue_style( 'laposteproexp_parcel_point', $assets_path . '/css/parcel-point.css', array(), '1.0.0' );
		wp_set_script_translations( 'laposteproexp_translation', 'la-poste-pro-expeditions-woocommerce' );
		// frontend data injection for legacy scripts.
		Frontend_Util::inject_inline_data( 'laposteproexp_shipping', 'laposteproexpData', $this->get_script_data() );
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array();
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array();
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		return Frontend_Util::get_frontend_data();
	}

	/**
	 * Files are reloaded when changed.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		return filemtime( $file );
	}
}
