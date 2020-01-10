<?php
/**
** A base module for [quiz]
**/

/* form_tag handler */

add_action( 'kiwi_cf_init', 'kiwi_cf_add_form_tag_quiz', 10, 0 );

function kiwi_cf_add_form_tag_quiz() {
	kiwi_cf_add_form_tag( 'quiz',
		'kiwi_cf_quiz_form_tag_handler',
		array(
			'name-attr' => true,
			'do-not-store' => true,
			'not-for-mail' => true,
		)
	);
}

function kiwi_cf_quiz_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = kiwi_cf_get_validation_error( $tag->name );

	$class = kiwi_cf_form_controls_class( $tag->type );

	if ( $validation_error ) {
		$class .= ' kiwi-not-valid';
	}

	$atts = array();

	$atts['size'] = $tag->get_size_option( '40' );
	$atts['maxlength'] = $tag->get_maxlength_option();
	$atts['minlength'] = $tag->get_minlength_option();

	if ( $atts['maxlength'] and $atts['minlength']
	and $atts['maxlength'] < $atts['minlength'] ) {
		unset( $atts['maxlength'], $atts['minlength'] );
	}

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );
	$atts['autocomplete'] = 'off';
	$atts['aria-required'] = 'true';
	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

	$pipes = $tag->pipes;

	if ( $pipes instanceof Kiwi_CF_Pipes
	and ! $pipes->zero() ) {
		$pipe = $pipes->random_pipe();
		$question = $pipe->before;
		$answer = $pipe->after;
	} else {
		// default quiz
		$question = '1+1=?';
		$answer = '2';
	}

	$answer = kiwi_cf_canonicalize( $answer );

	$atts['type'] = 'text';
	$atts['name'] = $tag->name;

	$atts = kiwi_cf_format_atts( $atts );

	$html = sprintf(
		'<span class="kiwi-form-control-wrap %1$s"><label><span class="kiwi-quiz-label">%2$s</span> <input %3$s /></label><input type="hidden" name="_quiz_answer_%4$s" value="%5$s" />%6$s</span>',
		sanitize_html_class( $tag->name ),
		esc_html( $question ), $atts, $tag->name,
		wp_hash( $answer, 'kiwi_cf_quiz' ), $validation_error );

	return $html;
}


/* Validation filter */

add_filter( 'kiwi_cf_validate_quiz', 'kiwi_cf_quiz_validation_filter', 10, 2 );

function kiwi_cf_quiz_validation_filter( $result, $tag ) {
	$name = $tag->name;

	$answer = isset( $_POST[$name] ) ? kiwi_cf_canonicalize( $_POST[$name] ) : '';
	$answer = wp_unslash( $answer );

	$answer_hash = wp_hash( $answer, 'kiwi_cf_quiz' );

	$expected_hash = isset( $_POST['_kiwi_cf_quiz_answer_' . $name] )
		? sanitize_text_field( (string) $_POST['_kiwi_cf_quiz_answer_' . $name] )
		: '';

	if ( $answer_hash != $expected_hash ) {
		$result->invalidate( $tag, kiwi_cf_get_message( 'quiz_answer_not_correct' ) );
	}

	return $result;
}


/* Ajax echo filter */

add_filter( 'kiwi_cf_ajax_onload', 'kiwi_cf_quiz_ajax_refill', 10, 1 );
add_filter( 'kiwi_cf_ajax_json_echo', 'kiwi_cf_quiz_ajax_refill', 10, 1 );

function kiwi_cf_quiz_ajax_refill( $items ) {
	if ( ! is_array( $items ) ) {
		return $items;
	}

	$fes = kiwi_cf_scan_form_tags( array( 'type' => 'quiz' ) );

	if ( empty( $fes ) ) {
		return $items;
	}

	$refill = array();

	foreach ( $fes as $fe ) {
		$name = $fe['name'];
		$pipes = $fe['pipes'];

		if ( empty( $name ) ) {
			continue;
		}

		if ( $pipes instanceof Kiwi_CF_Pipes
		and ! $pipes->zero() ) {
			$pipe = $pipes->random_pipe();
			$question = $pipe->before;
			$answer = $pipe->after;
		} else {
			// default quiz
			$question = '1+1=?';
			$answer = '2';
		}

		$answer = kiwi_cf_canonicalize( $answer );

		$refill[$name] = array( $question, wp_hash( $answer, 'kiwi_cf_quiz' ) );
	}

	if ( ! empty( $refill ) ) {
		$items['quiz'] = $refill;
	}

	return $items;
}


/* Messages */

add_filter( 'kiwi_cf_messages', 'kiwi_cf_quiz_messages', 10, 1 );

function kiwi_cf_quiz_messages( $messages ) {
	$messages = array_merge( $messages, array(
		'quiz_answer_not_correct' => array(
			'description' =>
				__( "Sender doesn't enter the correct answer to the quiz", 'kiwi-contact-form' ),
			'default' =>
				__( "The answer to the quiz is incorrect.", 'kiwi-contact-form' ),
		),
	) );

	return $messages;
}


/* Tag generator */

add_action( 'kiwi_cf_admin_init', 'kiwi_cf_add_tag_generator_quiz', 40, 0 );

function kiwi_cf_add_tag_generator_quiz() {
	$tag_generator = KiwiCfTagGenerator::get_instance();
	$tag_generator->add( 'quiz', __( 'quiz', 'kiwi-contact-form' ),
		'kiwi_cf_tag_generator_quiz' );
}

function kiwi_cf_tag_generator_quiz( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$type = 'quiz';

	$description = __( "Generate a form-tag for a question-answer pair. For more details, see %s.", 'kiwi-contact-form' );

	$desc_link = kiwi_cf_link( __( ' ', 'kiwi-contact-form' ), __( 'Quiz', 'kiwi-contact-form' ) );

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
	<th scope="row"><?php echo esc_html( __( 'Questions and answers', 'kiwi-contact-form' ) ); ?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html( __( 'Questions and answers', 'kiwi-contact-form' ) ); ?></legend>
		<textarea name="values" class="values" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>"></textarea><br />
		<label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><span class="description"><?php echo esc_html( __( "One pipe-separated question-answer pair (e.g. The capital of Brazil?|Rio) per line.", 'kiwi-contact-form' ) ); ?></span></label>
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
