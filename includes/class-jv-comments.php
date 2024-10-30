<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://jointventures.io
 * @since      1.0.0
 *
 * @package    Jv_Comments
 * @subpackage Jv_Comments/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Jv_Comments
 * @subpackage Jv_Comments/includes
 * @author     Joint Ventures <admin@jointventures.io>
 */
class Jv_Comments {

    /**
     * Instance of the JvComments API service.
     *
     * @since    1.0.0
     * @access   private
     * @var      JvComments_Api_Service    $api_service    Instance of the JvComments API service.
     */
    private $api_service;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Jv_Comments_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'JV_COMMENTS_VERSION' ) ) {
			$this->version = JV_COMMENTS_VERSION;
		} else {
			$this->version = '1.0.1';
		}
		$this->plugin_name = 'jv-comments';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_rest_api_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Jv_Comments_Loader. Orchestrates the hooks of the plugin.
	 * - Jv_Comments_i18n. Defines internationalization functionality.
	 * - Jv_Comments_Admin. Defines all hooks for the admin area.
	 * - Jv_Comments_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jv-comments-loader.php';

        /**
         * The class responsible making requests to the Disqus API.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jv-comments-api-service.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jv-comments-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-jv-comments-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-jv-comments-public.php';

        /**
         * The class responsible for defining all actions that occur on the REST API of
         * the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'rest-api/class-jv-comments-rest-api.php';

        $this->api_service = new JvComments_Api_Service();
		$this->loader = new Jv_Comments_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Jv_Comments_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Jv_Comments_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

        $plugin_admin = new Jv_Comments_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'jv_contruct_admin_menu' );
        $this->loader->add_action( 'admin_bar_menu', $plugin_admin, 'jv_construct_admin_bar', 999 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Jv_Comments_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_filter( 'comments_template', $plugin_public, 'jv_comments_template' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

    }

    /**
     * Register all of the hooks related to the REST API functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_rest_api_hooks() {
        $plugin_rest_api = new JvComments_Rest_Api( $this->get_api_service(), $this->get_version() );

        $this->loader->add_action( 'rest_api_init', $plugin_rest_api, 'jv_comments_register_endpoints' );
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

    /**
     * Returns instance of the JvComments API service.
     *
     * @since     1.0.0
     * @return    string    Instance of the JvComments API service.
     */
    public function get_api_service() {
        return $this->api_service;
    }

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Jv_Comments_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
