<?php
/**
 * Created by PhpStorm.
 * User: travis.smith
 * Date: 1/7/18
 * Time: 1:40 PM
 */

namespace WPS\Core;

use WPS\Scripts;

if (!class_exists('Parallax')) {
	class Parallax
	{

		private $section_id;
		private $setting_prefix;
		private $sections;

		public function __construct($section_id, $sections)
		{
			if (!genesis_is_customizer()) {
				return;
			}

			$this->section_id = self::sanitize_name($section_id);
			$this->sections = $sections;
			$this->setting_prefix = $this->section_id . '_';
			add_action('customize_register', array($this, 'register_bg_sections'));

			Scripts\Parallax::get_instance();
		}

		public static function sanitize_name($name)
		{
			return str_replace('-', '_', sanitize_title_with_dashes($name));
		}

		public function register_bg_sections($wp_customize)
		{

			// Add new section on customizer
//		$wp_customize->add_panel(
//			'wps_child_theme_settings', array(
//				'priority'   => 5,
//				'capability' => 'edit_theme_options',
//				'title'      => __( 'Theme Options', CHILD_TEXT_DOMAIN ),
//				'description' => ''
//			)
//		);

			$wp_customize->add_panel(
				$this->section_id,
				array(
					'title' => __('Site Options', CHILD_TEXT_DOMAIN),
					'description' => '',
					'priority' => 202,
				)
			);

//		// Create section
//		$wp_customize->add_section(
//			$this->section_id,
//			array(
//				'title'    => __( 'Front Page Options', CHILD_TEXT_DOMAIN ),
//				'priority'    => 202,
//			)
//		);

			foreach ((array)$this->sections as $section) {

				$id = self::sanitize_name($section['id']);
				$setting = $this->setting_prefix . 'setting_' . $id;


				// Create section
				$section_name = $this->section_id . '_' . $id;
				$wp_customize->add_section(
					$section_name,
					array(
						'title' => __('Background for ' . $section['name'], CHILD_TEXT_DOMAIN),
						'panel' => $this->section_id,
					)
				);

				// Create color setting
				$wp_customize->add_setting(
					$setting . '_color',
					array(
						'default' => '',
						'sanitize_callback' => 'sanitize_hex_color'
					)
				);

				$wp_customize->add_control(
					new \WP_Customize_Color_Control(
						$wp_customize,
						$id . '_color',
						array(
							'label' => __('Background Color', CHILD_TEXT_DOMAIN),
							'settings' => $setting . '_color',
							'section' => $section_name,
							'description' => sprintf(
								'<p>%s:</p><code>$background_color = get_theme_mod( "%s" ); <br/>echo \'' .
								htmlspecialchars('<div class="parallax-window" style="background-color:$background_color"><div class="site-inner">') .
								'\';<br/>/* DO SOMETHING */ <br/>echo "' . htmlspecialchars('</div></div>') . '";</code>',
								__("To use on the frontend", CHILD_TEXT_DOMAIN),
								$setting . '_color'
							),
						)
					)
				);

				// Create image setting
				$wp_customize->add_setting(
					$setting . '_image',
					array(
						'default' => '',
						'sanitize_callback' => __NAMESPACE__ . '\Parallax::sanitize_bgi',
					)
				);

				$wp_customize->add_control(
					new \WP_Customize_Image_Control(
						$wp_customize,
						$id . '_image',
						array(
							'label' => __('Background Image', CHILD_TEXT_DOMAIN),
							'settings' => $setting . '_image',
							'section' => $section_name,
							'description' => sprintf(
								'<p>%s:</p><code>$background_image_url = get_theme_mod( "%s" ); <br/>echo \'' .
								htmlspecialchars('<div class="parallax-window" data-speed="0.1" data-parallax="scroll" data-image-src="$background_image_url"><div class="site-inner">') .
								'\';<br/>/* DO SOMETHING */ <br/>echo "' . htmlspecialchars('</div></div>') . '</code>',
								__("To use on the frontend", CHILD_TEXT_DOMAIN),
								$setting . '_image'
							),
						)
					)
				);
			}
		}

		public static function sanitize_bgi($image, $setting)
		{

			$mimes = array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif' => 'image/gif',
				'png' => 'image/png',
				'bmp' => 'image/bmp',
				'tif|tiff' => 'image/tiff',
				'ico' => 'image/x-icon'
			);
			$file = wp_check_filetype($image, $mimes);

			return ($file['ext'] ? $image : $setting->default);
		}

		public static function get_bg_open($id, $color_or_image)
		{
			$background_color = get_theme_mod('wps_parallax_setting_before_' . self::sanitize_name($id) . '_' . $color_or_image);

			return "<div class='parallax-window' style='background-color:$background_color'><div class='site-inner'>";
		}

		public static function get_bg_image_close()
		{
			return '</div></div>';
		}
	}
}