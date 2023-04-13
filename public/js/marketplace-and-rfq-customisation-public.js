(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	jQuery(document).on('click', '.markasclosed', function(e){
        e.preventDefault();
        let id = jQuery(this).data('id');
        let product = jQuery(this).data('product');
        if(confirm('By cancelling, you\'ll no longer offers for this request. Any existing offers will move to Closed status.')){
            jQuery('#wk-mp-loader-rfq').removeClass('hide');
            jQuery('#wk-mp-loader-rfq').show();
            jQuery.ajax({
                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                async: false,
                type: "POST",
                data: {
                    action: 'change_rfq_status',
                    id: id,
                    product:product,
                },
                success: function(data) {
                window.location.reload();
                },
                error: function(errorThrown) {
                    console.log(errorThrown);
                }
            });
        }
    });
})( jQuery );
