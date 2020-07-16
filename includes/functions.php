<?php

namespace GroundhoggBuddyBoss;

use Groundhogg\Contact;
use Groundhogg\Preferences;
use Groundhogg\Tag;
use function Groundhogg\get_contactdata;
use function Groundhogg\get_request_var;
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