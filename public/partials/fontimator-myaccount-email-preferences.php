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

  <h3><?php 
        $gender_specific_lets = Fontimator_I18n::genderize_string(
        _x( 'Let\'s', 'Gender-nuetral "Let\'s" in user email preference page', 'fontimator' ),
        _x( 'Let\'s', 'Male "Let\'s" in user email preference page', 'fontimator' ),
        _x( 'Let\'s', 'Female "Let\'s" in user email preference page', 'fontimator' )
      );
      printf(
        __( '%1$s stay in touch!', 'fontimator' ),
        $gender_specific_lets
      ); ?></h3>
  <p class="newsletter-text"><?php _e( 'Every once in a while we send emails with updates about new fonts, special discounts & offers, tips for designers, free stuff and a lot of inspiration. We don\'t spam, and we will never give away your details to strangers.', 'fontimator' ); ?></p>
  <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
  <h5>
    <?php 
      $gender_specific_action = Fontimator_I18n::genderize_string(
        _x( 'would you like', 'Gender-nuetral "would you like" in user email preference page', 'fontimator' ),
        _x( 'would you like', 'Male "would you like" in user email preference page', 'fontimator' ),
        _x( 'would you like', 'Female "would you like" in user email preference page', 'fontimator' )
      );
      printf(
        __( 'Which updates %1$s to receive?', 'fontimator' ),
        $gender_specific_action
      ); 
    ?>
  </h5>
  <div class="interest-groups">
    <?php
    
    foreach ( $options as $option ) {
      $group_id = $option['ftm_interest_group'];
      $icon     = $option['ftm_interest_icon'];
      $label    = $option['ftm_interest_label'];
      $description = $option['ftm_interest_description'];
      $frequency = $option['ftm_interest_frequency'];
      ?>
      <label class="form-full-checkbox">
        <input type="checkbox" class="ios-checkbox" style="display:none" name="interests[<?php echo $group_id; ?>]" <?php echo ( in_array( $group_id, $user_groups ) ) ? 'checked' : ''; ?> />
          
        <i class="icon" data-icon="<?php echo esc_attr($icon ?: 'ñ'); ?>"></i>

        <div class="texts">
          <?php if ( $label ) { ?>
            <span class="title"><?php echo $label; ?></span>
          <?php } ?>

          <?php if ( $description ) { ?>
            <span class="description"><?php echo $description; ?></span>
          <?php } ?>

          <?php if ( $frequency ) { ?>
            <span class="frequency icon" data-icon="Ó"><?php echo $frequency; ?></span>
          <?php } ?>
        </div>

      </label>
      <?php
    } 
  ?>
  </div>

  <p>
		<?php wp_nonce_field( 'save_email_preferences', 'save-email-preferences-nonce' ); ?>
		<button type="submit" class="woocommerce-Button button b-big" name="save_email_preferences" value="<?php esc_attr_e( 'Save email preferences', 'fontimator' ); ?>"><?php esc_html_e( 'Save email preferences', 'fontimator' ); ?></button>
		<input type="hidden" name="action" value="save_email_preferences" />
	</p>
</form>