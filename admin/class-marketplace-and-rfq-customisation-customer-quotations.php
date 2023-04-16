<?php 

if ( ! class_exists( 'Womprfq_customer_Quotation' ) ) {
	/**
	 * Load front side functions.
	 */
	class Womprfq_customer_Quotation {
		public function __construct() {
			// Override Add new quotation form
			add_action( 'wkmprfq_after_customer_new_product_submit_form', array( $this, 'customer_endpoint_template' ) );
			//Save country field inside quotation
			add_action( 'womprfq_save_quotation_meta', array($this,  'save_quotation_meta'), 10, 2 );
		}
		
		
		/**
		 * Add new quotation form
		 *
		 * @since    1.0.0
		 */
		public function customer_endpoint_template(){
			$attributes = $this->womprfq_get_attribute_info();
			require_once plugin_dir_path(__DIR__).'templates/customer/add_new_product_form.php';
			// die();
		}
		/**
		 * Save quotation meta
		 *
		 * @param $id    Quotation Id
		 * @param $attr  Attributes
		 * @since    1.0.0
		*/
		public function save_quotation_meta($id, $attr){
		    global $wpdb;
		    $table = $wpdb->prefix . 'womprfq_main_quotation_meta';
		    $wpdb->insert(
		        $table,
		        array(
		            'main_quotation_id' => intval( $id ),
		            'key'               => sanitize_text_field( 'quotation_country' ),
		            'value'             => sanitize_text_field( $_POST['wkmp_quotation_country'] ),
		        ),
		        array(
		            '%d',
		            '%s',
		            '%s',
		        )
		    );
			
			// $wpdb->insert(
			 //        $table,
			 //        array(
			 //            'main_quotation_id' => intval( $id ),
			 //            'key'               => sanitize_text_field( 'quotation_state' ),
			 //            'value'             => sanitize_text_field( $_POST['wkmp_quotation_state'] ),
			 //        ),
			 //        array(
			 //            '%d',
			 //            '%s',
			 //            '%s',
			 //        )
			 //    );	
		}

		/**
		 * Returns attribute info
		 *
		 * @param int    $search attribute id.
		 * @param string $filter filter.
		 *
		 * @return $search
		*/
		public function womprfq_get_attribute_info( $search = '', $filter = '' ) {
			global $wpdb;
			$wpdb_obj = $wpdb;
			$attr_table = $wpdb->prefix . 'womprfq_attribute';
			$response = array();
			if ( ! empty( $search ) && ! empty( $filter ) ) {
				$response = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM $attr_table WHERE type = %s and label LIKE %s ", $filter, '%' . $search . '%' ) );
			} elseif ( ! empty( $filter ) ) {
				$response = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM $attr_table WHERE type = %s ", $filter ) );
			} elseif ( ! empty( $search ) ) {
				$response = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM $attr_table WHERE label LIKE %s ", '%' . $search . '%' ) );
			} else {
				$response = $wpdb_obj->get_results( "SELECT * FROM $attr_table" );
			}

			return $response;
		}
	}
	new Womprfq_customer_Quotation();
}
?>