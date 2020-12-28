<?php
$on_sale_weights = Fontimator_Query::on_sale_weights();
?>
<section class="fontimator-sale-products">
	<?php
	if ( $on_sale_weights->have_posts() ) :
		foreach ( $on_sale_weights->posts as $sale_variation_id ) :
			$sale_variation = new Fontimator_Font_Variation( $sale_variation_id );

			$regular_price = $sale_variation->get_regular_price();
			$sale_price = $sale_variation->get_sale_price();
			if ( $sale_price ) {
				$saved_percentage = round( 100 - ( $sale_price / $regular_price * 100 ) ) . '%';
			}


			$sale_end = get_post_meta( $sale_variation_id, '_sale_price_dates_to', true );
			?>
			<span class="sale-product">
				<a href="<?php echo $sale_variation->get_permalink(); ?>">
					<?php
					printf(
						// translators: %1$s: The percentage saved including % sign, %2$s: The full variation name
						__( '%1$s discount on font %2$s', 'fontimator' ),
						'<strong>' . $saved_percentage . '</strong>',
						'<span>' . $sale_variation->get_name() . '</span>'
					);
					if ( $sale_end ) {
						$sale_end_date = date_i18n( 'j בF', $sale_end );
						$sale_end_text = printf( __( 'Only till %s', 'fontimator' ), $sale_end_date );
					} else {
						$text_array = Array(
							__( 'The sale that will not return', 'fontimator' ),
							__( 'Dont miss this one', 'fontimator' ),
							__( 'Fares so low, it’s scary', 'fontimator' ),
							__( 'Limited time, blow out sale', 'fontimator' ),
							__( 'Blast price!', 'fontimator' ),
							__( 'End of the season sale', 'fontimator' ),
							__( 'This sale is hot!', 'fontimator' ),
							__( 'Buy before it ends', 'fontimator' ),
							__( 'Available today only', 'fontimator' ),
						);
						$sale_end_text =  $text_array[array_rand($text_array)];
					}
					?>
					<date class="sale-end">
						<?php echo $sale_end_text; ?>
					</date>
				</a>
			</span>
		<?php endforeach; ?>
	<?php else : ?>
	
	<?php endif; ?>
</section>
