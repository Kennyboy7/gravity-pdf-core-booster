<?php

namespace GFPDF\Tests\EnhancedLabels;

use GFPDF\Plugins\CoreBooster\EnhancedLabels\Options\DisplayFieldLabel;

use WP_UnitTestCase;

use GF_Fields;

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
 * @package GFPDF\Tests\EnhancedLabels
 *
 * @group   labels
 */
class TestDisplayFieldLabel extends WP_UnitTestCase {

	/**
	 * @var DisplayFieldLabel
	 * @since 1.0
	 */
	private $class;

	/**
	 * @since 1.0
	 */
	public function setUp() {
		$this->class = new DisplayFieldLabel();
		$this->class->set_logger( $GLOBALS['GFPDF_Test']->log );
		$this->class->init();
	}

	/**
	 * @since 1.0
	 */
	public function test_add_actions() {
		$this->assertEquals( 10, has_action( 'gfpdf_pre_html_fields', [
			$this->class,
			'apply_settings',
		] ) );

		$this->assertEquals( 10, has_action( 'gfpdf_post_html_fields', [
			$this->class,
			'reset_settings',
		] ) );
	}

	/**
	 * @since 1.0
	 */
	public function test_change_field_label_display() {
		$field = GF_Fields::create( [
			'label'      => 'Public',
			'adminLabel' => 'Private',
		] );

		$this->assertEquals( 'Public', $this->class->change_field_label_display( $field->label, $field ) );

		/* Set to display admin field */
		$this->class->apply_settings( '', [ 'settings' => [ 'field_label_display' => 'Admin' ] ] );
		$this->assertEquals( 'Private', $this->class->change_field_label_display( $field->label, $field ) );

		$this->class->apply_settings( '', [ 'settings' => [ 'field_label_display' => 'Admin Empty' ] ] );
		$this->assertEquals( 'Private', $this->class->change_field_label_display( $field->label, $field ) );

		/* Check Admin Empty falls back to standard label */
		$field = GF_Fields::create( [
			'label' => 'Public',
		] );

		$this->assertEquals( 'Public', $this->class->change_field_label_display( $field->label, $field ) );

		$this->class->apply_settings( '', [ 'settings' => [ 'field_label_display' => 'No Label' ] ] );
		$this->assertEquals( '', $this->class->change_field_label_display( $field->label, $field ) );
	}

	/**
	 * @since 1.1
	 */
	public function test_change_product_field_label_display() {
		$this->assertFalse( $this->class->change_product_field_label_display( false ) );

		$this->class->apply_settings( '', [ 'settings' => [ 'field_label_display' => 'Admin' ] ] );

		$this->assertTrue( $this->class->change_product_field_label_display( false ) );
	}

	/**
	 * @since 1.0
	 */
	public function test_reset_settings() {
		$this->class->apply_settings( '', [ 'settings' => [ 'field_label_display' => 'Admin' ] ] );
		$this->assertEquals( 10, has_action( 'gfpdf_field_label', [ $this->class, 'change_field_label_display' ] ) );

		$this->class->reset_settings();
		$this->assertFalse( has_action( 'gfpdf_field_label', [ $this->class, 'change_field_label_display' ] ) );
	}
}