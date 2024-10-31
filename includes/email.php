<?php

add_action('init', 'pgem_create_nonce');
function pgem_create_nonce() {
  wp_create_nonce('send_email');
}


function pgem_create_email_options() {
  $user = wp_get_current_user();
  $prefix = 'pgem_';
  $options = array(
    'smtp_from_email' => $user->user_email,
    'smtp_from_name' => $user->display_name,
    'smtp_host' => '',
    'smtp_port' => 465,
    'smtp_encryption_type' => 'SSL',
    'smtp_authentication' => 'true',
    'smtp_username' => $user->user_email,
    'smtp_password' => '',
  );
  foreach ($options as $opt => $val ) {
    add_option( $prefix . $opt, $val );
  }
}


add_action( 'phpmailer_init', 'pgem_phpmailer_init' );
function pgem_phpmailer_init($phpmailer) {
  $phpmailer->isSMTP();
  $phpmailer->Host = get_option( 'pgem_smtp_host' );
  $phpmailer->Port = (int) get_option( 'pgem_smtp_port' );

  $enc_type = strtolower( get_option( 'pgem_smtp_encryption_type' ) );
  $phpmailer->SMTPSecure = ( $enc_type == 'none' ) ? false : $enc_type;

  $phpmailer->SMTPAuth = ( get_option( 'pgem_smtp_authentication' ) == 'true' ) ? true : false;
  $phpmailer->Username = get_option( 'pgem_smtp_username' );
  $phpmailer->Password = get_option( 'pgem_smtp_password' );
}


// AJAX endpoint
add_action( 'wp_ajax_nopriv_send_email', 'pgem_ajax_send_email' );
add_action( 'wp_ajax_send_email', 'pgem_ajax_send_email' );
function pgem_ajax_send_email() {
  if ( pgem_send_email() ) {
    echo __( 'Email sent successfully.', 'pocketgecko-email' );
  }
  wp_die();
}


// POST endpoint
add_action( 'admin_post_nopriv_send_email', 'pgem_post_send_email' );
add_action( 'admin_post_send_email', 'pgem_post_send_email' );
function pgem_post_send_email() {
  ob_start();
  $sent = pgem_send_email();
  $error = ob_get_clean();
  $message = null;

  if ( $sent ) {
    $message = __( 'Email sent successfully.', 'pocketgecko-email' );
    set_transient( 'pgem-message', $message );
  }

  if ($error) {
    set_transient( 'pgem-error', $error);
  }

  wp_redirect( $_SERVER['HTTP_REFERER'], 302, 'pocketgecko-email' );
}


function pgem_send_email() {
  // check nonce
  if ( ! isset( $_POST['pgem-nonce'] )
    || ! wp_verify_nonce( $_POST['pgem-nonce'], 'send_email' ) 
  ) {
    echo __( "Error: request not authenticated.\n", 'pocketgecko-email' );
    return false;
  }

  // check POST request for errors
  $has_error = false;
  if ( ! isset( $_POST['recipient'] ) ) {
    echo __( "Error: an email requires a recipient.\n", 'pocketgecko-email' );
    $has_error = true;
  }

  if ( ! current_user_can( 'publish_posts') 
       && $_POST['recipient'] != 'default' ) {
    echo __( "Error: only users with the 'publish_posts' capability can send emails to non-default recipients.\n", 'pocketgecko-email' );
    $has_error = true;
  }

  if ( ! current_user_can( 'publish_posts') 
       && isset( $_POST['cc'] ) ) {
    echo __( "Error: only users with the 'publish_posts' capability can send carbon copies.\n", 'pocketgecko-email' );
    $has_error = true;
  }

  if ( ! current_user_can( 'publish_posts') 
       && isset( $_POST['bcc'] ) ) {
    echo __( "Error: only users with the 'publish_posts' capability can send blind carbon copies.\n", 'pocketgecko-email' );
    $has_error = true;
  }

  if ( ! isset( $_POST['subject'] ) ) {
    echo __( "Error: an email requires a subject.\n", 'pocketgecko-email' );
    $has_error = true;
  }

  if ( ! isset( $_POST['body'] ) ) {
    echo __( "Error: an email requires a body.\n", 'pocketgecko-email' );
    $has_error = true;
  }

  if ( $has_error ) {
    return false;
  }

  // build headers (Cc, Bcc)
  $headers = array();

  if ( isset( $_POST['cc'] ) ) {
    $cc = explode( ',', $_POST['cc'] );
    foreach ($cc as $email) {
      array_push( $headers, "Cc:" . sanitize_email( $email ) );
    }
  }

  if ( isset( $_POST['bcc'] ) ) {
    $bcc = explode( ',', $_POST['bcc'] );
    foreach ($bcc as $email) {
      array_push( $headers, "Bcc:" . sanitize_email ( $email ) );
    }
  }

  // handle attachments
  $attachments = array();

  /* To support the html5 'multiple' attribute of the file input element,
   * we need to overwrite $_FILES (yeah, I know) so that each file has a 
   * corresponding key that media_handle_upload will be able to find
   * without causing an error.
   * See: https://www.php.net/manual/en/features.file-upload.post-method.php#91479 
   * for a description of the rather odd structure of $_FILES.
  */

  // let's not rule out the possibility there's more than one 
  // input element with the 'multiple' attribute

  // a mixture of 'multiple' and single file elements is also possible

  // $form_name is the value of the 'name' attribute of the file input element
  foreach ( $_FILES as $form_name => $value ) {

    if ( gettype( $_FILES[$form_name]['name'] ) == "array" ) {
      // 'multiple' files

      $files = $_FILES[$form_name];

      foreach( $files['name'] as $index => $val ) {

        if ( $files['error'][$index] == 4 ) {
          // file not uploaded
          continue 1;
        }

        $file = array(
          'name' => $files['name'][$index],
          'type' => $files['type'][$index],
          'tmp_name' => $files['tmp_name'][$index],
          'error' => $files['error'][$index],
          'size' => $files['size'][$index],
        );

        $_FILES['tmp_file_id'] = $file;
        $attachment_id = media_handle_upload( "tmp_file_id", 0 );
        if ( is_wp_error( $attachment_id ) ) {
          pgem_print_error( $attachment_id );
          return false;
        }

        $file_path = get_attached_file( $attachment_id );
        array_push( $attachments, $file_path );

      }

    } else {
      // single file

      if ( $_FILES[$form_name]['error'] == 4 ) {
        // file not uploaded
        continue 1;
      }

      $attachment_id = media_handle_upload( $form_name, 0 );
      if ( is_wp_error( $attachment_id ) ) {
        pgem_print_error( $attachment_id );
        return false;
      }
      $file_path = get_attached_file( $attachment_id );
      array_push( $attachments, $file_path );
    }
  }
  
  // sanitize recipient, subject and body
  if ( $_POST['recipient'] === 'default' ) {
    $to = sanitize_email( get_option( 'pgem_smtp_username' ) );
  } else {
    $to = sanitize_email( $_POST['recipient'] );
  }
  $subject = sanitize_text_field( $_POST['subject'] );
  $body = sanitize_text_field( $_POST['body'] );

  // send the email
  return wp_mail( $to, $subject, $body, $headers, $attachments );
}


// show wp_mail() errors
add_action( 'wp_mail_failed', 'pgem_print_error' );
function pgem_print_error ( $wp_error ) {
  foreach ( $wp_error->get_error_messages() as $err ) {
    print_r( $err );
  }
}


/* For whatever reason, phpmailer->setFrom does not override these settings,
   so we must use the filters below instead. */
add_filter( 'wp_mail_from', 'pgem_set_from_email' );
function pgem_set_from_email() {
  return get_option( 'pgem_smtp_from_email' );
}

add_filter( 'wp_mail_from_name', 'pgem_set_from_name' );
function pgem_set_from_name() {
  return get_option( 'pgem_smtp_from_name' );
}

