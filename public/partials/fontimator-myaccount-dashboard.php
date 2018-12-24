<?php

/**
 * The Fontimator Dashboard
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/public/partials
 */


$customer_orders = wc_get_orders(
	array(
		'status' => 'completed',
		// 'type' => 'shop_order',
		'limit'  => -1,
		'customer_id' => get_current_user_id(),
	)
);

$user_fonts = array();
$user_weights = array();

foreach ( $customer_orders as $order ) {
	foreach ( $order->get_items() as $order_item ) {
		if ( $order_item->get_product()->get_type() === 'variation' ) {
			if ( ! in_array( $order_item->get_product_id(), $user_fonts ) ) {
				array_push( $user_fonts, $order_item->get_product_id() );
			}
			if ( ! in_array( $order_item->get_variation_id(), $user_weights ) ) {
				array_push( $user_weights, $order_item->get_variation_id() );
			}
		}
	}
}

$subscriptions = wcs_get_users_subscriptions();
foreach ( $subscriptions as $subscription ) {
	if ( 'active' !== $subscription->get_status() ) {
		continue;
	}
	// Get the start date, if set
	$subscription_renew_date = $subscription->get_date( 'next_payment' );
	break;
}



if ( count( $customer_orders ) > 0 || count( $subscriptions ) > 0 ) :
?>
<div class="fontimator-myaccount-dashboard">
	<div class="top">
		<section class="dashbox dashbox-half" id="dash-welcome">
			<h2 class="fontimator-timed-message-greeting" data-name="<strong><?php echo esc_attr( $current_user->first_name ); ?></strong>"><!-- Text here is generated automatically by the Fontimator --></h2>
			<p class="fontimator-timed-message-welcome"><!-- Text here is generated automatically by the Fontimator --></p>
			<p>
				<?php
					printf(
						__( 'From your account dashboard you can download your <a href="%1$s">font files</a>, view your <a href="%2$s">recent orders</a>, and <a href="%3$s">edit your password and account details</a>.', 'fontimator' ),
						esc_url( wc_get_endpoint_url( 'downloads' ) ),
						esc_url( wc_get_endpoint_url( 'orders' ) ),
						esc_url( wc_get_endpoint_url( 'edit-account' ) )
					);
				?>
			</p>
		</section>

		<section class="dashbox dashbox-quarter" id="dash-font-count">
			<h5><?php _e( "Fonts you've purchased:", 'fontimator' ); ?></h5>
			<?php if ( $subscription ) { ?>
				<figure>
					<div class="big">∞</div>
					<figcaption><?php _e( '(You have a membership)', 'fontimator' ); ?></figcaption>
				</figure>
			<?php } else { ?>
				<figure>
					<div class="big"><?php echo count( $user_fonts ); ?></div>
					<figcaption><?php printf( _n( '(%d weight total)', '(%d weights total)', count( $user_weights ), 'fontimator' ), count( $user_weights ) ); ?></figcaption>
				</figure>
			<?php } ?>
			<a class="more" href="<?php echo wc_get_endpoint_url( 'downloads' ); ?>"><?php _e( 'To all purchased licenses', 'fontimator' ); ?></a>
		</section>

		
		<section class="dashbox dashbox-quarter dashbox-colored" id="dash-membership">
			<h5><?php _ex( 'Your Membership', 'Dashboard membership dashbox title', 'fontimator' ); ?></h5>
			<figure>
				<div class="big"><i class="icon" data-icon="ø"></i></div>
				<figcaption>
					<?php
					if ( $subscription ) {
						printf( __( 'Will renew on %s', 'fontimator' ), wc_format_datetime( new WC_DateTime( $subscription_renew_date ) ) );
					} else {
						_e( "You don't have an active membership. :(", 'fontimator' );
					}
					?>
				</figcaption>
			</figure>
			<?php
			if ( $subscription ) {
				?>
				<a class="more" href="<?php echo $subscription->get_view_order_url(); ?>"><?php _e( 'To the subscription details', 'fontimator' ); ?></a>
				<?php
			} else {
				?>
				<a class="more" href="<?php echo home_url( 'membership' ); ?>"><?php _e( 'Learn more about memberships', 'fontimator' ); ?></a>
				<?php
			}
			?>
		</section>
	</div>

	<div class="tables">
		<section class="dashbox dashbox-half" id="dash-recent-purchses">
			<h5><?php _e( 'Recent Activity', 'fontimator' ); ?></h5>
			<h4><?php _e( 'Your Last Orders', 'fontimator' ); ?></h4>
			<table class="feat-tnum">
				<tr>
					<th><?php _ex( 'Order', 'Dashboard last orders table column name', 'fontimator' ); ?></th>
					<th><?php _ex( 'Date', 'Dashboard last orders table column name', 'fontimator' ); ?></th>
					<th><?php _ex( 'Total', 'Dashboard last orders table column name', 'fontimator' ); ?></th>
					<th><?php _ex( 'Status', 'Dashboard last orders table column name', 'fontimator' ); ?></th>
				</tr>
				<?php

				$last_customer_orders = wc_get_orders(
					array(
						// 'status' => 'completed',
						'type' => 'shop_order',
						// 'order' => 'DESC',
						'limit'  => 5,
						'customer_id' => get_current_user_id(),
					)
				);

				if ( count( $last_customer_orders ) ) {
					foreach ( $last_customer_orders as $order ) {
						?>
						<tr>
							<td><a href="<?php echo $order->get_view_order_url(); ?>">#<?php echo $order->get_order_number(); ?></a></td>
							<td><time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time></td>
							<td>
								<?php
								/* translators: 1: formatted order total 2: total order items */
								printf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $order->get_item_count(), 'fontimator' ), $order->get_formatted_order_total(), $order->get_item_count() );
								?>
							</td>
							<td><a href="<?php echo $order->get_view_order_url(); ?>"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></a></td>
						</tr>
						<?php
					}
				} else {
					?>
					<tr>
						<td colspan="4"><?php _e( 'You have not made any orders yet.', 'fontimator' ); ?></td>
					</tr>
					<?php
				}
				?>
			</table>
			<a class="more" href="<?php echo wc_get_endpoint_url( 'orders' ); ?>"><?php _e( 'To all your orders', 'fontimator' ); ?></a>
		</section>

		<section class="dashbox dashbox-half" id="dash-recent-versions">
			<h5><?php _e( 'Upgrade Fonts', 'fontimator' ); ?></h5>
			<h4><?php _e( 'Recently Updated Fonts', 'fontimator' ); ?></h4>
			<table class="feat-tnum">
				<tr>
					<th><?php _ex( 'Font', 'Dashboard upgrades table column name', 'fontimator' ); ?></th>
					<th><?php _ex( 'Version', 'Dashboard upgrades table column name', 'fontimator' ); ?></th>
					<th></th>
				</tr>

				<?php
				$last_updated_fonts = wc_get_products(
					array(
						'category' => 'nr',
					)
				);

				if ( count( $last_updated_fonts ) ) {
					foreach ( $last_updated_fonts as $font ) {
						?>
						<tr>
							<td><a href="<?php echo $font->get_permalink(); ?>"><?php echo $font->get_title(); ?></a></td>
							<td><?php the_field( 'font_version', $font->get_id() ); ?></td>
							<?php
							if ( in_array( $font->get_id(), $user_fonts ) ) {
								?>
								<td><a href="<?php echo wc_get_endpoint_url( 'downloads' ); ?>"><?php _ex( 'Download', 'Dashboard recently upgraded fonts action button', 'fontimator' ); ?></a></td>
								<?php
							} else {
								?>
								<td><a href="<?php echo $font->get_permalink(); ?>"><?php _ex( 'Get it now', 'Dashboard recently upgraded fonts action button', 'fontimator' ); ?></a></td>
								<?php
							}
							?>
						</tr>
						<?php
					}
				} else {
					?>
					<tr>
						<td colspan="4"><?php _e( 'There are no newly-updated fonts.', 'fontimator' ); ?></td>
					</tr>
					<?php
				}


				?>
			</table>
			<a class="more" href="<?php echo wc_get_endpoint_url( 'downloads' ); ?>"><?php _e( 'To all your downloads', 'fontimator' ); ?></a>
		</section>
	</div>
	<?php
	/*
	<div class="clearfix"></div>

	<a class="dashbox dashbox-half" id="dash-sale" href="">
		<h5 class="feat-smcp">SALE</h5>
		<h4>50% הנחה על פונט פעמון</h4>
		<p>מסתיים בעוד <span class="countdown">3 ימים | 23 שעות | 4 שניות</span></p>
	</a>

	<a class="dashbox dashbox-half" id="dash-sale-2" href="">
		<h5 class="feat-smcp">SALE</h5>
		<h4>50% הנחה על פונט פעמון</h4>
		<p>מסתיים בעוד <span class="countdown">3 ימים | 23 שעות | 4 שניות</span></p>
	</a>


	<section class="dashbox dashbox-half" id="dash-mishalist">
		<h5>משאליסט</h5>
		<h4>הפונטים שהוספת לרשימה שלך</h4>
		<ul>
			<li><a href="">אנומליה</a></li>
			<li><a href="">שלוק</a></li>
			<li><a href="">פעמון</a></li>
		</ul>
		<a class="more" href="">למשאליסט המלא שלך</a>
	</section>

	<section class="dashbox dashbox-half" id="dash-recent-posts">
		<h5>פונטים מומלצים</h5>
		<h4>פונטים שפשע שעדיין אין לך אותם</h4>
		<ul>
			<li><a href="">אנומליה</a></li>
			<li><a href="">שלוק</a></li>
			<li><a href="">פעמון</a></li>
		</ul>
	</section>

	*/
	?>
</div>
<?php
else :
	$link = sprintf( '<a href="%s" class="button wc-forward my-button">%s</a>', esc_url( wc_get_page_permalink( 'shop' ) ), esc_html__( 'Return to shop', 'fontimator' ) );
	wc_add_notice( __( 'Once you purchase some fonts, an awesome dashboard will be activated here.', 'fontimator' ) . $link, 'error' );
	wc_print_notices();
	get_template_part( 'template-parts/empty-dude' );

endif;

