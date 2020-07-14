<?php
/*
	Plugin Name: Network Admin Assistant
	Plugin URI:  http://www.roytanck.com
	Description: Provides helpful information for WordPress multisite network administrators.
	Version:     1.0
	Author:      Roy Tanck
	Author URI:  http://www.roytanck.com
	Domain path: /languages
	License:     GPLv3
	Network:     true
*/

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('Network_Admin_Assistant') && is_multisite() ){

	class Network_Admin_Assistant {

		public function init(){
			// Load the plugin's dashboard class, and initialize it.
			require_once( 'includes/dashboard.php' );
			$dashboard = new NAA_Dashboard();
			$dashboard->init();
			// Load the plugin stats class, and initialize it.
			require_once( 'includes/plugin_stats.php' );
			$plugin_stats = new NAA_Plugin_Stats();
			$plugin_stats->init();
			// Load the plugin stats class, and initialize it.
			require_once( 'includes/widget_stats.php' );
			$widget_stats = new NAA_Widget_Stats();
			$widget_stats->init();
		}

	}

	// create an instance of the class
	$network_admin_assistant = new Network_Admin_Assistant();
	$network_admin_assistant->init();

}