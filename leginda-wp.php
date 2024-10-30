<?php
/**
 * Plugin Name: LEGINDA WP Translate
 * Description: Export posts and pages and imports the translations, automatically creating a post with the translation. 
 * Version: 1.6
 * Author: Antonio Sanchez
 * Author URI: https://asanchez.dev
 * Text Domain: leginda-wp
 * Domain Path: leginda-wp
 * License: GPL2 v2.0

    Copyright 2014  Antonio Sanchez (email : antonio@asanchez.dev)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Load Domain
load_plugin_textdomain('leginda-wp', false, basename( dirname( __FILE__ ) ) . '/languages' );

$dir = plugin_dir_path( __FILE__ );
$dir_posts = $dir . "posts";
$dird = plugin_dir_url( __FILE__ );


/**
 * -----------------------------------------
 * Add Configuration Link
 * -----------------------------------------
 *
 */
function leginda_wp_add_action_links ( $links ) 
{
    $mylinks = array(
        '<a href="' . admin_url( 'options-general.php?page=leginda_wp' ) . '">' . __('Settings', 'leginda-wp') . '</a>',
    );
    
    return array_merge( $links, $mylinks );
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'leginda_wp_add_action_links' );


/**
 * -----------------------------------------
 * Add Plugin Pages
 * -----------------------------------------
 *
 */
require_once 'inc/functions.php';
require_once 'inc/wp-options.php';
require_once 'inc/wp-post.php';
require_once 'inc/wp-pages.php';
require_once 'inc/wp-widgets.php';
require_once 'inc/wp-upload.php';
require_once 'inc/wp-categories.php';
require_once 'inc/wp-products.php';


add_action('admin_init', 'leginda_wp_export_single_file');
add_action('admin_init', 'leginda_wp_export_zip');
add_action('admin_init', 'leginda_wp_export_single_widget');
add_action('admin_init', 'leginda_wp_export_widget_zip');
add_action('admin_init', 'leginda_wp_export_taxonomies');

/**
 * Register and enqueue styles
 */
add_action( 'admin_init', 'leginda_wp_style' );
function leginda_wp_style()
{
    wp_register_style( 'leginda_wp-dropzone', plugins_url('/leginda-wp-translate/css/dropzone.css') );
    wp_register_style( 'leginda_wp-style', plugins_url('/leginda-wp-translate/css/style.css') );
}

function leginda_wp_enqueue_style()
{
    wp_enqueue_style( 'leginda_wp-dropzone' );
    wp_enqueue_style( 'leginda_wp-style' );
}

/**
 * Register and enqueue scripts
 */
add_action( 'admin_init', 'leginda_wp_script' );
function leginda_wp_script()
{
    wp_register_script( 'leginda_wp', plugins_url('/leginda-wp-translate/js/dropzone.js') );
    wp_register_script( 'leginda_wp-main', plugins_url('/leginda-wp-translate/js/main.js') );
}

function leginda_wp_enqueue_script()
{
    wp_enqueue_script('leginda_wp');
    wp_enqueue_script('leginda_wp-main');
}

/**
 * Add the Admin Menu
 */
add_action('admin_menu', 'leginda_wp_menu');
function leginda_wp_menu()
{
    $page = add_menu_page( 'Options', __('Leginda', 'leginda-wp'), 'manage_options', 'leginda_wp', 'leginda_wp_options', 'dashicons-translation');
    add_action( 'admin_print_scripts-' . $page, 'leginda_wp_enqueue_script');
    add_action( 'admin_print_styles-' . $page, 'leginda_wp_enqueue_style' );

    $subpageposts = add_submenu_page( 'leginda_wp', 'Leginda Posts', __('Posts', 'leginda-wp'), 'manage_options', 'leginda_wp_posts', 'leginda_wp_posts' );
    add_action( 'admin_print_scripts-' . $subpageposts, 'leginda_wp_enqueue_script');
    add_action( 'admin_print_styles-' . $subpageposts, 'leginda_wp_enqueue_style' );

    $subpagepages = add_submenu_page( 'leginda_wp', 'Leginda Pages', __('Pages', 'leginda-wp'), 'manage_options', 'leginda_wp_pages', 'leginda_wp_pages' );
    add_action( 'admin_print_scripts-' . $subpagepages, 'leginda_wp_enqueue_script');
    add_action( 'admin_print_styles-' . $subpagepages, 'leginda_wp_enqueue_style' );

    if ( is_woocommerce_activated() ){
        $subpageproducts = add_submenu_page( 'leginda_wp', 'Leginda Products', __('Products', 'leginda-wp'), 'manage_options', 'leginda_wp_products', 'leginda_wp_products' );
        add_action( 'admin_print_scripts-' . $subpageproducts, 'leginda_wp_enqueue_script');
        add_action( 'admin_print_styles-' . $subpageproducts, 'leginda_wp_enqueue_style' );
    }

    $subpagepages = add_submenu_page( 'leginda_wp', 'Leginda Widgets', __('Widgets', 'leginda-wp'), 'manage_options', 'leginda_wp_widgets', 'leginda_wp_widgets' );
    add_action( 'admin_print_scripts-' . $subpagepages, 'leginda_wp_enqueue_script');
    add_action( 'admin_print_styles-' . $subpagepages, 'leginda_wp_enqueue_style' );

    $subpagepages = add_submenu_page( 'leginda_wp', 'Leginda Taxonomies', __('Categories', 'leginda-wp'), 'manage_options', 'leginda_wp_categories', 'leginda_wp_categories' );
    add_action( 'admin_print_scripts-' . $subpagepages, 'leginda_wp_enqueue_script');
    add_action( 'admin_print_styles-' . $subpagepages, 'leginda_wp_enqueue_style' );
}