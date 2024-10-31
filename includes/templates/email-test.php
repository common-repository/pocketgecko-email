<form id="send-test-email" action="/wp-admin/admin-post.php" method="post" enctype="multipart/form-data">
  <h2>Send Test Email</h2>
  <p>Use the saved smtp settings above to send a test email.</p>
  <input type="hidden" name="action" value="send_email">
  <?php wp_nonce_field( 'send_email', 'pgem-nonce' ); ?>
  <table class="form-table" role="presentation">
    <tbody>

      <tr>
        <th scope="row">Recipient</th>
        <td>
          <input type="text" name="recipient" 
            value="<?php echo get_option( 'pgem_smtp_username' ); ?>" required>
        </td>
      </tr>

      <tr>
        <th scope="row">Cc</th>
        <td>
          <input type="text" name="cc">
          <p class="description">A comma separated list of emails for carbon copy</p>
        </td>
      </tr>

      <tr>
        <th scope="row">Bcc</th>
        <td>
          <input type="text" name="bcc">
          <p class="description">A comma separated list of emails for blind carbon copy</p>
        </td>
      </tr>

      <tr>
        <th scope="row">Subject</th>
        <td>
          <input type="text" name="subject" value="Test Email" required>
        </td>
      </tr>

      <tr>
        <th scope="row">Body</th>
        <td>
          <textarea name="body" required>This is a test email.</textarea>
        </td>
      </tr>

      <tr>
        <th scope="row">Attachment(s)</th>
        <td>
          <input type="file" name="attachments[]" multiple>
        </td>
      </tr>

    </tbody>
  </table>
  <p class="submit">
    <input type="submit" class="button button-primary" value="Send Test Email">
  </p>
</form>
