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


if( ! class_exists('Network_Admin_Assistant') && is_multisite() ){

	class Network_Admin_Assistant {

		private $plugin_stats = null;
		private $widget_stats = null;

		public function init(){
			if( is_network_admin() ){
				// Load the plugin's dashboard class, and initialize it.
				require_once( 'includes/dashboard.php' );
				$dashboard = new NAA_Dashboard();
				$dashboard->init();
				// Load the plugin stats class, and initialize it.
				require_once( 'includes/plugin_stats.php' );
				$this->plugin_stats = new NAA_Plugin_Stats();
				$this->plugin_stats->init();
				// Load the plugin stats class, and initialize it.
				require_once( 'includes/widget_stats.php' );
				$this->widget_stats = new NAA_Widget_Stats();
				$this->widget_stats->init();
				// Load the user filters class, and initialize it.
				require_once( 'includes/user_filters.php' );
				$user_filters = new NAA_User_Filters();
				$user_filters->init();
				// Load the plugin's CSS file on the dashboard.
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
				// Refresh caches of needed.
				add_action( 'admin_init', array( $this, 'refresh_caches' ) );
			}
		}


		public function enqueue_styles( $hook ){
			// Check if we're on the right page.
			if ( 'toplevel_page_naa-dashboard' != $hook ) {
				return;
			}
			// Enqueue the stylesheet.
			wp_enqueue_style( 'naa_css', plugin_dir_url( __FILE__ ) . 'css/naa.css' );
		}


		public function refresh_caches(){
			if( is_network_admin() ){
				// Calling the check_cache_expired will refresh the cache only if it has expired.
				$this->plugin_stats->check_cache_expired();
				$this->widget_stats->check_cache_expired();
			}
		}

	}

	// create an instance of the class
	$network_admin_assistant = new Network_Admin_Assistant();
	$network_admin_assistant->init();

}