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
			echo '<div id="naa-dash-container">';

			// Get the stored data for the plugins section.
			$plugin_stats = get_site_option( 'naa_plugin_stats' );

			// Render the plugins section.
			echo '<section>';
			echo '<h2>' . __( 'Plugins', 'network-admin-assistant' ) . '</h2>';
			echo '<p class="naa-large">' . ( isset( $plugin_stats['installed'] ) ? $plugin_stats['installed'] : '?' ) . '</p>';
			echo '<p class="naa-stats">';
			printf(
				esc_html__(	'%1$s network activated, %2$s not active on any site.', 'network-admin-assistant' ),
				'<strong>' . ( isset( $plugin_stats['network-activated'] ) ? $plugin_stats['network-activated'] : '?' ) . '</strong>',
				'<strong>' . ( isset( $plugin_stats['inactive'] ) ? $plugin_stats['inactive'] : '?' ) . '</strong>'
			);
			echo '</p>';
			echo '<a class="button" href="' . network_admin_url( 'admin.php?page=naa-plugin-stats' ) . '">' . __( 'Plugin statistics', 'network-admin-assistant' ) . '</a>';
			echo '<p><a href="' . network_admin_url( 'admin.php?page=naa-plugin-stats' ) . '">' . __( 'Visit the Plugin Stats page to update these statistics.', 'network-admin-assistant' ) . '</a></p>';
			echo '</section>';

			// Get the stored data for the widgets section.
			$widget_stats = get_site_option( 'naa_widget_stats' );

			// Render the widgets section.
			echo '<section>';
			echo '<h2>' . __( 'Widgets', 'network-admin-assistant' ) . '</h2>';
			echo '<p class="naa-large">' . ( isset( $widget_stats['active'] ) ? $widget_stats['active'] : '?' ) . '</p>';
			echo '<p class="naa-stats">';
			printf(
				esc_html__(	'%1$s widgets are current in use on at least one site.', 'network-admin-assistant' ),
				'<strong>' . ( isset( $widget_stats['active'] ) ? $widget_stats['active'] : '?' ) . '</strong>',
			);
			echo '</p>';
			echo '<a class="button" href="' . network_admin_url( 'admin.php?page=naa-widget-stats' ) . '">' . __( 'Widget statistics', 'network-admin-assistant' ) . '</a>';
			echo '<p><a href="' . network_admin_url( 'admin.php?page=naa-widget-stats' ) . '">' . __( 'Visit the Widget Stats page to update these statistics.', 'network-admin-assistant' ) . '</a></p>';
			echo '</section>';

			// Assemble data for the users section.
			$users = get_users(
				array(
					'blog_id' => 0,
					'fields'  => 'ID',
				)
			);
			// Initialize some counters.
			$users_without_role = 0;
			$network_admin_count = 0;
			// Loop through the users and coutn network admins and role-less users.
			foreach( $users as $user_id ){
				if( is_super_admin( $user_id ) ){
					$network_admin_count++;
				} else {
					$blogs = get_blogs_of_user( $user_id );
					if( empty( $blogs ) ){
						$users_without_role++;
					}
				}
			}

			// Render the users section.
			echo '<section>';
			echo '<h2>' . __( 'Users', 'network-admin-assistant' ) . '</h2>';
			echo '<p class="naa-large">' . ( isset( $users ) ? count( $users ) : '?' ) . '</p>';
			echo '<p class="naa-stats">';
			printf(
				esc_html__(	'%1$s users have no role on any site, %2$s network admin(s)', 'network-admin-assistant' ),
				'<strong>' . $users_without_role . '</strong>',
				'<strong>' . $network_admin_count . '</strong>',
			);
			echo '</p>';
			echo '<a class="button" href="' . network_admin_url( 'users.php' ) . '">' . __( 'Manage users', 'network-admin-assistant' ) . '</a>';
			echo '<p><a href="' . network_admin_url( 'users.php?naa_user_filter=naa_no_role' ) . '">' . __( 'View users with no role.', 'network-admin-assistant' ) . '</a></p>';
			echo '</section>';

			// wrap up
			echo '</div>';
			echo '</p>';
			echo '</div>';
		}

	}

}

?>