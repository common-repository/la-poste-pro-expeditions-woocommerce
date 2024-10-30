<?php
/**
 * Contains code for the settings page class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Settings
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Settings;

use LaPoste\LaPosteProExpeditionsWoocommerce\Notice\Notice_Controller;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Misc_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Shipping_Method_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Configuration_Util;

/**
 * Settings page class.
 *
 * Manages settings for the plugin.
 */
class Page {

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
	 * Plugin settings section id.
	 *
	 * @var string
	 */
	private $plugin_settings_id = 'la-poste-pro-expeditions-woocommerce';

	/**
	 * Plugin tutorial section id.
	 *
	 * @var string
	 */
	private $plugin_tutorial_id = 'la-poste-pro-expeditions-woocommerce-section-tutorial';

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
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add settings page.
	 *
	 * @void
	 */
	public function add_menu() {
		add_submenu_page( 'woocommerce', 'La Poste Pro Expéditions', 'La Poste Pro Expéditions', 'manage_woocommerce', 'la-poste-pro-expeditions-woocommerce-settings', array( $this, 'render_page' ) );
	}

	/**
	 * Return the list of options for order status select.
	 *
	 * @return array list of order status options
	 */
	private function get_order_status_options() {
		$status         = wc_get_order_statuses();
		$status_options = array(
			'none' => esc_html__( 'No status associated', 'la-poste-pro-expeditions-woocommerce' ),
		);

		foreach ( $status as $key => $translation ) {
			$status_options[ $key ] = esc_html( $translation );
		}

		return $status_options;
	}

	/**
	 * Register settings.
	 *
	 * @void
	 */
	public function register_settings() {
		$status_options   = $this->get_order_status_options();
		$slug             = 'la-poste-pro-expeditions-woocommerce';
		$tutorial_section = 'la-poste-pro-expeditions-woocommerce-section-tutorial';

		add_settings_section(
			$slug,
			'1. ' . esc_html__( 'Plugin settings', 'la-poste-pro-expeditions-woocommerce' ),
			'',
			$this->plugin_settings_id
		);

		register_setting(
			$slug,
			'LAPOSTEPROEXP_ORDER_SHIPPED',
			array(
				'type'              => 'string',
				'default'           => null,
				'sanitize_callback' => array( $this, 'sanitize_status' ),
			)
		);
		register_setting(
			$slug,
			'LAPOSTEPROEXP_ORDER_DELIVERED',
			array(
				'type'              => 'string',
				'default'           => null,
				'sanitize_callback' => array( $this, 'sanitize_status' ),
			)
		);
		register_setting(
			$slug,
			'LAPOSTEPROEXP_LOGGING',
			array(
				'type'    => 'boolean',
				'default' => false,
			)
		);

		add_settings_field(
			'LAPOSTEPROEXP_ORDER_SHIPPED',
			esc_html__( 'Shipped status', 'la-poste-pro-expeditions-woocommerce' ),
			'woocommerce_wp_select',
			$this->plugin_settings_id,
			$slug,
			array(
				'type'         => 'select',
				'option_group' => $this->plugin_settings_id,
				'id'           => 'LAPOSTEPROEXP_ORDER_SHIPPED',
				'name'         => 'LAPOSTEPROEXP_ORDER_SHIPPED',
				'label_for'    => 'LAPOSTEPROEXP_ORDER_SHIPPED',
				'value'        => Configuration_Util::get_order_shipped(),
				'cbvalue'      => Configuration_Util::get_order_shipped(),
				'label'        => '',
				'options'      => $status_options,
			)
		);

		add_settings_field(
			'LAPOSTEPROEXP_ORDER_DELIVERED',
			esc_html__( 'Delivered status', 'la-poste-pro-expeditions-woocommerce' ),
			'woocommerce_wp_select',
			$this->plugin_settings_id,
			$slug,
			array(
				'type'         => 'select',
				'option_group' => $this->plugin_settings_id,
				'id'           => 'LAPOSTEPROEXP_ORDER_DELIVERED',
				'name'         => 'LAPOSTEPROEXP_ORDER_DELIVERED',
				'label_for'    => 'LAPOSTEPROEXP_ORDER_DELIVERED',
				'value'        => Configuration_Util::get_order_delivered(),
				'cbvalue'      => Configuration_Util::get_order_delivered(),
				'label'        => '',
				'options'      => $status_options,
			)
		);

		add_settings_field(
			'LAPOSTEPROEXP_LOGGING',
			esc_html__( 'Enable logging', 'la-poste-pro-expeditions-woocommerce' ),
			'woocommerce_wp_checkbox',
			$this->plugin_settings_id,
			$slug,
			array(
				'type'        => 'checkbox',
				'name'        => 'LAPOSTEPROEXP_LOGGING',
				'id'          => 'LAPOSTEPROEXP_LOGGING',
				'label_for'   => 'LAPOSTEPROEXP_LOGGING',
				'value'       => Configuration_Util::get_logging(),
				'cbvalue'     => '1',
				'label'       => '',
				'description' => esc_html__( 'Should remain unchecked by default.', 'la-poste-pro-expeditions-woocommerce' ),
			)
		);

		$tuto_url = Configuration_Util::get_help_center_link();
		if ( null !== $tuto_url ) {
			add_settings_section(
				$slug,
				'2. ' . esc_html__( 'Shipping settings', 'la-poste-pro-expeditions-woocommerce' ),
				array( $this, 'output_shipping_settings_description' ),
				$this->plugin_tutorial_id
			);
		}

	}

	/**
	 * Print shipping settings description.
	 *
	 * @param string $tuto_url tutorial url.
	 * @void
	 */
	public function output_shipping_settings_description( $tuto_url ) {
		$tuto_url   = Configuration_Util::get_help_center_link();
		$link_label = esc_html__( 'Go to the tutorial', 'la-poste-pro-expeditions-woocommerce' );

		echo wp_kses(
			sprintf(
				// translators: 1) tutorian link 2) tutorial link label.
				__( 'Just one last step, it will only take a few minutes, let us guide you: <a target="_blank" href="%1$1s">%2$2s</a>', 'la-poste-pro-expeditions-woocommerce' ),
				$tuto_url,
				$link_label
			),
			array(
				'a' => array(
					'href'   => true,
					'target' => true,
				),
			)
		);
	}

	/**
	 * Render settings page.
	 *
	 * @void
	 */
	public function render_page() {
		$plugin_settings_id = $this->plugin_settings_id;
		$plugin_tutorial_id = $this->plugin_tutorial_id;
		include_once dirname( __DIR__ ) . '/assets/views/html-settings-page.php';
	}

	/**
	 * Sanitize status option.
	 *
	 * @param string $input status value.
	 *
	 * @return string
	 */
	public function sanitize_status( $input ) {
		return 'none' === $input ? null : $input;
	}
}
