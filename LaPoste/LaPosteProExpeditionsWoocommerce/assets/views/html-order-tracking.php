<?php
/**
 * Order tracking rendering
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Assets\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="laposteproexp-tracking">
	<?php if ( property_exists( $tracking, 'shipmentsTracking' ) && ! empty( $tracking->shipmentsTracking ) ) : ?>
		<?php foreach ( $tracking->shipmentsTracking as $shipment ) : ?>
			<?php $parcel_count = count( $shipment->parcelsTracking ); ?>
			<?php if ( 1 === $parcel_count || 0 === $parcel_count ) : ?>
				<?php /* translators: 1) int number of shipments */ ?>
				<p><?php echo esc_html( sprintf( __( 'Your shipment has %s package.', 'la-poste-pro-expeditions-woocommerce' ), $parcel_count ) ); ?></p>
			<?php else : ?>
				<?php /* translators: 1) int number of shipments */ ?>
				<p><?php echo esc_html( sprintf( __( 'Your shipment has %s packages.', 'la-poste-pro-expeditions-woocommerce' ), $parcel_count ) ); ?></p>
			<?php endif; ?>
			<?php foreach ( $shipment->parcelsTracking as $parcel ) : ?>
				<?php if ( null !== $parcel->trackingUrl ) : ?>
					<?php /* translators: 1) shipment reference */ ?>
					<p><?php echo sprintf( esc_html__( 'Package reference %s', 'la-poste-pro-expeditions-woocommerce' ), '<a href="' . esc_url( $parcel->trackingUrl ) . '" target="_blank">' . esc_html( $parcel->reference ) . '</a>' ); ?></p>
				<?php else : ?>
					<?php /* translators: 1) shipment reference */ ?>
					<p><?php echo esc_html( sprintf( __( 'Package reference %s', 'la-poste-pro-expeditions-woocommerce' ), $parcel->reference ) ); ?></p>
				<?php endif; ?>
				<?php if ( is_array( $parcel->trackingEvents ) && count( $parcel->trackingEvents ) > 0 ) : ?>
				<?php else : ?>
					<p><?php esc_html_e( 'No tracking event for this package yet.', 'la-poste-pro-expeditions-woocommerce' ); ?></p>
				<?php endif; ?>
				<br/>
			<?php endforeach; ?>
		<?php endforeach; ?>

	<?php endif; ?>
</div>
