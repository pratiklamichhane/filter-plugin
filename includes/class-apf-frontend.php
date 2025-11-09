<?php
/**
 * Frontend Display Class
 * File: includes/class-apf-frontend.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class APF_Frontend {
    
    public function __construct() {
        add_action('wp_head', array($this, 'add_custom_styles'));
        add_filter('woocommerce_product_query', array($this, 'filter_products'), 10, 2);
    }
    
    public function add_custom_styles() {
        $settings = get_option('apf_style_settings', array());
        
        $primary_color = isset($settings['primary_color']) ? $settings['primary_color'] : '#2563eb';
        $button_bg = isset($settings['button_bg_color']) ? $settings['button_bg_color'] : '#000000';
        $button_text = isset($settings['button_text_color']) ? $settings['button_text_color'] : '#ffffff';
        $custom_css = isset($settings['custom_css']) ? $settings['custom_css'] : '';
        
        ?>
        <style type="text/css">
            :root {
                --apf-primary-color: <?php echo esc_attr($primary_color); ?>;
                --apf-button-bg: <?php echo esc_attr($button_bg); ?>;
                --apf-button-text: <?php echo esc_attr($button_text); ?>;
            }
            
            .apf-filter-option input[type="checkbox"]:checked + span,
            .apf-filter-option input[type="radio"]:checked + span {
                color: var(--apf-primary-color);
            }
            
            .apf-color-swatch.selected .apf-color-circle {
                border-color: var(--apf-primary-color);
                box-shadow: 0 0 0 2px var(--apf-primary-color);
            }
            
            .apf-filter-tag {
                background-color: var(--apf-primary-color);
            }
            
            .apf-quiz-link {
                color: var(--apf-primary-color);
            }
            
            <?php echo wp_kses_post($custom_css); ?>
        </style>
        <?php
    }
    
    public function filter_products($q, $query) {
        // This hook is for non-Ajax filtering
        // The actual filtering is handled in class-apf-ajax.php
        return $q;
    }
}

new APF_Frontend();
