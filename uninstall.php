<?php

defined('WP_UNINSTALL_PLUGIN') || exit;

global $wpdb;

// remove options
$sql  = "SELECT option_name FROM {$wpdb->prefix}options ";
$sql .= "WHERE option_name LIKE 'pgem_%';";
foreach ( $wpdb->get_results($sql) as $opt ) {
    delete_option($opt);
}

