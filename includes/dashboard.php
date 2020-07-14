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

			$plugin_stats = get_site_option( 'naa_plugin_stats' );

			echo '<div id="naa-dash-container">';
			echo '<section>';
			echo '<h2>' . __( 'Plugins', 'network-admin-assistant' ) . '</h2>';
			echo '<p class="naa-large">' . ( isset( $plugin_stats['installed'] ) ? $plugin_stats['installed'] : '?' ) . '</p>';
			echo '<p>';
			printf(
				esc_html__(	'%1$s network activated, %2$s not active on any site.', 'network-admin-assistant' ),
				'<strong>' . ( isset( $plugin_stats['network-activated'] ) ? $plugin_stats['network-activated'] : '?' ) . '</strong>',
				'<strong>' . ( isset( $plugin_stats['inactive'] ) ? $plugin_stats['inactive'] : '?' ) . '</strong>'
			);
			echo '</p>';
			echo '<a class="button" href="' . admin_url( 'network/admin.php?page=naa-plugin-stats' ) . '">' . __( 'Plugin statistics', 'network-admin-assistant' ) . '</a>';
			echo '<p><a href="' . admin_url( 'network/admin.php?page=naa-widget-stats' ) . '">' . __( 'Visit the Plugin Stats page to update these statistics.', 'network-admin-assistant' ) . '</a></p>';
			echo '</section>';

			echo '<section>';
			echo '<h2>' . __( 'Widgets', 'network-admin-assistant' ) . '</h2>';
			echo '<p class="naa-large">' . 56 . '</p>';
			echo '<a class="button" href="' . admin_url( 'network/admin.php?page=naa-widget-stats' ) . '">' . __( 'Widget statistics', 'network-admin-assistant' ) . '</a>';
			echo '</section>';
			echo '</div>';

			// wrap up
			echo '</p>';
			echo '</div>';
		}

	}

}

?>