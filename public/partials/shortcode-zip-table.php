<section class="archive-wrap archive-wrap-files">
	<div class="center-float">
		<section class="content">
		<style>
			table.fontimator-zip-table tbody:not(:hover) tr:not(:first-of-type) {
				display: none;
			}

			table.fontimator-zip-table {
				width: 100%;
			}
			table.fontimator-zip-table td, table.fontimator-zip-table th {
				border: 1px solid black;
				padding: 5px;
				margin: 2px;
				font-size: 22px;
			}
		</style>
		<?php
		if ( current_user_can( 'editor' ) || current_user_can( 'administrator' ) || current_user_can( 'manage_woocommerce' ) ) :
			$weight_taxonomy = 'pa_' . FTM_WEIGHT_ATTRIBUTE;
			$license_taxonomy = 'pa_' . FTM_LICENSE_ATTRIBUTE;

			$licenses = get_terms( array(
				'taxonomy' => $license_taxonomy,
			) );

			$fonts = wc_get_products(array(
				'type' => 'variable',
				'paginate' => false,
				'limit' => -1,
			));
			$memberships = wc_get_products(array(
				'type' => 'variable-subscription',
				'paginate' => false,
				'limit' => -1,
			));

			$fonts = array_merge( $fonts, $memberships );

			?>
			<table class="fontimator-zip-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Font Name × Licenses', 'fontimator' ); ?></th>
						<?php
						foreach ( $licenses as $license ) {
							?>
							<th><?php echo $license->slug; ?></th>
							<?php
						}
						?>
					</tr>
				</thead>
				<?php
				foreach ( $fonts as $font ) {
					$font_attributes = $font->get_attributes();
					if ( $font_attributes[ $weight_taxonomy ] ) {
						$font_weights = $font_attributes[ $weight_taxonomy ]->get_terms();
						usort( $font_weights, function( $a, $b ) {
							return strcmp( $a->slug, $b->slug );
						} );
					} else {
						$font_weights = array();
					}
					$font_variations = $font->get_children();
					$font_downloads = array();

					foreach ( $font_variations as $font_variation_id ) {
						$font_variation_attributes = wc_get_product_variation_attributes( $font_variation_id );
						$font_variation_weight = $font_variation_attributes[ 'attribute_' . $weight_taxonomy ];
						$font_variation_license = $font_variation_attributes[ 'attribute_' . $license_taxonomy ];

						if ( ! isset( $font_downloads[ $font_variation_weight ] ) ) {
							$font_downloads[ $font_variation_weight ] = array();
						}

						$font_downloads[ $font_variation_weight ][ $font_variation_license ] = $font_variation_id;
					}
					?>
					<tbody>
						<tr>
							<td><?php echo $font->get_title(); ?></td>
							<?php
							foreach ( $licenses as $license ) {
								if ( 'variable-subscription' === $font->get_type() ) {
								?>
								<td>
									<a href="<?php echo Zipomator::get_bundle_url( 'membership', $license->slug ); ?>">
										<?php echo $license->slug; ?>
									</a>
								</td>
								<?php
								} else {
								?>
								<th><?php echo $license->slug; ?></th>
								<?php
								}
							}
							?>
						</tr>
						<?php
						foreach ( $font_weights as $weight ) {
							?>
							<tr>
								<td>
									<?php
									/* TRANSLATORS: %s: Weight term name. */
									echo sprintf( _x( 'Weight: %s', 'fontimator' ), $weight->name );
									?>
								</td>

								<?php
								foreach ( $licenses as $license ) {
									?>
									<td>
										<?php
										$cell_variation_id = $font_downloads[ $weight->slug ][ $license->slug ];
										if ( $cell_variation_id ) {
											$clean_weight = Zipomator::get_clean_weight( $weight->slug );
											?>
											<a href="<?php echo Zipomator::get_bundle_url( $font->get_slug(), $clean_weight, $license->slug ); ?>">
												<?php echo $cell_variation_id; ?>
											</a>
											<?php
										}
										?>
									</td>
									<?php
								}
								?>
							</tr>
							<?php
						}
						?>
					</tbody>
					<?php
				}
				?>
			</table>
		<?php
		else :
			echo 'Nothing to see here...';
		endif;
		?>





		</section><!-- /.content -->
	</div><!-- /.center-float -->
</section><!-- /.archive-wrap -->
