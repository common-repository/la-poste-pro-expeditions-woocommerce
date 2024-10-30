<?php
/**
 * Front order tracking rendering
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="laposteproexp-order-parcelpoint">
	<h2><?php esc_html_e( 'Chosen pickup point', 'la-poste-pro-expeditions-woocommerce' ); ?></h2>

	<?php
		require 'html-admin-order-parcelpoint.php';
	?>
</div>
