<?php
/**
 * Schema Class File
 *
 * Contains schema definitions.
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Schema
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Schema;

use WPS\Core\Singleton;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Schema\Schemas' ) ) {
	/**
	 * Class Schemas.
	 *
	 * @package WPS\Schema
	 */
	class Schemas extends Singleton {
		/**
		 * Array of types.
		 *
		 * @var array
		 */
		protected $types = array();

		/**
		 * Array of props.
		 *
		 * @var array
		 */
		protected $props = array();

		/**
		 * Array of scopes.
		 *
		 * @var array
		 */
		protected $scopes = array();

		/**
		 * Schemas constructor.
		 */
		public function __construct() {
			$this->set_types();
			$this->set_props();
			$this->set_scopes();
		}

		/**
		 * Gets the schema for a particular item.
		 *
		 * @param string $schema Schema name.
		 *
		 * @return array|mixed
		 */
		protected function get_the_schema( $schema ) {
			if ( isset( $this->types[ $schema ] ) ) {
				return $this->types[ $schema ];
			} elseif ( isset( $this->props[ $schema ] ) ) {
				return $this->props[ $schema ];
			} elseif ( isset( $this->scopes[ $schema ] ) ) {
				return $this->scopes[ $schema ];
			}

			return array();
		}

		/**
		 * Gets the schema.
		 *
		 * @param string $schema Schema name.
		 *
		 * @return array|mixed
		 */
		public function get_schema( $schema ) {
			$s = $this->get_the_schema( $schema );
			if ( ! empty( $s ) ) {
				return $s;
			}

			$alt_schema = str_replace( 'entry-', '', $schema );
			if ( $alt_schema !== $schema && isset( $this->schemas[ $alt_schema ] ) ) {
				return $this->get_the_schema( $alt_schema );
			}

			return array();
		}

		/**
		 * Gets the schema itemscope.
		 *
		 * @param string $itemscope Item Scope.
		 * @param string $href      Schema URL.
		 * @param string $itemprop  Item Property.
		 *
		 * @return array
		 */
		protected function get_schema_itemscope( $itemscope = 'itemscope', $href = null, $itemprop = null ) {
			$schema = array();

			if ( $href ) {
				$schema['href'] = $href;
			}
			if ( $itemscope ) {
				$schema['itemscope'] = $itemscope;
			}
			if ( $itemprop ) {
				$schema['itemprop'] = $itemprop;
			}

			return $schema;
		}

		/**
		 * Gets item property.
		 *
		 * @param bool|string $itemprop Item Property.
		 *
		 * @return array
		 */
		protected function get_schema_itemprop( $itemprop = false ) {
			if ( $itemprop || '' === $itemprop ) {
				return array( 'itemprop' => $itemprop );
			}

			return array();
		}

		/**
		 * Gets item type.
		 *
		 * @param string $itemtype  Item type.
		 * @param bool   $itemprop  Item property.
		 * @param string $itemscope Item scope.
		 *
		 * @return array
		 */
		protected function get_schema_itemtype( $itemtype, $itemprop = false, $itemscope = 'itemscope' ) {
			$schema = array_merge(
				array( 'itemtype' => $itemtype ),
				$this->get_schema_itemscope( $itemscope ),
				$this->get_schema_itemprop( $itemprop )
			);

			return $schema;
		}

		/**
		 * Sets the class properties from schema.json.
		 */
		protected function set_types() {
			// Maybe fix to use WP_Filesystem & wp_vip_file_get_contents.
			$str     = file_get_contents( plugin_dir_path( __FILE__ ) . 'schema.json' );
			$schemas = json_decode( $str, true );
			$this->set_type( $schemas );
			$this->set_alt_types();
		}

		/**
		 * Gets the type's suffix.
		 *
		 * @param string $name Type.
		 *
		 * @return mixed|string
		 */
		private function get_type_suffix( $name ) {
			$suffixes = array(
				'Action',
				'Business',
				'Service',
				'Event',
				'Agent',
				'Agency',
				'Center',
				'Location',
				'Office',
				'Station',
				'Node',
				'Object',
				'Article',
			);
			foreach ( $suffixes as $suffix ) {
				if ( $this->has_type_suffix( $name, $suffix ) ) {
					return $suffix;
				}
			}

			return '';
		}

		/**
		 * Determines whether a type as a specific suffix.
		 *
		 * @param string $name   Type name.
		 * @param string $suffix Suffix.
		 *
		 * @return bool
		 */
		private function has_type_suffix( $name, $suffix ) {
			return ( $suffix !== $name && false !== strpos( $name, $suffix ) );
		}

		/**
		 * Adds sanitizes itemtype to types.
		 *
		 * @param string $name     Type name.
		 * @param array  $itemtype Item type.
		 */
		protected function add_to_types( $name, $itemtype ) {
			$ln = strtolower( $name );
			if ( ! isset( $this->types[ $ln ] ) ) {
				$this->types[ $ln ] = $itemtype;
			}
			$dn = $this->camel2dashed( $name );
			if ( ! isset( $this->types[ $dn ] ) ) {
				$this->types[ $dn ] = $itemtype;
			}
		}

		/**
		 * Sets the type.
		 *
		 * @param string $name     Type name.
		 * @param string $suffix   Suffix.
		 * @param array  $itemtype Item Type.
		 */
		protected function set_the_type( $name, $suffix, $itemtype ) {
			// Set the original.
			$this->add_to_types( $name, $itemtype );

			if ( '' !== $suffix && false !== strpos( $name, $suffix ) ) {
				$this->add_to_types( $name . $suffix, $itemtype );
			}
		}

		/**
		 * Sets the type recursively.
		 *
		 * @param array $schemas Schemas.
		 */
		protected function set_type( $schemas ) {
			foreach ( $schemas as $schema ) {
				if ( '' !== $schema['name'] ) {
					// e.g., ScreeningEvent, HealthAndBeautyBusiness, CableOrSatelliteService.
					$name = $schema['name'];

					// e.g. screening-event, health-and-beauty-business, cable-or-satellite-service.
					$dashed_name     = $this->camel2dashed( $name );
					$schema_itemtype = $this->get_schema_itemtype( 'http://schema.org/' . $name );

					$this->add_to_types( $name, $schema_itemtype );

					// e.g., Event, Business, Service.
					$suffix = $this->get_type_suffix( $schema['name'] );

					// Handle And/Or Splits.
					if ( false !== strpos( $dashed_name, '-or-' ) ) {
						// cable, satellite, service => cable, satellite.
						$parts = explode( '-', $this->camel2dashed( str_replace( 'Or', '', $name ) ) );
						array_pop( $parts );
						foreach ( $parts as $part ) {
							// Add single.
							$this->add_to_types( $part, $schema_itemtype );

							// Add with suffix.
							$this->add_to_types( $part . $suffix, $schema_itemtype );
						}
					} elseif ( false !== strpos( $dashed_name, '-and-' ) ) {
						// health, beauty, business => health, beauty.
						$parts = explode( '-', $this->camel2dashed( str_replace( 'And', '', $name ) ) );
						array_pop( $parts );
						foreach ( $parts as $part ) {
							// Add single.
							$this->add_to_types( $part, $schema_itemtype );

							// Add with suffix.
							$this->add_to_types( $part . $suffix, $schema_itemtype );
						}
					} elseif ( '' !== $suffix ) {
						$this->add_to_types( str_replace( $suffix, '', $name ), $schema_itemtype );
					}

					if ( ! empty( $schema['children'] ) ) {
						$this->set_type( $schema['children'] );
					}
				}
			}
		}

		/**
		 * Changes camel-cased string to dashed string.
		 *
		 * @param string $str String to be sanitized.
		 *
		 * @return string
		 */
		private function camel2dashed( $str ) {
			return strtolower( preg_replace( '/([a-zA-Z])(?=[A-Z])/', '$1-', $str ) );
		}

		/**
		 * Sets alternative types.
		 */
		protected function set_alt_types() {
			$types       = array(
				// ItemTypes.
				'storage'          => $this->get_schema_itemtype( 'http://schema.org/SelfStorage', '' ),
				'tourist'          => $this->get_schema_itemtype( 'http://schema.org/TouristInformationCenter', '' ),
				'press'            => $this->get_schema_itemtype( 'http://schema.org/NewsArticle', '' ),
				'press-release'    => $this->get_schema_itemtype( 'http://schema.org/NewsArticle', '' ),
				'location'         => $this->get_schema_itemtype( 'http://schema.org/Place', '' ),
				'address'          => $this->get_schema_itemtype( 'http://schema.org/PostalAddress', 'address' ),
				'book-illustrator' => $this->get_schema_itemtype( 'http://schema.org/Person', 'illustrator' ),
				'rating'           => $this->get_schema_itemtype( 'http://schema.org/AggregateRating', 'aggregateRating' ),

			);
			$this->types = array_merge( $this->types, $types );
		}

		/**
		 * Sets props.
		 */
		protected function set_props() {
			$this->props = array(
				'street'           => $this->get_schema_itemprop( 'streetAddress' ),
				'city'             => $this->get_schema_itemprop( 'addressLocality' ),
				'state'            => $this->get_schema_itemprop( 'addressRegion' ),
				'country'          => $this->get_schema_itemprop( 'addressCountry' ),
				'book'             => $this->get_schema_itemtype( 'Book' ),
				'title'            => $this->get_schema_itemprop( 'name' ),
				'author'           => $this->get_schema_itemprop( 'author' ),
				'offer'            => $this->get_schema_itemtype( 'http://schema.org/Offer' ),
				'name'             => $this->get_schema_itemprop( 'name' ),
				'date-start'       => $this->get_schema_itemprop( 'startDate' ),
				'date-end'         => $this->get_schema_itemprop( 'endDate' ),
				'book-edition'     => $this->get_schema_itemprop( 'endDate' ),
				'book-genre'       => $this->get_schema_itemprop( 'genre' ),
				'book-description' => $this->get_schema_itemprop( 'desc' ),
				'book-isbn'        => $this->get_schema_itemprop( 'isbn' ),
				'book-language'    => $this->get_schema_itemprop( 'inLanguage' ),
				'book-published'   => $this->get_schema_itemprop( 'datePublished' ),
				'book-publisher'   => $this->get_schema_itemprop( 'publisher' ),
				'book-pages'       => $this->get_schema_itemprop( 'numberOfPages' ),
				'price'            => $this->get_schema_itemprop( 'price' ),
				'price-range'      => $this->get_schema_itemprop( 'priceRange' ),
				'price-currency'   => $this->get_schema_itemprop( 'priceCurrency' ),
				'telephone'        => $this->get_schema_itemprop( 'telephone' ),
				'hours'            => $this->get_schema_itemprop( 'openingHours' ),
			);
		}

		/**
		 * Sets scopes.
		 */
		protected function set_scopes() {
			$this->scopes = array(
				'book-format-paperback' => $this->get_schema_itemscope( 'bookFormat', 'http://schema.org/Paperback' ),
				'book-format-hardback'  => $this->get_schema_itemscope( 'bookFormat', 'http://schema.org/Hardback' ),
				'book-format-ebook'     => $this->get_schema_itemscope( 'bookFormat', 'http://schema.org/EBook' ),
				'availability'          => $this->get_schema_itemscope( 'InStock', 'http://schema.org/InStock', 'availability' ),
			);

		}

	}
}
