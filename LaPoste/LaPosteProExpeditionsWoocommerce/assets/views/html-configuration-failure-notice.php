<?php
/**
 * Configuration failure notice rendering
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="laposteproexp-notice laposteproexp-warning">
	<?php
	/* translators: 1) Plugin name */
	echo sprintf( esc_html__( 'There was a problem initializing the %s plugin. You should contact our support team.', 'la-poste-pro-expeditions-woocommerce' ), 'La Poste Pro Expéditions WooCommerce' );
	?>
</div>
