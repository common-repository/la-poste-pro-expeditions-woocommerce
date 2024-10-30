<?php
/**
 * Contains code for subscription util class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Util
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Util;

/**
 * Logger util class.
 *
 * Helper to manage requests logging
 */
class Logger_Util {

	/**
	 * Get Logger context.
	 *
	 * @var array
	 */
	private static function get_context() {
		return array(
			'source' => 'la-poste-pro-expeditions-woocommerce',
		);
	}

	/**
	 * Log a new plugin message
	 * Logs are accepted if the log configuration is enabled or if the plugin is not paired yet.
	 *
	 * @param string $level log level.
	 * @param string $message log message.
	 */
	private static function log( $level, $message ) {
		if ( Configuration_Util::get_logging() || ! Auth_Util::is_plugin_paired() ) {
			wc_get_logger()->log( $level, $message, self::get_context() );
		}
	}

	/**
	 * Log an info message.
	 *
	 * @param string $message log message.
	 */
	public static function info( $message ) {
		self::log( 'info', $message );
	}

	/**
	 * Log a warning message.
	 *
	 * @param string $message log message.
	 */
	public static function warning( $message ) {
		self::log( 'warning', $message );
	}
}
