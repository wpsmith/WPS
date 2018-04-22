<?php

/*
 * Widget class.
 */

namespace WPS\Widgets;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widget_Title extends \WP_Widget {

	/**
	 * WPS_Widget_Title constructor.
	 */
	public function __construct() {

		/* Widget settings. */
		$widget_ops = array(
			'classname'   => 'site_title_widget',
			'description' => __( 'A widget that displays a title.', WPSCORE_PLUGIN_DOMAIN )
		);

		/* Create the widget. */
		parent::__construct( 'site_title_widget', __( 'Title Widget', WPSCORE_PLUGIN_DOMAIN ), $widget_ops );
	}

	/**
	 * Display Widget.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

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
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = wp_kses( $new_instance['title'], wp_kses_allowed_html( 'post' ) );
		$instance['tag']   = strip_tags( $new_instance['tag'] );

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 *
	 * @param array $instance
	 *
	 * @return null
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title' => __( '', SITECORE_PLUGIN_DOMAIN ),
			'tag'   => 'h3',
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', SITECORE_PLUGIN_DOMAIN ) ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>"/>
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
	$attributes['class'] = 'widget-title widgettitle';

	return $attributes;

}