<?php

namespace WPS\Plugins;

use WPS\Core\Singleton;

class Gravity_Forms extends Singleton {

	public function __construct() {
		add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );
	}

}