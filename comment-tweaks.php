<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/jnorell/
 * @since             1.0.0
 * @package           Comment_Tweaks
 *
 * @wordpress-plugin
 * Plugin Name:       Comment Tweaks
 * Plugin URI:        https://github.com/jnorell/comment-tweaks/
 * Description:       Enhance Wordpress native comments (enables WP editor)
 * Version:           1.1.2
 * Author:            Jesse Norell
 * Author URI:        https://github.com/jnorell/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       comment-tweaks
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'COMMENT_TWEAKS_VERSION', '1.1.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-comment-tweaks-activator.php
 */
function activate_comment_tweaks() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-comment-tweaks-activator.php';
	Comment_Tweaks_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-comment-tweaks-deactivator.php
 */
function deactivate_comment_tweaks() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-comment-tweaks-deactivator.php';
	Comment_Tweaks_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_comment_tweaks' );
register_deactivation_hook( __FILE__, 'deactivate_comment_tweaks' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-comment-tweaks.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_comment_tweaks() {

	$plugin = new Comment_Tweaks();
	$plugin->run();

}
run_comment_tweaks();
