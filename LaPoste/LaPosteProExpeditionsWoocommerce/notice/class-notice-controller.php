<?php
/**
 * Contains code for the notice controller class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Notice
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Notice;

use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Auth_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Shipping_Api_Util;
use LaPoste\LaPosteProExpeditionsWoocommerce\Util\Frontend_Util;

/**
 * Notice controller class.
 *
 * Controller for notices.
 */
class Notice_Controller {

	/**
	 * Notice name.
	 *
	 * @var string
	 */
	public static $update = 'update';

	/**
	 * Notice name.
	 *
	 * @var string
	 */
	public static $setup_wizard = 'setup-wizard';

	/**
	 * Notice name.
	 *
	 * @var string
	 */
	public static $configuration_failure = 'configuration-failure';

	/**
	 * Notice name.
	 *
	 * @var string
	 */
	public static $pairing = 'pairing';

	/**
	 * Notice name.
	 *
	 * @var string
	 */
	public static $pairing_update = 'pairing-update';

	/**
	 * Notice name.
	 *
	 * @var string
	 */
	public static $custom = 'custom';

	/**
	 * Notice name.
	 *
	 * @var string
	 */
	public static $environment_warning = 'environment-warning';

	/**
	 * Array of notices - name => callback.
	 *
	 * @var array
	 */
	private static $core_notices = array( 'update', 'setup-wizard', 'pairing', 'pairing-update', 'configuration-failure', 'environment-warning' );

	/**
	 * Plugin url.
	 *
	 * @var string
	 */
	private $plugin_url;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $plugin_version;

	/**
	 * WordPress nonce for ajax requests
	 *
	 * @var string
	 */
	private $ajax_nonce;

	/**
	 * Construct function.
	 *
	 * @param array $plugin plugin array.
	 * @void
	 */
	public function __construct( $plugin ) {
		$this->plugin_url     = $plugin['url'];
		$this->plugin_version = $plugin['version'];
		$this->ajax_nonce     = wp_create_nonce( 'lpfr-eco_woocommerce_notice' );
	}

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run() {
		add_action( 'admin_init', array( $this, 'init_notices' ) );
	}

	/**
	 * Initialize plugin notices
	 *
	 * @void
	 */
	public function init_notices() {
		global $plugin_page;

		if ( 'la-poste-pro-expeditions-woocommerce-settings' === $plugin_page ) {
			self::remove_notice( self::$pairing );
		}

		$notices = self::get_notice_instances();

		if ( ! empty( $notices ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'notice_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'notice_styles' ) );
			add_action( 'wp_ajax_laposteproexp_hide_notice', array( $this, 'hide_notice_callback' ) );

			foreach ( $notices as $notice ) {
				add_action( 'admin_notices', array( $notice, 'render' ) );

				if ( 'pairing-update' === $notice->type ) {
					add_action( 'wp_ajax_laposteproexp_pairing_update_validate', array( $this, 'pairing_update_validate_callback' ) );
				}
			}
		}
	}

	/**
	 * Enqueue notice scripts
	 *
	 * @void
	 */
	public function notice_scripts() {
		wp_enqueue_script( 'laposteproexp_polyfills', $this->plugin_url . 'LaPoste/LaPosteProExpeditionsWoocommerce/assets/js/polyfills.min.js', array(), $this->plugin_version, false );
		wp_enqueue_script( 'laposteproexp_notices', $this->plugin_url . 'LaPoste/LaPosteProExpeditionsWoocommerce/assets/js/notices.min.js', array( 'laposteproexp_polyfills' ), $this->plugin_version, false );

		$data = array( 'noticeAjaxNonce' => $this->ajax_nonce );
		Frontend_Util::inject_inline_data( 'laposteproexp_notices', 'laposteproexpData', $data );
	}

	/**
	 * Enqueue notice styles
	 *
	 * @void
	 */
	public function notice_styles() {
		wp_enqueue_style( 'laposteproexp_notices', $this->plugin_url . 'LaPoste/LaPosteProExpeditionsWoocommerce/assets/css/notices.css', array(), $this->plugin_version );
	}

	/**
	 * Get notice instances.
	 *
	 * @return mixed $notices instances of notice.
	 */
	public static function get_notice_instances() {
		$notices          = self::get_notice_keys();
		$notice_instances = array();

		foreach ( $notices as $key ) {
			$classname = 'LaPoste\LaPosteProExpeditionsWoocommerce\Notice\\';
			if ( ! in_array( $key, self::$core_notices, true ) ) {
				$notice = get_transient( $key );
				if ( false !== $notice && isset( $notice['type'] ) ) {
					$classname .= ucfirst( $notice['type'] ) . '_Notice';
					if ( class_exists( $classname, true ) ) {
						$class              = new $classname( $key, $notice );
						$notice_instances[] = $class;
					}
				} else {
					self::remove_notice( $key );
				}
			} else {
				$classname .= ucwords( str_replace( '-', '_', $key ) ) . '_Notice';
				if ( class_exists( $classname, true ) ) {
					$extra = get_option( 'LAPOSTEPROEXP_NOTICE_' . $key );
					if ( false !== $extra ) {
						$class = new $classname( $key, $extra );
					} else {
						$class = new $classname( $key );
					}
					$notice_instances[] = $class;
				}
			}
		}
		return $notice_instances;
	}

	/**
	 * Get notice keys.
	 *
	 * @return array of notice keys.
	 */
	public static function get_notice_keys() {
		return get_option( 'LAPOSTEPROEXP_NOTICES', array() );
	}

	/**
	 * Add notice.
	 *
	 * @param string $type type of notice.
	 * @param mixed  $args additional args.
	 * @void
	 */
	public static function add_notice( $type, $args = array() ) {
		if ( ! in_array( $type, self::$core_notices, true ) ) {
			$key           = uniqid( 'laposteproexp_', false );
			$value         = $args;
			$value['type'] = $type;
			set_transient( $key, $value, DAY_IN_SECONDS );
		} else {
			$key = $type;
			if ( ! empty( $args ) ) {
				update_option( 'LAPOSTEPROEXP_NOTICE_' . $key, $args );
			}
		}
		$notices = get_option( 'LAPOSTEPROEXP_NOTICES', array() );
		if ( ! in_array( $key, $notices, true ) ) {
			$notices[] = $key;
			update_option( 'LAPOSTEPROEXP_NOTICES', $notices );
		}
	}

	/**
	 * Remove notice.
	 *
	 * @param string $key notice key.
	 * @void
	 */
	public static function remove_notice( $key ) {
		$notices = self::get_notice_keys();
		$index   = array_search( $key, $notices, true );
		if ( false !== $index ) {
			unset( $notices[ $index ] );
		}
		update_option( 'LAPOSTEPROEXP_NOTICES', $notices );
	}

	/**
	 * Whether there are active notices.
	 *
	 * @return boolean
	 */
	public static function has_notices() {
		$notices = self::get_notice_keys();
		return ! empty( $notices );
	}

	/**
	 * Whether given notice is active.
	 *
	 * @param string $notice notice id.
	 * @return boolean
	 */
	public static function has_notice( $notice ) {
		$notices = self::get_notice_keys();
		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice_key ) {
				if ( $notice === $notice_key ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Ajax callback. Hide notice.
	 *
	 * @void
	 */
	public function hide_notice_callback() {
		check_ajax_referer( 'lpfr-eco_woocommerce_notice', 'security' );
		header( 'Content-Type: application/json; charset=utf-8' );
		if ( ! isset( $_REQUEST['notice_id'] ) ) {
			wp_send_json( true );
		}
		$notice_id = sanitize_text_field( wp_unslash( $_REQUEST['notice_id'] ) );
		self::remove_notice( $notice_id );
		wp_send_json( true );
	}

	/**
	 * Ajax callback. Validate pairing update.
	 *
	 * @void
	 */
	public function pairing_update_validate_callback() {
		check_ajax_referer( 'lpfr-eco_woocommerce_notice', 'security' );
		header( 'Content-Type: application/json; charset=utf-8' );
		if ( ! isset( $_REQUEST['approve'] ) ) {
			wp_send_json_error( 'missing input' );
		}
		$approve            = sanitize_text_field( wp_unslash( $_REQUEST['approve'] ) );
		$pairing_update_url = get_option( 'LAPOSTEPROEXP_PAIRING_UPDATE' );

		$updated = Shipping_Api_Util::update_pairing( $pairing_update_url, $approve );

		if ( $updated ) {
			Auth_Util::end_pairing_update();
			self::remove_notice( self::$pairing_update );
			if ( '1' === $approve ) {
				self::add_notice( self::$pairing, array( 'result' => 1 ) );
			}
			wp_send_json( true );
		} else {
			wp_send_json_error( 'pairing validation failed' );
		}
	}

	/**
	 * Remove all notices.
	 *
	 * @void
	 */
	public static function remove_all_notices() {
		update_option( 'LAPOSTEPROEXP_NOTICES', array() );
	}
}
