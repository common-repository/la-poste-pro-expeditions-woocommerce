<?php
/**
 * Contains code for the component class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Init
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Init;

/**
 * Component class.
 *
 * Inits components.
 */
class Component {

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
		add_action( 'admin_enqueue_scripts', array( $this, 'component_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'component_styles' ) );
	}

	/**
	 * Enqueue component scripts
	 *
	 * @void
	 */
	public function component_scripts() {
		wp_enqueue_script( 'laposteproexp_components', $this->plugin_url . 'LaPoste/LaPosteProExpeditionsWoocommerce/assets/js/component.min.js', array(), $this->plugin_version, false );
	}

	/**
	 * Enqueue component styles
	 *
	 * @void
	 */
	public function component_styles() {
		wp_enqueue_style( 'laposteproexp_components', $this->plugin_url . 'LaPoste/LaPosteProExpeditionsWoocommerce/assets/css/component.css', array(), $this->plugin_version );
	}
}
