<?php

namespace GroundhoggBuddyBoss\Bulk_Jobs;

use Groundhogg\Bulk_Jobs\Bulk_Job;
use Groundhogg\Contact;
use function GroundhoggBuddyBoss\get_all_member_types;
use function GroundhoggBuddyBoss\update_contact_from_xprofile_field_map;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sync_Groups_And_Member_Types extends Bulk_Job {

	/**
	 * post type
	 */

	protected $member_types = [];

	protected $groups = [];


	/**
	 * Get the action reference.
	 *
	 * @return string
	 */
	function get_action() {
		return 'bb_sync_groups_and_member_types';
	}


	/**
	 * Get an array of items someway somehow
	 *
	 * @param $items array
	 *
	 * @return array
	 */
	public function query( $items ) {
		if ( ! current_user_can( 'edit_tags' ) ) {
			return $items;
		}

		// get groups and
		return wp_parse_id_list( wp_list_pluck( get_users(), 'ID' ) );

	}

	/**
	 * Get the maximum number of items which can be processed at a time.
	 *
	 * @param $max   int
	 * @param $items array
	 *
	 * @return int
	 */
	public function max_items( $max, $items ) {
		if ( ! current_user_can( 'edit_contacts' ) ) {
			return $max;
		}

		return min( 50, intval( ini_get( 'max_input_vars' ) ) );
	}

	/**
	 * Process an item
	 *
	 * @param $item mixed
	 *
	 * @return void
	 */
	protected function process_item( $item ) {

		$contact = new Contact( $item, true );

		if ( ! $contact->exists() ) {
			return;
		}

		$type = bp_get_member_type( $item );

		$args = [
			'user_id' => $item,
		];

		if ( bp_has_profile( $args ) ) :
			while ( bp_profile_groups() ) :
				bp_the_profile_group();
				if ( bp_profile_group_has_fields() ) :
					while ( bp_profile_fields() ) :
						bp_the_profile_field();
						if ( bp_field_has_data() ) :
							update_contact_from_xprofile_field_map( $contact, bp_get_the_profile_field_id(), bp_get_the_profile_field_value() );
						endif;
					endwhile;
				endif;
			endwhile;
		endif;

		/**
		 * Remove other profile type tags
		 */
		foreach ( array_diff( array_keys( $this->member_types ), [ $type ] ) as $m ) {
			$contact->remove_tag( $this->member_types [ $m ] ['apply_tags'] );
		}

		/**
		 * Apply member type tag
		 */
		if ( $type && array_key_exists( $type, $this->member_types ) ) {
			$contact->apply_tag( $this->member_types [ $type ] ['apply_tags'] );
			$contact->remove_tag( $this->member_types [ $type ] ['remove_tags'] );
		}

		// apply groups tags

		$groups = wp_parse_id_list( wp_list_pluck( bp_get_user_groups( $item ), 'group_id' ) );
		/**
		 * Remove tag in which user is not a member
		 */
		foreach ( array_diff( array_keys( $this->groups ), $groups ) as $g ) {
			$contact->remove_tag( $this->groups [ $g ] ['apply_tags'] );
		}

		/**
		 * apply tag based on the group
		 */
		if ( ! empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$contact->apply_tag( $this->groups [ $group ] ['apply_tags'] );
				$contact->remove_tag( $this->groups [ $group ] ['remove_tags'] );
			}
		}
	}

	/**
	 * Do stuff before the loop
	 *
	 * @return void
	 */
	protected function pre_loop() {

		// loop through tags and make a list of the apply tags based on post type
		$types = get_all_member_types();

		foreach ( $types as $type ) {
			$this->member_types [ bp_get_member_type_key( $type ) ]['apply_tags']  = get_post_meta( absint( $type ), 'groundhogg_tags', true );
			$this->member_types [ bp_get_member_type_key( $type ) ]['remove_tags'] = get_post_meta( absint( $type ), 'groundhogg_tags_remove', true );
		}

		// get groups and
		$groups = wp_parse_id_list( wp_list_pluck( groups_get_groups( [ 'per_page' => - 1 ] )['groups'], 'id' ) );
		foreach ( $groups as $group ) {
			$this->groups [ $group ]['apply_tags']  = groups_get_groupmeta( absint( $group ), 'groundhogg_tags', true );
			$this->groups [ $group ]['remove_tags'] = groups_get_groupmeta( absint( $group ), 'groundhogg_tags_remove', true );
		}
	}

	/**
	 * do stuff after the loop
	 *
	 * @return void
	 */
	protected function post_loop() {
	}

	/**
	 * Cleanup any options/transients/notices after the bulk job has been processed.
	 *
	 * @return void
	 */
	protected function clean_up() {
	}


	/**
	 * Get the return URL
	 *
	 * @return string
	 */
	protected function get_return_url() {
		return admin_url( 'admin.php?page=gh_tools&tab=misc' );
	}
}