<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Page for managing translations of posts
 *  
 */
function leginda_wp_posts()
{
    if ( ! current_user_can('manage_options') )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    global $dird;
    global $dir;

    // The Query
    $query1 = new WP_Query( array( 'post_status' => 'publish', 'posts_per_page' => -1 )  );
 
    ?>
    <div class="wrap">
    <h1>Posts</h1>

    <form method="post" action="">

    <?php leginda_wp_list_of_languages(); ?>

    <table class="wp-list-table widefat fixed striped posts">

        <thead>
        <tr>
            <td id="cb" class="manage-column column-cb check-column">
                <label class="screen-reader-text" for="cb-select-all-1"><?php echo __("Select All", 'leginda-wp'); ?></label>
                <input id="cb-select-all-1" type="checkbox">
            </td> 
            <td><?php echo __("Title", 'leginda-wp'); ?></td>
            <td><?php echo __("See", 'leginda-wp'); ?></td> 
            <td><?php echo __("Edit", 'leginda-wp'); ?></td>
            <td><?php echo __("Download", 'leginda-wp'); ?></td>
            <td><?php echo __("Word count", 'leginda-wp'); ?></td>
        </tr>
        </thead>

        <tbody id="the-list">
        <?php while( $query1->have_posts() ) : ?>
            <?php $query1->the_post(); ?>
            <?php $id = $query1->post->ID; ?>
            <tr>
                <th scope="row" class="check-column">           
                    <label class="screen-reader-text" for="cb-select-5"><?php echo __("Select", 'leginda-wp'); ?></label>
                    <input id="<?php echo $id; ?>" type="checkbox" name="post_<?php echo $id; ?>" value="<?php echo $id; ?>">
                    <div class="locked-indicator"></div>
                </th>
                <td><?php echo $query1->post->post_title; ?></td>
                <td>
                    <a href="<?php echo get_permalink($id); ?>" target="_blank">
                       <span class="dashicons dashicons-visibility"></span> <?php echo __("See", 'leginda-wp'); ?>
                    </a>
                </td>
                <td>
                    <a href="<?php echo get_edit_post_link($id); ?>">
                        <span class="dashicons dashicons-edit"></span> <?php echo __("Edit", 'leginda-wp'); ?>
                    </a>
                </td> 
                <td>
                    <a class="leginda_wp_down" id="<?php echo $id; ?>" href="<?php echo admin_url( 'admin.php?page=leginda_wp_posts' ); ?>&leginda_wp_submitted=<?php echo $id; ?>">
                        <span class='dashicons dashicons-arrow-down-alt'></span> <?php echo  __('Export and Download', 'leginda-wp'); ?>
                    </a>
                </td>
                <td>
                    <?php echo str_word_count($query1->post->post_content); ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>

    </table>

    <br>

    <input type="submit" name="leginda_wp_submit" id="submit" class="button button-primary" value="<?php echo  __('Export and Download', 'leginda-wp'); ?>">
    </form>

    <br>
    
    <h1><?php echo __("Import", 'leginda-wp'); ?></h1>
    <div id="dropzonewordpress">
        <form action="<?php echo admin_url( 'admin-ajax.php' ); ?>" class="dropzone needsclick dz-clickable" id="dropzone-wordpress-form">   
            <?php wp_nonce_field( 'protect_content', 'my_nonce_field' ); ?>                  
            <div class="dz-message needsclick">
                <?php echo  __('Drop files here or click to upload.', 'leginda-wp'); ?><br>             
                <span class="note needsclick">(<?php echo  __('Files are uploaded to', 'leginda-wp'); ?> uploads/yyyy/mm)</span>            
            </div>          
            <input type='hidden' name='action' value='submit_dropzonejs'>       
        </form>
    </div>

    <?php

    leginda_wp_link_script(); 
}

?>