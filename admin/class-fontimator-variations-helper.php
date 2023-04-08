<?php

/**
 * The WooCommerce variation Helper
 *
 * @link       https://alefalefalef.co.il
 * @since      2.0.0
 *
 * @package    Fontimator
 * @subpackage Fontimator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fontimator
 * @subpackage Fontimator/admin
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Fontimator_Variations_Helper extends Fontimator_Admin {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		parent::__construct( 'fontimator-variations-helper', $version );
		// Override Woocommerce Max Linked Variations
		define( 'WC_MAX_LINKED_VARIATIONS', 100 );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    2.0.0
	 */
	public $version;
	public function enqueue_scripts() {
		if ( 'product' === get_current_screen()->id ) {
			wp_enqueue_script(
				'fontimator-variations-helper-toolbar',
				plugin_dir_url( __FILE__ ) . 'js/fontimator-variations-helper-toolbar.js',
				array( 'jquery' ),
				$this->version,
				true // is_footer true here, to make sure the tab selection works
			);
		}

	}

	public function variable_product_toolbar() {
		?>
		<div class="toolbar fontimator-toolbar">
			<strong style="line-height: 27px; margin-left: 10px;"><?php esc_html_e( 'Fontimator Tools:', 'fontimator' ); ?></strong>
			<a id="fontimator_setup_variations" href="" class="button button-primary"><?php esc_html_e( 'Set-up all variations', 'fontimator' ); ?></a>
		</div>
		<?php
	}

	public function product_options_update_attributes_notice() {
		?>
		<div>
			<strong style="line-height: 27px; margin-left: 10px;"><?php esc_html_e( 'After saving the attributes, DO NOT update product. Instead, refresh page or exit.', 'fontimator' ); ?></strong>
		</div>
		<?php
	}


	public function ajax_setup_variations() {
		$post_id = $_REQUEST['post_id'];
		$font = new Fontimator_Font( $post_id );
		header( 'Content-type: application/json' );
		if ( $font->get_id() ) {
			$amount = $font->setup_variations();
			echo json_encode( array(
				'result' => 'success',
				'amount' => $amount,
			) );
		} else {
			echo json_encode( array(
				'result' => 'error',
			) );
		}
		die();
	}

	public function http_setup_variations() {
		if ( isset( $_GET['fontimator_setup_variations'] ) ) {
			$post_id = $_REQUEST['post_id'];
			$font = new Fontimator_Font( $post_id );

			if ( $font->get_id() ) {
				$amount = $font->setup_variations();
				$font_name = $font->get_title();

				add_action(
					'admin_notices', function() use ( &$font_name, &$amount ) {
						?>
						<div class="notice notice-success is-dismissible">
							<?php // translators: %1$d: variations changed, %2$s: Font name ?>
							<p><strong><?php echo sprintf( __( 'Fontimator set-up %1$d variations for font %2$s', 'fontimator' ), $amount, $font_name ); ?></strong></p>
						</div>
						<?php
					}
				);

			} else {

				add_action(
					'admin_notices', function() use ( &$post_id ) {
						?>
						<div class="notice notice-error is-dismissible">
							<?php // translators: ID parameter ?>
							<p><strong><?php echo sprintf( __( 'Fontimator could not find font with ID %s', 'fontimator' ), $post_id ); ?></strong></p>
						</div>
						<?php
					}
				);

			}
		}
	}

	public function http_generate_variations() {
		if ( isset( $_GET['fontimator_generate_variations'] ) ) {
			$post_id = $_GET['post_id'];

			$font = new Fontimator_Font( $post_id );

			if ( $font->get_id() ) {
				$amount = $font->generate_variations();
				$font_name = $font->get_title();

				add_action(
					'admin_notices', function() use ( &$amount, &$font_name ) {
					?>
				<div class="notice notice-success is-dismissible">
					<?php // translators: Post name ?>
					<p><strong><?php echo sprintf( __( 'Fontimator created %1$d variations for font %2$s', 'fontimator' ), $amount, $font_name ); ?></strong></p>
				</div>
				<?php
					}
				);

			} else {
				add_action(
					'admin_notices', function() use ( &$post_id ) {
						?>
						<div class="notice notice-error is-dismissible">
							<?php // translators: ID parameter ?>
							<p><strong><?php echo sprintf( __( 'Fontimator could not find font with ID %s', 'fontimator' ), $post_id ); ?></strong></p>
						</div>
						<?php
					}
				);
			}
		}
	}

	public function add_product_row_actions( $actions, $post ) {
		if ( 'product' == $post->post_type ) {
			$actions = array_merge(
				$actions, array(
					'fontimator_setup_variations' => sprintf(
						'<a href="%s">Fontimator: Set-up all Variations</a>',
						sprintf( 'edit.php?post_type=product&fontimator_setup_variations=true&post_id=%d', $post->ID )
					),
					'fontimator_generate_all_variations' => sprintf(
						'<a href="%s">Fontimator: Create all Variations</a>',
						sprintf( 'edit.php?post_type=product&fontimator_generate_variations=true&post_id=%d', $post->ID )
					),
				)
			);
			return $actions;
		}
		return $actions;
	}

	public function print_ignore_checkbox( $loop, $variation_data, $variation ) {
		?>
		<label class="tips" data-tip="<?php esc_html_e( "Enable this option if this variation has custom price/download file which you don't want to be overriden by the Fontimator", 'fontimator' ); ?>">
			<?php esc_html_e( 'Fontimator: Ignore me!', 'fontimator' ); ?>
			<input type="checkbox" class="checkbox variable_fontimator_ignore" name="_fontimator_ignore[<?php echo esc_attr( $variation->ID ); ?>]" <?php checked( isset( $variation_data['_fontimator_ignore'] ) && is_array( $variation_data['_fontimator_ignore'] ) ? reset( $variation_data['_fontimator_ignore'] ) : null, 'yes', true ); ?> />
		</label>
		<?php
	}

	public function save_ignore_checkbox_value( $post_id ) {
		$fontimator_ignore = isset( $_POST['_fontimator_ignore'][ $post_id ] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fontimator_ignore', $fontimator_ignore );
	}

}
