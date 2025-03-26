<?php
add_action('admin_menu', 'v4_justuno_plugin_menu');
if (!function_exists('v4_justuno_plugin_menu')) {
    function v4_justuno_plugin_menu()
    {
        add_options_page('Justuno Reimagined', 'Justuno Reimagined', 'manage_options', 'ju4_justuno-settings-conf', 'ju4_justuno_plugin_page');
    }
}

if (!function_exists('ju4_justuno_plugin_page')) {
    function ju4_justuno_plugin_page()
    {
        $link = 'http://www.justuno.com/getstarted.html';
        ?>
        <div class="wrap">
            <h2>Justuno Reimagined</h2>
            <form action="options.php" method="post">
                <?php settings_fields('ju4_justuno_base_settings'); ?>
                <?php do_settings_sections('ju4_justuno_base_settings'); ?>
                <input name="Submit" class="button button-primary" type="submit" value="Save Changes" />
                <?php if (class_exists('WooCommerce')): ?>
                    <input name="button" class="button button-secondary" type="button" onclick="justuno_generate_random_token()"
                        value="Regenerate Token" />
                <?php endif; ?>
            </form>
        </div>
        <?php
    }
}

add_filter('admin_enqueue_scripts', 'ju4_justuno_admin_js_files');
if (!function_exists('ju4_justuno_admin_js_files')) {
    function ju4_justuno_admin_js_files($files)
    {
        $script_version = '1.0.0';
        wp_enqueue_script('my_custom_script', plugins_url('/js/admin.js', __FILE__), array('jquery'), $script_version, true);
    }
}

add_action("admin_init", "ju4_justuno_display_options");
if (!function_exists('ju4_justuno_display_options')) {
    function ju4_justuno_display_options()
    {
        add_settings_section(
            'ju4_justuno_api_key',
            'Integration Settings',
            'ju4_justuno_api_key_description',
            'ju4_justuno_base_settings'
        );

        // Register a callback
        register_setting(
            'ju4_justuno_base_settings',
            'ju4_justuno_api_key',
            'trim'
        );

        add_settings_field(
            'ju4_justuno_api_key',
            'Justuno Account Number',
            'ju4_justuno_api_key_field',
            'ju4_justuno_base_settings',
            'ju4_justuno_api_key',
            array('label_for' => 'ju4_justuno_api_key')
        );


        // -----------------------------------------

        add_settings_section(
            'justuno_sub_domain',
            'Visibility Boost Domain',
            'justuno_sub_domain_description',
            'ju4_justuno_base_settings'
        );

        // Register a callback
        register_setting(
            'ju4_justuno_base_settings',
            'justuno_sub_domain',
            'trim'
        );

        add_settings_field(
            'justuno_sub_domain',
            'Justuno Subdomain URL',
            'justuno_sub_domain_field',
            'ju4_justuno_base_settings',
            'justuno_sub_domain',
            array('label_for_sub_domain' => 'justuno_sub_domain')
        );

        // -----------------------------------------


        if (class_exists('WooCommerce')) {
            add_settings_section(
                'ju4_justuno_woocommerce_token',
                'WooCommerce Token',
                'ju4_justuno_woocommerce_token_description',
                'ju4_justuno_base_settings'
            );

            // Register a callback
            register_setting(
                'ju4_justuno_base_settings',
                'ju4_justuno_woocommerce_token',
                'trim'
            );

            add_settings_field(
                'ju4_justuno_woocommerce_token',
                'WooCommerce Token',
                'ju4_justuno_woocommerce_token_field',
                'ju4_justuno_base_settings',
                'ju4_justuno_woocommerce_token',
                array('label_for' => 'ju4_justuno_woocommerce_token')
            );
        }
    }

    function ju4_justuno_api_key_description()
    {
        echo '<p>You need to have an existing account at justuno.com.<br /><a target="_blank" href="https://www.justuno.com/get-started/">Click here</a> to create a Free Trial account if needed.</p><p style="margin-bottom: 25px;">For more help with this screen, <a target="_blank" href="https://hub.justuno.com/knowledge/install-justuno-on-your-wordpress-woocommerce-store">click here</a><br /></p>';
    }

    function ju4_justuno_api_key_field($args)
    {
        $data = esc_attr(get_option('ju4_justuno_api_key', ''));

        printf(
            '<input type="text" name="ju4_justuno_api_key" value="%1$s" class="all-options" id="%2$s" /><a style="margin-left: 20px" class="button button-primary" target="_blank" href="https://portal.justuno.com/app/account/embed-code">Find My Justuno Account Number</a>',
            esc_attr($data),
            esc_attr($args['label_for'])
        );
    }

    // ------------------------------------------------
    function justuno_sub_domain_description()
    {
        echo '<p>A subdomain will act as proxy to our server that would server all the files and API endpoints to your website<br /></p>';
    }



    function justuno_sub_domain_field($args)
    {
        $result_data = esc_attr(get_option('justuno_sub_domain', 'justone.ai'));

        printf(
            '<input type="text" name="justuno_sub_domain" value="%1$s" class="all-options" id="%2$s" />',
            esc_attr($result_data),
            esc_attr($args['label_for_sub_domain'])
        );
    }
    // ------------------------------------------------



    function ju4_justuno_woocommerce_token_description()
    {
        echo '<p>This is an autogenerated token for you WooCommerce data in Justuno.<br /> Please place this token inside your dashboard to begin the data collection process.</p>';
    }

    function ju4_justuno_woocommerce_token_field($args)
    {
        $data = esc_attr(get_option('ju4_justuno_woocommerce_token', ''));

        printf(
            '<input type="text" name="ju4_justuno_woocommerce_token" class="all-options" value="%1$s" id="%2$s" />',
            esc_attr($data),
            esc_attr($args['label_for'])
        );
    }
}
