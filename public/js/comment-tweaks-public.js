(function( $ ) {
	'use strict';

	$(function() {
		if ( typeof wp === 'undefined' || typeof wp.editor === 'undefined' ) {
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


		/**
		 * Handles removing and adding the editor to 'comment' when replying to a comment.
		 *
		 * When threaded comments are enabled, replying to an earlier comment moves
		 * #comment within the dom, so we must remove the editor before the dom change
		 * and add it again afterwards.
		 *
		 * @since 1.0.0
		 */
		$( '.comment-reply-link' ).click( function( e ) {
			e.preventDefault();

			ct.removeEditor( 'comment' );

			var args = $( this ).data( 'onclick' );
			if ( typeof args !== 'undefined' ) {
				args = args.replace( /.*\(|\)/gi, '' ).replace( /\"|\s+/g, '' );
				args = args.split( ',' );
				addComment.moveForm.apply( addComment, args );
			}

			ct.initializeEditor( 'comment' );

			// save original onclick handler added by addComment.moveForm()
			var cancelLink = document.getElementById( 'cancel-comment-reply-link' );
			if ( typeof cancelLink.saveOnClick !== 'function' ) {
				cancelLink.saveOnClick = cancelLink.onclick;
			}

			/*
			 * Overwrites the cancel comment reply link onclick handler.
			 *
			 * Overwrites the onclick handler for the cancel comment reply link
			 * (added dynamically by addComment.moveForm()), which moves #comment
			 * within the DOM.  First remove editor, call original handler,
			 * then add the editor back.
			 *
			 * @since 1.0.0
			 */
			cancelLink.onclick = function() {
				var ct = window.commentTweaks;

				ct.removeEditor( 'comment' );

				if ( typeof cancelLink.saveOnClick === 'function' ) {
					cancelLink.saveOnClick();
				}

				ct.initializeEditor( 'comment' );
			};
		});
	});

})( jQuery );

