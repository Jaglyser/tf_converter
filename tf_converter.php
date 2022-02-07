<?php

class TF_Converter {
	function __construct() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'tf convert', [ $this, 'read_files' ] );
		}
	}

	function read_files() {
		$base_dir = trailingslashit( get_template_directory() );
		$dir      = 'acf-json/';
		$files    = scandir( $base_dir . $dir );
		if ( empty( $files ) ) {
			return;
		}

		if ( ! is_dir( 'acf' ) ) {
			mkdir( $base_dir . '/inc/acf', 0777, true );
		}

		

		foreach ( $files as $file ) {
			if ( strpos( $file, '.' ) === 0 ) {
				continue;
			}

			if ( pathinfo( $file, PATHINFO_EXTENSION ) !== 'json' ) {
				continue;
			}

			// get json data and decode
			$json_data = file_get_contents( $base_dir . $dir . $file );
			$php_data  = json_decode( $json_data, true );

			// filepath to filename formatting
			$filename = str_replace( '.json', '', $file );

			// new file path and data
			$file_path     = $base_dir . "inc/acf/$filename.php";
			$new_file_data = '<?php return ' . $this->short_var_export( $php_data, true ) . ";\n";

			// create file and print associative array
			fopen( $file_path, 'w' );
			file_put_contents( $file_path, $new_file_data );
		}

	}

	// var_export but new formatting with brackets shortsynxat for modern look
	// added formatting for removing integer keys from parent objects
	function short_var_export( $expression, $return = false ) {
		$export   = var_export( $expression, true );
		$patterns = [
			'/array \(/'                       => '[',
			'/^([ ]*)\)(,?)$/m'                => '$1]$2',
			"/=>[ ]?\n[ ]+\[/"                 => '=> [',
			"/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
			'/[0-9]+ =>/'                      => '',
		];

		$export = preg_replace( array_keys( $patterns ), array_values( $patterns ), $export );
		if ( (bool) $return ) {
			return $export;
		} else {
			echo $export;
		}
	}
}
new TF_Converter;
