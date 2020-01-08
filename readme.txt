=== Kiwi Contact Form ===

Tags: contact, form, contact form, feedback, email, ajax, captcha, akismet, multilingual
Requires at least: 5.3
Tested up to: 5.3.2
Stable tag: 5.3.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Kiwi Contact Form can manage multiple contact forms, plus you can customize the form and the mail contents flexibly with simple markup. The form supports Ajax-powered submitting, CAPTCHA, Akismet spam filtering and so on.

== Installation ==

1. Upload the entire `kiwi-contact-form` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
You will find 'Kiwi Contact' menu in your WordPress admin panel.

== Changelog ==
= 1.0.1 =

* REST API: retrieves the contact form ID explicitly from the route parameters.
* Config Validator: New test item for the attachments_overweight and unavailable_html_elements errors.
* reCAPTCHA: introduces the KIWI_CF_RECAPTCHA_SITEKEY and kiwi_cf_RECAPTCHA_SECRET constants.
* reCAPTCHA: Introduces the kiwi_cf_recaptcha_actions, kiwi_cf_recaptcha_threshold, kiwi_cf_recaptcha_sitekey and kiwi_cf_recaptcha_secret filter hooks.
* Adds $status parameter to the kiwi_cf_form_response_output filter.
* Creates a nonce only when the submitter is a logged-in user.
* Introduces KiwiCfContactForm::unit_tag(), a public method that returns a unit tag.
* reCAPTCHA: gives a different spam log message for cases where the response token is empty.
* Acceptance Checkbox: supports the label_first option in an acceptance form-tag.
* Constant Contact: Introduces the constant_contact additional setting and contact list selector.
* reCAPTCHA: Modifies the reaction to empty response tokens.
* Introduces the Constant Contact integration module.
* Fixes the inconsistency problem between get_data_option() and get_default_option() in the kiwi_cf_FormTag class.
* Suppresses PHP errors occur on unlink() calls.
* Introduces kiwi_cf_is_file_path_in_content_dir() to support the use of the UPLOADS constant.
* Specifies the capability_type argument explicitly in the register_post_type() call to fix the privilege escalation vulnerability issue.
* Local File Attachment – disallows the specifying of absolute file paths referring to files outside the wp-content directory.
* Config Validator – adds a test item to detect invalid file attachment settings.
* Fixes a bug in the JavaScript fallback function for legacy browsers that do not support the HTML5 placeholder attribute.
* Acceptance Checkbox – unsets the form-tag's do-not-store feature.
* CSS: Applies the "not-allowed" cursor style to submit buttons in the "disabled" state.
* Acceptance Checkbox: Revises the tag-generator UI to encourage the use of better options in terms of personal data protection.
* Introduces kiwi_cf_anonymize_ip_addr() function.
* Introduces the consent_for:storage option for all types of form-tags.
* Updated the Information meta-box content.
* Use get_user_locale() instead of get_locale() where it is more appropriate.
* Acceptance Checkbox: Reset submit buttons' disabled status after a successful submission.
* Fixed incorrect uses of _n().
* Config validation: Fixed incorrect count of alerts in the Additional Settings tab panel.
* Config validation: Fixed improper treatment for the [_site_admin_email] special mail-tag in the From mail header field.
* Acceptance checkbox: The class and id attributes specified were applied to the wrong HTML element.
* Config validation: When there is an additional mail header for mailboxes like Cc or Reply-To, but it has a possible empty value, "Invalid mailbox syntax is used" error will be returned.
* Explicitly specify the fourth parameter of add_action() to avoid passing unintended parameter values.
* Check if the target directory is empty before removing the directory.
* Additional settings: on_sent_ok and on_submit have been removed.
* New additional setting: skip_mail
* Flamingo: Inbound channel title changes in conjunction with a change in the title of the corresponding contact form.
* DOM events: Make an entire API response object accessible through the event.detail.apiResponse property.
* HTML mail: Adds language-related attributes to the HTML header.
* File upload: Sets the accept attribute to an uploading field.
* Introduces the kiwi_cf_MailTag class.
* Allows aborting a mail-sending attempt using the kiwi_cf_before_send_mail action hook. Also, you can set a custom status and a message through the action hook.
* Acceptance checkbox: Allows the specifying of a statement of conditions in the form-tag's content part and supports the optional option.
* New special mail tags: [_site_title], [_site_description], [_site_url], [_site_admin_email], [_invalid_fields], [_user_login], [_user_email], [_user_url], [_user_first_name], [_user_last_name], [_user_nickname], and [_user_display_name]
* New filter hooks: kiwi_cf_upload_file_name, kiwi_cf_autop_or_not, kiwi_cf_posted_data_{$type}, and kiwi_cf_mail_tag_replaced_{$type}
* New form-tag features: zero-controls-container and not-for-mail