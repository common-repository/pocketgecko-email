<?php

add_action( 'wp_loaded', 'pgem_register_scripts' );
function pgem_register_scripts() {
  wp_register_script( 'pocketgecko-email',
      plugins_url( '/../public/js/pocketgecko-email-min.js', __FILE__ ) );
}

// public script available on both admin and public screens
add_action( 'wp_enqueue_scripts', 'pgem_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'pgem_enqueue_scripts' );
function pgem_enqueue_scripts() {
  wp_enqueue_script( 'pocketgecko-email' );
  wp_localize_script( 'pocketgecko-email', 'pgem', array(
    'url' => '/wp-admin/admin-ajax.php',
    'action' => 'send_email',
    'nonce' => wp_create_nonce( 'send_email' ),
    'sendingString' => __( 'Sending', 'pocketgecko-email' ),
  ) );
}

