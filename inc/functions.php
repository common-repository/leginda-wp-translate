<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Read and import the file
 *
 * Read the uploaded file and imports it into a post. 
 * If WPML is activ, it creates the post as a translation of the original post.
 * 
 * @arg string $file Path to uploaded file
 */
function leginda_wp_import_file( $file )
{
    $fp = fopen( $file, 'rb');
    $id = false;
    $title = false;
    $tolanguage = false;
    $content = '';
    $excerpt = '';
    $read_content = false;
    $read_excerpt = false;

    while ( ($line = fgets($fp)) !== false) 
    {
        $read = true;

        if( ! $id ){
            if( $id = leginda_wp_everything_in_tags($line, 'wt_post_id') ){
                $read = false;
            }
        }

        if( ! $id ){
            if( $id = leginda_wp_everything_in_tags($line, 'wt_page_id') ){
                $read = false;
            }
        }

        if( ! $title ){
            if( $title = leginda_wp_everything_in_tags($line, 'wt_title') ){
                $read = false;
            }
        }

        if( ! $tolanguage ){
            if( $tolanguage = leginda_wp_everything_in_tags($line, 'wt_to_language') ){
                $read = false;
            }
        }

        if( $read ){

            if( leginda_wp_in_tags($line, '<wt_content>') OR $read_content ){
                $read_content = true;

                if( leginda_wp_in_tags($line, '</wt_content>') ){
                    $content .= $line;
                    $read_content = false;
                }else{
                    $content .= $line;
                }
            }

            if( leginda_wp_in_tags($line, '<wt_post_excerpt>') OR $read_excerpt ){
                $read_excerpt = true;

                if( leginda_wp_in_tags($line, '</wt_post_excerpt>') ){
                    $excerpt .= $line;
                    $read_excerpt = false;
                }else{
                    $excerpt .= $line;
                }
            }
        }
    }

    if( ! $id ){
        return false;
    }
    
    if( ! $post = get_post($id) ){
        return false;
    }

    $element_type = 'post_' . $post->post_type;

    /**
     * WPML (look for the match)
     */
    if( $tolanguage AND function_exists('icl_object_id') )
    {
        global $wpdb;

        $table = $wpdb->prefix . 'icl_translations';

        /**
         * 1st Steep: 
         * Search for the translation group id (trid) 
         */
        if( $group = $wpdb->get_row("SELECT * FROM $table WHERE element_id='$id' AND element_type='$element_type'") ){

            // Group found
            $trid = $group->trid;
            $language_code = $group->language_code;
        }

        /**
         * 2nd Steep:
         * Search for the correspondance 
         */
        if( $original = $wpdb->get_row( "SELECT * FROM $table WHERE trid='$trid' and language_code='$tolanguage'" ) ){

            // Translation is already done
            $post = get_post( $original->element_id );
            
        }else{

            // Translation is not done. We have to create a new row. But first, create a post
            // Create new post
            $defaults = array(
                'post_author' => $post->post_author,
                'post_content' => $post->post_content,
                'post_content_filtered' => $post->post_content_filtered, // Was ist das??
                'post_title' => $post->post_title,
                'post_name' => sanitize_title($title),
                'post_excerpt' => $post->post_excerpt, // Das muss auch exportiert werden
                'post_status' => $post->post_status,
                'post_type' => $post->post_type,
                'comment_status' => $post->comment_status,
                'ping_status' => $post->ping_status,
                'post_password' => $post->post_password,
                'to_ping' =>  $post->to_ping,
                'pinged' => $post->pinged,
                'post_parent' => $post->post_parent,
                'menu_order' => $post->menu_order,
                'guid' => $post->guid,
                'import_id' => $post->import_id,
                'context' => $post->context,
            );   

            if( ! $new_id = wp_insert_post($defaults, true) ){
                return false;
            }

            $array = array(
                'element_id'            => $new_id,
                'element_type'          => $element_type,
                'trid'                  => $trid,
                'language_code'         => $tolanguage,
                'source_language_code'  => $language_code,
            );
            
            $wpdb->update($table, $array, array('element_id' => $new_id));

            $post = get_post($new_id);
        }
    }

    $content = str_replace('<content>', '', $content);
    $content = str_replace('</content>', '', $content);
    $excerpt = str_replace('<post_excerpt>', '', $excerpt);
    $excerpt = str_replace('</post_excerpt>', '', $excerpt);

    $defaults = array(
        'ID'    => $post->ID,
        'post_author' => $post->post_author,
        'post_content' => $content,
        'post_content_filtered' => $post->post_content_filtered, // Was ist das??
        'post_title' => $title,
        'post_excerpt' => $excerpt, // Das muss auch exportiert werden
        'post_status' => $post->post_status,
        'post_type' => $post->post_type,
        'comment_status' => $post->comment_status,
        'ping_status' => $post->ping_status,
        'post_password' => $post->post_password,
        'to_ping' =>  $post->to_ping,
        'pinged' => $post->pinged,
        'post_parent' => $post->post_parent,
        'menu_order' => $post->menu_order,
        'guid' => $post->guid,
        'import_id' => $post->import_id,
        'context' => $post->context,
    );

    $result = wp_insert_post($defaults, true);
    if( ! $result || is_wp_error($result) ){
        return false;
    }

    return false;
}


/**
 * Return the text between the given tags
 * 
 * @arg string $string Text where to look for
 * @arg string $tagname Tags to look for
 * @return string The found string
 */
function leginda_wp_everything_in_tags($string, $tagname)
{
    $pattern = "#<\s*?$tagname\b[^>]*>(.*?)</$tagname\b[^>]*>#s";
    preg_match($pattern, $string, $matches);
    return $matches[1];
}


/**
 * Check if a tag is in a string
 * 
 * @arg string $string Text where to look for
 * @arg string $tagname Tags to look for
 * @return boolean True if found. 
 */
function leginda_wp_in_tags($string, $tagname)
{
    if( strpos($string, $tagname) === false ){
        return false;
    }

    return true;
}


/**
 * Reads and export a post into a file
 * 
 * @arg integer $id ID of the post
 * @arg boolean $page True if it is a page
 * @arg string $lang Target language of the translation
 * @return string The name of the exported file
 */
function leginda_wp_export($id, $lang = false)
{
    global $dir;
    global $dir_posts;
    global $dird;

    $fromlang = ICL_LANGUAGE_CODE;

    $tag = 'post_id';
    $post = get_post($id);
    $name = "post-" . $post->ID . ".htm";
    $file = $dir_posts . "/" . $name;

    // Content
    $content = apply_filters('the_content', $post->post_content);

    $fp = fopen( $file, "w" );
    fwrite($fp, pack("CCC",0xef,0xbb,0xbf));
    fwrite($fp, "<wt_$tag>" . $post->ID . "</wt_$tag>\r\n");
    fwrite($fp, "<wt_from_language>" . $fromlang . "</wt_from_language>\r\n");
    fwrite($fp, "<wt_to_language>" . $lang . "</wt_to_language>\r\n");
    fwrite($fp, "<wt_title>" . $post->post_title . "</wt_title>\r\n");
    fwrite($fp, "<wt_content>" . $content . "</wt_content>\r\n");
    
    if( $post->post_excerpt ){
        fwrite($fp, "<wt_post_excerpt>" . $post->post_excerpt . "</wt_post_excerpt>\r\n");
    }

    fclose($fp);

    return $name;
}

function leginda_wp_export_widget($id, $lang = false)
{
    global $dir;
    global $dir_posts;
    global $dird;
    global $wpdb;

    if( $query1 = $wpdb->get_results( "SELECT * FROM wp_options where option_name = 'widget_text'") ){
        $query1 = $query1[0];
    }

    $query1 = unserialize($query1->option_value);

    $widget = $query1[$id];

    $fromlang = ICL_LANGUAGE_CODE;

    $name = "widget-" . $id . ".htm";
    $file = $dir_posts . "/" . $name;

    $fp = fopen( $file, "w" );
    fwrite($fp, pack("CCC",0xef,0xbb,0xbf));
    fwrite($fp, "<wt_widget_title>" . $id . "</wt_widget_title>\r\n");
    fwrite($fp, "<wt_from_language>" . $fromlang . "</wt_from_language>\r\n");
    fwrite($fp, "<wt_to_language>" . $lang . "</wt_to_language>\r\n");
    fwrite($fp, "<wt_title>" . $widget['title'] . "</wt_title>\r\n");
    fwrite($fp, "<wt_content>" . $widget['text'] . "</wt_content>\r\n");

    fclose($fp);

    return $name;
}


/**
 * Creates a zip file with the given files
 * 
 * @arg array $files Array of files to be added
 * @arg boolean $overwrite Overwrite files if set to true
 * @return boolean True if the files was succesfullz created
 */
function leginda_wp_create_zip( $files = array() ) 
{   
    global $dir;
    global $dir_posts;
    global $dird;

    $name = "export" . uniqid() . ".zip";

    $destination = $dir_posts . "/" . $name;

    //vars
    $valid_files = array();
    
    //if files were passed in...
    if( is_array($files) ) 
    {
        //cycle through each file
        foreach($files as $file) {
            //make sure the file exists
            if( file_exists( $dir_posts . "/" . $file ) ) {
                $valid_files[] = $file;
            }
        }
    }
    
    //if we have good files...
    if( count($valid_files) ) 
    {        
        // create the archive
        if( ! $zip = new ZipArchive() ){
            return false;
        }
        
        $res = $zip->open( $destination, ZIPARCHIVE::CREATE );
        if( $res !== true ) {
            return false;
        }
        
        //add the files
        foreach($valid_files as $file) {
            $zip->addFile($dir_posts . "/" . $file, $file);
        }
        
        //close the zip -- done!
        $zip->close();
        
        //check to make sure the file exists
        if( file_exists($destination) ){
            return $name;
        }
    }
    
    return false;
}


/**
 * Return a html select input with the list of available languages in WPML
 * 
 * @return (boolean| print html) Print the html if WPML is activ, false if not. 
 */
function leginda_wp_list_of_languages()
{
    if( ! function_exists('icl_object_id') ){
        return false;
    }

    $languages = icl_get_languages('skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str');

    echo "<label for='name'>" . __('Translate to: ', 'leginda_wp') . "</label>";
    echo "<select id='leginda_wp_lang_select' name='language'><option></option>";
    foreach($languages as $key => $lang){
        echo "<option value='$key'>" . $lang['native_name'] . "</option>";
    }       
    echo "</select>";
}

/**
 * Print the script to change the targe translation
 * 
 * As the user chooses another language, the script changes the corresponding links.
 */
function leginda_wp_link_script()
{
    global $dird;

    ?>
    <script type="text/javascript">
    jQuery(document).ready(function( $ ) {
        
        function change_language()
        {
            var _href = "<?php echo admin_url( 'admin.php?page=leginda_wp_posts' ); ?>&leginda_wp_submitted=";
            var lang = $('#leginda_wp_lang_select').val();
            $("a.leginda_wp_down").each(function() {
               var $this = $(this);       
               var aid = $this.attr("id"); 
               $this.attr("href", _href + aid + '&toleng=' + lang);
            });
        }

        //change_language();

        $('#leginda_wp_lang_select').change(function(){
            change_language();
        });

    });
    </script>
    <?php
}

/**
 * Export the selected posts into a .zip file and force the download of it
 * 
 */
function leginda_wp_export_zip()
{
    if( ! isset($_POST['leginda_wp_submit']) ){
        return false;
    }

    global $dir;
    global $dir_posts;
    global $dird;

    $page = false;
    if( isset($_GET['page']) && $_GET['page'] == true ){
        $page = true;
    }

    $lang = '';
    if( isset($_POST['language']) ){
        $lang = sanitize_text_field($_POST['language']);
    }

    $$files_to_zip = array();
    foreach( $_POST as $key => $submitted ){
        if( strpos($key, 'post_') !== false ){
            if( is_numeric($submitted) ){
                $files_to_zip[] = leginda_wp_export($submitted, $lang);
            }
        }
    }

    $name = leginda_wp_create_zip( $files_to_zip );

    if(  $name !== false ){

        ob_clean();
        ob_end_flush();
        
        $file = $dir_posts . "/". $name;

        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header('Content-Type: application/zip;\n');
        header("Content-Transfer-Encoding: Binary");
        header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
        readfile($file);
        
        // Delete all the exported files
        foreach (glob($dir_posts ."/*.*") as $filename) {
            if (is_file($filename)) {
                unlink($filename);
            }
        }

        exit();
    } 
}

/**
 * Export a single post into a .htm file and force the download of it
 * 
 */
function leginda_wp_export_single_file()
{
    if( ! isset($_GET['leginda_wp_submitted']) ){
        return false;
    }

    if( ! is_numeric($_GET['leginda_wp_submitted']) ){
        return false;
    }

    global $dir;
    global $dir_posts;
    global $dird;

    $submitted = sanitize_text_field($_GET['leginda_wp_submitted']);

    $file = $dir_posts . "/post-" . $submitted . ".htm" ;

    $lang = '';
    if( isset($_GET['toleng']) ){
        $lang = sanitize_text_field($_GET['toleng']);
    }

    if( leginda_wp_export($submitted, $lang) !== false ){

        header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
        header("Content-Length: " . filesize($file));
        header("Content-Type: application/octet-stream;");

        readfile($file);
    
        exit();
    }
}

/**
 * Export a single widget a .htm file and force the download of it
 * 
 */
function leginda_wp_export_single_widget()
{
    if( ! isset($_GET['leginda_wp_submitted_widget']) ){
        return false;
    }

    if( ! is_numeric($_GET['leginda_wp_submitted_widget']) ){
        return false;
    }

    global $dir;
    global $dir_posts;
    global $dird;

    $submitted = sanitize_text_field($_GET['leginda_wp_submitted_widget']);

    $file = $dir_posts . "/widget-" . $submitted . ".htm" ;

    $lang = '';
    if( isset($_GET['toleng']) ){
        $lang = sanitize_text_field($_GET['toleng']);
    }

    if( leginda_wp_export_widget($submitted, $lang) !== false ){

        header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
        header("Content-Length: " . filesize($file));
        header("Content-Type: application/octet-stream;");

        readfile($file);

        exit();
    }
}

/**
 * Export the selected posts into a .zip file and force the download of it
 * 
 */
function leginda_wp_export_widget_zip()
{
    if( ! isset($_POST['leginda_wp_submit_widget']) ){
        return false;
    }

    global $dir;
    global $dir_posts;
    global $dird;

    $lang = '';
    if( isset($_POST['language']) ){
        $lang = sanitize_text_field($_POST['language']);
    }

    $$files_to_zip = array();
    foreach( $_POST as $key => $submitted ){
        if( strpos($key, 'widget_') !== false ){
            if( is_numeric($submitted) ){
                $files_to_zip[] = leginda_wp_export_widget($submitted, $lang);
            }
        }
    }

    $name = leginda_wp_create_zip( $files_to_zip );

    if(  $name !== false )
    {
        ob_clean();
        ob_end_flush();
        
        $file = $dir_posts . "/". $name;

        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
        
        readfile($file);
        
        // Delete all the exported files
        foreach (glob($dir_posts ."/*.*") as $filename) {
            if (is_file($filename)) {
                //unlink($filename);
            }
        }

        exit();
    } 
}

function leginda_wp_export_taxonomies()
{
    if( ! isset($_POST['leginda_wp_submit_taxonomies']) ){
        return false;
    }

    $categories = get_categories();

    global $dir;
    global $dir_posts;
    global $dird;

    $name = "taxonomies.csv";
    $file = $dir_posts . "/" . $name;

    $fp = fopen( $file, "w" );

    foreach( $categories as $key => $cat ){
        $array = array($cat->term_id, $cat->name);
        fputcsv($fp, $array);
    }

    fclose($fp);
    
    ob_clean();
    ob_end_flush();

    // output headers so that the file is downloaded rather than displayed
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");

    readfile($file);
        
    exit();
}

// Check if Woocommerce is activated
if ( ! function_exists( 'is_woocommerce_activated' ) ) {
    function is_woocommerce_activated() {
        if ( class_exists( 'woocommerce' ) ) { return true; } else { return false; }
    }
}