<?php

namespace GroundhoggBuddyBoss\Admin;

use BP_XProfile_Data_Template;
use BP_XProfile_Group;

class Buddy_Boss_Info_Card {

	/**
	 * Buddy_Boss_Info_Card constructor.
	 *
	 * @param \Groundhogg\Admin\Contacts\Info_Cards $cards
	 */
	public function __construct( $cards ) {
		$cards::register( 'buddyboss', __( 'BuddyBoss' ), [ $this, 'callback' ] );
	}

	/**
	 * Contact
	 *
	 * @param $contact
	 */
	public function callback( $contact ) {
		if ( ! $contact->get_user_id() ) {
			printf( "<p>%s</p>", __( "This contact does not have any BuddyBoss data.", 'groundhogg-buddyboss' ) );

			return;
		}

		printf( "<p><a href='%s' class='button secondary'>%s</a></p>", admin_url( 'users.php?page=bp-profile-edit&user_id=' . $contact->get_user_id() ), __( 'Edit Profile', 'groundhogg-buddyboss' ) );

		$profile_template = new BP_XProfile_Data_Template( [
			'user_id' => $contact->get_user_id(),
		] );

		foreach ( $profile_template->groups as $group ) {

			echo "<h3>{$group->name}</h3>";

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

			echo " <table class=\"form-table\">";
//                echo "<div class='content-info'>";
//                echo "<ul>";
			foreach ( $group_data[0]->fields as $field ) :
				?>
                <tr>
                    <th><label><?php _e( $field->name, 'groundhogg-buddyboss' ) ?></label></th>
                    <td><code><?php _e( $field->data->value, 'groundhogg-buddyboss' ) ?></code></td>
                </tr>

                <!---->
                <!---->
                <!--                    <li>-->
                <!---->
                <!--                        <div class="label">--><?php //_e( $field->name . ": ", 'groundhogg-buddyboss' )
				?><!--</div>-->
                <!---->
                <!--                        <div class="content">--><?php //_e( $field->data->value, 'groundhogg-buddyboss' )
				?><!--</div>-->
                <!---->
                <!--                    </li>-->
                <!---->

			<?php
			endforeach;
			echo "</table>";
//				echo "</ul>";
//				echo "</div>";

		}
	}
}
