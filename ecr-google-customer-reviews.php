<?php
/*
  Plugin Name:        Google Customer Reviews for WooCommerce
  Description:        Integrates Google Merchant Center's Google Customer Reviews survey opt-in and badge into your WooCommerce store.
  Author:             eCreations
  Author URI:         https://www.ecreations.net
  License:            GPLv3
  License URI:        http://www.gnu.org/licenses/quick-guide-gplv3.html
  Text Domain:        ecr-google-customer-reviews
  Version:            1.0.3
  Requires at least:  3.0.0
  Tested up to:       4.7.5
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'init', 'ecr_woocheck' );
function ecr_woocheck () {
  if (class_exists( 'WooCommerce' )) {
    if( get_option( 'ecr_merch_id' ) ) {
      add_action('woocommerce_thankyou', 'ecr_gcr_scripts');
    }else{
      add_action( 'admin_notices', 'ecr_gcr_missing_key_notice' );
    }
  }else{
    add_action( 'admin_notices', 'ecr_gcr_missing_wc_notice' );
  }
}


// Admin Error Messages

function ecr_gcr_missing_wc_notice() {
  ?>
  <div class="error notice">
      <p><?php _e( 'You need to install and activate WooCommerce in order to use Google Customer Reviews Integration!', 'ecr-google-customer-reviews' ); ?></p>
  </div>
  <?php
}

function ecr_gcr_missing_key_notice() {
  ?>
  <div class="update-nag notice">
      <p><?php _e( 'Please <a href="options-general.php?page=ecr_gcr">enter your Google Merchant ID</a> in order to use Google Customer Reviews Integration!', 'ecr-google-customer-reviews' ); ?></p>
  </div>
  <?php
}

// Admin Settings Menu

add_action( 'admin_menu', 'ecr_gcr_menu' );
function ecr_gcr_menu(){
  add_options_page( 'Google Customer Reviews Integration',
                'Google Customer Reviews', 
                'manage_options', 
                'ecr_gcr', 
                'ecr_gcr_page' );
  add_action( 'admin_init', 'update_ecr_gcr' );
}

// Register Settings (Merchant Key)

function update_ecr_gcr() {
  register_setting( 'ecr_gcr_settings', 'ecr_merch_id' );
  register_setting( 'ecr_gcr_settings', 'ecr_gcr_lang' );
  register_setting( 'ecr_gcr_settings', 'ecr_delivery_days' );
  register_setting( 'ecr_gcr_settings', 'ecr_optin_style' );
  register_setting( 'ecr_gcr_settings', 'ecr_badge_enable' );
  register_setting( 'ecr_gcr_settings', 'ecr_badge_isshop' );
  register_setting( 'ecr_gcr_settings', 'ecr_badge_style' );
}

// Admin Settings Page

function ecr_gcr_page(){
?>
<div class="wrap">
  <h1>Google Customer Reviews (GCR) Integration</h1>
  <p>Paste your Google Merchant ID below and click "Save Changes" in order to enable the Google Customer Reviews Integration. <a href="https://merchants.google.com" target="_blank">Click here to get your Google Merchant ID &raquo;</a></p>
  <p>Also, make sure you have <a href="https://merchants.google.com/mc/programs" target="_blank">enabled the Customer Reviews program</a> inside your Google Merchant account.</p>
  <form method="post" action="options.php">
    <?php settings_fields( 'ecr_gcr_settings' ); ?>
    <?php do_settings_sections( 'ecr_gcr_settings' ); ?>
    <h2>Merchant Settings</h2>
    <table class="form-table">
      <tr valign="top">
      <th scope="row">Google Merchant ID:</th>
      <td><input type="text" name="ecr_merch_id" value="<?php echo get_option( 'ecr_merch_id' ); ?>"/></td>
      </tr>
      <tr valign="top">
      <th scope="row">Language:</th>
      <td>
      <select name="ecr_gcr_lang" value="<?php $lang = get_option( 'ecr_gcr_lang' ); echo $lang; ?>"/>
      <?php
      $languages = array(
        '' => 'Auto-detect',
        'cs' => 'Czech',
        'da' => 'Danish',
        'de' => 'German',
        'en_AU' => 'English (Australia)',
        'en_GB' => 'English (United Kingdom)',
        'en_US' => 'English (United States)',
        'es' => 'Spanish',
        'fr' => 'French',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'nl' => 'Dutch',
        'no' => 'Norwegian',
        'pl' => 'Polish',
        'pt_BR' => 'Portuguese (Brazil)',
        'ru' => 'Russian',
        'sv' => 'Swedish',
        'tr' => 'Turkish'
      );
      foreach($languages as $code => $label) {
        echo '<option value="'.$code.'" ';
        if($lang==$code)echo 'selected';
        echo '>'.$label.'</option>';
      }
      ?>
      </select>
      </td>
      </tr>
    </table>
    <h2>Survey Opt-in Popup Settings</h2>
    <table class="form-table">
      <tr valign="top">
      <th scope="row">Popup Position:</th>
      <td><select name="ecr_optin_style" value="<?php $style = get_option( 'ecr_optin_style' ); echo $style; ?>"/>
        <option value="CENTER_DIALOG" <?php if($style=='CENTER_DIALOG')echo 'selected';?>>Center</option>
        <option value="TOP_LEFT_DIALOG" <?php if($style=='TOP_LEFT_DIALOG')echo 'selected';?>>Top Left</option>
        <option value="TOP_RIGHT_DIALOG" <?php if($style=='TOP_RIGHT_DIALOG')echo 'selected';?>>Top Right</option>
        <option value="BOTTOM_LEFT_DIALOG" <?php if($style=='BOTTOM_LEFT_DIALOG')echo 'selected';?>>Bottom Left</option>
        <option value="BOTTOM_RIGHT_DIALOG" <?php if($style=='BOTTOM_RIGHT_DIALOG')echo 'selected';?>>Bottom Right</option>
        <option value="BOTTOM_TRAY" <?php if($style=='BOTTOM_TRAY')echo 'selected';?>>Bottom Tray</option>
      </select></td>
      </tr>
      <tr valign="top">
      <th scope="row">Estimated Delivery (Days):</th>
      <td><input type="number" name="ecr_delivery_days" value="<?php echo get_option( 'ecr_delivery_days' ); ?>"/></td>
      </tr>
    </table>
    <h2>GCR Badge Settings</h2>
    <table class="form-table">
      <tr valign="top">
      <th scope="row">Enable Rating Badge:</th>
      <td><input type="checkbox" name="ecr_badge_enable" value="true" <?php if(get_option('ecr_badge_enable')==true)echo 'checked'; ?>/></td>
      </tr>
      <tr valign="top">
      <th scope="row">Only Show Badge in Shop:</th>
      <td><input type="checkbox" name="ecr_badge_isshop" value="true" <?php if(get_option('ecr_badge_isshop')==true)echo 'checked'; ?>/></td>
      </tr>
      <tr valign="top">
      <th scope="row">Rating Badge Position:</th>
      <td><select name="ecr_badge_style" value="<?php $style = get_option( 'ecr_badge_style' ); echo $style; ?>"/>
        <option value="none" <?php if($style=='none')echo 'selected';?>>None</option>
        <option value="BOTTOM_LEFT" <?php if($style=='BOTTOM_LEFT')echo 'selected';?>>Bottom Left</option>
        <option value="BOTTOM_RIGHT" <?php if($style=='BOTTOM_RIGHT')echo 'selected';?>>Bottom Right</option>
      </select></td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
</div>
<?php
}

function ecr_gcr_scripts($order_id) {
	$order = new WC_Order( $order_id );
    ?><!-- BEGIN GCR Opt-in Module Code -->
<script src="https://apis.google.com/js/platform.js?onload=renderOptIn"
  async defer>
</script>

<script>
  window.renderOptIn = function() { 
    window.gapi.load('surveyoptin', function() {
      window.gapi.surveyoptin.render(
        {
          "merchant_id": <?php echo get_option('ecr_merch_id'); ?>,
          "order_id": "<?php echo $order_id; ?>",
          "email": "<?php echo $order->billing_email; ?>",
          "delivery_country": "<?php echo $order->billing_country; ?>",
          "estimated_delivery_date": "<?php echo date('Y-m-d', strtotime($order->order_date.' + '.(int)get_option('ecr_delivery_days').' days')); ?>",
          "opt_in_style": "<?php echo get_option( 'ecr_optin_style' ); ?>"
        }); 
     });
  }
</script>
<!-- END GCR Opt-in Module Code -->

<!-- BEGIN GCR Language Code -->
<script>
  window.___gcfg = {
    lang: '<?php echo get_option( 'ecr_gcr_lang' ); ?>'
  };
</script>
<!-- END GCR Language Code -->
<?php 
}

add_action( 'wp_footer', 'gcr_badge' );
function gcr_badge() {
  if(get_option('ecr_badge_enable')){
    if(get_option('ecr_badge_isshop') && !is_woocommerce()){
      //do nothing
    }else{
      $style = get_option('ecr_badge_style');
      if($style != 'none') {
      ?>
    <!-- BEGIN GCR Badge Code -->
    <script src="https://apis.google.com/js/platform.js?onload=renderBadge"
      async defer>
    </script>

    <script>
      window.renderBadge = function() {
        var ratingBadgeContainer = document.createElement("div");
        document.body.appendChild(ratingBadgeContainer);
        window.gapi.load('ratingbadge', function() {
          window.gapi.ratingbadge.render(
            ratingBadgeContainer, {
              "merchant_id": <?php echo get_option('ecr_merch_id'); ?>,
              "position": "<?php echo $style; ?>"
            });
        });
      }
    </script>
    <!-- END GCR Badge Code -->

    <!-- BEGIN GCR Language Code -->
    <script>
      window.___gcfg = {
        lang: '<?php echo get_option( 'ecr_gcr_lang' ); ?>'
      };
    </script>
    <!-- END GCR Language Code -->
    <?php
      }
    }
  }
}