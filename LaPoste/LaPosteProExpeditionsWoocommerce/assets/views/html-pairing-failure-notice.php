<?php
/**
 * Pairing failure notice rendering
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="laposteproexp-notice laposteproexp-warning">
	<?php
	/* translators: 1) company name 2) company name */
	echo sprintf( esc_html__( 'Pairing with %1$1s is not complete. Please check your WooCommerce connector in your %2$2s account for a more complete diagnostic.', 'la-poste-pro-expeditions-woocommerce' ), 'La Poste', 'La Poste' );
	?>
</div>
