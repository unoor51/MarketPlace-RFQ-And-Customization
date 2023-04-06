<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.fiverr.com/gemini002
 * @since      1.0.0
 *
 * @package    Marketplace_And_Rfq_Customisation
 * @subpackage Marketplace_And_Rfq_Customisation/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Marketplace_And_Rfq_Customisation
 * @subpackage Marketplace_And_Rfq_Customisation/admin
 * @author     Noor <unoor51@gmail.com>
 */
class Marketplace_And_Rfq_Customisation_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		// Add country fields
		add_action('wkmp_add_fields_to_general_tab',array($this, 'add_extra_fields_to_profile_form'),50);
		// Save seller profile data
		add_action( 'mp_save_seller_profile_details',array($this, 'save_seller_profle_meta'), 10, 2 );
		// Remove WooCommerce By Default Sorting
		add_filter( 'woocommerce_sort_countries', '__return_false' );
		// Get woocommerce countries 
		add_filter( 'woocommerce_countries', array($this,  'wc_countries_custom_order'), 10, 1 );
		// Add extra field to add new product quote
		add_filter( 'wk_mp_rfq_add_product_quote_extrafield', array($this,  'rfq_add_new_product_quote_extrafield'),30);
		//Save country field inside quotation
		add_action( 'womprfq_save_quotation_meta', 'save_quotation_meta', 10, 2 );

		
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Marketplace_And_Rfq_Customisation_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Marketplace_And_Rfq_Customisation_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/marketplace-and-rfq-customisation-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Marketplace_And_Rfq_Customisation_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Marketplace_And_Rfq_Customisation_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/marketplace-and-rfq-customisation-admin.js', array( 'jquery' ), $this->version, false );

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
	/**
	 * Add country field to the Add Quote Product quotation
	 *
	 * @since    1.0.0
	*/
	public function rfq_add_new_product_quote_extrafield(){ 
		echo "<pre>";print_r($seller_info);
		?>
		<tr valign="top">
			<th>
				<label for="qcountry"><?php esc_html_e( 'Deliver To', 'wk-mp-rfq' ); ?></label><span class="required">*</span>
			</th>
			<td class="forminp subscribed_country">
				<select name="wkmp_quotation_country" id="billing-country" class="form-control" oninvalid="this.setCustomValidity('You need to select the country in the list.')" oninput="this.setCustomValidity('')" required>
					<option value=""><?php esc_html_e( 'Select Country', 'wk-mp-rfq' ); ?></option>
					<?php
					$countries_obj = new \WC_Countries();
					$countries     = $countries_obj->__get( 'countries' );
					foreach ( $countries as $key => $country ) {
						?>
						<?php if ( $key === $seller_info['wkmp_shop_country'] ) { ?>
							<option value="<?php echo esc_attr( $key ); ?>" selected><?php echo esc_html( $country ); ?></option>
						<?php } else { ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $country ); ?></option>
						<?php } ?>
					<?php } ?>
				</select>
			
			</td>
		</tr>
	<?php }

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
}
