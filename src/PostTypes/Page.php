<?php

namespace WPS\PostTypes;

use WPS\Core;
use StoutLogic\AcfBuilder\FieldsBuilder;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Page extends Core\Singleton {

	public $post_type = 'page';

	public function __construct() {
		add_action( 'core_acf_fields', array( $this, 'core_acf_fields' ) );
	}

	public function core_acf_fields( $fields ) {
		$content = $this->new_fields_builder();
		$content
			->addTextarea( 'page_description_value', array(
				'label'       => __( 'Page Description', SITE_MU_TEXT_DOMAIN ),
				'description' => __( 'Define slide order. Ex. 1,2,3,4,...', SITE_MU_TEXT_DOMAIN ),
			) )
			->setLocation( 'post_type', '==', $this->post_type );

		$fields->builder[] = $content;
	}

}
