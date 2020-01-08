<?php
/**
** A base module for [acceptance]
**/

/* form_tag handler */

add_action( 'kiwi_cf_init', 'kiwi_cf_add_form_tag_acceptance', 10, 0 );

function kiwi_cf_add_form_tag_acceptance() {
	kiwi_cf_add_form_tag( 'acceptance',
		'kiwi_cf_acceptance_form_tag_handler',
		array(
			'name-attr' => true,
		)
	);
}

function kiwi_cf_acceptance_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = kiwi_cf_get_validation_error( $tag->name );

	$class = kiwi_cf_form_controls_class( $tag->type );

	if ( $validation_error ) {
		$class .= ' kiwi-not-valid';
	}

	if ( $tag->has_option( 'invert' ) ) {
		$class .= ' invert';
	}

	if ( $tag->has_option( 'optional' ) ) {
		$class .= ' optional';
	}

	$atts = array(
		'class' => trim( $class ),
	);

	$item_atts = array();

	$item_atts['type'] = 'checkbox';
	$item_atts['name'] = $tag->name;
	$item_atts['value'] = '1';
	$item_atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );
	$item_atts['aria-invalid'] = $validation_error ? 'true' : 'false';

	if ( $tag->has_option( 'default:on' ) ) {
		$item_atts['checked'] = 'checked';
	}

	$item_atts['class'] = $tag->get_class_option();
	$item_atts['id'] = $tag->get_id_option();

	$item_atts = kiwi_cf_format_atts( $item_atts );

	$content = empty( $tag->content )
		? (string) reset( $tag->values )
		: $tag->content;

	$content = trim( $content );

	if ( $content ) {
		if ( $tag->has_option( 'label_first' ) ) {
			$html = sprintf(
				'<span class="kiwi-list-item-label">%2$s</span><input %1$s />',
				$item_atts, $content );
		} else {
			$html = sprintf(
				'<input %1$s /><span class="kiwi-list-item-label">%2$s</span>',
				$item_atts, $content );
		}

		$html = sprintf(
			'<span class="kiwi-list-item"><label>%s</label></span>',
			$html
		);

	} else {
		$html = sprintf(
			'<span class="kiwi-list-item"><input %1$s /></span>',
			$item_atts );
	}

	$atts = kiwi_cf_format_atts( $atts );

	$html = sprintf(
		'<span class="kiwi-form-control-wrap %1$s"><span %2$s>%3$s</span>%4$s</span>',
		sanitize_html_class( $tag->name ), $atts, $html, $validation_error );

	return $html;
}


/* Validation filter */

add_filter( 'kiwi_cf_validate_acceptance',
	'kiwi_cf_acceptance_validation_filter', 10, 2 );

function kiwi_cf_acceptance_validation_filter( $result, $tag ) {
	if ( ! kiwi_cf_acceptance_as_validation() ) {
		return $result;
	}

	if ( $tag->has_option( 'optional' ) ) {
		return $result;
	}

	$name = $tag->name;
	$value = ( ! empty( $_POST[$name] ) ? 1 : 0 );

	$invert = $tag->has_option( 'invert' );

	if ( $invert and $value
	or ! $invert and ! $value ) {
		$result->invalidate( $tag, kiwi_cf_get_message( 'accept_terms' ) );
	}

	return $result;
}


/* Acceptance filter */

add_filter( 'kiwi_cf_acceptance', 'kiwi_cf_acceptance_filter', 10, 2 );

function kiwi_cf_acceptance_filter( $accepted, $submission ) {
	$tags = kiwi_cf_scan_form_tags( array( 'type' => 'acceptance' ) );

	foreach ( $tags as $tag ) {
		$name = $tag->name;

		if ( empty( $name ) ) {
			continue;
		}

		$value = ( ! empty( $_POST[$name] ) ? 1 : 0 );

		$content = empty( $tag->content )
			? (string) reset( $tag->values )
			: $tag->content;

		$content = trim( $content );

		if ( $value and $content ) {
			$submission->add_consent( $name, $content );
		}

		if ( $tag->has_option( 'optional' ) ) {
			continue;
		}

		$invert = $tag->has_option( 'invert' );

		if ( $invert and $value
		or ! $invert and ! $value ) {
			$accepted = false;
		}
	}

	return $accepted;
}

add_filter( 'kiwi_cf_form_class_attr',
	'kiwi_cf_acceptance_form_class_attr', 10, 1 );

function kiwi_cf_acceptance_form_class_attr( $class ) {
	if ( kiwi_cf_acceptance_as_validation() ) {
		return $class . ' kiwi-acceptance-as-validation';
	}

	return $class;
}

function kiwi_cf_acceptance_as_validation() {
	if ( ! $contact_form = kiwi_cf_get_current_contact_form() ) {
		return false;
	}

	return $contact_form->is_true( 'acceptance_as_validation' );
}

add_filter( 'kiwi_cf_mail_tag_replaced_acceptance',
	'kiwi_cf_acceptance_mail_tag', 10, 4 );

function kiwi_cf_acceptance_mail_tag( $replaced, $submitted, $html, $mail_tag ) {
	$form_tag = $mail_tag->corresponding_form_tag();

	if ( ! $form_tag ) {
		return $replaced;
	}

	if ( ! empty( $submitted ) ) {
		$replaced = __( 'Consented', 'kiwi-contact-form' );
	} else {
		$replaced = __( 'Not consented', 'kiwi-contact-form' );
	}

	$content = empty( $form_tag->content )
		? (string) reset( $form_tag->values )
		: $form_tag->content;

	if ( ! $html ) {
		$content = wp_strip_all_tags( $content );
	}

	$content = trim( $content );

	if ( $content ) {
		$replaced = sprintf(
			/* translators: 1: 'Consented' or 'Not consented', 2: conditions */
			_x( '%1$s: %2$s', 'mail output for acceptance checkboxes',
				'kiwi-contact-form' ),
			$replaced,
			$content
		);
	}

	return $replaced;
}


/* Tag generator */

add_action( 'kiwi_cf_admin_init', 'kiwi_cf_add_tag_generator_acceptance', 35, 0 );

function kiwi_cf_add_tag_generator_acceptance() {
	$tag_generator = KiwiCfTagGenerator::get_instance();
	$tag_generator->add( 'acceptance', __( 'acceptance', 'kiwi-contact-form' ),
		'kiwi_cf_tag_generator_acceptance' );
}

function kiwi_cf_tag_generator_acceptance( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$type = 'acceptance';

	$description = __( "Generate a form-tag for an acceptance checkbox. For more details, see %s.", 'kiwi-contact-form' );

	$desc_link = kiwi_cf_link( __( ' ', 'kiwi-contact-form' ), __( 'Acceptance Checkbox', 'kiwi-contact-form' ) );

?>
<div class="control-box">
<fieldset>
<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

<table class="form-table">
<tbody>
	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'kiwi-contact-form' ) ); ?></label></th>
	<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-content' ); ?>"><?php echo esc_html( __( 'Condition', 'kiwi-contact-form' ) ); ?></label></th>
	<td><input type="text" name="content" class="oneline large-text" id="<?php echo esc_attr( $args['content'] . '-content' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><?php echo esc_html( __( 'Options', 'kiwi-contact-form' ) ); ?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html( __( 'Options', 'kiwi-contact-form' ) ); ?></legend>
		<label><input type="checkbox" name="optional" class="option" checked="checked" /> <?php echo esc_html( __( 'Make this checkbox optional', 'kiwi-contact-form' ) ); ?></label>
		</fieldset>
	</td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'kiwi-contact-form' ) ); ?></label></th>
	<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'kiwi-contact-form' ) ); ?></label></th>
	<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
	</tr>

</tbody>
</table>
</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'kiwi-contact-form' ) ); ?>" />
	</div>
</div>
<?php
}
