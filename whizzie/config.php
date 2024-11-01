<?php
/**
 * Settings for plugin wizard
 *
 * @package Sirat_Demo_Importer_Whizzie
 * @author Catapult Themes
 * @since 1.0.0
 */

/**
 * Define constants
 **/
if ( ! defined( 'SIRAT_DEMO_IMPORTER_WHIZZIE_DIR' ) ) {
	define( 'SIRAT_DEMO_IMPORTER_WHIZZIE_DIR', dirname( __FILE__ ) );
}
// Load the Sirat_Demo_Importer_Whizzie class and other dependencies
require trailingslashit( SIRAT_DEMO_IMPORTER_WHIZZIE_DIR ) . 'sirat_demo_importer_whizzie.php';

// Gets the plugin object

$current_plugin	=	get_plugin_data( SIRAT_DEMO_IMPORTER_EXT_FILE );
$plugin_title		=	$current_plugin[ 'Name' ];


/**
 * Make changes below
 **/

// Change the title and slug of your wizard page
$config['page_slug'] 	= 'sirat-demo-importer-get-started';
$config['page_title']	= esc_html( 'Get Started' );

// You can remove elements here as required
// Don't rename the IDs - nothing will break but your changes won't get carried through
$config['steps'] = array(
	'intro' => array(
		'id'					=>	'intro', // ID for section - don't rename
		'title'				=>	__( 'Welcome to ', 'sirat-demo-importer' ) . $plugin_title, // Section title
		'icon'				=>	'dashboard', // Uses Dashicons
		'button_text'	=>	__( 'Start Now', 'sirat-demo-importer' ), // Button text
		'can_skip'		=>	false, // Show a skip button?
		'icon_url'		=>	esc_url( SIRAT_DEMO_IMPORTER_EXT_URI . 'whizzie/assets/img/Icons-01.svg' )
	),
	'themes' => array(
		'id'			=> 'themes',
		'title'				=>	__( 'Themes', SIRAT_DEMO_IMPORTER_TEXT_DOMAIN ),
		'icon'				=>	'admin-appearance',
		'button_text'	=>	__( 'Install Themes', SIRAT_DEMO_IMPORTER_TEXT_DOMAIN ),
		'can_skip'		=>	false
	),
	'plugins' => array(
		'id'					=>	'plugins',
		'title'				=>	__( 'Plugins', 'sirat-demo-importer' ),
		'icon'				=>	'admin-plugins',
		'button_text'	=>	__( 'Install Plugins', 'sirat-demo-importer' ),
		'can_skip'		=>	false,
		'icon_url'		=>	esc_url( SIRAT_DEMO_IMPORTER_EXT_URI . 'whizzie/assets/img/Icons-02.svg' )
	),
	'widgets' => array(
		'id'							=>	'widgets',
		'title'						=>	__( 'Demo Importer', 'sirat-demo-importer' ),
		'icon'						=>	'welcome-widgets-menus',
		'button_text_one'	=>	__( 'Click On The Image To Import Customizer Demo', 'sirat-demo-importer' ),
		'button_text_two'	=>	__( 'Click On The Image To Import Gutenberg Block Demo', 'sirat-demo-importer' ),
		'can_skip'				=>	false,
		'icon_url'				=>	esc_url( SIRAT_DEMO_IMPORTER_EXT_URI . 'whizzie/assets/img/Icons-03.svg' )
	),
	'done' => array(
		'id'				=>	'done',
		'title'			=>	__( 'All Done', 'sirat-demo-importer' ),
		'icon'			=>	'yes',
		'icon_url'	=>	esc_url( SIRAT_DEMO_IMPORTER_EXT_URI . 'whizzie/assets/img/Icons-04.svg' )
	)
);

/**
 * This kicks off the wizard
 **/
if( class_exists( 'Sirat_Demo_Importer_Whizzie' ) ) {
	$Sirat_Demo_Importer_Whizzie = new Sirat_Demo_Importer_Whizzie( $config );
}
