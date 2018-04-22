<?php


/**
 * WPS HTTP Server Push
 *
 * @since 0.0.6
 *
 * @package   WPS_Core
 * @author    Travis Smith <t@wpsmith.net>
 * @license   GPL-2.0+
 * @link      http://wpsmith.net
 * @copyright 2014 Travis Smith, WP Smith, LLC
 */

namespace WPS\Core;

use WPS;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_Cron')) {
    /**
     * Class Server_Push
     * @package WPS\HTTP2
     */
    class WP_Cron extends Singleton
    {

        /**
         * Cron name.
         *
         * @var string
         */
        private $name = '';

        /**
         * Either a cron interval string or cron schedule array.
         *
         * @var string|array
         */
        private $interval;

        /**
         * Cron function.
         *
         * @var callable
         */
        private $callback;

        public function __construct($cron_name, $interval, $callback, $create_now = false)
        {
            $this->name = $cron_name;
            $this->interval = $interval;

            if (is_array($interval)) {
                add_filter('cron_schedules', array($this, 'add_cron_interval'));
            }

            $this->maybe_remove_cron();

            register_activation_hook(__FILE__, array($this, 'create_cron'));
            register_deactivation_hook(__FILE__, array($this, 'remove_cron'));

            if ($create_now) {
                $this->create_cron();
            }
            if (is_callable($callback)) {
                $this->callback = $callback;
                add_action($cron_name, $callback);
            }

        }

        public function maybe_remove_cron()
        {
            if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
                wp_clear_scheduled_hook($this->name);
                delete_option('wps_cron_' . $this->name);
            }
        }

        public function add_cron_interval($schedules)
        {
            // Only add the 30 minute interval if it wasn't already set.
            if (!isset($schedules[$this->interval['name']])) {
                $schedules[$this->interval['name']] = array(
                    'interval' => $this->interval['interval'],
                    'display' => $this->interval['display'],
                );
            }

            return $schedules;
        }

        private function get_interval()
        {
            if (is_array($this->interval) && isset($this->interval['name'])) {
                return $this->interval['name'];
            }

            if (is_string($this->interval)) {
                return $this->interval;
            }

            return 'daily';
        }

        public static function is_cron_disabled()
        {
            return (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON);
        }

        public static function is_doing_cron()
        {
            return (
                defined('DOING_CRON') && DOING_CRON ||
                isset($_GET['doing_wp_cron'])
            );
        }

        public function create_cron()
        {
            //Use wp_next_scheduled to check if the event is already scheduled
            $timestamp = wp_next_scheduled($this->name);

            if ($timestamp == false) {
                $result = wp_schedule_event(time(), $this->get_interval(), $this->name);
//				var_dump( $result );
//				wp_die();
            }
        }

    }
}


