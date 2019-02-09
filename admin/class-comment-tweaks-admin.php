<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/jnorell/
 * @since      1.0.0
 *
 * @package    Comment_Tweaks
 * @subpackage Comment_Tweaks/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * - Defines the plugin name and version.
 * - Defines hooks to enqueue the admin-specific stylesheet and JavaScript.
 * - Registers admin settings, defines display and validation callbacks.
 *
 * @package    Comment_Tweaks
 * @subpackage Comment_Tweaks/admin
 * @author     Jesse Norell <jesse@kci.net>
 */
class Comment_Tweaks_Admin {

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
	 * @param    string    $plugin_name The name of this plugin.
	 * @param    string    $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * Currently not needed, and disabled in includes/class-comment-tweaks.php.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/comment-tweaks-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * Currently not needed, and disabled in includes/class-comment-tweaks.php.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/comment-tweaks-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add admin settings fields and Register admin settings.
	 *
	 * @since    1.1.0
	 */
	public function admin_settings() {

		$options = Comment_Tweaks::get_options();

		// add settings so they will display
		add_settings_field(
			"comment_tweaks_wp_editor",
			__( 'Comment Editing' ),
			array( $this, 'settings_checkbox' ),
			'discussion',
			'default',
			array(
			    'id'      => 'comment_tweaks_wp_editor',
			    'name'    => 'comment_tweaks[wp_editor]',
			    'label'   => __( 'Use WP Editor to edit comments' ),
			    'checked' => $this->sanitize_boolean( $options['wp_editor'] ),
			)
		);

		// register settings so they will be saved
		register_setting(
			'discussion',
			'comment_tweaks',
			array(
			    'sanitize_callback' => array( $this, 'validate_settings' ),
			)
		);

	}

	/**
	 * Outputs a checkbox for an admin setting.
	 *
	 * @since    1.1.0
	 *
	 * @param    array  $args {
	 *     @string   $id         The HTML id for this checkbox.
	 *     @string   $name       The HTML name for this checkbox.
	 *     @string   $label      Optional. The label displayed for this checkbox.
	 *     @boolean  $checked    Optional. The initial checked state of the checkbox.
	 *                           Default is false.
	 * }
	 */
	public function settings_checkbox( $args ) {
		$id = isset( $args['id'] ) ? $args['id'] : '';
		$name = isset( $args['name'] ) ? $args['name'] : '';
		$label = isset( $args['label'] ) ? $args['label'] : false;
		$checked = isset( $args['checked'] ) ? $args['checked'] : false;

		if ( $id == '' || $name == '' ) { ?>
			<!-- Comment_Tweaks::option_checkbox: required arg is missing --><?php
			return;
		}

		if ( $label ): ?>
			<label for="<?= $id ?>">
			    <input id="<?= $id ?>" type="checkbox" value="1" name="<?= $name ?>" <?php checked( $checked, true ); ?> />
			    <?= $label ?>
			</label> <?php
		else: ?>
			<input id="<?= $id ?>" type="checkbox" value="1" name="<?= $name ?>" <?php checked( $checked, true ); ?> /> <?php
		endif;
	}

	/**
	 * Validates array of admin setting.
	 *
	 * @since    1.1.0
	 *
	 * @param    array    $input    The settings to validate.
	 * @return   array    Settings after being sanitized
	 */
	public function validate_settings( $input ) {

		$output = Comment_Tweaks::get_options();

		$output['wp_editor'] = @$this->sanitize_boolean( $input['wp_editor'] );

		return $output;

	}

	/**
	 * Sanitizes a boolean admin setting.
	 *
	 * @since    1.1.0
	 *
	 * @param    bool   $input    The boolean value to sanitize.
	 * @return   int    1 if true, 0 if false.
	 */
	public static function sanitize_boolean( $input ) {
		return ( isset( $input ) && $input ) ? 1 : 0;
	}

}
