<?php
/**
 * Admin subscription edit page parcelpoint rendering
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$network       = $parcelpoint->network;
$networks_name = isset( $parcelpoint_networks->$network )
	? implode( ', ', $parcelpoint_networks->$network ) : null;

?>
<div class="laposteproexp-subscription-parcelpoint">
	<p>
		<?php
		echo wp_kses(
			sprintf(
			/* translators: %1$s : parcelpoint code, %2$s : parcelpoint network name */
				__( 'Your client chose the pickup point %1$s from %2$s.', 'la-poste-pro-expeditions-woocommerce' ),
				'<b>' . $parcelpoint->code . '</b>',
				$networks_name
			),
			array( 'b' => array() )
		);
		?>
	</p>
	<?php
		require 'html-admin-subscription-parcelpoint.php';
	?>
</div>
