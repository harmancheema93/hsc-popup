<?php 

/**
	 Plugin Name: Popup
	 Plugin URI: https://github.com/harmancheema93
	Description: A plugin to add popup in the website.
	Author: Harmandeep Singh
	Version: 1.0
	Author URI: https://github.com/harmancheema93
 */

function hsc_popup_scripts(){
	wp_enqueue_style( 'hsc-popup-style' , plugin_dir_url(__FILE__).'/hsc-popup.css' );
	wp_enqueue_script( 'jquery-cookie' , 'https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js' );
}
add_action( 'wp_enqueue_scripts', 'hsc_popup_scripts', 100 );

function hsc_popup_post_type(){
	$labels = array(
	    'name'                => _x( 'Popups', 'Post Type General Name', 'hsc' ),
	    'singular_name'       => _x( 'Popup', 'Post Type Singular Name', 'hsc' ),
	    );
	     
    $args = array(
        'label'               => __( 'Popup', 'hsc' ),
        'description'         => __( 'Popup for Website ', 'hsc' ),
        'labels'              => $labels,
        'show_in_rest' 		  => true,
        'supports'            => array( 'title','custom-fields', 'editor' ),
        'hierarchical'        => false,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => false,
        'menu_icon'		      => 'dashicons-editor-expand',
        'show_in_admin_bar'   => true,
        'menu_position'       => 50,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => false,
        'capability_type'     => 'page',
    );
	     
	register_post_type( 'hsc_popup', $args );
}
add_action( 'init', 'hsc_popup_post_type', 0 );

function hsc_popup_metabox(){
    $prefix = '_popup_';
    $cmb = new_cmb2_box( array(
        'id'            => 'popup_options',
        'title'         => __( 'Options for the Visiblity of Popup', 'hsc' ),
        'object_types'  => array( 'hsc_popup', ), 
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, 
    ) );

    $cmb->add_field( array(
        'name'    => 'Show on Load',
        'id'      => $prefix.'load',
        'type'    => 'radio_inline',
        'options' => array(
            'no' => __( 'No', 'hsc' ),
            'yes'   => __( 'Yes', 'hsc' ),
        ),
        'default' => 'no',
    ) );

    $cmb->add_field( array(
        'name'    => 'Show Title',
        'id'      => $prefix.'heading',
        'type'    => 'radio_inline',
        'options' => array(
            'no' => __( 'No', 'hsc' ),
            'yes'   => __( 'Yes', 'hsc' ),
        ),
        'default' => 'no',
    ) );
}
add_action( 'cmb2_admin_init', 'hsc_popup_metabox' );
function hsc_popup_save($post_id){
	if(get_post_type($post_id) == 'hsc_popup'){
   		$trigger = '#popup-'.$post_id;
   		update_post_meta( $post_id, 'popup_trigger', $trigger);
   	}
}
add_action('save_post', 'hsc_popup_save');

function add_trigger_columns($columns) {
    return array_merge($columns,
              array('trigger' => __('Trigger ID'),));
}
add_filter('manage_hsc_popup_posts_columns' , 'add_trigger_columns');

function hsc_popup_shortcode_order_column( $columns ) {
    $new = array();
	foreach($columns as $key => $title) {
		if ($key=='date')
	      $new['trigger'] = 'Trigger';
	    $new[$key] = $title;
	  }
	  return $new;
}
add_filter( 'manage_hsc_popup_posts_columns', 'hsc_popup_shortcode_order_column', 10 );

function hsc_popup_order_column( $column, $post_id ) {
    switch ( $column ) {
        case 'trigger' :
            echo "<input type='text' readonly='readonly' value='".get_post_meta( $post_id , 'popup_trigger' , true )."' style='width:350px; cursor: text;'  onClick='this.select();'>"; 
            break;
    }
}
add_action( 'manage_hsc_popup_posts_custom_column' , 'hsc_popup_order_column', 10, 2 );


function hsc_add_popup_body() {
    $popups  = get_posts( array( 'post_type' => 'hsc_popup', 'posts_per_page' => '-1', 'suppress_filters' => false) ); ?>
    
    <div class="popup-body harman"><div>
    <?php foreach( $popups as $popup ){ ?>
        <div class="popup-inner" id="popup-<?php echo $popup->ID; ?>"><?php if(get_post_meta($popup->ID, '_popup_heading', true) == 'yes') { ?><div class="popup-title"><p class="h3 text-center"><?php echo $popup->post_title; ?></p></div><?php } ?><?php echo apply_filters( 'the_content', $popup->post_content ); ?></div>
    <?php
        if(get_post_meta( $popup->ID, '_popup_load', true) == 'yes'){        
            $loads[] = '#popup-'.$popup->ID;
        }
    } ?>
    </div></div>
    <script>
    /** popup functionality */
    jQuery(document).ready(function() {
        jQuery('.popup-body .popup-inner').append('<div class="popup-close">x</div>');
        jQuery('.popup-trigger a').click(function(e) {
            e.preventDefault();
        });
        jQuery('.popup-trigger').click(function() {
            var link = jQuery(this).find('a').attr('href');
            jQuery('body').addClass('popup-open');
            jQuery('.popup-body').hide();
            jQuery('.popup-inner').hide();
            if (link != undefined) {
                jQuery('.popup-body').show();
                jQuery('.popup-body').find(link).fadeIn();
            } else {
                jQuery(this).next('.popup-body').toggle();
            }
        });
        jQuery('.popup-close, .popup-body').click(function(e) {
            if (!jQuery(e.target).hasClass('popup-inner')) {
                jQuery.cookie('popup-closed', 'yes', {expires: 7});
                jQuery('body').removeClass('popup-open');
                jQuery('.popup-body').hide();
                jQuery('.popup-inner').hide();
            }
        }); 
        jQuery(".popup-inner").click(function (event) {
            event.stopPropagation();
        });
        <?php if($loads){ $load = implode(',', $loads); ?>
        setTimeout(function() {
            if(jQuery.cookie('popup-closed') != 'yes'){
                jQuery('body').addClass('popup-open');
                jQuery('.popup-body').show();
                jQuery('<?php echo $load; ?>').fadeIn();
            }
        }, 2000);
        <?php } ?>
    });
    </script>
    <?php 
}
add_filter( 'wp_footer','hsc_add_popup_body' );