<?php
/*
 	Plugin Name: BBVA - WooCommerce
 	Description: Acepta pagos atraves de tu Cuenta Bancaria BBVA o tu CCI, todo gracias a nuestro plugin.
 	Requires at least: 5.2
 	Requires PHP: 7.0
 	Version: 2.1.0
 	Author: Alcanc3 - Joan David
 	Plugin URI: https://alcanc3.ml/e-commerce/
 	Author URI: https://Alcanc3.ml
 	Text Domain: max-pay-bbva
 	License: GPL v2 or later
 	License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
function max_pay_bbva_load_textdomain() {
	load_plugin_textdomain( 'max-pay-bbva', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}

//Additional links on the plugin page
add_filter( 'plugin_row_meta', 'max_pay_bbva_register_plugin_links', 10, 2 );
function max_pay_bbva_register_plugin_links($links, $file) {
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="https://alcanc3.ml/tienda" target="_blank">' . __( 'Ver más Plugins', 'rsb' ) . '</a>';

	}
	return $links;
}

add_action( 'plugins_loaded', 'max_pay_bbva_load_textdomain' );

/*
 	This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'max_pay_bbva_add_gateway_class' );
function max_pay_bbva_add_gateway_class( $gateways ) {
	$gateways[] = 'max_pay_bbva_WC_Gateway';
	return $gateways;
}
add_action( 'plugins_loaded', 'max_pay_bbva_init_gateway_class' );
function max_pay_bbva_init_gateway_class() {

 	require plugin_dir_path( __FILE__ ) . 'functions.php';
  class max_pay_bbva_WC_Gateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'woocommerce_max_pay_bbva';
		$this->icon               = plugins_url('image/pagoconbbva.png', __FILE__);// URL of the icon that will be displayed on checkout page near your gateway name
		$this->has_fields         = true;
		$this->method_title       = _x( 'Max Pay', 'max-pay-bbva' );
		$this->method_description = __( 'Permite la integración de tu cuenta bancaria bbva simple y ligero ', 'max-pay-bbva' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title        = $this->get_option( 'title' );
		$this->icon = plugins_url('image/pagoconbbva.png', __FILE__);
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions' );
		$this->enabled = $this->get_option( 'enabled' );

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_woocommerce_max_pay_bbva', array( $this, 'thankyou_page' ) );

		// Customer Emails.
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'      => array(
				'title'   => __( 'Habilitar/Deshabilitar', 'max-pay-bbva' ),
				'type'    => 'checkbox',
				'label'   => __( 'Habilitar Max Pay | bbva', 'max-pay-bbva' ),
				'default' => 'no',
			),
			'title'        => array(
				'title'       => __( 'Titulo', 'max-pay-bbva' ),
				'type'        => 'text',
				'description' => __( 'Esto controla el título que el usuario ve al terminar la compra.', 'max-pay-bbva' ),
				'default'     => __( 'BBVA', 'max-pay-bbva' ),
				'desc_tip'    => true,
			),
			'info' => array(
                'title'       => __( 'lo que debes agregar o cambiar es', 'max-pay-bbva' ),
                'type'        => 'title',
                'description' => __( 'NUMERO DE CUENTA, NUMERO DE CUENTA CCI, NOMBRE DEL TITULAR', 'max-pay-bbva' ) . '</b>',                   				 
                ),
			'description'  => array(
				'title'       => __( 'Descripción', 'max-pay-bbva' ),
				'type'        => 'textarea',
				'description' => __( 'Descripción del método de pago que el cliente verá en el pago.', 'max-pay-bbva' ),
				'default'     => __( '<b>NUESTRO SERVICIO ESTÁ EN ESPERA DE TU PAGO</b>
<br>
Paga por <b> BANCA VIRTUAL</b> (app y web del mismo banco) o <b>AGENTES</b> (farmacias o tiendas). No uses ventanilla de banco por exceso de comisión. Será verificado a la brevedad.
<br>
<br>
<b>BBVA:</b> 
<a class="estilobbva cuentabbva">NUMERO DE CUENTA</a><br>
<br>
<b>CCI:</b>
<a class="estilobbva cuentabbvacci">NUMERO DE CUENTA CCI</a><br>
<br>
<b>Títular de la cuenta:</b>
NOMBRE DEL TITULAR', 'max-pay-bbva' ),
				'desc_tip'    => true,
			),
			'info2' => array(
                'title'       => __( 'lo que debes agregar o cambiar es', 'max-pay-bbva' ),
                'type'        => 'title',
                'description' => __( 'NUMERO DE CELULAR', 'max-pay-bbva' ) . '</b>',                   				 
                ),

			'instructions' => array(
				'title'       => __( 'Instrucciones', 'max-pay-bbva' ),
				'type'        => 'textarea',
				'description' => __( 'Instrucciones que se añadirán a la página de agradecimiento.', 'max-pay-bbva' ),
				'default'     => __( '<b>(ALTERNATIVA)</b><br>
<b>Recepción de comprobante:</b> <a href="tel:NUMERO DE CELULAR">+51 NUMERO DE CELULAR</a>
<br>
<br>
Agrega al número <b>NUMERO DE CELULAR</b> a tu lista de contactos, realiza el pago por el monto total y envía la captura vía whatsapp al mismo número junto a tu <b>"número de pedido".</b><br>
<b>Una vez comprobado el pago correcto el pedido será actualizado a "Completado".</b>', 'max-pay-bbva' ),
				'desc_tip'    => true,
			),
			'info3' => array(
                'title'       => __( 'agrega el código CSS en tu wordpress', 'max-pay-bbva' ),
                'type'        => 'textarea',
				'description' => __( 'es importante que copies y agreges este codigo css a tu wordpress para que funcione', 'max-pay-bbva' ),
                'default'     => __( '/*CUENTA BBVA*/
.cuentabbva {
font-size: 20px;
display: grid;
background: #072146;
color: #fff !important;
border-radius: 4px;
line-height: 1.1;
padding: 8px 1%;
text-transform: uppercase;
font-weight: bold;
box-shadow: 0 6px 0 #1464a5 !important;
transition: all 0.3s ease-in-out;
text-align: center !important;
text-decoration:none !important;
}
.cuentabbva:hover{
box-shadow: 0 6px 0 #072146 !important;
}
.cuentabbvacci {
font-size: 20px;
display: grid;
background: #1464a5;
color: #fff !important;
border-radius: 4px;
line-height: 1.1;
padding: 8px 1%;
text-transform: uppercase;
font-weight: bold;
box-shadow: 0 6px 0 #072146 !important;
transition: all 0.3s ease-in-out;
text-align: center !important;
text-decoration:none !important;
}
.cuentabbvacci:hover{
box-shadow: 0 6px 0 #1464a5 !important;
}
/*CUENTA BBVA*/', 'max-pay-bbva' ),
				'desc_tip'    => true,                  				 
                ),
			'ayuda' => array(
                'title'       => __( '¿necesitas ayuda?, contactanos para darte soporte <a href="https://alcanc3.ml">CONTACTO</a>', 'max-pay-bbva' ),
                'type'        => 'title',                  				 
                ),
		);
	}

	/**
	 * Output for the order received page.
	 */
	public function thankyou_page() {
		if ( $this->instructions ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 * @param WC_Order $order Order object.
	 * @param bool     $sent_to_admin Sent to admin.
	 * @param bool     $plain_text Email format: plain text or HTML.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && 'woocommerce_max_pay_bbva' === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) . PHP_EOL );
		}
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( $order->get_total() > 0 ) {
			// Mark as on-hold (we're awaiting the payment).
			$order->update_status( apply_filters( 'woocommerce_max_pay_bbva_process_payment_order_status', 'on-hold', $order ), _x( 'Awaiting payment', 'Check payment method', 'woocommerce' ) );
		} else {
			$order->payment_complete();
		}

		// Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}
  }
}