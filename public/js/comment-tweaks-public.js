(function( $ ) {
	'use strict';

	$(function() {
		if ( typeof wp === 'undefined' || typeof wp.editor === 'undefined' ) {
			return;
		}

		if ( comment_tweaks.wp_editor === 'false' ) {
			return;
		}

		if ( typeof window.commentTweaks === 'undefined' ) {
			window.commentTweaks = {};
			window.commentTweaks.editorSettings = null;

			/**
			 * Saves settings to use when initializing wp.editor.
			 *
			 * @since 1.0.0
			 *
			 * @todo Allow specifying an editor id and save settings for multiple editors.
			 *
			 * @param {object} settings Configuration to save for newly initialized editors.
			 */
			window.commentTweaks.setEditorSettings = function( settings ) {
				window.commentTweaks.editorSettings = settings;
			}

			/**
			 * Adds wp.editor to dom element with the specified id.
			 *
			 * @since 1.0.0
			 *
			 * @param {string} id       ID of the dom element to initialize with wp.editor.
			 * @param {object} settings Optional.  Configuration for the newly initialized editor.
			 */
			window.commentTweaks.initializeEditor = function( id, settings = null ) {
				if ( settings !== null ) {
					window.commentTweaks.setEditorSettings( settings );
				}

				/*
				 * @todo test if editor for 'id' is already initialized
				 * (multiple calls to initialize() breaks things)
				 *
				 * (tinymce.editors() returns an array of all editors, loop through)
				 */
				wp.editor.initialize( id, window.commentTweaks.editorSettings );
			}

			/**
			 * Removes wp.editor with the specified id.
			 *
			 * @since 1.0.0
			 *
			 * @param {string} id ID of the editor to be removed.
			 */
			window.commentTweaks.removeEditor = function( id ) {
				wp.editor.remove( id );
			}

			/*
			 * @todo Define an event to load editor settings via javascript.
			 *
			 * The js event will fire first, before the ajax call which initializes
			 * the editor.
			 */

		}

		var ct = window.commentTweaks;

		if ( comment_tweaks.get_editor_settings === 'true' ) {
			// get editor settings for 'comment' editor
			var ajaxData = {
				'action':    'get_editor_settings',
				'nonce':     comment_tweaks.nonce,
				'editor_id': 'comment'
			};
			$.post( comment_tweaks.ajax_url, ajaxData, function( response ) {
				if ( response.success === true ) {
					ct.setEditorSettings( response.data );
				}

				// add editor to 'comment' field in ajax response
				ct.initializeEditor( 'comment' );
			});
		} else {
			// skipping ajax, add editor to 'comment' field now
			ct.initializeEditor( 'comment' );
		}


		/**
		 * Overwrites addComment.moveForm() and addComment.cancelEvent()
		 * to handle removing and adding the editor to #comment when replying to
		 * or canceling a reply to a comment.
		 *
		 * @since 1.1.3
		 */
		window.addEventListener( 'load', function() {

			if ( typeof addComment === 'undefined' ) {
				return;
			}

			// Save addComment.cancelEvent function (if any) prior to overwriting.
			addComment._commentTweaks_cancelEvent = addComment.cancelEvent;

			/**
			 * Overwrites addComment.cancelEvent() to handle removing and adding the editor
			 * to #comment when canceling a replying to a comment.
			 *
			 * Note this won't actually overwrite the original cancelEvent until/unless a reference to
			 * the original cancelEvent function is added to the addComment closure return.
			 *
			 * @since 1.1.3
			 */
			addComment.cancelEvent = function( event ) {
				var cancelLink = this;
				var temporaryElement = document.getElementById( 'wp-temp-form-div' );
				var respondElement = document.getElementById( addComment._commentTweaks_respondId );

				// If no #wp-temp-form-div, check for our rename.
				if ( ! temporaryElement ) {
					temporaryElement = document.getElementById( 'wp-temp-form-div--comment-tweaks' );
				}

				if ( ! temporaryElement || ! respondElement ) {
					// Conditions for cancel link fail.
					return;
				}

				if ( typeof tinymce === 'object' ) {
					// Preserve editor content when canceling a reply if it existed when hitting Reply.
					var preserveContent = tinymce.get( 'comment' )._commentTweaks_preserveContent;
				}

				// Disable editor.
				ct.removeEditor( 'comment' );

				// Move the respond form back in place of the temporary element.
				document.getElementById( 'comment_parent' ).value = '0';
				temporaryElement.parentNode.replaceChild( respondElement, temporaryElement );
				cancelLink.style.display = 'none';

				// Re-initialize error.
				ct.initializeEditor( 'comment' );

				if ( typeof tinymce === 'object' ) {
					// Preserve editor content when canceling a reply if it existed when hitting Reply.
					if ( parseInt( preserveContent ) > 0 ) {
						tinymce.get( 'comment' ).focus();
					} else {
						tinymce.get( 'comment' ).setContent( '' );
					}
				}

				event.preventDefault();
			};

			// Save addComment.moveForm function prior to overwriting.
			//
			// Source: Jetpack comments module by Automattic.
			// See https://github.com/Automattic/jetpack/blob/2e9efb22810cbd0e60ad2d2a9158e47a4432577c/modules/comments/comments.php#L375-L413
			addComment._commentTweaks_moveForm = addComment.moveForm;

			/**
			 * Overwrites addComment.moveForm() to handle removing and adding the editor
			 * to #comment when replying to a comment.
			 *
			 * When threaded comments are enabled, replying to an earlier comment moves
			 * #comment within the dom, so we must remove the editor before the dom change
			 * and add it again afterwards.
			 *
			 * @since 1.1.3
			 *
			 * @return boolean  Return false to prevent default click event.
			 */
			addComment.moveForm = function(  commId, parentId, respondId, postId ) {
				var tempElement = document.getElementById( 'wp-temp-form-div--comment-tweaks' );

				// Change renamed #wp-temp-form-div back to original id.
				if ( tempElement ) {
					tempElement.id = 'wp-temp-form-div';
				}

				if ( typeof tinymce === 'object' ) {
					// Preserve editor content when canceling a reply if it existed when hitting Reply.
					var preserveContent = tinymce.get( 'comment' ).getContent().length;
				}

				// Disable editor.
				commentTweaks.removeEditor( 'comment' );

				// Save respondId for use in canceEvent.
				addComment._commentTweaks_respondId = respondId;

				// Call saved/original moveForm.
				addComment._commentTweaks_moveForm(  commId, parentId, respondId, postId );

				// Re-initialize error.
				ct.initializeEditor( 'comment' );

				if ( typeof tinymce === 'object' ) {
					tinymce.get( 'comment' )._commentTweaks_preserveContent = preserveContent;
					tinymce.get( 'comment' ).focus();
				}

				var cancelElement = document.getElementById( 'cancel-comment-reply-link' );

				if ( ! cancelElement ) {
					return false;
				};

				// If there is a saved cancelEvent function we're on WP 5.1+ and
				// our custom cancelEvent function will fire, otherwise we need
				// to set that as the cancel reply link onclick handler.
				//
				// Note: to succeed this check, addComment closure return must
				// contain a reference to cancelEvent, which it does not as of 5.2.1.
				if ( typeof addComment._commentTweaks_cancelEvent === 'function' ) {

					// remove listener calling original cancelEvent()
					cancelElement.removeEventListener( 'touchstart', addComment._commentTweaks_cancelEvent );
					cancelElement.removeEventListener( 'click',      addComment._commentTweaks_cancelEvent );

					// add listener calling our cancelEvent()
					cancelElement.addEventListener( 'touchstart', addComment.cancelEvent );
					cancelElement.addEventListener( 'click',      addComment.cancelEvent );

				} else {

					// Rename wp-temp-form-div so original cancel returns false.
					//
					// Needed for WP 5.1+, which currently have no reference to cancelEvent
					// function, and which fires ahead of the onclick event assigned below.
					document.getElementById( 'wp-temp-form-div' ).id = 'wp-temp-form-div--comment-tweaks';

					// Overwrite original onclick handler added by addComment.moveForm()
					//
					// Needed for WP < 5.1, which set cancel link onclick handler inline in original
					// moveForm() (called above).
					cancelElement.ontouchstart = addComment.cancelEvent;
					cancelElement.onclick = addComment.cancelEvent;

				};

				// Return false signals click event handler to run event.preventDefault().
				return false;

			};

		});

	});

})( jQuery );

