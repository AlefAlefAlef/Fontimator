<?php

/**
 * Zip_File by Reuven Karasik
 */
class Zip_File {

	private $_filename, $_nice_filename, $_tmp_file;
	function __construct( $files, $nice_filename = false ) {
		$this->_nice_filename = $nice_filename . '.zip';
		if ( $this->create_zip( $files ) ) {
			return true;
		} else {
			echo 'There was en error creating the ZIP file.';
			return false;
		};
	}

	/* creates a compressed zip file */
	private function create_zip( $files = array() ) {

		//vars
		$valid_files = array();
		//if files were passed in...
		if ( is_array( $files ) ) {
			//cycle through each file
			foreach ( $files as $file ) {
				//make sure the file exists
				if ( file_exists( $file[0] ) || 3 === count( $file ) ) {
					$valid_files[] = $file;
				}
			}
		}
		//if we have good files...
		if ( count( $valid_files ) ) {
			//create the archive
			$zip = new ZipArchive;
			$this->_tmp_file = tempnam( WP_CONTENT_DIR . '/zips/', '' );
			$zip_open = $zip->open( $this->_tmp_file, ZIPARCHIVE::CREATE );
			if ( true !== $zip_open ) {
				return false;
			}
			//add the files
			foreach ( $valid_files as $file ) {
				if ( file_exists( $file[0] ) && is_file( $file[0] ) ) {
					$zip->addFile( $file[0], $file[1] );
				}
				if ( false === $file[0] ) {
					$zip->addFromString( $file[2], $file[1] );
				}
			}
			//debug
			// echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
			// die();

			//close the zip -- done!
			$zip->close();

			return 0 === $zip->status;
		} else {
			return false;
		}
	}

	public function serve() {
		header( 'Content-type: application/zip' );
		header( 'Content-Disposition: attachment; filename=' . $this->_nice_filename );
		header( 'Content-length: ' . filesize( $this->_tmp_file ) );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		readfile( $this->_tmp_file );
		unlink( $this->_tmp_file );
	}

}
