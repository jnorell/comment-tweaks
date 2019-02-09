<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/jnorell/
 * @since      1.0.0
 *
 * @package    Comment_Tweaks
 * @subpackage Comment_Tweaks/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Comment_Tweaks
 * @subpackage Comment_Tweaks/includes
 * @author     Jesse Norell <jesse@kci.net>
 */
class Comment_Tweaks_Activator {

	/**
	 * Perform needed updates from previous versions and store current version number.
	 *
	 * - No updates currently needed, merely save the version number.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		$updated_to = Comment_Tweaks::get_option( 'version' );

		/* version-dependent updates when needed...
		if ( version_compare( $updated_to, '1.2.3' ) < 0 ) {
			// do updates for 1.2.3
			$updated_to = '1.2.3';
		}
		if ( version_compare( $updated_to, '2.3.4' ) < 0 ) {
			// do updates for 2.3.4
			$updated_to = '2.3.4';
		}
		*/

		if ( version_compare( $updated_to, COMMENT_TWEAKS_VERSION ) < 0 ) {
			$updated_to = COMMENT_TWEAKS_VERSION;
		}

		Comment_Tweaks::update_option( 'version', $updated_to );
	}

}
