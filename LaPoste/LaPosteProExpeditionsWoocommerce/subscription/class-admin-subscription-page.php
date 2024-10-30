<?php
/**
 * Contains code for the admin subscription page class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Subscription
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Subscription;

use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Order_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Subscription_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Configuration_Util;

/**
 * Admin_Subscription_Page class.
 *
 * Adds additional info to subscription order page.
 */
class Admin_Subscription_Page {

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
	 * Subscription parcel point.
	 *
	 * @var mixed
	 */
	private $parcelpoint;

	/**
	 * Construct function.
	 *
	 * @param array $plugin plugin array.
	 * @void
	 */
	public function __construct( $plugin ) {
		$this->plugin_url     = $plugin['url'];
		$this->plugin_version = $plugin['version'];
		$this->parcelpoint    = null;
	}

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run() {
		add_filter( 'add_meta_boxes_shop_subscription', array( $this, 'add_parcelpoint_to_admin_subscription_page' ), 10, 2 );
	}

	/**
	 * Add parcelpoint info to admin subscription page
	 *
	 * @void
	 */
	public function add_parcelpoint_to_admin_subscription_page() {
		$subscription      = Subscription_Util::admin_get_subscription();
		$this->parcelpoint = Subscription_Util::get_parcelpoint( $subscription );

		if ( null === $this->parcelpoint ) {
			return;
		}

		if ( function_exists( 'wc_get_order_types' ) ) {
			foreach ( wc_get_order_types( 'order-meta-boxes' ) as $type ) {
				/* translators: 1) plugin name */
				add_meta_box( 'lpfr-eco-subscription-parcelpoint', sprintf( __( '%s - Shipment pickup point', 'la-poste-pro-expeditions-woocommerce' ), 'La Poste Pro Expéditions' ), array( $this, 'subscription_edit_page_parcelpoint' ), $type, 'side', 'default' );
			}
		} else {
			/* translators: 1) plugin name */
			add_meta_box( 'lpfr-eco-subscription-parcelpoint', sprintf( __( '%s - Shipment pickup point', 'la-poste-pro-expeditions-woocommerce' ), 'La Poste Pro Expéditions' ), array( $this, 'subscription_edit_page_parcelpoint' ), 'shop_subscription', 'side', 'default' );
		}
	}

	/**
	 *
	 * Display the parcel point metabox content
	 *
	 * @Void
	 */
	public function subscription_edit_page_parcelpoint() {
		$parcelpoint          = $this->parcelpoint;
		$parcelpoint_networks = Configuration_Util::get_network_list();
		require_once realpath( plugin_dir_path( __DIR__ ) ) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'html-admin-subscription-edit-page-parcelpoint.php';
	}
}
