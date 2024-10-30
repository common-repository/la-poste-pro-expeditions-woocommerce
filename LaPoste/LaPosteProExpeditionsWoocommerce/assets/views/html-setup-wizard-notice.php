<?php
/**
 * Setup wizard notice rendering
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="laposteproexp-notice laposteproexp-info">
	<a class="laposteproexp-close-link laposteproexp-hide-notice" data-action="laposteproexp_hide_notice" rel="setup-wizard">x</a>
	<h2>
	<?php
		/* translators: 1) company name */
		echo sprintf( esc_html__( 'Welcome to %s!', 'la-poste-pro-expeditions-woocommerce' ), 'La Poste Pro Expéditions' );
	?>
	</h2>
	<p><?php esc_html_e( 'The adventure begins in a few clicks', 'la-poste-pro-expeditions-woocommerce' ); ?></p>
	<p>
		<a href="<?php echo esc_url( $notice->onboarding_link ); ?>" target="_blank" class="button-primary">
			<?php esc_html_e( 'Connect my shop', 'la-poste-pro-expeditions-woocommerce' ); ?>
		</a>
	</p>
</div>
