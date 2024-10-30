<?php
/**
 * Contains code for the shipping method class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Shipping_Method
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Shipping_Method;

use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Cart_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Misc_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Shipping_Method_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Shipping_Rate_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Configuration_Util;

/**
 * Shipping_Method class.
 *
 * Add a plugin shipping method to WooCommerce.
 */
class Shipping_Method extends \WC_Shipping_Method {

	/**
	 * Field name used to pass the pricem items form nonce
	 *
	 * @var string
	 */
	private $post_action_field_name = 'shipping-method-submit';

	/**
	 * Nonce action name used for pricing items form
	 *
	 * @var string
	 */
	private $post_action;

	/**
	 * Constructor for your shipping class
	 *
	 * @param string $instance_id shipping method instance id.
	 *
	 * @return void
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'la_poste_pro_expeditions_woocommerce';
		$this->instance_id        = absint( $instance_id );
		$this->post_action        = 'lpfr-eco_' . $this->instance_id . '_pricing_items';
		$this->method_title       = 'La Poste Pro Expéditions';
		$this->method_description = __( 'Lets you define weight/price ranges of your shipping costs for each of your delivery methods and add a parcel point map.', 'la-poste-pro-expeditions-woocommerce' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
		);
		$this->init();

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Init your settings
	 *
	 * @return void
	 */
	public function init() {
		$this->instance_form_fields = $this->init_form_fields();
		$this->title                = $this->get_option( 'title' );
		$this->tax_status           = 'taxable';
	}

	/**
	 * Init fom fields
	 *
	 * @return array
	 */
	public function init_form_fields() {
		return array(
			'title' => array(
				'title'       => __( 'Method title', 'la-poste-pro-expeditions-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'la-poste-pro-expeditions-woocommerce' ),
				/* translators: 1) platform name */
				'default'     => sprintf( __( 'Flat rate %s', 'la-poste-pro-expeditions-woocommerce' ), 'La Poste Pro Expéditions' ),
				'desc_tip'    => true,
			),
			'rates' => array(
				'type' => 'rates_table',
			),
		);
	}

	/**
	 * Generate multilingual text type field html.
	 *
	 * @param string $key option key.
	 * @param array  $data option data.
	 *
	 * @return string
	 */
	public function generate_rates_table_html( $key, $data ) {
		$pricing_items          = \LaPoste\LaPosteProExpeditionsWoocommerce\Shipping_Method\Controller::get_pricing_items(
			Shipping_Method_Util::get_unique_identifier( $this )
		);
		$parcel_point_networks  = Misc_Util::get_network_options();
		$shipping_classes       = Shipping_Method_Util::get_shipping_class_list();
		$help_center_link       = Configuration_Util::get_help_center_link();
		$post_action            = $this->post_action;
		$post_action_field_name = $this->post_action_field_name;
		ob_start();
		include_once dirname( __DIR__ ) . '/assets/views/html-admin-shipping-method-rates-table.php';
		return ob_get_clean();
	}

	/**
	 * Update carrier options.
	 *
	 * @void
	 */
	public function process_admin_options() {
		if ( check_admin_referer( $this->post_action, $this->post_action_field_name ) ) {
			parent::process_admin_options();
			$pricing_items = isset( $_POST['pricing-items'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['pricing-items'] ) ) ) : null;
			Controller::save_pricing_items( $this, $pricing_items );
		}
	}

	/**
	 * Calculate_shipping function.
	 *
	 * @param array $package Package of items from cart.
	 *
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {

		$unique_identifier = Shipping_Method_Util::get_unique_identifier( $this );
		WC()->session->set( 'laposteproexp_parcel_point_networks_' . $unique_identifier, null );

		$pricing_items = Controller::get_pricing_items( $unique_identifier );

		$cart_weight           = Cart_Util::get_weight();
		$cart_price            = $package['contents_cost'];
		$cart_shipping_classes = array();
		foreach ( $package['contents'] as $cart_item ) {
			$shipping_class = $cart_item['data']->get_shipping_class();
			if ( '' === $shipping_class && ! in_array( 'none', $cart_shipping_classes, true ) ) {
				$cart_shipping_classes[] = 'none';
			} elseif ( '' !== $shipping_class && ! in_array( $shipping_class, $cart_shipping_classes, true ) ) {
				$cart_shipping_classes[] = $shipping_class;
			}
		}

		$final_rate = null;

		foreach ( $pricing_items as $pricing_item ) {
			if ( null !== $pricing_item['weight_from'] && $cart_weight < $pricing_item['weight_from'] ) {
				continue;
			}

			if ( null !== $pricing_item['weight_to'] && $cart_weight >= $pricing_item['weight_to'] ) {
				continue;
			}

			if ( null !== $pricing_item['price_from'] && $cart_price < $pricing_item['price_from'] ) {
				continue;
			}

			if ( null !== $pricing_item['price_to'] && $cart_price >= $pricing_item['price_to'] ) {
				continue;
			}

			if ( ! empty( array_diff( $cart_shipping_classes, $pricing_item['shipping_class'] ) ) ) {
				continue;
			}

			switch ( $pricing_item['pricing'] ) {
				case 'rate':
					$final_rate = $pricing_item['flat_rate'];
					break;

				case 'free':
					$final_rate = 0;
					break;

				case 'deactivate':
				default:
					break;
			}

			if ( WC()->session ) {
				WC()->session->set( 'laposteproexp_parcel_point_networks_' . $unique_identifier, $pricing_item['parcel_point_network'] );
			}

			break;
		}

		if ( null === $final_rate ) {
			return;
		}

		$rate = array(
			'id'      => $this->get_rate_id(),
			'label'   => $this->title,
			'cost'    => $final_rate,
			'package' => $package,
		);

		$this->add_rate( $rate );
	}
}
