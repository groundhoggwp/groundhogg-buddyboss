<?php

namespace GroundhoggBuddyBoss;

use Groundhogg\Contact;
use function Groundhogg\get_contactdata;
use function Groundhogg\get_post_var;

/**
 * Get the post by the member type
 *
 * @return array|\WP_Post|null
 */
function get_post_by_member_type( $member_type ) {
	$post = get_page_by_path( $member_type, OBJECT, bp_get_member_type_post_type() );

	return $post;
}


/**
 * Get all the member-type ids
 *
 * @param $member_type
 *
 * @return int[]
 */
function get_all_member_type( $member_type ) {
	$obj   = bp_get_member_type_object( $member_type );
	$posts = get_posts( [
		'post_type' => bp_get_member_type_post_type(),
		'slug'      => $obj->directory_slug,
	] );

	return wp_parse_id_list( wp_list_pluck( $posts, 'ID' ) );

}


add_action( 'init', __NAMESPACE__ . '\add_tab' );


function add_tab() {

	global $bp;


	if ( bp_is_my_profile() && defined( 'GROUNDHOGG_ADVANCED_PREFERENCES_VERSION' ) ) {

		bp_core_new_subnav_item( array(
			'name'            => __( 'Preferences' ,'groundhogg-buddyboss' ),
			'slug'            => 'manage-preference',
			'parent_url'      => $bp->loggedin_user->domain . $bp->profile->slug . '/',
			'parent_slug'     => $bp->profile->slug,
			'screen_function' => __NAMESPACE__ . '\display_preference_screen',
			'position'        => 40
		) );
	}

}

/**
 * add hooks to display page details.
 */
function display_preference_screen() {
	//add title and content here – last is to call the members plugin.php template
	add_action( 'bp_template_title', __NAMESPACE__ . '\preference_title' );
	add_action( 'bp_template_content', __NAMESPACE__ . '\preference_content' );
	bp_core_load_template( 'members/single/plugins' );

}

/**
 * Displays the title of the preference page
 */
function preference_title() {
	echo __( "Manage Preferences" ,'groundhogg-buddyboss'  );
}

/**
 * Displays the content
 */
function preference_content() {

	if ( get_post_var( 'tag_prefs' ) ) {
		$contact = get_contactdata( get_current_user_id(), true );
		if ( $contact && $contact->exists() ) {
			\GroundhoggAdvancedPreferences\Plugin::$instance->preferences->save_tag_preferences( $contact, get_post_var( 'tag_prefs' ) );
		}
	}

	echo "<form name='update-preference' method='post'>";
	\GroundhoggAdvancedPreferences\Plugin::$instance->preferences->show_tag_preferences();
	echo "<input type='submit' value='Save Changes'>";
	echo "</form>";

}

/**
 * Display profile picture from the buddy boss area.
 *
 * @param  $profile_pic string
 * @param $contact_id int
 * @param $contact Contact
 *
 * @return false|string
 */
function set_profile_picture( $profile_pic, $contact_id, $contact ) {

	if ( get_avatar_url( $contact->get_user_id() ) ) {
		return get_avatar_url( $contact->get_user_id() );
	}

	return $profile_pic;
}

add_filter( 'groundhogg/contact/profile_picture', __NAMESPACE__ . '\set_profile_picture' , 10 , 3 );