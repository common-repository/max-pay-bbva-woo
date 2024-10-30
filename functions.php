<?php
	
	if ( !function_exists( 'max_pay_bbva_admin_script' ) ) {
		function max_pay_bbva_admin_script() {

			if ( ! did_action( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}
			wp_enqueue_script( 'max-pay-bbva-admin', plugins_url( '/assets/woopro.js', __FILE__ ), array( 'jquery' ), null, false );
			wp_enqueue_style( 'max-pay-bbva-admin', plugins_url( '/assets/woopro.css', __FILE__ ) );
		}
	}
	add_action( 'admin_enqueue_scripts', 'max_pay_bbva_admin_script' );

	if ( !function_exists( 'max_pay_bbva_front_script' ) ) {
		function max_pay_bbva_front_script() {

			wp_enqueue_script( 'max-pay-bbva', plugins_url( 'assets/woopro-front.js', __FILE__ ), array( 'jquery' ), null, false );
			wp_enqueue_style( 'max-pay-bbva', plugins_url( 'assets/woopro-front.css', __FILE__ ) );
			wp_localize_script( 'max-pay-bbva', 'kwajaxurl', 
				array( 
					'ajaxurl' 	=> admin_url( 'admin-ajax.php' ),
				)
			);
		}
	}
