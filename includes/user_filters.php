<?php

// If called without WordPress, exit.
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('NAA_User_Filters') ){

	class NAA_User_Filters {

		private $no_role_user_ids = null;

		/**
		 * Set up hooks and filters.
		 */
		public function init() {
			// Filter the network admin users screen views.
			add_action( 'views_users-network', array( $this, 'add_view' ), 10, 1 );
			// Use an early hook to get no-role users (to later exlude them from the query).
			add_action( 'admin_init', array( $this, 'get_users_without_role' ) );
			// Use pre_get_users to filter the user listing.
			add_action( 'pre_get_users', array( $this, 'filter_user_query' ), 10, 1 );
		}


		/**
		 * Adds the 'No role' view to the top of the network admin users screen.
		 */
		public function add_view( $views ){
			// Are we currently on 'our' view?
			$filtered = ( isset( $_GET['naa_user_filter'] ) && 'naa_no_role' == $_GET['naa_user_filter'] );
			// If so, remove the 'current' attributes from the other views (yes, this is a hack).
			if( $filtered ){
				foreach( $views as $key=>$view ){
					$views[ $key ] = str_replace( ' class="current" aria-current="page"', '', $views[ $key ] );
				}
			}
			// Add the new view to the $views array (code mostly copied from WP core).
			$current_link_attributes = $filtered ? ' class="current" aria-current="page"' : '';
			$views['naa_no_role'] = sprintf(
				'<a href="%s"%s>%s</a>',
				network_admin_url( 'users.php?naa_user_filter=naa_no_role' ),
				$current_link_attributes,
				sprintf(
					/* translators: Number of users. */
					_nx(
						'No role <span class="count">(%s)</span>',
						'No role <span class="count">(%s)</span>',
						count( $this->no_role_user_ids ),
						'users',
						'network-admin-assistant'
					),
					number_format_i18n( count( $this->no_role_user_ids ) )
				)
			);
			// Return the updated views array.
			return $views;
		}


		/**
		 * If the 'No role' view is acive, limit the query to just the IDs stored by get_users_without_role().
		 */
		public function filter_user_query( $query ){
			// Exit if not in wp-admin.
			if( ! is_network_admin() ){
				return;
			}
			// Exit if not on the correct screen.
			$screen = get_current_screen();
			if( ! isset( $screen ) || 'users-network' != $screen->id ){
				return;
			}
			// Are we on 'our' view? If not, exit.
			$filtered = ( isset( $_GET['naa_user_filter'] ) && 'naa_no_role' == $_GET['naa_user_filter'] );
			if( ! $filtered ){
				return;
			}
			// Include only the no-role users..
			$query->query_vars['include'] = $this->no_role_user_ids;
		}


		/**
		 * Find users with no role on any site.
		 */
		public function get_users_without_role(){
			// Exit if not in the network admin section.
			if( ! is_network_admin() ){
				return;
			}
			// Get all users.
			$users = get_users(
				array(
					'blog_id' => 0,
					'fields'  => 'ID',
				)
			);
			// Create an array to hold no-role user ID's.
			$user_ids = array();
			// Loop through the users and count role-less users.
			foreach( $users as $user_id ){
				if( ! is_super_admin( $user_id ) ){
					$blogs = get_blogs_of_user( $user_id );
					if( empty( $blogs ) ){
						$user_ids[] = $user_id;
					}
				}
			}
			// Store the results in the class var.
			$this->no_role_user_ids = $user_ids;
		}

	}
}