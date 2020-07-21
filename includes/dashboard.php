<?php

// If called without WordPress, exit.
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('NAA_Dashboard') ){

	class NAA_Dashboard {

		/**
		 * Set up hooks.
		 */
		public function init() {
			// Hook for adding the admin page.
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
		 * Render the options page.
		 */
		public function settings_page() {
			// Start the page's output.
			echo '<div class="wrap">';
			echo '<h1>' . __( 'Network Admin Assistant', 'network-admin-assistant' ) . '</h1>';
			echo '<p>';
			echo '<div id="naa-dash-container">';
			// Render the sections.
			$this->render_plugins_section();
			$this->render_widgets_section();
			$this->render_themes_section();
			$this->render_users_section();
			// Wrap up.
			echo '</div>';
			echo '</p>';
			echo '</div>';
		}


		/**
		 * Render the plugins box on the dashboard.
		 */
		private function render_plugins_section(){
			// Get the stored data for the plugins section.
			$plugin_stats = get_site_option( 'naa_plugin_stats' );
			$cached_stats = get_site_transient( 'naa_plugin_data' );
			// Render the plugins section.
			echo '<section>';
			echo '<h2>' . __( 'Plugins', 'network-admin-assistant' ) . '</h2>';
			echo '<p class="naa-large">' . ( isset( $plugin_stats['installed'] ) ? $plugin_stats['installed'] : '?' ) . '</p>';
			echo '<p class="naa-stats">';
			// Create a nice summary with some useful info by first creating parts.
			$messages = array();
			$messages[] = sprintf(
				wp_kses(
					/* translators: Number of network-activated users. */
					_n(
						'<strong>%d</strong> is network activated',
						'<strong>%d</strong> are network activated',
						(int) $plugin_stats['network-activated'],
						'network-admin-assistant'
					),
					array( 'strong' => array() )
				),
				number_format_i18n( (int) $plugin_stats['network-activated'] )
			);
			$messages[] = sprintf(
				wp_kses(
					/* translators: Number of inactive plugins. */
					_n(
						'<strong>%d</strong> is not active on any site',
						'<strong>%d</strong> are not active on any site',
						(int) $plugin_stats['inactive'],
						'network-admin-assistant'
					),
					array( 'strong' => array() )
				),
				number_format_i18n( (int) $plugin_stats['inactive'] )
			);
			// Echo the parts glued together.
			echo implode( ', ', $messages );
			echo '</p>';
			// Button to go to the plugins screen.
			echo '<a class="button" href="' . network_admin_url( 'admin.php?page=naa-plugin-stats' ) . '">' . __( 'Plugin statistics', 'network-admin-assistant' ) . '</a>';
			// Caching info.
			echo '<p>';
			if( isset( $cached_stats['timestamp'] ) ){
				echo sprintf( __( 'Last updated at %s.', 'network-admin-assistant' ), date_i18n( get_option('date_format') . ' - ' . get_option('time_format'), $cached_stats['timestamp'] ) );
			} else {
				echo __( 'No cached data available.', 'network-admin-assistant' );
			}
			echo '<br />';
			echo '<a href="' . network_admin_url( 'admin.php?page=naa-plugin-stats' ) . '">' . __( 'Visit the Plugin Stats page to update these statistics.', 'network-admin-assistant' ) . '</a>';
			echo '</p>';
			// Wrap up.
			echo '</section>';
		}


		/**
		 * Render the widgets box on the dashboard.
		 */
		private function render_widgets_section(){
			// Get the stored data for the widgets section.
			$widget_stats = get_site_option( 'naa_widget_stats' );
			$cached_stats = get_site_transient( 'naa_widget_data' );
			// Render the widgets section.
			echo '<section>';
			echo '<h2>' . __( 'Widgets', 'network-admin-assistant' ) . '</h2>';
			echo '<p class="naa-large">' . ( isset( $widget_stats['active'] ) ? $widget_stats['active'] : '?' ) . '</p>';
			echo '<p class="naa-stats">';
			printf(
				wp_kses(
					/* translators: Number of sites a widget is used on. */
					_n(
						'<strong>%d</strong> widget is currently in use on at least one site.',
						'<strong>%d</strong> widgets are currently in use on at least one site.',
						(int) $widget_stats['active'],
						'network-admin-assistant'
					),
					array( 'strong' => array() )
				),
				number_format_i18n( (int) $widget_stats['active'] )
			);
			echo '</p>';
			// Button to go to the widgets screen.
			echo '<a class="button" href="' . network_admin_url( 'admin.php?page=naa-widget-stats' ) . '">' . __( 'Widget statistics', 'network-admin-assistant' ) . '</a>';
			// Caching info.
			echo '<p>';
			if( isset( $cached_stats['timestamp'] ) ){
				echo sprintf( __( 'Last updated at %s.', 'network-admin-assistant' ), date_i18n( get_option('date_format') . ' - ' . get_option('time_format'), $cached_stats['timestamp'] ) );
			} else {
				echo __( 'No cached data available.', 'network-admin-assistant' );
			}
			echo '<br />';
			echo '<a href="' . network_admin_url( 'admin.php?page=naa-widget-stats' ) . '">' . __( 'Visit the Widget Stats page to update these statistics.', 'network-admin-assistant' ) . '</a>';
			echo '</p>';
			// Wrap up.
			echo '</section>';
		}


		/**
		 * Render the themes box on the dashboard.
		 */
		private function render_themes_section(){
			// Get the stored data for the themes section.
			$plugin_stats = get_site_option( 'naa_theme_stats' );
			$cached_stats = get_site_transient( 'naa_theme_data' );
			// Render the themes section.
			echo '<section>';
			echo '<h2>' . __( 'Themes', 'network-admin-assistant' ) . '</h2>';
			echo '<p class="naa-large">' . ( isset( $plugin_stats['installed'] ) ? $plugin_stats['installed'] : '?' ) . '</p>';
			echo '<p class="naa-stats">';
			// Create a nice summary with some useful info by first creating parts.
			$messages = array();
			$messages[] = sprintf(
				wp_kses(
					/* translators: Number of parent themes in use. */
					_n(
						'<strong>%d</strong> parent theme is in use',
						'<strong>%d</strong> parent themes are in use.',
						(int) $plugin_stats['parent-themes'],
						'network-admin-assistant'
					),
					array( 'strong' => array() )
				),
				number_format_i18n( (int) $plugin_stats['parent-themes'] )
			);
			$messages[] = sprintf(
				wp_kses(
					/* translators: Number of inactive themes. */
					_n(
						'<strong>%d</strong> theme is not active on any site',
						'<strong>%d</strong> themes are not active on any site',
						(int) $plugin_stats['inactive'],
						'network-admin-assistant'
					),
					array( 'strong' => array() )
				),
				number_format_i18n( (int) $plugin_stats['inactive'] )
			);
			// Echo the parts glued together.
			echo implode( ', ', $messages );
			echo '</p>';
			// Button to go to the themes screen.
			echo '<a class="button" href="' . network_admin_url( 'admin.php?page=naa-theme-stats' ) . '">' . __( 'Theme statistics', 'network-admin-assistant' ) . '</a>';
			// Caching info.
			echo '<p>';
			if( isset( $cached_stats['timestamp'] ) ){
				echo sprintf( __( 'Last updated at %s.', 'network-admin-assistant' ), date_i18n( get_option('date_format') . ' - ' . get_option('time_format'), $cached_stats['timestamp'] ) );
			} else {
				echo __( 'No cached data available.', 'network-admin-assistant' );
			}
			echo '<br />';
			echo '<a href="' . network_admin_url( 'admin.php?page=naa-theme-stats' ) . '">' . __( 'Visit the Theme Stats page to update these statistics.', 'network-admin-assistant' ) . '</a>';
			echo '</p>';
			// Wrap up.
			echo '</section>';
		}


		/**
		 * Render the users box on the dashboard.
		 */
		private function render_users_section(){
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
			// Create a nice summary with some useful info by first creating parts.
			$messages = array();
			$messages[] = sprintf(
				wp_kses(
					/* translators: Number of users with no role. */
					_n(
						'<strong>%s</strong> user has no role on any site',
						'<strong>%s</strong> users have no role on any site',
						$users_without_role,
						'network-admin-assistant'
					),
					array( 'strong' => array() )
				),
				number_format_i18n( $users_without_role )
			);
			$messages[] = sprintf(
				wp_kses(
					/* translators: Number of network admin users. */
					_n(
						'there is <strong>%d</strong> network admin',
						'there are <strong>%d</strong> network admins',
						$network_admin_count,
						'network-admin-assistant'
					),
					array( 'strong' => array() )
				),
				number_format_i18n( $network_admin_count )
			);
			// Echo the parts glued together.
			echo implode( ', ', $messages );
			// Wrap up.
			echo '</p>';
			echo '<a class="button" href="' . network_admin_url( 'users.php' ) . '">' . __( 'Manage users', 'network-admin-assistant' ) . '</a>';
			echo '<p><a href="' . network_admin_url( 'users.php?naa_user_filter=naa_no_role' ) . '">' . __( 'View users with no role.', 'network-admin-assistant' ) . '</a></p>';
			echo '</section>';
		}

	}

}

?>