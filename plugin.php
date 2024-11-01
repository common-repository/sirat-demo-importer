<?php
/*
  Plugin Name:       Sirat Demo Importer
  Plugin URI:        https://www.vwthemes.com/
  Description:       Premium multipurpose WordPress plugin is truly beneficial for wide range of businesses or any kind of startup and the best part of it is that it is not only simple and clean but is also user friendly as well apart from being lightweight and extensive to the limit as preferred by the user. Multipurpose WordPress plugin is not only fast but responsive to the core other than being RTL and translation ready and highly suitable for some of the finest SEO practices. The ecommerce features with this premium category plugin are unique and there is no doubt about this fact.
  Version:           0.0.1
  Requires at least: 5.2
  Requires PHP:      7.2
  Author:            VowelWeb
  Author URI:        https://www.vowelweb.com/
  License:           GPL v2 or later
  License URI:       https://www.gnu.org/licenses/gpl-2.0.html
  Text Domain: sirat-demo-importer
*/

define( 'SIRAT_DEMO_IMPORTER_EXT_FILE', __FILE__ );
define( 'SIRAT_DEMO_IMPORTER_EXT_BASE', plugin_basename( SIRAT_DEMO_IMPORTER_EXT_FILE ) );
define( 'SIRAT_DEMO_IMPORTER_EXT_DIR', plugin_dir_path( SIRAT_DEMO_IMPORTER_EXT_FILE ) );
define( 'SIRAT_DEMO_IMPORTER_EXT_URI', plugins_url( '/', SIRAT_DEMO_IMPORTER_EXT_FILE ) );
define( 'SIRAT_DEMO_IMPORTER_EXT_VER', '0.0.1' );
define( 'SIRAT_DEMO_IMPORTER_EXT_TEMPLATE_DEBUG_MODE', false );
define( 'SIRAT_DEMO_IMPORTER_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SIRAT_DEMO_IMPORTER_THEME', "Sirat" );
define( 'SIRAT_DEMO_IMPORTER_TEXT_DOMAIN', "sirat-demo-importer" );
define( 'SIRAT_DEMO_IMPORTER_LICENCE_ENDPOINT', 'https://vwthemes.com/wp-json/ibtana-licence/v2/' );

// For using Get plugin data functions
if( ! function_exists('get_plugin_data') ) {
  require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
// var_dump(SIRAT_DEMO_IMPORTER_EXT_DIR);exit;

require_once SIRAT_DEMO_IMPORTER_EXT_DIR . 'whizzie/config.php';
