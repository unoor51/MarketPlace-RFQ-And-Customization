<?php 
	$subscribe_email = get_user_meta($seller_info['wkmp_seller_id'],'subscribe_email',true );
	$subscribe_country = get_user_meta($seller_info['wkmp_seller_id'],'subscribe_country',true );
	$seller_info['wkmp_subscribe_email'] = !empty( $subscribe_email ) ? $subscribe_email : '';

	$seller_info['wkmp_subscribed_country'] = !empty( $subscribe_country ) ? $subscribe_country : '';
 ?>
<div class="mrc-country-box">	
	<div class="form-group <?php if($seller_info['wkmp_subscribed_country'] != '') { echo 'w-50'; }else{ echo 'w-50';} ?> " id="wk-seller-subscribe-email-box">
		<label><?php esc_html_e( 'Subscribed E-Mail', 'marketplace-and-rfq-customisation' ); ?></label>
			<p>
				<input type="checkbox" id="wk-seller-subscribe-email" name="wkmp_subscribe_email" value="yes" <?php echo ( 'yes' === $seller_info['wkmp_subscribe_email'] ) ? 'checked' : ''; ?>> <label for="wk-seller-banner-status"><?php esc_html_e( 'Received email on customer order request', 'marketplace-and-rfq-customisation' ); ?> </label>
			</p>
	</div>
	<div class="form-group w-50 subscribed_country" id="subscribed_country" <?php echo ( 'yes' === $seller_info['wkmp_subscribe_email'] ) ? 'style="display:block"' : 'style="display:none"'; ?> >
		<label for="subscribed-country"><?php esc_html_e( 'Country', 'marketplace-and-rfq-customisation' ); ?></label>
		<select name="wkmp_subscribed_country" id="subscribed-country" class="form-control" oninvalid="this.setCustomValidity('You need to select the country in the list.')" oninput="this.setCustomValidity('')" <?php echo ( 'yes' == $seller_info['wkmp_subscribe_email'] ) ? 'required' : ''; ?> >
			<option value=""><?php esc_html_e( 'Select Country', 'marketplace-and-rfq-customisation' ); ?></option>
			<option value="all" <?php if($seller_info['wkmp_subscribed_country'] == "all" ){ echo "selected"; }  ?> ><?php esc_html_e( 'All', 'marketplace-and-rfq-customisation' ); ?></option>
			<?php
			$countries_obj = new \WC_Countries();
			$countries     = $countries_obj->__get( 'countries' );
			foreach ( $countries as $key => $country ) {
				?>
				<?php if ( $key === $seller_info['wkmp_subscribed_country'] ) { ?>
					<option value="<?php echo esc_attr( $key ); ?>" selected><?php echo esc_html( $country ); ?></option>
				<?php } else { ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $country ); ?></option>
				<?php } ?>
			<?php } ?>
		</select>
	</div>
</div>
<script type="text/javascript">
jQuery('#wk-seller-subscribe-email').on('click', function () {
    if(jQuery('#wk-seller-subscribe-email').prop('checked')){
		jQuery("#subscribed-country").attr('required','required');
        jQuery("#subscribed_country").show(500);
    }else{
        jQuery("#subscribed_country").hide(500);
		jQuery("#subscribed-country").removeAttr('required');
    }
});
</script>