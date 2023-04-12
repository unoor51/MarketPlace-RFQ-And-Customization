<?php 

if ( ! class_exists( 'Womprfq_Quotation_Handler' ) ) {
	/**
	 * Load front side functions.
	 */
	class Womprfq_Quotation_Handler {

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

			add_action( 'wkmprfq_after_customer_new_product_submit_form', array( $this, 'wkmprfq_after_customer_new_product_submit_form_handler' ), 10, 3 );
		}

		/**
		 * Customer New Product form handler.
		 *
		 * @param array $postdta post data.
		 */
		public function wkmprfq_after_customer_new_product_submit_form_handler( $postdta ) {
			
			if ( $postdta && isset( $postdta['update-customer-new-quotation-submit'] ) ) {
				if ( ! empty( $postdta['wc-customer-quote-nonce'] ) && wp_verify_nonce( wp_unslash( $postdta['wc-customer-quote-nonce'] ), 'wc-customer-quote-nonce-action' ) ) {

					if ( isset( $postdta['wpmp-rfq-form-product-name'] ) && ! empty( $postdta['wpmp-rfq-form-product-name'] ) ) {
						if ( preg_match( '/^[a-zA-Z0-9_ ]*$/', $postdta['wpmp-rfq-form-product-name'] ) ) {
							$q_pro_name = sanitize_text_field( $postdta['wpmp-rfq-form-product-name'] );
						} else {
							wc_print_notice( esc_html__( 'Use only Alphabets, numbers and underscore for product name.', 'wk-mp-rfq' ), 'error' );
						}
					} else {
						$q_pro_name = '';
					}
					if ( isset( $postdta['wpmp-rfq-quote-quantity'] ) && ! empty( $postdta['wpmp-rfq-quote-quantity'] ) ) {
						$q_pro_qty = intval( $postdta['wpmp-rfq-quote-quantity'] );
					} else {
						$q_pro_qty = 0;
					}
					if ( isset( $postdta['wpmp-rfq-quote-desc'] ) && ! empty( $postdta['wpmp-rfq-quote-desc'] ) ) {
						$q_pro_desc = sanitize_text_field( stripslashes( $postdta['wpmp-rfq-quote-desc'] ) );
					} else {
						$q_pro_desc = '';
					}
					if ( isset( $postdta['wpmp-rfq-form-sample-img'] ) && ! empty( $postdta['wpmp-rfq-form-sample-img'] ) ) {
						$q_pro_img = $postdta['wpmp-rfq-form-sample-img'];
					} else {
						$q_pro_img = '';
					}

					$admin_attrs = preg_grep( '/^(.*)wpmp-rfq-admin-quote-(.*)$/', array_keys( $postdta ) );
					$attr        = array();
					if ( $admin_attrs ) {
						foreach ( $admin_attrs as $admin_attr ) {
							if ( isset( $postdta[ $admin_attr ] ) && ! empty( $postdta[ $admin_attr ] ) ) {
								$attr[ $admin_attr ] = $postdta[ $admin_attr ];
							}
						}
					}

					if ( ! empty( $q_pro_name ) && $q_pro_qty > 0 && ! empty( $q_pro_desc ) ) {
						if ( intval( $this->helper->quote_min_qty ) <= $q_pro_qty ) {
							$quote_data       = array(
								'product_id'   => 0,
								'variation_id' => 0,
								'quantity'     => $q_pro_qty,
								'customer_id'  => get_current_user_id(),
							);
							$attr['image']    = $q_pro_img;
							$attr['pro_name'] = $q_pro_name;
							$attr['pro_desc'] = $q_pro_desc;

							$response = $this->womprfq_addnew_main_quotation( $quote_data, '', $attr );

							if ( $response ) {
								wc_add_notice( esc_html__( 'Quotation Added Successfully', 'wk-mp-rfq' ), 'success' );

								wp_safe_redirect( esc_url( wc_get_page_permalink( 'myaccount' ) . 'main-quote/' . intval( $response ) ) );
								exit;
							}
						} else {
							wc_print_notice( esc_html__( 'Minimum Quantity required for quote is ', 'wk-mp-rfq' ) . intval( $this->helper->quote_min_qty ) . '.', 'error' );
						}
					} else {
						//wc_print_notice( esc_html__( 'Please Enter Valid Details.', 'wk-mp-rfq' ), 'error' );
					}
				}
			}
		}

		/**
		 * Insert main quotation.
		 *
		 * @param array $data data.
		 * @param int   $order_id order id.
		 * @param array $attr attr.
		 */
		public function womprfq_addnew_main_quotation( $data, $order_id, $attr ) {
			$response = false;
			if ( $data ) {
				if ( $this->admin_approval ) {
					$status = 0;
				} else {
					$status = 1;
				}

				if ( $order_id ) {
					$sql = $this->wpdb->insert(
						$this->main_quote_table,
						array(
							'product_id'   => intval( $data['product_id'] ),
							'variation_id' => intval( $data['variation_id'] ),
							'quantity'     => intval( $data['quantity'] ),
							'customer_id'  => intval( $data['customer_id'] ),
							'order_id'     => intval( $order_id ),
							'status'       => intval( $status ),
						)
					);
				} else {
					$sql = $this->wpdb->insert(
						$this->main_quote_table,
						array(
							'product_id'   => intval( $data['product_id'] ),
							'variation_id' => intval( $data['variation_id'] ),
							'customer_id'  => intval( $data['customer_id'] ),
							'quantity'     => intval( $data['quantity'] ),
							'status'       => intval( $status ),
						)
					);
				}
				if ( $sql ) {
					$id = $this->wpdb->insert_id;
					do_action( 'womprfq_save_quotation_meta', $id, $attr );
					// to customer.
					$cmes  = array(
						esc_html__( 'A new request has been submitted by you.', 'wk-mp-rfq' ),
					);
					$cmes  = $this->womprfq_get_mail_quotation_detail( $id, $cmes );
					$cdata = array(
						'msg'     => $cmes,
						'sendto'  => get_user_by( 'ID', $data['customer_id'] )->user_email,
						'heading' => esc_html__( 'New Request For Quotation', 'wk-mp-rfq' ),
					);
					do_action( 'womprfq_quotation', $cdata );

					// to seller.
					if ( ! $this->admin_approval ) {
						$this->womprfq_notify_sellers_for_quote( $this->womprfq_get_main_quotation_by_id( $id ), $id );
					}

					// to admin.
					$ames  = array(
						esc_html__( 'A new request has been submitted by ', 'wk-mp-rfq' ) . get_user_by( 'ID', $data['customer_id'] )->user_login . '.',
					);
					$ames  = $this->womprfq_get_mail_quotation_detail( $id, $ames );
					$adata = array(
						'msg'     => $ames,
						'sendto'  => get_option( 'admin_email' ),
						'heading' => esc_html__( 'New Request For Quotation', 'wk-mp-rfq' ),
					);
					do_action( 'womprfq_quotation', $adata );

					return apply_filters( 'womprfq_addnew_main_quotation', $id );
				}
			}

			return apply_filters( 'womprfq_addnew_main_quotation', $response );
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

			// $res_id       = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT id FROM $this->seller_quote_table  WHERE main_quotation_id = %d ORDER BY id ASC", intval( $id ) ) );
			// if ( $res_id ) {
			// $sel_quote_id = $res_id[0]->id;
			// }
			// $response = array();

			// if ( intval( $sel_quote_id ) ) {
			// $msg = '';
			// $res = $wpdb_obj->get_results( $wpdb_obj->prepare( "SELECT * FROM $this->seller_quote_comment_table WHERE seller_quotation_id = %d ORDER BY id ASC", intval( $sel_quote_id ) ) );
			// if ( $res ) {
			// foreach ( $res as $result ) {
			// $response[] = array(
			// 'id'           => intval( $result->id ),
			// 'sender_id'    => intval( $result->sender_id ),
			// 'image'        => $result->image,
			// 'comment_text' => html_entity_decode( $result->comment_text ),
			// 'date'         => $result->date,
			// );
			// }
			// }
			// }
			$size = count( $response );
			if ( $size > 0 ) {
				$msg = $response[ $size - 1 ]['comment_text'];
			}

			return apply_filters( 'womprfq_get_seller_quote_comment_details_for_mail', $msg );
		}

		/**
		 * Notify seller for quote.
		 *
		 * @param array $qdata quote data.
		 * @param int   $qid quote id.
		 */
		public function womprfq_notify_sellers_for_quote( $qdata, $qid ) {
			
			//JS edit. New Emails - part 1.7
			global $woocommerce;
			
			// JS edit. Add country and city drop down filter and country preference. Step 2
			$q_meta_data = $this->womprfq_get_quote_meta_info( $qid );
			$customer_id = intval( $qdata->customer_id );
			$users       = get_users(
				array(
					'role'    => 'wk_marketplace_seller',
					'exclude' => array( $customer_id ),
					
					// JS edit. Add country and city drop down filter and country preference. Step 3
					'meta_query' => array(
						'key'     => 'subscribe_country',
						'value'   => $q_meta_data['quotation_country'],
						'compare' => '='
					)
				)
			);
			foreach ( $users as $user ) {
				
				// JS edit. Add country and city drop down filter and country preference. Step 4
				$subscribe_country = get_user_meta($user->ID, 'subscribe_country', true );
				if($subscribe_country == "all"){
				
				if ( $user->user_email && ( $user->ID != $customer_id ) ) {
					$smes  = array(
						
						//JS edit. New Emails - part 1.8
						'<p>Hi ' . $user->user_login . ',</p>',
						'<p>A new shopping  request has been submitted by ' . get_user_by('ID', $customer_id)->user_login . '.</p>',
						'<p>If you\'re traveling to their location, why not make an offer to earn some cash?</p>',
						//JS edit. New RFQ email to sellers - Negotiate date
						'<p>Try to negotiate the delivery or meet-up date to match your travel plans.</p>',
						
					);
					
					//JS edit. New Emails - part 1.9
					$mdata =  (array) $this->womprfq_get_quote_meta_info($qid);

					$customer = get_userdata($customer_id);

					if ($qdata->product_id != 0) {
						if ($qdata->variation_id != 0) {
							$product_name = get_the_title($qdata->variation_id);
						} else {
							$product_name = get_the_title($qdata->product_id);
						}
					} else {
						$product_name = $mdata['pro_name'];
					}
					$quantity = $qdata->quantity;

					$delivery_location  = $mdata['wpmp-rfq-admin-quote-your_location_(city_and_country)'];

					$smes[] = '<table style="padding-bottom:20px;width:100%;">
						<tbody>
							<tr>
								<td align="center" bgcolor="#ffffff" height="1" style="padding:30px 40px 5px" valign="top" width="100%">
									<table cellpadding="0" cellspacing="0" width="100%">
										<tbody>
											<tr>
												<td style="border-top:1px solid #e4e4e4"> </td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td class="content" style="padding:10px 0px 0px 40px">
									<p>Request #' . $qid . '</p>
									<p>Item: ' . $product_name . '</p>
									<p>Quantity: ' . $quantity . '</p>
									<p>Buyer: ' . $customer->data->user_login . '</p>
									<p>Deliver to: ' . esc_html($mdata['wpmp-rfq-admin-quote-blocation']) . '</p>
								</td>
							</tr>  
							<tr>
								<td align="center" bgcolor="#ffffff" height="1" valign="top" width="100%">
									<table cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px;">
										<tbody>
										<tr>
											<td style="text-align:center;" >
												<a href="' . home_url() . '/seller/add-quote/' . $qid . '" style="color: #ffffff;background-color:#eb9a72;display:inline-block;font-size:16px;line-height:30px;text-align:center;text-decoration:none;padding:5px 20px;border-radius:3px; text-transform:none; margin:0 auto;margin-bottom:10px;margin-top:15px" class="link__btn">View or Make an Offer</a> 
											</td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr> 
							<tr>
								<td align="center" bgcolor="#ffffff" height="1" style="padding:20px 40px 5px" valign="top" width="100%">
									<table cellpadding="0" cellspacing="0" width="100%">
										<tbody>
											<tr>
												<td style="border-top:1px solid #e4e4e4"> </td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table> ';
					$smes[] = '<p>Be quick! If the Buyer accepts an offer from another Seller, the request will move to Closed.</p>';
					$smes[] = '<p>Read our <a href="' . home_url() . '/category/for-personal-shoppers"><font color="#eb9a72">Tips and Guides</font></a> for more info, including how to propose a Tip for your service.</p>';
					$smes[] = '<p>&nbsp;</p>';					
					
					
					$sdata = array(
						
						//JS edit. New Emails - part 1.10
						'msg'     => join('', $smes),
						
						'sendto'  => $user->user_email,
						
						//JS edit. New Emails - part 1.11
						'heading' => esc_html__('Is it time to earn?', 'wk-mp-rfq'),
						'subject' => esc_html__('Eatr: New shopping request', 'wk-mp-rfq')
						
					);
					
					//JS edit. New Emails - part 1.12
					$mailer = $woocommerce->mailer();
					ob_start();
					wc_get_template('emails/email-header.php', array('email_heading' => $sdata['heading']));
					echo $sdata['msg'];
					wc_get_template('emails/email-footer.php');
					$msg = ob_get_clean();
					$mailer->send($sdata['sendto'], $sdata['subject'], $msg);
					
				}
			
			// JS edit. Add country and city drop down filter and country preference. Step 5
			}else{
				
					if ( $user->user_email && ( $user->ID != $customer_id ) && $subscribe_country == $q_meta_data['quotation_country'] ) {
						$smes  = array(
							esc_html__( 'A new requested has been submitted by ', 'wk-mp-rfq' ) . get_user_by( 'ID', $customer_id )->user_login . '.',
						);
						$smes  = $this->womprfq_get_mail_quotation_detail( $qid, $smes );
						$sdata = array(
							'msg'     => $smes,
							'sendto'  => $user->user_email,
							'heading' => esc_html__( 'New Request For Quotation', 'wk-mp-rfq' ),
						);
						do_action( 'womprfq_quotation', $sdata );
					}
				}
			
			}
		}
	}
	new Womprfq_Quotation_Handler();
}