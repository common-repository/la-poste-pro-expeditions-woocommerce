<?php
/**
 * Environment warning notice rendering
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="laposteproexp-notice laposteproexp-warning">
	<?php echo esc_html( $notice->message ); ?>
</div>
