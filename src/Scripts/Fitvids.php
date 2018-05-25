<?php
/**
 * Fitvids Script Class File
 *
 * Adds Fitvids support.
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Scripts
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Scripts;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Scripts\Fitvids' ) ) {
	/**
	 * Class Fitvids
	 *
	 * @package WPS\Scripts
	 */
	class Fitvids extends Script {

		/**
		 * Script Handle.
		 *
		 * @var string
		 */
		public $handle = 'fitvids';

		/**
		 * Fitvids constructor.
		 *
		 * @param array $args Array of script args.
		 */
		protected function __construct( $args = array() ) {
			add_action( 'the_content', array( $this, 'check_content' ) );
			add_action( 'embed_oembed_html', array( $this, 'embed_oembed_html' ) );

			$suffix = self::get_suffix();
			$args   = wp_parse_args( array(
				'deps'   => array( 'jquery', ),
				'inline' => '(function($){$(document).ready(function(){$(".entry-content,.wp-video-shortcode,.flex-caption").fitVids()})})(jQuery);'
			), $this->get_defaults( "/core/assets/js/jquery.fitvids{$suffix}.js" ) );

			parent::__construct( $args );
		}

		/**
		 * Checks oembed HTML for youtube or vimeo.
		 *
		 * @param string $html HTML output of oembed.
		 *
		 * @return mixed
		 */
		public function embed_oembed_html( $html ) {
			if ( preg_match( '/<iframe[^>]*src=\"[^\"]*(youtu[.]?be|vimeo.com).*<\/iframe>/', $html ) ) {
				add_filter( 'wps_fitvids_conditional', '__return_true' );
				$this->enqueue();
			}

			return $html;
		}

		/**
		 * Checks the content to see if the video shortcode exists.
		 *
		 * @param string $content The content.
		 *
		 * @return mixed
		 */
		public function check_content( $content ) {
			if ( has_shortcode( $content, 'video' ) ) {
				add_filter( 'wps_fitvids_conditional', '__return_true' );
				$this->enqueue();
			}

			return $content;
		}

		/**
		 * Conditional callback.
		 *
		 * Adds the script if it has a category of video or if it is on the front page.
		 *
		 * @return mixed
		 */
		protected function conditional() {
			$post = get_post();

			return (bool) apply_filters( 'wps_fitvids_conditional', ( has_category( 'video', $post ) || is_front_page() ) );
		}
	}
}
