<?php 

if ( ! class_exists( 'Womprfq_Seller_Profile' ) ) {
	/**
	 * Load front side functions.
	 */
	class Womprfq_Seller_Profile {
		public function __construct() {
			// Add country fields
			add_action('wkmp_add_fields_to_general_tab',array($this, 'add_extra_fields_to_profile_form'),50);
			// Save seller profile data
			add_action( 'mp_save_seller_profile_details',array($this, 'save_seller_profle_meta'), 10, 2 );
			// Remove WooCommerce By Default Sorting
			add_filter( 'woocommerce_sort_countries', '__return_false' );
			// Get woocommerce countries 
			add_filter( 'woocommerce_countries', array($this,  'wc_countries_custom_order'), 10, 1 );
		}
		
		/**
		 * Add country field to the seller profile form
		 *
		 * @since    1.0.0
		 * @param    $seller_info    array   Seller info array
		 */
		public function add_extra_fields_to_profile_form($seller_info ){
			require_once plugin_dir_path(__DIR__).'templates/seller/profile.php';
		}

		/**
		 * Save country field to the seller meta table
		 *
		 * @since    1.0.0
		 * @param    $posted_data   array   Form posted data
		 * @param    $seller_id    	int   Seller id
		 */
		public function save_seller_profle_meta($posted_data, $seller_id){
			$subscribe_email = empty( $posted_data['wkmp_subscribe_email'] ) ? '' : wp_strip_all_tags( $posted_data['wkmp_subscribe_email'] );
		    $subscribe_country = empty( $posted_data['wkmp_subscribed_country'] ) ? '' : wp_strip_all_tags( $posted_data['wkmp_subscribed_country'] );
		    if(!empty($subscribe_email)){
		        update_user_meta( $seller_id, 'subscribe_email', $subscribe_email );
		        update_user_meta( $seller_id, 'subscribe_country', $subscribe_country );
		    }else{
		        update_user_meta( $seller_id, 'subscribe_email', '' );
		        update_user_meta( $seller_id, 'subscribe_country', '' );
		    }
		}
		/**
		 * Reorder woocommerce countries array
		 *
		 * @since    1.0.0
		 * @param    $countries   array   WC countries array
		 * @return   $countries   array   
		*/

		public function wc_countries_custom_order( $countries ) {
		  // replace with iso code of the country (example: US or GB)
		  unset($countries['PH']);
		  unset($countries['US']);
		  unset($countries['GB']);
		  // replace with iso code of country AND country name (example: US | United States or GB | United Kingdom (UK)
		  $countries = ['PH' => 'Philippines'] + ['US' => 'United States'] + ['GB' => 'United Kingdom (UK)'] + $countries;
		   
		  return $countries;
		}

	}
	new Womprfq_Seller_Profile();
}
?>