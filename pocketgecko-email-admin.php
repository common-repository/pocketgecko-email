<?php

add_action( 'admin_menu', 'pgem_settings_menu' );
function pgem_settings_menu() {

  add_submenu_page(
    'options-general.php',

    // Page Title
    __( 'PocketGecko Email', 'pocketgecko-email' ),

    // Menu Title
    __( 'Email', 'pocketgecko-email' ),

    'manage_options', // capability
    'pgem_settings_menu', // menu slug

    // callback function that echoes page content
    'pgem_settings_menu_render'
  );
}

function pgem_settings_menu_render() {
  // check user capabilities 
  if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'You do not have sufficient permissions to access this page.', 'pocketgecko-email' ) );
  }

  $message = get_transient( 'pgem-message' );
  $error = get_transient( 'pgem-error' );

  if ( $message ) {
    pgem_render_message( $message );
  }

  if ( $error ) {
    pgem_render_error( $error );
  } 

  delete_transient( 'pgem-message' );
  delete_transient( 'pgem-error' );

  ?>

  <div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <form action="options.php" method="post">
      <?php
      // output security fields
      settings_fields( 'pgem_email_settings' );

      // output settings fields
      do_settings_sections( 'pgem_settings_menu' );

      // output save button
      submit_button( __( 'Save Changes', 'pocketgecko-email' ) );
      ?>
    </form>

    <hr>

    <?php require __DIR__ . '/includes/templates/email-test.php'; ?>

  </div>
<?php
}


add_action( 'admin_init', 'pgem_settings_menu_init' );
function pgem_settings_menu_init() {
  $option_group = 'pgem_email_settings';
  $page_slug = 'pgem_settings_menu';

  // Email Settings Section
  add_settings_section(
    $option_group,
    __( 'SMTP Settings', 'pocketgecko-email' ),
    null,
    $page_slug,
  );

  function pgem_render_email_settings_section() {
    ?>
    <p>SMTP settings for sending emails.</p>
    <?php
  }

  _pgem_create_option( 
    'From Email Address', 'pgem_smtp_from_email', 
    $option_group, $page_slug, array( 'type' => 'text'),
    __( "The address emails will be sent from", 'pocketgecko-email' )
  );

  _pgem_create_option( 
    'From Name', 'pgem_smtp_from_name', 
    $option_group, $page_slug, array( 'type' => 'text'),
    __( "The name of the sender", 'pocketgecko-email' )
  );

  _pgem_create_option( 
    'Host', 'pgem_smtp_host', 
    $option_group, $page_slug, array( 'type' => 'text'),
    __( "Your mail sever", 'pocketgecko-email' )
  );

  _pgem_create_option( 
    'Port', 'pgem_smtp_port', 
    $option_group, $page_slug, array( 'type' => 'number' ),
    __( "Port used by your mail server", 'pocketgecko-email' )
  );

  _pgem_create_option( 
    'Type of Encryption', 'pgem_smtp_encryption_type', 
    $option_group, $page_slug, array( 
      'type' => 'radio',
      'values' => array(
        'None', 'SSL', 'TLS',
      ) ),
    __( "The encryption method used by your mail server", 'pocketgecko-email' )
  );

  _pgem_create_option( 
    'Authentication', 'pgem_smtp_authentication', 
    $option_group, $page_slug, array( 
      'type' => 'checkbox' ),
    __( "Whether to use SMTP authentication", 'pocketgecko-email' )
  );

  _pgem_create_option( 
    'Username', 'pgem_smtp_username', 
    $option_group, $page_slug, array( 'type' => 'text' ),
    __( "Username to login to your mail server", 'pocketgecko-email' )
  );

  _pgem_create_option( 
    'Password', 'pgem_smtp_password', 
    $option_group, $page_slug, array( 'type' => 'password' ),
    __( "Password to login to your mail server", 'pocketgecko-email' )
  );

}


function pgem_render_message($message) {
  ?>
  <div class="notice notice-success is-dismissible">
    <p><?php echo $message; ?></p>
  </div>
  <?php
}

function pgem_render_error($error) {
  ?>
  <div class="notice notice-error is-dismissible">
    <pre style="white-space:pre-wrap"><?php echo $error ?></pre>
  </div>
  <?php
}


function _pgem_create_option($title, $name, $group, $page, $attributes=array(), $description='') {
  /** 
   * A helper function that simplifies creating basic WordPress settings.
   *
   * @param String $title Formatted title of the option; shown as html label.
   * @param String $name Name of the option to retrieve/save.
   * @param String $group A settings group name.
   * @param String $page The slug-name of the settings page that $group belongs to.
   * @param Array $attributes Attribute-value pairs used to render the html input element.
   * If 'type' is not provided, then 'text' is assumed. 
   * The value of 'type' is used to determine the values of 
   * 'type' and 'santize_callback' passed to 'register_setting'.
   * @param String $description The contents of the tagline for the field.
   */

  if ( ! array_key_exists( 'type', $attributes ) ) {
    $attributes['type'] = 'text';
  }

  if ( ! array_key_exists( 'name', $attributes ) ) {
    $attributes['name'] = $name;
  }

  if ( ! array_key_exists( 'value', $attributes ) ) {
    $attributes['value'] = get_option( $name );
  }

  $type_lookup = array(
    'text' => array(
      'setting_type' => 'string',
      'sanitize_callback' => 'sanitize_text_field',
      'render_callback' => 'pgem_gen_render_input' ),

    'password' => array(
      'setting_type' => 'string',
      'sanitize_callback' => 'sanitize_text_field',
      'render_callback' => 'pgem_gen_render_input' ),

    'email' => array(
      'setting_type' => 'string',
      'sanitize_callback' => 'sanitize_email',
      'render_callback' => 'pgem_gen_render_input' ),

    'number' => array(
      'setting_type' => 'number',
      'sanitize_callback' => 'sanitize_text_field',
      'render_callback' => 'pgem_gen_render_input' ),

    'url' => array(
      'setting_type' => 'string',
      'sanitize_callback' => 'esc_url_raw',
      'render_callback' => 'pgem_gen_render_input' ),

    'radio' => array(
      'setting_type' => 'string',
      'sanitize_callback' => 'sanitize_text_field',
      'render_callback' => 'pgem_gen_render_radio_input' ),

    'checkbox' => array(
      'setting_type' => 'string',
      'sanitize_callback' => 'sanitize_text_field',
      'render_callback' => 'pgem_gen_render_checkbox_input' ),



  );

  $setting_type = $type_lookup[$attributes['type']]['setting_type'];
  $sanitize_callback_name = $type_lookup[$attributes['type']]['sanitize_callback'];
  $render_callback = $type_lookup[$attributes['type']]['render_callback']( $attributes, $description );

  register_setting( $group, $name, array(
    'type' => $setting_type,
    'sanitize_callback' => $sanitize_callback_name ) );


  add_settings_field(
    $name . '_field',
    __( $title, 'pocketgecko-email' ),
    $render_callback, $page, $group
  );
}

function pgem_gen_render_input($attributes, $description='') {

  return function() use ( $attributes, $description ) {
    ob_start();
    ?>

    <input 
      <?php foreach ($attributes as $attr => $val): ?>
        <?php echo $attr . '="' . esc_attr( $val ) . '"'; ?>
      <?php endforeach; ?>
    >

    <?php if ( $description ): ?>
      <p class="description"><?php echo $description; ?></p>
    <?php endif; ?>

    <?php
    echo ob_get_clean();
  };

}

function pgem_gen_render_radio_input($attributes, $description='') {

  return function() use ( $attributes, $description ) {
    ob_start();
    ?>
    <p>
    <?php foreach ($attributes['values'] as $value): ?>
      <label>
      <input 
        type="radio" 
        name="<?php echo $attributes['name']; ?>" 
        value="<?php echo $value; ?>"
        <?php if ( $value == $attributes['value'] ): ?>
          checked
        <?php endif; ?>
      >
        <?php echo $value; ?>
      </label><br>
    <?php endforeach; ?>

    <?php if ( $description ): ?>
      <p class="description"><?php echo $description; ?></p>
    <?php endif; ?>
    </p>
    <?php
    echo ob_get_clean();
  };

}


function pgem_gen_render_checkbox_input($attributes, $description) {

  return function() use ( $attributes, $description ) {
    ob_start();
    ?>

    <label>
      <input 
        <?php foreach ( $attributes as $attr => $val ): ?>
          <?php if ( $attr != 'value' ): ?>
            <?php echo $attr . '="' . esc_attr( $val ) . '"'; ?>
          <?php endif; ?>
        <?php endforeach; ?>
        value="true"
        <?php if ( $attributes['value'] == 'true' ): ?>
          checked="checked"
        <?php endif; ?>
      >
      <?php if ( $description ): ?>
        <?php echo $description; ?>
      <?php endif; ?>
    </label>

    <?php echo ob_get_clean();
  };

}

