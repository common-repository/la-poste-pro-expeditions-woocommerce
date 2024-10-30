<?php
/**
 * Pairing success notice rendering
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="laposteproexp-notice laposteproexp-success">
	<a class="laposteproexp-close-link laposteproexp-hide-notice" data-action="laposteproexp_hide_notice" rel="pairing">x</a>
	<h2><?php esc_html_e( 'Congratulations, your shop is connected !', 'la-poste-pro-expeditions-woocommerce' ); ?></h2>
	<p><?php esc_html_e( 'Finalize your settings to start shipping', 'la-poste-pro-expeditions-woocommerce' ); ?></p>
	<p>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=la-poste-pro-expeditions-woocommerce-settings' ) ); ?>" class="button-primary" rel="pairing">
			<?php esc_html_e( 'Finalize the settings', 'la-poste-pro-expeditions-woocommerce' ); ?>
		</a>
	</p>
</div>
