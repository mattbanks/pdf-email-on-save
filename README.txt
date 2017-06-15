=== PDF Email on Save ===
Contributors: mjbanks, jchristopher
Donate link: http://mattbanks.me/donate/
Tags: pdf, email, custom post types,
Requires at least: 3.7
Tested up to: 4.8
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a PDF for a given post, page, or custom post type when the content is saved and email it to the user.

== Description ==

Create a PDF for a given post, page, or custom post type when the content is saved. Users can select which post types are included and which email to send the PDF to on the plugin settings page under Settings -> PDF Email on Save.

Filters are available for changing all content in the generated PDF - see FAQ for more details.

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'pdf-email-on-save'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `pdf-email-on-save.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `pdf-email-on-save.zip`
2. Extract the `pdf-email-on-save` directory to your computer
3. Upload the `pdf-email-on-save` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard


== Frequently Asked Questions ==

= How do I customize the PDF or email being sent? =

There are a number of filters available to change the content of the PDF or the email being sent. All standard content can be viewed in the plugin folder under `public/class-pdf-email-on-save.php`. The filters available include:

- Subject for email being sent: `pdf_email_on_save_subject`
- Content of email message being sent: `pdf_email_on_save_message`
- Filename of PDF attachement being sent: `pdf_email_on_save_filename`
- Header of generated PDF: `pdf_email_on_save_header`
- Content of generated PDF: `pdf_email_on_save_content`
- Footer of generated PDF: `pdf_email_on_save_footer`
- Combined HTML of generated PDF: `pdf_email_on_save_html`


== Screenshots ==

1. Plugin settings page

== Changelog ==

= 1.1.0 =
* update to mpdf 6.1.0
* update mail function call to work better with attachments

= 1.0.1 =
* remove utf8 encoding when writing html - causes weird character encoding in emails

= 1.0 =
* Initial build
