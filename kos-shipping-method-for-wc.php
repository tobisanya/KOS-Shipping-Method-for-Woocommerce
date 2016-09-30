<?php
/*
Plugin Name: KOS Shipping Method for Woocommerce
Plugin URI: https://github.com/hoshomoh/KOS-Shipping-Method-for-Woocommerce
Description: A Wordpress WooCommerce Plugin that add Kos shipping method to woocommerce and also calculates shipping price on checkout.
Version: 1.0.0
Author: Oforomeh Oshomo 
Author URI: http://hoshomoh.github.io/
*/
/**
 * Copyright (c) 2016 Oforomeh Oshomo. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 **/
if ( ! class_exists( 'WC_Kos_Shipping_Method' ) ) :
    function kos_shipping_method()
    {
        class WC_Kos_Shipping_Method extends WC_Shipping_Method
        {
            /**
             * Construct the plugin.
             */
            public $error = null;

            public function __construct()
            {
                $this->id = 'kos_shipping_method_for_wc';
                $this->method_title = __('KOS Shipping', 'kos_shipping_method_for_wc');
                $this->method_description = __('Custom Shipping Method for KOS', 'kos_shipping_method_for_wc');
                $this->init();
                $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
                $this->title = isset($this->settings['title']) ? $this->settings['title'] : __('KOS Shipping', 'kos_shipping_method_for_wc');
                $this->client_token  = isset($this->settings['client_token']) ? $this->settings['client_token'] : '';
                $this->drop_off_location  = isset($this->settings['drop_off_location']) ? $this->settings['drop_off_location'] : '';
            }

            /**
             * Initialize the plugin.
             */
            public function init()
            {
                // Checks if All Countries Counties For WooCommerce is installed.
                if (class_exists('WC_All_Country_Counties')) {
                    // Load the settings API
                    $this->init_form_fields();
                    $this->init_settings();

                    // Save settings in admin if you have any defined
                    add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                } else {
                    // throw an admin error if you like
                    $this->showError(__('KOS Shipping Method for Woocommerce is enabled but not effective. It requires All Countries Counties For WooCommerce in order to work. Kindly Install/Activate All Countries Counties For WooCommerce.',
                        'kos-shipping-method-for-wc'));

                    return false;
                }
            }

            /**
             *
             */
            public function admin_options() {
                $this->environment_check();
                parent::admin_options();
            }

            /**
             *
             */
            private function environment_check() {
                if (empty($this->title) || empty($this->client_token) || empty($this->drop_off_location)) {
                    echo '<div class="error">
                            <p>' . __( 'The title, client token and drop-off location are required fields. Without them the shipping calculator will not work.', 'kos_shipping_method_for_wc' ) . '</p>
                        </div>';
                }
            }

            /**
             * Define settings field for this shipping
             * @return void
             */
            public function init_form_fields()
            {

                $this->form_fields = array(

                    'enabled' => array(
                        'title' => __('Enable', 'kos_shipping_method_for_wc'),
                        'type' => 'checkbox',
                        'description' => __('Enable this shipping.', 'kos_shipping_method_for_wcv'),
                        'default' => 'yes'
                    ),

                    'title' => array(
                        'title' => __('Title', 'kos_shipping_method_for_wc'),
                        'type' => 'text',
                        'description' => __('Title to be display on site', 'kos_shipping_method_for_wc'),
                        'default' => __('KOS Shipping', 'kos_shipping_method_for_wc')
                    ),

                    'client_token' => array(
                        'title' => __('Client Token', 'kos_shipping_method_for_wc'),
                        'type' => 'text',
                        'description' => __('Ask KOS for you client token', 'kos_shipping_method_for_wc')
                    ),

                    'drop_off_location' => array(
                        'title' => __('Select Drop-off Location', 'kos_shipping_method_for_wc'),
                        'type' => 'select',
                        'options'     => array(
                            '' => __('Select an option…', 'kos_shipping_method_for_wc' )
                        )
                    ),

                );

            }

            /**
             * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
             *
             * @access public
             * @param mixed $package
             * @return void
             */
            public function calculate_shipping( $package ) {

                $default_location = $this->settings['drop_off_location'];
                $weight = 0;
                $cost = 0;
                $country = $package["destination"]["country"];
                $local_government = $package["destination"]["local_government"];

                foreach ( $package['contents'] as $key => $value ) {
                    $_product = $value['data'];
                    $weight = $weight + $_product->get_weight() * $value['quantity'];
                }

                $weight = wc_get_weight( $weight, 'kg' );

                if( $weight <= 10 ) {
                    $cost = 300;
                } else {
                    $cost = 200;
                }

                $rate = array(
                    'id' => $this->id,
                    'label' => $this->title,
                    'cost' => $cost
                );

                $this->add_rate( $rate );

            }

            /**
             * @return mixed
             */
            public function plugin_path()
            {
                return untrailingslashit(plugin_dir_path(__FILE__));
            }

            /**
             * Output notice
             *
             * @param string $message
             * @param bool $success
             */
            public function outputNotice($message, $success = true)
            {
                echo '
                <div class="' . ($success ? 'updated' : 'error') . '" style="position: relative;">
                    <p>' . $message . '</p>
                </div>
            ';
            }

            /**
             * Show error
             *
             * @param string $error
             */
            public function showError($error)
            {
                $this->error = $error;
                add_action('admin_notices', array(&$this, 'outputLastError'));
            }

            /**
             * Output last error
             */
            public function outputLastError()
            {
                $this->outputNotice($this->error, false);
            }

        }
    }

    add_action( 'woocommerce_shipping_init', 'kos_shipping_method' );

    function add_kos_shipping_method( $methods ) {
        $methods[] = 'WC_Kos_Shipping_Method';
        return $methods;
    }

    add_filter( 'woocommerce_shipping_methods', 'add_kos_shipping_method' );

endif;
