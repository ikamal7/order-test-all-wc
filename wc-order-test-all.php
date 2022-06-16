<?php
/**
 * Plugin Name: Order Test For All for WooCommerce
 * Plugin URI:  https://github.com/ikamal7/wc-order-test-all
 * Description: A payment gateway plugin for WooCommerce to see if your checkout works.
 * Author:      Kamal Hosen
 * Author URI:  https://kamalhosen.xyz/
 * Language URI: /languages
 * text-domain: wc_order_test
 * Version:     1.0
 */

function wc_order_test_init() {
	if (!class_exists('WC_Payment_Gateway')) {
		return;
	}
	
	class WC_Test_Order_Gateway extends WC_Payment_Gateway {
	
		public function __construct() {
			$this->id = 'wc_order_test';
			$this->has_fields = false;
			$this->method_title = __( 'WC Order Test', 'wc_order_test' );
			$this->init_form_fields();
			$this->init_settings();
			$this->title = 'Test Gateway';
	
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}
		
		function init_form_fields() {
			$this->form_fields = [
				'enabled' => [
					'title' => __( 'Enable/Disable', 'wc_order_test' ),
					'type' => 'checkbox',
					'label' => __( 'Enable order test gateway', 'wc_order_test' ),
					'default' => 'yes'
				],
				'enabled_all' => [
					'title' => __( 'Enable/Disable All Users', 'wc_order_test' ),
					'type' => 'checkbox',
					'label' => __( 'Enable gateway for all', 'wc_order_test' ),
					'default' => 'yes'
				],
			];
		}
	    
		
		public function admin_options() {
			echo '	<h3>Order Test Gateway</h3><br>
			<p>Enable this below to test the checkout process on your site.</p>
				<table class="form-table">';
				
			$this->generate_settings_html();
			
			echo '	</table>';
		}
	
		public function process_payment( $order_id ) {
			global $woocommerce;
	    
			$order = new WC_Order( $order_id );
			$order->payment_complete();
			$order->reduce_order_stock();
			$woocommerce->cart->empty_cart();
	
			return array(
				'result' => 'success',
				//'redirect' => add_query_arg('key', $order->order_key, add_query_arg('order', $order->id, get_permalink(woocommerce_get_page_id('thanks')))),
				'redirect' => $order->get_checkout_order_received_url()
			);
		}
	
	}	

	function add_user_roles_to_gateway( $methods ) {
		$enabled_all = get_option( 'woocommerce_wc_order_test_settings');
		if (current_user_can('administrator') || $enabled_all['enabled_all'] == 'yes' || WP_DEBUG ) {
			$methods[] = 'WC_Test_Order_Gateway';
		}
		
		return $methods;
	}
	
	add_filter('woocommerce_payment_gateways', 'add_user_roles_to_gateway' );
	
}

add_filter('plugins_loaded', 'wc_order_test_init' );

?>