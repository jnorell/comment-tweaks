<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/jnorell/
 * @since      1.0.0
 *
 * @package    Comment_Tweaks
 * @subpackage Comment_Tweaks/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Comment_Tweaks
 * @subpackage Comment_Tweaks/includes
 * @author     Jesse Norell <jesse@kci.net>
 */
class Comment_Tweaks {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Comment_Tweaks_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'COMMENT_TWEAKS_VERSION' ) ) {
			$this->version = COMMENT_TWEAKS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'comment-tweaks';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_shared_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Comment_Tweaks_Loader. Orchestrates the hooks of the plugin.
	 * - Comment_Tweaks_i18n. Defines internationalization functionality.
	 * - Comment_Tweaks_Admin. Defines all hooks for the admin area.
	 * - Comment_Tweaks_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-comment-tweaks-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-comment-tweaks-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-comment-tweaks-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-comment-tweaks-public.php';

		$this->loader = new Comment_Tweaks_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Comment_Tweaks_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Comment_Tweaks_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Comment_Tweaks_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		// Currently not needed, so disabled for efficiency
		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add Settings link to plugin admin screen
		if ( ! empty ( $GLOBALS['pagenow'] ) and ( 'plugins.php' === $GLOBALS['pagenow'] ) ) {
			$this->loader->add_filter( 'plugin_action_links_comment-tweaks/comment-tweaks.php', $plugin_admin, 'add_action_links', 10, 1 );
		}

		// Add and register admin settings when needed
		if ( ! empty ( $GLOBALS['pagenow'] )
		    and ( 'options-discussion.php' === $GLOBALS['pagenow'] or 'options.php' === $GLOBALS['pagenow'] )
		   ) {
			$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_settings' );
		}

		//  Configure rich text editor for editing comments on the dashboard.
		if ( ! empty ( $GLOBALS['pagenow'] ) and ( 'comment.php' === $GLOBALS['pagenow'] ) ) {
			$this->loader->add_filter( 'wp_editor_settings', $plugin_admin, 'wp_editor_settings', 10, 1 );
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		global $wp_version;

		$plugin_public = new Comment_Tweaks_Public( $this->get_plugin_name(), $this->get_version() );

		// Currently not needed, so disabled for efficiency
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		if ( $this->get_option( 'wp_editor' ) ) {
            // WP 5.1 improved reply/cancel link click handler, no longer uses onclick attribute
			if ( version_compare( $wp_version, '5.1' ) < 0 ) {
				$this->loader->add_filter( 'comment_reply_link', $plugin_public, 'comment_reply_link', 10, 4 );
			}

            // get_post_reply_link() uses the same onclick attribute calling addComment.moveForm(),
            // even in WP 5.1, filtered via post_comments_link
			$this->loader->add_filter( 'post_comments_link', $plugin_public, 'comment_reply_link', 10, 4 );

			if ( is_admin() ) {
				$this->loader->add_action( 'wp_ajax_get_editor_settings', $plugin_public, 'get_editor_settings' );
				$this->loader->add_action( 'wp_ajax_nopriv_get_editor_settings', $plugin_public, 'get_editor_settings' );
			}
		}

	}

	/**
	 * Register all of the hooks related to functionality of the plugin
	 * shared by to the admin area and the public-facing side.
	 *
	 * @since    1.1.0
	 * @access   private
	 */
	private function define_shared_hooks() {

		if ( $this->get_option( 'edit_own_comments' ) ) {
			$this->loader->add_filter( 'map_meta_cap', $this, 'meta_cap_edit_comment', 10, 4 );
		}

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Comment_Tweaks_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Retrieve wp_option used by the plugin and fill default values.
	 *
	 * This is akin to wordpress get_options(), but storing our plugin
	 * options as an array in a single wp_option.
	 *
	 * @since     1.1.0
	 * @return    array    The plugin options.
	 */
	public static function get_options() {

		$default_options = array(
		    'version'           => ( defined ( 'COMMENT_TWEAKS_VERSION' ) ? COMMENT_TWEAKS_VERSION : null ),
		    'wp_editor'         => 1,
		    'edit_own_comments' => 0,
		);

		$options = get_option( 'comment_tweaks' );

		if ( ! is_array( $options ) ) {
			return $default_options;
		}

		$options = array_merge( $default_options, $options );

		return $options;
	}

	/**
	 * Retrieve a single option within the wp_option array used by the plugin.
	 *
	 * This is akin to wordpress get_option(), but storing our plugin
	 * options as an array in a single wp_option.
	 *
	 * @since     1.1.0
	 * @return    mixed    The named option from the plugin options or null.
	 */
	public static function get_option( $name ) {
		$options = Comment_Tweaks::get_options();

		return isset( $options[$name] ) ? $options[$name] : null;
	}

	/**
	 * Set an option within the wp_option array used by the plugin.
	 *
	 * This is akin to wordpress update_option(), but storing our plugin
	 * options as an array in a single wp_option.  *
	 * @since     1.1.0
	 * @param     string    $name    The name of the option to set.
	 * @param     mixed     $value   The value of the option to set.
	 * @return    boolean   True if option value has changed, false if not or if update failed.
	 */
	public static function update_option( $name, $value ) {
		$options = Comment_Tweaks::get_options();

		if ( isset( $options[$name] ) && $options[$name] === $value ) {
			return false;
		}

		$options[$name] = $value;

		return update_option( 'comment_tweaks', $options );
	}

	/**
	 * Add an option within the wp_option array used by the plugin.
	 *
	 * This is akin to wordpress add_option(), but storing our plugin
	 * options as an array in a single wp_option.
	 *
	 * @since     1.1.0
	 * @param     string    $name    The name of the option to set.
	 * @param     mixed     $value   The value of the option to set.
	 * @return    boolean   True if option value has changed, false if not or if update failed.
	 */
	public static function add_option( $name, $value ) {
		return Comment_Tweaks::update_options( $name, $value );
	}

	/**
	 * Gives 'edit_comment' meta capability to comment author.
	 *
	 * Wordpress edit_comment_link() checks the 'edit_comment' meta capability,
	 * which this remaps to 'read' for logged in users, allowing comment authors
	 * to edit their own comments, and prohibiting post authors from the comments
	 * of other users.  Called via {@see 'map_meta_cap'} filter.
	 *
	 * @since     1.1.0
	 */
	public function meta_cap_edit_comment( $caps, $cap, $user_id, $args ) {
		if ( $user_id == 0 ) {
			return $caps;
		}

		if ( 'edit_comment' === $cap ) {
			if ( user_can( $user_id, 'moderate_comments' ) ) {
				return $caps;
			}

			if ( user_can( $user_id, 'read' ) ) {
				$comment_id = $args[0];
				$comment = get_comment( $comment_id, OBJECT );
				$post_id = $comment->comment_post_ID;

				if ( 'spam' === $comment->comment_approved ) {
					return $caps;
				}

				if ( $user_id == $comment->user_id ) {
					// make 'read' the only required capability for comment authors
					$caps = [ 'read' ];
				} elseif ( 0 != $comment->user_id ) {
					// make 'moderate_comments' required to edit comments by other users
					$caps[] = 'moderate_comments';
				}
				// else anonymous comment, pass $caps unchanged
			}
		}

		return $caps;

	} //end meta_cap_edit_comment

}
