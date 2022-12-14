<?php

function kite_retina_upload_filter( $metadata ) {

	// Check for errors
	if ( ! count( $metadata ) ||
	   ! array_key_exists( 'file', $metadata ) ||
	   ! array_key_exists( 'sizes', $metadata ) ) {
		return $metadata;
	}

	$upload  = wp_upload_dir();
	$relPath = dirname( $metadata['file'] );
	$path    = kite_path_combine( $upload['basedir'], $relPath );
	$sizes   = $metadata['sizes'];

	// remove duplicate values from a $sizes array ( cause Error when Upload Images )
	$sizes = array_map( 'unserialize', array_unique( array_map( 'serialize', $sizes ) ) );

	// Check for @2x extension
	foreach ( $sizes as $key => $size ) {
		if ( ! kite_ends_with( $key, '@2x' ) ) {
			continue;
		}

		// key without @2x
		$id = substr( $key, 0, strlen( $key ) - 3 );

		// Check for normal resolution key
		if ( ! array_key_exists( $id, $sizes ) ) {
			continue;
		}

		// Change the file name to match the LoDPI one
		// Except we add @2x to the filename
		$file     = $sizes[ $id ]['file'];
		$ext      = pathinfo( $file, PATHINFO_EXTENSION );
		$fileName = basename( $file, ".$ext" );
		$newName  = "$fileName@2x.$ext";
		// Rename the file
		rename( kite_path_combine( $path, $sizes[ $key ]['file'] ), kite_path_combine( $path, $newName ) );

		// Save the new name
		$metadata['sizes'][ $key ]['file'] = $newName;
	}

	return $metadata;
}

add_filter( 'wp_generate_attachment_metadata', 'kite_retina_upload_filter' );
