<?php

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('NAA_Widget_Stats') ){

	class NAA_Widget_Stats {

		/**
		 * Set up hooks and filters.
		 */
		public function init() {
			// hook for the admin page
			add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
		}


		/**
		 * Add a new options page to the network admin
		 */
		public function admin_menu() {
			add_submenu_page(
				'naa-dashboard',
				__( 'Widget Stats', 'network-admin-assistant' ),
				__( 'Widget Stats', 'network-admin-assistant' ),
				'manage_options',
				'naa-widget-stats',
				array( $this, 'settings_page' )
			);
		}


		public function check_cache_expired() {
			// Get fresh stats if needed. This will also cache them and refresh the dashboard stats.
			$stats = $this->gather_stats( false );
		}


		public function gather_stats( $refresh = false ){

			// Before we do any hard work, check if there's a cached version of the stats data.
			if( ! $refresh ){
				$cached_stats = get_site_transient( 'naa_widget_data' );
				if( ! empty( $cached_stats ) ){
					return $cached_stats;
				}
			}

			// start a timer to keep track of processing time
			$starttime = microtime( true );

			// create a new array to keep the stats in
			$active_widgets = array();
			$dashboard_stats = array();

			// get all currently published sites
			$args = array(
				'archived'   => 0,
				'mature'     => 0,
				'spam'       => 0,
				'deleted'    => 0,
				'number'      => 9999,
			);
			$sites = get_sites( $args );

			// gather the data by looping through the sites and getting the sidebars_widgets option
			foreach( $sites as $site ){
				
				$sidebars = get_blog_option( $site->blog_id, 'sidebars_widgets', null );
				
				foreach( $sidebars as $sidebarname=>$widgets ){
					if( !empty( $widgets ) && $this->is_valid_sidebar( $sidebarname ) ){
						foreach( $widgets as $widget_id ){
							// get the widget's id by chopping the end off the instance id
							$widgetname = $this->get_widget_name( $widget_id );
							// make sure there's an array for this type of widget
							if( !isset( $active_widgets[ $widgetname ] ) || !is_array( $active_widgets[ $widgetname ] ) ){
								$active_widgets[ $widgetname ] = array();
							}
							// add the instance's data to the array
							$active_widgets[ $widgetname ][] = '<a href="' . $site->siteurl . '">' . $site->blogname . '</a> (<a href="' . esc_url( get_admin_url( $site->blog_id , 'widgets.php' ) ) . '">' . __( 'configure', 'network-admin-assistant' ) . ')</a>' . ' <em>(' . $sidebarname . ')</em>';
						}
					}
				}
			}

			// sort the results array alphabetically
			ksort( $active_widgets );

			// Store the number of widgets that are active for use on the dashboard.
			$dashboard_stats['active'] = count( $active_widgets );

			// Store the dashboard stats.
			update_site_option( 'naa_widget_stats', $dashboard_stats );

			$stats = array(
				'processing_time' => round( microtime( true ) - $starttime, 3 ),
				'active_widgets'  => $active_widgets,
				'site_count'      => count( $sites ),
				'timestamp'       => current_time( 'timestamp' ),
			);

			// Store the stats in a transient
			set_site_transient( 'naa_widget_data', $stats, DAY_IN_SECONDS );

			return $stats;
		}


		/**
		 * Render the options page
		 */
		public function settings_page() {

			$stats = $this->gather_stats( true );

			// start the page's output
			echo '<div class="wrap">';
			echo '<h1>' . __( 'Widget Statistics', 'network-admin-assistant' ) . '</h1>';
			echo '<p>';

			// render the html table
			$this->render_table( $stats['active_widgets'] );
			
			// wrap up
			echo '</p>';
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
		 * Gets passed the results array, renders a nice HTML table
		 */
		private function render_table( $active_widgets ){
			$html = '<table class="widefat fixed" cellspacing="0">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Widget name', 'network-admin-assistant' ) . '</th>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Instance count', 'network-admin-assistant' ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			$count = 0;

			foreach( $active_widgets as $name=>$inst ){
				$html .= '<tr' . ( ( $count % 2 == 0 ) ? ' class="alternate"' : '' ) . '>';
				$html .= '<td class="column-columnname"><strong>' . $name . '</strong></td>';
				$html .= '<td class="column-columnname">';
				$html .= '<details>';
				$html .= '<summary>' . sprintf( esc_html__( 'Active on %d sidebars.', 'network-admin-assistant' ), count( $inst ) ) . '</summary>';
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
		 * Check sidebar names agains a couple the WP uses internally
		 */
		private function is_valid_sidebar( $name ){
			$reserved_names = array( 'wp_inactive_widgets', 'array_version', 'orphaned_widgets' );
			foreach( $reserved_names as $r ){
				if( substr( $name, 0, strlen( $r ) ) == $r ){
					return false;
				}
			}
			return true;
		}


		/**
		 * Strip the instance number from a widget id to get the "real" name
		 */
		private function get_widget_name( $id_str ){
			if( strpos( $id_str, '-' ) !== false ){
				return substr( $id_str, 0, strrpos( $id_str, '-' ) );	
			}
			return $id_str;
		}

	}

}

?>