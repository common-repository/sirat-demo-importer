<?php
/**
 * Wizard
 *
 * @package Sirat_Demo_Importer_Whizzie
 * @author Catapult Themes
 * @since 1.0.0
 */

class Sirat_Demo_Importer_Whizzie {

	protected $version = '1.1.0';

	public static $is_valid_key = 'false';
	public static $plugin_license_key 		= '';

	/** @var string Current plugin name, used as namespace in actions. */
	protected $plugin_name = '';
	protected $plugin_title = '';

	/** @var string Wizard page slug and title. */
	protected $page_slug = '';
	protected $page_title = '';

	/** @var array Wizard steps set by user. */
	protected $config_steps = array();

	/**
	 * Relative plugin url for this plugin folder
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_url = '';

	/**
	 * TGMPA instance storage
	 *
	 * @var object
	 */
	protected $sirat_demo_importer_tgmpa_instance;

	/**
	 * TGMPA Menu slug
	 *
	 * @var string
	 */
	protected $sirat_demo_importer_tgmpa_menu_slug = 'sirat-demo-importer-tgmpa-install-plugins';

	/**
	 * TGMPA Menu url
	 *
	 * @var string
	 */
	protected $tgmpa_url = 'plugins.php?page=sirat-demo-importer-tgmpa-install-plugins';


	/**
	 * Constructor
	 *
	 * @param $config	Our config parameters
	 */
	public function __construct( $config ) {
		$this->set_vars( $config );
		$this->init();
	}

	public static function sdi_sanitize_array( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'self::sdi_sanitize_array', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}

	public static function get_the_validation_status() {
		return get_option('sirat_demo_importer_plugin_license_validation_status');
	}

	public static function set_the_validation_status( $is_valid ) {
		update_option( 'sirat_demo_importer_plugin_license_validation_status', $is_valid );
	}

	public static function get_the_suspension_status() {
		return get_option( 'sirat_demo_importer_plugin_license_suspension_status' );
	}

	public static function set_the_suspension_status( $is_suspended ) {
		update_option( 'sirat_demo_importer_plugin_license_suspension_status' , $is_suspended );
	}

	public static function set_the_plugin_key( $the_key ) {
		update_option( 'sirat_demo_importer_plugin_license_key', $the_key );
	}

	public static function remove_the_plugin_key() {
		delete_option( 'sirat_demo_importer_plugin_license_key' );
	}

	public static function get_the_plugin_key() {
		return get_option( 'sirat_demo_importer_plugin_license_key' );
	}

	/**
	 * Set some settings
	 * @since 1.0.0
	 * @param $config	Our config parameters
	 */
	public function set_vars( $config ) {

		require_once trailingslashit( SIRAT_DEMO_IMPORTER_WHIZZIE_DIR ) . 'tgmpa/sirat-demo-importer-class-tgm-plugin-activation.php';
		require_once trailingslashit( SIRAT_DEMO_IMPORTER_WHIZZIE_DIR ) . 'tgmpa/required-plugins.php';

		require_once trailingslashit( SIRAT_DEMO_IMPORTER_WHIZZIE_DIR ) . 'widgets/class-sirat-demo-importer-widget-importer.php';

		if( isset( $config['page_slug'] ) ) {
			$this->page_slug	=	esc_attr( $config['page_slug'] );
		}
		if( isset( $config['page_title'] ) ) {
			$this->page_title = esc_attr( $config['page_title'] );
		}

		if( isset( $config['steps'] ) ) {
			$this->config_steps	= Sirat_Demo_Importer_Whizzie::sdi_sanitize_array( $config['steps'] );
		}

		$this->plugin_path	=	trailingslashit( dirname( __FILE__ ) );
		$relative_url				=	str_replace( SIRAT_DEMO_IMPORTER_EXT_DIR, '', $this->plugin_path );
		$this->plugin_url		=	trailingslashit( SIRAT_DEMO_IMPORTER_EXT_URI . $relative_url );
		$this->plugin_url		=	SIRAT_DEMO_IMPORTER_EXT_URI . 'whizzie/';

		$current_plugin			=	get_plugin_data( SIRAT_DEMO_IMPORTER_EXT_FILE );
		$this->plugin_title	=	$current_plugin[ 'Name' ];
		$this->plugin_name	=	strtolower( preg_replace( '#[^a-zA-Z]#', '', $current_plugin[ 'Name' ] ) );
		$this->page_slug		=	apply_filters( $this->plugin_name . '_theme_setup_wizard_page_slug', $this->plugin_name . '-setup' );

		$this->parent_slug	=	apply_filters( $this->plugin_name . '_theme_setup_wizard_parent_slug', '' );
	}

	/**
	 * Hooks and filters
	 * @since 1.0.0
	 */
	public function init() {

		add_action( 'activated_plugin', array( $this, 'redirect_to_wizard' ), 100, 2 );

		if ( class_exists( 'SIRAT_DEMO_IMPORTER_TGM_Plugin_Activation' ) && isset( $GLOBALS['vw_sirat_pro'] ) ) {
			add_action( 'init', array( $this, 'sirat_demo_importer_tgmpa_instance' ), 30 );
			add_action( 'init', array( $this, 'set_tgmpa_url' ), 40 );
		}
		// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'menu_page' ) );
		add_action( 'admin_init', array( $this, 'get_plugins' ), 30 );


		add_filter( 'sirat_demo_importer_tgmpa_load', array( $this, 'sirat_demo_importer_tgmpa_load' ), 10, 1 );
		add_action( 'wp_ajax_sirat_demo_importer_setup_plugins', array( $this, 'sirat_demo_importer_setup_plugins' ) );
		add_action( 'wp_ajax_sirat_demo_importer_setup_themes', array( $this, 'sirat_demo_importer_setup_themes' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'sirat_demo_importer_admin_theme_style' ) );

		add_action( 'wp_ajax_wz_activate_sirat_demo_importer', array( $this, 'wz_activate_sirat_demo_importer' ) );

		add_filter( 'woocommerce_prevent_automatic_wizard_redirect', '__return_true' );




		//
		add_action( 'wp_ajax_sirat_demo_importer_step_popup', array( $this, 'sirat_demo_importer_step_popup' ) );
		add_action( 'wp_ajax_sirat_demo_importer_get_the_key_status', array( $this, 'sirat_demo_importer_get_the_key_status' ) );
		add_action( 'wp_ajax_sirat_demo_importer_setup_plugins_step_popup', array( $this, 'sirat_demo_importer_setup_plugins_step_popup' ) );
		add_action( 'wp_ajax_sirat_demo_importer_install_and_activate_plugin', array( $this, 'sirat_demo_importer_install_and_activate_plugin' ) );
		add_action( 'wp_ajax_sirat_demo_importer_setup_elementor', array( $this, 'sirat_demo_importer_setup_elementor' ) );
		//


		add_action( 'wp_enqueue_scripts', array( $this, 'wpdocs_theme_name_scripts' ) );
	}

	function wpdocs_theme_name_scripts() {
		wp_enqueue_style( 'sirat-demo-importer-frontend-style', 'https://vwthemesdemo.com/ibtana_json/elementor_theme/css/sirat-elementor-css.css' );
	}

	function random_string($length) {
		$key	=	'';
		$keys	=	array_merge( range( 0, 9 ), range( 'a', 'z' ) );
		for ( $i = 0; $i < $length; $i++ ) {
			$key .= $keys[ array_rand( $keys ) ];
		}
		return $key;
	}

	function sirat_demo_importer_setup_elementor() {

		$elementor_template_data				=	Sirat_Demo_Importer_Whizzie::sdi_sanitize_array( $_POST['elementor_template_response'] );

		$elementor_template_data_title	=	$elementor_template_data['title'];
		$elementor_template_data_json		=	wp_unslash( $elementor_template_data['json'] );

		// Upload the file first
		$upload_dir	=	wp_upload_dir();
		$filename		=	$this->random_string(25) . '.json';
		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}
		$file_put_contents = file_put_contents( $file, $elementor_template_data_json );

		$json_path	=	$upload_dir['path'] . '/' . $filename;
		$json_url		=	$upload_dir['url'] . '/' . $filename;

		$elementor_home_data					=	$this->get_elementor_theme_data( $json_url, $json_path );

		$page_title = $elementor_template_data_title;

		$vw_page = array(
			'post_type'			=>	'page',
			'post_title'		=>	$page_title,
			'post_content'	=>	$elementor_home_data['elementor_content'],
			'post_status'		=>	'publish',
			'post_author'		=>	1,
			'meta_input'		=>	$elementor_home_data['elementor_content_meta']
		);
		$home_id = wp_insert_post( $vw_page );

		// Widget Import START
		if ( $elementor_template_data['wie'] && gettype( json_decode( wp_unslash( $elementor_template_data['wie'] ) ) ) ) {
			$Sirat_Demo_Importer_Widget_Importer = new Sirat_Demo_Importer_Widget_Importer;
			$Sirat_Demo_Importer_Widget_Importer->import_widgets( wp_unslash( $elementor_template_data['wie'] ) );
		}
		// Widget Import END


		wp_send_json(array(
			'permalink'				=>	get_permalink( $home_id ),
			'edit_post_link'	=>	admin_url( 'post.php?post=' . $home_id . '&action=elementor' ),
		));
	}

	public function get_elementor_theme_data( $json_url, $json_path ) {

		// Mime a supported document type.
		$elementor_plugin = \Elementor\Plugin::$instance;
		$elementor_plugin->documents->register_document_type( 'not-supported', \Elementor\Modules\Library\Documents\Page::get_class_full_name() );

		$template                  	=	$json_url;
		$name                      	=	'';
		$_FILES['file']['tmp_name']	=	$template;

		$elementor                  = new \Elementor\TemplateLibrary\Source_Local;
		$elementor->import_template( $name, $template );
		unlink( $json_path );

		$args = array(
			'post_type'        => 'elementor_library',
			'nopaging'         => true,
			'posts_per_page'   => '1',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => true,
		);

		$query = new \WP_Query( $args );


		$last_template_added = $query->posts[0];
		//get template id
		$template_id = $last_template_added->ID;

		wp_reset_query();
		wp_reset_postdata();

		//page content
		$page_content = $last_template_added->post_content;

		//meta fields
		$elementor_data_meta      = get_post_meta( $template_id, '_elementor_data' );
		$elementor_ver_meta       = get_post_meta( $template_id, '_elementor_version' );
		$elementor_edit_mode_meta = get_post_meta( $template_id, '_elementor_edit_mode' );
		$elementor_css_meta       = get_post_meta( $template_id, '_elementor_css' );

		$elementor_metas = array(
			'_elementor_data'     	=>	! empty( $elementor_data_meta[0] ) ? wp_slash( $elementor_data_meta[0] ) : '',
			'_elementor_version'  	=>	! empty( $elementor_ver_meta[0] ) ? $elementor_ver_meta[0] : '',
			'_elementor_edit_mode'	=>	! empty( $elementor_edit_mode_meta[0] ) ? $elementor_edit_mode_meta[0] : '',
			'_elementor_css'      	=>	$elementor_css_meta,
		);

		$elementor_json = array('elementor_content' => $page_content, 'elementor_content_meta' => $elementor_metas);
		return $elementor_json;
	}

	public function sirat_demo_importer_install_and_activate_plugin() {
		$post_plugin_details	= Sirat_Demo_Importer_Whizzie::sdi_sanitize_array( $_POST['plugin_obj_to_install'] );

		$plugin_text_domain		= $post_plugin_details['slug'];
		$plugin_file_path			=	$post_plugin_details['file_path'];

		$plugin_url						=	$post_plugin_details['source'];

		$plugin = array(
			'text_domain'	=>	$plugin_text_domain,
			'path' 				=>	$plugin_url,
			'install' 		=>	$plugin_file_path
		);

		// Get
		$args = array(
			'path'					=>	ABSPATH . 'wp-content/plugins/',
			'preserve_zip'	=>	false
		);

		$get_plugins = get_plugins();

		if ( isset( $get_plugins[ $plugin_file_path ] ) ) {
			return false;
		}

		$response = wp_remote_get(
			$plugin_url,
	    array(
				'timeout'     => 120,
	    )
		);
		$responseData = wp_remote_retrieve_body( $response );

		$is_file_moved = file_put_contents( $args['path'] . $plugin_text_domain . '.zip', $responseData );

		if ( !$is_file_moved ) {
			return false;
		}

		global $wp_filesystem;

		require_once ABSPATH . '/wp-admin/includes/file.php';

		WP_Filesystem();

		$file_system	=	$wp_filesystem;
		$plugin_path	=	str_replace( ABSPATH, $file_system->abspath(), SIRAT_DEMO_IMPORTER_EXT_DIR ); /* get remote system absolute path */

		$plugin_path	=	str_replace( "sirat-demo-importer/", "", $plugin_path );

		$result	=	unzip_file( $args['path'] . $plugin_text_domain . '.zip', $plugin_path );

		if ( $result ) {
			wp_delete_file( $args['path'] . $plugin_text_domain . '.zip' );
			return true;
		} else {
			return false;
		}

	}

	public function sirat_demo_importer_get_the_key_status() {
		wp_send_json(array(
			'status'	=>	Sirat_Demo_Importer_Whizzie::get_the_validation_status()
		));
	}

	public function redirect_to_wizard( $plugin, $network_wide ) {
		global $pagenow;
		if( is_admin() && ( 'plugins.php' == $pagenow ) && current_user_can( 'manage_options' ) && ( SIRAT_DEMO_IMPORTER_EXT_BASE == $plugin ) ) {

			wp_redirect( admin_url( 'admin.php?page=' . esc_attr( $this->page_slug ) ) );
			exit;

		}
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'sirat-demo-importer-whizzie-style', $this->plugin_url . 'assets/css/sirat-demo-importer-admin-style.css', array(), time() );

		wp_enqueue_script('tabs', $this->plugin_url . 'getstarted/js/tab.js');

		wp_enqueue_script( 'sirat-demo-importer-notify-popup', $this->plugin_url . 'assets/js/notify.min.js');

		wp_register_script(
			'sirat-demo-importer-whizzie-script-js',
			$this->plugin_url . 'assets/js/sirat_demo_importer_whizzie.js',
			array( 'jquery' ),
			time(),
			// true
		);

		wp_localize_script(
			'sirat-demo-importer-whizzie-script-js',
			'sirat_demo_importer_whizzie_params',
			array(
				'ajaxurl'				=>	admin_url( 'admin-ajax.php' ),
				'admin_url'			=>	admin_url(),
				'wpnonce' 			=>	wp_create_nonce( 'sirat_demo_importer_whizzie_nonce' ),
				'verify_text'		=>	esc_html( ' verifying', SIRAT_DEMO_IMPORTER_TEXT_DOMAIN ),
				'woocommerce'		=>	class_exists( 'WooCommerce' )
			)
		);
		wp_enqueue_script( 'sirat-demo-importer-whizzie-script-js' );

		wp_enqueue_script( 'updates' );

		wp_register_script(
			'sirat-wizard-script',
			$this->plugin_url . 'assets/js/sirat-wizard-script.js',
			array( 'jquery' ),
			time(),
			true
		);
		wp_localize_script(
			'sirat-wizard-script',
			'sirat_wizard_script_params',
			array(
				'ajaxurl'																=>	admin_url( 'admin-ajax.php' ),
				'admin_url'															=>	admin_url(),
				'site_url'															=>	site_url(),
				'wpnonce'																=>	wp_create_nonce( 'sirat_demo_importer_whizzie_nonce' ),
				'verify_text'														=>	esc_html( ' verifying', SIRAT_DEMO_IMPORTER_TEXT_DOMAIN ),
				'license_key'														=>	Sirat_Demo_Importer_Whizzie::get_the_plugin_key(),
				'SIRAT_DEMO_IMPORTER_LICENCE_ENDPOINT'	=>	SIRAT_DEMO_IMPORTER_LICENCE_ENDPOINT,
				'pro_badge'															=>	SIRAT_DEMO_IMPORTER_EXT_URI . 'whizzie/assets/img/pro-badge.svg'
			)
		);
		wp_enqueue_script( 'sirat-wizard-script' );

	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function sirat_demo_importer_tgmpa_load( $status ) {
		return is_admin() || current_user_can( 'install_plugins' );
	}

	/**
	 * Get configured TGMPA instance
	 *
	 * @access public
	 * @since 1.1.2
	 */
	public function sirat_demo_importer_tgmpa_instance() {
		$this->sirat_demo_importer_tgmpa_instance = call_user_func( array( get_class( $GLOBALS['vw_sirat_pro'] ), 'get_instance' ) );
	}

	/**
	 * Update $sirat_demo_importer_tgmpa_menu_slug and $tgmpa_parent_slug from TGMPA instance
	 *
	 * @access public
	 * @since 1.1.2
	 */
	public function set_tgmpa_url() {
		$this->sirat_demo_importer_tgmpa_menu_slug	=	( property_exists( $this->sirat_demo_importer_tgmpa_instance, 'menu' ) ) ? $this->sirat_demo_importer_tgmpa_instance->menu : $this->sirat_demo_importer_tgmpa_menu_slug;
		$this->sirat_demo_importer_tgmpa_menu_slug	=	apply_filters( $this->plugin_name . '_theme_setup_wizard_tgmpa_menu_slug', $this->sirat_demo_importer_tgmpa_menu_slug );
		$tgmpa_parent_slug								=	( property_exists( $this->sirat_demo_importer_tgmpa_instance, 'parent_slug' ) && $this->sirat_demo_importer_tgmpa_instance->parent_slug !== 'themes.php' ) ? 'admin.php' : 'themes.php';
		$this->tgmpa_url									=	apply_filters( $this->plugin_name . '_theme_setup_wizard_tgmpa_url', $tgmpa_parent_slug . '?page=' . $this->sirat_demo_importer_tgmpa_menu_slug );
	}

	/**
	 * Make a modal screen for the wizard
	 */
	public function menu_page() {
		add_menu_page(
			esc_html( $this->page_title ),
			esc_html( $this->page_title ),
			'manage_options', $this->page_slug,
			array( $this, 'sirat_demo_importer_wizard_page' ),
			esc_url( SIRAT_DEMO_IMPORTER_EXT_URI.'whizzie/assets/img/admin-menu.svg' ),
			40
		);
	}

	public function activation_page() {
		$plugin_license_key	= Sirat_Demo_Importer_Whizzie::get_the_plugin_key();
		$validation_status	= Sirat_Demo_Importer_Whizzie::get_the_validation_status();
		?>
		<div class="wrap">
			<label><?php esc_html_e( 'Enter Your Theme License Key:', 'sirat-demo-importer' ); ?></label>
			<form id="sirat_demo_importer_license_form">
				<input type="text" name="sirat_demo_importer_license_key"
				value="<?php echo esc_attr( $plugin_license_key ) ?>" <?php if( $validation_status === 'true' ) { echo esc_attr( "disabled" ); } ?> required
				placeholder="License Key" />
				<div class="licence-key-button-wrap">
					<button class="button" type="submit" name="button" <?php if( $validation_status === 'true' ) { echo esc_attr( "disabled" ); } ?>>
						<?php if ($validation_status === 'true') { ?>
							<?php esc_html_e( 'Activated', 'sirat-demo-importer' ); ?>
						<?php } else { ?>
							<?php esc_html_e( 'Activate', 'sirat-demo-importer' ); ?>
						<?php } ?>
					</button>

					<?php if ($validation_status === 'true') { ?>
						<button id="change--key" class="button" type="button" name="button">
							<?php esc_html_e( 'Change Key', 'sirat-demo-importer' ); ?>
						</button>
					<?php } ?>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Make an interface for the wizard
	 */
	public function sirat_demo_importer_wizard_page() {

		sirat_demo_importer_tgmpa_load_bulk_installer();

		// install plugins with TGM.
		if ( ! class_exists( 'SIRAT_DEMO_IMPORTER_TGM_Plugin_Activation' ) || ! isset( $GLOBALS['vw_sirat_pro'] ) ) {
			die( 'Failed to find TGM' );
		}
		$url = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'whizzie-setup' );

		// copied from TGM
		$method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
		$fields = array_keys( Sirat_Demo_Importer_Whizzie::sdi_sanitize_array( $_POST ) ); // Extra fields to pass to WP_Filesystem.

		if ( false === ( $creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields ) ) ) {
			return true; // Stop the normal page form from displaying, credential request form will be shown.
		}
		// Now we have some credentials, setup WP_Filesystem.
		if ( ! WP_Filesystem( $creds ) ) {
			// Our credentials were no good, ask the user for them again.
			request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );
			return true;
		}


		/* If we arrive here, we have the filesystem */

		$display_string = '';

		// Check the validation Start
		$sirat_demo_importer_license_key	=	Sirat_Demo_Importer_Whizzie::get_the_plugin_key();
		$endpoint													=	SIRAT_DEMO_IMPORTER_LICENCE_ENDPOINT . 'ibtana_client_premium_add_on_check_activation_status';
		$body = [
			'add_on_key'					=>	$sirat_demo_importer_license_key,
			'site_url'						=>	site_url(),
			'add_on_text_domain'	=>	'sirat-demo-importer'
		];
		$body = wp_json_encode( $body );
		$options = [
			'body'        => $body,
			'headers'     => [
				'Content-Type' => 'application/json',
			]
		];
		$response = wp_remote_post( $endpoint, $options );
		if ( is_wp_error( $response ) ) {
			// Sirat_Demo_Importer_Whizzie::set_the_validation_status('false');
		} else {
			$response_body = wp_remote_retrieve_body( $response );
			$response_body = json_decode( $response_body );

			if ( $response_body->is_suspended == 1 ) {
				Sirat_Demo_Importer_Whizzie::set_the_suspension_status( 'true' );
			} else {
				Sirat_Demo_Importer_Whizzie::set_the_suspension_status( 'false' );
			}

			$display_string = isset($response_body->display_string) ? $response_body->display_string : '';

			if ( $display_string != '' ) {
				if ( strpos( $display_string, '[THEME_NAME]' ) !== false ) {
					$display_string = str_replace( "[THEME_NAME]", "Sirat Demo Importer", $display_string );
				}
				if ( strpos( $display_string, '[THEME_PERMALINK]' ) !== false ) {
					$display_string = str_replace( "[THEME_PERMALINK]", "https://www.vwthemes.com/themes/multipurpose-wordpress-theme/", $display_string );
				}

				printf( '<div class="notice is-dismissible error thb_admin_notices">%s</div>', $display_string );
			}

			if ( $response_body->status === false ) {
				Sirat_Demo_Importer_Whizzie::set_the_validation_status('false');
			} else {
				Sirat_Demo_Importer_Whizzie::set_the_validation_status('true');
			}
		}
		// Check the validation END

		$theme_validation_status = Sirat_Demo_Importer_Whizzie::get_the_validation_status();

		?>




		<div class="sirat-demo-importer-theme-page-header">
			<div class="sirat-demo-importer-container sirat-demo-importer-flex sirat-demo-importer-templates-header-container">
				<div class="sirat-demo-importer-theme-title">
					<img src="<?php echo esc_url( SIRAT_DEMO_IMPORTER_EXT_URI . 'whizzie/assets/img/adminIcon.png' ); ?>" class="sirat-demo-importer-theme-icon" />
				</div>
				<div class="sirat-demo-importer-top-links">
					<p class="siratprora-theme-version">
						<strong>
							<?php
								esc_html_e( 'v' . SIRAT_DEMO_IMPORTER_EXT_VER, SIRAT_DEMO_IMPORTER_TEXT_DOMAIN );
							?>
						</strong>
					</p>
					<p>
						<img src="<?php echo esc_url( SIRAT_DEMO_IMPORTER_EXT_URI . 'whizzie/assets/img/lightning.svg' ); ?>" class="siratprora-lightning-icon"> <?php esc_html_e( 'Lightning Fast &amp; Fully Customizable WordPress theme!', SIRAT_DEMO_IMPORTER_TEXT_DOMAIN ); ?>
					</p>
				</div>
			</div>
		</div>

		<div id="elementor_templates" class="sirat-demo-importer-spinning">
			<?php $this->template_cards_container(); ?>

			<?php $this->theme_install_overlay(); ?>

			<?php $this->activation_popup(); ?>

			<div class="sirat-demo-importer-step-loading"><span class="spinner"></span></div>
		</div>

		<div class="wrapper-info get-stared-page-wrap" style="display: none;">

			<div class="wrapper-info-content">
				<h2><?php esc_html_e( 'Welcome to Sirat Demo Importer', 'sirat-demo-importer' ); ?></h2>
				<p>
					<?php
					esc_html_e(
						'All our WordPress themes are modern, minimalist, 100% responsive, seo-friendly, feature-rich, block based and multipurpose that best suit designers, bloggers and other professionals who are working in the creative fields.',
						'sirat-demo-importer'
					);
					?>
				</p>
			</div>

			<div class="tab-sec theme-option-tab">

				<div id="demo_offer" class="tabcontent open">
					<div class="sirat-demo-importer-wrap">
						<?php // printf( '<h1>%s</h1>', esc_html( $this->page_title ) );
						echo '<div class="card sirat-demo-importer-whizzie-wrap">'; ?>

							<div class="wizard-logo-wrap">
								<img src="<?php echo esc_url( SIRAT_DEMO_IMPORTER_EXT_URI . '/whizzie/assets/img/adminIcon.png' ); ?>">
								<span class="wizard-main-title">
									<?php esc_html_e( 'Welcome to ' . $this->plugin_title, 'sirat-demo-importer' ); ?>
								</span>
							</div>

						<?php
							// The wizard is a list with only one item visible at a time
							$steps = $this->get_steps();
							echo '<ul class="sirat-demo-importer-whizzie-menu">';
							foreach( $steps as $step ) {
								$class = 'sirat-demo-importer-step step-' . esc_attr( $step['id'] );
								echo '<li data-step="' . esc_attr( $step['id'] ) . '" class="' . esc_attr( $class ) . '">';
									printf( '<h2>%s</h2>', esc_html( $step['title'] ) );
									// $content is split into summary and detail
									$content = call_user_func( array( $this, $step['view'] ) );
									if( isset( $content['summary'] ) ) {
										printf(
											'<div class="summary">%s</div>',
											wp_kses_post( $content['summary'] )
										);
									}
									if( isset( $content['detail'] ) ) {
										// Add a link to see more detail
										printf( '<p><a href="#" class="sirat-demo-importer-more-info">%s</a></p>', __( 'More Info', SIRAT_DEMO_IMPORTER_TEXT_DOMAIN ) );
										printf(
											'<div class="detail">%s</div>',
											$content['detail'] // Need to escape this
										);
									}

									if ( Sirat_Demo_Importer_Whizzie::get_the_validation_status() === 'true' ) {

										// The next button
										if( isset( $step['button_text'] ) && $step['button_text'] ) {
											printf(
												'<div class="sirat-demo-importer-button-wrap"><a href="#" class="button button-primary sirat-demo-importer-do-it" data-callback="%s" data-step="%s">%s</a></div>',
												esc_attr( $step['callback'] ),
												esc_attr( $step['id'] ),
												esc_html( $step['button_text'] )
											);
										}

										if( isset( $step['button_text_one'] )) {
											printf(
												'<div class="sirat-demo-importer-button-wrap button-wrap-one">
													<a href="#" class="button button-primary sirat-demo-importer-do-it install_widgets_img" data-callback="install_widgets" data-step="widgets"><img src="'.SIRAT_DEMO_IMPORTER_EXT_URI.'/whizzie/assets/img/Customize-Icon.png"></a>
													<p class="demo-type-text">%s</p>
												</div>',
												esc_html( $step['button_text_one'] )
											);
										}

										if( isset( $step['button_text_two'] )) {
											printf(
												'<div class="sirat-demo-importer-button-wrap button-wrap-two">
													<a href="#" class="button button-primary sirat-demo-importer-do-it install_widgets_img" data-step="widgets" data-callback="page_builder" id="ibtana_button"><img src="'.SIRAT_DEMO_IMPORTER_EXT_URI.'/whizzie/assets/img/Gutenberg-Icon.png"></a>
													<p class="demo-type-text">%s</p>
												</div>',
												esc_html( $step['button_text_two'] )
											);
										}

										// The skip button
										if( isset( $step['can_skip'] ) && $step['can_skip'] ) {
											printf(
												'<div class="sirat-demo-importer-button-wrap" style="margin-left: 0.5em;"><a href="#" class="button button-secondary sirat-demo-importer-do-it" data-callback="%s" data-step="%s">%s</a></div>',
												'do_next_step',
												esc_attr( $step['id'] ),
												__( 'Skip', SIRAT_DEMO_IMPORTER_TEXT_DOMAIN )
											);
										}

									} else {
										printf(
											'<div class="sirat-demo-importer-button-wrap"><a class="button button-primary key-activation-tab-click">%s</a></div>',
											esc_html__( 'Activate Your License', 'sirat-demo-importer' )
										);
									}
								echo '</li>';
							}
							echo '</ul>';
							echo '<ul class="sirat-demo-importer-whizzie-nav">';
								foreach( $steps as $step ) {
									if( isset( $step['icon'] ) && $step['icon'] ) {
										echo '<li class="nav-step-' . esc_attr( $step['id'] ) . '"><span class="dashicons dashicons-' . esc_attr( $step['icon'] ) . '"></span></li>';
									}
								}
							echo '</ul>';
							?>
							<div class="sirat-demo-importer-step-loading"><span class="spinner"></span></div>
						</div><!-- .sirat-demo-importer-whizzie-wrap -->
					</div>
				</div>

			</div>




		</div><!-- .wrap -->
		<?php
	}


	public function activation_popup() {
		?>
		<div id="theme_activation" style="display:none;">
			<div class="theme_activation-wrapper">
				<button class="button btn-close">
					<?php echo esc_html( 'x' ); ?>
				</button>
				<span class="theme-license-message">
					<?php esc_html_e( 'Check your theme license key in ', 'sirat-demo-importer' ); ?>
					<a href="<?php echo esc_url('https://www.vwthemes.com/my-account/'); ?>" target="_blank">
						<?php esc_html_e( 'My Account', 'sirat-demo-importer' ); ?>
					</a>
				</span>
				<div class="theme_activation_spinner">
					<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
						<g transform="translate(50,50)">
							<g transform="scale(0.7)">
							<circle cx="0" cy="0" r="50" fill="#0f81d0"></circle>
							<circle cx="0" cy="-28" r="15" fill="#cfd7dd">
								<animateTransform attributeName="transform" type="rotate" dur="1s" repeatCount="indefinite" keyTimes="0;1" values="0 0 0;360 0 0"></animateTransform>
							</circle>
							</g>
						</g>
					</svg>
				</div>
				<div class="theme-wizard-key-status">
					<?php
						if ( Sirat_Demo_Importer_Whizzie::get_the_validation_status() === 'false' ) {
							esc_html_e( 'Theme License Key is not activated!', 'sirat-demo-importer' );
						} else {
							esc_html_e( 'Theme License is Activated!', 'sirat-demo-importer' );
						}
					?>
				</div>
				<?php $this->activation_page(); ?>
			</div>
		</div>
		<?php
	}


	public function sirat_demo_importer_step_popup() {

		$elementor_template_plugins = Sirat_Demo_Importer_Whizzie::sdi_sanitize_array( $_POST['data']['elementor_template_plugins'] );

		?>

		<div class="sirat-demo-importer-plugin-popup">
			<div class="sirat-demo-importer-admin-modal">
				<button class="sirat-demo-importer-step-close-button"><?php esc_html_e( '×', 'sirat-demo-importer' ); ?></button>
				<div class="sirat-demo-importer-demo-step-container" data-current-step="do_next_step">

					<div class="sirat-demo-importer-current-step">

						<!-- <div class="sirat-demo-importer-demo-step" data-step="do_next_step">
							<h2>Welcome</h2>
							<p>This template may require to install a base theme and plugins!</p>
						</div> -->

						<div class="sirat-demo-importer-demo-step active" data-step="theme_install">
							<h2><?php esc_html_e( 'Install & Activate Base Theme', 'sirat-demo-importer' ); ?></h2>
							<p><?php esc_html_e( 'Base theme installation is required!', 'sirat-demo-importer' ); ?></p>

							<div class="sirat-demo-importer-step-checkbox-container" sirat-demo-importer-template-text-domain="sirat">
								<?php esc_html_e( 'Install Base Theme Sirat', 'sirat-demo-importer' ); ?>
								<span class="sirat-demo-importer-step-checkbox active">
									<!-- <svg width="10" height="8" viewBox="0 0 11.2 9.1">
										<polyline class="check" points="1.2,4.8 4.4,7.9 9.9,1.2 "></polyline>
									</svg> -->
									<?php esc_html_e( 'Required', 'sirat-demo-importer' ); ?>
								</span>
							</div>

						</div>


						<!-- Plugin List Start -->
						<?php if ( !empty( $elementor_template_plugins ) ): ?>
						<div class="sirat-demo-importer-demo-step" data-step="plugin_install">
							<h2><?php esc_html_e( 'Install & Activate Plugins', 'sirat-demo-importer' ); ?></h2>
							<p><?php esc_html_e( 'Plugins installation is required!', 'sirat-demo-importer' ); ?></p>

							<?php foreach ( $elementor_template_plugins as $key => $template_plugin ): ?>
							<div class="sirat-demo-importer-step-checkbox-container" sirat-plugin-text-domain="<?php echo esc_attr( $template_plugin['plugin_text_domain'] ); ?>"
								sirat-demo-importer-plugin-main-file="<?php echo esc_attr( $template_plugin['plugin_main_file'] ); ?>"
								sirat-demo-importer-plugin-url="<?php echo esc_url( $template_plugin['plugin_url'] ); ?>">
							  <?php esc_html_e( $template_plugin['plugin_title'], 'sirat-demo-importer' ); ?>
							  <span class="sirat-demo-importer-step-checkbox active">
							    <!-- <svg width="10" height="8" viewBox="0 0 11.2 9.1">
							      <polyline class="check" points="1.2,4.8 4.4,7.9 9.9,1.2 "></polyline>
							    </svg> -->
									<?php esc_html_e( 'Required', 'sirat-demo-importer' ); ?>
							  </span>
							</div>
							<?php endforeach; ?>

						</div>
						<?php endif; ?>
						<!-- Plugin List End -->

						<div class="sirat-demo-importer-demo-step" data-step="demo_import">
							<h2><?php esc_html_e( 'Demo Import', 'sirat-demo-importer' ); ?></h2>
							<p><?php esc_html_e( 'Import the demo!', 'sirat-demo-importer' ); ?></p>
						</div>

						<div class="sirat-demo-importer-demo-step" data-step="demo_finish">
							<h2><?php esc_html_e( 'Finish', 'sirat-demo-importer' ); ?></h2>
							<p><?php esc_html_e( 'Check the preview!', 'sirat-demo-importer' ); ?></p>
							<div>
								<a class="button button-secondary" target="_blank"><?php esc_html_e( 'Edit Page', 'sirat-demo-importer' ); ?></a>
								<a class="button button-primary" target="_blank"><?php esc_html_e( 'Visit Page', 'sirat-demo-importer' ); ?></a>
							</div>
						</div>

					</div>


					<div class="sirat-demo-importer-demo-step-controls">
					  <button class="sirat-demo-importer-demo-btn sirat-demo-importer-demo-back-btn" style="display: none;">
					  	<?php esc_html_e( 'Back', 'sirat-demo-importer' ); ?>
					  </button>
					  <ul class="sirat-demo-importer-steps-pills">
					    <li class="active"><?php esc_html_e( '1', 'sirat-demo-importer' ); ?></li>

							<?php if ( !empty($elementor_template_plugins) ): ?>
					    <li><?php esc_html_e( '2', 'sirat-demo-importer' ); ?></li>
							<?php endif; ?>

					    <li><?php esc_html_e( '3', 'sirat-demo-importer' ); ?></li>
					    <li><?php esc_html_e( '4', 'sirat-demo-importer' ); ?></li>
					  </ul>
					  <button class="sirat-demo-importer-demo-btn sirat-demo-importer-demo-main-btn">
							<span class="merlin__button--loading__text">
								<?php esc_html_e( 'Next', 'sirat-demo-importer' ); ?>
							</span>
							<span class="merlin__button--loading__spinner">
							  <div class="merlin-spinner">
							    <svg class="merlin-spinner__svg" viewBox="25 25 50 50">
							      <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="6" stroke-miterlimit="10"></circle>
							    </svg>
							  </div>
							</span>
						</button>
					</div>

				</div>
			</div>
		</div>

		<?php
		exit;
	}


	public function template_cards_container() {
		?>
		<div class="theme-browser content-filterable rendered">
			<div class="themes wp-clearfix">

			</div>
		</div>
		<?php
	}


	public function theme_install_overlay() {
		?>

		<div id="theme-install-overlay" class="theme-install-overlay wp-full-overlay expanded iframe-ready" style="display: none;">
			<div class="wp-full-overlay-sidebar">
				<div class="wp-full-overlay-header">
					<button class="close-full-overlay">
						<span class="screen-reader-text">
							<?php esc_html_e( 'Close', 'sirat-demo-importer' ); ?>
						</span>
					</button>
					<!-- <button class="previous-theme disabled" disabled=""><span class="screen-reader-text">Previous theme</span></button>
					<button class="next-theme"><span class="screen-reader-text">Next theme</span></button> -->

					<a class="button button-primary theme-install" data-name="" data-slug="" data-is-paid="">
						<?php esc_html_e( 'Import', 'sirat-demo-importer' ); ?>
					</a>

				</div>
				<div class="wp-full-overlay-sidebar-content">
					<div class="install-theme-info">
						<h3 class="theme-name">
							<?php esc_html_e( 'Twenty Twenty-One', 'sirat-demo-importer' ); ?>
						</h3>
						<!-- <span class="theme-by">By WordPress.org</span> -->

						<img class="theme-screenshot" src="" alt="">

						<div class="theme-details">

							<!-- <div class="theme-version">Version: 1.4 </div> -->

							<div class="theme-description">
								<?php
								esc_html_e(
									'Twenty Twenty-One is a blank canvas for your ideas and it makes the block editor your best brush.',
									'sirat-demo-importer'
								);
								?>
							</div>

						</div>
					</div>
				</div>
				<div class="wp-full-overlay-footer">
					<button type="button" class="collapse-sidebar button" aria-expanded="true" aria-label="Collapse Sidebar">
						<span class="collapse-sidebar-arrow"></span>
						<span class="collapse-sidebar-label">
							<?php esc_html_e( 'Collapse', 'sirat-demo-importer' ); ?>
						</span>
					</button>
				</div>
			</div>
			<div class="wp-full-overlay-main">
				<iframe src="" title="Preview"></iframe>
			</div>
			<div class="sirat-demo-importer-step-loading"><span class="spinner"></span></div>
		</div>

		<?php
	}

	/**
	 * Set options for the steps
	 * Incorporate any options set by the plugin dev
	 * Return the array for the steps
	 * @return Array
	 */
	public function get_steps() {
		$dev_steps = $this->config_steps;
		$steps = array(
			'intro' => array(
				'id'					=>	'intro',
				'title'				=>	__( 'Welcome to ', SIRAT_DEMO_IMPORTER_TEXT_DOMAIN ) . $this->plugin_title,
				'icon'				=>	'dashboard',
				'view'				=>	'get_step_intro', // Callback for content
				'callback'		=>	'do_next_step', // Callback for JS
				'button_text'	=>	__( 'Start Now', 'sirat-demo-importer' ),
				'can_skip'		=>	false, // Show a skip button?
				'icon_url'		=>	esc_url( SIRAT_DEMO_IMPORTER_EXT_URI . '/whizzie/assets/img/Icons-01.svg' )
			),
			'themes' => array(
				'id'					=>	'themes',
				'title'				=>	__( 'Theme', SIRAT_DEMO_IMPORTER_TEXT_DOMAIN ),
				'icon'				=>	'admin-appearance',
				'view'				=>	'get_step_themes',
				'callback'		=>	'install_themes',
				'button_text'	=>	__( 'Install Theme', SIRAT_DEMO_IMPORTER_TEXT_DOMAIN ),
				'can_skip'		=>	true
			),
			'plugins' => array(
				'id'					=>	'plugins',
				'title'				=>	__( 'Plugins', 'sirat-demo-importer' ),
				'icon'				=>	'admin-plugins',
				'view'				=>	'get_step_plugins',
				'callback'		=>	'install_plugins',
				'button_text'	=>	__( 'Install Plugins', 'sirat-demo-importer' ),
				'can_skip'		=>	true,
				'icon_url'		=>	esc_url( SIRAT_DEMO_IMPORTER_EXT_URI . '/whizzie/assets/img/Icons-02.svg' )
			),
			'widgets' => array(
				'id'							=>	'widgets',
				'title'						=>	__( 'Customizer', 'sirat-demo-importer' ),
				'icon'						=>	'welcome-widgets-menus',
				'view'						=>	'get_step_widgets',
				'callback'				=>	'install_widgets',
				'button_text_one'	=>	__( 'Click On The Image To Import Customizer Demo', 'sirat-demo-importer' ),
				'button_text_two'	=>	__( 'Click On The Image To Import Gutenberg Block Demo', 'sirat-demo-importer' ),
				'can_skip'				=>	true,
				'icon_url'				=>	esc_url( SIRAT_DEMO_IMPORTER_EXT_URI . '/whizzie/assets/img/Icons-03.svg' )
			),
			'done' => array(
				'id'				=>	'done',
				'title'			=>	__( 'All Done', 'sirat-demo-importer' ),
				'icon'			=>	'yes',
				'view'			=>	'get_step_done',
				'callback'	=>	'',
				'icon_url'	=>	esc_url( SIRAT_DEMO_IMPORTER_EXT_URI . '/whizzie/assets/img/Icons-04.svg' )
			)
		);

		// Iterate through each step and replace with dev config values
		if( $dev_steps ) {
			// Configurable elements - these are the only ones the dev can update from config.php
			$can_config = array( 'title', 'icon', 'button_text', 'can_skip' );
			foreach( $dev_steps as $dev_step ) {
				// We can only proceed if an ID exists and matches one of our IDs
				if( isset( $dev_step['id'] ) ) {
					$id = $dev_step['id'];
					if( isset( $steps[$id] ) ) {
						foreach( $can_config as $element ) {
							if( isset( $dev_step[$element] ) ) {
								$steps[$id][$element] = $dev_step[$element];
							}
						}
					}
				}
			}
		}
		return $steps;
	}

	/**
	 * Print the content for the intro step
	 */
	public function get_step_intro() {
		?>
		<div class="summary">
			<p>
				<?php esc_html_e( 'Thank you for choosing this ' . $this->plugin_title.' Plugin. Using this quick setup wizard, you will be able to configure your new website and get it running in just a few minutes. Just follow these simple steps mentioned in the wizard and get started with your website.', 'sirat-demo-importer' ); ?>
			</p>
			<p>
				<?php esc_html_e( 'You may even skip the steps and get back to the dashboard if you have no time at the present moment. You can come back any time if you change your mind.', 'sirat-demo-importer' ); ?>
			</p>
		</div>
		<?php
	}

	public function get_step_themes() {
		$themes = $this->get_themes();

		$content = array();
		// The summary element will be the content visible to the user
		$content['summary'] = sprintf(
			'<p>%s</p>',
			__(
				'This plugin works only when required themes are installed. Click the button to install. You can still install or deactivate plugins later from the dashboard.',
				SIRAT_DEMO_IMPORTER_TEXT_DOMAIN
			),
			SIRAT_DEMO_IMPORTER_TEXT_DOMAIN
		);
		$content = apply_filters( 'whizzie_filter_summary_content', $content );

		// The detail element is initially hidden from the user
		$content['detail'] = '<ul class="sirat-demo-importer-do-themes">';
		// Add each theme into a list
		foreach( $themes['all'] as $slug => $theme ) {
			$content['detail'] .= '<li data-slug="' . esc_attr( $slug ) . '">' . esc_html( $theme['name'] ) . '<span>';
			$keys = array();
			if ( isset( $themes['install'][ $slug ] ) ) {
			    $keys[]	=	esc_html( 'Installation' );
			}
			if ( isset( $themes['update'][ $slug ] ) ) {
			    $keys[]	=	esc_html( 'Update' );
			}

			if ( isset( $themes['network_enable'][ $slug ] ) ) {
			    $keys[]	=	esc_html( 'Network Enable' );
			}

			if ( isset( $themes['activate'][ $slug ] ) ) {
			    $keys[]	=	esc_html( 'Activation' );
			}
			$content['detail'] .= implode( ' and ', $keys ) . ' required';
			$content['detail'] .= '</span></li>';
		}
		$content['detail'] .= '</ul>';

		return $content;
	}




	/**
	 * Get the content for the plugins step
	 * @return $content Array
	 */
	public function get_step_plugins() {
		$plugins = $this->get_plugins();

		$content = array(); ?>
		<div class="summary">
			<p>
				<?php esc_html_e( 'Additional plugins always make your website exceptional. Install these plugins by clicking the install button. You may also deactivate them from the dashboard.', 'sirat-demo-importer' ); ?>
			</p>
		</div>

		<?php

		// The detail element is initially hidden from the user
		$content['detail'] = '<ul class="sirat-demo-importer-whizzie-do-plugins">';
		// Add each plugin into a list
		foreach( $plugins['all'] as $slug=>$plugin ) {
			$content['detail'] .= '<li data-slug="' . esc_attr( $slug ) . '">' . esc_html( $plugin['name'] ) . '<span>';
			$keys = array();
			if ( isset( $plugins['install'][ $slug ] ) ) {
			    $keys[] = esc_html( 'Installation' );
			}
			if ( isset( $plugins['update'][ $slug ] ) ) {
			    $keys[] = esc_html( 'Update' );
			}
			if ( isset( $plugins['activate'][ $slug ] ) ) {
			    $keys[] = esc_html( 'Activation' );
			}
			$content['detail'] .= implode( ' and ', $keys ) . ' required';
			$content['detail'] .= '</span></li>';
		}
		$content['detail'] .= '</ul>';

		return $content;
	}

	/**
	 * Print the content for the widgets step
	 * @since 1.1.0
	 */
	public function get_step_widgets() {
		?>
		<div class="summary">
			<p>
				<?php
				esc_html_e(
					'This theme supports importing the demo content and adding widgets. Get them installed with the below button. Using the Customizer, it is possible to update or even deactivate them',
					'sirat-demo-importer'
				);
				?>
			</p>
		</div>
		<?php
	}


	/**
	 * Print the content for the final step
	 */
	public function get_step_done() {

		?>
		<div class="sirat-demo-importer-setup-finish">
			<p>
				<?php echo esc_html('Your demo content has been imported successfully . Click on the finish button for more information.'); ?>
			</p>
			<div class="finish-buttons">
				<a href="<?php echo esc_url(admin_url('/customize.php')); ?>" class="wz-btn-customizer" target="_blank"><?php esc_html_e('Customize Your Demo','sirat-demo-importer') ?></a>
				<a href="" class="wz-btn-builder" target="_blank"><?php esc_html_e('Customize Your Demo','sirat-demo-importer'); ?></a>
				<a href="<?php echo esc_url(site_url()); ?>" class="wz-btn-visit-site" target="_blank"><?php esc_html_e('Visit Your Site','sirat-demo-importer'); ?></a>
			</div>
		</div>
		<div class="sirat-demo-importer-finish-btn">
			<a href="javascript:void(0);" class="button button-primary" onclick="openCity(event, 'theme_info')" data-tab="theme_info">
				<?php esc_html_e( 'Finish', 'sirat-demo-importer' ); ?>
			</a>
		</div>
	<?php

	}


	public function is_theme_available_to_network_activate( $slug ) {
		return !isset( wp_get_themes( array( 'errors' => false, 'allowed' => 'network' ) ) [$slug] );
	}


	public function can_theme_activate( $slug ) {
		return ( ( wp_get_theme()->get( 'TextDomain' ) != $slug ) && !get_theme_update_available( wp_get_theme( $slug ) ) );
	}


	public function get_themes() {
		$themes = array(
			'all' 						=>	array(),
			'install'					=>	array(),
			'update'					=>	array(),
			'network_enable'	=>	array(),
			'activate'				=>	array()
		);

		$instance_themes = array(
			'sirat'	=> array(
				'name' 								=> 'Sirat',
				'slug' 								=> 'sirat',
				'source' 							=> 'repo',
				'required' 						=> 1,
				'version' 						=> '',
				'force_activation' 		=> '',
				'force_deactivation'	=> '',
				'external_url' 				=> '',
				'is_callable' 				=> '',
				'file_path' 					=> 'sirat',
				'source_type' 				=> ''
			)
		);

		foreach( $instance_themes as $slug => $theme ) {

			if( ( wp_get_theme()->get( 'TextDomain' ) == $slug ) && ( false === get_theme_update_available( wp_get_theme() ) ) ) {
				// Theme is installed and up to date
				continue;
			} else {
				$themes['all'][$slug] = $theme;

				if( !wp_get_theme( $slug )->exists() ) {
					$themes['install'][$slug] = $theme;
				} else {

					if( false != get_theme_update_available( wp_get_theme( $slug ) ) ) {
						$themes['update'][$slug] = $theme;
					}

					if (
						current_user_can( 'manage_network_themes' ) &&
						$this->is_theme_available_to_network_activate( $slug ) &&
						$this->can_theme_activate( $slug )
					) {
						$themes['network_enable'][$slug] = $theme;
					} else if( $this->can_theme_activate( $slug ) ) {
						$themes['activate'][$slug] = $theme;
					}

				}
			}
		}

		return $themes;

	}




	/**
	 * Get the plugins registered with TGMPA
	 */
	public function get_plugins() {

		$instance = call_user_func( array( get_class( $GLOBALS['vw_sirat_pro'] ), 'get_instance' ) );

		$plugins = array(
			'all' 			=>	array(),
			'install'		=>	array(),
			'update'		=>	array(),
			'activate'	=>	array()
		);
		foreach( $instance->plugins as $slug=>$plugin ) {
			if( $instance->is_plugin_active( $slug ) && false === $instance->does_plugin_have_update( $slug ) ) {
				// Plugin is installed and up to date
				continue;
			} else {
				$plugins['all'][$slug] = $plugin;
				if( ! $instance->is_plugin_installed( $slug ) ) {
					$plugins['install'][$slug] = $plugin;
				} else {
					if( false !== $instance->does_plugin_have_update( $slug ) ) {
						$plugins['update'][$slug] = $plugin;
					}
					if( $instance->can_plugin_activate( $slug ) ) {
						$plugins['activate'][$slug] = $plugin;
					}
				}
			}
		}
		return $plugins;
	}




	public function sirat_demo_importer_setup_themes() {

		if ( ! check_ajax_referer( 'sirat_demo_importer_whizzie_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) ) {
			wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No Slug Found' ) ) );
		}

		$json = array();
		// send back some json we use to hit up TGM
		$themes = $this->get_themes();

		if ( current_user_can( 'manage_network_themes' ) ) {
			foreach ( $themes['network_enable'] as $slug => $theme ) {
				if ( $_POST['slug'] == $slug ) {
					$encoded_slug = urlencode( $slug );
					$theme_network_enable_url = wp_nonce_url(
						network_admin_url( 'themes.php?action=enable&amp;theme=' . $encoded_slug ), 'enable-theme_' . $slug
					);
					$theme_network_enable_url	=	str_replace( '&amp;', '&', $theme_network_enable_url );
					$json = array(
						'url'           =>	$theme_network_enable_url,
						'theme'        	=>	array( $slug ),
						'tgmpa-page'    =>	$this->sirat_demo_importer_tgmpa_menu_slug,
						'theme_status' 	=>	'all',
						'_wpnonce'      =>	wp_create_nonce( 'bulk-plugins' ),
						'action'        =>	$theme_network_enable_url,
						'action2'       =>	-1,
						'message'       =>	esc_html__( 'Network Enabling Theme' ),
					);
					break;
				}
			}
		}

		// what are we doing with this plugin?
		foreach ( $themes['activate'] as $slug => $theme ) {

			if ( $_POST['slug'] == $slug ) {

				$encoded_slug				=	urlencode( $slug );
				$theme_activate_url	=	wp_nonce_url(
					admin_url( 'themes.php?action=activate&amp;stylesheet=' . $encoded_slug ), 'switch-theme_' . $slug
				);
				$theme_activate_url	=	str_replace( '&amp;', '&', $theme_activate_url );
				$json = array(
					'url'           =>	$theme_activate_url,
					'theme'        	=>	array( $slug ),
					'tgmpa-page'    =>	$this->sirat_demo_importer_tgmpa_menu_slug,
					'theme_status' 	=>	'all',
					'_wpnonce'      =>	wp_create_nonce( 'bulk-plugins' ),
					'action'        =>	$theme_activate_url,
					'action2'       =>	-1,
					'message'       =>	esc_html__( 'Activating Theme' ),
				);
				break;
			}
		}

		foreach ( $themes['update'] as $slug => $theme ) {
			if ( $_POST['slug'] == $slug ) {
				$update_php				= admin_url( 'update.php?action=upgrade-theme' );
				$theme_update_url = add_query_arg(
					array(
						'theme'    => $slug,
						'_wpnonce' => wp_create_nonce( 'upgrade-theme_' . $slug ),
					),
					$update_php
				);
				$json = array(
					'url'           =>	$theme_update_url,
					'theme'        	=>	array( $slug ),
					'tgmpa-page'    =>	$this->sirat_demo_importer_tgmpa_menu_slug,
					'theme_status' 	=>	'all',
					'_wpnonce'      =>	wp_create_nonce( 'bulk-plugins' ),
					'action'        =>	$theme_update_url,
					'action2'       =>	-1,
					'message'       =>	esc_html__( 'Updating Theme' ),
				);
				break;
			}

		}

		foreach ( $themes['install'] as $slug => $theme ) {

			if ( $_POST['slug'] == $slug ) {
				$install_php				= admin_url( 'update.php?action=install-theme' );
				$theme_install_url	= add_query_arg(
					array(
						'theme'    => $slug,
						'_wpnonce' => wp_create_nonce( 'install-theme_' . $slug ),
					),
					$install_php
				);
				$json = array(
					'url'           =>	$theme_install_url,
					'theme'        	=>	array( $slug ),
					'tgmpa-page'    =>	$this->sirat_demo_importer_tgmpa_menu_slug,
					'theme_status' 	=>	'all',
					'_wpnonce'      =>	wp_create_nonce( 'bulk-plugins' ),
					'action'        =>	$theme_install_url,
					'action2'       =>	-1,
					'message'       =>	esc_html__( 'Installing Theme' ),
				);
				break;
			}
		}

		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next theme
			wp_send_json( $json );
		} else {
			wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Success' ) ) );
		}
		exit;
	}


	public function sirat_demo_importer_setup_plugins() {

		if ( ! check_ajax_referer( 'sirat_demo_importer_whizzie_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) ) {
			wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No Slug Found' ) ) );
		}

		$json = array();
		// send back some json we use to hit up TGM
		$plugins = $this->get_plugins();

		// what are we doing with this plugin?
		foreach ( $plugins['activate'] as $slug => $plugin ) {
			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'url'          	=>	admin_url( $this->tgmpa_url ),
					'plugin'       	=>	array( $slug ),
					'tgmpa-page'   	=>	$this->sirat_demo_importer_tgmpa_menu_slug,
					'plugin_status'	=>	'all',
					'_wpnonce'     	=>	wp_create_nonce( 'bulk-plugins' ),
					'action'       	=>	'sirat-demo-importer-tgmpa-bulk-activate',
					'action2'      	=>	- 1,
					'message'      	=>	esc_html__( 'Activating Plugin' ),
				);
				break;
			}
		}

		foreach ( $plugins['update'] as $slug => $plugin ) {
			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'url'          	=>	admin_url( $this->tgmpa_url ),
					'plugin'       	=>	array( $slug ),
					'tgmpa-page'   	=>	$this->sirat_demo_importer_tgmpa_menu_slug,
					'plugin_status'	=>	'all',
					'_wpnonce'     	=>	wp_create_nonce( 'bulk-plugins' ),
					'action'       	=>	'sirat-demo-importer-tgmpa-bulk-update',
					'action2'      	=>	- 1,
					'message'      	=>	esc_html__( 'Updating Plugin' ),
				);
				break;
			}
		}

		foreach ( $plugins['install'] as $slug => $plugin ) {

			$nonce_url = wp_nonce_url(
				add_query_arg(
					array(
						'plugin'				=>	urlencode( $plugin['slug'] ),
						'tgmpa-install'	=>	'install-plugin',
					),
					$GLOBALS['vw_sirat_pro']->get_tgmpa_url()
				),
				'tgmpa-install',
				'tgmpa-nonce'
			);

			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'url'          	=>	str_replace( '&amp;', '&', $nonce_url ),
					'plugin'       	=>	array( $slug ),
					'tgmpa-page'   	=>	$this->sirat_demo_importer_tgmpa_menu_slug,
					'plugin_status'	=>	'all',
					'_wpnonce'     	=>	wp_create_nonce( 'bulk-plugins' ),
					'action'       	=>	'sirat-demo-importer-tgmpa-bulk-install',
					'action2'      	=>	- 1,
					'message'      	=>	esc_html__( 'Installing Plugin' ),
				);
				break;
			}

		}

		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
			wp_send_json( $json );
		} else {
			wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Success' ) ) );
		}
		exit;

	}


	public function sirat_demo_importer_does_plugin_have_update( $plugin_file_path ) {
		wp_update_plugins();
		$update_plugins = get_site_transient( 'update_plugins' );
		if ( is_object( $update_plugins ) ) {
			$update_plugins_response = $update_plugins->response;
			return isset( $update_plugins_response[ $plugin_file_path ] );
		}
		return false;
	}


	public function can_plugin_activate_in_step_popup( $file_path ) {
		return ( ! is_plugin_active( $file_path ) && ! $this->sirat_demo_importer_does_plugin_have_update( $file_path ) );
	}


	public function get_plugins_step_popup() {

		$POST_elementor_plugins	=	Sirat_Demo_Importer_Whizzie::sdi_sanitize_array( $_POST['elementor_plugins'] );

		$new_plugins_array = array();
		foreach ( $POST_elementor_plugins as $plugin_single ) {

			$new_plugins_array[ $plugin_single['plugin_text_domain'] ] = array(
				'name'								=>	$plugin_single['plugin_title'],
				'slug'								=>	$plugin_single['plugin_text_domain'],
				'source'							=>	$plugin_single['plugin_url'] ? $plugin_single['plugin_url'] : 'repo',
				'required'						=>	1,
				'version'							=>	'',
				'force_activation'		=>	1,
				'force_deactivation'	=>	0,
				'external_url'				=>	'',
				'is_callable'					=>	'',
				'file_path'						=>	$plugin_single['plugin_text_domain'] . '/' . $plugin_single['plugin_main_file'],
				'source_type'					=>	$plugin_single['plugin_url'] ? 'bundled' : 'repo'
			);
		}

		$plugins = array(
			'all' 			=>	array(),
			'install'		=>	array(),
			'update'		=>	array(),
			'activate'	=>	array()
		);


		$slug_with_file_path	=	sanitize_text_field( $_POST['slug'] ) . '/' . sanitize_text_field( $_POST['file'] );
		$get_plugins					=	get_plugins();

		foreach( $new_plugins_array as $slug => $plugin ) {


			if( is_plugin_active( $plugin['file_path'] ) && false === $this->sirat_demo_importer_does_plugin_have_update( $plugin['file_path'] ) ) {
				// Plugin is installed and up to date
				continue;
			} else {
				$plugins['all'][$slug] = $plugin;

				// is plugin installed
				if( ! isset( $get_plugins[ $plugin['file_path'] ] ) ) {
					$plugins['install'][$slug] = $plugin;
				} else {
					if( false !== $this->sirat_demo_importer_does_plugin_have_update( $plugin['file_path'] ) ) {
						$plugins['update'][$slug] = $plugin;
					}
					if( $this->can_plugin_activate_in_step_popup( $plugin['file_path'] ) ) {
						$plugins['activate'][$slug] = $plugin;
					}
				}
			}
		}

		return $plugins;
	}


	public function sirat_demo_importer_setup_plugins_step_popup() {


		if ( ! check_ajax_referer( 'sirat_demo_importer_whizzie_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) ) {
			wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No Slug Found' ) ) );
		}

		$json = array();
		// send back some json we use to hit up TGM
		$plugins = $this->get_plugins_step_popup();

		// what are we doing with this plugin?
		foreach ( $plugins['activate'] as $slug => $plugin ) {

			$encoded_path = urlencode( $plugin['file_path'] );

			$activate_url = wp_nonce_url(
				admin_url( 'plugins.php?action=activate&amp;plugin=' . $encoded_path ),
				'activate-plugin_' . $plugin['file_path']
			);
			$activate_url	=	str_replace( '&amp;', '&', $activate_url );

			$plugin['url']	=	$activate_url;

			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'plugin'        => $plugin,
					'plugin_status' => 'all',
					'action'        => 'sirat-demo-importer-bulk-activate',
					'message'       => esc_html__( 'Activating Plugin' ),
				);
				break;
			}
		}


		foreach ( $plugins['update'] as $slug => $plugin ) {

			$encoded_path = urlencode( $plugin['file_path'] );

			$update_url = wp_nonce_url(
				admin_url( 'update.php?action=upgrade-plugin&amp;plugin=' . $encoded_path ),
				'upgrade-plugin_' . $plugin['file_path']
			);
			$update_url			=	str_replace( '&amp;', '&', $update_url );
			$plugin['url']	=	$update_url;

			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'plugin'        => $plugin,
					'plugin_status' => 'all',
					'action'        => 'sirat-demo-importer-bulk-update',
					'message'       => esc_html__( 'Updating Plugin' ),
				);
				break;
			}
		}


		foreach ( $plugins['install'] as $slug => $plugin ) {
			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'plugin'        => $plugin,
					'plugin_status' => 'all',
					'action'        => 'sirat-demo-importer-bulk-install',
					'message'       => esc_html__( 'Installing Plugin' ),
				);
				break;
			}
		}

		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
			wp_send_json( $json );
		} else {
			wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Success' ) ) );
		}
		exit;
	}


	public function wz_activate_sirat_demo_importer() {


		if ( !isset( $_POST['sirat_demo_importer_license_key'] ) ) {
			wp_send_json(
				array( 'status' => false, 'msg' => esc_html( 'License Key Is Required!' ) )
			);
		}


		$sirat_demo_importer_license_key	=	sanitize_text_field( $_POST['sirat_demo_importer_license_key'] );

		$endpoint													=	SIRAT_DEMO_IMPORTER_LICENCE_ENDPOINT . 'ibtana_license_activate_premium_addon';

		$body = [
			'add_on_key'					=>	$sirat_demo_importer_license_key,
			'site_url'						=>	site_url(),
			'add_on_text_domain'	=>	'sirat-demo-importer'
		];


		$body			=	wp_json_encode( $body );
		$options	=	[
			'body'		=>	$body,
			'headers'	=>	[
				'Content-Type'	=>	'application/json',
			]
		];
		$response = wp_remote_post( $endpoint, $options );
		if ( is_wp_error( $response ) ) {
			Sirat_Demo_Importer_Whizzie::remove_the_plugin_key();
			Sirat_Demo_Importer_Whizzie::set_the_validation_status('false');
			wp_send_json( array( 'status' => false, 'msg' => esc_html( 'Something Went Wrong!' ) ) );
			exit;
		} else {
			$response_body = wp_remote_retrieve_body( $response );
			$response_body = json_decode( $response_body );

			if ( $response_body->is_suspended == 1 ) {
				Sirat_Demo_Importer_Whizzie::set_the_suspension_status( 'true' );
			} else {
				Sirat_Demo_Importer_Whizzie::set_the_suspension_status( 'false' );
			}

			if ( $response_body->status === false ) {
				Sirat_Demo_Importer_Whizzie::remove_the_plugin_key();
				Sirat_Demo_Importer_Whizzie::set_the_validation_status('false');
				wp_send_json( array( 'status' => false, 'msg' => esc_html( $response_body->msg ) ) );
				exit;
			} else {
				Sirat_Demo_Importer_Whizzie::set_the_validation_status('true');
				Sirat_Demo_Importer_Whizzie::set_the_plugin_key( $sirat_demo_importer_license_key );
				wp_send_json( array( 'status' => true, 'msg' => esc_html( 'Plugin Activated Successfully!' ) ) );
				exit;
			}
		}
	}


	// Add a Custom CSS file to WP Admin Area
	public function sirat_demo_importer_admin_theme_style() {
		wp_enqueue_style( 'sirat-demo-importer-font', $this->sirat_demo_importer_admin_font_url(), array() );
		wp_enqueue_style('custom-admin-style', SIRAT_DEMO_IMPORTER_EXT_URI . 'whizzie/getstarted/getstart.css');
	}

	// Theme Font URL
	public function sirat_demo_importer_admin_font_url() {
		$font_url = '';
		$font_family = array();
		$font_family[] = 'Muli:300,400,600,700,800,900';

		$query_args = array(
			'family'	=> urlencode( implode( '|', $font_family ) ),
		);
		$font_url = add_query_arg($query_args,'//fonts.googleapis.com/css');
		return $font_url;
	}

}
