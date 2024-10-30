<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Fired during plugin deactivation
 *
 * @link       https://jointventures.io
 * @since      1.0.0
 *
 * @package    Jv_Comments
 * @subpackage Jv_Comments/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Jv_Comments
 * @subpackage Jv_Comments/includes
 * @author     Joint Ventures <admin@jointventures.io>
 */
class Jv_Comments_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
        delete_option('jv_comments_site');
        delete_option('jv_comments_admin_token');
	}

}
