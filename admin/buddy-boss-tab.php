<?php

namespace GroundhoggBuddyBoss\Admin;


use BP_XProfile_Data_Template;
use BP_XProfile_Group;
use Groundhogg\Admin\Contacts\Tab;

class Buddy_Boss_Tab extends Tab {

	public function get_id() {
		return 'buddy_boss';
	}

	public function get_name() {
		return __( 'BuddyBoss', 'groundhogg-buddyboss' );
	}


	public function content( $contact ) {

	    if (! $contact->get_user_id() ){
	        echo sprintf( "<p><h2> %s</h2></p>" ,__("This contact does not have any BuddyBoss data.", 'groundhogg-buddyboss'));
	        return;
        }

	    echo sprintf( "<p><a href='%s' class='button secondary'>%s</a></p>", admin_url( 'users.php?page=bp-profile-edit&user_id=' . $contact->get_user_id() ) , 'Edit Profile');


	    $profile_template = new BP_XProfile_Data_Template( [
			'user_id' => $contact->get_user_id(),
		] );

		foreach ( $profile_template->groups as $group ) {

			echo "<h2>{$group->name}</h2>";

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
			foreach ( $group_data[0]->fields as $field ) :
                ?>
                    <tr>
                        <th><label for="company_name"><?php echo _x( $field->name, 'groundhogg-buddyboss' ) ?></label></th>
                        <td><label for="company_name"><?php echo _x( $field->data->value, 'groundhogg-buddyboss' ) ?></label></td>
                    </tr>
				<?php
			endforeach;
			echo "</table>";

		}

	}
}