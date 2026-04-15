<?php
namespace Opencart\Catalog\Controller\Extension\DcMinimal\Module;

class DcMinimal extends \Opencart\System\Engine\Controller {
    /**
     * Event handler to load theme-specific language strings into the header and core pages.
     * Trigger: catalog/controller/common/header/before
     */
    public function beforeHeader(string &$route, array &$args): void {
        $this->load->language('extension/dc_minimal/common/header');
    }

    /**
     * Event handler to redirect core language requests to the theme directory if missing.
     * Trigger: language/{route}/before
     */
    public function beforeLanguage(string &$route, array &$args): void {
        // This is a more complex logic if we want to support any core page.
        // For now, we will explicitly load account translations in the account controllers.
    }
    
    /**
     * Event handler for Account pages.
     * Trigger: catalog/controller/account/{route}/before
     */
    public function beforeAccount(string &$route, array &$args): void {
        $parts = explode('/', $route);
        $page = end($parts);
        
        // Load our theme-based translation for this account page if it exists
        $this->load->language('extension/dc_minimal/account/' . $page);
    }

    /**
     * Event handler for Checkout pages.
     * Trigger: catalog/controller/checkout/{route}/before
     */
    public function beforeCheckout(string &$route, array &$args): void {
        $lang_page = str_replace('catalog/controller/', '', $route);
        $lang_page = str_replace('/before', '', $lang_page);
        
        // Load our theme-based translation for this checkout page if it exists
        $this->load->language('extension/dc_minimal/' . $lang_page);
    }

    /**
     * Event handler for Total modules.
     * Trigger: catalog/model/extension/opencart/total/{route}/getTotal/before
     */
    public function beforeTotal(string &$route, array &$args): void {
        $lang_page = str_replace('catalog/model/extension/opencart/', '', $route);
        $lang_page = str_replace('/getTotal/before', '', $lang_page);
        
        // Load our theme-based translation for this total module if it exists
        $this->load->language('extension/dc_minimal/' . $lang_page);
    }

    /**
     * Event handler to inject language strings into views globally.
     * Trigger: view/{route}/before
     */
    public function beforeView(string &$route, array &$data): void {
        $lang_page = $route;
        
        // Strip theme prefix if present
        if (strpos($lang_page, 'extension/dc_minimal/') === 0) {
            $lang_page = str_replace('extension/dc_minimal/', '', $lang_page);
        }
        
        // Strip core extension prefix if present
        if (strpos($lang_page, 'extension/opencart/') === 0) {
            $lang_page = str_replace('extension/opencart/', '', $lang_page);
        }

        if ($lang_page) {
            // Load base theme translations
            $base_translated = $this->load->language('extension/dc_minimal/vi-vn');
            if (is_array($base_translated)) {
                $data = array_merge($data, $base_translated);
            }

            // Load the page-specific language file
            $translated = $this->load->language('extension/dc_minimal/' . $lang_page);
            if (is_array($translated)) {
                $data = array_merge($data, $translated);
            }
            
            // Special handling for sub-views like 'checkout/cart_list' -> load 'checkout/cart'
            if (strpos($lang_page, '_') !== false) {
                $lang_base = explode('_', $lang_page)[0];
                $translated_base = $this->load->language('extension/dc_minimal/' . $lang_base);
                if (is_array($translated_base)) {
                    $data = array_merge($data, $translated_base);
                }
            }
        }
    }
}
