(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	// @todo: load settings via ajax
	// settings for initializing wp editor
	var editorSettings = { };
	$(function() {
		if ( typeof wp !== 'undefined' && typeof wp.editor !== 'undefined' ) {
			editorSettings = wp.editor.getDefaultSettings();
		}
		editorSettings.quicktags = false;
		// logged in users more featured editor
		if ( $( 'body' ).hasClass( 'logged-in' ) ) {
			editorSettings = {
				mediaButtons: true,
				tinymce: {
					media_buttons: true,
					toolbar1: 'bold,italic,underline,bullist,numlist,aligncenter,blockquote,link,undo,redo',
					plugins: 'charmap,colorpicker,hr,lists,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wptextpattern,media',
					relative_urls: true
				},
				quicktags: { buttons: 'strong,em,ul,ol,li,block,link,img,close' }
			};
		}
	});

	$(function() {
		if ( typeof wp === 'undefined' || typeof wp.editor === 'undefined' ) {
			return;
		}

		if ( typeof window.commentTweaks === 'undefined' ) {
			window.commentTweaks = {};
			window.commentTweaks.editorSettings = true;
			window.commentTweaks.delayedInit = {};

			window.commentTweaks.setEditorSettings = function( settings = null ) {
				if ( settings !== null ) {
					window.commentTweaks.editorSettings = settings;
				}
			}

			window.commentTweaks.initializeEditor = function( id, settings = null ) {
				if ( id in window.commentTweaks.delayedInit ) {
					delete window.commentTweaks.delayedInit[ id ];
				}
				window.commentTweaks.setEditorSettings( settings );

				/*
				 * @todo: test if editor for 'id' is already initialized
				 * (multiple calls to initialize() break things)
				 */
				wp.editor.initialize( id, window.commentTweaks.editorSettings );
			}

			window.commentTweaks.removeEditor = function( id ) {
				wp.editor.remove( id );
			}

		}

		var ct = window.commentTweaks;

		// @todo: load settings via ajax
		ct.setEditorSettings( editorSettings );

		// add editor to comments field
		ct.initializeEditor( 'comment' );

		// when replying to a comment, remove editor before the dom change, and add afterwards
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

			// save original onclick handler (created by addComment.moveForm above)
			var cancelLink = document.getElementById( 'cancel-comment-reply-link' );
			if ( typeof cancelLink.saveOnClick !== 'function' ) {
				cancelLink.saveOnClick = cancelLink.onclick;
			}

			/*
			 * cancel link: remove editor, call original handler, then add editor back
			 *
			 * this overwrites the onclick handler created by addComment.moveForm (above)
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

