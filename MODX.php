<?php
//https://wfoojjaec.eu.org/
    if( is_file( $filename = __DIR__ . '/core/docs/version.inc.php' ) ) {
        $version = require_once( $filename );
        $version = $version[ 'version' ] . '.' . $version[ 'major_version' ] . '.' . $version[ 'minor_version' ];
    }
    if( is_dir( __DIR__ . '/setup' ) )
        header( 'Location: /setup/' );
    else {
        if( $ch = curl_init( 'https://modx.com/download' ) ) {
            curl_setopt_array( $ch, [
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT => 30
            ] );
            preg_match_all( '/Current Version:\s+([0-9]+\.[0-9]+\.[0-9]+)/i', curl_exec( $ch ), $matches );
            if( count( $matches[ 1 ] ) === 1 ) {
                if( isset( $version ) && $matches[ 1 ][ 0 ] === $version ) {
                    if( is_file( $filename = __DIR__ . '/core/config/config.inc.php' ) ) {
                        $data = file_get_contents( $filename );
                        /*
                        $data = preg_replace( '/\$database_type = .*;/', '$database_type = \'mysql\';', $data );
                        $data = preg_replace( '/\$database_server = .*;/', '$database_server = \'localhost\';', $data );
                        $data = preg_replace( '/\$database_user = .*;/', '$database_user = \'wfoojjaec\';', $data );
                        $data = preg_replace( '/\$database_connection_charset = .*;/', '$database_connection_charset = \'utf8mb4\';', $data );
                        $data = preg_replace( '/\$dbase = .*;/', '$dbase = \'wfoojjaec\';', $data );
                        $data = preg_replace( '/\$table_prefix = .*;/', '$table_prefix = \'modx_\';', $data );
                        $data = preg_replace( '/\$database_dsn = .*;/', '$database_dsn = \'mysql:host=localhost;dbname=wfoojjaec;charset=utf8mb4\';', $data );
                        */
                        $data = preg_replace( '/\$modx_core_path= .*;/', '$modx_core_path= \'' . __DIR__ . '/core/\';', $data );
                        $data = preg_replace( '/\$modx_processors_path= .*;/', '$modx_processors_path= \'' . __DIR__ . '/core/model/modx/processors/\';', $data );
                        $data = preg_replace( '/\$modx_connectors_path= .*;/', '$modx_connectors_path= \'' . __DIR__ . '/connectors/\';', $data );
                        $data = preg_replace( '/\$modx_manager_path= .*;/', '$modx_manager_path= \'' . __DIR__ . '/manager/\';', $data );
                        $data = preg_replace( '/\$modx_base_path= .*;/', '$modx_base_path= \'' . __DIR__ . '/\';', $data );
                        $data = preg_replace( '/\$modx_assets_path= .*;/', '$modx_assets_path= \'' . __DIR__ . '/assets/\';', $data );
                        file_put_contents( $filename, $data );
                    } else
                        echo 'is_file ' . $filename;
                    foreach( [
                        __DIR__ . '/config.core.php',
                        __DIR__ . '/connectors/config.core.php',
                        __DIR__ . '/manager/config.core.php'
                    ] as $filename )
                        if( is_file( $filename ) )
                            file_put_contents( $filename, preg_replace( '/define\(\'MODX_CORE_PATH\', .*\);/', 'define(\'MODX_CORE_PATH\', \'' . __DIR__ . '/core/\');', file_get_contents( $filename ) ) );
                        else
                            echo 'is_file ' . $filename;
                } elseif( $fp = fopen( $filename = 'modx-' . $matches[ 1 ][ 0 ] . '-pl-advanced.zip', 'w+' ) ) {
                    curl_setopt_array( $ch, [
                        CURLOPT_FILE => $fp,
                        //CURLOPT_URL => 'https://modx.com/download/direct?id=' . $filename . '&0=abs'
                        CURLOPT_URL => 'https://modx.s3.amazonaws.com/releases/' . $matches[ 1 ][ 0 ] . '/' . $filename
                    ] );
                    curl_exec( $ch );
                    fclose( $fp );
                    if( curl_errno( $ch ) )
                        echo 'curl_error ' . curl_error( $ch );
                    else {
                        if( in_array( $code = curl_getinfo( $ch, CURLINFO_HTTP_CODE ), [ 200, 301, 302, 303, 304, 307, 308 ] ) ) {
                            if( is_dir( $dir = __DIR__ . '/modx-' . $matches[ 1 ][ 0 ] . '-pl' ) )
                                echo 'is_dir ' . $dir;
                            else {
                                if( mkdir( $dir ) ) {
                                    if( $ZipArchive = new ZipArchive ) {
                                        if( $ZipArchive->open( $filename ) === TRUE ) {
                                            if( is_dir( __DIR__ . '/core' ) )
                                                rename( __DIR__ . '/core', $dir . '/core' );
                                            $ZipArchive->extractTo( __DIR__ );
                                            $ZipArchive->close();
                                            rename( $dir . '/core', __DIR__ . '/core' );
                                            rename( $dir . '/setup', __DIR__ . '/setup' );
                                            header( 'Location: /setup/' );
                                        } else
                                            echo 'ZipArchive open ' . $filename;
                                    } else
                                        echo 'ZipArchive';
                                    rmdir( $dir );
                                } else
                                    echo 'mkdir ' . $dir;
                            }
                        } else
                            echo 'HTTP ' . $code;
                    }
                    unlink( $filename );
                } else
                    echo 'fopen ' . $filename;
            } else
                echo 'version';
            curl_close( $ch );
        } else
            echo 'curl_init';
    }
    unlink( __FILE__ );
