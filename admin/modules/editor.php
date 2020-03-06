<?php

class Kiwi_CF_Editor {

    private $contact_form;
    private $panels = array();

    public function __construct( KiwiCfContactForm $contact_form ) {
        $this->contact_form = $contact_form;
    }

    public function add_panel( $id, $title, $callback ) {
        if ( kiwi_cf_is_name( $id ) ) {
            $this->panels[$id] = array(
                'title' => $title,
                'callback' => $callback,
            );
        }
    }

    public function display() {
        if ( empty( $this->panels ) ) {
            return;
        }

        echo '<ul id="contact-form-editor-tabs" class="nav nav-tabs">';

        foreach ( $this->panels as $id => $panel ) {
            echo sprintf( '<li id="%1$s-tab" class="nav-item"><a href="#%1$s" class="nav-link" data-toggle="tab">%2$s</a></li>',
                esc_attr( $id ), esc_html( $panel['title'] ) );
        }

        echo '</ul>';

        echo '<div class="tab-content">';
        foreach ( $this->panels as $id => $panel ) {
            echo sprintf( '<div class="tab-pane contact-form-editor-panel" id="%1$s">',
                esc_attr( $id ) );

            if ( is_callable( $panel['callback'] ) ) {
                $this->notice( $id, $panel );
                call_user_func( $panel['callback'], $this->contact_form );
            }

            echo '</div>';
        }
        echo '</div>';

    }

    public function notice( $id, $panel ) {
        echo '<div class="config-error"></div>';
    }
}

function kiwi_cf_editor_panel_form( $post ) {
    $desc_link = kiwi_cf_link(
        __( '', ' kiwi-contact-form' ),
        __( 'Editing Form Template', ' kiwi-contact-form' ) );
    $description = __( "You can edit the form template here. For details, see %s.", ' kiwi-contact-form' );
    $description = sprintf( esc_html( $description ), $desc_link );
    ?>

    <fieldset>
        <legend><?php echo $description; ?></legend>
        <?php
        $tag_generator = KiwiCfTagGenerator::get_instance();
        $tag_generator->print_buttons();

        preg_match_all ( "/\[[^\]]*\]/", $post->prop( 'form' ), $parsed_textarea );
        //var_dump($post->prop('form'), '<pre />', $parsed_textarea);
        if (empty($parsed_textarea[0])) {
            ?>
            <div id="drag-and-drop-form" class="drag-area">
                <form>
                    <ul id="selectable-input-group-default">
                        <li>
                            <div class="form-group box">
                                <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                    <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                </div>
                                <label for="inputName">Your Name</label>
                                <input type="text"
                                       class="form-control"
                                       id="inputName"
                                       onkeyup="kiwi.insertInTextarea()"
                                       shortcode='[text* your-name label:"Your Name (required)" ]'
                                       name="your-name"
                                       value="Your Name (required)">
                            </div>
                        </li>
                        <li>
                            <div class="form-group box">
                                <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                    <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                </div>
                                <label for="inputName">Your Email</label>
                                <input type="text"
                                       class="form-control"
                                       id="inputName"
                                       onkeyup="kiwi.insertInTextarea()"
                                       name="your-email"
                                       shortcode='[text* your-email label:"Your Email (required)" ]'
                                       value="Your Email (required)">
                            </div>
                        </li>
                        <li>
                            <div class="form-group box">
                                <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                    <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                </div>
                                <label for="inputName">Subject</label>
                                <input type="text"
                                       class="form-control"
                                       id="inputName"
                                       onkeyup="kiwi.insertInTextarea()"
                                       name="your-subject"
                                       shortcode='[text your-subject label:"Subject" ]'
                                       value="Subject">
                            </div>
                        </li>
                        <li>
                            <div class="form-group box">
                                <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                    <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                </div>
                                <label for="exampleFormControlMessage">Message</label>
                                <textarea class="form-control"
                                          readonly
                                          id="exampleFormControlMessage"
                                          onkeyup="kiwi.insertInTextarea()"
                                          name="your-message"
                                          shortcode='[textarea* your-message label:"Message" ]'
                                          rows="3">Message</textarea>
                            </div>
                        </li>
                    </ul>
                </form>
            </div>
            <textarea id="kiwi-form"
                      name="kiwi-form"
                      cols="100" rows="3"
                      class="large-text code"
                      data-config-field="form.body">
                <?php echo esc_textarea( $post->prop( 'form' ) ); ?>
            </textarea>
            <?php
        } else {
            ?>
            <div id="drag-and-drop-form" class="drag-area">
                <form>
                    <ul id="selectable-input-group">
                    <?php
                    foreach ($parsed_textarea[0] as $parsed_item) {
                         $exported_item = substr($parsed_item, 1, -1);

                        // Input label
                        preg_match('/label:"([^"]+)"/', $exported_item, $input_label);
                        if ( !empty($input_label)) {
                            $label =  trim($input_label[1]);
                        } else {
                            $label = '';
                        }


                         // Input type (name)
                         preg_match('/^([\w\*?\-]+)/', $exported_item, $input_name);
                         if ( substr($input_name[0], -1) === "*" ) {
                             $input_name =  trim($input_name[0], "*");
                             $required = 'required';
                         } else {
                             $input_name = $input_name[0];
                             $required = '';
                         }

                         // Default values
                         preg_match_all('/ "(.*?)"/', $exported_item, $values);
                        if (!empty($values[1])) {
                            if (count($values[1]) === 1) {
                                    $value = trim($values[1][0]);
                            } else {
                                    $value = array_map(function($val){ return trim($val); }, $values[1]);
                            }
                        } else {
                            $value = '';
                        }


                         // If Placeholder exists
                         if ( preg_match('/placeholder/', $exported_item) ) {
                             $placeholder = "placeholder='$value'";
                             $value = '';
                         } else {
                             $placeholder = "";
                         }

                         // Id
                         preg_match('/id:([\w\-]+)/', $exported_item, $id);
                         $id =  !empty ( $id ) ? $id[1] : '';

                         // Classes
                         preg_match_all('/class:([\w\-]+)/', $exported_item, $classes);
                         $class =  !empty ( $classes ) ? join(" ", $classes[1]) : '';


                         // Min Max
                        preg_match('/min:([\d]+)/', $exported_item, $min);
                        $min =  !empty ( $min ) ? $min[1] : '';

                        preg_match('/max:([\d]+)/', $exported_item, $max);
                        $max =  !empty ( $max ) ? $max[1] : '';

                        if ( $input_name === 'textarea' ) {
                            $label = $label ? $label : '';
                            ?>
                            <li>
                                <div class="form-group box">
                                    <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                        <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    </div>
                                    <label for="exampleFormControlMessage"><?= $label ?></label>
                                    <textarea id="<?= $id ?>"
                                              class="<?= $class ?> form-control"
                                              onkeyup="kiwi.insertInTextarea()"
                                              shortcode='<?= $parsed_item ?>'
                                              rows="3"
                                              <?= $placeholder ?>
                                    ><?= strip_tags($value) ?></textarea>
                                </div>
                            </li>
                            <?php
                        } else if ($input_name === 'acceptance' ) {
                            $label = $label ? $label : '';
                            ?>
                            <li>
                                <div class="form-group box">
                                    <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                        <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    </div>
                                    <label for="inputName"><?= $label ?></label>
                                    <input type="checkbox"
                                           checked="<?= strip_tags($value) ?>"
                                           id="<?= $id ?> form-control"
                                           class="<?= $class ?>"
                                           shortcode='<?= $parsed_item ?>'
                                    >
                                    <span class="checkmark"></span>
                                </div>
                            </li>
                            <?php
                        } else if ( $input_name === 'date' ) {
                           $label = $label ? $label : '';
                            ?>
                            <li>
                                <div class="form-group box">
                                    <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                        <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    </div>
                                    <label for="inputName"><?= $label ?></label>
                                    <input type="date"
                                           class="<?= $class ?> form-control"
                                           shortcode='<?= $parsed_item ?>'
                                    >
                                </div>
                            </li>
                            <?php
                        } else if ( $input_name === 'range' ) {
                            $label = $label ? $label : '';
                            ?>
                            <li>
                                <div class="form-group box">
                                    <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                        <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    </div>
                                    <label for="inputName"><?= $label ?></label>
                                    <input type="range"
                                           min="<?= $min ?>"
                                           max="<?= $max ?>"
                                           class="<?= $class ?> form-control"
                                           shortcode='<?= $parsed_item ?>'
                                    >
                                </div>
                            </li>
                            <?php
                        }
                        else if ( $input_name === 'radio' ) {
                            $label = $label ? $label : '';
                            ?>
                            <li>
                                <div class="form-group box">
                                    <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                        <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    </div>
                                    <label for="inputRadio"><?= $label ?></label>
                                    <input type="radio"
                                           shortcode='<?= $parsed_item ?>'
                                           value="male"
                                           id="<?= $id ?> inputRadio"
                                           class="<?= $class ?> form-control"
                                    >
                                </div>
                            </li>
                            <?php
                        } else if ( $input_name === 'checkbox' ) {
                            $label = $label ? $label : '';
                            ?>
                            <li>
                                <div class="form-group box">
                                    <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                        <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    </div>
                                    <label for="inputCheckbox"><?= $label ?></label>
                                    <br>
                                    <?php if ($value && is_array($value)) {
                                        foreach ($value as $val ){
                                            ?>
                                            <br>
                                            <input type="checkbox"
                                                   id="<?= $id ?> "
                                                   class="<?= $class ?> form-control"
                                                   shortcode='<?= $parsed_item ?>'
                                            >
                                            <span class="checkmark"><?= $val ?></span>
                                        <?php } } else {?>
                                            <input type="checkbox"
                                                   id="<?= $id ?> "
                                                   class="<?= $class ?> form-control"
                                                   shortcode='<?= $parsed_item ?>'
                                            >
                                            <span class="checkmark"><?= $value ?></span>

                                        <?php }?>
                                </div>
                            </li>
                            <?php
                        } else if ( $input_name ==='select' ) {
                            $label = $label ? $label : '';
                            ?>
                            <li>
                                <div class="form-group box">
                                    <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                        <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    </div>
                                    <label for="inputName"><?= $label ?></label>
                                    <select
                                            shortcode='<?= $parsed_item ?>'
                                            class="<?= $class ?> form-control"
                                            id="<? $id ?>"
                                    >
                                        <option value="volvo">Volvo</option>
                                        <option value="saab">Saab</option>
                                        <option value="mercedes">Mercedes</option>
                                        <option value="audi">Audi</option>
                                    </select>
                                </div>
                            </li>
                            <?php
                        } else if ( $input_name === 'quiz' ) {
                            $label = $label ? $label : '';
                            ?>
                            <li>
                                <div class="form-group box">
                                    <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                        <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    </div>
                                    <label for="inputName"><?= $label ?></label>
                                    <input type="checkbox"
                                           checked="checked"
                                           id="<?= $id ?>"
                                           shortcode='<?= $parsed_item ?>'
                                           class="<?= $class ?> form-control">
                                    <span class="checkmark"></span>
                                </div>
                            </li>
                            <?php
                        } else if ( $input_name === 'file' ) {
                            $label = $label ? $label : '';
                            ?>
                            <li>
                                <div class="form-group box">
                                    <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                        <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    </div>
                                    <label for="inputName"><?= $label ?></label>
                                    <input type="file"
                                           class="<?= $class ?> form-control"
                                           id="<?= $id ?>"
                                           onkeyup="kiwi.insertInTextarea()"
                                           shortcode='<?= $parsed_item ?>'
                                           value="<?= strip_tags($value) ?>"
                                    >
                                </div>
                            </li>
                            <?php
                        } else if ( $input_name === 'number' ) {
                            $label = $label ? $label : '';
                            ?>
                            <li>
                                <div class="form-group box">
                                    <div class="field-actions">
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                        <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    </div>
                                    <label for="inputName"><?= $label ?></label>
                                    <input type="number"
                                           class="<?= $class ?> form-control"
                                           id="<?=  $id ?>"
                                           min="<?= $min ?>"
                                           max="<?= $max ?>"
                                           onkeyup="kiwi.insertInTextarea()"
                                           shortcode='<?= $parsed_item ?>'
                                           value="<?= strip_tags($value) ?>"
                                            <?= $placeholder ?>
                                </div>
                            </li>
                            <?php
                        } else if ( $input_name === '/' || $input_name === 'submit' ) {
                            continue;
                        } else {
                            $label = $label ? $label : '';
                            //preg_match('~(.*?)\[~', get_string_between($post->prop( 'form' ), '<label>',$parsed_item ), $res);
                            ?>
                            <li>
                                <div class="form-group box">
                                    <div class="field-actions">
                                        <span class="field-edit-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-edit"></i>
                                        </span>
                                        <span class="field-copy-btn" data-toggle="tooltip" data-placement="top" title="Duplicate the field" >
                                            <i class="fas fa-copy"></i>
                                        </span>
                                        <span class="field-delete-btn" data-toggle="tooltip" data-placement="top" title="Delete the field" >
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    </div>
                                    <label for="inputName"><?= $label ?></label>
                                    <input type="text"
                                           class="<?= $class ?> form-control"
                                           id="<?= $id ?>"
                                           onkeyup="kiwi.insertInTextarea()"
                                           value="<?= strip_tags($value)  ?>"
                                           shortcode='<?= $parsed_item ?>'
                                           <?= $placeholder ?>
                                    >
                                </div>
                            </li>
                            <?php
                            $res = [];
                        }
                    }
                    ?>
                    </ul>
                </form>
            </div>
            <textarea  id="kiwi-form"
                       name="kiwi-form"
                       cols="100"
                       rows="3"
                       class="large-text code"
                       data-config-field="form.body"
            ><?php echo esc_textarea( $post->prop( 'form' ) ); ?>
            </textarea>
        <?php } ?>
    </fieldset>
    <?php
    kiwi_cf_editor_delete_field_modal();
    kiwi_cf_editor_edit_field_modal();
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function kiwi_cf_editor_panel_mail( $post ) {
    kiwi_cf_editor_box_mail( $post );

    echo '<br class="clear" />';

    kiwi_cf_editor_box_mail( $post, array(
        'id' => 'kiwi-mail-2',
        'name' => 'mail_2',
        'title' => __( 'Mail (2)', ' kiwi-contact-form' ),
        'use' => __( 'Use Mail (2)', ' kiwi-contact-form' ),
    ) );
}

function kiwi_cf_editor_box_mail( $post, $args = '' ) {
    $args = wp_parse_args( $args, array(
        'id' => 'kiwi-mail',
        'name' => 'mail',
        'title' => __( 'Mail', ' kiwi-contact-form' ),
        'use' => null,
    ) );

    $id = esc_attr( $args['id'] );

    $mail = wp_parse_args( $post->prop( $args['name'] ), array(
        'active' => false,
        'recipient' => '',
        'sender' => '',
        'subject' => '',
        'body' => '',
        'additional_headers' => '',
        'attachments' => '',
        'use_html' => false,
        'exclude_blank' => false,
    ) );

    ?>
    <div class="contact-form-editor-box-mail" id="<?php echo $id; ?>">
        <?php
        if ( ! empty( $args['use'] ) ) :
            ?>
            <label for="<?php echo $id; ?>-active"><input type="checkbox" id="<?php echo $id; ?>-active" name="<?php echo $id; ?>[active]" class="toggle-form-table" value="1"<?php echo ( $mail['active'] ) ? ' checked="checked"' : ''; ?> /> <?php echo esc_html( $args['use'] ); ?></label>
            <p class="description"><?php echo esc_html( __( "Mail (2) is an additional mail template often used as an autoresponder.", ' kiwi-contact-form' ) ); ?></p>
        <?php
        endif;
        ?>

        <fieldset>
            <legend>
                <?php
                $desc_link = kiwi_cf_link(
                    __( '', ' kiwi-contact-form' ),
                    __( 'Setting Up Mail', ' kiwi-contact-form' ) );
                $description = __( "You can edit the mail template here. For details, see %s.", ' kiwi-contact-form' );
                $description = sprintf( esc_html( $description ), $desc_link );
                echo $description;
                echo '<br />';

                echo esc_html( __( "In the following fields, you can use these mail-tags:",
                    ' kiwi-contact-form' ) );
                echo '<br />';
                $post->suggest_mail_tags( $args['name'] );
                ?>
            </legend>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="<?php echo $id; ?>-recipient"><?php echo esc_html( __( 'To', ' kiwi-contact-form' ) ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="<?php echo $id; ?>-recipient" name="<?php echo $id; ?>[recipient]" class="large-text code" size="70" value="<?php echo esc_attr( $mail['recipient'] ); ?>" data-config-field="<?php echo sprintf( '%s.recipient', esc_attr( $args['name'] ) ); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php echo $id; ?>-sender"><?php echo esc_html( __( 'From', ' kiwi-contact-form' ) ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="<?php echo $id; ?>-sender" name="<?php echo $id; ?>[sender]" class="large-text code" size="70" value="<?php echo esc_attr( $mail['sender'] ); ?>" data-config-field="<?php echo sprintf( '%s.sender', esc_attr( $args['name'] ) ); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php echo $id; ?>-subject"><?php echo esc_html( __( 'Subject', ' kiwi-contact-form' ) ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="<?php echo $id; ?>-subject" name="<?php echo $id; ?>[subject]" class="large-text code" size="70" value="<?php echo esc_attr( $mail['subject'] ); ?>" data-config-field="<?php echo sprintf( '%s.subject', esc_attr( $args['name'] ) ); ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php echo $id; ?>-additional-headers"><?php echo esc_html( __( 'Additional Headers', ' kiwi-contact-form' ) ); ?></label>
                    </th>
                    <td>
                        <textarea id="<?php echo $id; ?>-additional-headers" name="<?php echo $id; ?>[additional_headers]" cols="100" rows="4" class="large-text code" data-config-field="<?php echo sprintf( '%s.additional_headers', esc_attr( $args['name'] ) ); ?>"><?php echo esc_textarea( $mail['additional_headers'] ); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php echo $id; ?>-body"><?php echo esc_html( __( 'Message Body', ' kiwi-contact-form' ) ); ?></label>
                    </th>
                    <td>
                        <textarea id="<?php echo $id; ?>-body" name="<?php echo $id; ?>[body]" cols="100" rows="18" class="large-text code" data-config-field="<?php echo sprintf( '%s.body', esc_attr( $args['name'] ) ); ?>"><?php echo esc_textarea( $mail['body'] ); ?></textarea>

                        <p><label for="<?php echo $id; ?>-exclude-blank"><input type="checkbox" id="<?php echo $id; ?>-exclude-blank" name="<?php echo $id; ?>[exclude_blank]" value="1"<?php echo ( ! empty( $mail['exclude_blank'] ) ) ? ' checked="checked"' : ''; ?> /> <?php echo esc_html( __( 'Exclude lines with blank mail-tags from output', ' kiwi-contact-form' ) ); ?></label></p>

                        <p><label for="<?php echo $id; ?>-use-html"><input type="checkbox" id="<?php echo $id; ?>-use-html" name="<?php echo $id; ?>[use_html]" value="1"<?php echo ( $mail['use_html'] ) ? ' checked="checked"' : ''; ?> /> <?php echo esc_html( __( 'Use HTML content type', ' kiwi-contact-form' ) ); ?></label></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="<?php echo $id; ?>-attachments"><?php echo esc_html( __( 'File Attachments', ' kiwi-contact-form' ) ); ?></label>
                    </th>
                    <td>
                        <textarea id="<?php echo $id; ?>-attachments" name="<?php echo $id; ?>[attachments]" cols="100" rows="4" class="large-text code" data-config-field="<?php echo sprintf( '%s.attachments', esc_attr( $args['name'] ) ); ?>"><?php echo esc_textarea( $mail['attachments'] ); ?></textarea>
                    </td>
                </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
    <?php
}

function kiwi_cf_editor_panel_messages( $post ) {
    $desc_link = kiwi_cf_link(
        __( '', ' kiwi-contact-form' ),
        __( 'Editing Messages', ' kiwi-contact-form' ) );
    $description = __( "You can edit messages used in various situations here. For details, see %s.", ' kiwi-contact-form' );
    $description = sprintf( esc_html( $description ), $desc_link );

    $messages = kiwi_cf_messages();

    if ( isset( $messages['captcha_not_match'] )
        and ! kiwi_cf_use_really_simple_captcha() ) {
        unset( $messages['captcha_not_match'] );
    }

    ?>
    <fieldset>
        <legend><?php echo $description; ?></legend>
        <?php

        foreach ( $messages as $key => $arr ) {
            $field_id = sprintf( 'kiwi-message-%s', strtr( $key, '_', '-' ) );
            $field_name = sprintf( 'kiwi-messages[%s]', $key );

            ?>
            <p class="description">
                <label for="<?php echo $field_id; ?>"><?php echo esc_html( $arr['description'] ); ?><br />
                    <input type="text" id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" class="large-text" size="70" value="<?php echo esc_attr( $post->message( $key, false ) ); ?>" data-config-field="<?php echo sprintf( 'messages.%s', esc_attr( $key ) ); ?>" />
                </label>
            </p>
            <?php
        }
        ?>
    </fieldset>
    <?php
}

function kiwi_cf_editor_panel_additional_settings( $post ) {
    $desc_link = kiwi_cf_link(
        __( ' ', ' kiwi-contact-form' ),
        __( 'Additional Settings', ' kiwi-contact-form' ) );
    $description = __( "You can add customization code snippets here. For details, see %s.", ' kiwi-contact-form' );
    $description = sprintf( esc_html( $description ), $desc_link );

    ?>
    <fieldset>
        <legend><?php echo $description; ?></legend>
        <textarea id="kiwi-additional-settings" name="kiwi-additional-settings" cols="100" rows="8" class="large-text" data-config-field="additional_settings.body"><?php echo esc_textarea( $post->prop( 'additional_settings' ) ); ?></textarea>
    </fieldset>
    <?php
}

function kiwi_cf_editor_delete_field_modal() {
    ?>
    <div class="modal fade" id="delete-field-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div>

                        <h5 class="text-center  ">
                            <div class="mb-3">
                                <i class="fas fa-trash fa-2x "></i>
                            </div>
                           <p class=" h5">
                               Are you sure you want to delete this field? <br>
                            Your changes will be lost if you don't save them.
                           </p>
                        </h5>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger btn-sm field-delete-modal-btn">Delete</button>
                </div>
            </div>
        </div>
    </div>
<?php
}

function kiwi_cf_editor_edit_field_modal() {
    ?>
    <div class="modal fade" id="edit-field-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body edit-field-modal-body">
                    <h6 class="h5 text-info">Edit Field</h6>
                        <table class="w-100 mt-5">
                            <tbody>
                            <tr>
                                <td>Label</td>
                                <td class="float-right">
                                    <input class="edit-modal-label-input" type="text">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success btn-sm field-edit-modal-save-btn">Update</button>
                </div>
            </div>
        </div>
    </div>
    <?php
}
