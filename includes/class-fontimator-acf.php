<?php

/**
 * Class that handles all ACF-related issues
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/includes
 */

/**
 * Class that handles all ACF-related issues.
 *
 * @since      2.0.0
 * @package    Fontimator
 * @subpackage Fontimator/includes
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_ACF {
	public $is_enabled = false;
	protected $field_groups = array();
	protected $options_pages = array();

	protected $defaults;

	/**
	 * Initialize the fields.
	 *
	 * @since    2.0.0
	 */
	public function __construct() {

		$this->set_defaults();

		if ( function_exists( 'acf_add_local_field_group' ) ) {
			$this->is_enabled = true;
		} else {
			throw new Exception( 'This plugin requires ACF.' );
			return false;
		}

		$this->options_pages = array(
			'fontimator-config',
		);

		$this->field_groups = array(
			'fontimator-options',
			'fontimator-font-options',
			'fontimator-font-price-formulas',
			'fontimator-free-downloads',
			'fontimator-complete-family',
		);

		global $mc4wp_aaa;
		if ( $mc4wp_aaa ) {
			$this->field_groups[] = 'fontimator-mc-gifts';
		}
	}

	public function config() {
		$this->load_options_pages();
		$this->load_field_groups();
	}

	protected function set_defaults() {
		$this->defaults = array(
			'fontprice_ratios' => array(
				'family' => 0.75,
				'otf-2' => 1,
				'otf-4' => 1.5,
				'otf-10' => 2,
				'otf-39' => 3,
				'otf-inf' => 10,
				'web-100k' => 2.5,
				'web-1m' => 4,
				'web-inf' => 10,
				'app-50k' => 3,
				'app-500k' => 5,
				'app-inf' => 10,
			),
			'fonts_directory' => 'fonts',
			'site_prefix' => 'zm',
			'site_name' => 'Fontimator',
			'license_attribute' => 'license',
			'weight_attribute' => 'weight',
			'specimen_filename_prefix' => 'specimen',
			'display_family_discount_percentage' => 1,
		);
	}

	public function get_defaults() {
		return $this->defaults;
	}

	public function get_default( $field, $context = null ) {
		return $this->get_defaults()[ $field ];
	}

	public function get_acf_field( $field, $context = null ) {
		if ( $this->is_enabled ) {
			return get_field( $field, $context );
		}
		return null;
	}

	public function get_field( $field, $context = null ) {
		if ( null !== $this->get_acf_field( $field, $context ) ) {
			return $this->get_acf_field( $field, $context );
		}
		return $this->get_default( $field, $context );
	}

	protected function load_field_groups() {
		if ( $this->is_enabled ) {
			foreach ( $this->field_groups as $field_group ) {
				require_once plugin_dir_path( __FILE__ ) . 'acf-config/' . $field_group . '.php';
			}
		}
	}
	protected function load_options_pages() {
		if ( $this->is_enabled ) {
			foreach ( $this->options_pages as $options_page ) {
				require_once plugin_dir_path( __FILE__ ) . 'acf-config/' . $options_page . '.php';
			}
		}
	}

	protected function get_font_price_formulas_subfields() {
		$fontprice_ratio_fields = array();

		$fontimator_default_font_price_formulas = $this->get_default( 'fontprice_ratios' );
		$licenses = array(
			'000' => 'family',
		);

		$terms = get_terms(
			array(
				'taxonomy' => 'pa_' . FTM_LICENSE_ATTRIBUTE,
				'hide_empty' => false,
				'fields' => 'id=>slug',
			)
		);

		if ( $terms && is_array( $terms ) ) {
			$licenses += $terms;
		}

		foreach ( $licenses as $id => $key ) {
			$default = $fontimator_default_font_price_formulas[ $key ];
			$fontprice_ratio_fields[ 'fontprice_ratios_' . $key ] = array(
				'key' => 'fontprice_ratios_' . $key,
				'label' => $key,
				'name' => $key,
				'type' => 'number',
				'instructions' => "A fraction <strong>of 1</strong>, default: <code>$default</code>",
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				// 'default_value' => $default,
				'default_value' => '',
				'placeholder' => '',
				'prepend' => '',
				'append' => '',
				'min' => 0,
				'max' => '',
				'step' => '',
			);
		}

		$fontprice_ratio_fields['fontprice_ratios_family']['instructions'] .= '<br />(This is multiplied by the amount of weights this family has)';

		return $fontprice_ratio_fields;
	}

}
