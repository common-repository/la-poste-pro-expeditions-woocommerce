<?php
/**
 * Plugin Name: La Poste Pro Expéditions WooCommerce
 * Description: Manage your ecommerce shipments. No subscription, no hidden fees.
 * Author: La Poste
 * Author URI: https://app.expeditions-pro.laposte.fr/
 * Text Domain: la-poste-pro-expeditions-woocommerce
 * Domain Path: /LaPoste/LaPosteProExpeditionsWoocommerce/translation
 * Version: 1.0.0
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * WC requires at least: 2.6.14
 * WC tested up to: 9.2.3
 *
 * @package LaPoste\LaPosteProExpeditionsWoocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

require_once trailingslashit( __DIR__ ) . 'LaPoste/LaPosteProExpeditionsWoocommerce/autoloader.php';

use LaPoste\LaPosteProExpeditionsWoocommerce\Plugin;

$plugin_instance = Plugin::initInstance( __FILE__ );

add_action( 'before_woocommerce_init', array( $plugin_instance, 'plugins_before_woocommerce_init_action' ) );

add_action( 'plugins_loaded', array( $plugin_instance, 'plugins_loaded_action' ) );

add_action( 'wpmu_new_blog', array( $plugin_instance, 'wpmu_new_blog_action' ), 10, 6 );

add_action( 'wpmu_drop_tables', array( $plugin_instance, 'wpmu_drop_tables_action' ) );

register_activation_hook( __FILE__, 'LaPoste\LaPosteProExpeditionsWoocommerce\Plugin::activation_hook' );

register_uninstall_hook( __FILE__, 'LaPoste\LaPosteProExpeditionsWoocommerce\Plugin::uninstall_hook' );

