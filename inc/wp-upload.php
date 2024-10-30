<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'wp_ajax_nopriv_submit_dropzonejs', 'leginda_wp_dropzonejs_upload' ); //allow on front-end
add_action( 'wp_ajax_submit_dropzonejs', 'leginda_wp_dropzonejs_upload' );

/**
 * Handles the AJAX request when uploading files and imports them with leginda_wp_import_file()
 * 
 */
function leginda_wp_dropzonejs_upload() 
{
    global $dird;
    global $dir;

    $dir = $dir . "posts";
    $dird = $dird . "/posts/";

    if ( ! empty( $_FILES ) && wp_verify_nonce( $_REQUEST['my_nonce_field'], 'protect_content' ) ) {

        $uploaded_bits = wp_upload_bits(
            $_FILES['file']['name'],
            null, //deprecated
            file_get_contents( $_FILES['file']['tmp_name'] )
        );

        if ( false !== $uploaded_bits['error'] ) {

            $error = $uploaded_bits['error'];
            
            return add_action( 'admin_notices', function() use ( $error ) {
                $msg[] = '<div class="error"><p>';
                $msg[] = '<strong>DropzoneJS & WordPress</strong>: ';
                $msg[] = sprintf( __( 'wp_upload_bits failed,  error: "<strong>%s</strong>' ), $error );
                $msg[] = '</p></div>';
                echo implode( PHP_EOL, $msg );
            } );
        }

        $uploaded_file     = $uploaded_bits['file'];
        $uploaded_url      = $uploaded_bits['url'];
        $uploaded_filetype = wp_check_filetype( basename( $uploaded_bits['file'] ), null );

        if( strpos($_FILES['file']['name'], '.zip') !== false )
        {
            $zip = new ZipArchive;
            $res = $zip->open( $uploaded_bits['file'] );
            if( $res === TRUE ){

                $files = array();
                for($i = 0; $i < $zip->numFiles; $i++) { 
                    $files[] = $zip->getNameIndex($i); 
                } 
                $zip->extractTo( $dir );
                $zip->close(); 
            }

            if( $files ){
                foreach( $files as $key => $file ){
                    echo "error";
                }
            }

        }
        else
        {
            if( ! leginda_wp_import_file( $uploaded_bits['url'] ) ){
                echo "error";
            }
        }
    }
    die();
}

?>