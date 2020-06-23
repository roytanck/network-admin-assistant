<?php

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('MPWS_Plugin_stats') ){

	class MPWS_Plugin_stats {

		/**
		 * Constructor.
		 */
		public function init() {
			// hook for the admin page
			add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
		}


		/**
		 * Add a new options page to the network admin.
		 */
		public function admin_menu() {
			add_submenu_page(
				'settings.php',
				__( 'Plugin Stats', 'multisite-plugin-and-widget-stats' ),
				__( 'Plugin Stats', 'multisite-plugin-and-widget-stats' ),
				'manage_options',
				'rt_plugin_stats',
				array( $this, 'settings_page' )
			);
		}


		/**
		 * Render the options page.
		 */
		public function settings_page() {

			// Start a timer to keep track of processing time.
			$starttime = microtime( true );

			// Create a new array to keep the stats in.
			$results = array();

			// Start the page's output.
			echo '<div class="wrap">';
			echo '<h1>' . __( 'Plugin Statistics', 'multisite-plugin-and-widget-stats' ) . '</h1>';
			echo '<h2>' . __( 'Network activated plugins', 'multisite-plugin-and-widget-stats' ) . '</h2>';
			echo '<p>';
			
			// Get network activated plugins.
			$network_plugins = get_site_option( 'active_sitewide_plugins', null );
			
			// Render the html table.
			if( !empty( $network_plugins ) ){
				$this->render_network_activated_table( $network_plugins );
			}
			
			echo '</p>';

			// Get all currently published sites.
			$args = array(
				'archived'   => 0,
				'mature'     => 0,
				'spam'       => 0,
				'deleted'    => 0,
				'number'      => 9999,
			);
			$sites = get_sites( $args );

			echo '<h2>' . __( 'Activated plugins', 'multisite-plugin-and-widget-stats' ) . '</h2>';
			echo '<p>';

			// Gather the data by looping through the sites and getting the active_plugins option.
			foreach( $sites as $site ){
				
				$plugins = get_blog_option( $site->blog_id, 'active_plugins', null );
			
				foreach( $plugins as $plugin ){
					if( !empty( $plugin ) ){
						// Clean up the php file path that WordPress stores to get a "semi-readable" name.
						$pluginname = $this->get_plugin_name( $plugin );
						// Make sure there's an array for this plugin.
						if( !isset($results[$pluginname]) || !is_array( $results[$pluginname] ) ){
							$results[$pluginname] = array();
						}
						// Add the instance's data to the array.
						$results[$pluginname][] = '<a href="' . $site->siteurl . '">' . $site->blogname . '</a> (<a href="' . esc_url( get_admin_url( $site->blog_id ) ) . '">' . __( 'dashboard', 'multisite-plugin-and-widget-stats' ) . ')</a>';
					}
				}

			}

			// Sort the results array alphabetically.
			ksort( $results );

			// Render the html table.
			$this->render_table( $results );
			
			// Wrap up.
			echo '</p>';
			echo '<p><em>';
			printf( __('Page render time: %1$s seconds, sites queried: %2$s', 'multisite-plugin-and-widget-stats' ), round( microtime( true ) - $starttime, 3 ), count( $sites ) );
			echo '</em></p>';
			echo '</div>';
		}


		/**
		 * Gets passed the network activated plugins array, renders a nice HTML table.
		 */
		private function render_network_activated_table( $results ){
			$html = '<table class="widefat fixed" cellspacing="0">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Plugin name', 'multisite-plugin-and-widget-stats' ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			$count = 0;

			foreach( $results as $name=>$inst ){
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
		private function render_table( $results ){
			$html = '<table class="widefat fixed" cellspacing="0">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Plugin name', 'multisite-plugin-and-widget-stats' ) . '</th>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Activation count', 'multisite-plugin-and-widget-stats' ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			$count = 0;

			foreach( $results as $name=>$inst ){
				$html .= '<tr' . ( ( $count % 2 == 0 ) ? ' class="alternate"' : '' ) . '>';
				$html .= '<td class="column-columnname"><strong>' . $name . '</strong></td>';
				$html .= '<td class="column-columnname">';
				$html .= '<details>';
				$html .= '<summary>' . sprintf( esc_html__( 'Active on %d sites.', 'multisite-plugin-and-widget-stats' ), count( $inst ) ) . '</summary>';
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