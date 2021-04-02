<?php
namespace GroundhoggBuddyBoss\Admin\Cards;
use function Groundhogg\dashicon_e;
use function Groundhogg\html;
use BP_XProfile_Data_Template;
use BP_XProfile_Group;
use BP_Groups_Member;

/**
 * @var $contact Contact
 */
$user_id = $contact->get_user_id();
$user    = get_user_to_edit( $user_id );

/**
* Render the last active date.
* @since BuddyPress 2.0.0
* @param WP_User|null $user The WP_User object to be edited.
*/
function user_last_active($user = null){
	// Bail if no user ID.
	if ( empty( $user->ID ) ) {
		return;
	}
	// If account is not activated last activity is the time user registered.
	if ( isset( $user->user_status ) && 2 == $user->user_status ) {
		$last_active = $user->user_registered;
		// Account is activated, getting user's last activity.
	} else {
		$last_active = bp_get_user_last_activity( $user->ID );
	}
	$date = date_i18n( bp_core_date_format( true ), strtotime( $last_active ) );
	return $date;
}
/**
 * @param int $user_id
 *
 * @return string
 */
function user_connections(int $user_id) {
	$r = bp_parse_args(
		array(
			'user_id' => $user_id,
			'friends' => 0,
			'output'  => '',
		),
		'friends_get_profile_stats'
	);
	return function_exists( 'friends_get_total_friend_count' ) ? absint( friends_get_total_friend_count( $r['user_id'] ) ) : 0;
}
/**
 * @param int $user_id
 * @return string
 */
function profile_type(int $user_id) {
	$types   = bp_get_member_types( array(), 'objects' );
	$member_type = bp_get_member_type( $user_id );
	return $types[$member_type]->labels['singular_name'];
}
/**
 * @param int $user_id
 * @return string
 */
function getuser_status(int $user_id) {
	$is_spammer = bp_is_user_spammer( $user_id );
	if ( !$is_spammer ):
		?><span class="green"><?php esc_html_e( 'Active', 'buddyboss' ); ?></span> <?php
	elseif (bp_moderation_is_user_suspended( $user->ID )):
		?><span class="red"><?php esc_html_e( 'Suspend', 'buddyboss' ); ?></span> <?php
	endif;
}
/**
 * @param int $member_id
 * @return string
 */
function member_url( int $member_id ) {
	return admin_url( 'users.php?page=bp-profile-edit&user_id=' . $member_id );
}
/**
 * @param int $group_id
 * @return string
 */
function group_link( int $group_id ) {
	$group = groups_get_group($group_id);
	return sprintf( '<a href="%s" target="_blank">%s</a>', esc_url(bp_get_group_permalink( $group)), __( $group->name, 'groundhogg-buddyboss' ) );
}
/**
 * @param int $user_id
 * @return string
 */
function user_groups(int $user_id) {
	// Parse the args.
	$r = bp_parse_args(
		array(
			'user_id' => $user_id,
			'friends' => 0,
			'output'  => '',
		),
		'friends_get_profile_stats'
	);
	return (bp_is_active( 'groups' ) && bp_get_total_group_count_for_user( $r['user_id'] ) ) ? absint( bp_get_total_group_count_for_user( $r['user_id'] ) ) : 0;
}
/**
 * @param string $type
 * @param string $data
 * @return string
 */
function profile_data($type , $data)
{
	switch ( $type ):
		case 'Date':
		$value = date_i18n( bp_core_date_format( true ), strtotime( $data ) );
		break;
		case 'Single Line Text':
		default:
		$value = $data;
		break;
	endswitch;
	return $value;
}
if ( empty( $user ) ) : ?>
	<p><?php _e( 'No Data Available.', 'groundhogg-buddyboss' ); ?></p>
<?php else: ?>
	<div class="buddyboss-infocard">
		<div class="ic-section open">
			<div class="ic-section-content">
				<table class="">
					<tbody>
					<tr>
						<th><?php _e( 'Member ID', 'groundhogg-buddyboss' ); ?></th>
						<td><a class="no-underline" href="<?php echo esc_url( member_url( $user->ID ) ); ?>"
						       target="_blank">#<?php echo $user->ID; ?></a></td>
					</tr>
					<tr>
						<th><?php _e( 'Last Active', 'groundhogg-buddyboss' ); ?></th>
						<td><?php echo user_last_active( $user ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Connections', 'groundhogg-buddyboss' ); ?></th>
						<td><?php echo user_connections($user->ID); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Groups', 'groundhogg-buddyboss' ); ?></th>
						<td><?php echo user_groups($user->ID); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Status', 'groundhogg-buddyboss' ); ?></th>
						<td><?php getuser_status($user->ID);//echo (bp_moderation_is_user_suspended( $user->ID )) ? 'Suspended' : 'Active';  ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Profile Type', 'groundhogg-buddyboss' ); ?></th>
						<td><?php echo profile_type($user->ID);  ?></td>
					</tr>					

					</tbody>
				</table>
			</div>
		</div>
		<div class="ic-section">
			<div class="ic-section-header">
				<div class="ic-section-header-content">
					<?php dashicon_e( 'admin-multisite' ); ?>
					<?php _e( 'Fields', 'groundhogg-buddyboss' ); ?>
				</div>
			</div>
			<div class="ic-section-content">
				<?php
				$profile_template = new BP_XProfile_Data_Template( [
					'user_id' => $user->ID,
				] );
				foreach ( $profile_template->groups as $group ):
				$group_data = BP_XProfile_Group::get( [
					'user_id'                => $contact->get_user_id(),
					'profile_group_id'       => $group->id,
					'member_type'            => 'any',
					'hide_empty_groups'      => false,
					'hide_empty_fields'      => false,
					'fetch_fields'           => true,
					'fetch_field_data'       => true,
					'fetch_visibility_level' => false,
					'exclude_groups'         => true,
					'exclude_fields'         => false,
					'include_fields'         => true,
					'update_meta_cache'      => true,
				] );
				if ( ! empty( $group_data[0] ) ) :
					echo '<table><tbody>';
					foreach ( $group_data[0]->fields as $field ): ?>
							<tr>
                                <th class="label"><?php _e( $field->name . ": ", 'groundhogg-buddyboss' ) ?></th>
                                <td class="content"><?php echo profile_data($field->type_obj->name, $field->data->value); ?></td>
                            </tr>
					<?php endforeach;
					echo '</tbody></table>'; ?>
				<?php else: _e( 'No Course Enrolled yet!!', 'groundhogg-buddyboss' ); endif; 
			 endforeach; ?>
			</div>
		</div>
		<?php
		if (class_exists( 'BP_Groups_Member' ) ) :
		$groups = BP_Groups_Member::get_group_ids( $user->ID ) ['groups']; ?>
		<div class="ic-section">
			<div class="ic-section-header">
				<div class="ic-section-header-content">
					<?php dashicon_e( 'groups' ); ?>
					<?php _e( 'Groups', 'groundhogg-buddyboss' ); ?>
				</div>
			</div>
			<div class="ic-section-content">
				<?php
				if ( ! empty( $groups ) ) :
					echo '<table><tbody>';
					foreach ( $groups as $group_id ): ?>
							<tr >
                                <td style="text-align:left;"><?php echo group_link($group_id) ?></td>
                            </tr>
					<?php endforeach;
					echo '</tbody></table>'; ?>
				<?php else: _e( 'No groups joined yet!!', 'groundhogg-buddyboss' ); endif; ?>
			</div>
		</div>
	<?php endif; ?>
	</div>
<?php endif;