<section class="archive-wrap archive-wrap-files">
	<section class="content">
	<style>
		table.fontimator-zip-table tbody:not(:hover) tr:not(:first-of-type) {
			//display: none;
		}

		table.fontimator-zip-table {
			width: 100%;
		}
		table.fontimator-zip-table td, table.fontimator-zip-table th {
			border: 1px solid black;
			padding: 0 5px;
			margin: 2px;
			font-size: 0.9em;
		}
		table.fontimator-zip-table td.title {
			background: #fffb8d;
		}
		table.fontimator-zip-table th {
			font-size: 0.7em;
			text-align: right;
			font-weight: 700;
			text-transform: uppercase;
			background: #fffb8d;
		}
		table.fontimator-zip-table tr:hover {
			background: white;
		}
		table.fontimator-zip-table h3{
			margin: 0;
			font-size: 1em;
		}
		table.fontimator-zip-table h3 a{
		    //color: #e43;
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
						<td class="title"><h3><a href="<?php echo get_permalink( $font->get_id() ); ?>"><?php echo $font->get_title(); ?></a> <?php edit_post_link( '<i class="icon" data-icon="r"></i>', '', '', $font->get_id() ); ?></h3></td>
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
							<td class="feat-tnum">
								<?php
								/* TRANSLATORS: %s: Weight term name. */
								//echo sprintf( __( 'Weight: %s', 'fontimator' ), $weight->name );
								echo ' &emsp; '.$weight->name;
								?>
							</td>

							<?php
							foreach ( $licenses as $license ) {
								?>
								<td>
									<?php
									$cell_variation_id = $font_downloads[ $weight->slug ][ $license->slug ];
									if ( $cell_variation_id ) {
										?>
										<a href="<?php echo Zipomator::get_nonced_url( $cell_variation_id ); ?>">
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
	else : ?>
		<div class="center-float">
			<?php wp_login_form(); ?>
		</div>
	<?php endif; ?>





	</section><!-- /.content -->
</section><!-- /.archive-wrap -->
