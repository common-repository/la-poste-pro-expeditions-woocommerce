<?php
/**
 * Contains code for api util class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Util
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Util;

use LaPoste\LaPosteProExpeditionsWoocommerce\Plugin;

/**
 * Shipping api util class.
 *
 * Helper to manage api request to an external shipping service.
 */
class Shipping_Api_Util {

	/**
	 * Execute a request and Log the http response if this is an error.
	 *
	 * @param string $path request path.
	 * @param array  $args request parameters.
	 * @return mixed request response.
	 */
	private static function request( $path, $args ) {
		$response = wp_remote_request( $path, $args );

		if ( ! self::is_success_response( $response ) ) {
			Logger_Util::warning( 'Request to shipping api failed: ' . "\n" . '[Request]  ' . $path . ':' . wp_json_encode( $args ) . "\n" . '[Response] ' . wp_json_encode( $response ) );
		}

		return $response;
	}

	/**
	 * Return the default headers for an shipping api call
	 */
	private static function get_request_headers() {
		return array(
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			'Authorization' => base64_encode( Auth_Util::get_access_key() . ':' . Auth_Util::get_secret_key() ),
			'Content-type'  => 'application/json; charset=UTF-8',
		);
	}

	/**
	 * Extract a body or null from an http response
	 *
	 * @param array|WP_Error $response request response.
	 * @return mixed|null extracted body as a json object.
	 */
	private static function get_body( $response ) {
		$result = null;

		if ( is_array( $response ) && $response['body'] && ! is_wp_error( $response ) ) {
			$result = json_decode( $response['body'] );
		}

		return $result;
	}

	/**
	 * Check is the response is a success http response (2XX)
	 *
	 * @param array|WP_Error $response request response.
	 * @return boolean the response is a success http response
	 */
	private static function is_success_response( $response ) {
		$result = null;

		if ( is_array( $response ) && is_array( $response['response'] ) && $response['response']['code'] && ! is_wp_error( $response ) ) {
			$result = $response['response']['code'];
		}

		return null !== $result && $result >= 200 && $result < 300;
	}

	/**
	 * Request a shipping order from it's woocommerce id
	 *
	 * @param integer $reference shipping order reference.
	 * @return mixed array|WP_Error
	 */
	public static function get_order( $reference ) {
		$args = array(
			'method'  => 'GET',
			'headers' => self::get_request_headers(),
		);

		$response = self::request( 'https://api.expeditions-pro.laposte.fr/v2/shop-order/' . $reference, $args );

		return self::get_body( $response );
	}

	/**
	 * Update a pairing status.
	 *
	 * @param string $path pairing update response unique endpoint.
	 * @param string $approve pairing update response ('0' or '1').
	 * @return boolean request is success
	 */
	public static function update_pairing( $path, $approve ) {
		$args = array(
			'method'  => 'PATCH',
			'headers' => self::get_request_headers(),
			'body'    => wp_json_encode( array( 'approve' => $approve ) ),
		);

		$response = self::request( $path, $args );

		return self::is_success_response( $response );
	}

	/**
	 * Retrieve a list of parcel points.
	 *
	 * @param array $address  parcel point address.
	 * @param array $networks wanted parcel point networks.
	 * @return mixed array|WP_Error list of parcel points per network
	 */
	public static function get_parcel_points( $address, $networks ) {
		$parcel_points = null;

		$body = array(
			'networks' => $networks,
			'address'  => array(
				'zipCode' => $address['zipCode'],
				'country' => $address['country'],
			),
		);
		if ( isset( $address['street'] ) ) {
			$body['address']['street'] = $address['street'];
		}

		if ( isset( $address['city'] ) ) {
			$body['address']['city'] = $address['city'];
		}

		$transient_key = 'laposteproexp_get_parcel_points_' . wp_json_encode( $body );
		$response      = get_transient( $transient_key );
		if ( false === $response ) {
			$args = array(
				'method'  => 'POST',
				'headers' => self::get_request_headers(),
				'body'    => wp_json_encode( $body ),
			);

			$response = self::request( 'https://api.expeditions-pro.laposte.fr/v2/parcel-point', $args );

			if ( self::is_success_response( $response ) ) {
				$parcel_points = self::get_body( $response );
				set_transient( $transient_key, $parcel_points, 3600 );
			}
		} else {
			$parcel_points = $response;
		}

		return $parcel_points;
	}

	/**
	 * Retrieve a new parcel points map token.
	 *
	 * @param string $path map token unique endpoint.
	 * @return mixed json response
	 */
	public static function get_map_token( $path ) {
		$map_token = null;
		$args      = array(
			'method'  => 'POST',
			'headers' => self::get_request_headers(),
		);

		$response = self::request( $path, $args );

		$response = self::get_body( $response );

		if ( null !== $response && property_exists( $response, 'accessToken' ) ) {
			$map_token = $response->accessToken;
		}

		return $map_token;
	}

}
