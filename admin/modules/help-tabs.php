<?php

class KIWI_CF_Help_Tabs {

	private $screen;

	public function __construct( WP_Screen $screen ) {
		$this->screen = $screen;
	}

	public function set_help_tabs( $type ) {
		switch ( $type ) {
			case 'list':
				$this->screen->add_help_tab( array(
					'id' => 'list_overview',
					'title' => __( 'Overview', 'kiwi-contact-form' ),
					'content' => $this->content( 'list_overview' ) ) );

				$this->screen->add_help_tab( array(
					'id' => 'list_available_actions',
					'title' => __( 'Available Actions', 'kiwi-contact-form' ),
					'content' => $this->content( 'list_available_actions' ) ) );

				$this->sidebar();

				return;
			case 'edit':
				$this->screen->add_help_tab( array(
					'id' => 'edit_overview',
					'title' => __( 'Overview', 'kiwi-contact-form' ),
					'content' => $this->content( 'edit_overview' ) ) );

				$this->screen->add_help_tab( array(
					'id' => 'edit_form_tags',
					'title' => __( 'Form-tags', 'kiwi-contact-form' ),
					'content' => $this->content( 'edit_form_tags' ) ) );

				$this->screen->add_help_tab( array(
					'id' => 'edit_mail_tags',
					'title' => __( 'Mail-tags', 'kiwi-contact-form' ),
					'content' => $this->content( 'edit_mail_tags' ) ) );

				$this->sidebar();

				return;
			case 'integration':
				$this->screen->add_help_tab( array(
					'id' => 'integration_overview',
					'title' => __( 'Overview', 'kiwi-contact-form' ),
					'content' => $this->content( 'integration_overview' ) ) );

				$this->sidebar();

				return;
		}
	}

	private function content( $name ) {
		$content = array();

		$content['list_overview'] = '<p>' . __( "On this screen, you can manage contact forms provided by Kiwi Contact Form. You can manage an unlimited number of contact forms. Each contact form has a unique ID and Kiwi Contact Form shortcode ([kiwi-contact-form ...]). To insert a contact form into a post or a text widget, insert the shortcode into the target.", 'kiwi-contact-form' ) . '</p>';

		$content['list_available_actions'] = '<p>' . __( "Hovering over a row in the contact forms list will display action links that allow you to manage your contact form. You can perform the following actions:", 'kiwi-contact-form' ) . '</p>';
		$content['list_available_actions'] .= '<p>' . __( "<strong>Edit</strong> - Navigates to the editing screen for that contact form. You can also reach that screen by clicking on the contact form title.", 'kiwi-contact-form' ) . '</p>';
		$content['list_available_actions'] .= '<p>' . __( "<strong>Duplicate</strong> - Clones that contact form. A cloned contact form inherits all content from the original, but has a different ID.", 'kiwi-contact-form' ) . '</p>';

		$content['edit_overview'] = '<p>' . __( "On this screen, you can edit a contact form. A contact form is comprised of the following components:", 'kiwi-contact-form' ) . '</p>';
		$content['edit_overview'] .= '<p>' . __( "<strong>Title</strong> is the title of a contact form. This title is only used for labeling a contact form, and can be edited.", 'kiwi-contact-form' ) . '</p>';
		$content['edit_overview'] .= '<p>' . __( "<strong>Form</strong> is a content of HTML form. You can use arbitrary HTML, which is allowed inside a form element. You can also use Kiwi Contact Form&#8217;s form-tags here.", 'kiwi-contact-form' ) . '</p>';
		$content['edit_overview'] .= '<p>' . __( "<strong>Mail</strong> manages a mail template (headers and message body) that this contact form will send when users submit it. You can use Kiwi Contact Form&#8217;s mail-tags here.", 'kiwi-contact-form' ) . '</p>';
		$content['edit_overview'] .= '<p>' . __( "<strong>Mail (2)</strong> is an additional mail template that works similar to Mail. Mail (2) is different in that it is sent only when Mail has been sent successfully.", 'kiwi-contact-form' ) . '</p>';
		$content['edit_overview'] .= '<p>' . __( "In <strong>Messages</strong>, you can edit various types of messages used for this contact form. These messages are relatively short messages, like a validation error message you see when you leave a required field blank.", 'kiwi-contact-form' ) . '</p>';
		$content['edit_overview'] .= '<p>' . __( "<strong>Additional Settings</strong> provides a place where you can customize the behavior of this contact form by adding code snippets.", 'kiwi-contact-form' ) . '</p>';

		$content['edit_form_tags'] = '<p>' . __( "A form-tag is a short code enclosed in square brackets used in a form content. A form-tag generally represents an input field, and its components can be separated into four parts: type, name, options, and values. Kiwi Contact Form supports several types of form-tags including text fields, number fields, date fields, checkboxes, radio buttons, menus, file-uploading fields, CAPTCHAs, and quiz fields.", 'kiwi-contact-form' ) . '</p>';
		$content['edit_form_tags'] .= '<p>' . __( "While form-tags have a comparatively complex syntax, you don&#8217;t need to know the syntax to add form-tags because you can use the straightforward tag generator (<strong>Generate Tag</strong> button on this screen).", 'kiwi-contact-form' ) . '</p>';

		$content['edit_mail_tags'] = '<p>' . __( "A mail-tag is also a short code enclosed in square brackets that you can use in every Mail and Mail (2) field. A mail-tag represents a user input value through an input field of a corresponding form-tag.", 'kiwi-contact-form' ) . '</p>';
		$content['edit_mail_tags'] .= '<p>' . __( "There are also special mail-tags that have specific names, but don&#8217;t have corresponding form-tags. They are used to represent meta information of form submissions like the submitter&#8217;s IP address or the URL of the page.", 'kiwi-contact-form' ) . '</p>';

		$content['integration_overview'] = '<p>' . __( "On this screen, you can manage services that are available through Kiwi Contact Form. Using API will allow you to collaborate with any services that are available.", 'kiwi-contact-form' ) . '</p>';
		$content['integration_overview'] .= '<p>' . __( "You may need to first sign up for an account with the service that you plan to use. When you do so, you would need to authorize Kiwi Contact Form to access the service with your account.", 'kiwi-contact-form' ) . '</p>';
		$content['integration_overview'] .= '<p>' . __( "Any information you provide will not be shared with service providers without your authorization.", 'kiwi-contact-form' ) . '</p>';

		if ( ! empty( $content[$name] ) ) {
			return $content[$name];
		}
	}

	public function sidebar() {
		$content = '<p><strong>' . __( 'For more information:', 'kiwi-contact-form' ) . '</strong></p>';
		$content .= '<p>' . kiwi_cf_link( __( ' ', 'kiwi-contact-form' ), __( 'Docs', 'kiwi-contact-form' ) ) . '</p>';
		$content .= '<p>' . kiwi_cf_link( __( ' ', 'kiwi-contact-form' ), __( 'FAQ', 'kiwi-contact-form' ) ) . '</p>';
		$content .= '<p>' . kiwi_cf_link( __( ' ', 'kiwi-contact-form' ), __( 'Support', 'kiwi-contact-form' ) ) . '</p>';

		$this->screen->set_help_sidebar( $content );
	}
}
