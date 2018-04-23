<?php
/**
 * WPS Core Crong Class
 *
 * @package    WPS\Core
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 WP Smith, Travis Smith
 * @link       https://github.com/wpsmith/WPS/
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Cron' ) ) {
	/**
	 * WordPress Cron Class
	 *
	 * Assists in creating and managing cron jobs.
	 *
	 * @package WPS\Core
	 * @author Travis Smith <t@wpsmith.net>
	 */
	class WP_Cron extends Singleton {

		/**
		 * Cron name.
		 *
		 * @var string
		 */
		private $name = '';

		/**
		 * Either a cron interval string.
		 *
		 * @var integer
		 */
		private $interval;

		/**
		 * Either a cron schedule.
		 *
		 * array(
		 *     'name'     => 'every-other-day',
		 *     'interval' => DAY_IN_SECONDS * 2,
		 *     'display'  => __( 'Every Other Day', 'domain' ),
		 * )
		 *
		 * @var array
		 */
		private $schedule;

		/**
		 * Cron function.
		 *
		 * @var callable
		 */
		private $callback;

		/**
		 * Array of some additional built-in cron names and times.
		 *
		 * @var array Array of some additional times.
		 */
		private $intervals = array(
			'minute'  => MINUTE_IN_SECONDS,
			'weekly'  => WEEK_IN_SECONDS,
			'monthly' => MONTH_IN_SECONDS,
			'yearly'  => YEAR_IN_SECONDS,
		);

		/**
		 * Default interval is daily (60*60*24)
		 *
		 * @const integer
		 */
		const DEFAULT_INTERVAL = DAY_IN_SECONDS;

		/**
		 * Minute-by-minute schedule name.
		 * @const string
		 */
		const MINUTE = 'minute';

		/**
		 * Weekly schedule name
		 *
		 * @const string
		 */
		const WEEKLY = 'weekly';

		/**
		 * Monthly schedule name
		 *
		 * @const string
		 */
		const MONTHLY = 'monthly';

		/**
		 * Yearly schedule name
		 *
		 * @const string
		 */
		const YEARLY = 'yearly';

		/**
		 * WP_Cron constructor.
		 *
		 * @param string $cron_name Name of the cron job.
		 * @param string $interval Interval of the cron job. The 'interval' is a number in seconds of when the cron job should run.
		 *                         Built-in values include: minute, hourly, daily, weekly, monthly, yearly.
		 * @param callable $callback Function callback for job.
		 * @param bool $create_now Whether to create the cron job now.
		 */
		public function __construct( $cron_name, $interval_or_schedule, $callback, $create_now = false ) {
			$this->name = $cron_name;

			$this->maybe_add_cron_schedule( $interval_or_schedule );

			$this->maybe_remove_cron();

			register_activation_hook( __FILE__, array( $this, 'create_cron' ) );
			register_deactivation_hook( __FILE__, array( $this, 'remove_cron' ) );

			if ( $create_now ) {
				$this->create_cron();
			}
			if ( is_callable( $callback ) ) {
				$this->callback = $callback;
				add_action( $cron_name, $callback );
			}

		}

		/**
		 * Determines whether the cron schedules has the interval.
		 *
		 * @return bool wp_get_schedules() contains the interval.
		 */
		private function has_interval() {
			$schedules = wp_get_schedules();
			foreach ( $schedules as $name => $schedule ) {
				if ( $schedule['interval'] == $this->interval ) {
					if ( empty( $this->schedule ) ) {
						$this->schedule = $schedule;
					}

					return true;
				}
			}

			return false;
		}

		/**
		 * Ensures that schedule is added to wp_get_schedules()
		 *
		 * @param integer|array $interval_or_schedule Interval or schedule array.
		 */
		private function maybe_add_cron_schedule( $interval_or_schedule ) {

			// If $interval_or_schedule is a string and not numeric, then let's see if it represents any known time length.
			if ( is_string( $interval_or_schedule ) && ! is_numeric( $interval_or_schedule ) ) {
				switch ( $interval_or_schedule ) {
					case self::MINUTE:
						$interval_or_schedule = $this->get_schedule( self::MINUTE, MINUTE_IN_SECONDS );
						break;
					case self::WEEKLY:
						$interval_or_schedule = $this->get_schedule( self::WEEKLY, WEEK_IN_SECONDS );
						break;
					case self::MONTHLY:
						$interval_or_schedule = $this->get_schedule( self::MONTHLY, MONTH_IN_SECONDS );
						break;
					case self::YEARLY:
						$interval_or_schedule = $this->get_schedule( self::YEARLY, YEAR_IN_SECONDS );
						break;
				}

				// If $interval_or_schedule is numeric, then let's assume it is the interval
			} elseif ( is_numeric( $interval_or_schedule ) ) {
				$this->interval = $interval_or_schedule;

				if ( ! $this->has_interval() ) {
					switch ( $interval_or_schedule ) {
						case MINUTE_IN_SECONDS:
							$interval_or_schedule = $this->get_schedule( self::MINUTE, MINUTE_IN_SECONDS );
							break;
						case WEEK_IN_SECONDS:
							$interval_or_schedule = $this->get_schedule( self::WEEKLY, WEEK_IN_SECONDS );
							break;
						case MONTH_IN_SECONDS:
							$interval_or_schedule = $this->get_schedule( self::MONTHLY, MONTH_IN_SECONDS );
							break;
						case YEAR_IN_SECONDS:
							$interval_or_schedule = $this->get_schedule( self::YEARLY, YEAR_IN_SECONDS );
							break;
					}
				}
			} elseif ( is_array( $interval_or_schedule ) && isset( $interval_or_schedule['interval'] ) ) {
				// Sanitize 'interval'
				$interval_or_schedule['interval'] = $this->get_interval( $interval_or_schedule['interval'] );
			}

			// Now we should have a sanitized schedule in $interval_or_schedule.
			// If $interval_or_schedule is an schedule array, then let's hook into `cron_schedules`
			if ( is_array( $interval_or_schedule ) ) {
				if ( empty( $this->schedule ) ) {
					$this->schedule = $interval_or_schedule;
				}
				if ( $this->interval === 0 || $this->interval == null ) {
					$this->interval = $interval_or_schedule['interval'];
				}
				add_filter( 'cron_schedules', array( $this, 'add_cron_schedule' ) );
			}
		}

		/**
		 * Gets a schedule array.
		 *
		 * @param string $name Slug of the cron job.
		 * @param string|integer $interval Interval value of the cron job.
		 *
		 * @return array Schedule array.
		 */
		private function get_schedule( $name, $interval ) {
			return array(
				'display'  => ucwords( str_replace( '-', ' ', $name ) ),
				'interval' => $this->get_interval( $interval ),
				'name'     => $name,
			);
		}

		/**
		 * Removes the cron job if cron is disabled.
		 */
		public function maybe_remove_cron() {
			if ( $this->is_cron_disabled() ) {
				wp_clear_scheduled_hook( $this->name );
				delete_option( 'wps_cron_' . $this->name );
			}
		}

		/**
		 * Adds a cron job.
		 *
		 * @param $schedules
		 *
		 * @return mixed
		 */
		public function add_cron_schedule( $schedules ) {
			// Only add the schedule if it wasn't already set.
			if ( ! isset( $schedules[ $this->schedule['name'] ] ) ) {
				$schedules[ $this->schedule['name'] ] = array(
					'interval' => $this->schedule['interval'],
					'display'  => $this->schedule['display'],
				);

				// If it is already set, let's check the interval
				// If the intervals do not match, let's create the cron with a different schedule name.
			} elseif (
				isset( $schedules[ $this->schedule['name'] ] ) &&
				$schedules[ $this->schedule['name'] ]['interval'] !== $this->schedule['name']['interval']
			) {
				$this->schedule['name'] = $this->schedule['name'] . '-' . $this->schedule['name']['interval'];
				$schedules[ $this->schedule['name'] ] = array(
					'interval' => $this->schedule['interval'],
					'display'  => $this->schedule['display'],
				);
			}

			return $schedules;
		}

		/**
		 * Gets a sanitized interval.
		 *
		 * @param string|integer $interval
		 *
		 * @return integer
		 */
		private function get_interval( $interval ) {
			switch ( $interval ) {
				case 'minute':
				case 'weekly'  :
				case 'monthly' :
				case 'yearly':
					return $this->intervals[ $interval ];
				default:
					if ( is_numeric( $interval ) ) {
						return (int)$interval;
					}

					return self::DEFAULT_INTERVAL;
			}

		}

		/**
		 * Whether cron has been disabled or not.
		 *
		 * @return bool
		 */
		public function is_cron_disabled() {
			return \WPS\is_cron_disabled();
		}

		/**
		 * Whether a CRON job is running.
		 *
		 * @return bool
		 */
		public function is_doing_cron() {
			return \WPS\is_doing_cron();
		}

		/**
		 * Schedules a cron job.
		 *
		 * @return bool False if the event does not get scheduled.
		 */
		public function create_cron() {
			// Use wp_next_scheduled() to check if the event is already scheduled
			$timestamp = wp_next_scheduled( $this->name );

			// If not scheduled, let's schedule it
			if ( false === $timestamp ) {
				$scheduled = wp_schedule_event( time(), $this->schedule['name'], $this->name );
				if ( false === $scheduled ) {
					return $scheduled;
				}
			}

			return true;
		}

	}
}


