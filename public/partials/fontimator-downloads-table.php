<?php
/**
 * Order Downloads.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-downloads.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  Automattic
 * @package WooCommerce/Templates
 * @version 3.3.0
 */


// Copied by Reuven on 07/01/19 for the purpose of collapsing similar downloads
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$downloads = Fontimator_MyAccount::group_downloads_by_family( $downloads );

?>
<section class="woocommerce-order-downloads">
	<?php if ( isset( $show_title ) ) : ?>
		<h2 class="woocommerce-order-downloads__title"><?php esc_html_e( 'Downloads', 'woocommerce' ); ?></h2>
	<?php endif; ?>

	<table class="fontimator-table woocommerce-table woocommerce-table--order-downloads shop_table shop_table_responsive order_details">
		<thead>
			<tr>
				<?php foreach ( wc_get_account_downloads_columns() as $column_id => $column_name ) : ?>
				<th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<?php foreach ( $downloads as $family_name => $family_group ) : ?>
				<tbody>
					<tr class="font-family-header">
						<td class="icon-cell"></td>
						<td colspan="<?php echo count( wc_get_account_downloads_columns() ) - 1; ?>">
							<span class="font_title"><?php
							if ( 'membership' === $family_name ) {
								echo '<i class="icon" data-icon="ø"></i> ';
								_e( 'Membership License', 'fontimator' );
							} elseif ( 'academic' === $family_name ) {
								echo '<i class="icon" data-icon="Ÿ"></i> ';
								_e( 'Academic License', 'fontimator' );
							} elseif ( 'archive' === $family_name ) {
								echo '<i class="icon" data-icon="׳"></i> ';
								_e( 'Archive', 'fontimator' );
							} elseif ( 'gift' === $family_name ) {
								echo '<i class="icon" data-icon="‚"></i> ';
								_e( 'Gifts', 'fontimator' );
							} elseif ( 'free' === $family_name ) {
								echo '<i class="icon" data-icon="₪"></i> ';
								_e( 'Free Downloads', 'fontimator' );
							} elseif ( 'membership-reseller' === $family_name ) {
								echo '<i class="icon" data-icon="ø"></i> ';
								_e( 'Reseller Membership License', 'fontimator' );
							} else {
								$first_download = reset( $family_group );
								Fontimator_Public::print_with_font_preview( $first_download['product_name'], wp_get_post_parent_id( $first_download['product_id'] ) );
							}
							?></span>
						</td>
					</tr>
					<?php
					foreach ( (array) $family_group as $download ) {
						?>
						<tr>
							<?php foreach ( wc_get_account_downloads_columns() as $column_id => $column_name ) : ?>
								<td class="<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
									<?php
									if ( has_action( 'woocommerce_account_downloads_column_' . $column_id ) ) {
										do_action( 'woocommerce_account_downloads_column_' . $column_id, $download );
									} else {
										switch ( $column_id ) {
											case 'download-product': // Overriden by Fontimator_MyAccount::prepend_icon_to_download_name
												if ( $download['product_url'] ) {
													echo '<a href="' . esc_url( $download['product_url'] ) . '">' . esc_html( $download['product_name'] ) . '</a>';
												} else {
													echo esc_html( $download['product_name'] );
												}
												break;
											case 'download-file':
												echo '<a href="' . esc_url( $download['download_url'] ) . '" class="woocommerce-MyAccount-downloads-file button alt">' . esc_html( $download['download_name'] ) . '</a>';
												break;
										}
									}
									?>
								</td>
							<?php endforeach; ?>
						</tr>

						<?php
					}
					do_action( 'ftm_account_downloads_after_group', $family_name, $family_group );
					?>
				</tbody>
			
		<?php endforeach; ?>
	</table>
</section>
