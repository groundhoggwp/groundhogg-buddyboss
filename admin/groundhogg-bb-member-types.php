<?php

namespace GroundhoggBuddyBoss\Admin;

use function Groundhogg\get_contactdata;
use function Groundhogg\get_request_var;
use function Groundhogg\html;
use function Groundhogg\validate_tags;
use function Groundhogg\white_labeled_name;
use function GroundhoggBuddyBoss\get_all_member_type;
use function GroundhoggBuddyBoss\get_post_by_member_type;

class Groundhogg_Bb_Member_Types {

	public function __construct() {

		add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ] );
		add_action( 'save_post_bp-member-type', [ $this, 'save_data' ] );
		add_action( 'bp_set_member_type', [ $this, 'apply_member_type_tag' ], 10, 3 );


	}


	/**
	 * Adds the tag name based on the member type name.
	 * It Automatically assign new names but does not remove the old one.
	 *
	 *
	 * @param $user_id
	 * @param $member_type
	 * @param $append
	 */
	public function apply_member_type_tag( $user_id, $member_type, $append ) {


		$post    = get_post_by_member_type( $member_type );
		$contact = get_contactdata( $user_id, true );

		if ( $contact && $contact->exists() && $post->ID ) {

			foreach ( get_all_member_type( $member_type ) as $p ) {
				$contact->remove_tag( get_post_meta( $p, 'groundhogg_tags'  ,true) );
			}

			$contact->apply_tag( get_post_meta( $post->ID, 'groundhogg_tags' ,true ) );
			$contact->remove_tag( get_post_meta( $post->ID, 'groundhogg_tags_remove' ,true ) );

		}

	}


	public function register_meta_boxes() {
		add_meta_box(
			'groundhogg-bb-member-types',
			sprintf( __( '%s Integration' ), white_labeled_name() ),
			[ $this, 'meta_box_callback' ],
			[ 'bp-member-type' ],
			'side',
			'default'
		);
	}


	public function meta_box_callback( $post ) {

		$post_id = $post->ID;

		echo html()->wrap( html()->wrap( __( 'Add Tags', 'groundhogg' ), 'b' ), 'h3' );

		echo html()->tag_picker( [
			'name'     => 'groundhogg_tags[]',
			'id'       => 'groundhogg_tags',
			'selected' => get_post_meta( $post_id, 'groundhogg_tags', true )
		] );

		echo html()->description( __( 'add tags', 'groundhogg-buddyboss' ) );

		echo html()->wrap( html()->wrap( __( 'Remove Tags', 'groundhogg' ), 'b' ), 'h3' );


		echo html()->tag_picker( [
			'name'     => 'groundhogg_tags_remove[]',
			'id'       => 'groundhogg_tags_remove',
			'selected' => get_post_meta( $post_id, 'groundhogg_tags_remove', true )
		] );

		echo html()->description( __( 'Remove tag', 'groundhogg' ) );

	}


	/**
	 *-
	 * @param $post_id int
	 */
	public function save_data( $post_id ) {

		update_post_meta( $post_id, 'groundhogg_tags', validate_tags( get_request_var( 'groundhogg_tags', [] ) ) );
		update_post_meta( $post_id, 'groundhogg_tags_remove', validate_tags( get_request_var( 'groundhogg_tags_remove', [] ) ) );
	}


}