<?php

namespace GroundhoggBuddyBoss;

use BP_XProfile_Field;
use Groundhogg\Contact;
use Groundhogg\Preferences;
use Groundhogg\Tag;
use function Groundhogg\generate_contact_with_map;
use function Groundhogg\get_array_var;
use function Groundhogg\get_contactdata;
use function Groundhogg\get_mappable_fields;
use function Groundhogg\get_request_var;
use function Groundhogg\html;
use function GroundhoggAdvancedPreferences\get_preference_tag_ids;

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

add_action( 'bp_notification_settings', __NAMESPACE__ . '\display_preference_screen', 1 );

/**
 * Show groundhogg preferences
 */
function display_preference_screen() {

	if ( ! function_exists( 'bp_is_my_profile' ) || ! bp_is_my_profile() || ! defined( 'GROUNDHOGG_ADVANCED_PREFERENCES_VERSION' ) ) {
		return;
	}

	$contact = get_contactdata();

	if ( ! $contact ) {
		return;
	}

	?>

    <table class="notification-settings" id="groundhogg-notification-settings">
        <thead>
        <tr>
            <th class="icon"></th>
            <th class="title"><?php _e( 'Marketing Messages', 'groundhogg-buddyboss' ); ?></th>
            <th class="yes"><?php _e( 'Yes', 'groundhogg-buddyboss' ); ?></th>
            <th class="no"><?php _e( 'No', 'groundhogg-buddyboss' ); ?></th>
        </tr>
        </thead>

        <tbody>

		<?php

		$contact = get_contactdata();

		$tag_ids = get_preference_tag_ids( $contact->get_id() );

		foreach ( $tag_ids as $tag_id ):

			$tag = new Tag( $tag_id );

			$label       = $tag->get_name();
			$class       = $tag->get_slug();
			$description = $tag->get_description();

			?>

            <tr id="groundhogg-notification-settings-<?php esc_attr_e( $class ); ?>">
                <td></td>
                <td>
					<?php _e( $label ); ?>
					<?php if ( $description ) : ?>
						<?php _e( ' - ' ); ?>
                        <i><?php _e( $description ); ?></i>
					<?php endif; ?>
                </td>
                <td class="yes">
                    <div class="bp-radio-wrap">
                        <input type="radio" name="<?php esc_attr_e( sprintf( 'tag_prefs[%d]', $tag_id ) ); ?>"
                               id="notification-messages-<?php esc_attr_e( $class ); ?>-yes" class="bs-styled-radio"
                               value="1" <?php checked( $contact->has_tag( $tag_id ), true, true ); ?> />
                        <label for="notification-messages-<?php esc_attr_e( $class ); ?>-yes"><span
                                    class="bp-screen-reader-text"><?php _e( 'Yes, send email', 'buddyboss' ); ?></span></label>
                    </div>
                </td>
                <td class="no">
                    <div class="bp-radio-wrap">
                        <input type="radio" name="<?php esc_attr_e( sprintf( 'tag_prefs[%d]', $tag_id ) ); ?>"
                               id="notification-messages-<?php esc_attr_e( $class ); ?>-no" class="bs-styled-radio"
                               value="0" <?php checked( $contact->has_tag( $tag_id ), false, true ); ?> />
                        <label for="notification-messages-<?php esc_attr_e( $class ); ?>-no"><span
                                    class="bp-screen-reader-text"><?php _e( 'No, do not send email', 'buddyboss' ); ?></span></label>
                    </div>
                </td>
            </tr>

		<?php

		endforeach;

		/**
		 * Fires inside the closing </tbody> tag for messages screen notification settings.
		 *
		 * @since BuddyPress 1.0.0
		 */
		do_action( 'groundhogg_buddyboss_marketing_notifications_screen' );
		?>
        </tbody>
    </table>

	<?php

}

add_action( 'bp_core_notification_settings_after_save', __NAMESPACE__ . '\save_tag_preferences' );

/**
 * Save the tag preferences!
 */
function save_tag_preferences() {

	$contact = get_contactdata();

	if ( ! $contact || ! defined( 'GROUNDHOGG_ADVANCED_PREFERENCES_VERSION' ) ) {
		return;
	}

	$all_tags           = get_preference_tag_ids( $contact->get_id() ); // PB: Added parameter to updated function
	$passed_preferences = array_filter( get_request_var( 'tag_prefs', [] ) );

	if ( ! empty( $all_tags ) ) {
		$tag_prefs = wp_parse_id_list( array_keys( $passed_preferences ) );

		$remove_tags = array_values( array_diff( $all_tags, $tag_prefs ) );
		$add_tags    = array_values( array_intersect( $all_tags, $tag_prefs ) );

		$contact->remove_tag( $remove_tags );
		$contact->add_tag( $add_tags );
	}
}

add_filter( 'groundhogg/contact/profile_picture', __NAMESPACE__ . '\set_profile_picture_from_bp', 10, 3 );

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

	if ( $avatar ) {
		return $avatar;
	}

	return $profile_pic;
}

function confirm_user_email_when_user_gets_activated( $user_id, $key, $user ) {
	$contact = new Contact( $user_id, true );
	if ( $contact->exists() ) {
		$contact->change_marketing_preference( Preferences::CONFIRMED );
	}
}

add_action( 'bp_core_activated_user', __NAMESPACE__ . '\confirm_user_email_when_user_gets_activated', 10, 3 );

/**
 *  ###########  TOOLS  ###########
 */
/**
 * Displays Validate email button inside tools page of Groundhogg
 *
 * @param $page
 */
function display_settings( $page ) {
	?>
    <div class="show-upload-view">
        <div class="upload-plugin-wrap">
            <div class="upload-plugin">
                <p class="install-help"><?php _e( 'Buddy Boss', 'groundhogg-buddyboss' ); ?></p>
                <form method="post" class="gh-tools-box">
					<?php wp_nonce_field(); ?>
					<?php echo \Groundhogg\Plugin::$instance->utils->html->input( [
						'type'  => 'hidden',
						'name'  => 'action',
						'value' => 'bb_sync_groups_and_member_types',
					] ); ?>
                    <p><?php _e( 'Sync group and profile type tags based on the current settings. This will add any missing tags to contacts based on their currently active groups and profile type. Tags with associated groups the contact does not belong to will be removed, as will any profile type tags.', 'groundhogg-buddyboss' ); ?></p>
                    <p class="submit" style="text-align: center;padding-bottom: 0;margin: 0;">
                        <button class="button-primary big-button" name="validate_contacts"
                                value="sync"><?php _ex( 'Start Sync', 'action', 'groundhogg-buddyboss' ); ?></button>
                    </p>
                </form>

            </div>
        </div>
    </div>


	<?php
}

add_action( 'groundhogg/admin/gh_tools/display/buddyboss_view', __NAMESPACE__ . '\display_settings', 10 );


/**
 * code to start the bulk job
 */

/**
 * Start's the bulk from the tools page
 *
 * @return mixed
 */
function sync_groups_and_member_types( $exitcode ) {

	\Groundhogg\Plugin::$instance->bulk_jobs->sync_groups_and_member_types->start();

	return $exitcode;
}

add_filter( 'groundhogg/admin/gh_tools/process/bb_sync_groups_and_member_types', __NAMESPACE__ . '\sync_groups_and_member_types', 10 );


/**
 * Adds new tab inside Groundhogg Tools page
 *
 * @param $tags
 *
 * @return array
 */
function tools_tab( $tags ) {
	$tags [] = [
		'name' => __( 'Buddy Boss' ),
		'slug' => 'buddyboss'
	];

	return $tags;
}

add_filter( 'groundhogg/admin/tools/tabs', __NAMESPACE__ . '\tools_tab', 10 );


function create_contact_using_buddyboss() {

	$field_map = array_merge( (array) get_option( 'gh_bb_field_map' ), [ 'signup_email' => 'email' ] );

	generate_contact_with_map( $_POST, $field_map );
}

add_action( 'bp_complete_signup', __NAMESPACE__ . '\create_contact_using_buddyboss', 10 );


/**
 * Adds section inside Buddyboss fields to set
 *
 * @param $field BP_XProfile_Field
 */
function add_groundhogg_field_picker( $field ) {
	?>
    <div class="postbox">
        <h2>
            <label for="default-visibility"><?php esc_html_e( 'Groundhogg Field Map', 'groundhogg-buddyboss' ); ?></label>
        </h2>
        <div class="inside">
            <div>
				<?php echo html()->dropdown( [
					'option_none' => '* Do Not Map *',
					'options'     => get_mappable_fields(),
					'selected'    => get_array_var( get_option( 'gh_bb_field_map' ), 'field_' . $field->id, '' ),
					'name'        => 'gh_field_map',
				] ) ?>
            </div>
        </div>
    </div>
	<?php
}

add_action( 'xprofile_field_after_sidebarbox', __NAMESPACE__ . '\add_groundhogg_field_picker', 10 );

/**
 * saves the field map into the custom meta
 *
 * @param $field BP_XProfile_Field
 */
function save_bb_map( $field ) {

	if ( get_request_var( 'gh_field_map' ) ) {
		$field_map                           = get_option( 'gh_bb_field_map' );
		$field_map [ 'field_' . $field->id ] = get_request_var( 'gh_field_map' );
		update_option( 'gh_bb_field_map', $field_map );
	}
}

add_action( 'xprofile_fields_saved_field', __NAMESPACE__ . '\save_bb_map', 10 );