<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://jointventures.io
 * @since      1.0.0
 *
 * @package    Jv_Comments
 * @subpackage Jv_Comments/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Jv_Comments
 * @subpackage Jv_Comments/public
 * @author     Joint Ventures <admin@jointventures.io>
 */
class Jv_Comments_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
        $site = get_option('jv_comments_site');

        global $post;

        if ($site && $this->jv_embed_can_load_for_post( $post ) ) {
            wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/jv-comments-embed.js', array(), $this->version, false );
            $params = json_decode($site, true);

            if ($params['is_open'] === 'false') {
                $params['is_open'] = false;
            }

            wp_localize_script( $this->plugin_name, 'jv_comments_config', $params);
        }
    }

    /**
	 * Returns the JvComments embed comments template
	 *
	 * @since     1.0.0
	 * @return    string    The new comment text.
	 */
    public function jv_comments_template() {
        global $post;

        if ( $this->jv_embed_can_load_for_post( $post ) ) {
            return plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/jv-comments-public-display.php';
        }
    }

    /**
	 * Determines if JvComments is configured and can the comments embed on a given page.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @param     WP_Post $post    The WordPress post used to determine if JvComments can be loaded.
	 * @return    boolean          Whether JvComments is configured properly and can load on the current page.
	 */
    private function jv_embed_can_load_for_post( $post ) {
		// Make sure we have a $post object.
		if ( ! isset( $post ) ) {
			return false;
        }

		// Don't load embed for certain types of non-public posts because these post types typically still have the
		// ID-based URL structure, rather than a friendly permalink URL.
		$illegal_post_statuses = array(
			'draft',
			'auto-draft',
			'pending',
			'future',
			'trash',
		);
		if ( in_array( $post->post_status, $illegal_post_statuses ) ) {
			return false;
		}

		// Don't load embed when comments are closed on a post.
		if ( 'open' != $post->comment_status ) {
			return false;
        }

        // Don't load embed when comments are closed on a post. These lines can solve a conflict with plugin Public Post Preview.
		if ( ! comments_open() ) {
			return false;
        }

        // Don't load embed if it's not a single post page.
		if ( ! is_singular() ) {
			return false;
		}

        return true;
    }

}
