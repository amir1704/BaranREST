<?php
require_once( ABSPATH.'wp-admin/includes/user.php' );

/**
 * Auto Complete all WooCommerce orders and Update first name , last name and display name.
 */
function custom_woocommerce_auto_complete_order( $order_id ) { 
    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    $order->update_status( 'completed' );
	$order_data = $order->get_data();
	$customer_id = $order_data['customer_id'];
	$firstname = $order_data['billing']['first_name'];
	$lastname = $order_data['billing']['last_name'];
	wp_update_user( array ('ID' => $customer_id, 'display_name' => $firstname . ' ' . $lastname));
	update_user_meta($customer_id, 'first_name' ,  $firstname);
	update_user_meta($customer_id , 'last_name' , $lastname );  
}
add_action( 'woocommerce_thankyou', 'custom_woocommerce_auto_complete_order' );


/**
 * set default user display name = first name + last name
 */
function update_displaynames_after_edit_account( $user_id ) {
   $outcome = trim(get_user_meta($user_id, 'first_name', true) . " " . get_user_meta($user_id, 'last_name', true));
    if (!empty($outcome)) {
       wp_update_user( array ('ID' => $user_id, 'display_name' => $outcome));    
    }
	update_user_meta($user_id, 'billing_first_name' , trim(get_user_meta($user_id, 'first_name', true)) );
	 update_user_meta($user_id , 'billing_last_name' , trim(get_user_meta($user_id, 'last_name', true)) );  
}
add_action( 'woocommerce_save_account_details', 'update_displaynames_after_edit_account');




function set_billing_phone_after_register( $user_id ) {
    $user = get_user_by('ID', $user_id);
    update_user_meta($user_id, 'billing_phone' , '0'.$user->display_name );
}
add_action( 'user_register', 'set_billing_phone_after_register');
