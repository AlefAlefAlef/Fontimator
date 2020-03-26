<?php
/**
 * The End User License Agreement generator.
 *
 * @since      2.0.0
 * @package    Zipomator
 * @subpackage Fontimator/Zipomator
 * @author     Reuven Karasik <rubik@karasik.org>
 */
class Zipomator_EULA {
	protected $template;
	protected $licenses;
	protected $licenses_htmls;
	protected $licenses_titles;

	public function __construct( $licenses ) {
		$this->licenses = $licenses;

		$post = get_page_by_path( 'eula' );
		$this->template = $post->post_content;
		$licenses_htmls = array();
		$licenses_titles = array();
		foreach ( get_field( 'list', $post->ID ) as $license ) {
			list($license_slug, $license_title) = explode( '|', $license['title'] );
			if ( ! $licenses || in_array( $license_slug, $licenses ) ) {
				$licenses_htmls[ $license_title ] = preg_replace( '/<\/?p>/i', '', $license['content'] );
				$licenses_titles[] = $license_title;
			}
		}

		$this->licenses_htmls = $licenses_htmls;
		$this->licenses_titles = implode( ', ', $licenses_titles );
	}

	public function html() {

		if ( $this->licenses ) {
			?>
			<h3>
				<?php
				// TRANSLATORS: The names of relevant licenses.
				//printf( esc_html__( 'End User License Agreement: %s', 'fontimator' ), $this->licenses_titles );
				echo $this->licenses_titles;
				?>
			</h3>
	<?php
		}
		// var_dump( $licenses );

		$relevant_licenses = '
<ol>
<li>
' . implode( '</li><li>', $this->licenses_htmls ) . '
</li>
</ol>
';

		$content = preg_replace( '/\[relevant-license\]/i', $relevant_licenses, $this->template );
		echo $content;
	}

	public function ploni_fontface() {
		$woff_file = file_get_contents( FTM_FONTS_PATH . 'ploni/ploni-regular-aaa.woff' );
		$woff_data = 'data:application/font-woff;charset=utf-8;base64,' . base64_encode( $woff_file );
		$woff2_file = file_get_contents( FTM_FONTS_PATH . 'ploni/ploni-regular-aaa.woff2' );
		$woff2_data = 'data:application/font-woff2;charset=utf-8;base64,' . base64_encode( $woff2_file );
		?>
		<style>
		

		@font-face {
			font-family: 'ploni';
			src: url(<?php echo $woff2_data; ?>) format('woff2'),
				url(<?php echo $woff_data; ?>) format('woff');
			font-weight: normal;
			font-style: normal;

		}

		</style>
		<?php
	}
	public function css() {
		$this->ploni_fontface();
		?>
		<style>

		html, body {
			text-align: right;
			direction: rtl;
			max-width: 800px;
			margin: 10px auto;
			font-family: 'ploni', sans-serif;
			font-size: 18px;
		}
		

		body > ol {
			padding-right: 1em;
			margin: 0;
		}

		h1 {
			font-weight: lighter;
			font-size: 90px;
			margin: 0;
			color: #e43;
			text-align: center;
		}
		
		h1 a, h1 a:hover, h1 a:active, h1 a:visited, h1 a:focus {
			line-height: 0.8;
			color: #e43;
			text-decoration: none;
		}

		ul, ol { margin-bottom: 1.1em; margin-right: 1em; list-style-type: none; margin: 0; }

		ul ul { margin-top: 0.4em; padding-right: 1em; }

		ol { counter-reset: section; }

		ol ol { margin-top: 0.4em; }

		ol.hebrew { list-style-type: hebrew; }

		ol li:before { content: counters(section, ".") " "; counter-increment: section; display: inline-block; text-indent: -1em; font-variant-numeric: tabular-nums; -moz-font-feature-settings: "tnum"; -webkit-font-feature-settings: "tnum"; font-feature-settings: "tnum"; }

		ol li li { margin-right: 0.5em; }

		ol li li:before { text-indent: -2.4em; font-size: 0.6em; vertical-align: middle; }

		ol li li li { margin-right: 1em; }

		ol li li li:before { text-indent: -3.3em; }

		li { margin-bottom: 0.5em; line-height: 1.5em; }
		</style>
		<?php
	}

	public function file() {
		?>
		<html>
			<head>
				<meta charset="UTF-8">
				<meta name="author" content="AlefAlefAlef">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>
					<?php
					// TRANSLATORS: The names of relevant licenses.
					printf( esc_html__( 'End User License Agreement: %s', 'fontimator' ), $this->licenses_titles );
					?>
				</title>
				<?php $this->css(); ?>
			</head>
			<body>
				<h1><a href="<?php echo home_url( 'eula' ); ?>" >ℵ</a></h1>
				<?php self::html( $relevant ); ?>
			</body>
		</html>

		<?php
	}


}

