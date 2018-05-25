<?php
/**
 * Site Options Class File
 *
 * Site Options.
 *
 * You may copy, distribute and modify the software as long as you track changes/dates in source files.
 * Any modifications to or software including (via compiler) GPL-licensed code must also be made
 * available under the GPL along with build & install instructions.
 *
 * @package    WPS\Widgets
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2015-2018 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://github.com/wpsmith/WPS
 * @version    1.0.0
 * @since      0.1.0
 */

namespace WPS\Site;

use StoutLogic\AcfBuilder\FieldsBuilder;
use WPS\Core\Singleton;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPS\Site\Options' ) ) {
	class Options extends Singleton {
		/**
		 * Prefix.
		 *
		 * @var string
		 */
		public $prefix = 'wps';

		/**
		 * Options constructor.
		 */
		public function __construct() {
			add_action( 'acf/init', array( $this, 'options_page' ) );
		}

		/**
		 * Options Page.
		 *
		 * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
		 */
		public function options_page() {
			if ( function_exists( 'acf_add_options_page' ) ) {

				acf_add_options_page( array(
					'page_title' => __( 'Site Settings', SITECORE_PLUGIN_DOMAIN ),
					'menu_title' => __( 'Site Settings', SITECORE_PLUGIN_DOMAIN ),
					'menu_slug'  => $this->prefix . '-settings',
					'capability' => 'edit_posts',
					'redirect'   => false,
					'position'   => 2,
				) );

			}

			$company = new FieldsBuilder( 'company', array(
				'title' => __( 'Company Settings', SITECORE_PLUGIN_DOMAIN ),
			) );
			$company
				->addMessage(
					__( 'Instructions', SITECORE_PLUGIN_DOMAIN ),
					__( 'Add a day opening and closing hours.', SITECORE_PLUGIN_DOMAIN )
				)
				->addText( 'name', array(
					'required' => 1,
					'default'  => get_bloginfo( 'name' ),
				) )
				->addText( 'phone', array(
					'placeholder' => '1-800-555-5555',
				) )
				->addEmail( 'primary_email', array(
					'placeholder' => 'example@domain.com',
				) )
				->addTextarea( 'description' )
				->addImage( 'logo' )
				->addFields( $this->get_company_type() )
				->addFields( $this->get_additional_types( 'EducationalOrganization' ) )
				->addFields( $this->get_additional_types( 'LocalBusiness' ) )
				->addFields( $this->get_additional_types( 'MedicalOrganization' ) )
				->addFields( $this->get_additional_types( 'PerformingGroup' ) )
				->addFields( $this->get_additional_types( 'SportsOrganization' ) )
				->addDatePicker( 'founding_date' )
				->addRepeater(
					'founders',
					array(
						'layout'       => 'table',
						'button_label' => __( 'Add Founder', SITECORE_PLUGIN_DOMAIN ),
					)
				)
				->addText( 'founder_name' )
				->endRepeater()
				->addRepeater(
					'operation_hours',
					array(
						'layout'       => 'table',
						'button_label' => __( 'Add Hours of Operation', SITECORE_PLUGIN_DOMAIN ),
					)
				)
				->addSelect( 'dayOfWeek' )
				->addChoice( 'su', __( 'Sunday' ) )
				->addChoice( 'mo', __( 'Monday' ) )
				->addChoice( 'tu', __( 'Tuesday' ) )
				->addChoice( 'we', __( 'Wednesday' ) )
				->addChoice( 'th', __( 'Thursday' ) )
				->addChoice( 'fr', __( 'Friday' ) )
				->addChoice( 'sa', __( 'Saturday' ) )
				->addTimePicker( 'open' )
				->addTimePicker( 'close' )
				->endRepeater()
				->addFields( $this->get_currencies() )
				->addFields( $this->get_payments_accepted() )
				->setLocation( 'options_page', '==', $this->prefix . '-settings' );


			$locations = new FieldsBuilder( 'buisness_locations', array(
				'title' => __( 'Locations', SITECORE_PLUGIN_DOMAIN ),
			) );
			$locations
				->addMessage(
					__( 'Instructions', SITECORE_PLUGIN_DOMAIN ),
					__( 'To add various business locations.', SITECORE_PLUGIN_DOMAIN )
				)
				->addRepeater(
					'locations',
					array(
						'layout'       => 'table',
						'button_label' => __( 'Add a Location', SITECORE_PLUGIN_DOMAIN ),
					)
				)
				->addText( 'name' )
				->addGoogleMap( 'location' )
				->endRepeater()
				->setLocation( 'options_page', '==', $this->prefix . '-settings' );

			$social = new FieldsBuilder( 'social', array(
				'title' => __( 'Social Settings', SITECORE_PLUGIN_DOMAIN ),
			) );
			$social
				->addMessage(
					__( 'Instructions', SITECORE_PLUGIN_DOMAIN ),
					__( 'See <a href="http://designpieces.com/2012/12/social-media-colours-hex-and-rgb/">This reference</a> for official social media account colors.', SITECORE_PLUGIN_DOMAIN )
				)
				->addRepeater(
					'social_accounts',
					array(
						'layout'       => 'table',
						'button_label' => __( 'Add Account', SITECORE_PLUGIN_DOMAIN ),
					)
				)
				->addText( 'account_name' )
				->addUrl( 'account_url' )
				->addText( 'account_icon' )
				->addColorPicker( 'account_color' )
				->endRepeater()
				->setLocation( 'options_page', '==', $this->prefix . '-settings' );

			$images = new FieldsBuilder( 'images', array(
				'title' => __( 'Image Settings', SITECORE_PLUGIN_DOMAIN ),
			) );

			global $wp_post_types;
			$post_types = array_keys( $wp_post_types );

			foreach ( $post_types as $post_type ) {
				if (
					'revision' === $post_type ||
					'nav_menu_item' === $post_type ||
					'customize_changeset' === $post_type ||
					'oembed_cache' === $post_type ||
					'user_request' === $post_type ||
					'wp_log' === $post_type ||
					'custom_css' === $post_type
				) {
					continue;
				}
				$images->addImage( "default-image-$post_type", array(
					'label' => __( 'Default Image for ' . $wp_post_types[ $post_type ]->label . ' (' . $post_type . ')', SITECORE_PLUGIN_DOMAIN ),
				) );
			}
			$images->setLocation( 'options_page', '==', $this->prefix . '-settings' );

			if ( function_exists( 'acf_add_local_field_group' ) ) {
				acf_add_local_field_group( $company->build() );
				acf_add_local_field_group( $locations->build() );
				acf_add_local_field_group( $social->build() );
//			acf_add_local_field_group( $images->build() );
			}
		}

		/**
		 * Payments Accepted
		 *
		 * @return FieldsBuilder
		 * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
		 */
		protected function get_payments_accepted() {
			$payments = new FieldsBuilder( 'payments' );
			$payments->addSelect( 'payments_accepted', array(
				'multiple' => true,
			) )
			         ->addChoice( 'http://purl.org/goodrelations/v1#ByBankTransferInAdvance', 'Bank Transfer' )
			         ->addChoice( 'http://purl.org/goodrelations/v1#Cash', 'Cash' )
			         ->addChoice( 'https://schema.org/CreditCard', 'Credit Card' )
			         ->addChoice( 'http://purl.org/goodrelations/v1#CheckInAdvance', 'Check' )
			         ->addChoice( 'http://purl.org/goodrelations/v1#ByInvoice', 'Invoice' )
			         ->addChoice( 'http://purl.org/goodrelations/v1#COD', 'COD' )
			         ->addChoice( 'http://purl.org/goodrelations/v1#DirectDebit', 'Direct Debit' )
			         ->addChoice( 'http://purl.org/goodrelations/v1#GoogleCheckout', 'Google Checkout' )
			         ->addChoice( 'http://purl.org/goodrelations/v1#PayPal', 'PayPal' )
			         ->addChoice( 'http://purl.org/goodrelations/v1#PaySwarm', 'PaySwarm' );

			$payments->addSelect( 'card', array(
				'AmericanExpress',
				'DinersClub',
				'Discover',
				'JCB',
				'MasterCard',
				'VISA',
			) )
			         ->conditional( 'payments_accepted', '==', 'Credit Card' );

			return $payments;
		}

		/**
		 * Gets currencies.
		 *
		 * @return FieldsBuilder
		 * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
		 */
		protected function get_currencies() {
			$currencies = new FieldsBuilder( 'currencies' );
			$currencies->addSelect( 'currencies_accepted', array(
				'multiple' => true,
			) )
			           ->addChoice( 'USD' )
			           ->addChoice( 'AUD' )
			           ->addChoice( 'GBP' )
			           ->addChoice( 'EUR' )
			           ->addChoice( 'JPY' )
			           ->addChoice( 'CHF' )
			           ->addChoice( 'USD' )
			           ->addChoice( 'AFN' )
			           ->addChoice( 'ALL' )
			           ->addChoice( 'DZD' )
			           ->addChoice( 'AOA' )
			           ->addChoice( 'ARS' )
			           ->addChoice( 'AMD' )
			           ->addChoice( 'AWG' )
			           ->addChoice( 'AUD' )
			           ->addChoice( 'ATS' )
			           ->addChoice( 'BEF' )
			           ->addChoice( 'AZN' )
			           ->addChoice( 'BSD' )
			           ->addChoice( 'BHD' )
			           ->addChoice( 'BDT' )
			           ->addChoice( 'BBD' )
			           ->addChoice( 'BYR' )
			           ->addChoice( 'BZD' )
			           ->addChoice( 'BMD' )
			           ->addChoice( 'BTN' )
			           ->addChoice( 'BOB' )
			           ->addChoice( 'BAM' )
			           ->addChoice( 'BWP' )
			           ->addChoice( 'BRL' )
			           ->addChoice( 'GBP' )
			           ->addChoice( 'BND' )
			           ->addChoice( 'BGN' )
			           ->addChoice( 'BIF' )
			           ->addChoice( 'XOF' )
			           ->addChoice( 'XAF' )
			           ->addChoice( 'XPF' )
			           ->addChoice( 'KHR' )
			           ->addChoice( 'CAD' )
			           ->addChoice( 'CVE' )
			           ->addChoice( 'KYD' )
			           ->addChoice( 'CLP' )
			           ->addChoice( 'CNY' )
			           ->addChoice( 'COP' )
			           ->addChoice( 'KMF' )
			           ->addChoice( 'CDF' )
			           ->addChoice( 'CRC' )
			           ->addChoice( 'HRK' )
			           ->addChoice( 'CUC' )
			           ->addChoice( 'CUP' )
			           ->addChoice( 'CYP' )
			           ->addChoice( 'CZK' )
			           ->addChoice( 'DKK' )
			           ->addChoice( 'DJF' )
			           ->addChoice( 'DOP' )
			           ->addChoice( 'XCD' )
			           ->addChoice( 'EGP' )
			           ->addChoice( 'SVC' )
			           ->addChoice( 'EEK' )
			           ->addChoice( 'ETB' )
			           ->addChoice( 'EUR' )
			           ->addChoice( 'FKP' )
			           ->addChoice( 'FIM' )
			           ->addChoice( 'FJD' )
			           ->addChoice( 'GMD' )
			           ->addChoice( 'GEL' )
			           ->addChoice( 'DMK' )
			           ->addChoice( 'GHS' )
			           ->addChoice( 'GIP' )
			           ->addChoice( 'GRD' )
			           ->addChoice( 'GTQ' )
			           ->addChoice( 'GNF' )
			           ->addChoice( 'GYD' )
			           ->addChoice( 'HTG' )
			           ->addChoice( 'HNL' )
			           ->addChoice( 'HKD' )
			           ->addChoice( 'HUF' )
			           ->addChoice( 'ISK' )
			           ->addChoice( 'INR' )
			           ->addChoice( 'IDR' )
			           ->addChoice( 'IRR' )
			           ->addChoice( 'IQD' )
			           ->addChoice( 'IED' )
			           ->addChoice( 'ILS' )
			           ->addChoice( 'ITL' )
			           ->addChoice( 'JMD' )
			           ->addChoice( 'JPY' )
			           ->addChoice( 'JOD' )
			           ->addChoice( 'KZT' )
			           ->addChoice( 'KES' )
			           ->addChoice( 'KWD' )
			           ->addChoice( 'KGS' )
			           ->addChoice( 'LAK' )
			           ->addChoice( 'LVL' )
			           ->addChoice( 'LBP' )
			           ->addChoice( 'LSL' )
			           ->addChoice( 'LRD' )
			           ->addChoice( 'LYD' )
			           ->addChoice( 'LTL' )
			           ->addChoice( 'LUF' )
			           ->addChoice( 'MOP' )
			           ->addChoice( 'MKD' )
			           ->addChoice( 'MGA' )
			           ->addChoice( 'MWK' )
			           ->addChoice( 'MYR' )
			           ->addChoice( 'MVR' )
			           ->addChoice( 'MTL' )
			           ->addChoice( 'MRO' )
			           ->addChoice( 'MUR' )
			           ->addChoice( 'MXN' )
			           ->addChoice( 'MDL' )
			           ->addChoice( 'MNT' )
			           ->addChoice( 'MAD' )
			           ->addChoice( 'MZN' )
			           ->addChoice( 'MMK' )
			           ->addChoice( 'ANG' )
			           ->addChoice( 'NAD' )
			           ->addChoice( 'NPR' )
			           ->addChoice( 'NLG' )
			           ->addChoice( 'NZD' )
			           ->addChoice( 'NIO' )
			           ->addChoice( 'NGN' )
			           ->addChoice( 'KPW' )
			           ->addChoice( 'NOK' )
			           ->addChoice( 'OMR' )
			           ->addChoice( 'PKR' )
			           ->addChoice( 'PAB' )
			           ->addChoice( 'PGK' )
			           ->addChoice( 'PYG' )
			           ->addChoice( 'PEN' )
			           ->addChoice( 'PHP' )
			           ->addChoice( 'PLN' )
			           ->addChoice( 'PTE' )
			           ->addChoice( 'QAR' )
			           ->addChoice( 'RON' )
			           ->addChoice( 'RUB' )
			           ->addChoice( 'RWF' )
			           ->addChoice( 'WST' )
			           ->addChoice( 'STD' )
			           ->addChoice( 'SAR' )
			           ->addChoice( 'RSD' )
			           ->addChoice( 'SCR' )
			           ->addChoice( 'SLL' )
			           ->addChoice( 'SGD' )
			           ->addChoice( 'SKK' )
			           ->addChoice( 'SIT' )
			           ->addChoice( 'SBD' )
			           ->addChoice( 'SOS' )
			           ->addChoice( 'ZAR' )
			           ->addChoice( 'KRW' )
			           ->addChoice( 'ESP' )
			           ->addChoice( 'LKR' )
			           ->addChoice( 'SHP' )
			           ->addChoice( 'SDG' )
			           ->addChoice( 'SRD' )
			           ->addChoice( 'SZL' )
			           ->addChoice( 'SEK' )
			           ->addChoice( 'CHF' )
			           ->addChoice( 'SYP' )
			           ->addChoice( 'TWD' )
			           ->addChoice( 'TZS' )
			           ->addChoice( 'THB' )
			           ->addChoice( 'TOP' )
			           ->addChoice( 'TTD' )
			           ->addChoice( 'TND' )
			           ->addChoice( 'TRY' )
			           ->addChoice( 'TMM' )
			           ->addChoice( 'USD' )
			           ->addChoice( 'UGX' )
			           ->addChoice( 'UAH' )
			           ->addChoice( 'UYU' )
			           ->addChoice( 'AED' )
			           ->addChoice( 'VUV' )
			           ->addChoice( 'VEB' )
			           ->addChoice( 'VND' )
			           ->addChoice( 'YER' )
			           ->addChoice( 'ZMK' )
			           ->addChoice( 'ZWD' );

			return $currencies;
		}

		/**
		 * Get Company Type.
		 *
		 * @return FieldsBuilder
		 * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
		 */
		protected function get_company_type() {
			$type = new FieldsBuilder( 'company_types' );
			$type
				->addSelect( 'company_type' )
				->addChoice( '' )
				->addChoice( 'Airline' )
				->addChoice( 'Corporation' )
				->addChoice( 'EducationalOrganization' )
				->addChoice( 'GovernmentOrganization' )
				->addChoice( 'LocalBusiness' )
				->addChoice( 'MedicalOrganization' )
				->addChoice( 'NGO' )
				->addChoice( 'PerformingGroup' )
				->addChoice( 'SportsOrganization' );

			return $type;
		}

		/**
		 * Get Additional Types.
		 *
		 * @param string $type Type.
		 *
		 * @return FieldsBuilder
		 * @throws \StoutLogic\AcfBuilder\FieldNameCollisionException
		 */
		protected function get_additional_types( $type ) {
			$types = new FieldsBuilder( 'additional_types' );

			switch ( $type ) {
				case 'EducationalOrganization':
					$types->addSelect( 'additional_type_' . strtolower( $type ), array( 'label' => __( 'Additional Type', SITECORE_PLUGIN_DOMAIN ) ) )
					      ->addChoice( 'CollegeOrUniversity' )
					      ->addChoice( 'ElementarySchool' )
					      ->addChoice( 'HighSchool' )
					      ->addChoice( 'MiddleSchool' )
					      ->addChoice( 'Preschool' )
					      ->addChoice( 'School' )
					      ->conditional( 'company_type', '==', $type );
					break;
				case 'LocalBusiness':
					$types->addSelect( 'additional_type_' . strtolower( $type ), array( 'label' => __( 'Additional Type', SITECORE_PLUGIN_DOMAIN ) ) )
					      ->addChoice( 'AnimalShelter' )
					      ->addChoice( 'AutomotiveBusiness', array(
						      'AutomotiveBusiness',
						      'AutoBodyShop',
						      'AutoDealer',
						      'AutoPartsStore',
						      'AutoRental',
						      'AutoRepair',
						      'AutoWash',
						      'GasStation',
						      'MotorcycleDealer',
						      'MotorcycleRepair',
					      ) )
					      ->addChoice( 'ChildCare' )
					      ->addChoice( 'Dentist' )
					      ->addChoice( 'DryCleaningOrLaundry' )
					      ->addChoice( 'EmergencyService', array(
						      'EmergencyService',
						      'FireStation',
						      'Hospital',
						      'PoliceStation',
					      ) )
					      ->addChoice( 'EmploymentAgency' )
					      ->addChoice( 'EntertainmentBusiness', array(
						      'EntertainmentBusiness',
						      'AdultEntertainment',
						      'AmusementPark',
						      'ArtGallery',
						      'Casino',
						      'ComedyClub',
						      'MovieTheater',
						      'NightClub',
					      ) )
					      ->addChoice( 'FinancialService', array(
						      'FinancialService',
						      'AccountingService',
						      'AutomatedTeller',
						      'BankOrCreditUnion',
						      'InsuranceAgency',
					      ) )
					      ->addChoice( 'FoodEstablishment', array(
						      'FoodEstablishment',
						      'Bakery',
						      'BarOrPub',
						      'Brewery',
						      'CafeOrCoffeeShop',
						      'FastFoodRestaurant',
						      'IceCreamShop',
						      'Restaurant',
						      'Winery',
					      ) )
					      ->addChoice( 'GovernmentOffice', array(
						      'GovernmentOffice',
						      'PostOffice',
					      ) )
					      ->addChoice( 'HealthAndBeautyBusiness', array(
						      'HealthAndBeautyBusiness',
						      'BeautySalon',
						      'DaySpa',
						      'HairSalon',
						      'HealthClub',
						      'NailSalon',
						      'TattooParlor',
					      ) )
					      ->addChoice( 'HomeAndConstructionBusiness', array(
						      'HomeAndConstructionBusiness',
						      'Electrician',
						      'GeneralContractor',
						      'HVACBusiness',
						      'HousePainter',
						      'Locksmith',
						      'MovingCompany',
						      'Plumber',
						      'RoofingContractor',
					      ) )
					      ->addChoice( 'InternetCafe' )
					      ->addChoice( 'LegalService', array(
						      'LegalService',
						      'Attorney',
						      'Notary',
					      ) )
					      ->addChoice( 'Library' )
					      ->addChoice( 'LodgingBusiness', array(
						      'LodgingBusiness',
						      'BedAndBreakfast',
						      'Campground',
						      'Hostel',
						      'Hotel',
						      'Motel',
						      'Resort',
					      ) )
					      ->addChoice( 'ProfessionalService' )
					      ->addChoice( 'RadioStation' )
					      ->addChoice( 'RealEstateAgent' )
					      ->addChoice( 'RecyclingCenter' )
					      ->addChoice( 'SelfStorage' )
					      ->addChoice( 'ShoppingCenter' )
					      ->addChoice( 'SportsActivityLocation', array(
						      'SportsActivityLocation',
						      'BowlingAlley',
						      'ExerciseGym',
						      'GolfCourse',
						      'HealthClub',
						      'PublicSwimmingPool',
						      'SkiResort',
						      'SportsClub',
						      'StadiumOrArena',
						      'TennisComplex',
					      ) )
					      ->addChoice( 'Store', array(
						      'Store',
						      'AutoPartsStore',
						      'BikeStore',
						      'BookStore',
						      'ClothingStore',
						      'ComputerStore',
						      'ConvenienceStore',
						      'DepartmentStore',
						      'ElectronicsStore',
						      'Florist',
						      'FurnitureStore',
						      'GardenStore',
						      'GroceryStore',
						      'HardwareStore',
						      'HobbyShop',
						      'HomeGoodsStore',
						      'JewelryStore',
						      'LiquorStore',
						      'MensClothingStore',
						      'MobilePhoneStore',
						      'MovieRentalStore',
						      'MusicStore',
						      'OfficeEquipmentStore',
						      'OutletStore',
						      'PawnShop',
						      'PetStore',
						      'ShoeStore',
						      'SportingGoodsStore',
						      'TireShop',
						      'ToyStore',
						      'WholesaleStore',
					      ) )
					      ->addChoice( 'TelevisionStation' )
					      ->addChoice( 'TouristInformationCenter' )
					      ->addChoice( 'TravelAgency' )
					      ->conditional( 'company_type', '==', $type );
					break;
				case 'MedicalOrganization':
					$types->addSelect( 'additional_type_' . strtolower( $type ), array( 'label' => __( 'Additional Type', SITECORE_PLUGIN_DOMAIN ) ) )
					      ->addChoice( 'Dentist' )
					      ->addChoice( 'Hospital' )
					      ->addChoice( 'Pharmacy' )
					      ->addChoice( 'Physician' )
					      ->conditional( 'company_type', '==', $type );
					break;
				case 'PerformingGroup':
					$types->addSelect( 'additional_type_' . strtolower( $type ), array( 'label' => __( 'Additional Type', SITECORE_PLUGIN_DOMAIN ) ) )
					      ->addChoice( 'DanceGroup' )
					      ->addChoice( 'MusicGroup' )
					      ->addChoice( 'TheaterGroup' )
					      ->conditional( 'company_type', '==', $type );
					break;
				case 'SportsOrganization':
					$types->addSelect( 'additional_type_' . strtolower( $type ), array( 'label' => __( 'Additional Type', SITECORE_PLUGIN_DOMAIN ) ) )
					      ->addChoice( 'SportsTeam' )
					      ->conditional( 'company_type', '==', $type );
					break;
			}

			return $types;
		}

	}
}
