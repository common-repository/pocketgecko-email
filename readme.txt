=== PocketGecko Email ===
Contributors: axiomattik
Tags: smtp, email, post, ajax, settings, options
Requires at least: 5.0
Tested up to: 5.7
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Makes it easy to configure and send emails using POST/AJAX.

== Description ==

PocketGecko Email provides a settings page `(Settings -> Email)` to connect WordPress to an email server using basic authentication (username/password). This allows any other plugin that uses `wp_mail` to function correctly.

PocketGecko Email also provides the shortcode `[pocketgecko-email]` which renders a simple HTML form for sending email. This form supports custom recipients, carbon copies, blind carbon copies, and sending emails with one or more attachments. Any attachments sent using PocketGecko Email are automatically uploaded to the Media Library.

Full functionality is restricted to users who possess the 'publish_posts' capability. Users, including guests, who do not have this capability cannot send carbon copies or blind carbon copies, and are restricted to sending emails back to the account used to send them.

By default, PocketGecko Email attempts to 'ajaxify' the HTML form to provide an interactive user experience. If, however, JavaScript is not available, then the form will fallback to using POST instead.

Two `send_email` endpoints are created at `/wp-admin/admin-post.php` and `/wp-admin/admin-ajax.php`. These can be used by other plugins or in your own custom functions.php, JavaScript or HTML to send emails. For security reasons, use of the endpoints must be authenticated with a nonce.

There are plans to make the email form available as a Gutenberg block.

= Privacy Notices =

No information about emails sent using PocketGecko Emails is stored by the plugin, except for email attachments which are uploaded to the Media Library. 

= Docs and Support =

Documentation is currently in progress and will be made available [here](https://pocketgecko.co.uk/wp-plugins/email/docs).

Support is available on the [forums](https://wordpress.org/plugins/search/pocketgecko-email/).

== Screenshots ==

1. The settings page
2. Email form as rendered by the Twenty Twenty-One theme

== Changelog ==

= 1.0 =
* Release

== Copyright ==

Icons provided by fontawesome.com - [license](https://fontawesome.com/license)
