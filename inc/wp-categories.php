<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Page for managing translations of posts
 *  
 */
function leginda_wp_categories()
{
    if ( ! current_user_can('manage_options') )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    global $dird;
    global $dir;
    global $wpdb;

    $categories = get_categories();

    ?>
    <div class="wrap">
    <h1><?php echo __('Categories', 'leginda-wp'); ?></h1>

    <form method="post" action="">

    <table class="wp-list-table widefat fixed striped posts">
        <tr>
            <th>Cat ID</th>
            <th>Term ID</th>
            <th>Name</th>
            <th>Slug</th>
        </tr>
        <?php foreach( $categories as $key => $cat ): ?>
            <tr>
                <td><?php echo $cat->cat_ID; ?></td>
                <td><?php echo $cat->term_id; ?></td>
                <td><?php echo $cat->name; ?></td>
                <td><?php echo $cat->slug; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <br>

    <input type="submit" name="leginda_wp_submit_taxonomies" id="submit" class="button button-primary" value="<?php echo  __('Export and Download', 'leginda-wp'); ?>">
    </form>
    <?php
}