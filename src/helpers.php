<?php

namespace WPS;

add_filter( 'genesis_pre_get_image', __NAMESPACE__ . '\genesis_pre_get_image', 10, 2 );
/**
 * Short-circuit genesis_get_image to output oEmbed featured items.
 *
 * @param false|string $pre Default false.
 * @param array $args Array of supported args.
 *
 * Supported $args keys are:
 *
 *  - format   - string, default is 'html'
 *  - size     - string, default is 'full'
 *  - num      - integer, default is 0
 *  - attr     - string, default is ''
 *  - fallback - mixed, default is 'first-attached'
 *
 * @return false|string
 */
function genesis_pre_get_image( $pre, $args ) {
	$meta = get_post_meta( get_the_ID(), 'video', true );
	if ( $meta && 'html' === $args['format'] ) {
		if ( isset( $args['size'] ) ) {
			if ( is_string( $args['size'] ) ) {
				$size = WPS\get_image_size( $args['size'] );

				return wp_oembed_get( $meta, $size );
			} else if ( is_array( $args['size'] ) ) {
				return wp_oembed_get( $meta, array(
					'height' => $args['size'][0],
					'width'  => $args['size'][1],
				) );
			}
		}

		return wp_oembed_get( $meta );
	}

	return $pre;
}