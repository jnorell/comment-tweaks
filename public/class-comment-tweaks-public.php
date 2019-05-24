<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/jnorell/
 * @since      1.0.0
 *
 * @package    Comment_Tweaks
 * @subpackage Comment_Tweaks/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Comment_Tweaks
 * @subpackage Comment_Tweaks/public
 * @author     Jesse Norell <jesse@kci.net>
 */
class Comment_Tweaks_Public {

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
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * Currently not needed, and disabled in includes/class-comment-tweaks.php.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/comment-tweaks-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/*
		 * Enqueues WP Editor if needed.
		 *
		 * If the current page includes comments to be edited, enqueue the editor.
		 *
		 * @todo create admin option to enable/disable enqueuing media (default disabled)
		 */
		if ( user_can_richedit() && is_single() && comments_open() ) {
			if ( Comment_Tweaks::get_option( 'wp_editor' ) ) {
				wp_enqueue_media();
				wp_enqueue_editor();
			}
		} else {
			/*
			 * For now the entire plugin functionality is adding the editor,
			 * so we'll skip all other enqueues if not on those pages.
			 */
			return;
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/comment-tweaks-public.js', [ 'jquery', 'comment-reply', ], $this->version, false );

		if ( Comment_Tweaks::compatibility_check( 'jetpack_comments' ) ) {
			$wp_editor = 'false';
		} else {
			$wp_editor = Comment_Tweaks::get_option( 'wp_editor' ) ? 'true' : 'false';
		}

		// Pass nonce and other info to javascript as 'comment_tweaks' object.
		$comment_tweaks_nonce = wp_create_nonce( 'comment_tweaks' );
		wp_localize_script( $this->plugin_name, 'comment_tweaks', array(
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'nonce'               => $comment_tweaks_nonce,
			'is_user_logged_in'   => is_user_logged_in() ? 'true' : 'false',
			'wp_editor'           => $wp_editor,
			'get_editor_settings' => has_filter( 'comment_tweaks_editor_settings' ) ? 'true' : 'false',
		) );

	}

	/**
	 * Returns settings for wp_editor to javascript.
	 *
	 * Returns the settings as json to be used directly in wp.editor.initialize(),
	 * allowing customization via the 'comment_tweaks_editor_settings' filter.
	 *
	 * @since    1.0.0
	 */
	public function get_editor_settings() {
		check_ajax_referer( 'comment_tweaks', 'nonce' );

		$editor_id = 'comment';
		if ( isset( $_POST['editor_id'] ) ) {
			$editor_id = $_POST['editor_id'];
			if ( ! preg_match( '/^[A-Za-z][A-Za-z0-9_:\.-]*/', $editor_id ) ) {
				wp_send_json_error( "Invalid Data: editor_id is not a valid html element id.", 403 );
				wp_die();
			}
		}

		// Use wp.editor.initialize() default (which is: { 'tinymce': true }).
		$settings = null;

		/**
		 * Filters wp_editor settings used for editing comments.
		 *
		 * @since    1.0.0
		 *
		 * @param mixed  $settings  Array of editor arguments.
		 * @param string $editor_id ID of the editor for which settings are being filtered.  Default: 'comment'.
		 */
		$settings = apply_filters( 'comment_tweaks_editor_settings', $settings, $editor_id );

		wp_send_json_success( $settings );
		wp_die();
	}
}
