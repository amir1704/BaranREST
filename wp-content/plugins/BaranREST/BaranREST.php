<?php

/*
Plugin Name: Baran REST
Plugin URI: https://baransys.com
Description: پلاگین اتصال نرم افزار باران به وردپرس
Version: 1.0
Author: گروه نرم افزاری باران
Author URI: https://baransys.com
License: A "Slug" license name e.g. GPL2
*/
require_once( ABSPATH . '/wp-admin/includes/taxonomy.php');
require_once( ABSPATH . 'wp-admin/includes/image.php' );
require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/media.php' );
/**
 * Get All orders IDs for a given product ID.
 *
 * @param  integer  $product_id (required)
 * @param  array    $order_status (optional) Default is 'wc-completed'
 *
 * @return array
 */
function get_orders_ids_by_product_id( $product_id, $order_status = array( 'wc-completed' ) ){
    global $wpdb;

    $results = $wpdb->get_col("
        SELECT order_items.order_id
        FROM {$wpdb->prefix}woocommerce_order_items as order_items
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
        WHERE posts.post_type = 'shop_order'
        AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
        AND order_items.order_item_type = 'line_item'
        AND order_item_meta.meta_key = '_product_id'
        AND order_item_meta.meta_value = '$product_id'
    ");

    return $results;
}

/**
 *
 * Send Products to Wordpress
 * @param WP_REST_Request $request
 * @return array
 *
 */
function ProductSEND(WP_REST_Request $request) {
    $product_array = $request->get_json_params();
    $productIds = array();

    foreach( $product_array as $product ){
        $sub_cat_result = 0;
        $mother_cat_result = 0;
        $productId = 0;
        if( $product['ChangeType'] != 2 ){
            $args = array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key'       => 'ref_id',
                        'value'     => $product['MainGroupId'],
                    ),
                ),
            );
            $main_cat = get_terms( $args );

            if(!empty($main_cat)){
                if($product['MainGroupId']) {
                    $catArr = array(
                        'cat_ID' => $main_cat[0]->term_id,
                        'taxonomy' => 'product_cat',
                        'cat_name' => $product['MainGroupName']
                    );
                    $mother_cat_result = wp_insert_category($catArr);
                }
                if( $mother_cat_result || !$product['MainGroupId'] ){
                    $args = array(
                        'taxonomy' => 'product_cat',
                        'hide_empty' => false,
                        'meta_query' => array(
                            array(
                                'key'       => 'ref_id',
                                'value'     => $product['GroupId'],
                            ),
                        ),
                    );
                    $sub_cat = get_terms( $args );
                    if(!empty($sub_cat)){
                        if( $mother_cat_result ){
                            $catArr = array(
                                'cat_ID' => $sub_cat[0]->term_id,
                                'taxonomy' => 'product_cat',
                                'cat_name' => $product['GroupName'],
                                'category_parent'=> $mother_cat_result
                            );
                        }
                        else{
                            $catArr = array(
                                'cat_ID' => $sub_cat[0]->term_id,
                                'taxonomy' => 'product_cat',
                                'cat_name' => $product['GroupName']
                            );
                        }

                        $sub_cat_result = wp_insert_category($catArr);
                    }
                    else{
                        if( $mother_cat_result ){
                            $subCatArr = array(
                                'cat_ID' => 0,
                                'taxonomy' => 'product_cat',
                                'cat_name' => $product['GroupName'],
                                'category_parent'=> $mother_cat_result
                            );
                        }
                        else{
                            $subCatArr = array(
                                'cat_ID' => 0,
                                'taxonomy' => 'product_cat',
                                'cat_name' => $product['GroupName']
                            );
                        }
                        $sub_cat_result = wp_insert_category($subCatArr);
                        if($sub_cat_result){
                            add_term_meta( $sub_cat_result , 'ref_id',$product['GroupId'] , true);
                        }
                    }
                }
            }
            else{

                if($product['MainGroupId']) {
                    $catArr = array(
                        'cat_ID' => 0,
                        'taxonomy' => 'product_cat',
                        'cat_name' => $product['MainGroupName']
                    );
                    $mother_cat_result = wp_insert_category($catArr);
                    add_term_meta( $mother_cat_result, 'ref_id', $product['MainGroupId'] , true );
                }
                if( $mother_cat_result || !$product['MainGroupId'] ) {

                    $args = array(
                        'taxonomy' => 'product_cat',
                        'hide_empty' => false,
                        'meta_query' => array(
                            array(
                                'key'       => 'ref_id',
                                'value'     => $product['GroupId'],
                            ),
                        ),
                    );
                    $sub_cat = get_terms( $args );
                    if(!empty($sub_cat)){
                        if( $mother_cat_result ){
                            $catArr = array(
                                'cat_ID' => $sub_cat[0]->term_id,
                                'taxonomy' => 'product_cat',
                                'cat_name' => $product['GroupName'],
                                'category_parent'=> $mother_cat_result
                            );
                        }
                        else{
                            $catArr = array(
                                'cat_ID' => $sub_cat[0]->term_id,
                                'taxonomy' => 'product_cat',
                                'cat_name' => $product['GroupName']
                            );
                        }

                        $sub_cat_result = wp_insert_category($catArr);
                    }
                    else{
                        if( $mother_cat_result ){
                            $subCatArr = array(
                                'cat_ID' => 0,
                                'taxonomy' => 'product_cat',
                                'cat_name' => $product['GroupName'],
                                'category_parent'=> $mother_cat_result
                            );
                        }
                        else{
                            $subCatArr = array(
                                'cat_ID' => 0,
                                'taxonomy' => 'product_cat',
                                'cat_name' => $product['GroupName']
                            );
                        }
                        $sub_cat_result = wp_insert_category($subCatArr);
                        if($sub_cat_result){
                            add_term_meta( $sub_cat_result , 'ref_id',$product['GroupId'] , true);
                        }
                    }
                }


            }

            if($sub_cat_result){
                $product_arg = array(
                    'post_type'  => 'product',
                    'numberposts' => 1,
                    'meta_query' => array(
                        array(
                            'key'     => '_sku',
                            'value'   => $product['ProductId'],
                            'compare' => '=',
                        ),
                    )
                );
                $product_obj = get_posts($product_arg);
                if(!empty($product_obj)){
                    $postArr = array(
                        'ID' => $product_obj[0]->ID,
                        'post_title' => $product['ProductName'],
                        'post_excerpt' => $product['ProductName2'],
                        'post_status' => 'publish',
                        'post_type' => 'product',
                        'post_content' => $product['ProductComment'],
                        'menu_order' => $product['OrderIndex']

                    );
                    $productId = wp_insert_post($postArr);
                    if($productId){
                        wp_set_object_terms($productId,$sub_cat_result,'product_cat');
                        switch($product['ProductStatus']){
                            case 0:
                                //wp_set_object_terms($productId,'عادی','product_tag');
                                break;
                            case 1:
                                wp_set_object_terms($productId,'دارای تخفیف','product_tag');
                                break;
                            case 2:
                                wp_set_object_terms($productId,'پیشنهاد ما','product_tag');
                                break;
                            case 3:
                                wp_set_object_terms($productId,'ویژه','product_tag');
                                break;
                        }
                        wp_set_object_terms($productId,'simple','product_type');
                        update_post_meta( $productId, '_visibility', 'visible' );
                        update_post_meta( $productId, '_sku', $product['ProductId'] );
                        if($product['RemainCount'] <= 0){
                            update_post_meta( $productId, '_stock_status', 'outofstock');
                            update_post_meta($productId, '_stock', 0);
                        }
                        else{
                            update_post_meta( $productId, '_stock_status', 'instock');
                            update_post_meta($productId, '_stock', $product['RemainCount']);
                        }
                        update_post_meta( $productId, '_manage_stock', "yes" );

                        update_post_meta( $productId, '_regular_price', $product['SellPrice'] );
                        $thumbnail = get_page_by_title($product['PictureName'],'OBJECT','attachment');
                        update_post_meta($productId, '_thumbnail_id', $thumbnail->ID);
                        $discount = $product['SellPrice'] * $product['DiscountPrecent'] / 100;
                        update_post_meta( $productId, '_sale_price', $product['SellPrice'] - $discount );
                        update_post_meta( $productId, '_price', $product['SellPrice'] - $discount );
                    }
                    $p['BaranId'] = $product['ProductId'];
                    $p['StatusId'] = 1;
                    array_push($productIds,$p);
                }
                else{
                    $postArr = array(
                        'ID' => 0,
                        'post_title' => $product['ProductName'],
                        'post_excerpt' => $product['ProductName2'],
                        'post_status' => 'publish',
                        'post_type' => 'product',
                        'post_content' => $product['ProductComment'],
                        'menu_order' => $product['OrderIndex']

                    );
                    $productId = wp_insert_post($postArr);
                    if($productId){
                        wp_set_object_terms($productId,$sub_cat_result,'product_cat');
                        switch($product['ProductStatus']){
                            case 0:
                                //wp_set_object_terms($productId,'عادی','product_tag');
                                break;
                            case 1:
                                wp_set_object_terms($productId,'دارای تخفیف','product_tag');
                                break;
                            case 2:
                                wp_set_object_terms($productId,'پیشنهاد ما','product_tag');
                                break;
                            case 3:
                                wp_set_object_terms($productId,'ویژه','product_tag');
                                break;
                        }
                        wp_set_object_terms($productId,'simple','product_type');
                        update_post_meta( $productId, '_visibility', 'visible' );
                        if($product['RemainCount'] <= 0){
                            update_post_meta( $productId, '_stock_status', 'outofstock');
                            update_post_meta($productId, '_stock', 0);
                        }
                        else{
                            update_post_meta( $productId, '_stock_status', 'instock');
                            update_post_meta($productId, '_stock', $product['RemainCount']);
                        }
                        update_post_meta( $productId, '_sku', $product['ProductId'] );
                        update_post_meta( $productId, '_manage_stock', "yes" );
                        $thumbnail = get_page_by_title($product['PictureName'],'OBJECT','attachment');
                        update_post_meta($productId, '_thumbnail_id', $thumbnail->ID);
                        update_post_meta( $productId, '_regular_price', $product['SellPrice'] );
                        $discount = $product['SellPrice'] * $product['DiscountPrecent'] / 100;
                        update_post_meta( $productId, '_sale_price', $product['SellPrice'] - $discount );
                        update_post_meta( $productId, '_price', $product['SellPrice'] - $discount );
                    }
                    $p['BaranId'] = $product['ProductId'];
                    $p['StatusId'] = 1;
                    array_push($productIds,$p);
                }
            }
        }
        else {
            //$result = wp_delete_post($product['ProductId'], true);
            $product_arg = array(
                'post_type'  => 'product',
                'numberposts' => 1,
                'meta_query' => array(
                    array(
                        'key'     => '_sku',
                        'value'   => $product['ProductId'],
                        'compare' => '=',
                    ),
                )
            );
            $product_obj = get_posts($product_arg);
            if(!empty($product_obj)){
                update_post_meta( $product_obj[0]->ID, '_stock_status', 'outofstock');
                update_post_meta( $product_obj[0]->ID, '_price', '');
                $p['BaranId'] = $product['ProductId'];
                $p['StatusId'] = 1;
                array_push($productIds,$p);
            }
            else{
                $p['BaranId'] = $product['ProductId'];
                $p['StatusId'] = 0;
                array_push($productIds,$p);
            }
        }

    }

    $response = new WP_REST_Response($productIds);
    $response->set_status( 200 );
    return $response;
}

/**
 *
 * Send picture to wordpress with form
 * @param WP_REST_Request $request
 * @return array
 *
 *
 */
function SENDPics(WP_REST_Request $request){
    $req_file = $request->get_file_params();

    $req_array = $request->get_params();
    $ids = explode('-',$req_array['ids']);
    $filename = $req_file['image']['name'];
    $upload_dir = wp_upload_dir();
    $thumbnail = get_page_by_title(pathinfo(sanitize_file_name( $filename ))['filename'],'OBJECT','attachment');
    if($thumbnail){
        foreach ($ids as $id){
            $product_arg = array(
                'post_type'  => 'product',
                'numberposts' => 1,
                'meta_query' => array(
                    array(
                        'key'     => '_sku',
                        'value'   => $id,
                        'compare' => '=',
                    ),
                )
            );
            $product_obj = get_posts($product_arg);
            update_post_meta($product_obj[0]->ID, '_thumbnail_id', $thumbnail->ID);
        }
        $output['StatusId'] = 1;
        $output['BaranId'] = $thumbnail->ID;
        return $output;
    }
    else{
        $attachment = array(
            'post_mime_type' => $req_file['image']['type'],
            'post_title' => pathinfo(sanitize_file_name( $filename ))['filename'],
            'post_content' => '',
            'post_status' => 'inherit'
        );



        foreach ($req_file as $key => $file) {
            $attachment_id = media_handle_upload( $key, 0,$attachment );
            if ( is_wp_error( $attachment_id ) ) {
                $output['StatusId'] = 0;
                $output['BaranId'] = $attachment_id;
                return $output;
            } else {
                // Success
                $output['StatusId'] = 1;
                $output['BaranId'] = $attachment_id;
                foreach ($ids as $id){
                    $product_arg = array(
                        'post_type'  => 'product',
                        'numberposts' => 1,
                        'meta_query' => array(
                            array(
                                'key'     => '_sku',
                                'value'   => $id,
                                'compare' => '=',
                            ),
                        )
                    );
                    $product_obj = get_posts($product_arg);
                    update_post_meta($product_obj[0]->ID, '_thumbnail_id', $attachment_id);
                }
                return $output;
            }
        }
    }

}

/**
 *
 * Send picture to wordpress with bytes
 * @param WP_REST_Request $request
 * @return array
 *
 *
 */
function SENDPics2(WP_REST_Request $request){

    $file = $request->get_body();
    $file_name = $request->get_header('filename');
    $ids = explode('-',$request->get_header('ids'));
    $filename =  pathinfo($file_name)['filename'];
    $thumbnail = get_page_by_title($filename,'OBJECT','attachment');
    if($thumbnail){
        foreach ($ids as $id){
            $product_arg = array(
                'post_type'  => 'product',
                'numberposts' => 1,
                'meta_query' => array(
                    array(
                        'key'     => '_sku',
                        'value'   => $id,
                        'compare' => '=',
                    ),
                )
            );
            $product_obj = get_posts($product_arg);
            update_post_meta($product_obj[0]->ID, '_thumbnail_id', $thumbnail->ID);
        }
        $output['StatusId'] = 1;
        $output['BaranId'] = $thumbnail->ID;
        return $output;
    }
    else{
        $result = wp_upload_bits($file_name,null,$file);
        if($result){
            $attachment = array(
                'guid'  => $result['url'],
                'post_mime_type' => $result['type'],
                'post_title' => $filename,
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attachment_id = wp_insert_attachment($attachment , $result['url']);
            if($attachment_id){
                $attach_data = wp_generate_attachment_metadata( $attachment_id, $result['url'] );
                wp_update_attachment_metadata( $attachment_id, $attach_data );
                foreach ($ids as $id){
                    $product_arg = array(
                        'post_type'  => 'product',
                        'numberposts' => 1,
                        'meta_query' => array(
                            array(
                                'key'     => '_sku',
                                'value'   => $id,
                                'compare' => '=',
                            ),
                        )
                    );
                    $product_obj = get_posts($product_arg);
                    update_post_meta($product_obj[0]->ID, '_thumbnail_id', $attachment_id);
                }
                $output['StatusId'] = 1;
                $output['BaranId'] = $attachment_id;
            }
            else{
                $output['StatusId'] = 0;
                $output['BaranId'] = $attachment_id;
            }
            return $output;
        }

    }


}


/**
 *
 * Send users to wordpress
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 *
 */
function CustomersSEND(WP_REST_Request $request){
    $users = $request->get_json_params();
    $userIds = array();
    foreach($users as $user){
        if($user['ChangeType'] != 2){
            $user_obj = get_user_by('id', $user['CustomerId']);

            if(!empty($user_obj)){

                $user_array = array(
                    'ID' => $user['CustomerId'],
                    'user_login' => 'u'.$user['CustomerId'],
                    'user_pass' => '',
                    'user_email' => $user['Email'],
                    'first_name' => $user_obj->first_name,
                    'last_name' => $user_obj->last_name,
                    'display_name' => $user['CustomerName']
                );
                $user_id = wp_insert_user($user_array);
                $user_obj_new = new WP_User( $user_id );
                $user_obj_new->set_role('customer');
                if($user_id){
                    update_user_meta($user_id,'digt_countrycode','+98');
                    update_user_meta($user_id,'digits_phone_no',$user['Mobile']);
                    update_user_meta($user_id,'billing_phone',$user['Mobile']);
                    update_user_meta($user_id,'digits_phone','+98' . $user['Mobile']);
                    update_user_meta($user_id,'account_funds',$user['Account'] + $user['Grant']);
                    update_user_meta($user_id,'CustomerCode',$user['CustomerCode']);
                    update_user_meta($user_id,'ReagentCode',$user['ReagentCode']);
                    $p['BaranId'] = $user['CustomerId'];
                    $p['StatusId'] = 1;
                    array_push($userIds,$p);
                }
                else{
                    $p['BaranId'] = $user['CustomerId'];
                    $p['StatusId'] = 0;
                    array_push($userIds,$p);
                }

            }
            else{
                global $wpdb;

                $wpdb->insert( $wpdb->users, array( 'ID' => $user['CustomerId'] , 'user_login' => 'u'.$user['CustomerId']) );
                $user_array = array(
                    'ID' => $user['CustomerId'],
                    'user_login' => 'u'.$user['CustomerId'],
                    'user_pass' => '',
                    'user_email' => $user['Email'],
                    'display_name' => $user['CustomerName']
                );
                $user_id = wp_insert_user($user_array);
                $user_obj_new = new WP_User( $user_id );
                $user_obj_new->set_role('customer');
                if($user_id){
                    update_user_meta($user_id,'digt_countrycode','+98');
                    update_user_meta($user_id,'digits_phone_no',$user['Mobile']);
                    update_user_meta($user_id,'digits_phone','+98' . $user['Mobile']);
                    update_user_meta($user_id,'billing_phone',$user['Mobile']);
                    update_user_meta($user_id,'account_funds',$user['Account'] + $user['Grant']);
                    update_user_meta($user_id,'CustomerCode',$user['CustomerCode']);
                    update_user_meta($user_id,'ReagentCode',$user['ReagentCode']);
                    $p['BaranId'] = $user['CustomerId'];
                    $p['StatusId'] = 1;
                    array_push($userIds,$p);
                }
                else{
                    $p['BaranId'] = $user['CustomerId'];
                    $p['StatusId'] = 0;
                    array_push($userIds,$p);
                }

            }
        }
        else{
            require_once( ABSPATH.'wp-admin/includes/user.php' );
            $user_obj = get_user_by('id', $user['CustomerId']);
            if($user_obj){

                $result = wp_delete_user($user_obj->ID);
                if($result){
                    $p['BaranId'] = $user['CustomerId'];
                    $p['StatusId'] = 1;
                    array_push($userIds,$p);
                }
                else{
                    $p['BaranId'] = $user['CustomerId'];
                    $p['StatusId'] = 0;
                    array_push($userIds,$p);
                }
            }
        }


    }
    $response = new WP_REST_Response($userIds);
    $response->set_status( 200 );
    return $response;
}

/**
 *
 * Send coupons to wordpress
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 *
 *
 */
function SendCoupons(WP_REST_Request $request){
    $coupons = $request->get_json_params();
    $couponIds = array();

    foreach($coupons as $coupon){
        if($coupon['OperationType'] != 2){
            $coupon_arg = array(
                'post_type'  => 'shop_coupon',
                'numberposts' => 1,
                'meta_query' => array(
                    array(
                        'key'     => 'ref_id',
                        'value'   => $coupon['Id'],
                        'compare' => '=',
                    ),
                )
            );
            $coupon_obj = get_posts($coupon_arg);
            if(!empty($coupon_obj)){
                $coupon_array = array(
                    "ID" => $coupon_obj[0]->ID,
                    'post_title' => $coupon['CouponNumber'],
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_type'     => 'shop_coupon'
                );
                $new_coupon_id = wp_insert_post( $coupon_array );
                if($new_coupon_id){
                    /*switch($coupon['CouponType']){
                        case 0:
                            update_post_meta( $new_coupon_id, 'discount_type', 'fixed_cart' );
                            update_post_meta( $new_coupon_id, 'coupon_amount', $coupon['Amount'] );
                            break;
                        case 1:
                            update_post_meta( $new_coupon_id, 'discount_type', 'percent' );
                            update_post_meta( $new_coupon_id, 'coupon_amount', $coupon['Percent'] );
                            break;
                        case 2:
                            update_post_meta( $new_coupon_id, 'discount_type', 'fixed_product' );
                            update_post_meta( $new_coupon_id, 'coupon_amount', $coupon['Amount']  );
                            break;
                        case 3:
                            update_post_meta( $new_coupon_id, 'discount_type', 'percent_product' );
                            update_post_meta( $new_coupon_id, 'coupon_amount', $coupon['Percent'] );
                            break;
                    }*/
                    if($coupon['Precent'] == 0){
                        update_post_meta( $new_coupon_id, 'discount_type', 'fixed_cart' );
                        update_post_meta( $new_coupon_id, 'coupon_amount', $coupon['Amount'] );
                    }
                    if($coupon['Amount'] == 0){
                        update_post_meta( $new_coupon_id, 'discount_type', 'percent' );
                        update_post_meta( $new_coupon_id, 'coupon_amount', $coupon['Precent'] );
                    }
                    update_post_meta( $new_coupon_id, 'individual_use', 'no' );
                    update_post_meta( $new_coupon_id, 'product_ids', '' );
                    update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
                    update_post_meta( $new_coupon_id, 'usage_limit', $coupon['MaxUsedCount'] );
                    update_post_meta( $new_coupon_id, 'expiry_date', strtotime($coupon['EndDate']));
                    update_post_meta( $new_coupon_id, 'date_expires', strtotime($coupon['EndDate']));
                    /*$user = get_userdata($coupon['PersonId']);
                    $email = $user->user_email;
                    $emails = array($email);
                    update_post_meta( $new_coupon_id, 'customer_email', $emails );*/
                    update_post_meta( $new_coupon_id, 'minimum_amount', $coupon['FactorBaseAmount'] );
                    update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
                    update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
                    $p['BaranId'] = $coupon['Id'];
                    $p['StatusId'] = 1;
                    array_push($couponIds,$p);
                }
                else{
                    $p['BaranId'] = $coupon['Id'];
                    $p['StatusId'] = 0;
                    array_push($couponIds,$p);
                }

            }
            else{

                $coupon_array = array(
                    'post_title' => $coupon['CouponNumber'],
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_author' => 1,
                    'post_type'     => 'shop_coupon'
                );
                $new_coupon_id = wp_insert_post( $coupon_array );
                if($new_coupon_id){
                    /*switch($coupon['CouponType']){
                        case 0:
                            update_post_meta( $new_coupon_id, 'discount_type', 'fixed_cart' );
                            update_post_meta( $new_coupon_id, 'coupon_amount', $coupon['Amount'] );
                            break;
                        case 1:
                            update_post_meta( $new_coupon_id, 'discount_type', 'percent' );
                            update_post_meta( $new_coupon_id, 'coupon_amount', $coupon['Percent'] );
                            break;
                        case 2:
                            update_post_meta( $new_coupon_id, 'discount_type', 'fixed_product' );
                            update_post_meta( $new_coupon_id, 'coupon_amount', $coupon['Amount']  );
                            break;
                        case 3:
                            update_post_meta( $new_coupon_id, 'discount_type', 'percent_product' );
                            update_post_meta( $new_coupon_id, 'coupon_amount', $coupon['Percent'] );
                            break;
                    }*/
                    if($coupon['Precent'] == 0){
                        update_post_meta( $new_coupon_id, 'discount_type', 'fixed_cart' );
                        update_post_meta( $new_coupon_id, 'coupon_amount', $coupon['Amount'] );
                    }
                    if($coupon['Amount'] == 0){
                        update_post_meta( $new_coupon_id, 'discount_type', 'percent' );
                        update_post_meta( $new_coupon_id, 'coupon_amount', $coupon['Precent'] );
                    }
                    update_post_meta( $new_coupon_id, 'individual_use', 'no' );
                    update_post_meta( $new_coupon_id, 'product_ids', '' );
                    update_post_meta( $new_coupon_id, 'exclude_product_ids', '' );
                    update_post_meta( $new_coupon_id, 'usage_limit', $coupon['MaxUsedCount'] );
                    update_post_meta( $new_coupon_id, 'expiry_date', strtotime($coupon['EndDate']));
                    update_post_meta( $new_coupon_id, 'date_expires', strtotime($coupon['EndDate']));
                    /*$user = get_userdata($coupon['PersonId']);
                    $email = $user->user_email;
                    $emails = array($email);
                    update_post_meta( $new_coupon_id, 'customer_email', $emails );*/
                    update_post_meta( $new_coupon_id, 'minimum_amount', $coupon['FactorBaseAmount'] );
                    update_post_meta( $new_coupon_id, 'apply_before_tax', 'yes' );
                    update_post_meta( $new_coupon_id, 'free_shipping', 'no' );
                    update_post_meta( $new_coupon_id, 'ref_id', $coupon['Id'] );
                    $p['BaranId'] = $coupon['Id'];
                    $p['StatusId'] = 1;
                    array_push($couponIds,$p);
                }
                else{
                    $p['BaranId'] = $coupon['Id'] ;
                    $p['StatusId'] = 0;
                    array_push($couponIds,$p);
                }

            }
        }
        else{
            /* $coupon_arg = array(
                 'post_type'  => 'shop_coupon',
                 'numberposts' => 1,
                 'meta_query' => array(
                     array(
                         'key'     => 'ref_id',
                         'value'   => $coupon['Id'],
                         'compare' => '=',
                     ),
                 )
             );
             $coupon_obj = get_posts($coupon_arg);*/
            $coupon_obj = get_page_by_title($coupon['CouponNumber'], 'OBJECT', 'shop_coupon');

            if(!empty($coupon_obj)){
                $result = wp_delete_post($coupon_obj->ID);
                if($result){
                    $p['BaranId'] = $coupon['Id'];
                    $p['StatusId'] = 1;
                    array_push($couponIds,$p);
                }
                else{
                    $p['BaranId'] = $coupon['Id'];
                    $p['StatusId'] = 0;
                    array_push($couponIds,$p);
                }
            }
            else{
                $p['BaranId'] = $coupon['Id'];
                $p['StatusId'] = 0;
                array_push($couponIds,$p);
            }
        }


    }
    $response = new WP_REST_Response($couponIds);
    $response->set_status( 200 );
    return $response;
}

/**
 *
 * Send Orders to software
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 *
 *
 */
function Orders(WP_REST_Request $request){
    $orders_array = array();
    $order_items_array = array();
    $order_arg = array(
        'post_type'  => 'shop_order',
        'post_status' => array('wc-processing','wc-completed'),
        'numberposts' => -1,
        'meta_query' => array(
            array(
                'key'     => 'clear',
                'value'   => '',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key' => '_fund_deposited',
                'value' => 'yes',
                'compare' => 'NOT EXISTS'
            )
        )
    );
    $wallet_flag = 0;
    $orders = get_posts($order_arg);

    foreach ($orders as $order){
        $order_data = new stdClass;
        $wallet_flag = 0;
        $order_obj = new WC_Order($order->ID);

        $items = $order_obj->get_items();

        $coupons = $order_obj->get_coupon_codes();
        $data = $order_obj->get_data();

        $order_data->FactorNumber = $data['id'];
        $order_data->OrderDate = $data['date_created'];
        $order_data->SendTime = null;

        $order_data->ProductDiscount = 0;

        $order_data->TotalDiscountAmount = $data['discount_total'];
        $order_data->SendAmount = $data['shipping_total'];
        $flag = 0;
        $used = 0;
        foreach($data['meta_data'] as $meta){

            if($meta->key == '_order_fund_removed' && $meta->value == 'yes'){
                $flag = 1;
            }
            if($meta->key == '_order_funds'){
                $used = $meta->value;
            }
        }
        if($flag){
            $order_data->CustomerClubAmount = $used;
            $order_data->TotalAmount =  $data['discount_total'] + $data['total'] + $used;
            $order_data->FinalAmount = $data['total'] + $used;
        }
        else{
            $order_data->CustomerClubAmount = 0;
            $order_data->TotalAmount =  $data['discount_total'] + $data['total'] + 0;
            $order_data->FinalAmount = $data['total'] + 0;
        }

        foreach ($coupons as $coupon){
            global $woocommerce;
            $c = new WC_Coupon($coupon);
            $c_data =  $c->get_data();
            $order_data->DiscountCode = $c_data['code'];
            $order_data->DiscountAmount = $data['discount_total'] ;
        }
        $order_data->Comment = $data['customer_note'];
        switch($data['payment_method']){
            case 'WC_ZPal':
                $order_data->PaymentCode = 2;
                break;
            case 'cod':
                $order_data->PaymentCode = 1;
                break;
        }
        $order_data->PaymentTitle = $data['payment_method_title'];
        $order_data->CustomerId = $data['customer_id'];
        $user_obj = get_user_by('id', $data['customer_id']);
        $state =  WC()->countries->get_states( $order_obj->get_shipping_country() )[$order_obj->get_shipping_state()];
        $order_data->CustomerName =  $user_obj->display_name;
        $order_data->CustomerPhone = get_user_meta($data['customer_id'],'digits_phone_no')[0];
        $order_data->CustomerAddress = $state .' '.  $data['shipping']['city'] . ' '. $data['shipping']['address_1'] . ' ' .$data['shipping']['postcode'];
        $order_data->CustomerEmail = $user_obj->user_email;
        $order_data->CustomerCode = get_user_meta($data['customer_id'],'CustomerCode',true);
        $order_data->CustomerReagentCode = get_user_meta($data['customer_id'],'ReagentCode',true);

        foreach ($items as $item){

            $i_data = $item->get_data();
            if(!$i_data['product_id']){
                $order_data->TotalAmount = $order_data->TotalAmount - $i_data['total'];
                $order_data->FinalAmount = $order_data->FinalAmount - $i_data['total'];
            }
            else{
                $product = new WC_Product($i_data['product_id']);
                $id = $product->get_sku();
                $item_data = new stdClass;
                $item_data->OrderId = $i_data['order_id'];
                $item_data->ProductId = $id;
                $item_data->ProductName = $i_data['name'];
                $item_data->ProductCount = $i_data['quantity'];
                $item_data->ProductPrice = $product->get_regular_price();
                $discount = ($product->get_regular_price() - $product->get_price())/$product->get_regular_price() * 100;
                $order_data->ProductDiscount = $order_data->ProductDiscount + ( ($product->get_regular_price() - $product->get_price()) * $i_data['quantity']);
                $item_data->DiscountPrecent = $discount;
                $item_data->ItemComment = "";
                array_push($order_items_array,$item_data);
                $wallet_flag = 1;
            }
        }
        if($flag){
            $order_data->CustomerClubAmount = $used;
            $order_data->TotalAmount =  $data['discount_total'] + $data['total'] + $used - $data['shipping_total'] + $order_data->ProductDiscount;
            $order_data->FinalAmount = $data['total'] + $used;
        }
        else{
            $order_data->CustomerClubAmount = 0;
            $order_data->TotalAmount =  $data['discount_total'] + $data['total'] + 0 - $data['shipping_total'] + $order_data->ProductDiscount;
            $order_data->FinalAmount = $data['total'] + 0;
        }
        if($wallet_flag){
            array_push($orders_array,$order_data);
        }
    }

    $order_response = new stdClass;
    $order_response->BaranStatus = 1;
    $order_response->LstOrderModel = $orders_array;
    $order_response->LstOrderItemsModel = $order_items_array;
    $response = new WP_REST_Response($order_response);
    $response->set_status( 200 );
    return $response;
}

/**
 *
 * Send ChargeWallet Orders to software
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 *
 *
 */

function ChargeWallets(WP_REST_Request $request){
    $charge_wallets = array();
    /*$deposit_args = array(
        'post_type' => 'product',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_type',
                'field' => 'slug',
                'terms' => array('deposit'),
                'operator' => 'IN',
                'include_children' => false
            )
        )
    );
    $deposits = get_posts($deposit_args);*/
    //foreach($deposits as $deposit){
    //$orders = get_orders_ids_by_product_id($deposit->ID);
    $order_arg = array(
        'post_type'  => 'shop_order',
        'post_status' => 'wc-completed',
        'numberposts' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key'     => 'clear',
                'value'   => '',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key' => '_fund_deposited',
                'value' => 'yes',
                'compare' => '='
            )
        )
    );

    $orders = get_posts($order_arg);
    foreach ($orders as $order){
        //$clear = get_post_meta($order,'clear',true);
        //if(!$clear){
        $order_obj = new WC_Order($order->ID);
        $order_data = new stdClass;
        $data = $order_obj->get_data();
        $items = $order_obj->get_items();
        $order_data->IdMonyWallet = 0;
        $order_data->IdWallet = $data['id'];
        $order_data->DateTime = $data['date_created'];
        foreach ($items as $item){
            $i_data = $item->get_data();
            $order_data->IdMonyWallet = $order_data->IdMonyWallet + $i_data['total'];
        }
        $order_data->CustomerId = $data['customer_id'];
        $user_obj = get_user_by('id', $data['customer_id']);
        $state =  WC()->countries->get_states( $order_obj->get_shipping_country() )[$order_obj->get_shipping_state()];
        $order_data->CustomerName =  $user_obj->display_name;
        $order_data->CustomerPhone = get_user_meta($data['customer_id'],'digits_phone_no')[0];
        $order_data->CustomerAddress = $state .' '.  $data['shipping']['city'] . ' '. $data['shipping']['address_1'] . ' ' .$data['shipping']['postcode'];
        $order_data->CustomerEmail = $user_obj->user_email;
        $order_data->CustomerCode = get_user_meta($data['customer_id'],'CustomerCode',true);
        $order_data->CustomerReagentCode = get_user_meta($data['customer_id'],'ReagentCode',true);
        array_push($charge_wallets,$order_data);
        /*}
        else{
            continue;
        }*/
    }
    //}
    $response = new WP_REST_Response($charge_wallets);
    $response->set_status( 200 );
    return $response;
}

/**
 *
 * clear orders and charge wallets send to software
 * because do not send again
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 *
 *
 */
function Clear_RecievedItems(WP_REST_Request $request){
    $items = $request->get_json_params();
    $items_array = explode('-',$items['ItemIds']);
    $output = array();
    foreach ($items_array as $item){
        update_post_meta($item, 'clear', 1);
        $p['BaranId'] = $item;
        $p['status'] = 1;
        array_push($output,$p);
    }
    $response = new WP_REST_Response($output);
    $response->set_status( 200 );
    return $response;

}

/**
 *
 * change order status from software
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 *
 *
 */

function ChangeOrderStatus(WP_REST_Request $request){
    $items = $request->get_json_params();
    $id = $items["Id"];
    $order = new WC_Order($id);
    $output = 0;
    if($order){
        $order->update_status($items['Status']);
        $output = 1;
    }
    $response = new WP_REST_Response($output);
    $response->set_status( 200 );
    return $response;

}

add_action( 'rest_api_init', function () {
    register_rest_route( 'custom-rest-woocommerce/v1', '/ProductSEND', array(
        'methods' => 'POST',
        'callback' => 'ProductSEND',
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'custom-rest-woocommerce/v1', '/SENDPics', array(
        'methods' => 'POST',
        'callback' => 'SENDPics',
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'custom-rest-woocommerce/v1', '/SENDPics2', array(
        'methods' => 'POST',
        'callback' => 'SENDPics2',
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'custom-rest-woocommerce/v1', '/CustomersSEND', array(
        'methods' => 'POST',
        'callback' => 'CustomersSEND',
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'custom-rest-woocommerce/v1', '/SendCoupons', array(
        'methods' => 'POST',
        'callback' => 'SendCoupons',
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'custom-rest-woocommerce/v1', '/Orders', array(
        'methods' => 'GET',
        'callback' => 'Orders',
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'custom-rest-woocommerce/v1', '/ChargeWallets', array(
        'methods' => 'GET',
        'callback' => 'ChargeWallets',
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'custom-rest-woocommerce/v1', '/Clear_RecievedItems', array(
        'methods' => 'POST',
        'callback' => 'Clear_RecievedItems',
    ) );
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'custom-rest-woocommerce/v1', '/ChangeOrderStatus', array(
        'methods' => 'POST',
        'callback' => 'ChangeOrderStatus',
    ) );
} );




