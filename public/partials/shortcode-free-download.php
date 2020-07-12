<?php
/**
 * @since      2.2.0
 */
$download_id = $atts['download'];
$download    = new Fontimator_Free_Download( $download_id );

?>

<section class="fontimator-free-download" id="ftm-free-download-<?php echo $download_id; ?>">

	<a class="open button b-fullwidth b-big b-outline b-icon-before" href="#" data-icon="☆"><?php printf( _x( 'Download Free License for %s:', 'Free Download Title', 'fontimator' ), $download->get_name() ); ?></a>
	<?php

	if ( isset( $_POST['download-id'] ) && $_POST['download-id'] === $download_id ) {

		if ( $_POST['download-user-name'] && $_POST['download-user-email'] && 'on' === $_POST['download-terms'] ) {
			if ( Fontimator::mc()->enabled() && 'on' === $_POST['download-newsletter'] && is_email( $_POST['download-user-email'] ) ) {
				$email         = $_POST['download-user-email'];
				$exploded_name = explode( ' ', $_POST['download-user-name'] );
				$first_name    = array_shift( $exploded_name );
				$last_name     = implode( ' ', $exploded_name );
				Fontimator::mc()->add_subscriber_to_freefonts_group( $email, $first_name, $last_name );
			}

			$download->register_download( $_POST['download-user-name'], $_POST['download-user-email'] );
			wp_redirect( $download->get_url() );
		} else {
			_e( 'Please enter a valid email address and name to download the files.', 'fontimator' );
		}
	}

	$current_user      = wp_get_current_user();
	$user_default_name = implode( ' ', array_filter( [ $current_user->first_name, $current_user->last_name ] ) ); // Fancy way of doing $first . ' ' . $last

	list($download_type, $download_name) = explode( '_', $download_id, 2 );
	?>

	<form action="#ftm-free-download-<?php echo $download_id; ?>" class="download-type-<?php echo $download_type; ?>" method="post">
		<?php if ( 'poster' != $download_type ) { ?>
			<h3><?php printf( _x( 'Download Free License for %s:', 'Free Download Title', 'fontimator' ), $download->get_name() ); ?></h3>
			<p><?php printf( __( 'Read our %1$sFree fonts EULA%2$s', 'fontimator' ), '<a href="https://alefalefalef.co.il/eula/?licenses=free" target="_blank">', '</a>' ); ?></p>
		<?php } ?>

		<label for="download-user-name"><?php echo esc_attr_x( 'Your name', 'Free Download Text Label', 'fontimator' ); ?></label>
		<input id="download-user-name" class="your-name" type="text" placeholder="<?php echo esc_attr_x( 'John Smith', 'Free Download Text Field', 'fontimator' ); ?>" value="<?php echo $user_default_name; ?>" required minlength="6" pattern="[א-ת \-־]+" name="download-user-name">

		<label for="download-user-email"><?php echo esc_attr_x( 'Your email', 'Free Download Text Label', 'fontimator' ); ?></label>
		<input id="download-user-email" class="your-email" type="email" placeholder="<?php echo esc_attr_x( 'your@email.here', 'Free Download Text Field', 'fontimator' ); ?>" value="<?php echo $current_user->user_email; ?>" name="download-user-email">

		<input type="hidden" name="download-id" value="<?php echo $download_id; ?>">
		
		<?php if ( 'poster' == $download_type ) { ?>
				<small>
					<?php printf( __( 'Your email is only for your license. This will not add you to a mailing list. If you would like to get updated %1$ssign up to our newsletter%2$s.', 'fontimator' ), '<a href="' . Fontimator_MC::SIGNUP_URL . '">', '</a>' ); ?>
				</small>
			<?php } else { ?>
				<small>
					<?php printf( __( 'Please do not contact us about free fonts. All the info you might need can be found in our %1$sFAQ%2$s page.', 'fontimator' ), '<a href="https://alefalefalef.co.il/resources/faq/">', '</a>' ); ?>
				</small>
				<small>
					<?php _e( 'Your free license will be activated upon submitting your name and email address. Font usage without a license is a violation of copyright laws.', 'fontimator' ); ?>
				</small>
		<?php } ?>

		<label>
			<input type="checkbox" name="download-terms" required>
			<?php if ( 'poster' == $download_type ) { ?>
				<?php printf( __( 'I have read the %1$sfree posters usage terms%2$s and I accept them.', 'fontimator' ), '<a href="#terms">', '</a>' ); ?>
			<?php } else { ?>
				<?php printf( __( 'I have read the %1$sfree fonts usage terms%2$s and I accept them.', 'fontimator' ), '<a href="https://alefalefalef.co.il/eula/?licenses=free" target="_blank">', '</a>' ); ?>
			<?php } ?>
		</label>

		<?php if ( Fontimator::mc()->enabled() && 'poster' != $download_type ) { ?>
			<label>
				<input type="checkbox" name="download-newsletter" class="tipotip-checkbox">
				<?php _e( 'Subscribe me to the newsletter!', 'fontimator' ); ?>
			</label>
		<?php } ?>

		<button type="submit" class="button b-icon-before b-medium" data-icon="x"><?php printf( _x( 'Download Free License for %s:', 'Free Download Title', 'fontimator' ), $download->get_name() ); ?></button>
		<div class="success-overlay">
			<h3><span>☺</span></h3>
			<p><?php _e( 'Thanks for downloading our font! Your download will begin shortly.', 'fontimator' ); ?></p>
			<p class="tipotip-message"><?php _e( 'Also, we just now sent you an email to approve your subsctiption to the Tipotip.', 'fontimator' ); ?></p>
			<button class="close">&times;</button>
		</div>
	</form>

</section><!--/#download-->
