<?php
/**
 * User Class
 *
 * Sets the current user and is usable very early,
 * before WordPress sets the current user.
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Core
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'User' ) ) {
	/**
	 * Class User
	 *
	 * @package WPS\Core
	 */
	class User extends Singleton {

		/**
		 * Current User
		 *
		 * @var \WP_User
		 */
		public $user;

		/**
		 * Super Users
		 *
		 * @var array Array of usernames.
		 */
		public $super_users;

		/**
		 * User constructor.
		 *
		 * @param array $super_users Array of super users.
		 */
		public function __construct( $super_users = array() ) {
			$this->super_users = $super_users;

			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 0 );
		}

		/**
		 * Sets the user.
		 */
		public function plugins_loaded() {
			$this->user = $this->set_user();
		}

		/**
		 * Exact copy of pluggable _wp_get_current_user();
		 *
		 * @return \WP_User
		 */
		private function set_user() {
			global $current_user;
			$user_id = apply_filters( 'determine_current_user', false );
			if ( ! $user_id ) {
				wp_set_current_user( 0 );

				return $current_user;
			}

			wp_set_current_user( $user_id );

			return $current_user;
		}

		/**
		 * Determines whether the user is a super user.
		 *
		 * @param mixed $user User to be checked.
		 *
		 * @return bool Whether the user is a super user.
		 */
		public function is_super_user( $user ) {
			$user = $this->get_user( $user );

			return in_array( $user, $this->super_users, true );
		}

		/**
		 * Determines whether the user is a current user.
		 *
		 * @param mixed $user User to be checked.
		 *
		 * @return bool Whether the user is the current user.
		 */
		public function is_current_user( $user ) {
			$user = $this->get_user( $user );
			if ( function_exists( 'wp_get_current_user' ) ) {
				$current = wp_get_current_user();

				return ( $current->ID === $user->ID );
			}

			$this->set_user();
			global $current_user;

			return ( $current_user->ID === $user->ID );
		}

		/**
		 * Gets the user by email, ID, or login.
		 *
		 * @param string|int|\WP_User $user User.
		 *
		 * @return false|\WP_User The WP_User object or false if User cannot be found.
		 */
		private function get_user( $user ) {
			if ( is_numeric( $user ) ) {
				$user = get_user_by( 'ID', $user );
			} elseif ( is_string( $user ) ) {
				if ( is_email( $user ) ) {
					$user = get_user_by( 'email', $user );
				} else {
					$user = get_user_by( 'login', $user );
				}
			}

			return $user;
		}

	}
}
