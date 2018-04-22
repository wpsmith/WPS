<?php
/**
 * WordPress Cleanup
 *
 * This file registers any custom taxonomies
 *
 * @package      Core_Mu
 * @since        1.0.0
 * @link         https://github.com/wpsmith
 * @author       Travis Smith <t@wpsmith.net>
 * @copyright    Copyright (c) 2017, Travis Smith
 * @license      http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace WPS\Core;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WordPress_Cleanup')) {
    class WordPress_Cleanup extends Cleanup
    {

        public $widgets;
        public $dashboard;
        public $menu;
        public $admin_bar;

        public function plugins_loaded()
        {
            $this->remove_allowed_tags();

            add_action('add_meta_boxes', array($this, 'reset_excert_metabox'), 10, 2);
            add_filter('wpseo_metabox_prio', array($this, 'wpseo_metabox_priority'));

            add_action('envira_gallery_loaded', array($this, 'envira_gallery_loaded'), 9999);
            add_action('soliloquy_init', array($this, 'soliloquy_init'), 9999);
        }

        /**
         * WP SEO Metabox Priority
         * @return string
         */
        public function wpseo_metabox_priority()
        {
            return 'default';
        }

        public function template_redirect()
        {
            global $wp_query, $post;

            if (is_attachment()) {
                $post_parent = $post->post_parent;

                if ($post_parent) {
                    wp_redirect(get_permalink($post->post_parent), 301);
                    exit;
                }

                $wp_query->set_404();

                return;
            }

            if (is_author() || is_date()) {
                $wp_query->set_404();
            }
        }

        public function remove_allowed_tags()
        {
            global $allowedtags;

            unset($allowedtags['cite']);
            unset($allowedtags['q']);
            unset($allowedtags['del']);
            unset($allowedtags['abbr']);
            unset($allowedtags['acronym']);
        }

        public static function reset_excert_metabox($post_type, $post)
        {

            add_meta_box('postexcerpt', __('Excerpt'), 'post_excerpt_meta_box', null, 'normal', 'high');

        }

        protected static function is_plugin_page()
        {
            global $plugin_page;

            return (
                (isset($plugin_page) && defined('DOING_AJAX') && !DOING_AJAX) ||
                (isset($plugin_page) && !defined('DOING_AJAX')) ||
                (isset($_SERVER['DOCUMENT_URI']) && strpos($_SERVER['DOCUMENT_URI'], '/wp-admin/plugins.php') > -1) ||
                (isset($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], '/wp-admin/plugins.php') > -1)
            );
        }

        public function envira_gallery_loaded()
        {
            if (!self::is_plugin_page()) {
                return;
            }
            self::remove_envira_updater();
        }


        public function soliloquy_init()
        {
            if (!self::is_plugin_page()) {
                return;
            }

            self::remove_soliloquy_updater();
        }

        public static function remove_soliloquy_updater()
        {
            remove_action('soliloquy_updater', 'soliloquy_custom_css_updater');
            remove_action('soliloquy_updater', 'soliloquy_themes_updater');

            if (class_exists('Soliloquy_Defaults')) {
                remove_action('soliloquy_updater', array(\Soliloquy_Defaults::get_instance(), 'updater'));
            }
            if (class_exists('Soliloquy_Dynamic')) {
                remove_action('soliloquy_updater', array(\Soliloquy_Dynamic::get_instance(), 'updater'));
            }
        }

        public static function remove_envira_updater()
        {
            if (class_exists('Envira_Albums')) {
                remove_action('envira_gallery_updater', array(\Envira_Albums::get_instance(), 'updater'));
            }

            remove_action('envira_gallery_updater', 'envira_custom_css_updater');
            add_action('envira_gallery_updater', 'envira_gallery_themes_updater');

            if (class_exists('Envira_Defaults')) {
                remove_action('envira_gallery_updater', array(\Envira_Defaults::get_instance(), 'updater'));
            }

            if (class_exists('Envira_Dynamic')) {
                remove_action('envira_gallery_updater', array(\Envira_Dynamic::get_instance(), 'updater'));
            }

            if (class_exists('Envira_Featured_Content')) {
                remove_action('envira_gallery_updater', array(\Envira_Featured_Content::get_instance(), 'updater'));
            }

            if (class_exists('Envira_Fullscreen')) {
                remove_action('envira_gallery_updater', array(\Envira_Fullscreen::get_instance(), 'updater'));
            }
            if (class_exists('Envira_Lightroom')) {
                remove_action('envira_gallery_updater', array(\Envira_Lightroom::get_instance(), 'updater'));
            }
            if (class_exists('Envira_Proofing')) {
                remove_action('envira_gallery_updater', array(\Envira_Proofing::get_instance(), 'updater'));
            }
            if (class_exists('Envira_Slideshow')) {
                remove_action('envira_gallery_updater', array(\Envira_Slideshow::get_instance(), 'updater'));
            }
            if (class_exists('Envira_Social')) {
                remove_action('envira_gallery_updater', array(\Envira_Social::get_instance(), 'updater'));
            }
            if (class_exists('Envira_Tags')) {
                remove_action('envira_gallery_updater', array(\Envira_Tags::get_instance(), 'updater'));
            }
            if (class_exists('Envira_Videos')) {
                remove_action('envira_gallery_updater', array(\Envira_Videos::get_instance(), 'updater'));
            }
            if (class_exists('Envira_Watermarking')) {
                remove_action('envira_gallery_updater', array(\Envira_Watermarking::get_instance(), 'updater'));
            }
            if (class_exists('Envira_ZIP_Importer')) {
                remove_action('envira_gallery_updater', array(\Envira_ZIP_Importer::get_instance(), 'updater'));
            }
            if (class_exists('Envira_Zoom')) {
                remove_action('envira_gallery_updater', array(\Envira_Zoom::get_instance(), 'updater'));
            }
        }
    }
}
