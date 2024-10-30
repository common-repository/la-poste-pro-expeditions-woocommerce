<?php
/**
 * Settings page rendering
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap" id="laposteproexp-settings">
	<h1>La Poste Pro Expéditions WooCommerce</h1>

	<form method="post" action="options.php">
		<?php
			settings_fields( $plugin_settings_id );
			do_settings_sections( $plugin_settings_id );
			submit_button();
			do_settings_sections( $plugin_tutorial_id );
		?>
	</form>
</div>
