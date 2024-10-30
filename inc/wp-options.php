<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Admin Page of the plugin
 *  
 */
function leginda_wp_options()
{
    if ( ! current_user_can('manage_options') )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    ?>
    <div class="wrap">
    <br>
    <img src="<?php echo plugins_url('/leginda-wp-translate/img/logo.png'); ?>" alt="leginda logo">
    <h2><?php echo __('The easiest way to translate websites in more than 40 languages', 'leginda-wp'); ?></h2>
    
    <div class="leginda-wp-card">
    	
	    <div class="leginda-wp-dash-col-2">

		    <h3><?php echo __("Advantages:", 'leginda-wp'); ?></h3>
		    <ul>
		    	<li><?php echo __("No machine translation", 'leginda-wp'); ?></li>
		    	<li><?php echo __("More than 40 languages", 'leginda-wp'); ?></li>
		    	<li><?php echo __("Very easy text selection", 'leginda-wp'); ?></li>
		    	<li><?php echo __("Importing translations via drag and drop with only one click", 'leginda-wp'); ?></li>
		    	<li><?php echo __("Translations performed by specialized native speakers", 'leginda-wp'); ?></li>
		    	<li><?php echo __("Connected translation memory (previous translations are automatically transferred)", 'leginda-wp'); ?></li>
		    	<li><?php echo __("Discounts on 100% matches and repetitions", 'leginda-wp'); ?></li>
		    	<li><?php echo __("Translations according to DIN EN 17100", 'leginda-wp'); ?></li>
		    </ul>

			<span class="dashicons dashicons-admin-links"></span> <a href="https://www.leginda.de" target="_blank">Leginda.de</a>
		</div>

		<div class="leginda-wp-dash-col-2 leginda-wp-last">
		    <iframe width="100%" height="320" src="https://www.youtube.com/embed/zQ4AtJJn-Vs" frameborder="0" allowfullscreen></iframe>
		</div>

	</div>

	<div class="leginda-wp-card">

	<h3><?php echo __("How it works:", 'leginda-wp'); ?></h3>
		
		<div class="leginda-wp-dash-col-2">
			<div class="leginda-wp-cuad">
			<span>1</span>
			<?php echo __("Choose the original documents as well as the source and target language", 'leginda-wp'); ?>
			</div>
		</div>

		<div class="leginda-wp-dash-col-2 leginda-wp-last">
			<div class="leginda-wp-cuad">
			<span>2</span>
    		<?php echo __("Click export and download the files to your computer.", 'leginda-wp'); ?>
    		</div>
    	</div>

    	<div class="leginda-wp-sep"></div>

    	<div class="leginda-wp-dash-col-2">
    		<div class="leginda-wp-cuad">
    		<span>3</span>
    		<?php echo __("Send the files to info@leginda.com or go to leginda.com", 'leginda-wp'); ?>
    		</div>
    	</div>

    	<div class="leginda-wp-dash-col-2 leginda-wp-last">
    		<div class="leginda-wp-cuad">
    		<span>4</span>
    		<?php echo __("As soon as the translation is completed, the translated texts are imported automatically by drag and drop into the right positions on the website.", 'leginda-wp'); ?>
    		</div>
    	</div>

    	<p><?php echo __("As a new customer of LEGINDA Business Translation you get a discount of € 20,00 EUR, $ 22,00 US or £ 17,00 on your first order.", 'leginda-wp'); ?></p>

    	<p><?php echo __("For further information please visit our LEGINDA Business Translation website: www.leginda.com.", 'leginda-wp'); ?></p>
	</div>

    <?php
}
