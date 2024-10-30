jQuery( function( $ ) {
    'use strict';

    ;( function ( document, window, index ) {      

    $( document ).ajaxComplete( function( event, request, options ) {
        var selectedValue = $( 'form.checkout .wc_payment_methods input[name^="payment_method"]:checked' ).val();
        if ( selectedValue == 'woocommerce_max_pay_bbva' ) {
            $( '.place-order button' ).addClass( 'max_pay' );
        }
    });

    $( 'form.checkout' ).on( 'change', 'input[name^="payment_method"]', function() {
        var choosenPaymentMethod = $( 'input[name^="payment_method"]:checked' ).val();
        if( choosenPaymentMethod == 'woocommerce_max_pay_bbva' ){
            $( '.place-order button' ).addClass( 'max_pay_bbva' );
        } else { 
            $( '.place-order button' ).removeClass( 'max_pay_bbva' );
        }
    });
});