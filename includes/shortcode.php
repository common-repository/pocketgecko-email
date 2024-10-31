<?php

add_shortcode( 'pocketgecko-email', 'pgem_form_shortcode' );
function pgem_form_shortcode( $atts, $content, $tag ) {
  ob_start();
  
  $title = "Send Email";
  $attachments = false;

  if ( isset ( $atts['title'] ) ) {
    $title = $atts['title'];
  }
  if ( isset ( $atts['attachments'] ) && $atts['attachments'] == 'true' ) {
    $attachments = true;
  }

  require __DIR__. '/templates/email-form.php';
  return ob_get_clean();
}
