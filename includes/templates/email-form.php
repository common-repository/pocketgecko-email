<form id="pocketgecko-email" action="/wp-admin/admin-post.php" method="post" enctype="multipart/form-data">
  <h2><?php echo $title; ?></h2>
  <input type="hidden" name="action" value="send_email">
  <?php wp_nonce_field( 'send_email', 'pgem-nonce' ); ?>


  <?php 
    $message = get_transient( 'pgem-message' );
    $error = get_transient( 'pgem-error' );

    if ( $message ) {
      echo "<strong>$message</strong>";
    }

    if ( $error ) {
      echo "<strong>$error</strong>";
    }
    delete_transient( 'pgem-message' );
    delete_transient( 'pgem-error' );
  ?>

  <fieldset>

    <?php if ( current_user_can( 'edit_posts' ) ): ?>

      <label for="recipient">Recipient: </label>
      <input id="recipient" type="text" name="recipient" required>

      <label for="cc">Cc: </label>
      <input id="cc" type="text" name="cc">

      <label for="bcc">Bcc: </label>
      <input id="bcc" type="text" name="cc">

    <?php else: ?>

      <input type="hidden" name="recipient" value="default">

    <?php endif; ?>

    <label for="subject">Subject: </label>
    <input id="subject" type="text" name="subject" required>

    <label for="body">Body: </label>
    <textarea id="body" name="body" required></textarea>

    <?php if ( $attachments == true ): ?>
      <label for="attachements">Attachment(s): </label>
      <input id="attachments" type="file" name="attachments[]" multiple>
    <?php endif; ?>

  </fieldset>

  <input type="submit" class="button button-primary" value="Send">

  <output></output>

</form>
