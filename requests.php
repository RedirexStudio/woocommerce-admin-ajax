<?php
/* JS Update function for product */ 
function update_product($post_id) {
    if( $_POST['formData'] ){
        // get all data from product form
        $params = array();
        parse_str($_POST['formData'], $params); 

        // set all data through WC_Product
        switch ($params['product-type']) {
            case 'simple':
                $product = new WC_Product_Simple($params['post_ID']);
                break;
            case 'grouped':
                $product = new WC_Product_Grouped($params['post_ID']);
                break;
            case 'external':
                $product = new WC_Product_External($params['post_ID']);
                break;
            case 'variable':
                $product = new WC_Product_Variable($params['post_ID']);
                break;
            
            default:
                $product = new WC_Product_Simple($params['post_ID']);
                break;
        }

        $product->set_name($params['post_title']); // name of product
        $product->set_description($params['content']); // main description
        $product->set_short_description($params['excerpt']);  // short description

        // change visibility option and dependencies of post status
        switch ($params['visibility']) {
            case 'public':
                $product->set_status('publish');
                wp_update_post(['ID' => $params['post_ID'],'post_password' => '']); //remove password if before we had password status
                break;
            case 'password':
                wp_update_post(['ID' => $params['post_ID'], 'post_password' => $params['post_password']]); // set product post spassword
                break;
            case 'private':
                $product->set_status($params['visibility']); // make private
                break;
            
            default:
                //silence is golden
                break;
        }

        // if status equel pending or draft - we will automaticly publish post
        if($params['original_post_status'] == 'pending' || $params['original_post_status'] == 'draft') $product->set_status('publish');
        
        // change post name (slug - url)
        $product->set_slug($params['post_name']);

        // set general image
        $product->set_image_id($params['_thumbnail_id']);

        // set visibility into catalog
        $product->set_catalog_visibility($params['_visibility']);
        
        // set created date
        $product->set_date_created($params['aa'].'-'.$params['mm'].'-'.$params['jj'].' '.$params['hh'].':'.$params['mn']);

        // chack if product set featured - set it
        if( $params['_featured'] == 'on' ){
            $product->set_featured(true);
        } else {
            $product->set_featured(false);
        }
        
        // set categories
        $product->set_category_ids($params['tax_input']['product_cat']);

        // set product tags
        wp_set_object_terms($params['post_ID'], $params['tax_input']['product_tag'], 'product_tag');
        
        // and... we add wc meta that placed in array by default
        foreach($params['meta'] as $meta_id => $meta_value){
                $product->update_meta_data($meta_item['key'], $meta_item['value'], $meta_id);
        }

        //gallery
        $product->set_gallery_image_ids($params['product_image_gallery']);

        // Woocommerce Metabox
            // virtual/download status
            // virtual status
            if( $params['_virtual'] == 'on' ){
                $product->set_virtual(true);
            } else {
                $product->set_virtual(false);
            }
            // download status
            if( $params['_downloadable'] == 'on' ){
                $product->set_downloadable(true);
            } else {
                $product->set_downloadable(false);
            }

        // set stock status
        $product->set_stock_status($params['_stock_status']);

        // set regular price
        $product->set_regular_price($params['_regular_price']);

        // set sale price
        $product->set_sale_price($params['_sale_price']);

        // set dates of sale price to metadata
        update_post_meta( $product->id, '_sale_price_dates_from', $params['_sale_price_dates_from'] );
        update_post_meta( $product->id, '_sale_price_dates_to', $params['_sale_price_dates_to'] );

        // set SKU
        $product->set_sku($params['_sku']);

        // chack if product set manage stock - set it
        if( $params['_manage_stock'] == 'yes' ){
            $product->set_manage_stock(true);
        } else {
            $product->set_manage_stock(false);
        }

        // set stock quantity
        $product->set_stock_quantity($params['_stock']);
        
        // set backorders
        $product->set_backorders($params['_backorders']);

        // set low stock amount
        $product->set_low_stock_amount($params['_low_stock_amount']);

        // set sold individually
        $product->set_sold_individually($params['_sold_individually']);

        // set weight in delivery tab
        $product->set_weight($params['_weight']);

        // set length in delivery tab
        $product->set_length($params['_length']);

        // set width in delivery tab
        $product->set_width($params['_width']);

        // set height in delivery tab
        $product->set_height($params['_height']);

        // set product shipping class in delivery tab
        $product->set_shipping_class_id($params['product_shipping_class']);

        // set product upsell ids
        $product->set_upsell_ids($params['upsell_ids']);

        // set product crosssell ids
        $product->set_cross_sell_ids($params['crosssell_ids']);

        // set purchase note
        $product->set_purchase_note($params['_purchase_note']);
        
        // set menu order
        $product->set_menu_order($params['menu_order']);

        // chack if comment status allowed - set it
        if($params['comment_status'] == 'open'){
            $product->set_reviews_allowed(true);
        } else {
            $product->set_reviews_allowed(false);
        }

        // Downloads
        // Check is downloadable
        if( $params['_downloadable'] == 'on' ){
            $download_arr = array(); // create array for marge all post data
            // create md5 key and push id and url
            foreach( $params['_wc_file_urls'] as $id=>$url ){
                    $hash_url = md5( $url );
                    $download_arr[$id]['id'] = $hash_url;
                    $download_arr[$id]['url'] = $url;
            }
            // add name to download_arr
            foreach( $params['_wc_file_names'] as $id=>$name ){
                    $download_arr[$id]['name'] = $name;
            }

            // create wc download objects
            foreach($download_arr as $i => $downloads){
                ${'download_object_' . $i} = new WC_Product_Download();
                ${'download_object_' . $i}->set_id( $downloads['id'] );
                ${'download_object_' . $i}->set_name( $downloads['name'] );
                ${'download_object_' . $i}->set_file( $downloads['url'] );
                $downloads_obj[$downloads['id']] = ${'download_object_' . $i};
            }
            
            // set download objects
            $product->set_downloads($downloads_obj);

            // set download limit
            $product->set_download_limit($params['_download_limit']);
            // set download expiry
            $product->set_download_expiry($params['_download_expiry']);
        }
        
        $product->save();

        // set fields of grouped type
        if( $params['product-type'] == 'simple' ){
            // change post type by class name
            $classname = WC_Product_Factory::get_product_classname( $params['post_ID'], $params['product-type'] );
            $product   = new $classname( $params['post_ID'] );
            // ...and save that
            $product->save();
        }
        // set fields of grouped type
        if( $params['product-type'] == 'grouped' ){
            // change post type by class name
            $classname = WC_Product_Factory::get_product_classname( $params['post_ID'], $params['product-type'] );
            $product   = new $classname( $params['post_ID'] );
            // ...and save that
            $product->save();
        }
        // set fields of external type
        if( $params['product-type'] == 'external' ){
            // change post type by class name
            $classname = WC_Product_Factory::get_product_classname( $params['post_ID'], $params['product-type'] );
            $product   = new $classname( $params['post_ID'] );
            // set product url
            $product->set_product_url($params['_product_url']);
            // set button text
            $product->set_button_text($params['_button_text']);
            // ...and save that
            $product->save();
        }
        // set fields of variable type
        if( $params['product-type'] == 'variable' ){
            // change post type by class name
            $classname = WC_Product_Factory::get_product_classname( $params['post_ID'], $params['product-type'] );
            $product   = new $classname( $params['post_ID'] );
            // ...and save that
            $product->save();
        }

        if ( function_exists( 'pll_the_languages' ) ) {
            $status = ['btn' => __('Update'), 'status' => __('Published'), 'success_msg' => __('Product is saved!')];
        } else {
            $status = ['btn' => __('Update', 'textdomain'), 'status' => __('Published', 'textdomain'), 'success_msg' => __('Product is updated!', 'textdomain')];
        }
        $status = json_encode($status);
        echo $status;

        wp_die(); 
    }
}
add_action('wp_ajax_save_post', 'update_product');