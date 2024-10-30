<?php

/**
 *
 * @link              https://jointventures.io
 * @since             1.0.0
 * @package           Jv_Comments
 *
 * @wordpress-plugin
 * Plugin Name:       Joint Comments Embedder
 * Plugin URI:        https://jointcomments.com
 * Description:       Embeddable Comment Widget - "Your content deserves much more comments."
 * Version:           1.0.1
 * Author:            Joint Ventures
 * Author URI:        https://jointventures.io
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jv-comments
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'JV_COMMENTS_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-jv-comments-activator.php
 */
function activate_jv_comments() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-jv-comments-activator.php';
	Jv_Comments_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-jv-comments-deactivator.php
 */
function deactivate_jv_comments() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-jv-comments-deactivator.php';
	Jv_Comments_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_jv_comments' );
register_deactivation_hook( __FILE__, 'deactivate_jv_comments' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-jv-comments.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_jv_comments() {

	$plugin = new Jv_Comments();
	$plugin->run();

}
run_jv_comments();
