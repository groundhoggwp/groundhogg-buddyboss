<?php

namespace GroundhoggBuddyBoss;

use Groundhogg\Preferences;
use function Groundhogg\get_contactdata;
use function Groundhogg\get_post_var;
use function Groundhogg\get_request_var;
use function Groundhogg\html;

/**
 * Get the post by the member type
 *
 * @param $member_type
 */
/**
 * Get the post by the member type
 *
 * @param $member_type
 */
function get_post_by_member_type( $member_type ) {
	$post = get_page_by_path( $member_type, OBJECT, bp_get_member_type_post_type() );

	return $post;
}

/**
 * Get the post by the member type
 *
 * @param $member_type
 */
function get_all_member_type( $member_type ) {
	$obj   = bp_get_member_type_object( $member_type );
	$posts = get_posts( [
		'post_type' => bp_get_member_type_post_type(),
		'slug'      => $obj->directory_slug,
	] );

	return wp_parse_id_list( wp_list_pluck( $posts, 'ID' ) );

}

//add_filter( 'tabs', __NAMESPACE__ . '\add_tab', 10, 3 );
//
///**
// * @param array $tabs
// * @param array $groups
// * @param string $group_name
// */
//function add_tab( array $tabs, array $groups, string $group_name ) {
//
//	return array_merge( $groups, [ 'Groundhogg' ] );
//
//}


add_action( 'init', __NAMESPACE__ . '\add_tab' );


function add_tab() {

	global $bp;


	if ( bp_is_my_profile() && defined( 'GROUNDHOGG_ADVANCED_PREFERENCES_VERSION' ) ) {

		bp_core_new_subnav_item( array(
			'name'            => __( 'Preferences' ),
			'slug'            => 'manage-preference',
			'parent_url'      => $bp->loggedin_user->domain . $bp->profile->slug . '/',
			'parent_slug'     => $bp->profile->slug,
			'screen_function' => __NAMESPACE__ . '\display_preference_screen',
			'position'        => 40
		) );
	}

}

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
	echo __( "Manage Preferences" );
}

/**
 * Displays the con
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