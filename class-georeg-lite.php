<?php
/**
 * Captures geographic informations about user.
 */
class AT_GeoReg_Lite {

	/**
	 * Sets up plugin.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init',                       array( &$this, 'load_plugin_textdomain' ) );
		add_action( 'login_form_register',        array( &$this, 'add_enqueue_scripts' ) );
		add_action( 'register_form',              array( &$this, 'add_geo_fields' ) );
		add_action( 'user_register',              array( &$this, 'save_geo_fields' ) );
		add_action( 'edit_user_profile',          array( &$this, 'show_geo_fields' ) );
		add_filter( 'manage_users_columns',       array( &$this, 'add_user_columns' ) );
		add_filter( 'manage_users_custom_column', array( &$this, 'add_user_column_value' ), 10, 3 );
		add_action( 'edit_user_profile_update',   array( &$this, 'save_geo_fields' ) );
	}


	/**
	 * Loads plugin textdomain.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'georeg-lite', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}


	/**
	 * Enqueues scripts.
	 * Registration page doesn't natively enqueue jQuery.
	 *
	 * @return void
	 */
	public function add_enqueue_scripts() {
		wp_enqueue_script( 'jquery' );
	}


	/**
	 * Returns an array of all geo fields.
	 *
	 * @return array
	 */
	public function geo_fields() {
		$fields = array(
			'geoip_ip_address'    =>  __( 'IP Address', 'georeg-lite' ),
			'geoip_country_code'  =>  __( 'Country Code', 'georeg-lite' ),
			'geoip_country_name'  =>  __( 'Country Name', 'georeg-lite' ),
		);

		return apply_filters( 'geo_fields', $fields );
	}


	/**
	 * Returns an array of all geo field keys mapped to used service.
	 *
	 * @return array
	 */
	public function geo_fields_map() {
		$fields = array();

		// default Telize fields map
		$fields_map = array(
			'geoip_ip_address'   => 'ip',
			'geoip_country_code' => 'country_code',
			'geoip_country_name' => 'country',
		);
		$fields_map = apply_filters( 'geo_fields_map', $fields_map );

		// return only fields that have relation
		foreach ( $this->geo_fields() as $key => $name ) {
			if ( isset( $fields_map[ $key ] ) ) {
				$fields[ $key ] = $fields_map[ $key ];
			}
		}

		return $fields;
	}


	/**
	 * Returns geoip service url.
	 *
	 * @return string
	 */
	public function get_service_url() {
		$service_url = 'http://www.telize.com/geoip';

		return apply_filters( 'geo_service_url', $service_url );
	}


	/**
	 * Adds the geo fields to the registration form.
	 *
	 * @param object $errors
	 *
	 * @return void
	 */
	public function add_geo_fields( $errors ) {
		$output = '';

		// generate hidden fields
		foreach ( $this->geo_fields() as $key => $value ) {
			$output .= '<input type="hidden" name="' . esc_attr( $key ) . '" value="" />';
		}

		// generate js script
		$output .= '<script type="text/javascript">' . PHP_EOL;
		$output .= '  jQuery(document).ready(function($) {' . PHP_EOL;
		$output .= '    $.getJSON("'. esc_attr( $this->get_service_url() ) . '", function(json) {' . PHP_EOL;

		foreach ( $this->geo_fields_map() as $field_name => $response_key ) {
			$output .= '      $("[name=\''. esc_attr( $field_name ) . '\']").val(json.' . esc_attr( $response_key ) . ');' . PHP_EOL;
		}

		$output .= '    });' . PHP_EOL;
		$output .= '  });' . PHP_EOL;
		$output .= '</script>' . PHP_EOL;

		echo apply_filters( 'add_geo_fields', $output );
	}


	/**
	 * Saves all the geo values.
	 *
	 * @param int $user_id
	 *
	 * @return void
	 */
	public function save_geo_fields( $user_id ) {
		foreach ( $this->geo_fields() as $key => $value ) {
			$posted = isset( $_POST[ $key ] ) ? wp_kses_data( $_POST[ $key ] ) : '';
			update_user_meta( $user_id, $key, $posted );
		}
	}


	/**
	 * Displays geo fields and map on admin edit user page.
	 *
	 * @param object $user
	 *
	 * @return void
	 */
	public function show_geo_fields( $user ) {
		$output = '';
		$i = 0;

		$output .= '<h3>' . __( 'Geographic Info', 'georeg-lite' ) . '</h3>';
		$output .= '<table class="form-table">';
		foreach ( $this->geo_fields() as $key => $value ) {
			$output .= '<tr><th><label for="' . esc_attr( $key ) . '">' . $value . '</label></th>';
			$output .= '<td width="325"><input type="text" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( get_the_author_meta( $key, $user->ID ) ) . '" class="regular-text" /></td>';
			$output .= '</tr>';
			$i++;
		}
		$output .= '</table>';

		echo apply_filters( 'show_geo_fields', $output );
	}


	/**
	 * Adds custom user columns.
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function add_user_columns( $columns ) {
		foreach ( $this->geo_fields() as $key => $value ) {
			$columns[ $key ] = $value;
		}

		return $columns;
	}


	/**
	 * Returns values for custom user columns.
	 *
	 * @param string $val
	 * @param string $column_name
	 * @param int $user_id
	 *
	 * @return string
	 */
	function add_user_column_value( $val, $column_name, $user_id ) {
		foreach ( $this->geo_fields() as $key => $value ) {
			if ( $key == $column_name ) {
				return get_the_author_meta( $key, $user_id );
			}
		}

		return $val;
	}

}
