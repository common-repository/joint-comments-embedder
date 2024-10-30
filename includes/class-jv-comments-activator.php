<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Fired during plugin activation
 *
 * @link       https://jointventures.io
 * @since      1.0.0
 *
 * @package    Jv_Comments
 * @subpackage Jv_Comments/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Jv_Comments
 * @subpackage Jv_Comments/includes
 * @author     Joint Ventures <admin@jointventures.io>
 */
class Jv_Comments_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        if ( version_compare( phpversion(), '5.4', '<' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( 'JV Comments requires PHP version 5.4 or higher. Plugin was deactivated.' );
        }
	}

}
