<?php

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('NAA_Dashboard') ){

	class NAA_Dashboard {

		/**
		 * Constructor
		 */
		public function init() {
			// hook for the admin page
			add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
		}


		/**
		 * Add a new options page to the network admin
		 */
		public function admin_menu() {
			// Add a top-level menu.
			add_menu_page(
				__( 'Network Admin Assistant', 'network-admin-assistant' ),
				__( 'Network Admin Assistant', 'network-admin-assistant' ),
				'manage_options',
				'naa-dashboard',
				array( $this, 'settings_page' ),
				'dashicons-chart-bar'
			);
		}


		/**
		 * Render the options page
		 */
		public function settings_page() {

			// start the page's output
			echo '<div class="wrap">';
			echo '<h1>' . __( 'Network Admin Assistant', 'network-admin-assistant' ) . '</h1>';
			echo '<p>';

			echo '<a href="' . admin_url( 'network/admin.php?page=naa-plugin-stats' ) . '">' . __( 'Plugin statistics', 'network-admin-assistant' ) . '</a>';
			echo '<br />';
			echo '<a href="' . admin_url( 'network/admin.php?page=naa-widget-stats' ) . '">' . __( 'Widget statistics', 'network-admin-assistant' ) . '</a>';
			
			// wrap up
			echo '</p>';
			echo '</div>';
		}

	}

}

?>