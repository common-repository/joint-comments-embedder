<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://jointventures.io
 * @since      1.0.0
 *
 * @package    Jv_Comments
 * @subpackage Jv_Comments/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Jv_Comments
 * @subpackage Jv_Comments/admin
 * @author     Joint Ventures <admin@jointventures.io>
 */
class Jv_Comments_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
        // @TODO Disable for now.
        // @TODO Enable later.
        // wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'bundles/style.bundle.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
        if ( ! isset( $_GET['page'] ) || 'jv_comments' !== $_GET['page'] ) {
            return;
        }

        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $auth = get_option('jv_comments_admin_token');
        $site = get_option('jv_comments_site');

        $hasToken = $auth ? true : false;

        $admin_js_vars = array(
            'rest' => array(
                'base' => esc_url_raw( rest_url( '/' ) ),
                'jvBase' => 'jv_comments/v1',

                // Nonce is required so that the REST api permissions can recognize a user/check permissions.
                'nonce' => wp_create_nonce( 'wp_rest' ),
            ),
            'site' => $site ? json_decode($site) : null,
            'auth' => $hasToken,
        );


		wp_enqueue_script( $this->plugin_name . '_admin', plugin_dir_url( __FILE__ ) . 'bundles/bundle.js', array(), $this->version, true );
        wp_localize_script( $this->plugin_name . '_admin', 'JV_COMMENTS_WP', $admin_js_vars );
    }

    public function jv_contruct_admin_menu() {
        if ( ! current_user_can( 'moderate_comments' ) ) {
            return;
        }

        // Replace the existing WordPress comments menu item to prevent confusion
        // about where to administer comments. The JV Comments page will have a link to
        // see WordPress comments.
        remove_menu_page( 'edit-comments.php' );

        add_menu_page(
            'JV Comments',
            'JV Comments',
            'moderate_comments',
            'jv_comments',
            array( $this, 'jv_render_admin_index' ),
            'dashicons-admin-comments',
            24
        );
    }

    /**
     * Builds the admin menu with the various JV Comments options
     *
     * @since    3.0
     * @param    WP_Admin_Bar $wp_admin_bar    Instance of the WP_Admin_Bar.
     */
    public function jv_construct_admin_bar( $wp_admin_bar ) {
        if ( ! current_user_can( 'moderate_comments' ) ) {
            return;
        }

        // Replace the existing WordPress comments menu item to prevent confusion
        // about where to administer comments. The JV Comments page will have a link to
        // see WordPress comments.
        $wp_admin_bar->remove_node( 'comments' );

        // @TODO Implement
    }

    /**
     * Renders the admin page view from a partial file
     *
     * @since    1.0.0
     */
    public function jv_render_admin_index() {
        require_once plugin_dir_path( __FILE__ ) . 'partials/jv-comments-admin-display.php';
    }
}
