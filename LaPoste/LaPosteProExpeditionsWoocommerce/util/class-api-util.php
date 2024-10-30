<?php
/**
 * Contains code for api util class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Util
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Util;

use LaPoste\LaPosteProExpeditionsWoocommerce\Plugin;

/**
 * Api util class.
 *
 * Helper to manage API responses.
 */
class Api_Util {

	/**
	 * API request validation.
	 *
	 * @param integer $code http code.
	 * @param mixed   $body to send along response.
	 * @void
	 */
	public static function send_api_response( $code, $body = null ) {
		header( 'X-Version: 1.0.0' );
		http_response_code( $code );
		if ( null !== $body ) {
			echo wp_json_encode( Auth_Util::encrypt_body( $body ) );
		}
		die();
	}
}
