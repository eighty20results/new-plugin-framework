<?php

/**
 * Created by PhpStorm.
 * User: sjolshag
 * Date: 10/8/16
 * Time: 8:12 AM
 */
class e20rLicenseTests extends WP_UnitTestCase {

	private $license_list = array();
	private $lic;

	public function testConnectToServer() {

		// Test the trial license
		$test_active = $this->lic->checkLicense('e20r_default_license');
		$this->assertTrue( $test_active );
	}

	public function testActivateLicense() {

		// Activate a license
		$license_name = 'e20r_activate_license';
		$product_name = "Test License to Activate";

		$settings = array(
			'first_name'   => 'Thomas',
			'last_name'    => 'Sjolshagen',
			'email'        => 'test@eighty20results.com'
		);

		// Test activation of license
		$test_activate = $this->lic->activateExistingLicenseOnServer( $license_name, $product_name, $settings );
		$this->assertTrue( $test_activate );
	}

	public function testCheckActiveLicense() {


		$license_name = 'e20r_activate_license';
		$product_name = "Test License to Activate";

		// Checking that the newly activated license is valid
		$this->assertTrue( $this->lic->checkLicense( $license_name ) );
	}

	public function testDeactivateLicense() {

		$license_name = 'e20r_activate_license';

		// Deactivating license
		$test_deactivate = $this->lic->deactivateExistingLicenseOnServer( $license_name );
		$this->assertTrue( $test_deactivate );
	}

	public function testNonExistentLicense() {

		$license_name = 'e20r_activate_license';

		$this->assertFalse( $this->lic->deleteLicense( $license_name ));

	}

	public function testValidateLicenseSettings() {


		$this->lic->addOptionsPage();
		$this->lic->registerSettings();
		echo $this->lic->licensePage();

	}

	public function testLicenseRegistration() {

		$license_name = 'e20r_activate_license';

		e20rLicense::registerLicense( $license_name, "Test License Activation");
	}

	public function tearDown() {

		$counter = array( 1, 2, 3, 4 );

		foreach( $this->license_list as $k => $settings ) {
			$this->lic->deleteLicense( $k );
		}

		parent::tearDown();
	}

	public function setUp() {

		$this->lic = e20rLicense::get_instance();
		$license = $this->lic->generateDefaultLicense('e20r_test_license', 'Test License #' );

		$this->license_list = array();
		$this->license_list['e20r_default_license'] = $license;
		$counter = array( 1, 2, 3, 4 );

		foreach( $counter as $k ) {
			$this->license_list["e20r_test_license_{$k}"] = $license;
			$this->license_list["e20r_test_license_{$k}"]['fulltext_name'] += $k;
			$this->license_list["e20r_test_license_{$k}"]['status'] = 'active';

			$this->lic->addLicense( "e20r_test_license_{$k}",  $this->license_list["e20r_test_license_{$k}"] );
		}
	}
}
