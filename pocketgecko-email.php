<?php
/**
 * Plugin Name: PocketGecko Email
 * Plugin URI:
 * Description: Provides an email settings page to configure wp_mail and simplifies sending emails using POST or AJAX.
 * Text Domain: pocketgecko-email
 * Domain Path: /i18n/languages
 * Version: 1.0.0
 * Requires at least:
 * Requires PHP:
 * Author: axiomattik
 * Author URI: 
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PGEM_PLUGIN_BASE' ) ) {
  define ( 'PGEM_PLUGIN_BASE', plugin_basename(__FILE__) );
}

if ( is_admin() ) {
  // the request is for an admin page
  require_once __DIR__ . '/pocketgecko-email-admin.php';
}

require_once __DIR__ . '/includes/email.php';
require_once __DIR__ . '/includes/scripts.php';
require_once __DIR__ . '/includes/shortcode.php';


register_activation_hook( __FILE__, 'pgem_activate' );
function pgem_activate() {
  pgem_create_email_options();
}


register_deactivation_hook( __FILE__, 'pgem_deactivate' );
function pgem_deactivate() {

}


add_filter( "plugin_action_links_pocketgecko-email/pocketgecko-email.php", "pgem_settings_link" );
function pgem_settings_link($links) {
  $link = '<a href="options-general.php?page=pgem_settings_menu">Settings</a>';
  array_unshift( $links, $link );
  return $links;
}


