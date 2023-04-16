<div class="add-new-rfq">
	<div class="wk-mp-rfq-header">
		<h2>
			<?php echo ucfirst( esc_html__( 'Add New Product RFQ', 'wk-mp-rfq' ) ); //phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>
		</h2>
	</div>
	<div id="main_container" class="wk_transaction woocommerce-MyAccount-content wm-mp-rfq" style="display: contents;">
		<form method="POST" id="wpmp-rfq-new-quote-form" class="wpmp-rfq-new-quote-form" action=""
						style="display: flex;flex-direction: row;">
			<table class="form-table wc_status_table widefat">
				<tbody style="border: none;">
					<tr valign="top">
						<td class="forminp">
							<label for="product_name"><?php esc_html_e( 'Product Name', 'wk-mp-rfq' ); ?></label>
							<span class="required">*</span>
							<span class="description">What would you like to buy?
							<br/>See <a href="/which-products-are-allowed" target="_blank">item restrictions</a> and our <a href="/category/for-buyers" target="_blank">buyer's guide</a>.</span>
							<input type="text" class="input" name="wpmp-rfq-form-product-name" required="required" />
						</td>
					</tr>
					<tr valign="top">
						<td class="forminp">
							<label for="quantity"><?php esc_html_e( 'Quantity', 'wk-mp-rfq' ); ?></label>
							<span class="required">*</span>
							<span class="description">How many pieces?</span>
							<input type="number" class="input" name="wpmp-rfq-quote-quantity" id="wpmp-rfq-quote-quantity" min="1" required="required" />
						</td>
					</tr>
					<tr valign="top">
						<td class="forminp">
							<label for="qdesc"><?php esc_html_e( 'Item Description', 'wk-mp-rfq' ); ?></label>
						<span class="required">*</span>
						<span class="description">Enter a full description of item.</span>
						<textarea style="min-height: 90px;max-height: 80px;border-color: #edecec !important;border-radius:7px;margin-bottom: 10px !important;" rows="3" cols="20" rows="6" cols="23" id="wpmp-rfq-quote-desc"  required="required" class="regular-text" name="wpmp-rfq-quote-desc"></textarea>
						<?php echo wc_help_tip( esc_html__( 'Enter text to add desc to quote.', 'wk-mp-rfq' ), false ); ?>
						</td>
					</tr>
					<tr valign="top">
						<td class="forminp">
							<div style="margin-right: 10%;">
								<label for="qdesc"><?php esc_html_e( 'Add Sample Image', 'wk-mp-rfq' ); ?></label>
								<span class="description">Optional (max 200kb)</span>
							</div>
							<div>
								<div id="wpmp-rfq-form-image">
								</div>
								<input type="hidden"  id="wpmp-rfq-form-sample-img" name="wpmp-rfq-form-sample-img" />
								<p>
									<a class="wpmp-rfq-form-upload-button" id="wpmp-rfq-form-upload-button" data-type-error="<?php echo esc_attr__( 'Only jpg|png|jpeg files are allowed.', 'wk-mp-rfq' ); ?>" href="javascript:void(0);" />
										<?php esc_html_e( 'Add Images', 'wk-mp-rfq' ); ?>
									</a>
								</p>
							</div>
							<div id="wpmp-rfq-form-sample-img-error" class="error-class"></div>
						</td>
					</tr>
					<tr valign="top">
						<td class="forminp">
							<label for="deliver"><?php esc_html_e( 'Deliver To', 'marketplace-and-rfq-customisation' ); ?></label>
						<span class="required">*</span>
						<span class="description"><?php esc_html_e( 'Select Deliver Place.', 'marketplace-and-rfq-customisation' ); ?></span>
						<select name="wkmp_quotation_country" id="billing-country" class="input" oninvalid="this.setCustomValidity('You need to select the country in the list.')" oninput="this.setCustomValidity('')" required>
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
						<?php echo wc_help_tip( esc_html__( 'Select Country to quote.', 'wk-mp-rfq' ), false ); ?>
						</td>
					</tr>
					<?php 
						if ( $attributes ) {
							foreach ( $attributes as $attribute ) {
								if ( 1 === intval( $attribute->status ) ) {
									if ( 2 === intval( $attribute->required ) ) {
										$require = 'required="required"';
									} else {
										$require = '';
									}
									?>
									<tr valign="top">
										<td class="forminp">
											<label for="<?php echo esc_attr( wc_strtolower( $attribute->label ) ); ?>"><?php echo esc_html( ucfirst( $attribute->label ) ); ?></label>
											<?php
											if ( $require ) {
												?>
												<span class="required">*</span>
												<?php
											}
											?>
										<span class="description">Enter <?php echo esc_html(ucfirst($attribute->label)); ?></span>
										<input class="input" type="<?php echo esc_attr( $attribute->type ); ?>" name="wpmp-rfq-admin-quote-<?php echo esc_attr( wc_strtolower( $attribute->label ) ); ?>" <?php echo esc_html( $require ); ?> <?php echo ( 'number' === $attribute->type ) ? 'min="0"' : ''; ?>>
										<div id="wpmp-rfq-quote-<?php echo esc_attr( wc_strtolower( $attribute->label ) ); ?>-error" class="error-class"></div>
										</td>
									</tr>
									<?php
								}
							}
						}
					?>
					<tr valign="top">
						<td colspan="2" class="forminp submit-box">
							<?php wp_nonce_field( 'wc-customer-quote-nonce-action', 'wc-customer-quote-nonce' ); ?>
							<input type="submit" name="update-customer-new-quotation-submit" value="<?php echo esc_attr__( 'Submit request', 'wk-mp-rfq' ); ?>" class="button button-primary input-submit" />
						</td>
					</tr>
				</tbody>
			</table>
			<div class="bg-image-container">
				<img src="<?php echo plugin_dir_url(__FILE__).'rfq-image.png'; ?>" />
			</div>
		</form>
	</div>
</div>
<style type="text/css">
	.wk-mp-rfq-header {
	    display: none;
	}
	div#main_container{
		display: none !important;
	}
	.add-new-rfq .wk-mp-rfq-header {
	    display: block;
	}
	.add-new-rfq div#main_container{
		display: contents !important;
	}
</style>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.wk-mp-rfq').remove();
	});
</script>