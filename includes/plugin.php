<?php

namespace GroundhoggBuddyBoss;

use Groundhogg\Extension;
use GroundhoggBuddyBoss\Admin\Buddy_Boss_Info_Card;
use GroundhoggBuddyBoss\Admin\Buddy_Boss_Tab;
use GroundhoggBuddyBoss\Admin\Groundhogg_Bb_Groups;
use GroundhoggBuddyBoss\Admin\Groundhogg_Bb_Member_Types;
use GroundhoggBuddyBoss\Bulk_Jobs\Sync_Groups_And_Member_Types;

class Plugin extends Extension {


	/**
	 * Override the parent instance.
	 *
	 * @var Plugin
	 */
	public static $instance;

	/**
	 * Include any files.
	 *
	 * @return void
	 */
	public function includes() {
		require __DIR__ . '/functions.php';
	}

	/**
	 * Init any components that need to be added.
	 *
	 * @return void
	 */
	public function init_components() {


		new Groundhogg_Bb_Member_Types();
		new Groundhogg_Bb_Groups();

		$this->bb_replacement  = new Bb_Replacements();

		if ( is_admin() ) {
			new Buddy_Boss_Tab();
		}
	}

	/**
	 * Get the ID number for the download in EDD Store
	 *
	 * @return int
	 */
	public function get_download_id() {
		return 52477;
	}

	/**
	 * @param \Groundhogg\Admin\Contacts\Info_Cards $cards
	 */
	public function register_contact_info_cards( $cards ) {
//		$cards->e = new Buddy_Boss_Info_Card();
		new Buddy_Boss_Info_Card($cards);
	}


	public function register_bulk_jobs( $manager )
	{
		$manager->sync_groups_and_member_types = new Sync_Groups_And_Member_Types();
	}

	/**
	 * Get the version #
	 *
	 * @return mixed
	 */
	public function get_version() {
		return GROUNDHOGG_BUDDY_BOSS_VERSION;
	}

	public function get_dependent_plugins() {
		return [
			'buddyboss-platform/bp-loader.php' => __( 'BuddyBoss Platform' )
		];
	}

	/**
	 * @return string
	 */
	public function get_plugin_file() {
		return GROUNDHOGG_BUDDY_BOSS__FILE__;
	}




	/**
	 * Register autoloader.
	 *
	 * Groundhogg autoloader loads all the classes needed to run the plugin.
	 *
	 * @since 1.6.0
	 * @access private
	 */
	protected function register_autoloader() {
		require __DIR__ . '/autoloader.php';
		Autoloader::run();
	}
}

Plugin::instance();