<?php

namespace GroundhoggBuddyBoss\Admin;

use Groundhogg\Contact;
use function Groundhogg\get_array_var;
use function Groundhogg\get_request_var;
use function Groundhogg\html;
use function Groundhogg\validate_tags;
use function Groundhogg\white_labeled_name;

class Groundhogg_Bb_Groups {

	public function __construct() {
		add_action( 'bp_groups_admin_meta_boxes', [ $this, 'register_meta_boxes' ] );
		add_action( 'bp_group_admin_edit_after', [ $this, 'save_tags_data' ], 10, 1 );
		add_action( 'groups_join_group', [ $this, 'add_group_tag' ], 10, 2 );
		add_action( 'groups_leave_group', [ $this, 'remove_group_tag' ], 10, 2 );
		add_action( 'groups_member_after_save', [ $this, 'learn_dash_sync_add' ], 10, 1 );
		add_action( 'groups_member_after_remove', [ $this, 'learn_dash_sync_remove' ], 10, 1 );
//		add_action( 'bp_ld_sync/learndash_group_user_added' , [$this, 'add_group_tag'] , 10 , 2  );
//		add_action( 'bp_ld_sync/learndash_group_user_removed' , [$this, 'remove_group_tag'] , 10 , 2  );

	}

	/**
	 * Handles the auto sync with LearnDash. User added action.
	 *
	 * @param $data
	 */
	function learn_dash_sync_add( $data ) {
		$this->add_group_tag( absint( get_array_var( $data, 'group_id' ) ), absint( get_array_var( $data, 'user_id' ) ) );
	}

	/**
	 * Handles the auto sync with LearnDash. User removed from the group action.
	 *
	 * @param $data
	 */
	function learn_dash_sync_remove( $data ) {
		$this->remove_group_tag( absint( get_array_var( $data, 'group_id' ) ), absint( get_array_var( $data, 'user_id' ) ) );
	}

	/**
	 * Remove the tags from the contact when contact leave the group.
	 *
	 * @param $group_id
	 * @param $user_id
	 */
	public function remove_group_tag( $group_id, $user_id ) {

		$contact = new Contact( absint($user_id ), true );

		if ( $contact && $contact->exists() && (bool) groups_get_groupmeta(absint( $group_id ), 'groundhogg_reverse_tags', true ) ) {
			$contact->remove_tag( groups_get_groupmeta(absint( $group_id ), 'groundhogg_tags', true ) );
			$contact->apply_tag( groups_get_groupmeta(absint( $group_id ), 'groundhogg_tags_remove', true ) );
		}
	}

	/**
	 *
	 * @param $group_id
	 * @param $user_id
	 */
	public function add_group_tag( $group_id, $user_id ) {

		$contact = new Contact( absint( $user_id ), true );
		if ( $contact && $contact->exists() && absint( $group_id ) ) {

			$contact->apply_tag( groups_get_groupmeta( absint( $group_id ), 'groundhogg_tags', true ) );
			$contact->remove_tag( groups_get_groupmeta( absint( $group_id ), 'groundhogg_tags_remove', true ) );
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

	/**
	 * Display the integration settings
	 *
	 * @param $post
	 */
	public function meta_box_callback( $post ) {

		$post_id = get_request_var( 'gid' );

		echo html()->wrap( html()->wrap( __( 'Add Tags', 'groundhogg-buddyboss' ), 'b' ), 'h3' );

		echo html()->tag_picker( [
			'name'     => 'groundhogg_tags[]',
			'id'       => 'groundhogg_tags',
			'selected' => groups_get_groupmeta( $post_id, 'groundhogg_tags', true )
		] );

		echo html()->description( __( 'The selected tags will be added to the contact when they join the group.', 'groundhogg-buddyboss' ) );

		echo html()->wrap( html()->wrap( __( 'Remove Tags', 'groundhogg-buddyboss' ), 'b' ), 'h3' );

		echo html()->tag_picker( [
			'name'     => 'groundhogg_tags_remove[]',
			'id'       => 'groundhogg_tags_remove',
			'selected' => groups_get_groupmeta( $post_id, 'groundhogg_tags_remove', true )
		] );

		echo html()->description( __( 'The selected tags will be removed from the contact when they leave the group.', 'groundhogg-buddyboss' ) );

		echo html()->wrap( html()->wrap( __( 'Reverse Tags', 'groundhogg-buddyboss' ), 'b' ), 'h3' );

		echo html()->checkbox( [
			'name'    => 'groundhogg_reverse_tags',
			'id'      => 'groundhogg_reverse_tags',
			'checked' => groups_get_groupmeta( $post_id, 'groundhogg_reverse_tags', true ) ? groups_get_groupmeta( $post_id, 'groundhogg_reverse_tags', true ) : 0,
			'label'   => __( 'Enable', 'groundhogg-buddyboss' )
		] );

		echo html()->description( __( 'When the contact leaves the group this will reverse the tags. Any tags which were added will be removed and any tags which were removed will be added.', 'groundhogg-buddyboss' ) );
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