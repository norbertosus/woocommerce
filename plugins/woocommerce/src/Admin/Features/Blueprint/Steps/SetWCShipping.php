<?php

declare( strict_types = 1);

namespace Automattic\WooCommerce\Admin\Features\Blueprint\Steps;

use Automattic\WooCommerce\Blueprint\Steps\Step;

/**
 * Class SetWCShipping
 *
 * This class sets WooCommerce shipping settings and extends the Step class.
 *
 * @package Automattic\WooCommerce\Admin\Features\Blueprint\Steps
 */
class SetWCShipping extends Step {

	/**
	 * Shipping methods.
	 *
	 * @var array $methods Shipping methods.
	 */
	private array $methods;

	/**
	 * Shipping locations.
	 *
	 * @var array $locations Shipping locations.
	 */
	private array $locations;

	/**
	 * Shipping zones.
	 *
	 * @var array $zones Shipping zones.
	 */
	private array $zones;

	/**
	 * Constructor.
	 *
	 * @param array $methods Shipping methods.
	 * @param array $locations Shipping locations.
	 * @param array $zones Shipping zones.
	 */
	public function __construct( $methods, $locations, $zones ) {
		$this->methods   = $methods;
		$this->locations = $locations;
		$this->zones     = $zones;
	}

	/**
	 * Prepare the JSON array for the step.
	 *
	 * @return array The JSON array.
	 */
	public function prepare_json_array() {
		return array(
			'step'   => static::get_step_name(),
			'values' => array(
				'methods'   => $this->methods,
				'locations' => $this->locations,
				'zones'     => $this->zones,
			),
		);
	}

	/**
	 * Get the name of the step.
	 *
	 * @return string
	 */
	public static function get_step_name() {
		return 'setWCShipping';
	}

	/**
	 * Get the schema for the step.
	 *
	 * @param int $version Optional version number of the schema.
	 * @return array The schema array.
	 */
	public static function get_schema( $version = 1 ) {
		return array(
			'type'       => 'object',
			'properties' => array(
				'step'   => array(
					'type' => 'string',
					'enum' => array( static::get_step_name() ),
				),
				'values' => array(
					'type'       => 'object',
					'properties' => array(
						'classes'            => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'term_taxonomy_id' => array( 'type' => 'string' ),
									'term_id'          => array( 'type' => 'string' ),
									'taxonomy'         => array( 'type' => 'string' ),
									'description'      => array( 'type' => 'string' ),
									'parent'           => array( 'type' => 'string' ),
									'count'            => array( 'type' => 'string' ),
								),
								'required'   => array( 'term_taxonomy_id', 'term_id', 'taxonomy', 'description', 'parent', 'count' ),
							),
						),
						'terms'              => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'term_id'    => array( 'type' => 'string' ),
									'name'       => array( 'type' => 'string' ),
									'slug'       => array( 'type' => 'string' ),
									'term_group' => array( 'type' => 'string' ),
								),
								'required'   => array( 'term_id', 'name', 'slug', 'term_group' ),
							),
						),
						'local_pickup'       => array(
							'type'       => 'object',
							'properties' => array(
								'general'   => array(
									'type'       => 'object',
									'properties' => array(
										'enabled'    => array( 'type' => 'string' ),
										'title'      => array( 'type' => 'string' ),
										'tax_status' => array( 'type' => 'string' ),
										'cost'       => array( 'type' => 'string' ),
									),
								),
								'locations' => array(
									'type'  => 'array',
									'items' => array(
										'type'       => 'object',
										'properties' => array(
											'name'    => array( 'type' => 'string' ),
											'address' => array(
												'type' => 'object',
												'properties' => array(
													'address_1' => array( 'type' => 'string' ),
													'city' => array( 'type' => 'string' ),
													'state' => array( 'type' => 'string' ),
													'postcode' => array( 'type' => 'string' ),
													'country' => array( 'type' => 'string' ),
												),
											),
											'details' => array( 'type' => 'string' ),
											'enabled' => array( 'type' => 'boolean' ),
										),
									),
								),
							),
						),
						'shipping_methods'   => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'zone_id'      => array( 'type' => 'string' ),
									'instance_id'  => array( 'type' => 'string' ),
									'method_id'    => array( 'type' => 'string' ),
									'method_order' => array( 'type' => 'string' ),
									'is_enabled'   => array( 'type' => 'string' ),
								),
								'required'   => array( 'zone_id', 'instance_id', 'method_id', 'method_order', 'is_enabled' ),
							),
						),
						'shipping_locations' => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'location_id'   => array( 'type' => 'string' ),
									'zone_id'       => array( 'type' => 'string' ),
									'location_code' => array( 'type' => 'string' ),
									'location_type' => array( 'type' => 'string' ),
								),
								'required'   => array( 'location_id', 'zone_id', 'location_code', 'location_type' ),
							),
						),
						'shipping_zones'     => array(
							'type'  => 'array',
							'items' => array(
								'type'       => 'object',
								'properties' => array(
									'zone_id'    => array( 'type' => 'string' ),
									'zone_name'  => array( 'type' => 'string' ),
									'zone_order' => array( 'type' => 'string' ),
								),
								'required'   => array( 'zone_id', 'zone_name', 'zone_order' ),
							),
						),
					),
				),
			),
			'required'   => array( 'step', 'values' ),
		);
	}
}
