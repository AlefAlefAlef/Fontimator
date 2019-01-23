<?php
/**
 * @since      2.2.0
 */
$download_id = $atts['download'];
$download = new Fontimator_Free_Download( $download_id );

?>

<section class="fontimator-free-download" id="ftm-free-download-<?php echo $download_id; ?>">
	<?php

	if ( isset( $_POST['download-id'] ) && $_POST['download-id'] === $download_id ) {


		if ( $_POST['download-user-name'] && $_POST['download-user-email'] && 'on' === $_POST['download-terms'] ) {
			if ( 'on' === $_POST['download-newsletter'] && function_exists( 'mc4wp_get_api' ) && is_email( $_POST['download-user-email'] ) ) {
				$list_id = '0b3d24ccab'; // Main AAA list
				$email = $_POST['download-user-email'];
				$api = mc4wp_get_api();
				$exploded_name = explode( ' ', $_POST['download-user-name'] );
				$first_name = array_shift( $exploded_name );
				$last_name = implode( ' ', $exploded_name );

				var_dump(
					$api->subscribe(
						$list_id, $email, array(
							'FNAME' => $first_name,
							'LNAME' => $last_name,
						), null, false
					)
				);

			}
			$download->register_download( $_POST['download-user-name'], $_POST['download-user-email'] );
			wp_redirect( FTM_FONTS_URL . '/' . $download->get_url() );
		} else {
			_e( 'Please enter a valid email address and name to download the files.', 'fontimator' );
		}
	}

	$current_user = wp_get_current_user();
	$user_default_name = implode( ' ', array_filter( [ $current_user->first_name, $current_user->last_name ] ) ); // Fancy way of doing $first . ' ' . $last

	?>

	<form action="#ftm-free-download-<?php echo $download_id; ?>" method="post">
		<h3><?php printf( _x( 'Download %s:', 'Free Download Title', 'fontimator' ), $download->get_name() ); ?></h3>
		<input class="your-name" type="text" placeholder="<?php echo esc_attr_x( 'Your name', 'Free Download Text Field', 'fontimator' ); ?>" value="<?php echo $user_default_name; ?>" required minlength="5" name="download-user-name">
		<input class="your-email" type="email" placeholder="<?php echo esc_attr_x( 'Your email', 'Free Download Text Field', 'fontimator' ); ?>" value="<?php echo $current_user->user_email; ?>" name="download-user-email">
		<input type="hidden" name="download-id" value="<?php echo $download_id; ?>">
		<label>
			<input type="checkbox" name="download-terms" required>
			<?php printf( __( 'I have read the %1$sfree fonts usage terms%2$s and I accept them.', 'fontimator' ), '<a href="' . Zipomator::get_eula_url( 'free' ) . '" target="_blank">', '</a>' ); ?>
		</label>
		<label>
			<input type="checkbox" name="download-newsletter">
			<?php _e( 'Subscribe me to the newsletter!', 'fontimator' ); ?>
		</label>
		<button type="submit" class="button"><?php _e( 'Download Free Font', 'fontimator' ); ?></button>
		<small>
			<?php _e( 'Your free license will be activated upon submitting your name and email address. Font usage without a license is a violation of copyright laws.', 'fontimator' ); ?>
		</small>
	</form>

</section><!--/#download-->
