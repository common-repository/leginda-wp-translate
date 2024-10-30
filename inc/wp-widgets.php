<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Page for managing translations of posts
 *  
 */
function leginda_wp_widgets()
{
    if ( ! current_user_can('manage_options') )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    global $dird;
    global $dir;
    global $wpdb;

    // The Query
    if( $query1 = $wpdb->get_results( "SELECT * FROM wp_options where option_name = 'widget_text'") ){
        $query1 = $query1[0];
        $query1 = unserialize($query1->option_value);
    }

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
            <td><?php echo __("Content", 'leginda-wp'); ?></td> 
            <td><?php echo __("Download", 'leginda-wp'); ?></td>
            <td><?php echo __("Word count", 'leginda-wp'); ?></td>
        </tr>
        </thead>

        <tbody id="the-list">
        <?php foreach($query1 as $id => $widget) : ?>
            <tr>
                <th scope="row" class="check-column">           
                    <label class="screen-reader-text" for="cb-select-5"><?php echo __("Select", 'leginda-wp'); ?></label>
                    <input id="<?php echo $id; ?>" type="checkbox" name="widget_<?php echo $id; ?>" value="<?php echo $id; ?>">
                    <div class="locked-indicator"></div>
                </th>
                <td><?php echo $widget['title']; ?></td>
                <td>
                    <?php echo $widget['text']; ?>
                </td> 
                <td>
                    <a class="leginda_wp_down" id="<?php echo $id; ?>" href="<?php echo admin_url( 'admin.php?page=leginda_wp_widgets' ); ?>&leginda_wp_submitted_widget=<?php echo $id; ?>">
                        <span class='dashicons dashicons-arrow-down-alt'></span> <?php echo  __('Export and Download', 'leginda-wp'); ?>
                    </a>
                </td>
                <td>
                    <?php echo str_word_count($widget['text']) + str_word_count($widget['title']); ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>

    </table>

    <br>

    <input type="submit" name="leginda_wp_submit_widget" id="submit" class="button button-primary" value="<?php echo  __('Export and Download', 'leginda-wp'); ?>">
    </form>
    <?php
}