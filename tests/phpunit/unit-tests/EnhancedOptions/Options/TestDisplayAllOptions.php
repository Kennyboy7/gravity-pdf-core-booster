<?php

namespace GFPDF\Tests\EnhancedOptions;

use GFPDF\Plugins\CoreBooster\EnhancedOptions\Options\DisplayAllOptions;

use WP_UnitTestCase;

/**
 * @package     Gravity PDF Core Booster
 * @copyright   Copyright (c) 2019, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
    This file is part of Gravity PDF Core Booster.

    Copyright (c) 2019, Blue Liquid Designs

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Class TestDisplayAllOptions
 *
 * @package GFPDF\Tests\EnhancedOptions
 *
 * @group   options
 */
class TestDisplayAllOptions extends WP_UnitTestCase {

	/**
	 * @var DisplayAllOptions
	 * @since 1.0
	 */
	private $class;

	/**
	 * @since 1.0
	 */
	public function setUp() {
		$this->class = new DisplayAllOptions();
		$this->class->set_logger( $GLOBALS['GFPDF_Test']->log );
		$this->class->init();
	}

	/**
	 * @since 1.0
	 */
	public function test_add_actions() {
		$this->assertEquals( 10, has_action( 'gfpdf_pre_html_fields', [
			$this->class,
			'save_settings',
		] ) );

		$this->assertEquals( 10, has_action( 'gfpdf_post_html_fields', [
			$this->class,
			'reset_settings',
		] ) );
	}

	/**
	 * @since 1.0
	 */
	public function test_add_filter() {
		$this->assertEquals( 10, has_filter( 'gfpdf_field_class', [
			$this->class,
			'maybe_autoload_class',
		] ) );
	}

	/**
	 * @since 1.0
	 */
	public function test_settings_store() {

		$this->assertNull( $this->class->get_settings() );

		$this->class->save_settings( null, [ 'settings' => 'saved' ] );
		$this->assertEquals( 'saved', $this->class->get_settings() );

		$this->class->reset_settings();
		$this->assertNull( $this->class->get_settings() );
	}

	/**
	 * @since 1.0
	 */
	public function test_setting_store_actions() {
		do_action( 'gfpdf_pre_html_fields', '', [ 'settings' => 'pre_html' ] );
		$this->assertEquals( 'pre_html', $this->class->get_settings() );

		do_action( 'gfpdf_post_html_fields' );
		$this->assertNull( $this->class->get_settings() );
	}

	/**
	 * @param $expected
	 * @param $field
	 *
	 * @dataProvider provider_maybe_autoload_class
	 *
	 * @since        1.0
	 */
	public function test_maybe_autoload_class( $expected, $field ) {

		/* Stub our settings */
		$settings = [
			'settings' => [
				'show_all_options' => [
					'Radio'       => 'Radio',
					'Select'      => 'Select',
					'Checkbox'    => 'Checkbox',
					'Multiselect' => 'Multiselect',
				],
			],
		];

		$this->class->save_settings( [], $settings );

		/* We aren't passing in the Gravity PDF field class so we need to cheat to ensure these are available */
		$field->gform = \GPDFAPI::get_form_class();
		$field->misc  = \GPDFAPI::get_misc_class();

		/* Check we still get blank when the incorrect field is passed */
		$results = $this->class->maybe_autoload_class( $field, $field, [ 'form_id' => 0 ] );
		$this->assertEquals( $expected, get_class( $results ) );
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	public function provider_maybe_autoload_class() {
		return [
			[ 'GF_Field', new \GF_Field() ],
			[ 'GFPDF\Plugins\CoreBooster\EnhancedOptions\Fields\AllRadio', new \GF_Field_Radio() ],
			[ 'GFPDF\Plugins\CoreBooster\EnhancedOptions\Fields\AllSelect', new \GF_Field_Select() ],
			[ 'GFPDF\Plugins\CoreBooster\EnhancedOptions\Fields\AllCheckbox', new \GF_Field_Checkbox() ],
			[ 'GFPDF\Plugins\CoreBooster\EnhancedOptions\Fields\AllMultiselect', new \GF_Field_MultiSelect() ],
		];
	}

}