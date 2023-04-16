<?php 

if ( ! class_exists( 'Womprfq_Seller_Quotation' ) ) {
	/**
	 * Load front side functions.
	*/
	class Womprfq_Seller_Quotation {
		/**
		 * Database global variable.
		 *
		 * @var object $wpdb wpdb.
		 */
		protected $wpdb;
		/**
		 * Main Quote table variable.
		 *
		 * @var string $main_quote_table main_quote_table.
		 */
		protected $main_quote_table;
		/**
		 * Main Quote Meta table variable.
		 *
		 * @var string $main_quote_meta_table main_quote_meta_table.
		 */
		protected $main_quote_meta_table;
		/**
		 * Seller Quote table variable.
		 *
		 * @var string $seller_quote_table seller_quote_table.
		 */
		protected $seller_quote_table;
		/**
		 * Seller Quote Comment table variable.
		 *
		 * @var string $seller_quote_comment_table seller_quote_comment_table.
		 */
		protected $seller_quote_comment_table;
		/**
		 * Seller Quotation Comment table variable.
		 *
		 * @var string $seller_quotation_comment_table seller_quotation_comment_table.
		 */
		protected $seller_quotation_comment_table;
		/**
		 * Enabled variable.
		 *
		 * @var bool $enabled enabled.
		 */
		public $enabled;
		/**
		 * Quote minimum qty variable.
		 *
		 * @var int $quote_min_qty quote_min_qty.
		 */
		public $quote_min_qty;
		/**
		 * Admin approval variable.
		 *
		 * @var bool $admin_approval admin_approval.
		 */
		public $admin_approval;
		/**
		 * Endpoint variable.
		 *
		 * @var string $endpoint endpoint.
		 */
		public $endpoint;
		public function __construct() {
			global $wpdb;
			$this->wpdb                           = $wpdb;
			$this->endpoint                       = 'rfq';
			$this->posts                          = $wpdb->posts;
			$this->main_quote_table               = $wpdb->prefix . 'womprfq_main_quotation';
			$this->main_quote_meta_table          = $wpdb->prefix . 'womprfq_main_quotation_meta';
			$this->seller_quote_comment_table     = $wpdb->prefix . 'womprfq_seller_quotation_comment';
			$this->seller_quote_table             = $wpdb->prefix . 'womprfq_seller_quotation';
			$this->seller_quotation_comment_table = $wpdb->prefix . 'womprfq_seller_quotation_comment';
			// Save seller quotation form
			add_action( 'womprfq_seller_quotation_save_form', array( $this, 'seller_quotation_save_form_handler' ), 10, 3 );
		}
		
		/**
		 * Seller Quotation save.
		 *
		 * @param array  $postdta post data.
		 * @param int    $sq_id seller quote id.
		 * @param string $action action.
		*/
		public function seller_quotation_save_form_handler( $postdta, $sq_id, $action ) {
			global $wkmarketplace;

			if ( $postdta && $sq_id ) {
				if ( isset( $postdta['update-seller-new-quotation-submit'] ) ) {
					if ( ! empty( $postdta['wc-seller-quote-nonce'] ) && wp_verify_nonce( wp_unslash( $postdta['wc-seller-quote-nonce'] ), 'wc-seller-quote-nonce-action' ) ) {
						$is_comment = false;
						if ( isset( $postdta['seller-quote-comment'] ) && ! empty( $postdta['seller-quote-comment'] ) ) {
							$q_comment  = stripslashes( $postdta['seller-quote-comment'] );
							$is_comment = true;
						}
						if ( isset( $postdta['seller-quote-comment-image'] ) && ! empty( $postdta['seller-quote-comment-image'] ) ) {
							$q_comnt_img = $postdta['seller-quote-comment-image'];
						} else {
							$q_comnt_img = null;
						}

						if ( isset( $postdta['seller-quote-quantity'] ) && ! empty( $postdta['seller-quote-quantity'] ) && $postdta['seller-quote-quantity'] >= 0 ) {
							$q_quantity = $postdta['seller-quote-quantity'];
						}
						if ( isset( $postdta['seller-quote-price'] ) && ! empty( $postdta['seller-quote-price'] ) && $postdta['seller-quote-price'] >= 0 ) {
							$q_price = $postdta['seller-quote-price'];
						}
						if ( ! empty( $q_quantity ) && ! empty( $q_price ) ) {
							$is_comment = false;
							if ( isset( $postdta['seller-quote-comment'] ) && ! empty( $postdta['seller-quote-comment'] ) ) {
								$q_comment  = stripslashes( $postdta['seller-quote-comment'] );
								$is_comment = true;
							}
							if ( isset( $postdta['seller-quote-comment-image'] ) && ! empty( $postdta['seller-quote-comment-image'] ) ) {
								$q_comnt_img = $postdta['seller-quote-comment-image'];
							} else {
								$q_comnt_img = null;
							}
							$page_name = 'my-account/';
							//JS edit. Maximum quotation amount
							if ( $action == 'add' && $q_quantity * $q_price > 10000 ) {
							wc_add_notice( esc_html__( 'Sorry, your offer exceeds the maximum allowed right now (PHP 10,000)', 'wk-mp-rfq' ), 'error' );
							wp_safe_redirect( esc_url( site_url( esc_html( $page_name ) . '/add-seller-quote/' . intval( $sq_id ) ) ) );
							 	die;
							}							
							if ( $action == 'edit' && $q_quantity * $q_price > 10000 ) {
							wc_add_notice( esc_html__( 'Sorry, your offer exceeds the maximum allowed right now (PHP 10,000)', 'wk-mp-rfq' ), 'error' );
							 	wp_safe_redirect( esc_url( site_url( esc_html( $page_name ) . '/edit-rfq/' . intval( $sq_id ) ) ) );
							 	die;
							 }

							$info     = array(
								'id'       => $sq_id,
								'price'    => $q_price,
								'quantity' => $q_quantity,
								'status'   => 2,
							);
							$save_res = $this->womprfq_update_seller_quotation( $info, $action );
							// customization.
							do_action( 'womprfqc_seller_quotation_save_form', $postdta, $save_res['seller_quote_id'], $action );
							if ( $save_res['status'] || $is_comment ) {
								if ( 'add' === $action && isset( $save_res['seller_quote_id'] ) && intval( $save_res['seller_quote_id'] ) > 0 ) {
									$sq_id = intval( $save_res['seller_quote_id'] );
								}
								if ( $is_comment ) {
									$comment_info = array(
										'seller_quotation_id' => $sq_id,
										'comment_text' => stripslashes( $q_comment ),
										'sender_id'    => get_current_user_id(),
										'image'        => $q_comnt_img,
									);
									$this->womprfq_update_seller_quotation_comment( $comment_info );
								}
								$main_quote_id = $this->womprfq_get_main_quote_id_for_customer( $sq_id );
								$smes          = array(
									esc_html__( 'Quotation status has been updated by Seller', 'wk-mp-rfq' ) . ' ( #' . intval( $main_quote_id ) . ' ).',
								);
								$sel_q_data    = $this->womprfq_get_seller_quotation_details( $sq_id );
								if ( $sel_q_data ) {
									$main_dat = $this->get_main_quotation_by_id( $sel_q_data->main_quotation_id );
									$smes     = $this->womprfq_get_mail_quotation_detail( $sel_q_data->main_quotation_id, $smes );
									if ( $main_dat ) {
										$user = get_user_by( 'ID', $main_dat->customer_id );
										if ( $user ) {
											$sdata = array(
												'msg'     => $smes,
												'sendto'  => $user->user_email,
												'heading' => esc_html__( 'Quotation Updated', 'wk-mp-rfq' ),
											);
											do_action( 'womprfq_quotation', $sdata );
										}
									}
								}if ( is_admin() ) {
									if ( 'add' === $action ) {
										echo '<div class="wrap"><div class="notice notice-success"><p>' . esc_html__( 'Quotation Added Successfully!', 'wk-mp-rfq' ) . '</p></div>';
										wp_safe_redirect( 'admin.php?page=manage-rfq&action=edit-rfq&id=' . intval( $sq_id ) );
										exit;

									}
									echo '<div class="wrap"><div class="notice notice-success"><p>' . esc_html__( 'Quotation Updated successfully.', 'wk-mp-rfq' ) . '</p></div>';
								} else {
									if ( 'add' === $action ) {
										wc_add_notice( esc_html__( 'Quotation Added successfully.', 'wk-mp-rfq' ), 'success' );
										if ( defined( 'MARKETPLACE_VERSION' ) && version_compare( MARKETPLACE_VERSION, '5.2.0', '>=' ) ) {
											wp_safe_redirect( esc_url( site_url( get_query_var( 'pagename' ) . '/edit-rfq/' . intval( $sq_id ) ) ) );
											exit;
										} else {
											wp_safe_redirect( esc_url( site_url( $wkmarketplace->seller_page_slug . '/edit-rfq/' . intval( $sq_id ) ) ) );
											exit;
										}
										die;

									}
									wc_print_notice( esc_html__( 'Quotation Updated successfully.', 'wk-mp-rfq' ), 'success' );
								}
							} else {
								if ( ! empty( $save_res['msg'] ) ) {
									foreach ( $save_res['msg'] as $msgg ) {
										if ( 'error' === $msgg['status'] ) {
											$stat = 'error';
										} else {
											$stat = 'success';
										}
										wc_print_notice( esc_html( $msgg['msg'] ), $stat );
									}
								}
							}
						} else {
							wc_print_notice( esc_html__( 'Please Enter Valid Details.', 'wk-mp-rfq' ), 'error' );
						}
					}
				}
			}
		}
		
		/**
		 * Update Seller Quote.
		 *
		 * @param array $info info.
		 * @param array $action action.
		 */
		public function womprfq_update_seller_quotation( $info, $action = '' ) {

			$response = array(
				'status' => false,
				'msg'    => array(),
			);

			$err = array();
			if ( $info && isset( $info['id'] ) && ! empty( $info['id'] ) && isset( $info['price'] ) && ! empty( $info['price'] ) && isset( $info['quantity'] ) && ! empty( $info['quantity'] ) && isset( $info['status'] ) && ! empty( $info['status'] ) ) {
				if ( 'add' === $action ) {
					$res = $this->wpdb->insert(
						$this->seller_quote_table,
						array(
							'main_quotation_id' => $info['id'],
							'seller_id'         => get_current_user_id(),
							'price'             => $info['price'],
							'quantity'          => $info['quantity'],
							'status'            => 0,
						)
					);
					if ( $res ) {
						$info['id'] = $this->wpdb->insert_id;
					}
				} else {
					$res = $this->wpdb->update(
						$this->seller_quote_table,
						array(
							'price'    => $info['price'],
							'quantity' => $info['quantity'],
							'status'   => $info['status'],
						),
						array(
							'id' => $info['id'],
						)
					);

				}
				if ( $res ) {
					$response['status']          = true;
					$response['seller_quote_id'] = $info['id'];
				} else {
					$response['status'] = false;
					$response['msg'][]  = array(
						'status' => 'error',
						'msg'    => esc_html__( 'Enter Details to change', 'wk-mp-rfq' ),
					);
				}
			}

			return apply_filters( 'womprfq_update_seller_quotation', $response );
		}

		/**
		 * Update Seller Comment.
		 *
		 * @param array $comment_info comment data.
		 */
		public function womprfq_update_seller_quotation_comment( $comment_info ) {
			$response = false;
			if ( $comment_info ) {
				$sql = $this->wpdb->insert(
					$this->seller_quotation_comment_table,
					$comment_info
				);
				if ( $sql ) {
					$response = $sql;
				}
			}
			return apply_filters( 'womprfq_update_seller_quotation_comment', $response );
		}
		/**
		 * Get Main Quote id for customer mail.
		 *
		 * @param int $id id.
		*/
		public function womprfq_get_main_quote_id_for_customer( $id ) {
			$wpdb_obj     = $this->wpdb;
			$sel_quote_id = $id;
			$res_id       = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT main_quotation_id FROM $this->seller_quote_table WHERE id = %d", intval( $id ) ) );

			if ( $res_id ) {
						$sel_quote_id = $res_id[0]->main_quotation_id;
			}

			return apply_filters( 'womprfq_get_main_quote_id_for_customer', $sel_quote_id, $id );
		}

		/**
		 * Get Seller Quote Details.
		 *
		 * @param int $sqid seller quote id.
		 */
		public function womprfq_get_seller_quotation_details( $sqid ) {
			$wpdb_obj = $this->wpdb;
			$response = false;
			if ( $sqid ) {
				$query = $wpdb_obj->prepare(
					"SELECT * FROM  $this->seller_quote_table where id=%s",
					intval( $sqid )
				);
				$res   = $wpdb_obj->get_row( $query );

				if ( $res ) {
					$response = $res;
				}
			}
			return apply_filters( 'womprfq_get_seller_quotation_details', $response, $sqid );
		}

		/**
		 * Get main Quote by id.
		 *
		 * @param int $qid quote id.
		 */
		public function get_main_quotation_by_id( $qid ) {
			$response = false;
			$wpdb_obj = $this->wpdb;
			if ( $qid ) {
				$query = $wpdb_obj->prepare( "SELECT * FROM $this->main_quote_table WHERE id = %d", $qid );
				$res   = $wpdb_obj->get_row( $query );
				if ( $res ) {
					$response = $res;
				}
			}
			return apply_filters( 'womprfq_get_main_quotation_by_id', $response, $qid );
		}

		/**
		 * Get mail Quotation detail.
		 *
		 * @param int   $qid quote id.
		 * @param mixed $res response.
		 */
		public function womprfq_get_mail_quotation_detail( $qid, $res ) {
			if ( intval( $qid ) > 0 ) {
				$data  = $this->womprfq_get_main_quotation_by_id( $qid );
				$mdata = (object) $this->womprfq_get_quote_meta_info( $qid );
				if ( ! strpos( $res[0], 'new' ) ) {
					$quote = $this->womprfq_get_seller_quote_comment_details_for_mail( $qid );
				}

				if ( $data ) {
					if ( 0 !== intval( $data->product_id ) ) {
						if ( 0 !== intval( $data->variation_id ) ) {
							$pro          = wc_get_product( $data->variation_id );
							$product_name = $pro->get_title();
						} else {
							$pro          = wc_get_product( $data->product_id );
							$product_name = $pro->get_title();
						}
					} else {
						$product_name = $mdata->pro_name;
					}
					$quantity = $data->quantity;

					$res[] = esc_html__( 'Please find the following details :', 'wk-mp-rfq' );
					$res[] = esc_html__( 'Requested Quote Topic : ', 'wk-mp-rfq' ) . esc_html( $product_name );
					$res[] = esc_html__( 'Bulk Quantity Requested : ', 'wk-mp-rfq' ) . esc_html( $quantity );
					if ( ! strpos( $res[0], 'new' ) ) {
						$res[] = esc_html__( 'Comment : ', 'wk-mp-rfq' ) . esc_html( $quote );
					}
				}
			}
			return apply_filters( 'womprfq_get_mail_quotation_detail', $res, $qid );
		}
		/**
		 * Get main Quote by id.
		 *
		 * @param int $qid quote id.
		 */
		public function womprfq_get_main_quotation_by_id( $qid ) {
			$response = false;
			$wpdb_obj = $this->wpdb;
			if ( $qid ) {
				$query = $wpdb_obj->prepare( "SELECT * FROM $this->main_quote_table WHERE id = %d", $qid );
				$res   = $wpdb_obj->get_row( $query );
				if ( $res ) {
					$response = $res;
				}
			}
			return apply_filters( 'womprfq_get_main_quotation_by_id', $response, $qid );
		}
		/**
		 * Get Quote meta.
		 *
		 * @param int $qid quote id.
		*/
		public function womprfq_get_quote_meta_info( $qid ) {
			$wpdb_obj = $this->wpdb;
			$res      = false;
			if ( $qid ) {
				$query   = $wpdb_obj->prepare( "SELECT * FROM $this->main_quote_meta_table WHERE main_quotation_id = %d", $qid );
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
		 * Seller Comment for mail.
		 *
		 * @param int $id quote id.
		*/
		public function womprfq_get_seller_quote_comment_details_for_mail( $id ) {
			$wpdb_obj     = $this->wpdb;
			$sel_quote_id = $id;
			$response     = array();
			$msg          = '';
			$seller_id    = get_current_user_id();
			$res          = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM $this->seller_quote_comment_table WHERE sender_id = %d ORDER BY id ASC", intval( $seller_id ) ) );
			if ( $res ) {
				foreach ( $res as $result ) {
					$response[] = array(
						'id'           => intval( $result->id ),
						'sender_id'    => intval( $result->sender_id ),
						'image'        => $result->image,
						'comment_text' => html_entity_decode( $result->comment_text ),
						'date'         => $result->date,
					);
				}
			}
			$size = count( $response );
			if ( $size > 0 ) {
				$msg = $response[ $size - 1 ]['comment_text'];
			}

			return apply_filters( 'womprfq_get_seller_quote_comment_details_for_mail', $msg );
		}

	}
	new Womprfq_Seller_Quotation();
}

?>