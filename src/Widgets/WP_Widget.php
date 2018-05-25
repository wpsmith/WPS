<?php
/**
 * WP Widget Class File
 *
 * Assist in creating WordPress Widgets.
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Widgets
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Widgets;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Widgets\WP_Widget' ) ) {
	/**
	 * Class WP_Widget.
	 *
	 * @package WPS\Widgets
	 */
	abstract class WP_Widget extends \WP_Widget {

		/**
		 * Holds widget settings defaults, populated in constructor.
		 *
		 * @var array
		 */
		protected $defaults;

		/**
		 * Constructor. Set the default widget options and create widget.
		 *
		 * @param string $id_base         Optional Base ID for the widget, lowercase and unique. If left empty,
		 *                                a portion of the widget's class name will be used Has to be unique.
		 * @param string $name            Name for the widget displayed on the configuration page.
		 * @param array  $widget_options  Optional. Widget options. See wp_register_sidebar_widget() for information
		 *                                on accepted arguments. Default empty array.
		 * @param array  $control_options Optional. Widget control options. See wp_register_widget_control() for
		 *                                information on accepted arguments. Default empty array.
		 */
		public function __construct( $id_base, $name, $widget_options = array(), $control_options = array() ) {

			add_action( 'admin_print_footer_scripts', array( __CLASS__, 'admin_footer_script' ) );

			$this->defaults = $this->get_defaults();

			parent::__construct( $id_base, $name, $widget_options, $control_options );

		}

		/**
		 * Gets default values.
		 *
		 * @return array
		 */
		abstract protected function get_defaults();

		/**
		 * Outputs an inline admin script.
		 */
		public static function admin_footer_script() {
			if ( ! self::is_widgets_page() ) {
				return;
			}

			$js = '(function($){if (typeof wpsWidgetSave !== \'function\'){window.wpsWidgetSave = function(t){wpWidgets.save($(t).closest(\'div.widget\'), 0, 1, 0);}}})(jQuery);';
			wp_add_inline_script( 'jquery', $js );
			wp_enqueue_script( 'jquery' );
		}

		/**
		 * Whether current admin page is the widgets page.
		 *
		 * @return bool
		 */
		public static function is_widgets_page() {
			if ( ! is_admin() ) {
				return false;
			}

			$screen = get_current_screen();
			if ( 'widgets' !== $screen->base && 'widgets' !== $screen->id ) {
				return false;
			}

			return true;
		}

	}
}
