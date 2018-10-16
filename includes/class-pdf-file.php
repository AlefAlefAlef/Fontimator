<?php

/**
 * PDFFile by Reuven Karasik
 */
class PDF_File {

	private $_filename, $_nice_filename, $_tmp_file, $_pdf;
	function __construct( $files, $nice_filename = false ) {
		$this->_nice_filename = $nice_filename . '.pdf';
		$this->create_pdf( $files );
	}

	/* creates a compressed pdf file */
	private function create_pdf( $files = array() ) {

		//vars
		$valid_files = array();
		//if files were passed in...
		if ( is_array( $files ) ) {
			//cycle through each file
			foreach ( $files as $file ) {
				//make sure the file exists
				if ( file_exists( $file ) ) {
					$valid_files[] = $file;
				}
			}
		}
		//if we have good files...
		if ( count( $valid_files ) ) {
			$this->_tmp_file = tempnam( WP_CONTENT_DIR . '/pdfs/', '' ) . '.pdf';

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/lib/PDFMerger/PDFMerger.php';

			$pdf = new PDFMerger; // or use $pdf = new \PDFMerger; for Laravel

			foreach ( $valid_files as $file ) {
				$pdf->addPDF( $file, 'all' );
			}

			$pdf->merge( 'file', $this->_tmp_file ); // generate the file
			$this->_pdf = $pdf;
			return true;
		} else {
			throw new Exception( __( 'There was an error generating the fonts catalog.', 'fontimator' ) );
		}
	}

	public function serve() {
		// $this->_pdf->merge( 'download', $this->_tmp_file ); // force download
		header( 'Content-type: application/pdf' );
		header( 'Content-Disposition: attachment; filename=' . $this->_nice_filename );
		header( 'Content-length: ' . filesize( $this->_tmp_file ) );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		readfile( $this->_tmp_file );
		unlink( $this->_tmp_file );
	}

}
