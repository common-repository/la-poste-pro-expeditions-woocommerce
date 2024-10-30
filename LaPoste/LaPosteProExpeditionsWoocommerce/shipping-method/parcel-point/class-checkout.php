<?php
/**
 * Contains code for the checkout class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Shipping_Method\Parcel_Point
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Shipping_Method\Parcel_Point;

use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Order_Item_Shipping_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Order_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Subscription_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Logger_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Frontend_Util;

/**
 * Checkout class.
 *
 * Handles setter and getter for parcel points.
 */
class Checkout {

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run() {
		add_action( 'woocommerce_checkout_create_subscription_shipping_item', array( $this, 'subscription_add_shipping_item' ), 10, 4 );
		// legacy hook.
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'order_created' ), 10, 3 );
		// blocks hook.
		add_action( 'woocommerce_store_api_checkout_order_processed', array( $this, 'store_api_order_created' ), 10, 3 );
	}

	/**
	 * Add parcel point info to order.
	 *
	 * @param \WC_Order $order woocommerce order.
	 * @void
	 */
	public function store_api_order_created( $order ) {
		Logger_Util::info( 'Store api order created : ' . get_class( $order ) );
		$this->add_parcel_point_to_order( $order );
	}

	/**
	 * Add parcel point info to order.
	 *
	 * @param string    $order_id the order id.
	 * @param array     $posted_data posted data.
	 * @param \WC_Order $order woocommerce order.
	 * @void
	 */
	public function order_created( $order_id, $posted_data, $order ) {
		$shipping_method = null;
		if ( isset( $posted_data['shipping_method'][0] ) && ! empty( $posted_data['shipping_method'] ) ) {
			$shipping_method = $posted_data['shipping_method'][0];
		}

		$this->add_parcel_point_to_order( $order, $shipping_method );
	}

	/**
	 * Add parcel point info to subscription.
	 *
	 * @param \WC_Order $order created shipping item for the subscription.
	 * @param string    $shipping_method shipping method.
	 * @void
	 */
	private function add_parcel_point_to_order( $order, $shipping_method = null ) {
		// in some cases (such as use of the Divi theme), $posted_data['shipping_method'] is an empty string.
		if ( null === $shipping_method ) {
			$shipping_methods    = $order->get_shipping_methods();
			$order_item_shipping = ! empty( $shipping_methods ) ? array_shift( $shipping_methods ) : null;
			$shipping_method     = Order_Item_Shipping_Util::get_method_id( $order_item_shipping ) . ':' . Order_Item_Shipping_Util::get_instance_id( $order_item_shipping );
		}

		if ( null !== $shipping_method ) {
			$carrier = sanitize_text_field( wp_unslash( $shipping_method ) );
			if ( WC()->session ) {

				$point = Frontend_Util::get_chosen_point( $carrier, 0 );
				if ( null === $point ) {
					$point = Frontend_Util::get_closest_point( $carrier, 0 );
				}

				Frontend_Util::reset_chosen_points( 0 );

				if ( null !== $point ) {
					Logger_Util::info( 'Saving parcel point to order ' . Order_Util::get_id( $order ) . ' : ' . $point->name . ' (' . $point->code . ')' );
					Order_Util::add_meta_data( $order, 'laposteproexp_parcel_point', $point );
					Order_Util::save( $order );
				}
			}
		}
	}

	/**
	 * Add parcel point info to subscription.
	 *
	 * @param \WC_Order_Item_Shipping $item created shipping item for the subscription.
	 * @param string                  $package_key package key.
	 * @param Array                   $package package.
	 * @param \WC_Subscription        $subscription created subscription.
	 * @void
	 */
	public function subscription_add_shipping_item( $item, $package_key, $package, $subscription ) {
		$shipping_method = Order_Item_Shipping_Util::get_method_id( $item ) . ':' . Order_Item_Shipping_Util::get_instance_id( $item );

		if ( null !== $shipping_method ) {
			$carrier = sanitize_text_field( wp_unslash( $shipping_method ) );
			if ( WC()->session ) {

				$point = Frontend_Util::get_chosen_point( $carrier, $package_key );
				if ( null === $point ) {
					$point = Frontend_Util::get_closest_point( $carrier, $package_key );
				}

				Frontend_Util::reset_chosen_points( $package_key );

				if ( null !== $point ) {
					Logger_Util::info( 'Saving parcel point to subscription ' . Subscription_Util::get_id( $subscription ) . ' : ' . $point->name . ' (' . $point->code . ')' );
					Subscription_Util::update_metadata( $subscription, 'laposteproexp_parcel_point', $point );
					Subscription_Util::save( $subscription );
				}
			}
		}
	}
}
