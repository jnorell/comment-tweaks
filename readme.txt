=== Comment Tweaks ===
Contributors: jnorell
Tags: comments, editor
Requires at least: 4.8
Tested up to: 5.1
Stable tag: trunk
Requires PHP: 5.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enhancements to Wordpress native comments

== Description ==

Comment Tweaks provides enhancements to Wordpress native comments.

= Features =

*   Allow comment authors to edit their own comments
*   Add WP editor (quicktags and/or tinymce) to comments area
*   Filter to customize WP editor settings (buttons/appearance)

= Bugs, Patches & Feature Requests =

Please submit any security issues found and they will be addressed.

You can submit bug reports or feature requests in the [GitHub issue tracker].  Patches are preferred as pull requests.

[GitHub issue tracker]: https://github.com/jnorell/comment-tweaks/issues

== Installation ==

= WordPress Admin =

Go to the 'Plugins' menu in WordPress, click 'Add New', search for 'Comment Tweaks', and click 'Install Now' for the 'Comment Tweaks' plugin.  Once installed, click 'Activate'.

= Plugin Upload =

An alternative to installing via the WordPress admin page is to upload the plugin to the WordPress installation on your web server.

1. Upload the plugin (the entire `comment-tweaks` directory and everything in it) to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Does Comment Tweaks work with Gutenberg enabled in WP 5.x? =

Yes.  The editor added to comments will be the WP editor (quicktags and/or tinymce), not Gutenberg.

= I can't find any settings for the plugin, are there any? =

Yes, the plugin settings are under Settings > Discussion > Comment Editing.

Customizing the editor settings is possible using a filter, there is currently no settings page for this.

= How can I customize the WP editor settings? =

You can use the `comment_tweaks_editor_settings` filter to customize the appearance of WP editor.

	<?php
	
	add_filter( 'comment_tweaks_editor_settings', function( $settings, $editor_id ) {
	
		$settings = array(
			'tinymce'      => array(
				'toolbar1'      => 'bold,italic,underline,bullist,numlist,aligncenter,blockquote,link,undo,redo',
				'plugins'       => 'charmap,colorpicker,hr,lists,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wptextpattern,media',
				'relative_urls' => true,
			),
			'quicktags'    => false,
		);
	
		return $settings;
	
	}, 10, 2);
	
	?>

= Can I use the 'Add Media' button =

Yes, the 'Add Media' button can be enabled using custom settings for the WP editor, allowing site users to access media.

	<?php
	
	add_filter( 'comment_tweaks_editor_settings', function( $settings, $editor_id ) {
	
		$settings = array(
			'mediaButtons' => true,
			'tinymce'      => array(
				'media_buttons' => true,
				'toolbar1'      => 'bold,italic,underline,bullist,numlist,aligncenter,blockquote,link,undo,redo',
				'plugins'       => 'charmap,colorpicker,hr,lists,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wptextpattern,media',
				'relative_urls' => true,
			),
			'quicktags'    => array(
				'buttons'       => 'strong,em,ul,ol,li,block,link,img,close',
			),
		);
	
		return $settings;
	
	}, 10, 2);
	
	?>

= Image captions display the [caption] shortcode =

Correct, enabling the 'Add Media' button allows users to add content and markup to the comments, but it does not change how that content is interpretted and displayed by your theme.  You can alter comments display to process shortcodes, but **be very careful not to create a security vulnerability**.

If anonymous users can add comments, and those comments are processed to execute shortcodes, you can easily allow anonymous users with many ways to abuse your site.

== Screenshots ==

1. WP editor enabled for comment field in twentynineteen theme.

== Changelog ==

= 1.1.1 =

Release Date:  February 22, 2019

* Improvement: Add Settings link to plugin admin screen.
* Fix: Reverse format_for_editor() when editing comments in the dashboard and tinymce is loaded.

= 1.1.0 =

Release Date:  February 14, 2019

* Feature:  Allow comment authors (logged in users) to edit their own comments (post author can not).
* Improvement: Add functions to manage plugin options.
* Improvement: Add admin setting for whether to add wp_editor to comments.
* Tweak: Save plugin version at activation.
* Tweak: Don't make ajax call if no comment_tweaks_editor_settings filters are set.

= 1.0.0 =

Release Date:  January 31, 2019

* Initial plugin version.
* Feature: add the WP editor to comments area.
* Feature: 'comment_tweaks_editor_settings' filter to customize WP editor.

