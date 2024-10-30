<?php
/**
 * Contains code for auth util class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Util
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Util;

/**
 * Auth util class.
 *
 * Helper to manage API auth.
 */
class Auth_Util {

	/**
	 * API request validation.
	 *
	 * @param \WP_REST_Request $request request.
	 * @return boolean|void
	 */
	public static function authenticate( $request ) {
		$body = $request->get_body();

		if ( null === self::decrypt_body( $body ) ) {
			Logger_Util::warning( 'Incoming shipping api request denied (401)' );
			Api_Util::send_api_response( 401 );
		}

		return true;
	}

	/**
	 * API request validation with access key check.
	 *
	 * @param \WP_REST_Request $request request.
	 * @return boolean|void
	 */
	public static function authenticate_access_key( $request ) {
		$decrypted_body = self::decrypt_body( $request->get_body() );
		if ( null === $decrypted_body ) {
			Logger_Util::warning( 'Incoming shipping api request denied (401)' );
			Api_Util::send_api_response( 401 );
		}

		if ( is_object( $decrypted_body ) && property_exists( $decrypted_body, 'accessKey' ) && self::get_access_key() === $decrypted_body->accessKey ) {
			return true;
		}

		Logger_Util::warning( 'Incoming shipping api request denied (403)' );
		Api_Util::send_api_response( 403 );
	}

	/**
	 * Is plugin paired.
	 *
	 * @return boolean
	 */
	public static function is_plugin_paired() {
		return false !== self::get_access_key() && false !== self::get_secret_key();
	}

	/**
	 * Can use plugin.
	 *
	 * @return boolean
	 */
	public static function can_use_plugin() {
		return false !== self::is_plugin_paired() && false === get_option( 'LAPOSTEPROEXP_PAIRING_UPDATE' ) && true === Configuration_Util::has_configuration();
	}

	/**
	 * Pair plugin.
	 *
	 * @param string $access_key API access key.
	 * @param string $secret_key API secret key.
	 * @void
	 */
	public static function pair_plugin( $access_key, $secret_key ) {
		update_option( 'LAPOSTEPROEXP_ACCESS_KEY', $access_key );
		update_option( 'LAPOSTEPROEXP_SECRET_KEY', $secret_key );
	}

	/**
	 * Start pairing update (puts plugin on hold).
	 *
	 * @param string $callback_url callback url.
	 * @void
	 */
	public static function start_pairing_update( $callback_url ) {
		update_option( 'LAPOSTEPROEXP_PAIRING_UPDATE', $callback_url );
	}

	/**
	 * End pairing update (release plugin).
	 *
	 * @void
	 */
	public static function end_pairing_update() {
		delete_option( 'LAPOSTEPROEXP_PAIRING_UPDATE' );
	}

	/**
	 * Request body decryption.
	 *
	 * @param string $json_body encrypted body.
	 * @return mixed
	 */
	public static function decrypt_body( $json_body ) {

		$body = json_decode( $json_body );

		if ( null === $body || ! is_object( $body ) || ! property_exists( $body, 'encryptedKey' ) || ! property_exists( $body, 'encryptedData' ) ) {
			return null;
		}

		$key = self::decrypt_public_key( $body->encryptedKey );

		if ( null === $key ) {
			return null;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$data = self::encrypt_rc4( base64_decode( $body->encryptedData ), $key );

		return json_decode( $data );
	}

	/**
	 * Request body encryption.
	 *
	 * @param mixed $body body.
	 * @return string
	 */
	public static function encrypt_body( $body ) {
		$key = self::get_random_key();
		if ( null === $key ) {
			return null;
		}

		return array(
			'encryptedKey'  => Misc_Util::base64_or_null( self::encrypt_public_key( $key ) ),
			'encryptedData' => Misc_Util::base64_or_null( self::encrypt_rc4( ( is_array( $body ) ? wp_json_encode( $body ) : $body ), $key ) ),
		);
	}

	/**
	 * Get random encryption key.
	 *
	 * @return array bytes array
	 */
	public static function get_random_key() {
		$random_key = openssl_random_pseudo_bytes( 200 );
		if ( false === $random_key ) {
			return null;
		}
		return bin2hex( $random_key );
	}

	/**
	 * Encrypt with public key.
	 *
	 * @param string $str string to encrypt.
	 * @return array bytes array
	 */
	public static function encrypt_public_key( $str ) {
		$public_key = file_get_contents( realpath( plugin_dir_path( __DIR__ ) ) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'publickey' );
		$encrypted  = '';
		if ( openssl_public_encrypt( $str, $encrypted, $public_key ) ) {
			return $encrypted;
		}
		return null;
	}

	/**
	 * Decrypt with public key.
	 *
	 * @param string $str to decrypt.
	 * @return mixed
	 */
	public static function decrypt_public_key( $str ) {
		$public_key = file_get_contents( realpath( plugin_dir_path( __DIR__ ) ) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'publickey' );
		$decrypted  = '';
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		if ( openssl_public_decrypt( base64_decode( $str ), $decrypted, $public_key ) ) {
			return json_decode( $decrypted );
		}
		return null;
	}

	/**
	 * RC4 symmetric cipher encryption/decryption
	 *
	 * @param string $str string to be encrypted/decrypted.
	 * @param array  $key secret key for encryption/decryption.
	 * @return array bytes array
	 */
	public static function encrypt_rc4( $str, $key ) {
		$s = array();
		for ( $i = 0; $i < 256; $i++ ) {
			$s[ $i ] = $i;
		}
		$j = 0;
		for ( $i = 0; $i < 256; $i++ ) {
			$j       = ( $j + $s[ $i ] + ord( $key[ $i % strlen( $key ) ] ) ) % 256;
			$x       = $s[ $i ];
			$s[ $i ] = $s[ $j ];
			$s[ $j ] = $x;
		}
		$i      = 0;
		$j      = 0;
		$res    = '';
		$length = strlen( $str );
		for ( $y = 0; $y < $length; $y++ ) {
			$i       = ( ++$i ) % 256;
			$j       = ( $j + $s[ $i ] ) % 256;
			$x       = $s[ $i ];
			$s[ $i ] = $s[ $j ];
			$s[ $j ] = $x;
			$res    .= $str[ $y ] ^ chr( $s[ ( $s[ $i ] + $s[ $j ] ) % 256 ] );
		}
		return $res;
	}

	/**
	 * Get access key.
	 *
	 * @return string
	 */
	public static function get_access_key() {
		return get_option( 'LAPOSTEPROEXP_ACCESS_KEY' );
	}

	/**
	 * Get secret key.
	 *
	 * @return string
	 */
	public static function get_secret_key() {
		return get_option( 'LAPOSTEPROEXP_SECRET_KEY' );
	}

	/**
	 * Get maps token.
	 *
	 * @return string
	 */
	public static function get_map_token_url() {
		return get_option( 'LAPOSTEPROEXP_MAP_TOKEN_URL' );
	}
}
