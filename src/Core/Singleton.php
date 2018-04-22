<?php

/**
 * Singleton Class
 *
 * @package    Core_Mu
 * @copyright  Copyright (c) 2017, Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      1.0.0
 */

namespace WPS\Core;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Singleton')) {
    abstract class Singleton
    {

        /**
         * Holds various singleton instances.
         *
         * @since 1.0.0
         *
         * @var array
         */
        private static $instances = array();

        /**
         * Singleton class constructor.
         * Protected constructor to prevent creating a new instance of the
         * *Singleton* via the `new` operator from outside of this class.
         *
         * @access private
         * @since  1.0.0
         */
        protected function __construct()
        {

            // Thou shalt not construct that which is unconstructable!
        }

        /**
         * Private clone method to prevent cloning of the instance of the
         * *Singleton* instance.
         *
         * @access private
         * @since  1.0.0
         *
         * @return void
         */
        protected function __clone()
        {
            // Me not like clones! Me smash clones!
        }

        /**
         * Private unserialize method to prevent unserializing of the *Singleton*
         * instance.
         *
         * @access private
         * @since  1.0.0
         *
         * @return void
         */
        private function __wakeup()
        {

            // Sleepy, sleepy.
            throw new Exception('Cannot unserialize singleton');
        }

        /**
         * Returns the singleton instance of the class.
         *
         * @access public
         * @since  1.0.0
         * @static
         *
         * @return object Instance of the Singleton class or child class.
         */
        public static function get_instance($args = array())
        {

            $cls = get_called_class(); // late-static-bound class name
            if (!isset(self::$instances[$cls])) {
                self::$instances[$cls] = new static($args);
            }

            return self::$instances[$cls];
        }
    }
}
