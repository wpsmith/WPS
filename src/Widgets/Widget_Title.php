<?php
/**
 * Widget Title Class File
 *
 * Title Widget.
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

if ( ! class_exists( 'WPS\Widgets\Widget_Title' ) ) {
	class Widget_Title extends WP_Widget {

		/**
		 * Widget_Title constructor.
		 */
		public function __construct() {

			/* Widget settings. */
			$widget_ops = array(
				'classname'   => 'site_title_widget',
				'description' => __( 'A widget that displays a title.', WPSCORE_PLUGIN_DOMAIN ),
			);

			/* Create the widget. */
			parent::__construct( 'site_title_widget', __( 'Title Widget', WPSCORE_PLUGIN_DOMAIN ), $widget_ops );
		}

		/**
		 * Display Widget.
		 *
		 * @param array $args Array of widget args.
		 * @param array $instance Array of widget instance args.
		 */
		public function widget( $args, $instance ) {

			$instance = wp_parse_args( $instance, $this->get_defaults() );

			/* Our variables from the widget settings. */
			$title = apply_filters( 'widget_title', $instance['title'] );

			/* Before widget (defined by themes). */
			echo $args['before_widget'];

			/* Display the widget title if one was input (before and after defined by themes). */
			if ( $title ) {
//		    echo $args['before_title']; //<h4 class="widget-title widgettitle">
				genesis_markup( array(
					'open'    => "<{$instance['tag']} %s>",
					'close'   => "</{$instance['tag']}>",
					'context' => 'widget-title',
					'content' => $title,
					'params'  => array(
						'is_widget' => true,
						'wrap'      => $instance['tag'],
					),
				) );
//			echo $args['after_title']; //</h4>
			}

			/* After widget (defined by themes). */
			echo $args['after_widget'];
		}

		/**
		 * Update Widget
		 *
		 * @param array $new_instance New instance values.
		 * @param array $old_instance Old instance values.
		 *
		 * @return array
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			/* Strip tags for title and name to remove HTML (important for text inputs). */
			$instance['title'] = wp_kses( $new_instance['title'], wp_kses_allowed_html( 'post' ) );
			$instance['tag']   = strip_tags( $new_instance['tag'] );

			return $instance;
		}

		/**
		 * Gets default values.
		 *
		 * @return array
		 */
		protected function get_defaults() {
			return array(
				'title' => '',
				'tag'   => 'h3',
			);
		}

		/**
		 * Displays the widget settings controls on the widget panel.
		 * Make use of the get_field_id() and get_field_name() function
		 * when creating your form elements. This handles the confusing stuff.
		 *
		 * @param array $instance Widget instance args.
		 */
		public function form( $instance ) {

			/* Set up some default widget settings. */
			$defaults = $this->get_defaults();
			$instance = wp_parse_args( (array) $instance, $defaults ); ?>

            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', SITECORE_PLUGIN_DOMAIN ) ?></label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                       name="<?php echo $this->get_field_name( 'title' ); ?>"
                       value="<?php echo $instance['title']; ?>"/>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'tag' ); ?>"><?php _e( 'Tag:', SITECORE_PLUGIN_DOMAIN ) ?> </label>
                <select id="<?php echo esc_attr( $this->get_field_id( 'tag' ) ); ?>"
                        name="<?php echo esc_attr( $this->get_field_name( 'tag' ) ); ?>">
                    <option value="h1" <?php selected( 'h1', $instance['tag'] ); ?>><?php echo 'h1'; ?></option>
                    <option value="h2" <?php selected( 'h2', $instance['tag'] ); ?>><?php echo 'h2'; ?></option>
                    <option value="h3" <?php selected( 'h3', $instance['tag'] ); ?>><?php echo 'h3'; ?></option>
                    <option value="h4" <?php selected( 'h4', $instance['tag'] ); ?>><?php echo 'h4'; ?></option>
                    <option value="h5" <?php selected( 'h5', $instance['tag'] ); ?>><?php echo 'h5'; ?></option>
                    <option value="h6" <?php selected( 'h6', $instance['tag'] ); ?>><?php echo 'h6'; ?></option>
                </select>
            </p>


			<?php
		}
	}
}

add_filter( 'genesis_attr_widget-title', 'site_genesis_attributes_widget_title' );
/**
 * Add attributes for entry title element.
 *
 * @param array $attributes Existing attributes for entry title element.
 *
 * @return array Amended attributes for entry title element.
 */
function site_genesis_attributes_widget_title( $attributes ) {

	$attributes['itemprop'] = 'headline';
	$attributes['class']    = 'widget-title widgettitle';

	return $attributes;

}