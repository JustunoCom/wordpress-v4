<?php
// If uninstall not called from WordPress exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// Delete option from options table
delete_option('ju4_justuno_api_key');
delete_option('ju4_justuno_woocommerce_token');
delete_option('justuno_sub_domain');
//remove any additional options and custom tables
