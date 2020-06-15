<?php

namespace GroundhoggBuddyBoss;

use Groundhogg\Contact;
use function Groundhogg\get_contactdata;
use function Groundhogg\get_post_var;

/**
 * Get all the member-type ids
 *
 * @return int[]
 */
function get_all_member_types() {

	$posts = get_posts( [
		'post_type' => bp_get_member_type_post_type(),
	] );

	return wp_parse_id_list( wp_list_pluck( $posts, 'ID' ) );
}

add_action( 'init', __NAMESPACE__ . '\add_preferences_tab' );

/**
 * Add the preferences tab to the profile
 */
function add_preferences_tab() {

	if ( ! function_exists( 'bp_is_my_profile' ) || ! defined( 'GROUNDHOGG_ADVANCED_PREFERENCES_VERSION' ) ) {
		return;
	}

	if ( bp_is_my_profile() ) {
		global $bp;
		bp_core_new_subnav_item( array(
			'name'            => __( 'Preferences', 'groundhogg-buddyboss' ),
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
	echo __( "Manage Preferences", 'groundhogg-buddyboss' );
}

/**
 * Displays the content
 */
function preference_content() {

	if ( wp_verify_nonce( get_post_var( 'tag_prefs_nonce' ), 'bp_save_tag_prefs' ) && get_post_var( 'tag_prefs' ) ) {
		$contact = get_contactdata( get_current_user_id(), true );
		if ( $contact && $contact->exists() ) {
			\GroundhoggAdvancedPreferences\Plugin::$instance->preferences->save_tag_preferences( $contact, get_post_var( 'tag_prefs' ) );
		}
	}

	echo "<form name='update-preference' method='post'>";
	wp_nonce_field( 'bp_save_tag_prefs', 'tag_prefs_nonce' );
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
function set_profile_picture_from_bp( $profile_pic, $contact_id, $contact ) {

	// Get the BB avatar url...
	$avatar = bp_core_fetch_avatar( [
		'item_id' => $contact->get_user_id(),
		'object'  => 'user',
		'type'    => 'thumb',
		'html'    => false
	] );

	if ( $avatar ){
		return $avatar;
	}

	return $profile_pic;
}

add_filter( 'groundhogg/contact/profile_picture', __NAMESPACE__ . '\set_profile_picture_from_bp', 10, 3 );