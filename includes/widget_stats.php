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


		/**
		 * Render the options page
		 */
		public function settings_page() {

			// start a timer to keep track of processing time
			$starttime = microtime( true );

			// create a new array to keep the stats in
			$results = array();
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

			// start the page's output
			echo '<div class="wrap">';
			echo '<h1>' . __( 'Widget Statistics', 'network-admin-assistant' ) . '</h1>';
			echo '<p>';

			// gather the data by looping through the sites and getting the sidebars_widgets option
			foreach( $sites as $site ){
				
				$sidebars = get_blog_option( $site->blog_id, 'sidebars_widgets', null );
				
				foreach( $sidebars as $sidebarname=>$widgets ){
					if( !empty( $widgets ) && $this->is_valid_sidebar( $sidebarname ) ){
						foreach( $widgets as $widget_id ){
							// get the widget's id by chopping the end off the instance id
							$widgetname = $this->get_widget_name( $widget_id );
							// make sure there's an array for this type of widget
							if( !isset( $results[ $widgetname ] ) || !is_array( $results[ $widgetname ] ) ){
								$results[ $widgetname ] = array();
							}
							// add the instance's data to the array
							$results[ $widgetname ][] = '<a href="' . $site->siteurl . '">' . $site->blogname . '</a> (<a href="' . esc_url( get_admin_url( $site->blog_id , 'widgets.php' ) ) . '">' . __( 'configure', 'network-admin-assistant' ) . ')</a>' . ' <em>(' . $sidebarname . ')</em>';
						}
					}
				}
			}

			// sort the results array alphabetically
			ksort( $results );

			// Store the number of widgets that are active for use n the dashboard.
			$dashboard_stats['active'] = count( $results );

			// render the html table
			$this->render_table( $results );
			
			// wrap up
			echo '</p>';
			echo '<p><em>';
			printf( __('Page render time: %1$s seconds, sites queried: %2$s', 'network-admin-assistant' ), round( microtime( true ) - $starttime, 3 ), count( $sites ) );
			echo '</em></p>';
			echo '</div>';

			// Store the dashboard stats.
			update_site_option( 'naa_widget_stats', $dashboard_stats );
		}


		/**
		 * Gets passed the results array, renders a nice HTML table
		 */
		private function render_table( $results ){
			$html = '<table class="widefat fixed" cellspacing="0">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Widget name', 'network-admin-assistant' ) . '</th>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Instance count', 'network-admin-assistant' ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			$count = 0;

			foreach( $results as $name=>$inst ){
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