<?php

namespace GroundhoggBuddyBoss\Admin;

use BP_Groups_Member;
use BP_XProfile_Data_Template;
use BP_XProfile_Group;
use Groundhogg\Contact;

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
	 * @param Contact $contact
	 */
	public function callback( $contact ) {
		if ( ! $contact->get_user_id() ) {
			printf( "<p>%s</p>", __( "This contact does not have any BuddyBoss data.", 'groundhogg-buddyboss' ) );

			return;
		}

		echo "  <ul id='sortable'>";





		$profile_template = new BP_XProfile_Data_Template( [
			'user_id' => $contact->get_user_id(),
		] );

		foreach ( $profile_template->groups as $group ) :

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

			?>
            <ul class="container-info">
                <div class="header-info">
                    <div class="header-content">
                        <span class="dashicons dashicons-businessman"></span><?php _e( 'Profile', 'groundhogg-buddyboss' ); ?>
                    </div>
                    <i class="dashicons dashicons-arrow-down-alt2"></i>
                </div>
                <div class="content-info">
					<?php printf( "<p><a href='%s' class='button secondary'>%s</a></p>", admin_url( 'users.php?page=bp-profile-edit&user_id=' . $contact->get_user_id() ), __( 'Edit Profile', 'groundhogg-buddyboss' ) ); ?>
                    <ul>
						<?php foreach ( $group_data[0]->fields as $field ) : ?>
                            <li>
                                <div class="label"><?php _e( $field->name . ": ", 'groundhogg-buddyboss' ) ?></div>
                                <div class="content"><?php _e( $field->data->value, 'groundhogg-buddyboss' ) ?></div>
                            </li>
						<?php endforeach; ?>
                    </ul>
                </div>
            </ul>
		<?php

        endforeach;


        // Rendering the Groups

        if (class_exists( 'BP_Groups_Member' ) ) :



		$groups = BP_Groups_Member::get_group_ids( $contact->get_user_id() ) ['groups'];



		?>


        <ul class="container-info">
            <div class="header-info">
                <div class="header-content">
                    <span class="dashicons dashicons-groups"></span><?php _e( 'Groups', 'groundhogg-buddyboss' ); ?>
                </div>
                <i class="dashicons dashicons-arrow-down-alt2"></i>
            </div>
            <div class="content-info">

                <ul>
					<?php
                    if ($groups) :
                    foreach ( $groups as $group_id ) :

                       $group = groups_get_group($group_id);
                        ?>
                        <li>
                            <div class="label"><?php _e( $group->name , 'groundhogg-buddyboss' ) ?></div>
<!--                            <div class="content">--><?php //_e( $group->data->value, 'groundhogg-buddyboss' ) ?><!--</div>-->
                        </li>
					<?php endforeach;
					else:
						printf( "<p>%s</p>", __( "This contact is not part of any groups.", 'groundhogg-buddyboss' ) );
                    endif;

						?>
                </ul>
            </div>
        </ul>


		<?php

        endif;
		echo " </ul>";

	}
}
