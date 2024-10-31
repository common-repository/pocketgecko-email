<div class="wrap">
  <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
  <form action="options.php" method="post">
    <?php
    settings_fields( 'pgem-settings-menu' );
    do_settings_sections( 'pgem-settings' );
    submit_button( __( 'Save Settings', 'pocketgecko-email' ) );
    ?>
  </form>
</div>
