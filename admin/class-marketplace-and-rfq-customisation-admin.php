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
		/*
		* Manage seller related quries
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
		// handle cancel button on RFQ
		require_once __dir__.'/class-marketplace-and-rfq-customisation-admin-cancel-button.php';
		
		/*
		* Manage customer quotations related quries
		*/
		require_once __dir__.'/class-marketplace-and-rfq-customisation-customer-quotations.php';
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
	 * Order history template
	 *
	 * @since    1.0.0
	 */
	public function order_list_history_template(){
		return plugin_dir_path(__DIR__).'templates/seller/orders/order-list.php';
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
