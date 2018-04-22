<?php
/**
 * Icon Widget
 *
 * Icon Widget creates a new WordPress widget that displays a Fontawesome icon,
 * title and description. Select the size, color and text-alignment with easy
 * to use dropdown options.
 *
 * @package   Icon_Widget
 * @author    SEO Themes <info@seothemes.com>
 * @license   GPL-2.0+
 * @link      https://seothemes.com
 * @copyright 2017 SEO Themes
 *
 * Plugin Name:       Icon Widget
 * Plugin URI:        https://seothemes.com
 * Description:       Displays a Fontawesome icon with a title and description
 * Version:           1.0.7
 * Author:            SEO Themes
 * Author URI:        https://seothemes.com
 * Text Domain:       icon-widget
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Widget class.
 */
class Icon_Widget extends WP_Widget {

	/**
	 * Unique identifier for the widget.
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * widget file.
	 *
	 * @since 1.0.0
	 *
	 * @var   string
	 */
	protected $widget_slug = 'icon-widget';

	/**
	 * Constructor
	 *
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		parent::__construct(
			$this->get_widget_slug(),
			__( 'Icon', 'icon-widget' ),
			array(
				'classname'   => 'icon_widget',
				'description' => __( 'Displays an icon with a title and description.', 'icon-widget' ),
			)
		);

		// Register admin styles and scripts.
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register site styles and scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );

	}

	/**
	 * Return the widget slug.
	 *
	 * @since  1.0.0
	 *
	 * @return Plugin slug variable.
	 */
	public function get_widget_slug() {

		return $this->widget_slug;

	}

	/*
	 |--------------------------------------------------------------------------
	 | Widget API Functions
	 |--------------------------------------------------------------------------
	 */

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array $args  The array of form elements.
	 * @param array $instance The current instance of the widget.
	 */
	public function widget( $args, $instance ) {

		if ( ! isset( $args['widget_id'] ) ) {

			$args['widget_id'] = $this->id;

		}

		echo $args['before_widget'];

		printf( '<div class="icon-widget" style="text-align: %s">', esc_attr( $instance['align'] ) );

		printf( '<i class="fa %1$s fa-%2$s" style="color: %3$s"></i>', esc_attr( $instance['icon'] ), esc_attr( $instance['size'] ), esc_attr( $instance['color'] ) );

		echo apply_filters( 'icon_widget_line_break', true ) ? '<br>' : '';

		echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];

		echo apply_filters( 'icon_widget_wpautop', true ) ? wp_kses_post( wpautop( $instance['content'] ) ) : wp_kses_post( $instance['content'] );

		echo '</div>';

		echo $args['after_widget'];

	}

	/**
	 * Process the widget's options to be saved.
	 *
	 * @param array $new_instance The new instance of values to be generated via the update.
	 * @param array $old_instance The previous instance of values before the update.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		// Update widget's old values with new incoming values.
		$instance['title']   = sanitize_text_field( $new_instance['title'] );
		$instance['content'] = wp_kses_post( $new_instance['content'] );
		$instance['icon']    = sanitize_html_class( $new_instance['icon'] );
		$instance['size']    = sanitize_html_class( $new_instance['size'] );
		$instance['align']   = sanitize_html_class( $new_instance['align'] );
		$instance['color']   = sanitize_hex_color( $new_instance['color'] );

		return $instance;

	}

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array $instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {

		// Define default values for your variables.
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'   => '',
				'content' => '',
				'icon'    => apply_filters( 'icon_widget_default_icon', '\f000' ),
				'size'    => apply_filters( 'icon_widget_default_size', '2x' ),
				'align'   => apply_filters( 'icon_widget_default_align', 'left' ),
				'color'   => apply_filters( 'icon_widget_default_color', '#333333' ),
			)
		);

		// Store the values of the widget in their own variable.
		$title   = $instance['title'];
		$content = $instance['content'];
		$icon    = $instance['icon'];
		$size    = $instance['size'];
		$align   = $instance['align'];
		$color   = $instance['color'];

		// Display the admin form.
		?>

		<div class="wrapper">

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
					<?php esc_html_e( 'Title:', 'icon-widget' ); ?>
				</label>
				<br/>
				<input type="text" class='widefat' id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>">
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'content' ) ); ?>">
					<?php esc_html_e( 'Content:', 'icon-widget' ); ?>
				</label>
				<br/>
				<textarea class='widefat' id="<?php echo esc_attr( $this->get_field_id( 'content' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'content' ) ); ?>" value="<?php echo esc_attr( $this->get_field_name( 'content' ) ); ?>"><?php echo esc_textarea( $content ); ?></textarea>
			</p>

			<?php

			$settings = get_option( 'icon_widget_settings' );
			$font     = $settings['font'];

			// Load the array of icon glyphs.
			include( plugin_dir_path( __DIR__ ) . 'includes/' . $font . '.php' );

			?>

			<script type="text/javascript">
                jQuery( document ).ready( function( $ ) {
                    $( '#widgets-right .select-picker' ).selectpicker( {
                        iconBase: 'fa',
                        dropupAuto: false
                    } );
                });
			</script>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'icon' ) ); ?>">
					<?php esc_html_e( 'Icon:', 'icon-widget' ); ?>
				</label>
				<br/>
				<select class='select-picker widefat' id="<?php echo esc_attr( $this->get_field_id( 'icon' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'icon' ) ); ?>" data-live-search="true">

					<?php foreach ( $icons as $icon ) : ?>

						<option data-icon='<?php echo esc_attr( $icon ); ?>' value="<?php echo esc_attr( $icon ); ?>" <?php echo ( $instance['icon'] === $icon ) ? 'selected' : ''; ?>><?php echo esc_html( str_replace( array( '-', 'fa ', 'ion ' ), array( ' ', '', '' ), $icon ) ); ?></option>

					<?php endforeach; ?>

				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>">
					<?php esc_html_e( 'Size:', 'icon-widget' ); ?>
				</label>
				<br/>
				<select class='widefat' id="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'size' ) ); ?>" type="text">

					<option value='lg' <?php echo ( 'lg' === $size ) ? 'selected' : ''; ?>>
						lg
					</option>
					<option value='2x' <?php echo ( '2x' === $size ) ? 'selected' : ''; ?>>
						2x
					</option>
					<option value='3x' <?php echo ( '3x' === $size ) ? 'selected' : ''; ?>>
						3x
					</option>
					<option value='4x' <?php echo ( '4x' === $size ) ? 'selected' : ''; ?>>
						4x
					</option>
					<option value='5x' <?php echo ( '5x' === $size ) ? 'selected' : ''; ?>>
						5x
					</option>

				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'align' ) ); ?>">
					<?php esc_html_e( 'Align:', 'icon-widget' ); ?>
				</label>
				<br/>
				<select class='widefat' id="<?php echo esc_attr( $this->get_field_id( 'align' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'align' ) ); ?>" type="text">

					<option value='left' <?php echo ( 'left' === $align ) ? 'selected' : ''; ?>>
						Left
					</option>
					<option value='center' <?php echo ( 'center' === $align ) ? 'selected' : ''; ?>>
						Center
					</option>
					<option value='right' <?php echo ( 'right' === $align ) ? 'selected' : ''; ?>>
						Right
					</option>

				</select>
			</p>

			<script type="text/javascript">
                jQuery( document ).ready( function( $ ) {
                    $( '#widgets-right .color-picker' ).wpColorPicker();
                } );
			</script>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'color' ) ); ?>">
					<?php esc_html_e( 'Color:', 'icon-widget' ); ?>
				</label>
				<br/>
				<input class="color-picker" type="text" id="<?php echo esc_attr( $this->get_field_id( 'color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'color' ) ); ?>" value="<?php echo esc_attr( $instance['color'] ); ?>" />
			</p>

		</div>


		<?php

	}

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		if ( ! is_customize_preview() && get_current_screen()->id !== 'widgets' ) {

			return;

		}

		wp_enqueue_style( 'bootstrap', plugins_url( 'assets/css/bootstrap.min.css', __FILE__ ), array( 'wp-color-picker' ) );

		wp_enqueue_style( 'bootstrap-select', plugins_url( 'assets/css/bootstrap-select.min.css', __FILE__ ), array( 'bootstrap' ) );

		// Icon font.
		$settings = get_option( 'icon_widget_settings' );
		$font     = $settings['font'];

		if ( 'font-awesome' === $font ) {

			wp_enqueue_style( 'font-awesome', plugins_url( 'assets/css/font-awesome.min.css', __FILE__ ) );

		} elseif ( 'line-awesome' === $font ) {

			wp_enqueue_style( 'line-awesome', plugins_url( 'assets/css/line-awesome.min.css', __FILE__ ) );

		} elseif ( 'ionicons' === $font ) {

			wp_enqueue_style( 'ionicons', plugins_url( 'assets/css/ionicons.min.css', __FILE__ ) );

		} elseif ( 'streamline' === $font ) {

			wp_enqueue_style( 'streamline', plugins_url( 'assets/css/streamline.min.css', __FILE__ ) );

		}

	}

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		if ( ! is_customize_preview() && get_current_screen()->id !== 'widgets' ) {

			return;

		}

		wp_enqueue_script( 'bootstrap', plugins_url( 'assets/js/bootstrap.min.js', __FILE__ ), array( 'jquery', 'wp-color-picker' ) );

		wp_enqueue_script( 'bootstrap-select', plugins_url( 'assets/js/bootstrap-select.min.js', __FILE__ ), array( 'bootstrap' ) );

	}

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {

		$settings = get_option( 'icon_widget_settings' );
		$font     = $settings['font'];

		if ( 'font-awesome' === $font ) {

			wp_enqueue_style( 'font-awesome', plugins_url( 'assets/css/font-awesome.min.css', __FILE__ ) );

		} elseif ( 'line-awesome' === $font ) {

			wp_enqueue_style( 'line-awesome', plugins_url( 'assets/css/line-awesome.min.css', __FILE__ ) );

		} elseif ( 'ionicons' === $font ) {

			wp_enqueue_style( 'ionicons', plugins_url( 'assets/css/ionicons.min.css', __FILE__ ) );

		} elseif ( 'streamline' === $font ) {

			wp_enqueue_style( 'streamline', plugins_url( 'assets/css/streamline.min.css', __FILE__ ) );

		}

	}

}

// Register settings.
include( plugin_dir_path( __FILE__ ) . 'includes/settings.php' );

// Add shortcode.
include( plugin_dir_path( __FILE__ ) . 'includes/shortcode.php' );

// Register widget.
add_action( 'widgets_init', create_function( '', 'register_widget("Icon_Widget");' ) );

// Hooks fired when the Widget is activated and deactivated.
register_activation_hook( __FILE__, array( 'Icon_Widget', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Icon_Widget', 'deactivate' ) );
