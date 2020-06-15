<?php

namespace GroundhoggBuddyBoss\Admin;

use Groundhogg\Contact;
use function Groundhogg\get_request_var;
use function Groundhogg\html;
use function Groundhogg\validate_tags;
use function Groundhogg\white_labeled_name;
use function GroundhoggBuddyBoss\get_all_member_types;

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
	 * @param $user_id
	 * @param $member_type
	 * @param $append
	 */
	public function apply_member_type_tag( $user_id, $member_type, $append ) {



		$post    = bp_member_type_post_by_type( $member_type );
		$contact = new Contact( $user_id, true );

		if ( $contact && $contact->exists() && $post ){

			// Remove the tags of other profile types...
			foreach ( get_all_member_types() as $p ) {
				$contact->remove_tag( get_post_meta( $p, 'groundhogg_tags'  ,true) );
			}

			// Add the new profile tag and remove any associated tags
			$contact->apply_tag( get_post_meta( absint($post ), 'groundhogg_tags' ,true ) );
			$contact->remove_tag( get_post_meta( absint( $post ), 'groundhogg_tags_remove' ,true ) );

		}

	}

	/**
	 * Register the metabox
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'groundhogg-bb-member-types',
			sprintf( __( '%s Integration' ), white_labeled_name() ),
			[ $this, 'meta_box_callback' ],
			[ bp_get_member_type_post_type() ],
			'side',
			'default'
		);
	}

	/**
	 * Display the Integration metabox
	 *
	 * @param $post
	 */
	public function meta_box_callback( $post ) {

		$post_id = $post->ID;

		echo html()->wrap( html()->wrap( __( 'Add Tags', 'groundhogg-buddyboss' ), 'b' ), 'h3' );

		echo html()->tag_picker( [
			'name'     => 'groundhogg_tags[]',
			'id'       => 'groundhogg_tags',
			'selected' => get_post_meta( $post_id, 'groundhogg_tags', true )
		] );

		echo html()->description( __( 'The selected tags will be added to the contact when this profile type assigned to the user. If the profile type is changed the tag will be removed automatically.', 'groundhogg-buddyboss' ) );

		echo html()->wrap( html()->wrap( __( 'Remove Tags', 'groundhogg-buddyboss' ), 'b' ), 'h3' );

		echo html()->tag_picker( [
			'name'     => 'groundhogg_tags_remove[]',
			'id'       => 'groundhogg_tags_remove',
			'selected' => get_post_meta( $post_id, 'groundhogg_tags_remove', true )
		] );

		echo html()->description( __( 'The selected tags will be removed from the contact when this member type assigned to the user.', 'groundhogg-buddyboss' ) );

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