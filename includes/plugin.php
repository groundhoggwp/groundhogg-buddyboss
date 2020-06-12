<?php

namespace GroundhoggBuddyBoss;

use Groundhogg\Extension;
use GroundhoggBuddyBoss\Admin\Buddy_Boss_Tab;
use GroundhoggBuddyBoss\Admin\Groundhogg_Bb_Groups;
use GroundhoggBuddyBoss\Admin\Groundhogg_Bb_Member_Types;

class Plugin extends Extension{


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
    public function includes()
    {
        require  GROUNDHOGG_BUDDY_BOSS_PATH . '/includes/functions.php';
    }

    /**
     * Init any components that need to be added.
     *
     * @return void
     */
    public function init_components()
    {
        new Groundhogg_Bb_Member_Types();
        new Groundhogg_Bb_Groups();

	    if ( is_admin() ) {
		    new Buddy_Boss_Tab();
	    }
    }

    /**
     * Get the ID number for the download in EDD Store
     *
     * @return int
     */
    public function get_download_id()
    {
        return 52477;
    }

    /**
     * Get the version #
     *
     * @return mixed
     */
    public function get_version()
    {
        return GROUNDHOGG_BUDDY_BOSS_VERSION;
    }

    /**
     * @return string
     */
    public function get_plugin_file()
    {
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
    protected function register_autoloader()
    {
        require GROUNDHOGG_BUDDY_BOSS_PATH . 'includes/autoloader.php';
        Autoloader::run();
    }
}

Plugin::instance();