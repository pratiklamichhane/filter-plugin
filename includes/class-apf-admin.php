<?php
/**
 * Admin Class
 * File: includes/class-apf-admin.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class APF_Admin {
    
    public function __construct() {
        add_action('admin_notices', array($this, 'admin_notices'));
        add_filter('plugin_action_links_' . APF_PLUGIN_BASENAME, array($this, 'plugin_action_links'));
    }
    
    public function admin_notices() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            ?>
            <div class="notice notice-error">
                <p><?php _e('Advanced Ajax Product Filter requires WooCommerce to be installed and activated.', 'ajax-product-filter'); ?></p>
            </div>
            <?php
        }
    }
    
    public function plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=apf-settings') . '">' . __('Settings', 'ajax-product-filter') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

new APF_Admin();