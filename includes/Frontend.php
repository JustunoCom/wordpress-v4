<?php

require_once dirname(__FILE__) . '/JustRESTManager.php';

add_action('init', function () {
    add_rewrite_endpoint('justuno', EP_PERMALINK);
});

add_action('template_redirect', function () {
    global $wp_query;
    if ($wp_query->query_vars['pagename'] === "v4-justuno-sync-job" || $wp_query->query_vars['pagename'] === "justuno-sync-job") {
        header('Content-type: application/json');
        $objRESTManager = new Integrations\ju4_JustRESTManager();
        $objRESTManager->entertainCall();
        die;
    }
});

// ------------------------------------------------
add_action('wp_head', 'ju4_justuno_script_for_subdomain');
if (!function_exists('ju4_justuno_script_for_subdomain')) {
    function ju4_justuno_script_for_subdomain()
    {
        $data = esc_attr(get_option('ju4_justuno_api_key', ''));
        $baseURL = "https://" . esc_attr(get_option('ju4_justuno_api_key', ''));
        $apiURL = "https://api.justuno.com";
        $objRESTManager = new Integrations\ju4_JustRESTManager();
        $code = $objRESTManager->getConversionTrackingCodes();
        $data_field_result = get_option('ju4_justuno_sub_domain');
        if ($data_field_result != "") {
            $baseURL = "https://" . get_option('ju4_justuno_sub_domain');
        }
        global $post;
        echo '<script data-cfasync="false">window.ju4_auth="' . esc_attr($data) . '";window.ju4_num="' . esc_attr($data) . '";window.ju4_asset_host="' . esc_attr($baseURL) . '/embed";window.ju4_pApi="' . esc_attr($baseURL) . '";(function(i,s,o,g,r,a,m){i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)};a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,"script",ju4_asset_host+"/ju_woocommerce_init.js?v=2","ju4app");' . $code . '</script>';
    }
}

// ------------------------------------------------


// define the woocommerce_thankyou callback 
function ju4_action_woocommerce_thankyou($order_get_id)
{
    $code = '';
    $order_id = absint($order_get_id);
    if ($order_id > 0) {
        $order = wc_get_order($order_id);
        $cartItems = [];
        foreach ($order->get_items() as $item) {
            $cartItems[] = '{
                            productID:' . $item->get_product_id() . ',
                            variationID:' . ($item->get_variation_id() > 0 ? $item->get_variation_id() : $item->get_product_id()) . ',
                            sku:"' . $item->get_product()->get_sku() . '",
                            name:"' . $item->get_name() . '",
                            qty:' . floatval($item->get_quantity()) . ',
                            price:' . floatval($item->get_total()) . '
                        }';
        }

        $coupons = '';
        if (method_exists($order, 'get_used_coupons')) {
            $coupons = $order->get_used_coupons();
        } else {
            $coupons = $order->get_coupon_codes();
        }
        $code .= '
        ju4app("order", {
            orderID: "' . $order->get_id() . '", 
            grandTotal:' . floatval($order->get_total()) . ',
            subTotal:' . floatval($order->get_subtotal()) . ',
            tax:' . floatval($order->get_total_tax()) . ',
            shipping:' . floatval($order->get_shipping_total()) . ',
            discount: ' . floatval($order->get_discount_total()) . ',
            currency: "' . $order->get_currency() . '",
            discountCodes: ' . json_encode($coupons) . ',
            cartItems:[' . join(",", $cartItems) . '],
        });';
    }
    echo '<script type="text/javascript">' . $code . '</script>';
}

// add the action 
add_action('woocommerce_thankyou', 'ju4_action_woocommerce_thankyou', 10, 1);




function my_custom_add_to_cart_action($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
{
    wp_add_inline_script('jquery', "console.log('Product added: ' + " . $product_id . ")");
}

function ju4_action_cart_tracking()
{
    $code = '';
    $cart = \WC()->cart;
    $totals = $cart->get_totals();
    $code .= 'ju4app("cart", {
	total:' . $totals['total'] . ',
	subtotal:' . $totals['subtotal'] . ',
	tax:' . $totals['total_tax'] . ',
	shipping:' . $totals['shipping_total'] . ',
	currency:"' . get_woocommerce_currency() . '",
	}
);';
    $cartItems = $cart->get_cart_contents();
    if (count($cartItems) > 0) {
        $code .= "ju4app('cartItems', [";
        foreach ($cart->get_cart() as $key => $item) {
            $cartItem = $cart->get_cart_item($key);
            $attrs = '';
            if ($cartItem['variation_id'] > 0) {
                foreach ($cartItem['variation'] as $key => $value) {
                    $attrs .= str_replace('attribute_', '', str_replace('pa_', '', $key)) . ": '{$value}', ";
                }
            }
            $variationId = $cartItem['variation_id'] > 0 ? $cartItem['variation_id'] : $cartItem['product_id'];
            $product = $cartItem['data'];
            $p = floatval($product->get_price());
            $code .= "
{ productID: '{$cartItem['product_id']}', variationID: '{$variationId}', sku:'{$product->get_sku()}', qty: {$cartItem['quantity']}, price: {$p}},";
        }
        $code = substr($code, 0, -1);
        $code .= "])";
    }
    wp_add_inline_script('jquery', 'setTimeout(function(){' . $code . '}, 2000);');
}

add_action('woocommerce_add_to_cart', 'ju4_action_cart_tracking');