<?php
/**
 * Custom notice rendering
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="laposteproexp-notice <?php echo esc_attr( 'laposteproexp-' . $notice->status ); ?>">
	<?php echo esc_html( $notice->message ); ?>

	<a class="button-secondary laposteproexp-hide-notice" data-action="laposteproexp_hide_notice" rel="<?php echo esc_attr( $notice->key ); ?>">
		<?php esc_html_e( 'Hide this notice', 'la-poste-pro-expeditions-woocommerce' ); ?>
	</a>
</div>
