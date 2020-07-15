<?php

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('NAA_Plugin_Stats') ){

	class NAA_Plugin_Stats {

		/**
		 * Set up hooks and filters.
		 */
		public function init() {
			// hook for the admin page
			add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
		}


		public function check_cache_expired() {
			// Get fresh stats if needed. This will also cache them and refresh the dashboard stats.
			$stats = $this->gather_stats( false );
		}


		/**
		 * Add a new options page to the network admin.
		 */
		public function admin_menu() {
			add_submenu_page(
				'naa-dashboard',
				__( 'Plugin Stats', 'network-admin-assistant' ),
				__( 'Plugin Stats', 'network-admin-assistant' ),
				'manage_options',
				'naa-plugin-stats',
				array( $this, 'settings_page' )
			);
		}


		/**
		 * Gather and store/return the plugin statistics.
		 */
		public function gather_stats( $refresh = false ){

			// Before we do any hard work, check if there's a cached version of the stats data.
			if( ! $refresh ){
				$cached_stats = get_site_transient( 'naa_plugin_data' );
				if( ! empty( $cached_stats ) ){
					return $cached_stats;
				}
			}

			// Start a timer to keep track of processing time.
			$starttime = microtime( true );

			// Create new arrays to keep the stats in.
			$active_plugins = array();
			$dashboard_stats = array();

			// Get a complete list of all plugins.
			// We'll remove active plugins from this to end up with the inactive ones.
			$installed_plugins = get_plugins();
			$dashboard_stats['installed'] = count( $installed_plugins );

			// Get network activated plugins.
			$network_plugins = get_site_option( 'active_sitewide_plugins', null );

			// Remove network activated plugins from our list of installed plugins.
			foreach( $network_plugins as $key => $value ){
				if( array_key_exists( $key, $installed_plugins ) ){
					unset( $installed_plugins[ $key ] );
				}
			}

			$dashboard_stats['network-activated'] = empty( $network_plugins ) ? 0 : count( $network_plugins );

			// Get all currently published sites.
			$args = array(
				'archived'   => 0,
				'mature'     => 0,
				'spam'       => 0,
				'deleted'    => 0,
				'number'      => 9999,
			);
			$sites = get_sites( $args );

			// Gather the data by looping through the sites and getting the active_plugins option.
			foreach( $sites as $site ){

				$plugins = get_blog_option( $site->blog_id, 'active_plugins', null );
			
				foreach( $plugins as $plugin ){
					if( !empty( $plugin ) ){
						// Exclude the plugin if it is (also) network-activated.
						if( ! array_key_exists( $plugin, $network_plugins ) ){
							// Clean up the php file path that WordPress stores to get a "semi-readable" name.
							$pluginname = $this->get_plugin_name( $plugin );
							// Make sure there's an array for this plugin.
							if( ! isset( $active_plugins[ $pluginname ] ) || ! is_array( $active_plugins[ $pluginname ] ) ){
								$active_plugins[$pluginname] = array();
							}
							// Add the instance's data to the array.
							$active_plugins[$pluginname][] = '<a href="' . $site->siteurl . '">' . $site->blogname . '</a> (<a href="' . esc_url( get_admin_url( $site->blog_id, 'plugins.php' ) ) . '">' . __( 'configure', 'network-admin-assistant' ) . ')</a>';
							// Remove this plugin from the list installed plugins
							if( array_key_exists( $plugin, $installed_plugins ) ){
								unset( $installed_plugins[ $plugin ] );
							}
						}
					}
				}
			}

			// Sort the results array alphabetically.
			ksort( $active_plugins );

			// Store the number of inactive plugins for display on the dashboard.
			$dashboard_stats['inactive'] = count( $installed_plugins );

			// Store the dashboard stats.
			update_site_option( 'naa_plugin_stats', $dashboard_stats );

			$stats = array(
				'processing_time'  => round( microtime( true ) - $starttime, 3 ),
				'network_plugins'  => $network_plugins,
				'active_plugins'   => $active_plugins,
				'inactive_plugins' => $installed_plugins,
				'site_count'       => count( $sites ),
				'timestamp'        => current_time( 'timestamp' ),
			);

			// Store the stats in a transient
			set_site_transient( 'naa_plugin_data', $stats, DAY_IN_SECONDS );
			
			return $stats;
		}


		/**
		 * Render the options page.
		 */
		public function settings_page() {

			// Get the statistics.
			$stats = $this->gather_stats( true );

			// Start the page's output.
			echo '<div class="wrap">';
			echo '<h1>' . __( 'Plugin Statistics', 'network-admin-assistant' ) . '</h1>';

			// Network activated plugins.
			echo '<h2>' . __( 'Network activated plugins', 'network-admin-assistant' ) . '</h2>';
			echo '<p>';
			if( !empty( $stats['network_plugins'] ) ){
				$this->render_network_activated_table( $stats['network_plugins'] );
			}
			echo '</p>';

			// Activated plugins.
			echo '<h2>' . __( 'Activated plugins', 'network-admin-assistant' ) . '</h2>';
			echo '<p>';
			$this->render_table( $stats['active_plugins'] );
			echo '</p>';

			// Inactive plugins.
			echo '<p>';
			echo '<h2>' . __( 'Inactive plugins', 'network-admin-assistant' ) . '</h2>';
			if( !empty( $stats['inactive_plugins'] ) ){
				$this->render_network_activated_table( $stats['inactive_plugins'] );
			} 
			echo '</p>';

			// Wrap up.
			echo '<p><em>';
			printf(
				__('Page render time: %1$s seconds, sites queried: %2$s', 'network-admin-assistant' ),
				$stats['processing_time'],
				$stats['site_count'],
			);
			echo '</em></p>';
			echo '</div>';
		}


		/**
		 * Gets passed the network activated plugins array, renders a nice HTML table.
		 */
		private function render_network_activated_table( $active_plugins ){
			$html = '<table class="widefat fixed" cellspacing="0">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Plugin name', 'network-admin-assistant' ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			$count = 0;

			foreach( $active_plugins as $name=>$inst ){
				$html .= '<tr' . ( ( $count % 2 == 0 ) ? ' class="alternate"' : '' ) . '>';
				$html .= '<td class="column-columnname"><strong>' . $this->get_plugin_name( $name ) . '</strong></td>';
				$html .= '</tr>';
				$count++;
			}

			$html .= '</tbody>';
			$html .= '</table>';

			echo $html;
		}


		/**
		 * Gets passed the results array, renders a nice HTML table.
		 */
		private function render_table( $active_plugins ){
			$html = '<table class="widefat fixed" cellspacing="0">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Plugin name', 'network-admin-assistant' ) . '</th>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Activation count', 'network-admin-assistant' ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			$count = 0;

			foreach( $active_plugins as $name=>$inst ){
				$html .= '<tr' . ( ( $count % 2 == 0 ) ? ' class="alternate"' : '' ) . '>';
				$html .= '<td class="column-columnname"><strong>' . $name . '</strong></td>';
				$html .= '<td class="column-columnname">';
				$html .= '<details>';
				$html .= '<summary>' . sprintf( esc_html__( 'Active on %d sites.', 'network-admin-assistant' ), count( $inst ) ) . '</summary>';
				$html .= '<ul>';
				foreach( $inst as $i ){
					$html .= '<li>' . $i . '</li>';
				}
				$html .= '</ul>';
				$html .= '</details>';
				$html .= '</td>';
				$html .= '</tr>';
				$count++;
			}

			$html .= '</tbody>';
			$html .= '</table>';

			echo $html;
		}


		/**
		 * Convert a plugins file's path into something readable.
		 */
		private function get_plugin_name( $path_str ){
			$r = $path_str;
			if( strpos( $path_str, '/' ) !== false ){
				$r = substr( $r, strrpos( $r, '/' )+1 );	
			}
			$r = str_replace( '.php', '', $r );
			return sanitize_title( $r );
		}

	}

}

?>