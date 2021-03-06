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
	 * Adds Settings link to plugin admin screen.
	 *
	 * @since    1.1.1
	 */
	public function add_action_links( $links ) {

		$settings = [ 'settings' => '<a href="options-discussion.php#comment_tweaks">' . __( 'Settings' ) . '</a>', ];
		$links = array_merge( $settings, $links );

		return $links;
	}

	/**
	 * Adds admin settings fields and Register admin settings.
	 *
	 * @since    1.1.0
	 */
	public function admin_settings() {

		$options = Comment_Tweaks::get_options();

		$wp_editor_notes = '';

		if ( Comment_Tweaks::compatibility_check( 'jetpack_comments' ) ) {
			$wp_editor_notes = '<br /><em>'
				. sprintf( __( 'Notice: You have the <a href="%s">comments module in Jetpack</a> enabled, which you will need to disable if you wish to use WP Editor to edit comments.' ),
						admin_url( 'admin.php?page=jetpack#/discussion' ) )
				. '</em>';
		}

		// add settings so they will display
		add_settings_field(
			"comment_tweaks",
			__( 'Comment Editing' ),
			array( $this, 'settings_checkboxes' ),
			'discussion',
			'default',
			array(
			    'anchor'     => 'comment_tweaks',
			    'legend'     => __( 'Comment Editing' ),
			    'checkboxes' => array(
			        array(
			            'id'      => 'comment_tweaks_wp_editor',
			            'name'    => 'comment_tweaks[wp_editor]',
			            'label'   => __( 'Use WP Editor to edit comments.' ) . $wp_editor_notes,
			            'checked' => $this->sanitize_boolean( $options['wp_editor'] ),
			        ),
			        array(
			            'id'      => 'comment_tweaks_edit_own_comments',
			            'name'    => 'comment_tweaks[edit_own_comments]',
			            'label'   => __( 'Comment authors can edit their comments.  Default Wordpress behavior is to allow post authors to edit all comments on their posts.  When enabled, this option lets users who are logged in edit their own comments, while post authors can still edit comments from anonymous users.' ),
			            'checked' => $this->sanitize_boolean( $options['edit_own_comments'] ),
			        ),
			    ),
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
	 * Outputs multiple checkboxes for admin settings.
	 *
	 * This outputs multiple checkboxes in a fieldset, which will display
	 * grouped together under a single settings title.
	 *
	 * This function outputs what is passed to it, escape/encode values as needed
	 * in the calling function.
	 *
	 * @since    1.1.0
	 *
	 * @param    array  $args {
	 *     'anchor'     => @string    $anchor    Optional. HTML anchor name.
	 *     'legend'     => @string    $legend    Optional. Screen reader text for the fieldset.
	 *     'checkboxes' => @array {
	 *         @array {
	 *             @string   $id         The HTML id for this checkbox.
	 *             @string   $name       The HTML name for this checkbox.
	 *             @string   $label      Optional. The label displayed for this checkbox.
	 *             @boolean  $checked    Optional. The initial checked state of the checkbox.
	 *                                   Default is false.
	 *         }
	 *     }
	 * }
	 */
	public function settings_checkboxes( $args ) {
		if ( ! is_array( $args ) ) {
			return;
		}

		if ( isset( $args['anchor'] ) ) {
			echo '<a id="' . $args['anchor'] . '_anchor" name="' . $args['anchor'] . '"></a>';
		}

		echo '<fieldset>';

		if ( isset( $args['legend'] ) ) { ?>
			<legend class="screen-reader-text">
				<span><?= $args['legend'] ?></span>
			</legend><?php
		}

		if ( isset( $args['checkboxes'] )  && is_array( $args['checkboxes'] ) ) {

			$cb_already = false;

			foreach ( $args['checkboxes'] as $cb_settings ) {

				if ( is_array( $cb_settings ) ) {

					if ( $cb_already ) {
						echo '<br />';
					} else {
						$cb_already = true;
					}

					$this->settings_checkbox( $cb_settings );
				}
			}
		}

		echo '</fieldset>';

	}

	/**
	 * Outputs a checkbox for an admin setting.
	 *
	 * This function outputs what is passed to it, escape/encode values as needed
	 * in the calling function.
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
		if ( ! is_array( $args ) ) {
			return;
		}

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

		$output['wp_editor']         = @$this->sanitize_boolean( $input['wp_editor'] );
		$output['edit_own_comments'] = @$this->sanitize_boolean( $input['edit_own_comments'] );

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

	/**
	 * Configures WP editor when editing comments on the dashboard.
	 *
	 * Called by {@see 'wp_editor_settings'} filter when on comments.php page.
	 *
	 * @since    1.1.1
	 */
	function wp_editor_settings( $settings, $id = 'content' ) {
		if ( ! ( is_admin() || user_can_richedit() ) ) {
			return $settings;
		}

		global $pagenow;

		if ( 'content' === $id && 'comment.php' === $pagenow ) {

			/** This filter is documented in public/class-comment-tweaks-public.php */
			$settings = apply_filters( 'comment_tweaks_editor_settings', $settings, $id );

			/* When tinymce is set, comment content is run through format_for_editor() */
			if ( isset( $settings['tinymce'] ) && ! empty( $settings['tinymce'] ) ) {
				if ( ! ( is_bool( $settings['tinymce'] ) && false === $settings['tinymce'] ) ) {
					add_filter( 'the_editor_content',  array( $this, 'unformat_for_editor' ), 20, 2 );
				}
			}
		}

		return $settings;
	}

	/**
	 * Reverse formatting done by format_for_editor();
	 *
	 * @since    1.1.1
	 *
	 * @param    string    $text    The HTML text being loaded in the editor.
	 * @return   string    HTML text after reversing format_for_editor().
	 */
	function unformat_for_editor( $text ) {

	    /* format_for_editor() runs:  htmlspecialchars( $text, ENT_NOQUOTES, get_option( 'blog_charset' ) ); */
	    $text = htmlspecialchars_decode( $text, ENT_NOQUOTES );

	    return $text;
	}

}
