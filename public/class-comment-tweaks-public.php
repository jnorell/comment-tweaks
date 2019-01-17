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
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * Currently none needed.
		 */
		return;

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Comment_Tweaks_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Comment_Tweaks_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/comment-tweaks-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * Enqueue WP Editor if needed.
		 *
		 * If the current page includes comments to be edited, enqueue the editor.
		 *
		 * @todo create admin option to disable this (currently the only plugin functionality)
		 */
		if ( user_can_richedit() && is_single() && comments_open() ) {
			wp_enqueue_media();
			wp_enqueue_editor();

// @todo should we enqueue comment-reply, or just assume dom matches
// will prevent undefined functions (addComment.moveForm.apply()) being called?
//			if ( get_option( 'thread_comments') ) {
//				wp_enqueue_script( 'comment-reply' );
//			}
		}

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Comment_Tweaks_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Comment_Tweaks_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/comment-tweaks-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Save #comment onclick data for later use.
	 *
	 * Save #comment onclick data for later parsing in javascript
	 * when creating a new onclick function.
	 *
	 * @since    1.0.0
	 */
	public function comment_reply_link($link) {
		return str_replace( 'onclick=', 'data-onclick=', $link );
	}

}
