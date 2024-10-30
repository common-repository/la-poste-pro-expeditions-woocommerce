<?php
/**
 * Contains code for shipping method util class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Util
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Util;

/**
 * Shipping method util class.
 *
 * Helper to manage consistency between woocommerce versions shipping methods.
 */
class Shipping_Method_Util {

	/**
	 * Get unique instance identifier from shipping method (must be same as rate id).
	 *
	 * @param \WC_Shipping_Method $method woocommerce shipping method.
	 *
	 * @return string $key shipping method identifier
	 */
	public static function get_unique_identifier( $method ) {
		return $method->id . ':' . $method->instance_id;
	}

	/**
	 * Get existing shipping classes.
	 *
	 * @return array $shipping_classes shipping classes
	 */
	public static function get_shipping_class_list() {
		if ( method_exists( WC()->shipping, 'get_shipping_classes' ) ) {
			$shipping_class_list = WC()->shipping->get_shipping_classes();
		} else {
			$shipping_class_list = WC()->shipping->shipping_classes;
		}
		$shipping_classes = array();
		foreach ( $shipping_class_list as $class ) {
			$shipping_classes[ $class->slug ] = $class->name;
		}
		$shipping_classes['none'] = __( 'No shipping class', 'la-poste-pro-expeditions-woocommerce' );
		return $shipping_classes;
	}
}
