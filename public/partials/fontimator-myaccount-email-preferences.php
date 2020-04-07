<?php
/**
 * Email Preferences Tab
 * 
 * @since 4.2.2
 */

defined( 'ABSPATH' ) or exit;
Fontimator::mc()->is_user_subscribed() or exit;

$options = Fontimator::mc()->get_preference_options();
$user_groups = Fontimator::mc()->get_user_groups();

if ( ! $options ) {
  echo __( 'There was a problem loading this page, please let us know.', 'fontimator' );
  exit;
}
?>
<form class="woocommerce-EmailPreferencesForm edit-account" action="" method="post">

  <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <span><?php _e( 'Which updates would you like to receive?', 'fontimator' ); ?></span>
    <?php
    
    foreach ( $options as $key => $option ) {
      ?>
      <label class="form-full-checkbox">
        <input type="checkbox" name="interests[<?php echo $key; ?>]" <?php echo ( in_array( $key, $user_groups ) ) ? 'checked' : ''; ?> />
        <?php echo $option; ?>
      </label>
      <?php
    }
    ?>
  </p>
  <div class="clear"></div>

  <p>
		<?php wp_nonce_field( 'save_email_preferences', 'save-email-preferences-nonce' ); ?>
		<button type="submit" class="woocommerce-Button button" name="save_email_preferences" value="<?php esc_attr_e( 'Save email preferences', 'fontimator' ); ?>"><?php esc_html_e( 'Save email preferences', 'fontimator' ); ?></button>
		<input type="hidden" name="action" value="save_email_preferences" />
	</p>
</form>