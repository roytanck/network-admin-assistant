<?php

// If called without WordPress, exit.
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('NAA_Theme_Stats') ){

	class NAA_Theme_Stats {

		/**
		 * Set up hooks and filters.
		 */
		public function init() {
			// Add the network admin page.
			add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
		}


		/**
		 * Check if there are cached stats, refresh if not.
		 */
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
				__( 'Theme Stats', 'network-admin-assistant' ),
				__( 'Theme Stats', 'network-admin-assistant' ),
				'manage_options',
				'naa-theme-stats',
				array( $this, 'settings_page' )
			);
		}


		/**
		 * Gather and store/return the plugin statistics.
		 */
		public function gather_stats( $refresh = false ){

			// Before we do any hard work, check if there's a cached version of the stats data.
			if( ! $refresh ){
				$cached_stats = get_site_transient( 'naa_theme_data' );
				if( ! empty( $cached_stats ) ){
					return $cached_stats;
				}
			}

			// Start a timer to keep track of processing time.
			$starttime = microtime( true );

			// Create new arrays to keep the stats in.
			$active_themes = array();
			$parent_themes = array();
			$dashboard_stats = array();

			// Get a complete list of all themes.
			// We'll remove active themes from this to end up with the inactive ones.
			$installed_themes = wp_get_themes();
			$dashboard_stats['installed'] = count( $installed_themes );

			// Get all currently published sites.
			$args = array(
				'archived'   => 0,
				'mature'     => 0,
				'spam'       => 0,
				'deleted'    => 0,
				'number'     => 9999,
			);
			$sites = get_sites( $args );

			// Gather the data by looping through the sites and getting the stylesheet option.
			foreach( $sites as $site ){

				$theme = Network_Admin_Assistant::naa_get_blog_option( $site->blog_id, 'stylesheet', null );

				// Check if this is a theme we've not enountered before.
				if( ! array_key_exists( $theme, $active_themes ) ){
					// Add it to the array of active themes.
					$active_themes[ $theme ] = array();
					// If it exists in the array of installed themes (should always be true).
					if( array_key_exists( $theme, $installed_themes ) ){
						// If it has a parent, add that parent's name to our array of parent themes being used.
						if( $installed_themes[ $theme ]->parent() ){
							$parent_name = $installed_themes[ $theme ]->parent()->stylesheet;
							if( ! array_key_exists( $parent_name, $parent_themes ) ){
								$parent_themes[ $parent_name ] = array();
							}
							$parent_themes[ $parent_name ][] = $theme;
						}
					}
				}

				$active_themes[ $theme ][] = array(
					'siteurl'  => $site->siteurl,
					'blogname' => $site->blogname,
					'url'      => esc_url( get_admin_url( $site->blog_id, 'themes.php' ) ),
				);

				// Remove this theme from the list of installed themes.
				if( array_key_exists( $theme, $installed_themes ) ){
					unset( $installed_themes[ $theme ] );
				}

			}

			// Sort the results array alphabetically.
			ksort( $active_themes );

			// Remove parent themes of active themes from the 'inactive' array to avoid accidental deletion.
			foreach( $parent_themes as $parent_theme => $children ){
				if( array_key_exists( $parent_theme, $installed_themes ) ){
					unset( $installed_themes[ $parent_theme ] );
				}
			}

			// Store the number of inactive and parent themes for display on the dashboard.
			$dashboard_stats['inactive']      = count( $installed_themes );
			$dashboard_stats['parent-themes'] = count( $parent_themes );

			// Store the dashboard stats.
			update_site_option( 'NAA_Theme_Stats', $dashboard_stats );

			// Assemble a nice array of stats to cache.
			$stats = array(
				'processing_time'  => round( microtime( true ) - $starttime, 3 ),
				'active_themes'    => $active_themes,
				'inactive_themes'  => array_keys( $installed_themes ),
				'parent_themes'    => $parent_themes,
				'site_count'       => count( $sites ),
				'timestamp'        => current_time( 'timestamp' ),
			);

			// Store the dashboard stats in a transient
			set_site_transient( 'naa_theme_data', $stats, DAY_IN_SECONDS );
			
			return $stats;
		}


		/**
		 * Render the options page.
		 */
		public function settings_page() {

			// Get the statistics.
			$stats = $this->gather_stats( false );

			// If the refresh parameter is on the URL, and it matches the current stats timestamp, het fresh stats.
			if( isset( $_GET['naa_refresh'] ) && (int) $_GET['naa_refresh'] == $stats['timestamp'] ){
				$stats = $this->gather_stats( true );
			}

			// Start the page's output.
			echo '<div class="wrap">';
			echo '<h1>' . __( 'Theme Statistics', 'network-admin-assistant' ) . '</h1>';

			// Provide some info about caching.
			echo '<p>';
			if( isset( $stats['timestamp'] ) ){
				echo sprintf( __( 'Data cached at %s.', 'network-admin-assistant' ), date_i18n( get_option('date_format') . ' - ' . get_option('time_format'), $stats['timestamp'] ) );
			} else {
				echo __( 'No cached data available.', 'network-admin-assistant' );
			}
			echo ' <a href="' . add_query_arg(  'naa_refresh', $stats['timestamp'] ) . '">' . __( 'Click here to refresh.', 'network-admin-assistant' ) . '</a>';
			echo '</p>';

			// Activated themes.
			echo '<h2>' . __( 'Activated themes', 'network-admin-assistant' ) . ' (' . count( $stats['active_themes'] ) . ')</h2>';
			echo '<p>';
			$this->render_table( $stats['active_themes'] );
			echo '</p>';

			// Parent themes.
			echo '<h2>' . __( 'Parent themes used by currently active themes', 'network-admin-assistant' ) . ' (' . count( $stats['parent_themes'] ) . ')</h2>';
			echo '<p>';
			if( !empty( $stats['inactive_themes'] ) ){
				$this->render_parents_table( $stats['parent_themes'] );
			}
			echo '</p>';

			// Inactive themes.
			echo '<h2>' . __( 'Inactive themes', 'network-admin-assistant' ) . ' (' . count( $stats['inactive_themes'] ) . ')</h2>';
			echo '<p>';
			if( !empty( $stats['inactive_themes'] ) ){
				$this->render_simple_table( $stats['inactive_themes'] );
			}
			echo '</p>';

			// Wrap up.
			echo '<p><em>';
			printf(
				__('Page render time: %1$s seconds, sites queried: %2$s', 'network-admin-assistant' ),
				$stats['processing_time'],
				$stats['site_count']
			);
			echo '</em></p>';
			echo '</div>';
		}


		/**
		 * Gets passed the network activated or inactive themes array, renders a nice HTML table.
		 */
		private function render_simple_table( $active_plugins ){
			$html = '<table class="widefat fixed" cellspacing="0">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Plugin name', 'network-admin-assistant' ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			$count = 0;

			foreach( $active_plugins as $key=>$name ){
				$html .= '<tr' . ( ( $count % 2 == 0 ) ? ' class="alternate"' : '' ) . '>';
				$html .= '<td class="column-columnname"><strong>' . $name . '</strong></td>';
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
			$html .= '<th class="manage-column column-columnname">' . __( 'Theme name', 'network-admin-assistant' ) . '</th>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Activation count', 'network-admin-assistant' ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			$count = 0;

			foreach( $active_plugins as $name => $activations ){
				$html .= '<tr' . ( ( $count % 2 == 0 ) ? ' class="alternate"' : '' ) . '>';
				$html .= '<td class="column-columnname"><strong>' . $name . '</strong></td>';
				$html .= '<td class="column-columnname">';
				$html .= '<details>';
				$html .= '<summary>' . sprintf( esc_html__( 'Active on %d sites.', 'network-admin-assistant' ), count( $activations ) ) . '</summary>';
				$html .= '<ul>';
				foreach( $activations as $a ){
					$html .= '<li><a href="' . $a['siteurl'] . '">' . $a['blogname'] . '</a> (<a href="' . $a['url'] . '">' . __( 'configure', 'network-admin-assistant' ) . ')</a></li>';
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
		 * Gets passed the parent array, renders a nice HTML table.
		 */
		private function render_parents_table( $parent_plugins ){
			$html = '<table class="widefat fixed" cellspacing="0">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Theme name', 'network-admin-assistant' ) . '</th>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Child themes', 'network-admin-assistant' ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			$count = 0;

			foreach( $parent_plugins as $name => $children ){
				$html .= '<tr' . ( ( $count % 2 == 0 ) ? ' class="alternate"' : '' ) . '>';
				$html .= '<td class="column-columnname"><strong>' . $name . '</strong></td>';
				$html .= '<td class="column-columnname">';
				$html .= '<details>';
				$html .= '<summary>' . sprintf( esc_html__( '%d activated child themes.', 'network-admin-assistant' ), count( $parent_plugins ) ) . '</summary>';
				$html .= '<ul>';
				foreach( $children as $c ){
					$html .= '<li>' . $c . '</li>';
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

	}

}

?>