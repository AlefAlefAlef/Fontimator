<?php
/**
 * @var Fontimator_Font $font The relevant font
 * @var array<string> $not_purchased_weights The weights missing
 * @var string $family_name The font family name
 * @var array $family_group The downloads already purchased
 */

$license_to_buy = null;
$i = 0;
do {
	$first_variation = new Fontimator_Font_Variation( $family_group[ $i ]['product_id'] );
	if ( $first_variation->get_license_type() === 'otf' ) {
		$license_to_buy = $first_variation->get_license();
		break;
	} else {
		$i++;
		if ( $i >= count( $family_group ) ) {
			$license_to_buy = 'otf-2';
			break;
		}
	}
} while ( ! $license_to_buy );

$missing_weights_variations_ids = array_map( function ( $weight_slug ) use ( &$font, $license_to_buy ) {
	return ( $variation = $font->get_specific_variation( $weight_slug, $license_to_buy ) ) ? $variation->get_id() : null;
}, $not_purchased_weights );

$discount_percent = Fontimator::acf()->get_acf_field( 'complete_family_discount_percent', 'options' );

session_start();
foreach ( $missing_weights_variations_ids as $variation_id ) { // Add discounts to session
	$_SESSION['ftm_variation_discount_' . $variation_id] = [
		'expiry' => time() + 60*60*24 /* day */,
		'discount_percent' => $discount_percent,
		'discount_reason' => _x( 'Family Reunite discount', 'Discount Reason', 'fontimator' )
	];
}

$add_missing_weights_to_cart_url = esc_url_raw( add_query_arg( 'ftm-add-to-cart', implode( ',', $missing_weights_variations_ids ), wc_get_cart_url() ) );
?>
<div class="complete-family-banner">

	<div class="complete-family-banner--text">
		<h3><?php _e( 'Why separate family members?', 'fontimator' ); ?></h3>
		<p>
			<?php
			$gender_specific_dear = Fontimator_I18n::genderize_string(
				_x( 'Dear %s', 'Gender-nuetral "Dear %s" in dashboard', 'fontimator' ),
				_x( 'Dear %s', 'Male "Dear %s" in dashboard', 'fontimator' ),
				_x( 'Dear %s', 'Female "Dear %s" in dashboard', 'fontimator' )
			);
			
			echo esc_html( sprintf( 
				__('%1$s, The holiday season is a great time to reunite font families. For a limited time only, we offer you a %2$s%% discount on %3$s so you can now complete font %4$s. This way you won\'t leave any weight behind!', 'fontimator' ),
				sprintf( $gender_specific_dear, wp_get_current_user()->first_name ),
				$discount_percent,
				sprintf( _n( 'the weight you are missing', 'the %s weights you are missing', count( $not_purchased_weights ), 'fontimator' ), count( $not_purchased_weights ) ),
				$font->get_title()
			) ); ?>
		</p>
		<a class="button" href="<?php echo $add_missing_weights_to_cart_url; ?>">
			<i class="icon" data-icon="Ý"></i> <?php _e( 'Add the missing weights to my cart!', 'fontimator' ); ?>
		</a>
	</div>

	<nav class="more-info-nav">
		<h5><?php _e( 'More info:', 'fontimator' ); ?></h5>
		<ul>
			<li><a href="<?php echo get_permalink( "45160" ); ?>" target="_blank"><?php echo sprintf( __( "About the famliy reunion sale", 'fontimator' ), $font->get_title() ); ?></a></li>
			<?php $font_specimen = get_field( 'font_specimen', $font->get_id() );
			if ( true == $font_specimen ) { ?>
				<li><a href="<?php echo content_url() . '/fonts/' . $family_name . '/' . $family_name . '-specimen.pdf'; ?>" download="<?php echo $family_name . '-specimen-aaa.pdf'; ?>"><?php echo sprintf( __( "Font %s specimen", 'fontimator' ), $font->get_title() ); ?></a></li>
			<?php } ?>
			<li><a href="<?php echo get_field( "facebook_url", "options" ); ?>"><?php _e( 'AlefAlefAef on Facebook', 'fontimator' ); ?></a></li>
			<?php if ( Fontimator_I18n::get_user_gender() === Fontimator_I18n::GENDER_NEUTRAL ) { ?>
				<li><a href="<?php echo get_permalink( "7857" ); ?>"><?php _e( 'Join our Newsletter', 'fontimator' ); ?></a></li>
			<?php } ?>

		</ul>
	</nav>

	<figure class="feature">
		<img src="<?php echo plugin_dir_url( __DIR__ ) . 'img/family-reunite-sale.png'; ?>" />
	</figure>

</div>