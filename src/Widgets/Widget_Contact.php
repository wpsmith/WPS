<?php
/**
 * Widget Contact Class File
 *
 * Contact Widget.
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

if ( ! class_exists( 'WPS\Widgets\Widget_Contact' ) ) {
	/**
	 * Class Widget_Contact.
	 *
	 * @package WPS\Widgets
	 */
	class Widget_Contact extends \WP_Widget {

		/**
		 * Widget_Contact constructor.
		 */
		public function __construct() {

			/* Widget settings. */
			$widget_ops = array(
				'classname'   => 'site_contact_widget',
				'description' => __( 'A widget that addresses your contact information.', WPSCORE_PLUGIN_DOMAIN ),
			);

			/* Create the widget. */
			parent::__construct( 'site_contact_widget', __( 'Contact Widget', WPSCORE_PLUGIN_DOMAIN ), $widget_ops );
		}

		/**
		 * Display Widget.
		 *
		 * @param array $args     Array of widget args.
		 * @param array $instance Array of widget instance args.
		 */
		public function widget( $args, $instance ) {

			extract( wp_parse_args( $instance, $this->get_defaults() ) );

			/* Our variables from the widget settings. */
			$title = apply_filters( 'widget_title', $title );

			/* Before widget (defined by themes). */
			echo $args['before_widget'];

			/* Display the widget title if one was input (before and after defined by themes). */
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			/* Display Contact */
			if ( $telephone ) {
				printf( '<p class="highlight"><span class="fa fa-phone"></span>&nbsp;<a href="tel:%s">%s</a></p>', $this->normalize_telephone( $telephone, ';' ), $this->normalize_telephone( $telephone ) );
			}
			if ( $fax ) {
				printf( '<p class="highlight"><span class="fa fa-fax"></span>&nbsp;%s</p>', $fax );
			}
			if ( $email ) {
				printf( '<p class="highlight"><span class="fa fa-envelope"></span>&nbsp;<a href="mailto:%1$s">%1$s</a></p>', antispambot( $email ) );
			}
			if ( $address ) {
				printf( '<p class="highlight"><span class="fa fa-building"></span></span>&nbsp;%s</p>', $address );
			}
			if ( $skype ) {
				printf( '<p class="highlight"><span class="fa fa-skype"></span>&nbsp;%s</p>', $skype );
			}
			if ( $text ) {
				printf( '<p class="highlight"><span class="text">%s</span></p>', $text );
			}

			/* After widget (defined by themes). */
			echo $args['after_widget'];
		}

		/**
		 * Normalize telephone string.
		 *
		 * @param string $phone Phone number.
		 * @param string $ext   Extension number.
		 *
		 * @return bool|null|string|string[]
		 */
		private function normalize_telephone( $phone, $ext = ' Ext ' ) {
			$format = "/(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|;|x\.?|ext\.?|extension)\s*(\d+))?$/";

			$alt_format = '/^(\+\s*)?((0{0,2}1{1,3}[^\d]+)?\(?\s*([2-9][0-9]{2})\s*[^\d]?\s*([2-9][0-9]{2})\s*[^\d]?\s*([\d]{4})){1}(\s*([[:alpha:]#][^\d]*\d.*))?$/';
			// Trim & Clean extension
			$phone = strtolower( trim( $phone ) );
			$phone = preg_replace( '/\s+(#|x|;|ext\.?(ension)?\s?)\.?:?\s*(\d+)/', $ext . '\3', $phone );

//		preg_match( $alt_format, $phone, $matches );
//		echo "alt match? $phone \n";
//		wps_printr( $matches );

//		preg_match( $format, $phone, $matches );
//		echo "match? $phone \n";
//		wps_printr( $matches );

			if ( preg_match( $alt_format, $phone, $matches ) ) {
				return '(' . $matches[4] . ') ' . $matches[5] . '-' . $matches[6] . ( ! empty( $matches[8] ) ? ' ' . $matches[8] : '' );
			} elseif ( preg_match( $format, $phone, $matches ) ) {
				// format
				$phone = preg_replace( $format, "($2) $3-$4", $phone );
				if ( $matches[5] ) {
					$phone .= $ext . $matches[5];
				}
				// Remove likely has a preceding dash
				$phone = ltrim( $phone, '-' );
				// Remove empty area codes
				if ( false !== strpos( trim( $phone ), '()', 0 ) ) {
					$phone = ltrim( trim( $phone ), '()' );
				}

				// Trim and remove double spaces created
				return preg_replace( '/\\s+/', ' ', trim( $phone ) );
			}

			return false;
		}

		/**
		 * Gets default values.
		 *
		 * @return array
		 */
		protected function get_defaults() {
			return array(
				'title'     => __( 'Contact', WPSCORE_PLUGIN_DOMAIN ),
				'telephone' => '',
				'fax'       => '',
				'email'     => '',
				'address'   => '',
				'skype'     => '',
				'text'      => '',
			);
		}

		/**
		 * Update Widget
		 *
		 * @param array $new_instance New instance values.
		 * @param array $old_instance Old instance values.
		 *
		 * @return array
		 */
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			/* Strip tags for title and name to remove HTML (important for text inputs). */
			$instance['title']     = strip_tags( $new_instance['title'] );
			$instance['telephone'] = strip_tags( $new_instance['telephone'] );
			$instance['fax']       = $new_instance['fax'];
			$instance['email']     = $new_instance['email'];
			$instance['address']   = $new_instance['address'];
			$instance['skype']     = $new_instance['skype'];
			$instance['text']      = wp_kses( $new_instance['text'], wp_kses_allowed_html( 'post' ) );

			/* No need to strip tags for.. */

			return $instance;
		}

		/**
		 * Displays the widget settings controls on the widget panel.
		 * Make use of the get_field_id() and get_field_name() function
		 * when creating your form elements. This handles the confusing stuff.
		 *
		 * @param array $instance Widget instance args.
		 */
		function form( $instance ) {

			/* Set up some default widget settings. */
			$defaults = $this->get_defaults();
			$instance = wp_parse_args( (array) $instance, $defaults ); ?>

            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', WPSCORE_PLUGIN_DOMAIN ) ?></label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                       name="<?php echo $this->get_field_name( 'title' ); ?>"
                       value="<?php echo $instance['title']; ?>"/>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'telephone' ); ?>"><?php _e( 'Telephone:', WPSCORE_PLUGIN_DOMAIN ) ?> </label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'telephone' ); ?>"
                       name="<?php echo $this->get_field_name( 'telephone' ); ?>"
                       value="<?php echo $instance['telephone']; ?>"/>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'fax' ); ?>"><?php _e( 'Fax:', WPSCORE_PLUGIN_DOMAIN ) ?></label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'fax' ); ?>"
                       name="<?php echo $this->get_field_name( 'fax' ); ?>" value="<?php echo $instance['fax']; ?>"/>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'email' ); ?>"><?php _e( 'Email:', WPSCORE_PLUGIN_DOMAIN ) ?> </label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'email' ); ?>"
                       name="<?php echo $this->get_field_name( 'email' ); ?>"
                       value="<?php echo $instance['email']; ?>"/>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'address' ); ?>"><?php _e( 'Address:', WPSCORE_PLUGIN_DOMAIN ) ?> </label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'address' ); ?>"
                       name="<?php echo $this->get_field_name( 'address' ); ?>"
                       value="<?php echo $instance['address']; ?>"/>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'skype' ); ?>"><?php _e( 'Skype:', WPSCORE_PLUGIN_DOMAIN ) ?> </label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'skype' ); ?>"
                       name="<?php echo $this->get_field_name( 'skype' ); ?>"
                       value="<?php echo $instance['skype']; ?>"/>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php _e( 'Text:', WPSCORE_PLUGIN_DOMAIN ) ?> </label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'text' ); ?>"
                       name="<?php echo $this->get_field_name( 'text' ); ?>"
                       value="<?php echo esc_html( $instance['text'] ); ?>"/>
            </p>

			<?php
		}
	}
}