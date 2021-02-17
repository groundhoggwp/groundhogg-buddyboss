<?php

namespace GroundhoggBuddyBoss;

use Groundhogg\Contact;
use function Groundhogg\do_replacements;
use Groundhogg\Replacements;
use \WP_Error;
use Groundhogg\Plugin;

class Bb_Replacements {

	/**
	 * @var array
	 */
	private $codes;

	public function __construct() {
		add_action( 'groundhogg/replacements/init', [ $this, 'setup_codes' ] );
	}


	/**
	 * @param $replacements Replacements
	 */
	public function setup_codes( $replacements ) {

		$replacements->add(
			"buddyboss",
			[ $this, 'replacement_bb' ],
			"Any data related to the BuddyBoss filds. Usage: {buddyboss.fieldname} Example:{buddyboss.First Name}"
		);

	}


	/**
	 * Return the
	 *
	 * @param $contact_id int
	 * @param $arg string the meta key
	 *
	 * @return mixed|string
	 */
	function replacement_bb( $arg, $contact_id ) {
		if ( empty( $arg ) ) {
			return '';
		}


		$contact = new Contact( $contact_id );

		if ( ! $contact->get_user_id() ) {
			return '';
		}

		return  xprofile_get_field_data( $arg, $contact->get_user_id() ) ;
	}


}