<?php
/**
 * Genesis Framework.
 *
 * WARNING: This file is part of the core Genesis Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package WPS\Widgets
 * @author  Travis Smith <t@wpsmith.net>
 * @license GPL-2.0+
 * @link    http://wpsmith.net
 */

namespace Site\Widgets;

use WPS\Widgets;

/**
 * Genesis Featured Post widget class.
 *
 * @since 0.1.8
 *
 * @package Genesis\Widgets
 */
class Genesis_Featured_Feature extends Widgets\Genesis_Featured_Widget {

	/**
	 * Holds the Post Type for the featured Feature.
	 *
	 * @var string
	 */
	protected $post_type = 'feature';
}
