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
		add_action( 'womprfq_save_quotation_meta', array($this,  'save_quotation_meta'), 10, 2 );
		// add country dropdown
		add_action('womprfq_manage_rfq_template_before_content',array($this,  'add_country_dropdown'),30,4 );

		add_filter( 'womprfq_get_seller_quotations', array( $this, 'get_quote_data_template' ), 10);
		// Get main quote
		add_filter( 'womprfq_get_country_for_main_quote', array($this,  'get_country_for_main_quote'), 10,2);
		require_once __dir__.'/class-marketplace-and-rfq-customisation-admin-submit-quotation.php';
		
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

	/**
	 * Add country dropdown before seller template table
	 *
	 * @since    1.0.0
	 * @param $tab 		Seller Tabs
	 * @param $offset 	Starting index
	 * @param $page 	Page Number
	 * @param $limit 	Limit records
	*/
	public function add_country_dropdown($tab, $offset, $page, $limit){
		if($tab == 'open'){ ?>
			<div class="dropdown">
				<button class="dropbtn">
					<?php 
					$subscribe_email = get_user_meta(get_current_user_id(),'subscribe_country', true);
					if(isset($_GET['c'])){ 
						if( $_GET['c'] == 'all' ) { 
							echo "All";
						}else{
							echo WC()->countries->countries[ $_GET['c'] ];
						}
					?>
						
					<?php }elseif(!empty($subscribe_email)) {
						if($subscribe_email == "all"){
							echo "All";
						}else{
							echo WC()->countries->countries[ $subscribe_email ];
						}
					?>
					<?php }else{ ?>
						All
					<?php } ?>
				</button>
			 	<div class="dropdown-content">
				    <a href="?c=all">All</a>
			 		<?php 
			 			$countries_obj = new \WC_Countries();
						$countries     = $countries_obj->__get( 'countries' );
						foreach ( $countries as $key => $country ) {
			 		?>
				    	<a href="?c=<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $country ); ?></a>
				    <?php } ?>
				</div> 
			</div>
		<?php }
	}

	public function get_quote_data_template($tdata){
		$post_data = $_REQUEST;

		if ( isset( $post_data['tab'] ) && ! empty( $post_data['tab'] ) ) {
			$tab = $post_data['tab'];
		} else {
			$tab = 'open';
		}
		if ( get_query_var( 'info' ) ) {
			$page = get_query_var( 'info' );
		} else {
			$page = 1;
		}
		$limit  = 5;
		$offset = ( $page == 1 ) ? 0 : ( $page - 1 ) * $limit;	
		
		if($tab == 'open'){
			if(isset($_GET['c'])){
				$seller_country = $_GET['c'];
			}else{
				$seller_country = get_user_meta(get_current_user_id(),'subscribe_country',true);
			}
			if($_GET['c'] === "all" OR  empty($seller_country) OR  $seller_country == "all" ){ 
				return $tdata;
			}else{
				$tab_data = $this->womprfq_get_seller_quotations_by_country( get_current_user_id(), $tab, $offset, $limit,$seller_country );
				return $tab_data;

			}		
		}else{
			return $tdata;
		}
	}

	// JS edit. Add country and city drop down filter and country preference. Step 6
	public function womprfq_get_seller_quotations_by_country( $sel_id, $tab, $offset, $limit,$country ) {
		global $wpdb;
		$endpoint                       = 'rfq';
		$posts                          = $wpdb->posts;
		$main_quote_table               = $wpdb->prefix . 'womprfq_main_quotation';
		$main_quote_meta_table          = $wpdb->prefix . 'womprfq_main_quotation_meta';
		$seller_quote_comment_table     = $wpdb->prefix . 'womprfq_seller_quotation_comment';
		$seller_quote_table             = $wpdb->prefix . 'womprfq_seller_quotation';
		$seller_quotation_comment_table = $wpdb->prefix . 'womprfq_seller_quotation_comment';
		$wpdb_obj                       = $wpdb;
		$tdata = $data = array();
		$tabs  = array(
			'open'     => 0,
			'pending'  => 1,
			'answered' => 2,
			'resolved' => 3,
			'closed'   => 4,
		);
		$ids   = array();
		if ( $sel_id && $tab ) {
			$status = $tabs[ $tab ];
			if ( $status == 0 ) {
				$query = $wpdb_obj->prepare( "SELECT main_quotation_id FROM $seller_quote_table WHERE seller_id = %d", $sel_id );
				$res   = $wpdb_obj->get_results( $query );
				if ( $res ) {
					$ids = wc_list_pluck( $res, 'main_quotation_id' );
				}
				if ( ! empty( $ids ) ) { 
					$ids_str = implode( ',', $ids );
					$query1  = $wpdb_obj->prepare( "SELECT $main_quote_table.* FROM $main_quote_table JOIN $main_quote_meta_table ON $main_quote_table.id = $main_quote_meta_table.main_quotation_id WHERE $main_quote_meta_table.key = 'quotation_country' AND $main_quote_meta_table.value = '$country' AND $main_quote_table.status = %d AND $main_quote_table.customer_id != %d AND $main_quote_table.id NOT IN(" . esc_html( $ids_str ) . ' ) ORDER BY '.$main_quote_table.'.id DESC LIMIT %d, %d', 1, $sel_id, $offset, $limit );
					$query1c = "SELECT count(*) as count FROM $main_quote_table JOIN $main_quote_meta_table ON $main_quote_table.id = $main_quote_meta_table.main_quotation_id WHERE $main_quote_meta_table.key = 'quotation_country' AND $main_quote_meta_table.value = '$country' AND $main_quote_table.status = 1 AND $main_quote_table.customer_id != $sel_id AND $main_quote_table.id NOT IN ( " . esc_html( $ids_str ) . ' )';
					$res1    = $wpdb_obj->get_results( $query1 );
					$resc    = $wpdb_obj->get_results( $query1c );
				} else {
					$query1  = $wpdb_obj->prepare( "SELECT $main_quote_table.* FROM $main_quote_table JOIN $main_quote_meta_table ON $main_quote_table.id = $main_quote_meta_table.main_quotation_id WHERE $main_quote_meta_table.key = 'quotation_country' AND $main_quote_meta_table.value = '$country' AND $main_quote_table.status = %d AND $main_quote_table.customer_id != %d  ORDER BY $main_quote_table.id DESC LIMIT %d, %d", 1, $sel_id, $offset, $limit );
					
					$query1c = $wpdb_obj->prepare( "SELECT count(*) as count FROM $main_quote_table JOIN $main_quote_meta_table ON $main_quote_table.id = $main_quote_meta_table.main_quotation_id WHERE $main_quote_meta_table.key = 'quotation_country' AND $main_quote_meta_table.value = '$country' AND status = %d AND customer_id != %d", 1, $sel_id );
					$res1    = $wpdb_obj->get_results( $query1 );
					$resc    = $wpdb_obj->get_results( $query1c );
				}
				if ( ! empty( $res1 ) ) {
					foreach ( $res1 as $rs ) {
						if ( $rs->variation_id != 0 ) {
							$pro_name = get_the_title( $rs->variation_id );
						} elseif ( $rs->product_id != 0 ) {
							$pro_name = get_the_title( $rs->product_id );
						} else {
							$dat = $this->wkrfq_get_quote_meta_info( $rs->id );
							if ( isset( $dat['pro_name'] ) ) {
								$pro_name = $dat['pro_name'];
							} else {
								$pro_name = esc_html__( 'N\A', 'wk-mp-rfq' );
							}
						}
						$user = get_user_by( 'ID', $rs->customer_id );

						if ( $user ) {
							$display_name = $user->display_name;
							$user_email   = $user->user_email;
						} else {
							$display_name = esc_html__( 'N\A', 'wk-mp-rfq' );
							$user_email   = esc_html__( 'N\A', 'wk-mp-rfq' );
						}
						$data[] = array(
							'id'             => $rs->id,
							'product_info'   => array(
								'product_id'   => $rs->product_id,
								'variation_id' => $rs->variation_id,
								'name'         => $pro_name,
							),
							'customer_info'  => array(
								'id'           => $rs->customer_id,
								'display_name' => $display_name,
								'email'        => $user_email,
							),
							'quote_status'   => $rs->status,
							'quote_quantity' => $rs->quantity,
							'date_created'   => $rs->date,
						);
					}
				}
			} 
		}

		$tdata['data']   = $data;
		$tdata['tcount'] = $resc[0]->count;
		return $tdata;
	}

	/**
	 * Get Quote meta.
	 *
	 * @param int $qid quote id.
	*/
	public function wkrfq_get_quote_meta_info( $qid ) {
		global $wpdb;
		$wpdb_obj              = $wpdb;
		$main_quote_meta_table = $wpdb->prefix . 'womprfq_main_quotation_meta';
		$res                   = false;
		if ( $qid ) {
			$query   = $wpdb_obj->prepare( "SELECT * FROM $main_quote_meta_table WHERE main_quotation_id = %d", $qid );
			$results = $wpdb_obj->get_results( $query );
			if ( $results ) {
				foreach ( $results as $result ) {
					$res[ $result->key ] = $result->value;
				}
			}
		}
		return apply_filters( 'womprfq_get_quote_meta_info', $res, $qid );
	}
	/**
	 * Display country inside main quote table
	 *
	 * @since    1.0.0
	*/
	public function get_country_for_main_quote($sh_data,$quote_d){ 
		$country = WC()->countries->countries[ $quote_d['quotation_country'] ];
		$state = WC()->countries->get_states( $quote_d['quotation_country'] )[$quote_d['quotation_state']];
		if ( isset( $quote_d['quotation_country'] ) ) {
			 $sh_data['country'] = array(
				 'title' => esc_html__( 'Deliver To', 'wk-mp-rfq' ),
				 'value' => $country,
			 );
			// if ( !empty($state) ) {
			// 	$sh_data['state'] = array(
			// 		 'title' => esc_html__( 'Region/State', 'wk-mp-rfq' ),
			// 		 'value' => $state,
			// 	 );
			// }
		}
		return $sh_data;
	}
}
