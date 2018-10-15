<?php
/*
Plugin Name: Eighty/20 Results - New Plugin Framework for Paid Memberships Pro
Plugin URI: https://eighty20results.com/wordpress-plugins/new-plugin-framework/
Description: TODO - Enter description here
Version: 1.0
Author: Eighty / 20 Results by Wicked Strong Chicks, LLC <thomas@eighty20results.com>
Author URI: https://eighty20results.com/thomas-sjolshagen/
Text Domain: TOD: Enter text domain here
Domain Path: /languages
License: GPL2

	Copyright 2018. - Eighty / 20 Results by Wicked Strong Chicks, LLC (thomas@eighty20results.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

namespace E20R\New_Plugin_Framework;

use E20R\Utilities\Utilities;
use E20R\Utilities\Licensing;

defined( 'ABSPATH' ) || die( 'Cannot access plugin sources directly' );
define( 'E20R_New_Plugin_Framework_VER', '1.0' );

if ( ! class_exists( '\E20R\New_Plugin_Framework\Controller') ) {
	class Controller {
		
		/**
		 * Constant for the plugin slug
		 */
		const plugin_slug = 'new-plugin-framework';
		
		/**
		 * @var Controller $instance The class instance
		 */
		static $instance = null;
		
		/**
		 * @var string $option_name The name to use in the WordPress options table
		 */
		private $option_name;
		
		/**
		 * @var array $options Array options
		 */
		private $options;
		
		/**
		 * @var Utilities   Instance of the utilities class
		 */
		private $util;
		
		/**
		 * @var string $license_name Name of the license we need/are managing
		 */
		private $license_name = 'e20r-' . Controller::plugin_slug;
		
		/**
		 * @var string $license_descr - The description of the license
		 */
		private $license_descr = null;
		
		/**
		 * Controller constructor.
		 */
		public function __construct() {
			
			$this->option_name   = strtolower( get_class( $this ) );
			$this->license_descr = __( "New Plugin Framework", Controller::plugin_slug );
		}
		
		/**
		 * Retrieve and initiate the class instance
		 *
		 * @return Controller
		 */
		public static function get_instance() {
			
			if ( is_null( self::$instance ) ) {
				self::$instance = new self;
			}
			
			return self::$instance;
		}
		
		public function checkLicense( $reply, $package, $upgrader ) {
			
			return Licensing\Licensing::isLicenseActive( $this->license_name );
		}
		
		public function registerLicense() {
			
			Licensing\Licensing::registerLicense( $this->license_name, $this->license_descr );
		}
		
		public function loadHooks() {
			
			$this->util    = Utilities::get_instance();
			$this->license = Licensing\Licensing::get_instance();
			
			add_action( 'admin_notices', array( $this->util, 'display_notice' ) );
			
			add_action( 'init', array( $this, 'registerLicense' ) );
			
			add_filter( 'upgrader_pre_download', array( $this, 'checkLicense' ), 10, 3 );
			add_action( 'http_api_curl', array( $this, 'force_tls_12' ) );
			
		}
		
		/**
		 * Load CSS and JS for the admin page(s).
		 */
		public function enqueue_admin_scripts() {
			
			wp_enqueue_style( Controller::plugin_slug, plugins_url( 'css/new-plugin-framework.css', __FILE__ ), null, E20R_NPF_VER );
		}
		
		/**
		 * Load the required translation file for the add-on
		 */
		public function loadTranslation() {
			
			$slug   = Controller::plugin_slug;
			$locale = apply_filters( "plugin_locale", get_locale(), Controller::plugin_slug );
			$mo     = "{$slug}-{$locale}.mo";
			
			// Paths to local (plugin) and global (WP) language files
			$local_mo  = plugin_dir_path( __FILE__ ) . "/languages/{$mo}";
			$global_mo = WP_LANG_DIR . "/{$slug}/{$mo}";
			
			// Load global version first
			load_textdomain( $slug, $global_mo );
			
			// Load local version second
			load_textdomain( $slug, $local_mo );
		}
		
		/**
		 * Check dependencies for this site
		 */
		public function checkDependencies() {
			
			$utils = Utilities::get_instance();
			
			$has_GravityForms   = class_exists( 'GFForms' );
			$has_PMPro          = function_exists( 'pmpro_getAllLevels' );
			$has_RegisterHelper = class_exists( '\PMProRH_Field' );
			$has_EmailTemplates = function_exists( 'pmproet_setup' );
			$has_Approvals      = class_exists( '\PMPro_Approvals' );
			
			// $has_MembersList       = class_exists( 'E20R\Members_List\Controller\E20R_Members_List' );
			// $has_GF_UserReg        = function_exists( 'gf_user_registration' );
			
			if ( is_admin() ) {
				
				if ( false === $has_GravityForms ) {
					$utils->add_message( sprintf(
						__( "Error: The <a href=\"%s\" target=\"_blank\">Gravity Forms</a> plugin must be installed and active!", Application_Management::plugin_slug ),
						'http://www.gravityforms.com/'
					), 'error', 'backend' );
				}
				
				/*
				if ( false === $has_GF_UserReg ) {
					$utils->add_message( sprintf(
						__( "Error: The <a href=\"%s\" target=\"_blank\">Gravity Forms User Registration</a> add-on should be installed and active!", Application_Management::plugin_slug ),
						'https://www.gravityforms.com/add-ons/user-registration/'
					), 'error', 'backend' );
				}
				*/
				
				if ( false === $has_PMPro ) {
					$utils->add_message( sprintf(
						__( "Error: The <a href=\"%s\" target=\"_blank\">Paid Memberships Pro</a> plugin must be installed and active!", Application_Management::plugin_slug ),
						'https://wordpress.org/plugins/paid-memberships-pro/'
					), 'error', 'backend' );
				}
				
				if ( false === $has_RegisterHelper ) {
					$utils->add_message( sprintf(
						__( "Error: The <a href=\"%s\" target=\"_blank\">PMPro Register Helper</a> plugin must be installed and active!", Application_Management::plugin_slug ),
						'https://wordpress.org/plugins/pmpro-register-helper/'
					), 'error', 'backend' );
				}
				
				if ( false === $has_EmailTemplates ) {
					$utils->add_message( sprintf(
						__( "Error: The <a href=\"%s\" target=\"_blank\">PMPro Email Templates Admin</a> add-on must be installed and active!", Application_Management::plugin_slug ),
						'https://wordpress.org/plugins/pmpro-email-templates-addon/'
					), 'error', 'backend' );
				}
				
				if ( false === $has_Approvals ) {
					$utils->add_message( sprintf(
						__( "Error: The <a href=\"%s\" target=\"_blank\">PMPro Approvals</a> add-on must be installed and active!", Application_Management::plugin_slug ),
						'https://www.paidmembershipspro.com/add-ons/approval-process-membership/'
					), 'error', 'backend' );
				}
				
				/*
				if ( false === $has_MembersList ) {
					$utils->add_message( sprintf(
						__( "Error: The <a href=\"%s\" target=\"_blank\">E20R Members List</a> plugin must be installed and active!", Application_Management::plugin_slug ),
						'https://eighty20results.com/wordpress-plugins/paid-memberships-pro'
					), 'error', 'backend' );
				}
				*/
			}
		}
		
		/**
		 * Class auto-loader for this plugin
		 *
		 * @param string $class_name Name of the class to auto-load
		 *
		 * @return string
		 *
		 * @since  1.0
		 * @access public static
		 */
		public static function autoLoader( $class_name ) {
			
			$is_e20r = ( false !== stripos( $class_name, 'e20r' ) );
			
			if ( false === stripos( $class_name, 'New_Plugin_Framework' ) && false === $is_e20r ) {
				return $class_name;
			}
			
			$parts  = explode( '\\', $class_name );
			$c_name = $is_e20r ? preg_replace( '/_/', '-', $parts[ ( count( $parts ) - 1 ) ] ) : $parts[ ( count( $parts ) - 1 ) ];
			
			$base_path = plugin_dir_path( __FILE__ ) . 'class/';
			
			if ( $is_e20r ) {
				$filename = strtolower( "class.{$c_name}.php" );
			} else {
				$filename = "class-{$c_name}.php";
			}
			
			$iterator = new \RecursiveDirectoryIterator( $base_path, \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveIteratorIterator::SELF_FIRST | \RecursiveIteratorIterator::CATCH_GET_CHILD | \RecursiveDirectoryIterator::FOLLOW_SYMLINKS );
			
			/**
			 * Loate class member files, recursively
			 */
			$filter = new \RecursiveCallbackFilterIterator( $iterator, function ( $current, $key, $iterator ) use ( $filename ) {
				
				$file_name = $current->getFilename();
				
				// Skip hidden files and directories.
				if ( $file_name[0] == '.' || $file_name == '..' ) {
					return false;
				}
				
				if ( $current->isDir() ) {
					// Only recurse into intended subdirectories.
					return $file_name() === $filename;
				} else {
					// Only consume files of interest.
					return strpos( $file_name, $filename ) === 0;
				}
			} );
			
			foreach ( new \ RecursiveIteratorIterator( $iterator ) as $f_filename => $f_file ) {
				
				$class_path = $f_file->getPath() . "/" . $f_file->getFilename();
				
				if ( $f_file->isFile() && false !== strpos( $class_path, $filename ) ) {
					require_once( $class_path );
				}
			}
		}
	}
}

// TODO: Update New_Plugin_Framework\Controller to match class name
spl_autoload_register( 'E20R\New_Plugin_Framework\Controller::autoLoader' );
add_action( 'plugins_loaded', array( Controller::get_instance(), 'loadHooks' ) );


/**
 * One-click update handler & checker
 */
if ( ! class_exists( '\\Puc_v4_Factory' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'lib/plugin-updates/plugin-update-checker.php' );
}

$plugin_updates = \Puc_v4_Factory::buildUpdateChecker(
	"https://eighty20results.com/protected-content/" . Controller::plugin_slug . "/metadata.json",
	__FILE__,
	Controller::plugin_slug
);