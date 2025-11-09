<?php
/**
 * Plugin Name: Advanced Ajax Product Filter
 * Plugin URI: pratiklamichhane.com.np
 * Description: Premium Ajax product filter with interactive price slider, color/image swatches, and professional design for WooCommerce
 * Version: 2.0.0
 * Author: Pratik Lamichhane
 * Author URI: pratiklamichhane.com.np
 * License: GPL v2 or later
 * Text Domain: ajax-product-filter
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('APF_VERSION', '2.0.0');
define('APF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('APF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('APF_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Advanced_Ajax_Product_Filter {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        require_once APF_PLUGIN_DIR . 'includes/class-apf-admin.php';
        require_once APF_PLUGIN_DIR . 'includes/class-apf-settings.php';
        require_once APF_PLUGIN_DIR . 'includes/class-apf-frontend.php';
        require_once APF_PLUGIN_DIR . 'includes/class-apf-ajax.php';
        require_once APF_PLUGIN_DIR . 'includes/class-apf-widget.php';
    }
    
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'check_requirements'));
        add_action('init', array($this, 'load_textdomain'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('widgets_init', array($this, 'register_widgets'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function check_requirements() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return false;
        }
        return true;
    }
    
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('Advanced Ajax Product Filter requires WooCommerce to be installed and active.', 'ajax-product-filter'); ?></p>
        </div>
        <?php
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('ajax-product-filter', false, dirname(APF_PLUGIN_BASENAME) . '/languages');
    }
    
    public function enqueue_frontend_assets() {
        if (!is_shop() && !is_product_taxonomy()) {
            return;
        }
        
        wp_enqueue_style(
            'apf-frontend',
            APF_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            APF_VERSION
        );
        
        wp_enqueue_script(
            'apf-frontend',
            APF_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            APF_VERSION,
            true
        );
        
        wp_localize_script('apf-frontend', 'apf_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('apf_filter_nonce'),
            'shop_url' => get_permalink(wc_get_page_id('shop')),
            'loading_text' => __('Loading...', 'ajax-product-filter'),
        ));
    }
    
    public function enqueue_admin_assets($hook) {
        if ('widgets.php' !== $hook && strpos($hook, 'apf-settings') === false) {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery-ui-sortable');
        
        wp_enqueue_style(
            'apf-admin',
            APF_PLUGIN_URL . 'assets/css/admin.css',
            array('wp-color-picker'),
            APF_VERSION
        );
        
        wp_enqueue_script(
            'apf-admin',
            APF_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker', 'jquery-ui-sortable'),
            APF_VERSION,
            true
        );
    }
    
    public function register_widgets() {
        register_widget('APF_Filter_Widget');
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Product Filter', 'ajax-product-filter'),
            __('Product Filter', 'ajax-product-filter'),
            'manage_options',
            'apf-settings',
            array('APF_Settings', 'render_settings_page'),
            'dashicons-filter',
            56
        );
    }
    
    public function activate() {
        // Create default options
        $default_options = array(
            'enable_ajax' => 'yes',
            'show_count' => 'yes',
            'animation' => 'fade',
            'filter_position' => 'left',
            'mobile_breakpoint' => '768',
            'results_text' => 'frames',
        );
        
        if (!get_option('apf_general_settings')) {
            update_option('apf_general_settings', $default_options);
        }
        
        // Create default filter configuration
        if (!get_option('apf_filter_config')) {
            $default_filters = array(
                array(
                    'id' => 'quick_filters',
                    'title' => 'Shop By',
                    'type' => 'quick_filter',
                    'enabled' => true,
                    'expanded' => false,
                    'options' => array(
                        array('value' => 'bestsellers', 'label' => 'Bestsellers'),
                        array('value' => 'price_95', 'label' => '$95 frames'),
                        array('value' => 'new_arrivals', 'label' => 'New arrivals'),
                    ),
                ),
                array(
                    'id' => 'shape',
                    'title' => 'Shape',
                    'type' => 'taxonomy',
                    'taxonomy' => 'pa_shape',
                    'enabled' => true,
                    'expanded' => false,
                    'display_type' => 'checkbox',
                    'show_count' => true,
                ),
                array(
                    'id' => 'color',
                    'title' => 'Color',
                    'type' => 'taxonomy',
                    'taxonomy' => 'pa_color',
                    'enabled' => true,
                    'expanded' => false,
                    'display_type' => 'color_swatch',
                    'show_count' => false,
                ),
                array(
                    'id' => 'price',
                    'title' => 'Frame price',
                    'type' => 'price_range',
                    'enabled' => true,
                    'expanded' => false,
                    'ranges' => array(
                        array('min' => 0, 'max' => 50, 'label' => 'Under $50'),
                        array('min' => 50, 'max' => 100, 'label' => '$50 - $100'),
                        array('min' => 100, 'max' => 150, 'label' => '$100 - $150'),
                        array('min' => 150, 'max' => 200, 'label' => '$150 - $200'),
                        array('min' => 200, 'max' => 999999, 'label' => '$200+'),
                    ),
                ),
            );
            update_option('apf_filter_config', $default_filters);
        }
        
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function apf_init() {
    return Advanced_Ajax_Product_Filter::get_instance();
}
add_action('plugins_loaded', 'apf_init');