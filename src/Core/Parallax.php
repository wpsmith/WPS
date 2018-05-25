<?php
/**
 * Parallax Class
 *
 * Sets up Parallax within WordPress.
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

use WPS\Scripts;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Parallax' ) ) {
	/**
	 * Class Parallax
	 *
	 * @package WPS\Core
	 */
	class Parallax {

		/**
		 * Section ID.
		 *
		 * @var string
		 */
		private $section_id;

		/**
		 * Setting prefix.
		 *
		 * @var string
		 */
		private $setting_prefix;

		/**
		 * Sections.
		 *
		 * @var array
		 */
		private $sections;

		/**
		 * Parallax constructor.
		 *
		 * @param string $section_id Section ID.
		 * @param array  $sections   Array of sections.
		 */
		public function __construct( $section_id, $sections ) {
			if ( ! genesis_is_customizer() ) {
				return;
			}

			$this->section_id     = self::sanitize_name( $section_id );
			$this->sections       = $sections;
			$this->setting_prefix = $this->section_id . '_';
			add_action( 'customize_register', array( $this, 'register_bg_sections' ) );

			Scripts\Parallax::get_instance();
		}

		/**
		 * Sanitizes the name.
		 *
		 * @param string $name Name.
		 *
		 * @return string Sanitized name.
		 */
		public static function sanitize_name( $name ) {

			return str_replace( '-', '_', sanitize_title_with_dashes( $name ) );

		}

		/**
		 * Registers background sections.
		 *
		 * @param \WP_Customize_Manager $wp_customize WP Customerizer.
		 */
		public function register_bg_sections( $wp_customize ) {

			$wp_customize->add_panel(
				$this->section_id,
				array(
					'title'       => __( 'Site Options', CHILD_TEXT_DOMAIN ),
					'description' => '',
					'priority'    => 202,
				)
			);

			foreach ( (array) $this->sections as $section ) {

				$id      = self::sanitize_name( $section['id'] );
				$setting = $this->setting_prefix . 'setting_' . $id;

				// Create section.
				$section_name = $this->section_id . '_' . $id;
				$wp_customize->add_section(
					$section_name,
					array(
						'title' => __( 'Background for ' . $section['name'], CHILD_TEXT_DOMAIN ),
						'panel' => $this->section_id,
					)
				);

				// Create color setting.
				$wp_customize->add_setting(
					$setting . '_color',
					array(
						'default'           => '',
						'sanitize_callback' => 'sanitize_hex_color',
					)
				);

				// Background Color Control.
				$wp_customize->add_control(
					new \WP_Customize_Color_Control(
						$wp_customize,
						$id . '_color',
						array(
							'label'       => __( 'Background Color', CHILD_TEXT_DOMAIN ),
							'settings'    => $setting . '_color',
							'section'     => $section_name,
							'description' => sprintf(
								'<p>%s:</p><code>$background_color = get_theme_mod( "%s" ); <br/>echo \'' .
								htmlspecialchars( '<div class="parallax-window" style="background-color:$background_color"><div class="site-inner">' ) .
								'\';<br/>/* DO SOMETHING */ <br/>echo "' . htmlspecialchars( '</div></div>' ) . '";</code>',
								__( "To use on the frontend", CHILD_TEXT_DOMAIN ),
								$setting . '_color'
							),
						)
					)
				);

				// Create image setting.
				$wp_customize->add_setting(
					$setting . '_image',
					array(
						'default'           => '',
						'sanitize_callback' => __NAMESPACE__ . '\Parallax::sanitize_bgi',
					)
				);

				// Image Control.
				$wp_customize->add_control(
					new \WP_Customize_Image_Control(
						$wp_customize,
						$id . '_image',
						array(
							'label'       => __( 'Background Image', CHILD_TEXT_DOMAIN ),
							'settings'    => $setting . '_image',
							'section'     => $section_name,
							'description' => sprintf(
								'<p>%s:</p><code>$background_image_url = get_theme_mod( "%s" ); <br/>echo \'' .
								htmlspecialchars( '<div class="parallax-window" data-speed="0.1" data-parallax="scroll" data-image-src="$background_image_url"><div class="site-inner">' ) .
								'\';<br/>/* DO SOMETHING */ <br/>echo "' . htmlspecialchars( '</div></div>' ) . '</code>',
								__( "To use on the frontend", CHILD_TEXT_DOMAIN ),
								$setting . '_image'
							),
						)
					)
				);
			}
		}

		/**
		 * Sanitizes bgi.
		 *
		 * @param string                $image    Image file name or path.
		 * @param \WP_Customize_Setting $settings Settings.
		 *
		 * @return mixed
		 */
		public static function sanitize_bgi( $image, $settings ) {

			$mimes = array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif'          => 'image/gif',
				'png'          => 'image/png',
				'bmp'          => 'image/bmp',
				'tif|tiff'     => 'image/tiff',
				'ico'          => 'image/x-icon',
			);
			$file  = wp_check_filetype( $image, $mimes );

			return ( $file['ext'] ? $image : $settings->default );

		}

		/**
		 * Gets background open tag, 2 <div> tags.
		 *
		 * @param string $id             Background ID.
		 * @param string $color_or_image Whether color or image.
		 *
		 * @return string
		 */
		public static function get_bg_open( $id, $color_or_image ) {
			$background_color = get_theme_mod( 'wps_parallax_setting_before_' . self::sanitize_name( $id ) . '_' . $color_or_image );

			return "<div class='parallax-window' style='background-color:$background_color'><div class='site-inner'>";
		}

		/**
		 * Gets background close tags, 2 </div> tags.
		 *
		 * @return string
		 */
		public static function get_bg_image_close() {

			return '</div></div>';

		}
	}
}
