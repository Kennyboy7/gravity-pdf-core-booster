<?php

namespace GFPDF\Tests\FieldSelector;

use GFPDF\Plugins\CoreBooster\FieldSelector\Options\AddFields;
use GFPDF\Plugins\CoreBooster\Shared\DoesTemplateHaveGroup;

use GPDFAPI;

use WP_UnitTestCase;

/**
 * @package     Gravity PDF Core Booster
 * @copyright   Copyright (c) 2019, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1
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
 * Class TestAddFields
 *
 * @package GFPDF\Tests\FieldDescription
 *
 * @group   selector
 */
class TestAddFields extends WP_UnitTestCase {

	/**
	 * @var AddFields
	 * @since 1.1
	 */
	private $class;

	/**
	 * @var int
	 * @since 1.1
	 */
	private $form_id;

	/**
	 * @since 1.1
	 */
	public function setUp() {

		$this->form_id = \GFAPI::add_form( json_decode( file_get_contents( __DIR__ . '/../../../json/products.json' ), true ) );

		/* Setup our class mocks */
		$form_settings = $this->getMockBuilder( '\GFPDF\Model\Model_Form_Settings' )
		                      ->setConstructorArgs( [
			                      GPDFAPI::get_form_class(),
			                      GPDFAPI::get_log_class(),
			                      GPDFAPI::get_data_class(),
			                      GPDFAPI::get_options_class(),
			                      GPDFAPI::get_misc_class(),
			                      GPDFAPI::get_notice_class(),
			                      GPDFAPI::get_templates_class(),
		                      ] )
		                      ->setMethods( [ 'get_template_name_from_current_page' ] )
		                      ->getMock();

		$form_settings->method( 'get_template_name_from_current_page' )
		              ->will( $this->onConsecutiveCalls( 'zadani', 'sabre', 'other' ) );

		$template = $this->getMockBuilder( '\GFPDF\Helper\Helper_Templates' )
		                 ->setConstructorArgs( [
			                 GPDFAPI::get_log_class(),
			                 GPDFAPI::get_data_class(),
			                 GPDFAPI::get_form_class(),
		                 ] )
		                 ->setMethods( [ 'get_template_info_by_id' ] )
		                 ->getMock();

		$template->method( 'get_template_info_by_id' )
		         ->will(
			         $this->returnValueMap( [
					         [ 'zadani', [ 'group' => 'Core' ] ],
					         [ 'sabre', [ 'group' => 'Universal (Premium)' ] ],
					         [ 'other', [ 'group' => 'Legacy' ] ],
				         ]
			         )
		         );

		$template = new DoesTemplateHaveGroup( $form_settings, $template );
		$template->set_logger( $GLOBALS['GFPDF_Test']->log );
		$this->class = new AddFields( $template, GPDFAPI::get_form_class() );
		$this->class->set_logger( $GLOBALS['GFPDF_Test']->log );
		$this->class->init();
	}

	/**
	 * @since 1.1
	 */
	public function test_add_filter() {
		$this->assertEquals( 9999, has_filter( 'gfpdf_form_settings_custom_appearance', [
			$this->class,
			'add_template_option',
		] ) );
	}

	/**
	 * @since 1.1
	 */
	public function test_add_template_option() {
		$_REQUEST['id'] = $this->form_id;

		/* Check our option is included */
		$results = $this->class->add_template_option( [] );
		$this->assertCount( 2, $results );
		$this->assertArrayHasKey( 'form_field_selector', $results );

		/* Check our option is included when using a universal template */
		$this->assertCount( 2, $this->class->add_template_option( [] ) );

		/* Check our option is not included when using a non-core or universal template */
		$this->assertCount( 0, $this->class->add_template_option( [] ) );

		/* Check our option is included when we use our overriding filter */
		add_filter( 'gfpdf_override_field_selector_fields', '__return_true' );
		$this->assertCount( 2, $this->class->add_template_option( [] ) );
		remove_filter( 'gfpdf_override_field_selector_fields', '__return_true' );

		/* Check the setting is disabled when no Gravity Form found */
		$_REQUEST['id'] = 0;
		$results    = $this->class->add_template_option( [] );
		$this->assertCount( 0, $results );
	}

	/**
	 * @since 1.1
	 */
	public function test_form_field_list() {
		$_REQUEST['id'] = $this->form_id;
		$results    = $this->class->add_template_option( [] );

		$fields = $results['form_field_selector']['options'];
		$this->assertCount( 6, $fields );
		$this->assertEquals( 'ID2: Product Name', $fields[2] );
	}
}