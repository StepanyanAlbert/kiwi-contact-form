( function( $ ) {

	'use strict';

	if ( typeof kiwi === 'undefined' || kiwi === null ) {
		return;
	}

	$( function() {
		var welcomePanel = $( '#welcome-panel' );
		var updateWelcomePanel;

		updateWelcomePanel = function( visible ) {
			$.post( ajaxurl, {
				action: 'kiwi-update-welcome-panel',
				visible: visible,
				welcomepanelnonce: $( '#welcomepanelnonce' ).val()
			} );
		};

		$( 'a.welcome-panel-close', welcomePanel ).click( function( event ) {
			event.preventDefault();
			welcomePanel.addClass( 'hidden' );
			updateWelcomePanel( 0 );
		} );

		$( '#contact-form-editor' ).tabs( {
			active: kiwi.activeTab,
			activate: function( event, ui ) {
				$( '#active-tab' ).val( ui.newTab.index() );
			}
		} );

		$( '#contact-form-editor-tabs' ).focusin( function( event ) {
			$( '#contact-form-editor .keyboard-interaction' ).css(
				'visibility', 'visible' );
		} ).focusout( function( event ) {
			$( '#contact-form-editor .keyboard-interaction' ).css(
				'visibility', 'hidden' );
		} );

		kiwi.toggleMail2( 'input:checkbox.toggle-form-table' );

		$( 'input:checkbox.toggle-form-table' ).click( function( event ) {
			kiwi.toggleMail2( this );
		} );

		if ( '' === $( '#title' ).val() ) {
			$( '#title' ).focus();
		}

		kiwi.titleHint();

		$( '.contact-form-editor-box-mail span.mailtag' ).click( function( event ) {
			var range = document.createRange();
			range.selectNodeContents( this );
			window.getSelection().addRange( range );
		} );

		kiwi.updateConfigErrors();

		$( '[data-config-field]' ).change( function() {
			var postId = $( '#post_ID' ).val();

			if ( ! postId || -1 == postId ) {
				return;
			}

			var data = [];

			$( this ).closest( 'form' ).find( '[data-config-field]' ).each( function() {
				data.push( {
					'name': $( this ).attr( 'name' ).replace( /^kiwi-/, '' ).replace( /-/g, '_' ),
					'value': $( this ).val()
				} );
			} );

			data.push( { 'name': 'context', 'value': 'dry-run' } );

			$.ajax( {
				method: 'POST',
				url: kiwi.apiSettings.getRoute( '/contact-forms/' + postId ),
				beforeSend: function( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', kiwi.apiSettings.nonce );
				},
				data: data
			} ).done( function( response ) {
				kiwi.configValidator.errors = response.config_errors;
				kiwi.updateConfigErrors();
			} );
		} );

		$( window ).on( 'beforeunload', function( event ) {
			var changed = false;

			$( '#kiwi-admin-form-element :input[type!="hidden"]' ).each( function() {
				if ( $( this ).is( ':checkbox, :radio' ) ) {
					if ( this.defaultChecked != $( this ).is( ':checked' ) ) {
						changed = true;
					}
				} else if ( $( this ).is( 'select' ) ) {
					$( this ).find( 'option' ).each( function() {
						if ( this.defaultSelected != $( this ).is( ':selected' ) ) {
							changed = true;
						}
					} );
				} else {
					if ( this.defaultValue != $( this ).val() ) {
						changed = true;
					}
				}
			} );

			if ( changed ) {
				event.returnValue = kiwi.saveAlert;
				return kiwi.saveAlert;
			}
		} );

		$( '#kiwi-admin-form-element' ).submit( function() {
			if ( 'copy' != this.action.value ) {
				$( window ).off( 'beforeunload' );
			}

			if ( 'save' == this.action.value ) {
				$( '#publishing-action .spinner' ).addClass( 'is-active' );
			}
		} );
	} );

	kiwi.toggleMail2 = function( checkbox ) {
		var $checkbox = $( checkbox );
		var $fieldset = $( 'fieldset',
			$checkbox.closest( '.contact-form-editor-box-mail' ) );

		if ( $checkbox.is( ':checked' ) ) {
			$fieldset.removeClass( 'hidden' );
		} else {
			$fieldset.addClass( 'hidden' );
		}
	};

	kiwi.updateConfigErrors = function() {
		var errors = kiwi.configValidator.errors;
		var errorCount = { total: 0 };

		$( '[data-config-field]' ).each( function() {
			$( this ).removeAttr( 'aria-invalid' );
			$( this ).next( 'ul.config-error' ).remove();

			var section = $( this ).attr( 'data-config-field' );

			if ( errors[ section ] ) {
				var $list = $( '<ul></ul>' ).attr( {
					'role': 'alert',
					'class': 'config-error'
				} );

				$.each( errors[ section ], function( i, val ) {
					var $li = $( '<li></li>' ).append(
						kiwi.iconInCircle( '!' )
					).append(
						$( '<span class="screen-reader-text"></span>' ).text( kiwi.configValidator.iconAlt )
					).append( ' ' );

					if ( val.link ) {
						$li.append(
							$( '<a></a>' ).attr( 'href', val.link ).text( val.message )
						);
					} else {
						$li.text( val.message );
					}

					$li.appendTo( $list );

					var tab = section
						.replace( /^mail_\d+\./, 'mail.' ).replace( /\..*$/, '' );

					if ( ! errorCount[ tab ] ) {
						errorCount[ tab ] = 0;
					}

					errorCount[ tab ] += 1;

					errorCount.total += 1;
				} );

				$( this ).after( $list ).attr( { 'aria-invalid': 'true' } );
			}
		} );

		$( '#contact-form-editor-tabs > li' ).each( function() {
			var $item = $( this );
			$item.find( '.icon-in-circle' ).remove();
			var tab = $item.attr( 'id' ).replace( /-panel-tab$/, '' );

			$.each( errors, function( key, val ) {
				key = key.replace( /^mail_\d+\./, 'mail.' );

				if ( key.replace( /\..*$/, '' ) == tab.replace( '-', '_' ) ) {
					var $mark = kiwi.iconInCircle( '!' );
					$item.find( 'a.ui-tabs-anchor' ).first().append( $mark );
					return false;
				}
			} );

			var $tabPanelError = $( '#' + tab + '-panel > div.config-error:first' );
			$tabPanelError.empty();

			if ( errorCount[ tab.replace( '-', '_' ) ] ) {
				$tabPanelError.append( kiwi.iconInCircle( '!' ) );

				if ( 1 < errorCount[ tab.replace( '-', '_' ) ] ) {
					var manyErrorsInTab = kiwi.configValidator.manyErrorsInTab
						.replace( '%d', errorCount[ tab.replace( '-', '_' ) ] );
					$tabPanelError.append( manyErrorsInTab );
				} else {
					$tabPanelError.append( kiwi.configValidator.oneErrorInTab );
				}
			}
		} );

		$( '#misc-publishing-actions .misc-pub-section.config-error' ).remove();

		if ( errorCount.total ) {
			var $warning = $( '<div></div>' )
				.addClass( 'misc-pub-section config-error' )
				.append( kiwi.iconInCircle( '!' ) );

			if ( 1 < errorCount.total ) {
				$warning.append(
					kiwi.configValidator.manyErrors.replace( '%d', errorCount.total )
				);
			} else {
				$warning.append( kiwi.configValidator.oneError );
			}

			$warning.append( '<br />' ).append(
				$( '<a></a>' )
					.attr( 'href', kiwi.configValidator.docUrl )
					.text( kiwi.configValidator.howToCorrect )
			);

			$( '#misc-publishing-actions' ).append( $warning );
		}
	};

	/**
	 * Copied from wptitlehint() in wp-admin/js/post.js
	 */
	kiwi.titleHint = function() {
		var $title = $( '#title' );
		var $titleprompt = $( '#title-prompt-text' );

		if ( '' === $title.val() ) {
			$titleprompt.removeClass( 'screen-reader-text' );
		}

		$titleprompt.click( function() {
			$( this ).addClass( 'screen-reader-text' );
			$title.focus();
		} );

		$title.blur( function() {
			if ( '' === $(this).val() ) {
				$titleprompt.removeClass( 'screen-reader-text' );
			}
		} ).focus( function() {
			$titleprompt.addClass( 'screen-reader-text' );
		} ).keydown( function( e ) {
			$titleprompt.addClass( 'screen-reader-text' );
			$( this ).unbind( e );
		} );
	};

	kiwi.iconInCircle = function( icon ) {
		var $span = $( '<span class="icon-in-circle" aria-hidden="true"></span>' );
		return $span.text( icon );
	};

	kiwi.apiSettings.getRoute = function( path ) {
		var url = kiwi.apiSettings.root;

		url = url.replace(
			kiwi.apiSettings.namespace,
			kiwi.apiSettings.namespace + path );

		return url;
	};

	// $( ".box" ).draggable({
	// 	scope: 'demoBox',
	// 	revertDuration: 100,
	// 	start: function( event, ui ) {
	// 		//Reset
	// 		$( ".box" ).draggable( "option", "revert", true );
	// 		$('.result').html('-');
	// 	}
	// });

	//todo
	$( "#selectable-input-group" ).sortable({
		update: function( event, ui ) {
			kiwi.changeForm()
		}
	});

	$( "#selectable-input-group-default" ).sortable({
		update: function( event, ui ) {
			kiwi.changeForm()
		}
	});

    $( "#testdiv" ).resizable({
        grid: [ 20, 10 ]
    });

	// $( ".drag-area" ).droppable({
	// 	scope: 'demoBox',
	// 	drop: function( event, ui ) {
	// 		$( ".box" ).draggable( "option", "revert", false );
	// 		$(ui.draggable).detach().css({top: 0,left: 0}).appendTo(this);
    //
	// 		kiwi.changeForm()
	// 	}
    //
	// });


	kiwi.changeForm = function() {
		let html = '';
		if ( $('#selectable-input-group li .box').length ) {

			$('#selectable-input-group li .box').each(function () {

				let shortcode = $(this).find('.form-control').attr('shortcode'),
					value = $(this).find('.form-control').val();

				// For Checkboxes and selects
				if ($(this).find('.form-control').length > 1) {
					shortcode = $(this).find('.form-control').first().attr('shortcode');
					value = $(this).find('.form-control').first().val();
				}

				if (value === '') {
					shortcode = shortcode.replace(/ ".*"/, '');
				} else if (/ ".*"/.test(shortcode)) {
					shortcode = shortcode.replace(/ ".*"/, ` "${value}"`);
				} else {
					shortcode = shortcode.substring(0, shortcode.length - 1) + ` "${value}"` + ']';
				}

				html += shortcode;
			});
		} else {
			$('#selectable-input-group-default li .box').each(function () {
				let shortcode = $(this).find('.form-control').attr('shortcode'),
					value = $(this).find('.form-control').val();

				// if (name === 'your-name') {
				// 	html += ' <label> ' + value + ' [text* your-name] ' + '</label>'
				// } else if (name === 'your-email') {
				// 	html += ' <label> ' + value + ' [email* your-email] ' + '</label>'
				// } else if (name === 'your-subject') {
				// 	html += ' <label> ' + value + ' [text your-subject] ' + '</label>'
				// } else if ($(this).find('textarea').attr('shortcode') === 'your-message') {
				// 	html += ' <label> ' + $(this).find('textarea').val() + ' [textarea* your-message] ' + '</label>'
				// }

				if (value === '') {
					shortcode = shortcode.replace(/ ".*"/, '');
				} else if (/ ".*"/.test(shortcode)) {
					shortcode = shortcode.replace(/ ".*"/, ` "${value}"`);
				} else {
					shortcode = shortcode.substring(0, shortcode.length - 1) + ` "${value}"` + ']';
				}

				html += shortcode;

			});
		}

		html += ' [submit "Send"]';
		$('#kiwi-form').text(html)
	};

	// kiwi.changeForm()

	kiwi.insertInTextarea = function() {
		kiwi.changeForm()
	};

	// EDIT
	$(document).on('click', '.field-edit-btn',  function() {

		const edit_block = $(this).parent().parent();
		const shortcode = edit_block.find('.form-control').attr('shortcode');
		const matchedLabel = shortcode.match(/label:"([^"]+)"/);
		const label = matchedLabel ? matchedLabel[1] : '';

		$("#delete-field-modal").attr('data-field', shortcode );
		$('.edit-modal-label-input').val(label)
		$("#edit-field-modal").modal();

	});

	// On click edit modal save button.
	$(document).on('click', '.field-edit-modal-save-btn',  function() {
		let kiwi_form_html   = $('#kiwi-form').text();
		const edit_shortcode = $("#delete-field-modal").attr('data-field');
		const new_label      = $('.edit-modal-label-input').val();
		let new_shortcode;

		if (new_label) {
			if (edit_shortcode.match(/label:"([^"]+)"/)) {
				new_shortcode = edit_shortcode
					.replace(/label:"[^"]+"/, `label:"${new_label}"`);
			} else {
				new_shortcode = edit_shortcode.substring(0, edit_shortcode.length - 1) +
					`label:"${new_label}"` + ']';
			}
		} else {
			new_shortcode = edit_shortcode
				.replace(/label:"[^"]+"/, '');
		}

		// update shortcode attr in selected field
		$(".form-control[shortcode = '" + edit_shortcode + "' ]")
			.attr('shortcode', new_shortcode);

		const x = $(".form-control[shortcode = '" + edit_shortcode + "' ]")
			.parent()



		console.log(x);


		const insert_index = kiwi_form_html.indexOf(edit_shortcode);
		kiwi_form_html =
			kiwi_form_html.substring(0, insert_index) +
			  new_shortcode +
			kiwi_form_html.substring(insert_index + edit_shortcode.length, kiwi_form_html.length);

		$('#kiwi-form').text(kiwi_form_html);
		$("#edit-field-modal").modal("hide")
	});


	// DELETE
	$(document).on('click', '.field-delete-btn',  function() {
			const delete_block = $(this).parent().parent();
			const shortcode = delete_block.find('.form-control').attr('shortcode');
			$("#delete-field-modal").attr('data-field', shortcode );
			$("#delete-field-modal").modal();
	});

	// On click delete modal save button.
	$(document).on('click', '.field-delete-modal-btn',  function() {
		const kiwi_form_html = $('#kiwi-form').text();
		const delete_filed_shortcode = $("#delete-field-modal").attr('data-field');
		$(`[shortcode='${delete_filed_shortcode}']`).parent().parent().remove();
		$('#kiwi-form').text(kiwi_form_html.replace(delete_filed_shortcode, ''));
		$("#delete-field-modal").modal("hide")
		kiwi.changeForm()
	});

	function generate_copy_filed_label(shortcode, label_from_html) {
		const all_shortcodes = $('#kiwi-form').text();
		const label_from_shortcode = shortcode.match(/label:"([^"]+)"/);
		const field = shortcode.match(/^([[\w\*?\-]+)/)[1];
		let label = label_from_html;

		if (null !== label_from_shortcode) {
			label = label_from_shortcode[1];
		}

		const label_name = label.match(/(^.*?) \(/);
		 if (label_name) {
		 	label = label_name[1];
		 }

		const reg =  new RegExp('\\' + field, 'g');
        const count = (all_shortcodes.match(reg) || []).length;
		if ( count === 0 ) {
            return label;
        }

		if ( count === 1 ) {
			return `${label} (1)`;
		}
		return `${label} (${count})`;

	}

	$(document).on('click', '.field-copy-btn',  function() {
		let _parent = $(this).parent().parent().parent();
		let copy_block = _parent.clone();
		let new_shortcode = $(this).parent().parent().find('.form-control').attr('shortcode');

		// const id_copy = 'copy:' + (new Date()).getMilliseconds() + Math.floor(Math.random() * 1000);
		// let new_shortcode = old_shortcode.substring(0, old_shortcode.length - 1) + ' ' + id_copy + ']';

		let label = copy_block.find('label').text();
        const new_label = generate_copy_filed_label(new_shortcode, label);
        if (new_shortcode.match(/label:"[^"]+"/)) {
			new_shortcode = new_shortcode.replace(/label:"[^"]+"/, `label:"${new_label}"`);
        } else {
            new_shortcode = new_shortcode.substring(0, new_shortcode.length - 1) + `label:"${new_label}"` + ']';
        }

        copy_block.find('label').text(new_label);

		copy_block.find('.form-control').attr('shortcode', new_shortcode );
		_parent.after(copy_block);

		let kiwi_form_html = $('#kiwi-form').text();
		const insert_index = kiwi_form_html.indexOf(name) + name.length;
		kiwi_form_html = kiwi_form_html.substring(0, insert_index) + new_shortcode + kiwi_form_html.substring(insert_index);
		$('#kiwi-form').text(kiwi_form_html)

	});


} )( jQuery );
