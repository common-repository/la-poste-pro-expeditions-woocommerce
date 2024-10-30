<?php
/**
 * Shipping method rate line rendering
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Assets\Views
 */

use LaPoste\LaPosteProExpeditionsWoocommerce\Shipping_Method\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$state    = isset( $pricing_item, $pricing_item['pricing'] ) ? $pricing_item['pricing'] : Controller::$rate;
$disabled = Controller::$deactivated === $state;

?>
<tr class="pricing-item<?php echo $disabled ? ' disabled' : ''; ?>">
	<td class="sort"></td>

	<td>
		<input type="text" <?php echo $disabled ? 'disabled' : ''; ?> value="<?php echo isset( $pricing_item, $pricing_item['price_from'] ) ? esc_attr( $pricing_item['price_from'] ) : null; ?>" name='pricing-items[<?php echo esc_attr( $i ); ?>]["price-from"]' class="price-from">
	</td>

	<td>
		<input type="text" <?php echo $disabled ? 'disabled' : ''; ?> value="<?php echo isset( $pricing_item, $pricing_item['price_to'] ) ? esc_attr( $pricing_item['price_to'] ) : null; ?>" name='pricing-items[<?php echo esc_attr( $i ); ?>]["price-to"]' class="price-to">
	</td>

	<td>
		<input type="text" <?php echo $disabled ? 'disabled' : ''; ?> value="<?php echo isset( $pricing_item, $pricing_item['weight_from'] ) ? esc_attr( $pricing_item['weight_from'] ) : null; ?>" name='pricing-items[<?php echo esc_attr( $i ); ?>]["weight-from"]' class="weight-from">
	</td>

	<td>
		<input type="text" <?php echo $disabled ? 'disabled' : ''; ?> value="<?php echo isset( $pricing_item, $pricing_item['weight_to'] ) ? esc_attr( $pricing_item['weight_to'] ) : null; ?>" name='pricing-items[<?php echo esc_attr( $i ); ?>]["weight-to"]' class="weight-to">
	</td>

	<?php if ( count( $shipping_classes ) > 1 ) { ?>
	<td class="select">
		<?php
		$custom_attributes = array(
			'multiple'     => 'multiple',
			'autocomplete' => 'off',
		);
		if ( $disabled ) {
			$custom_attributes['disabled'] = '';
		}

		$selected               = isset( $pricing_item, $pricing_item['shipping_class'] ) ? $pricing_item['shipping_class'] : false;
		$shipping_classes_field = array(
			'name'              => 'pricing-items[' . esc_attr( $i ) . ']["shipping-class"][]',
			'id'                => 'pricing-items[' . esc_attr( $i ) . ']["shipping-class"][]',
			'label'             => '',
			'custom_attributes' => $custom_attributes,
			'options'           => $shipping_classes,
			'value'             => $selected,
			'vbvalue'           => $selected,
			'class'             => 'laposteproexp-tom-select shipping-class',
		);
		woocommerce_wp_select( $shipping_classes_field )
		?>
	</td>
	<?php } ?>

	<td class="select">
		<?php
		$custom_attributes = array(
			'multiple'     => 'multiple',
			'autocomplete' => 'off',
		);
		if ( $disabled ) {
			$custom_attributes['disabled'] = '';
		}
		$selected                    = isset( $pricing_item, $pricing_item['parcel_point_network'] ) ? $pricing_item['parcel_point_network'] : null;
		$parcel_point_networks_field = array(
			'name'              => 'pricing-items[' . esc_attr( $i ) . ']["parcel-point-network"][]',
			'id'                => 'pricing-items[' . esc_attr( $i ) . ']["parcel-point-network"][]',
			'label'             => '',
			'custom_attributes' => $custom_attributes,
			'options'           => $parcel_point_networks,
			'class'             => 'laposteproexp-tom-select parcel-point-network',
			'value'             => $selected,
			'cbvalue'           => $selected,
		);
		woocommerce_wp_select( $parcel_point_networks_field )
		?>
	</td>

	<td class="flat-rate">
		<input <?php echo $disabled ? 'disabled' : ''; ?>
		type="text"
		id="flat-rate-<?php echo esc_attr( $i ); ?>"
		value="<?php echo isset( $pricing_item, $pricing_item['flat_rate'] ) ? esc_attr( $pricing_item['flat_rate'] ) : null; ?>"
		name='pricing-items[<?php echo esc_attr( $i ); ?>]["flat-rate"]'
		class="flat-rate">
	</td>

	<td class="state">
		<input type="checkbox"
			data-checked="<?php echo esc_attr( Controller::$rate ); ?>"
			data-unchecked="<?php echo esc_attr( Controller::$deactivated ); ?>"
			id="state-<?php echo esc_attr( $i ); ?>"
			class="state laposteproexp-change-state"
			name='pricing-items[<?php echo esc_attr( $i ); ?>]["state"]'
			value="1"
			<?php echo checked( 1, ! $disabled, false ); ?>
		/>
	</td>
	<td class="remove">
		<a <?php echo $disabled ? 'disabled' : ''; ?> class="laposteproexp-remove-line dashicons-before dashicons-trash">
		</a>
	</td>
</tr>
