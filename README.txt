=== Plugin Name ===
Contributors: jnorell
Tags: comments, editor
Requires at least: 4.8.0
Tested up to: 5.0.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enhancements to Wordpress native comments (enables WP editor)

== Description ==

Comment Tweaks provides enhancements to Wordpress native comments.

= Features =

*   Add WP editor (quicktags and/or tinymce) to comments area

Yes, a single feature.  This is the initial release.  :)

@todo: Note on wp_editor settings filter...

== Installation ==

= WordPress Admin =

Go to the 'Plugins' menu in WordPress, click 'Add New', search for 'Comment Tweaks',
and click 'Install Now' for the 'Comment Tweaks' plugin.  Once installed, click 'Activate'

= Plugin Upload =

An alternative to installing via the WordPress admin page is to upload the plugin to the
WordPress installation on your web server.

1. Upload the plugin (the entire `comment-tweaks` directory and everything in it) to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Does Comment Tweaks work with Gutenberg enabled in WP 5.x =

Yes.  The editor added to comments will be the WP editor (quicktags and/or tinymce), not Gutenberg.

= I can't find any settings for the plugin, are there any =

No, currently the plugin has only a single function, to enable the WP editor in comments area,
so just disable the plugin if you want that feature disabled.  Customizing the editor settings is
possible using a filter, there is currently no settings page.

= How can I customize the WP editor settings =

You can use the `comment_tweaks_wp_editor_settings` filter to customize the appearance of WP editor.

``
<?php echo example( "@todo" ); ?>
``

= Can I use the 'Add Media' button =

Yes, the 'Add Media' button can be enabled using custom settings for the WP editor,
allowing site users to access media.

``
<?php echo another_example( "@todo" ); ?>
``

= Image captions display the [caption] shortcode =

Correct, enabling the 'Add Media' button allows users to add content and markup to the comments,
but it does not change how that content is interpretted and displayed by your theme.  You can
alter comments display to process shortcodes, but **be very careful not to create a security
vulnerability**.

If anonymous users can add comments, and those comments are processed to execute
shortcodes, you can easily allow anonymous users with many ways to abuse your site.

== Screenshots ==

1. @todo:  WP editor for comments area (/assets/screenshot-1.png)

== Changelog ==

= 1.0.0 =

Release Date:  TBD (FIXME)

* Initial plugin version
* Single feature: adding the WP editor to comments area

