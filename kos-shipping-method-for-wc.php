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
    class WC_Kos_Shipping_Method {
        /**
         * Construct the plugin.
         */
        public $error = null;

        public function __construct() {
            add_action( 'plugins_loaded', array( $this, 'init' ) );
        }

        /**
        * Initialize the plugin.
        */
        public function init() {
            // Checks if All Countries Counties For WooCommerce is installed.
            if ( class_exists( 'WC_All_Country_Counties' ) ) {

            } else {
                // throw an admin error if you like
                $this->showError( __( 'KOS Shipping Method for Woocommerce is enabled but not effective. It requires All Countries Counties For WooCommerce in order to work. Kindly Install/Activate All Countries Counties For WooCommerce.',
                'kos-shipping-method-for-wc' ) );

                return false;
            }
        }

        /**
         * @return mixed
         */
        public function plugin_path() {
            return untrailingslashit( plugin_dir_path( __FILE__ ) );
        }

        /**
         * Output notice
         *
         * @param string $message
         * @param bool $success
         */
        public function outputNotice( $message, $success = true ) {
            echo '
                <div class="' . ( $success ? 'updated' : 'error' ) . '" style="position: relative;">
                    <p>' . $message . '</p>
                </div>
            ';
        }

        /**
         * Show error
         *
         * @param string $error
         */
        public function showError( $error ) {
            $this->error = $error;
            add_action( 'admin_notices', array( &$this, 'outputLastError' ) );
        }

        /**
         * Output last error
         */
        public function outputLastError() {
            $this->outputNotice( $this->error, false );
        }

    }

    $WC_Kos_Shipping_Method = new WC_Kos_Shipping_Method( __FILE__ );

endif;
