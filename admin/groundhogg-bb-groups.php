<?php

namespace GroundhoggBuddyBoss\Admin;

use Groundhogg\Contact;
use function Groundhogg\get_contactdata;
use function Groundhogg\get_request_var;
use function Groundhogg\html;
use function Groundhogg\is_option_enabled;
use function Groundhogg\validate_tags;
use function Groundhogg\white_labeled_name;
use function GroundhoggBuddyBoss\get_post_by_member_type;

class Groundhogg_Bb_Groups {

	public function __construct() {
		add_action( 'bp_groups_admin_meta_boxes', [ $this, 'register_meta_boxes' ] );
		add_action( 'bp_group_admin_edit_after', [ $this, 'save_tags_data' ]  , 10 , 1);
		add_action( 'groups_join_group', [ $this, 'add_group_tag' ], 10, 2 );
		add_action( 'groups_leave_group', [ $this, 'remove_group_tag' ], 10, 2 );

	}

	/**
	 * Remove the tags from the contact when contact leave the group.
	 *
	 * @param $group_id
	 * @param $user_id
	 */
	public function remove_group_tag( $group_id, $user_id ) {
		$contact = new Contact( $user_id, true );
		if ( $contact && $contact->exists() &&  (bool) groups_get_groupmeta($group_id,'groundhogg_reverse_tags' ,true)) {
			$contact->remove_tag( groups_get_groupmeta( $group_id, 'groundhogg_tags', true ) );
			$contact->apply_tag( groups_get_groupmeta( $group_id, 'groundhogg_tags_remove', true ) );

		}
	}


	/**
	 *
	 * @param $group_id
	 * @param $user_id
	 */
	public function add_group_tag( $group_id, $user_id ) {
		$contact = new Contact( $user_id, true );

		if ( $contact && $contact->exists() && $group_id) {
			$contact->apply_tag( groups_get_groupmeta( $group_id, 'groundhogg_tags', true ) );
			$contact->remove_tag( groups_get_groupmeta( $group_id, 'groundhogg_tags_remove', true ) );

		}
	}

	public function register_meta_boxes() {
		add_meta_box(
			'groundhogg-bb-groups',
			sprintf( __( '%s Integration' ), white_labeled_name() ),
			[ $this, 'meta_box_callback' ],
			get_current_screen()->id,
			'side',
			'default'
		);
	}


	public function meta_box_callback( $post ) {

		$post_id = get_request_var( 'gid' );

		echo html()->wrap( html()->wrap( __( 'Add Tags', 'groundhogg-buddyboss' ), 'b' ), 'h3' );

		echo html()->tag_picker( [
			'name'     => 'groundhogg_tags[]',
			'id'       => 'groundhogg_tags',
			'selected' => groups_get_groupmeta( $post_id, 'groundhogg_tags', true )
		] );

		echo html()->description( __( 'This tag will be added to the contact when contact join this group.', 'groundhogg-buddyboss' ) );

		echo html()->wrap( html()->wrap( __( 'Remove Tags', 'groundhogg-buddyboss' ), 'b' ), 'h3' );


		echo html()->tag_picker( [
			'name'     => 'groundhogg_tags_remove[]',
			'id'       => 'groundhogg_tags_remove',
			'selected' => groups_get_groupmeta( $post_id, 'groundhogg_tags_remove', true )
		] );

		echo html()->description( __( 'This tag will be removed from the contact when contact leave this group.', 'groundhogg-buddyboss' ) );

		echo html()->wrap( html()->wrap( __( 'Reverse Tags on Leave Group', 'groundhogg-buddyboss' ), 'b' ), 'h3' );

		echo html()->checkbox( [
			'name'    => 'groundhogg_reverse_tags',
			'id'      => 'groundhogg_reverse_tags',
			'checked' => groups_get_groupmeta( $post_id, 'groundhogg_reverse_tags', true ) ? groups_get_groupmeta( $post_id, 'groundhogg_reverse_tags', true ) : 0,
			'label' => __( 'Enable' , 'groundhogg-buddyboss' )
		] );

		echo html()->description( __( 'Enabling this option will remove all the applied tag and applied all the remove tag to the contact when contact leave the group.', 'groundhogg-buddyboss' ) );


	}

	/**
	 * @param $group_id int
	 */
	public function save_tags_data( $group_id ) {

		groups_update_groupmeta( $group_id, 'groundhogg_tags', validate_tags( get_request_var( 'groundhogg_tags', [] ) ) );
		groups_update_groupmeta( $group_id, 'groundhogg_tags_remove', validate_tags( get_request_var( 'groundhogg_tags_remove', [] ) ) );
		if ( get_request_var( 'groundhogg_reverse_tags' ) ) {
			groups_update_groupmeta( $group_id, 'groundhogg_reverse_tags', true );
		} else {
			groups_delete_groupmeta( $group_id, 'groundhogg_reverse_tags' );
		}

	}


}