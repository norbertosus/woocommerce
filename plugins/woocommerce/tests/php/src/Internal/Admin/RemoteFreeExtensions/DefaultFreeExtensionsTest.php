<?php

namespace Automattic\WooCommerce\Tests\Internal\Admin\RemoteFreeExtensions;

use Automattic\WooCommerce\Internal\Admin\RemoteFreeExtensions\DefaultFreeExtensions;
use Automattic\WooCommerce\Internal\Admin\RemoteFreeExtensions\EvaluateExtension;
use WC_Unit_Test_Case;

/**
 * DefaultFreeExtensions test.
 *
 * @class DefaultFreeExtensionsTest
 */
class DefaultFreeExtensionsTest extends WC_Unit_Test_Case {

	/**
	 * Mock of bundles of extensions to recommend.
	 *
	 * We will test the `is_visible` conditions on the plugins themselves.
	 *
	 * @var array
	 */
	private $bundles_mock;

	/**
	 * Set up.
	 */
	public function setUp(): void {
		parent::setUp();

		update_option( 'woocommerce_default_country', 'US:CA' );

		/*
		 * Required for the BaseLocationCountryRuleProcessor
		 * to not return false for "US:CA" country-state combo.
		 */
		update_option( 'woocommerce_store_address', 'foo' );

		update_option( 'active_plugins', array( 'foo/foo.php' ) );

		$this->bundles_mock = array(
			array(
				'key'     => 'foo',
				'title'   => 'Test bundle',
				'plugins' => array(
					DefaultFreeExtensions::get_plugin( 'woocommerce-services:shipping' ),
					DefaultFreeExtensions::get_plugin( 'woocommerce-services:tax' ),
				),
			),
		);
	}

	/**
	 * Tests the default behavior of recommending WCS&T twice - as a shipping solution and a tax solution.
	 *
	 * @return void
	 */
	public function test_wcservices_is_recommended_as_both_shipping_and_tax() {
		$recommended_plugin_slugs = $this->get_recommended_plugin_slugs( $this->bundles_mock );

		$this->assertCount( 2, $recommended_plugin_slugs );
		$this->assertContains( 'woocommerce-services:shipping', $recommended_plugin_slugs );
		$this->assertContains( 'woocommerce-services:tax', $recommended_plugin_slugs );
	}

	/**
	 * Asserts WCS&T is not recommended in unsupported countries.
	 *
	 * @return void
	 */
	public function test_wcservices_is_not_recommended_if_in_an_unsupported_country() {
		update_option( 'woocommerce_default_country', 'FOO' );

		$recommended_plugin_slugs = $this->get_recommended_plugin_slugs( $this->bundles_mock );

		$this->assertCount( 0, $recommended_plugin_slugs );
	}

	/**
	 * Asserts WCS&T is not recommended if WooCommerce Shipping is active.
	 *
	 * @return void
	 */
	public function test_wcservices_is_not_recommended_if_woocommerce_shipping_is_active() {
		update_option( 'active_plugins', array( 'woocommerce-shipping/woocommerce-shipping.php' ) );

		$recommended_plugin_slugs = $this->get_recommended_plugin_slugs( $this->bundles_mock );

		$this->assertCount( 0, $recommended_plugin_slugs );
	}

	/**
	 * Asserts WCS&T is not recommended if WooCommerce Tax is active.
	 *
	 * @return void
	 */
	public function test_wcservices_is_not_recommended_if_woocommerce_tax_is_active() {
		update_option( 'active_plugins', array( 'woocommerce-tax/woocommerce-tax.php' ) );

		$recommended_plugin_slugs = $this->get_recommended_plugin_slugs( $this->bundles_mock );

		$this->assertCount( 0, $recommended_plugin_slugs );
	}

	/**
	 * Asserts WCS&T is not recommended if WooCommerce Shipping and WooCommerce Tax are both active.
	 *
	 * @return void
	 */
	public function test_wcservices_is_not_recommended_if_both_woocommerce_shipping_and_woocommerce_tax_are_active() {
		update_option( 'active_plugins', array( 'woocommerce-shipping/woocommerce-shipping.php', 'woocommerce-tax/woocommerce-tax.php' ) );

		$recommended_plugin_slugs = $this->get_recommended_plugin_slugs( $this->bundles_mock );

		$this->assertCount( 0, $recommended_plugin_slugs );
	}

	/**
	 * Asserts WCS&T is not recommended if it is already active.
	 *
	 * @return void
	 */
	public function test_wcservices_is_not_recommended_if_it_is_already_active() {
		update_option( 'active_plugins', array( 'woocommerce-services/woocommerce-services.php' ) );

		$recommended_plugin_slugs = $this->get_recommended_plugin_slugs( $this->bundles_mock );

		$this->assertCount( 0, $recommended_plugin_slugs );
	}

	/**
	 * Asserts that in the core profiler, WCS&T is displayed as a shipping solution and a tax solution even if active.
	 *
	 * @return void
	 */
	public function test_core_profiler_recommends_wcservices_as_shipping_and_tax_even_if_already_active() {
		update_option( 'active_plugins', array( 'woocommerce-services/woocommerce-services.php' ) );

		$bundles_with_core_profiler_fields_mock               = $this->bundles_mock;
		$bundles_with_core_profiler_fields_mock[0]['plugins'] = DefaultFreeExtensions::with_core_profiler_fields( $this->bundles_mock[0]['plugins'] );

		$recommended_plugin_slugs = $this->get_recommended_plugin_slugs( $bundles_with_core_profiler_fields_mock );

		$this->assertCount( 2, $recommended_plugin_slugs );
	}

	/**
	 * Asserts that in the core profiler, WCS&T is not displayed if WooCommerce Shipping is active.
	 *
	 * @return void
	 */
	public function test_core_profiler_does_not_recommend_wcservices_at_all_if_woocommerce_shipping_is_active() {
		update_option( 'active_plugins', array( 'woocommerce-shipping/woocommerce-shipping.php' ) );

		$bundles_with_core_profiler_fields_mock               = $this->bundles_mock;
		$bundles_with_core_profiler_fields_mock[0]['plugins'] = DefaultFreeExtensions::with_core_profiler_fields( $this->bundles_mock[0]['plugins'] );

		$recommended_plugin_slugs = $this->get_recommended_plugin_slugs( $bundles_with_core_profiler_fields_mock );

		$this->assertCount( 0, $recommended_plugin_slugs );
	}

	/**
	 * Asserts that in the core profiler, WCS&T is not displayed if WooCommerce Tax is active.
	 *
	 * @return void
	 */
	public function test_core_profiler_does_not_recommend_wcservices_at_all_if_woocommerce_tax_is_active() {
		update_option( 'active_plugins', array( 'woocommerce-tax/woocommerce-tax.php' ) );

		$bundles_with_core_profiler_fields_mock               = $this->bundles_mock;
		$bundles_with_core_profiler_fields_mock[0]['plugins'] = DefaultFreeExtensions::with_core_profiler_fields( $this->bundles_mock[0]['plugins'] );

		$recommended_plugin_slugs = $this->get_recommended_plugin_slugs( $bundles_with_core_profiler_fields_mock );

		$this->assertCount( 0, $recommended_plugin_slugs );
	}

	/**
	 * Evaluates bundles passed as argument and extracts keys of recommended plugins.
	 *
	 * @param array $bundles Array of bundles to evaluate.
	 *
	 * @return array
	 */
	private function get_recommended_plugin_slugs( $bundles ) {
		/*
		 * The json_decode( json_encode() ) call is a trick that
		 * DefaultFreeExtensions::get_all uses to convert the entire
		 * associative array into an object.
		 */
		// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- We're duplicating what the tested class does.
		$bundles = json_decode( json_encode( $bundles ) );
		$results = EvaluateExtension::evaluate_bundles( $bundles );

		return array_map(
			function ( $plugin ) {
				return $plugin->key;
			},
			$results['bundles'][0]['plugins']
		);
	}
}
