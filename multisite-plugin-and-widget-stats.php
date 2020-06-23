<?php
/*
	Plugin Name: Multisite Plugin and Widget Stats
	Plugin URI:  http://www.roytanck.com
	Description: Keep track of plugin and widget usage across a WordPress multisite network.
	Version:     1.0
	Author:      Roy Tanck
	Author URI:  http://www.roytanck.com
	Domain path: /languages
	License:     GPL
	Network:     true
*/

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('Multsite_Plugin_And_Widget_Stats') && is_multisite() ){

	class Multsite_Plugin_And_Widget_Stats {

		public function init(){
			// Load the plugin stats class, and initialize it.
			require_once( 'includes/plugin_stats.php' );
			$plugin_stats = new MPWS_Plugin_Stats();
			$plugin_stats->init();
			// Load the plugin stats class, and initialize it.
			require_once( 'includes/widget_stats.php' );
			$widget_stats = new MPWS_Widget_Stats();
			$widget_stats->init();
		}

	}

	// create an instance of the class
	$multsite_plugin_and_widget_stats = new Multsite_Plugin_And_Widget_Stats();
	$multsite_plugin_and_widget_stats->init();

}