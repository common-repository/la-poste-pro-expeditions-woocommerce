<?php
/**
 * Pairing update notice rendering
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="laposteproexp-notice laposteproexp-warning">
	<?php
		/* translators: 1) company name */
		echo sprintf( esc_html__( 'Security alert: someone is trying to pair your site with %s. Was it you?', 'la-poste-pro-expeditions-woocommerce' ), 'La Poste' );
	?>
	<button class="button-secondary laposteproexp-pairing-update-validate" laposteproexp-pairing-update-validate="1" href="#"><?php esc_html_e( 'yes', 'la-poste-pro-expeditions-woocommerce' ); ?></button>
	<button class="button-secondary laposteproexp-pairing-update-validate" laposteproexp-pairing-update-validate="0" href="#"><?php esc_html_e( 'no', 'la-poste-pro-expeditions-woocommerce' ); ?></button>
</div>
