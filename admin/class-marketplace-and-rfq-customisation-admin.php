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
		$uri = $_SERVER['REQUEST_URI'];
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		/*
		* Manage seller profile related quries
		*/
		require_once __dir__.'/class-marketplace-and-rfq-customisation-seller-profile.php';

		// add country dropdown
		add_action('womprfq_manage_rfq_template_before_content',array($this,  'add_country_dropdown'),30,4 );

		add_filter( 'womprfq_get_seller_quotations', array( $this, 'get_quote_data_template' ), 10);
		// Get main quote
		add_filter( 'womprfq_get_country_for_main_quote', array($this,  'get_country_for_main_quote'), 10,2);
		// Quotation submit
		require_once __dir__.'/class-marketplace-and-rfq-customisation-admin-submit-quotation.php';
		// Seller Order History
		add_filter('wkmp_order_list',array($this,  'order_list_history_template'));
		// Seller Order Views
		add_filter('wkmp_order_views',array($this,  'order_views_history_template'));

		// handle cancel button on RFQ
		require_once __dir__.'/class-marketplace-and-rfq-customisation-admin-cancel-button.php';
		// handle seller edit quotation form
		
		if (strpos($uri, 'seller-quote') !== false) {
		    add_action('womprfq_seller_edit_quotation_save_form_field', array($this,  'seller_edit_quotation_save_form_field'),10,2 );
		}
		/*
		* Manage seller quotation related quries
		*/
		require_once __dir__.'/class-marketplace-and-rfq-customisation-seller-quotation.php';
		// Add columns to main table
		add_filter('womprfq_list_quote_columns',array($this,'list_quote_columns'));
		// Add columns data to main quote table
		add_action('womprfq_list_main_quote_data',array($this,'list_main_quote_data'),10,4 );

		// Seller Profile Tabs
		add_filter('wkmp_seller_front_profile_tabs',array($this,'seller_front_profile_tabs'));
		add_filter('add_attribute_type',array($this,'add_attribute_type_function'));

		add_filter( 'woocommerce_my_account_my_orders_actions', array($this,'msoa_mark_as_received' ), 10, 2 );

		/*
		* Manage customer quotations related quries
		*/
		require_once __dir__.'/class-marketplace-and-rfq-customisation-customer-quotations.php';
	}
	// Remove seller profile Tabs
	public function seller_front_profile_tabs($tabs){
    	$tabs = array();
		return $tabs;
	}
	// Add attribute type function
	public function add_attribute_type_function($fieldtype){
		/* JS edit: Add attribute type Date */
		$fieldtype[] = array(
			'type'  => 'date',
			'title' => esc_html__( 'Date', 'wk-mp-rfq' ),
		);
		return $fieldtype;
	}

	public function seller_edit_quotation_save_form_field($status,$seller_data){
		// JS edit: Add  name of personal shopper on quote display
		$seller_details = get_userdata($seller_data->seller_id);
		$seller_shopname= get_usermeta( $seller_data->seller_id,'display_name' ); ?>
		<tr class="order_item alt-table-row">
			<td colspan="2" class="product-name toptable">
		 		<strong>
		 		Personal shopper
		 		</strong>
		 	</td>
		 	<td colspan="2" class="product-total toptable">
		 		<a href="<?php echo site_url(); ?>/seller/seller-recent-products/<?php  echo $seller_shopname; ?>"><?php echo $seller_shopname; ?></a>
		 	</td>
		</tr>
	<?php }

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
	 * Order history template
	 *
	 * @since    1.0.0
	 */
	public function order_list_history_template(){
		return plugin_dir_path(__DIR__).'templates/seller/orders/order-list.php';
	}
	/**
	 * Order views template
	 *
	 * @since    1.0.0
	 */
	public function order_views_history_template(){
		return plugin_dir_path(__DIR__).'templates/seller/orders/order-views.php';
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

	/**
	 * Add main quoatation columns
	 *
	 * @param    $quote_columns  Columns
	 * @since    1.0.0
	*/
	public function list_quote_columns($quote_columns){
		unset($quote_columns['quote-actions']);
		$quote_columns['quontes-recieved'] = esc_html__( 'Offers', 'wk-mp-rfq' );

		$quote_columns['quote-actions'] = esc_html__( 'Actions', 'wk-mp-rfq' );
		return $quote_columns;
	}
	/**
	 * Add main quotation columns data
	 *
	 * @param    $quote_columns  Columns
	 * @since    1.0.0
	*/
	public function list_main_quote_data($roles,$current_login_user_id,$main_creator_ID,$data){

		if($roles[0]=='customer' || ($current_login_user_id == $main_creator_ID) ){
			global $wpdb;                                                         
			$query1c = $wpdb->prepare( "SELECT count(*) as count FROM ".$wpdb->prefix."womprfq_seller_quotation WHERE main_quotation_id = ".$data['id'] );                                                
			$resc    = $wpdb->get_results( $query1c );
			?>
			<td class="woocommerce-orders-table__cell "  >
			<?php echo $resc[0]->count; ?>
			</td>
		<?php }else{ ?>
			<td class="woocommerce-orders-table__cell "  > NA </td>
		<?php } 
	}

	public function msoa_mark_as_received( $actions, $order ) {
		$order_id = $order->id;

	    if ( ! is_object( $order ) ) {
	        $order_id = absint( $order );
	        $order    = wc_get_order( $order_id );
	    }
	    
	    // check if order status delivered and form not submitted

		if ( ( $order->has_status( 'delivered' ) ) && ( !isset( $_POST['mark_as_received'] ) ) ) {
			$check_received = ( $order->has_status( 'delivered' ) ) ? "true" : "false";
		    ?>
		    <div class="ms-mark-as-received">
			    <form method="post">
					<input type="hidden" name="mark_as_received" value="<?php echo esc_attr( $check_received ); ?>">
					<input type="hidden" name="order_id" value="<?php echo esc_attr($order_id);?>">
					<?php wp_nonce_field( 'so_38792085_nonce_action', '_so_38792085_nonce_field' ); ?> 
					<input class="int-button-small" type="submit" value="<?php echo esc_attr_e( 'Mark as Received', 'order-approval' ); ?>" data-toggle="tooltip" title="<?php echo esc_attr_e( 'Click to mark the order as complete if you have received the product', 'order-approval' ); ?>">
				</form>
		    </div>
		    <?php
		}

	    /*
	    //refresh page if form submitted

	    * fix status not updating
	    */
	    if( isset( $_POST['mark_as_received'] ) ) { 
	        echo "<meta http-equiv='refresh' content='0'>";
	    }

		// not a "mark as received" form submission
	    if ( ! isset( $_POST['mark_as_received'] ) ){
	        return $actions;
	    }

	    // basic security check
	    if ( ! isset( $_POST['_so_38792085_nonce_field'] ) || ! wp_verify_nonce( $_POST['_so_38792085_nonce_field'], 'so_38792085_nonce_action' ) ) {   
	        return $actions;
	    } 

	    // make sure order id is submitted
	    if ( ! isset( $_POST['order_id'] ) ){
	        $order_id = intval( $_POST['order_id'] );
	        $order = wc_get_order( $order_id );
	        $order->update_status( "completed" );
	        return $actions;
	    }  
	    if ( isset( $_POST['mark_as_received'] ) == true ) {
	    	$order_id = intval( $_POST['order_id'] );
	        $order = wc_get_order( $order_id );
	        $order->update_status( "completed" );
	    }

	    $actions = array(
	        'pay'    => array(
	            'url'  => $order->get_checkout_payment_url(),
	            'name' => __( 'Pay', 'woocommerce' ),
	        ),
	        'view'   => array(
	            'url'  => $order->get_view_order_url(),
	            'name' => __( 'View', 'woocommerce' ),
	        ),
	        'cancel' => array(
	            'url'  => $order->get_cancel_order_url( wc_get_page_permalink( 'myaccount' ) ),
	            'name' => __( 'Cancel', 'woocommerce' ),
	        ),
	    );

	    if ( ! $order->needs_payment() ) {
	        unset( $actions['pay'] );
	    }

	    if ( ! in_array( $order->get_status(), apply_filters( 'woocommerce_valid_order_statuses_for_cancel', array( 'pending', 'failed' ), $order ), true ) ) {
	        unset( $actions['cancel'] );
	    }

	    return $actions;

	}
}
