<?php 

if ( ! class_exists( 'Womprfq_Cancel_Button_Handler' ) ) {
	/**
	 * Load front side functions.
	 */
	class Womprfq_Cancel_Button_Handler {
		public function __construct() { die("as");
			// Change RFQ status for logged in users
			add_action('wp_ajax_change_rfq_status', array($this,'change_rfq_status') );
			// Change RFQ status for non logged in users
			add_action('wp_ajax_nopriv_change_rfq_status', array($this,'change_rfq_status') );
			// Add cancel button inside main quote template
			add_action('womprfq_get_main_quote_template',array($this,'get_main_quote_template'),10,2);
		}
		

		public function change_rfq_status(){
   			global $wpdb;
		    $table  = $wpdb->prefix . 'womprfq_main_quotation';
		    $table1 = $wpdb->prefix . 'womprfq_seller_quotation';
		    $tblmeta = $wpdb->prefix . 'womprfq_main_quotation_meta';
		   
		    $id     = $_POST['id'];
		    $product     = $_POST['product'];
		    // Send mail to all other seller 

		    $sellers     = $wpdb->get_results("SELECT * FROM $table1 WHERE main_quotation_id = $id");
		    $result      = $wpdb->get_row("SELECT * FROM $table WHERE id = $id");

		    $customer_id = $result->customer_id;
		    $customer    = get_userdata($customer_id);
		    $customer_name = $customer->display_name;
		    $meta = $this->ys_get_quote_meta_info($id);
   
		    $admin_email = get_option('admin_email');
			
		    if(!empty($sellers)){
		        foreach($sellers as $seller){ 
		            if($seller->status == 4) continue;
		            $seller_id = $seller->seller_id;
		            $user = get_userdata($seller_id);
		            $to   = $user->user_email;
		            $email_heading  = 'Buyer\'s shopping request was withdrawn';
		            $subject = __(get_option('blogname'). ': Shopping request was cancelled');
		            $headers = array('Content-Type: text/html; charset=UTF-8');
					
					//modified code
					$smes[] = '<p>Hi ' . $user->display_name . ',</p>';
					$smes[] = "<p>Unfortunately customer <strong>$customer_name</strong> has cancelled their shopping request, so the offer you've made has moved to Closed status.</p>";
					$smes[] = "<p>Don't worry, there are more opportunities to earn. Make sure to subscribe to new shopping request alerts from your Profile.</p>";
					$smes[] = '<p>You can also view current shopping requests anytime from your <a href="' . home_url() . '/seller/manage-rfq/"><font color="#eb9a72">Personal Shopper dashboard > Manage Offers</font></a>.</p>';
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
										<p>Offer #' . $seller->id. '</p>
										<p>Buyer: ' . $customer_name . '</p>
										<p>Item: ' . $product . '</p>
										<p>Deliver To: '. esc_html( WC()->countries->countries[ $meta->quotation_country ] ) . '</p>
									</td>
								</tr>  
								<tr>
									<td align="center" bgcolor="#ffffff" height="1" valign="top" width="100%">
										<table cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px;">
											<tbody>
											<tr>
												<td style="text-align:center;" >
													<a href="' . site_url('seller/edit-rfq/'.$seller->id) . '" style="color: #ffff;background-color:#eb9a72;display:inline-block;font-size:16px;line-height:30px;text-align:center;text-decoration:none;padding:5px 20px;border-radius:3px; text-transform:none; margin:0 auto;margin-bottom:10px;margin-top:15px" class="link__btn">View your offer</a> 
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
							$smes[] = "<p>Don't forget to make an offer only if you're traveling to the Buyer's location.</p>";
							$smes[] = '<p>Read our <a href="' . home_url() . '/category/for-personal-shoppers/"><font color="#eb9a72">Tips and Guides</font></a> for more info, including how to propose a Tip for your service.</p>';
							$smes[] = '<p>&nbsp;</p>';					
							
							
							//$mailer = $woocommerce->mailer();
							ob_start();
							wc_get_template('emails/email-header.php', array('email_heading' => $email_heading));
							echo join('', $smes);
							wc_get_template('emails/email-footer.php');
							$msg = ob_get_clean();
							//$mailer->send($sdata['sendto'], $sdata['subject'], $msg);
					
					
					
		             $mailer = WC()->mailer();
		             $mailer->send( $to, $subject, $msg,$headers);
		        }
		    }
	
		    // Update or order status
		    $wpdb->query("UPDATE $table SET status=3 WHERE id=$id");
		    $wpdb->query("UPDATE $table1 SET status=4 WHERE main_quotation_id=$id");

		    wc_add_notice( esc_html__( 'Request has been cancelled', 'wk-marketplace' ), 'success' );
		    wp_die();
		}

		public function ys_get_quote_meta_info( $qid ) {
		    $res = false;
		    global $wpdb;
		    $tblmeta = $wpdb->prefix . 'womprfq_main_quotation_meta';
		    if ( $qid ) {
		        $query   = "SELECT * FROM $tblmeta WHERE main_quotation_id = $qid";
		        $results = $wpdb->get_results( $query );
		        if ( $results ) {
		            foreach ( $results as $result ) {
		                $res[ $result->key ] = $result->value;
		            }
		        }
		    }
		    return $res;
		}
		/**
		 * Add cancel button to main quote template.
		*/
		public function get_main_quote_template($data,$product){
			//JS edit. Cancel this request button on Buyers RFQ. Step 1
			if($data->customer_id == get_current_user_id() &&  $data->status != 3 ): ?> 
				<tr>
					<td colspan="2" id="cancel-button">
						<button class="markasclosed" data-product= "<?= $product; ?>" data-id ="<?= $data->id ; ?>">Cancel</button>
					</td>
				</tr>	
			
			<?php  endif; ?>
			<?php 
			if($data->status == 3) : 
				echo '<tr><td colspan="2">This request is now closed.</td></tr>';
			endif;
			//End 
		}
	}
}
?>