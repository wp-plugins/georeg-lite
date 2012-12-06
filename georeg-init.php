<?php
/*
Plugin Name: GeoReg Lite
Plugin URI: http://marketplace.appthemes.com/plugins/georeg/
Description: A plugin that captures the IP address, country name, and country code of new users who register on your site. A full-blown <a href="http://marketplace.appthemes.com/plugins/georeg/" target="_blank">GeoReg plugin</a> is available in the <a href="http://marketplace.appthemes.com/" target="_blank">AppThemes Marketplace</a>.
Version: 1.0
Author: AppThemes
Author URI: http://www.appthemes.com
*/



class AT_GeoReg {

    public function __construct() {
        add_action( 'init',                             array( &$this, 'load_plugin_textdomain' ) );
        add_action( 'login_form_register',              array( &$this, 'add_jquery' ) );
        add_action( 'register_form',                    array( &$this, 'add_geo_fields' ) );
        add_action( 'user_register',                    array( &$this, 'save_geo_fields' ) );
        add_action( 'edit_user_profile',                array( &$this, 'show_geo_fields' ) );
        add_filter( 'manage_users_columns',             array( &$this, 'add_user_columns' ) );
        add_filter( 'manage_users_custom_column',       array( &$this, 'add_user_column_value' ), 10, 3 );
        add_action( 'edit_user_profile_update',         array( &$this, 'save_geo_fields' ) );
    }
    
    
    // setup support for translation files
    public function load_plugin_textdomain() {
        $domain = 'georeg';
        $locale = apply_filters( $domain . '_locale', get_locale(), $domain );
        load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
    
    
    // if wp-admin is set to another language, use it
    public function wplang() {
        if ( ! defined( 'WPLANG' ) && 2 <= WPLANG )
            return 'en';
            
        $a = explode( '_', WPLANG );
        return $a[0];
    }
    
    
    public function add_jquery() {
        wp_enqueue_script('jquery');
    }
    
    
    // declare all geo fields
    public function geo_fields() {
        $fields = array(
            'geoip_ip_address'    =>  __( 'IP Address', 'georeg' ),
            'geoip_country_code'  =>  __( 'Country Code', 'georeg' ),
            'geoip_country_name'  =>  __( 'Country Name', 'georeg' ),
        );
        
        return apply_filters( 'geo_fields', $fields );
    }
    
    
    // add the fields to the reg form
    public function add_geo_fields( $errors ) {
        $output = '';
        foreach( $this->geo_fields() as $key => $value )
            $output .= '<input type="hidden" name="' . esc_attr($key) . '" value="" />';
        $output .= '<script src="//maxmind.com/app/country.js" charset="ISO-8859-1" type="text/javascript"></script>';
        $output .= '<script type="text/javascript">function geoip_ip_address(){return "'. esc_attr($_SERVER['REMOTE_ADDR']) .'";}';
        $output .= 'jQuery(function($){';
        foreach( $this->geo_fields() as $key => $value )
            $output .= '$("[name=\''. esc_attr($key) . '\']").val(' . esc_attr($key) . '());';
        $output .= '});</script>';
        
        echo apply_filters( 'add_geo_fields', $output );
    }
    
    
    // save all geo values
    public function save_geo_fields( $user_id ) {
        foreach( $this->geo_fields() as $key => $value )
            update_user_meta( $user_id, "$key", $_POST["$key"] ); 
    }
    

    // display geo fields and map on user profile
    public function show_geo_fields( $user ) {
        $output = '';
        
        $output .= '<h3>'. __( 'Geographic Info', 'georeg' ) .'</h3>';
        $output .= '<table class="form-table">';
        foreach( $this->geo_fields() as $key => $value ) {
            $output .= '<tr><th><label for="'.esc_attr($key).'">'.$value.'</label></th>';
            $output .= '<td><input type="text" name="'.esc_attr($key).'" id="'.esc_attr($key).'" value="'.esc_attr(get_the_author_meta($key, $user->ID)).'" readonly="readonly" class="regular-text" /></td>';
            $output .= '</tr>';
        }
        $output .= '</table>';
        
        echo apply_filters( 'show_geo_fields', $output );
    }
    
    
    // add admin user columns
    public function add_user_columns( $columns ) {
        foreach( $this->geo_fields() as $key => $value )
            $columns["$key"] = $value;
        
        return $columns;
    }
    
    
    // add admin user column values
    function add_user_column_value( $val, $column_name, $user_id ) {
        foreach( $this->geo_fields() as $key => $value ) {
            if ( $key == $column_name )
                return get_the_author_meta( $key, $user_id );
        }
        
        return $val;
    }
    
}

// kick things off
new AT_GeoReg();
?>
