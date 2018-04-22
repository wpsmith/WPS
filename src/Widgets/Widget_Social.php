<?php

/*
 * Widget class.
 */

namespace WPS\Widgets;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widget_Social extends \WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * WPS_Widget_Social constructor.
	 */
	public function __construct() {

		$this->defaults = array(
			'title'     => '',
			'facebook'  => '',
			'twitter'   => '',
			'instagram' => '',
			'email'     => '',
		);

		/* Widget settings. */
		$widget_ops = array(
			'classname'   => 'site_social_widget',
			'description' => __( 'A widget that addresses your social information.', WPSCORE_PLUGIN_DOMAIN )
		);

		/* Create the widget. */
		parent::__construct( 'site_social_widget', __( 'Social Widget', WPSCORE_PLUGIN_DOMAIN ), $widget_ops );
	}

	/**
	 * Display Widget.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		/* Before widget (defined by themes). */
		echo $args['before_widget'];

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $instance['title'] ) {
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		}

		$output = '<ul class="swi">';
		foreach ( array( 'facebook', 'twitter', 'instagram', 'email', ) as $icon ) {
		    $output .= sprintf( '<li class="swi-' . strrev( $icon ) . '"><a href="%s" target="_blank">' . $this->get_icon( $icon ) . '</a></li>', $instance[ $icon ] );
		}
		$output .= '</ul>';

		echo $output;

		/* After widget (defined by themes). */
		echo $args['after_widget'];
	}

	private function get_icon( $name ) {
	    switch ( $name ) {
            case 'facebook':
                return '<svg width="30px" height="30px" viewBox="0 0 30 30" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <!-- Generator: Sketch 47.1 (45422) - http://www.bohemiancoding.com/sketch -->
    <desc>Created with Sketch.</desc>
    <defs></defs>
    <g id="Mockup-Update" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <g id="Home" transform="translate(-280.000000, -4160.000000)" fill="#3A589B">
            <g id="Footer" transform="translate(0.000000, 4005.000000)">
                <g id="Left" transform="translate(135.000000, 37.000000)">
                    <g id="social" transform="translate(145.000000, 118.000000)">
                        <g id="facebook">
                            <path d="M12.8825,23.16 L16.2545,23.16 L16.2545,14.999 L18.504,14.999 L18.802,12.187 L16.2545,12.187 L16.258,10.779 C16.258,10.046 16.328,9.6525 17.38,9.6525 L18.786,9.6525 L18.786,6.84 L16.536,6.84 C13.8335,6.84 12.8825,8.2045 12.8825,10.4985 L12.8825,12.187 L11.198,12.187 L11.198,14.9995 L12.8825,14.9995 L12.8825,23.16 L12.8825,23.16 Z M15,30 C6.716,30 0,23.284 0,15 C0,6.7155 6.716,0 15,0 C23.284,0 30,6.7155 30,15 C30,23.284 23.284,30 15,30 Z" id="Shape"></path>
                        </g>
                    </g>
                </g>
            </g>
        </g>
    </g>
</svg>';
            case 'email':
                return '<svg width="30px" height="30px" viewBox="0 0 30 30" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <!-- Generator: Sketch 47.1 (45422) - http://www.bohemiancoding.com/sketch -->
    <desc>Created with Sketch.</desc>
    <defs></defs>
    <g id="Mockup-Update" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <g id="Home" transform="translate(-430.000000, -4160.000000)" fill-rule="nonzero">
            <g id="Footer" transform="translate(0.000000, 4005.000000)">
                <g id="Left" transform="translate(135.000000, 37.000000)">
                    <g id="social" transform="translate(145.000000, 118.000000)">
                        <g id="mail" transform="translate(150.000000, 0.000000)">
                            <path d="M15,30 C23.2842717,30 30,23.2842717 30,15 C30,6.71572835 23.2842717,0 15,0 C6.71572835,0 0,6.71572835 0,15 C0,23.2842717 6.71572835,30 15,30 Z" id="Shape" fill="#3498DB"></path>
                            <path d="M15,16.7307693 L23.6538461,9.23076925 L6.34615385,9.23076925 L15,16.7307693 Z M12.6625883,15.9005268 L15,17.81912 L17.3007906,15.9005268 L23.6538461,21.3461539 L6.34615385,21.3461539 L12.6625883,15.9005268 Z M5.76923075,20.7692308 L5.76923075,9.8076923 L12.1153846,15.2884616 L5.76923075,20.7692308 Z M24.2307693,20.7692308 L24.2307693,9.8076923 L17.8846154,15.2884616 L24.2307693,20.7692308 Z" id="Shape" fill="#FFFFFF"></path>
                        </g>
                    </g>
                </g>
            </g>
        </g>
    </g>
</svg>';
            case 'instagram':
                return '<svg width="30px" height="30px" viewBox="0 0 30 30" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <!-- Generator: Sketch 47.1 (45422) - http://www.bohemiancoding.com/sketch -->
    <desc>Created with Sketch.</desc>
    <defs></defs>
    <g id="Mockup-Update" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <g id="Home" transform="translate(-380.000000, -4160.000000)" fill="#517FA6">
            <g id="Footer" transform="translate(0.000000, 4005.000000)">
                <g id="Left" transform="translate(135.000000, 37.000000)">
                    <g id="social" transform="translate(145.000000, 118.000000)">
                        <g id="instagram" transform="translate(100.000000, 0.000000)">
                            <path d="M19.6355,11.289 L19.6355,11.286 C19.8865,11.286 20.138,11.291 20.3895,11.285 C20.7125,11.277 20.9755,11 20.9755,10.6765 C20.9755,10.195 20.9755,9.713 20.9755,9.2315 C20.9755,8.886 20.702,8.6115 20.3575,8.611 C19.877,8.611 19.3965,8.6105 18.916,8.611 C18.572,8.6115 18.298,8.887 18.298,9.2325 C18.2975,9.71 18.296,10.1875 18.2995,10.665 C18.3,10.7365 18.3135,10.8105 18.336,10.878 C18.4225,11.132 18.6555,11.288 18.9405,11.2895 C19.172,11.2895 19.404,11.289 19.6355,11.289 Z M15,11.9085 C13.308,11.9075 11.9325,13.269 11.909,14.953 C11.8845,16.683 13.269,18.0535 14.929,18.089 C16.656,18.126 18.053,16.746 18.0895,15.0675 C18.127,13.3375 16.731,11.9075 15,11.9085 Z M9.023,13.558 L9.023,13.599 C9.023,15.8565 9.0225,18.114 9.023,20.3715 C9.023,20.696 9.304,20.9755 9.629,20.9755 C13.209,20.976 16.7885,20.976 20.3685,20.9755 C20.6965,20.9755 20.976,20.697 20.976,20.3695 C20.9765,18.115 20.976,15.8595 20.976,13.6055 L20.976,13.5585 L19.52,13.5585 C19.7255,14.2155 19.7885,14.884 19.708,15.5655 C19.6275,16.247 19.4075,16.881 19.05,17.467 C18.6925,18.053 18.228,18.5395 17.6605,18.926 C16.1885,19.929 14.25,20.017 12.6875,19.14 C11.898,18.6975 11.278,18.08 10.845,17.2835 C10.2005,16.097 10.0975,14.851 10.4755,13.558 C9.9915,13.558 9.5105,13.558 9.023,13.558 Z M21.1025,22.6275 C21.182,22.6145 21.2615,22.603 21.34,22.586 C21.963,22.4535 22.472,21.934 22.594,21.3075 C22.6065,21.239 22.6165,21.171 22.6275,21.103 L22.6275,8.897 C22.617,8.8305 22.6075,8.763 22.595,8.6965 C22.461,8.013 21.897,7.4825 21.205,7.3875 C21.176,7.384 21.1485,7.3775 21.12,7.3725 L8.8805,7.3725 C8.807,7.386 8.7325,7.396 8.66,7.4125 C7.984,7.5665 7.484,8.1105 7.3875,8.7955 C7.3835,8.824 7.3775,8.8525 7.373,8.881 L7.373,21.12 C7.387,21.197 7.398,21.2755 7.4155,21.3525 C7.565,22.0135 8.129,22.526 8.8005,22.6125 C8.8325,22.6165 8.8655,22.623 8.898,22.6275 L21.1025,22.6275 L21.1025,22.6275 Z M15,30 C6.716,30 0,23.284 0,15 C0,6.7155 6.716,0 15,0 C23.284,0 30,6.7155 30,15 C30,23.284 23.284,30 15,30 Z" id="Shape"></path>
                        </g>
                    </g>
                </g>
            </g>
        </g>
    </g>
</svg>';
            case 'twitter':
                return '<svg width="30px" height="30px" viewBox="0 0 30 30" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <!-- Generator: Sketch 47.1 (45422) - http://www.bohemiancoding.com/sketch -->
    <desc>Created with Sketch.</desc>
    <defs></defs>
    <g id="Mockup-Update" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <g id="Home" transform="translate(-330.000000, -4160.000000)" fill="#598DCA">
            <g id="Footer" transform="translate(0.000000, 4005.000000)">
                <g id="Left" transform="translate(135.000000, 37.000000)">
                    <g id="social" transform="translate(145.000000, 118.000000)">
                        <g id="twitter" transform="translate(50.000000, 0.000000)">
                            <path d="M17.0835,9.1415 C15.774,9.618 14.9465,10.847 15.0405,12.192 L15.072,12.711 L14.548,12.6475 C12.6415,12.404 10.9755,11.578 9.561,10.19 L8.8695,9.5015 L8.6915,10.01 C8.3145,11.1435 8.5555,12.3405 9.341,13.1455 C9.76,13.5905 9.6655,13.654 8.943,13.389 C8.6915,13.3045 8.4715,13.241 8.4505,13.2725 C8.3775,13.347 8.6285,14.3105 8.8275,14.692 C9.1,15.222 9.655,15.7405 10.263,16.048 L10.7765,16.2915 L10.169,16.302 C9.5825,16.302 9.5615,16.3125 9.6245,16.5355 C9.834,17.224 10.6615,17.955 11.5835,18.273 L12.233,18.495 L11.6675,18.834 C10.8295,19.322 9.8445,19.597 8.8595,19.6175 C8.3875,19.628 8,19.6705 8,19.7025 C8,19.808 9.2785,20.401 10.022,20.6345 C12.2535,21.323 14.9045,21.026 16.895,19.8505 C18.3095,19.0135 19.7235,17.3505 20.384,15.74 C20.7405,14.8825 21.0965,13.3145 21.0965,12.563 C21.0965,12.0755 21.128,12.012 21.7145,11.4295 C22.0605,11.0905 22.385,10.72 22.448,10.614 C22.553,10.4125 22.542,10.4125 22.008,10.5925 C21.1175,10.9105 20.9915,10.868 21.432,10.3915 C21.7565,10.0525 22.1445,9.438 22.1445,9.258 C22.1445,9.2265 21.9875,9.279 21.809,9.3745 C21.6205,9.4805 21.2015,9.6395 20.887,9.7345 L20.3215,9.915 L19.808,9.565 C19.525,9.3745 19.1275,9.1625 18.9175,9.099 C18.383,8.951 17.5655,8.972 17.0835,9.1415 Z M15,30 C6.716,30 0,23.284 0,15 C0,6.7155 6.716,0 15,0 C23.284,0 30,6.7155 30,15 C30,23.284 23.284,30 15,30 Z" id="Shape"></path>
                        </g>
                    </g>
                </g>
            </g>
        </g>
    </g>
</svg>';
        }
        return '';
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

		$instance['facebook']  = esc_url_raw( $new_instance['facebook'] );
		$instance['twitter']   = esc_url_raw( $new_instance['twitter'] );
		$instance['instagram'] = esc_url_raw( $new_instance['instagram'] );
		$instance['email']     = sanitize_email( $new_instance['email'] );


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

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', WPSCORE_PLUGIN_DOMAIN ) ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                   name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>"/>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'facebook' ); ?>"><?php _e( 'Facebook:', WPSCORE_PLUGIN_DOMAIN ) ?> </label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'facebook' ); ?>"
                   name="<?php echo $this->get_field_name( 'facebook' ); ?>"
                   value="<?php echo $instance['facebook']; ?>"/>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'twitter' ); ?>"><?php _e( 'Twitter:', WPSCORE_PLUGIN_DOMAIN ) ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'twitter' ); ?>"
                   name="<?php echo $this->get_field_name( 'twitter' ); ?>"
                   value="<?php echo $instance['twitter']; ?>"/>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'email' ); ?>"><?php _e( 'Email:', WPSCORE_PLUGIN_DOMAIN ) ?> </label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'email' ); ?>"
                   name="<?php echo $this->get_field_name( 'email' ); ?>" value="<?php echo $instance['email']; ?>"/>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'instagram' ); ?>"><?php _e( 'Instagram:', WPSCORE_PLUGIN_DOMAIN ) ?> </label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'instagram' ); ?>"
                   name="<?php echo $this->get_field_name( 'instagram' ); ?>"
                   value="<?php echo $instance['instagram']; ?>"/>
        </p>

		<?php
	}
}