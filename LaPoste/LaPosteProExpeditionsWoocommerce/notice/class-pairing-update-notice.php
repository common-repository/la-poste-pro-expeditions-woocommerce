<?php
/**
 * Contains code for the pairing update notice class.
 *
 * @package     LaPoste\LaPosteProExpeditionsWoocommerce\Notice
 */

namespace LaPoste\LaPosteProExpeditionsWoocommerce\Notice;

/**
 * Pairing update notice class.
 *
 * Enables pairing update validation.
 */
class Pairing_Update_Notice extends Abstract_Notice {

	/**
	 * Construct function.
	 *
	 * @param string $key key for notice.
	 * @void
	 */
	public function __construct( $key ) {
		parent::__construct( $key );
		$this->type         = 'pairing-update';
		$this->autodestruct = false;
		$this->template     = 'html-pairing-update-notice';
	}
}
