<?php
/**
 * Email Preferences Tab
 * 
 * @since 4.2.2
 */

defined( 'ABSPATH' ) or exit;
Fontimator::mc()->is_user_subscribed() or exit;

$options = Fontimator::mc()->interest_groups;
$user_groups = Fontimator::mc()->get_user_groups();

if ( ! $options ) {
  echo __( 'There was a problem loading this page, please let us know.', 'fontimator' );
  exit;
}
?>
<form class="woocommerce-EmailPreferencesForm edit-account" action="" method="post">

  <h3><?php _e( 'Let\'s stay in touch!', 'fontimator' ); ?></h3>
  <p class="newsletter-text"><?php _e( 'Every once in a while we send emails with updates about new fonts, special discounts & offers, tips for designers, free stuff and a lot of inspiration. We don\'t spam, and we will never give away your details to strangers.', 'fontimator' ); ?></p>
  <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <span><?php _e( 'Which updates would you like to receive?', 'fontimator' ); ?></span>
    <?php
    
    foreach ( $options as $option ) {
      $group_id = $option['ftm_interest_group'];
      $label = $option['ftm_interest_label'];
      ?>
      <label class="form-full-checkbox">
        <input type="checkbox" name="interests[<?php echo $group_id; ?>]" <?php echo ( in_array( $group_id, $user_groups ) ) ? 'checked' : ''; ?> />
        <?php echo $label; ?>
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