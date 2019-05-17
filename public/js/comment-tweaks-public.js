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
console.log ( 'commentTweaks: defining window.commentTweaks' );
			window.commentTweaks = {};
			window.commentTweaks.editorSettings = null;
			window.commentTweaks.respondId = null;

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
console.log( 'commentTweaks.initializeEditor called for ' + id );
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
console.log( 'commentTweaks.removeEditor called for ' + id );
				wp.editor.remove( id );
			}

			/**
			 * Comment reply link click event handler.
			 *
			 * @since 1.1.3
			 *
			 * @param {Event} event The calling event.
			 */
			window.commentTweaks.commentReplyLinkClickEvent = function( event ) {
				window.commentTweaks.removeEditor( 'comment' );
				if ( typeof window.addComment.clickEvent === 'function' ) {
                    console.log ( 'good so far, at this point we should call real clickEvent to move the comment form' );
					window.addComment.clickEvent( event );
				}
				window.commentTweaks.initializeEditor( 'comment' );
			}

			window.commentTweaks.cancelCommentReplyLinkClickEvent = function( event ) {
				console.log( 'cancelCommentReplyLinkClickEvent called here' );
			}

			/**
			 * Replaces a node with a clone to remove event listeners.
			 *
			 * @since 1.1.3
			 *
			 * @param {HTMLElement} el           DOM element to clone and replace.
			 * @param {Boolean}     withChildren True to clone all child elements
			 *                                   which will remove their event listeners, too.
			 */
			window.commentTweaks.recreateNode = function( el, withChildren ) {
                console.log( 'recreateNode called for ' + el );
				if ( withChildren ) {
					el.parentNode.replaceChild( el.cloneNode(true), el );
				} else {
					var newEl = el.cloneNode( false );
					while ( el.hasChildNodes() ) {
						newEl.appendChild( el.firstChild );
					}
					el.parentNode.replaceChild( newEl, el );
				}
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
		 * Handles removing and adding the editor to 'comment' when replying to a comment.
		 *
		 * When threaded comments are enabled, replying to an earlier comment moves
		 * #comment within the dom, so we must remove the editor before the dom change
		 * and add it again afterwards.
		 *
		 * @since 1.0.0
		 */
		window.addEventListener( 'load', function() {

		var ctReplyLinks = document.getElementsByClassName( 'comment-reply-link' );
        var ctCancelLink = document.getElementById( 'cancel-comment-reply-link' );
		var i;

/* currently there is no reference exported to addComment.clickEvent or addComment.cancelEvent
		if ( typeof window.addComment.clickEvent === 'function' ) {
			for ( i = 0; i < ctReplyLinks.length; i++ ) {
				ctReplyLinks[i].removeEventListener( 'touchstart', window.addComment.clickEvent );
				ctReplyLinks[i].removeEventListener( 'click',      window.addComment.clickEvent );
//				ctReplyLinks[i].addEventListener( 'touchstart', ct.commentReplyLinkClickEvent );
//				ctReplyLinks[i].addEventListener( 'click',      ct.commentReplyLinkClickEvent );
			}

			if ( ctCancelLink ) {
				ctCancelLink.removeEventListener( 'touchstart', window.addComment.cancelEvent );
				ctCancelLink.removeEventListener( 'click',      window.addComment.cancelEvent );
//				ctCancelLink.addEventListener( 'touchstart', ct.cancelCommentReplyLinkClickEvent );
//				ctCancelLink.addEventListener( 'click',      ct.cancelCommentReplyLinkClickEvent );
			}
        }
 */

        // clone all reply link elements to drop their event listeners
		for ( i = 0; i < ctReplyLinks.length; i++ ) {
console.log( 'typeof: ' + typeof( ctReplyLinks[i] ) );
			ct.recreateNode( ctReplyLinks[i] );
		}
		if ( ctCancelLink ) {
console.log( 'typeof cancelLink: ' + typeof( ctCancelLink ) );
			//ct.recreateNode( ctCancelLink[i] );
			ct.recreateNode( document.getElementById( 'cancel-comment-reply-link' ) );
		}

        if ( true ) {
			$( '.comment-reply-link' ).click( function( e ) {
				e.preventDefault();

				ct.removeEditor( 'comment' );

				var args = $( this ).data( 'comment_tweaks-onclick' );
                var respondId = '';
				if ( typeof args !== 'undefined' ) {
					// This is comments_reply_link in WP < 5.1.1 or post_comments_link
console.log( 'Old Wordpress found' );
					args = args.replace( /.*\(|\)/gi, '' ).replace( /\"|\s+/g, '' );
					args = args.split( ',' );
					ct.respondId = args[2];
					addComment.moveForm.apply( addComment, args );
				} else {
console.log( 'New Wordpress found - good luck!' );
					// wp 5.1.1 no longer adds onclick attribute to comment_reply_link
					var args = [ $( this ).data( 'belowelement' ) ];
					args.push( $( this ).data( 'commentid' ) );
					args.push( $( this ).data( 'respondelement' ) );
					args.push( $( this ).data( 'postid' ) );
					ct.respondId = args[2];
					//addComment.moveForm.apply( addComment, args );
					window.addComment.moveForm.apply( window.addComment, args );

				}

				ct.initializeEditor( 'comment' );
				if ( typeof tinymce.activeEditor.focus === 'function' ) {
					tinymce.activeEditor.focus();
                }

				// save original onclick handler added by addComment.moveForm()
				var cancelLink = document.getElementById( 'cancel-comment-reply-link' );
/*
				if ( typeof cancelLink.saveOnClick !== 'function' ) {
					cancelLink.saveOnClick = cancelLink.onclick;
				}
 */

console.log( 'creating new cancelLink onclick handler' );
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
console.log( 'custom cancelLink click handler running here, respondId ' + ct.respondId );

					ct.removeEditor( 'comment' );

					// This is basically the cancel link onclick handler from WP 5.0.4.
					//
					// addComment.cancelEvent() in WP 5.1.1 and newer implement the same logic,
					// but we don't have way to reference that function inside the addComment enclosure,
					// so we will overwrite it and just call this for now.
					var t       = window.addComment,
					    temp    = document.getElementById( 'wp-temp-form-div' ),
					    respond = document.getElementById( ct.respondId );

					if ( temp && respond ) {
						document.getElementById( 'comment_parent' ).value = '0';
						temp.parentNode.replaceChild( respondElement ,temporaryElement );
						this.style.display = 'none';
						this.onclick = null;
					}

/*
					if ( typeof cancelLink.saveOnClick === 'function' ) {
						cancelLink.saveOnClick();
					}
 */

					ct.initializeEditor( 'comment' );
				};
			});
        }
        });
	});

})( jQuery );

