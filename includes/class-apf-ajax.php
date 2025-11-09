<?php
/**
 * Ajax Handler Class
 * File: includes/class-apf-ajax.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class APF_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_apf_filter_products', array($this, 'filter_products'));
        add_action('wp_ajax_nopriv_apf_filter_products', array($this, 'filter_products'));
    }
    
    public function filter_products() {
        check_ajax_referer('apf_filter_nonce', 'nonce');
        
        // Get filter parameters
        $filters = $this->get_filter_params();
        
        // Build WooCommerce query
        $args = $this->build_product_query($filters);
        
        // Execute query
        $query = new WP_Query($args);
        
        // Get products HTML
        ob_start();
        
        if ($query->have_posts()) {
            woocommerce_product_loop_start();
            
            while ($query->have_posts()) {
                $query->the_post();
                wc_get_template_part('content', 'product');
            }
            
            woocommerce_product_loop_end();
        } else {
            echo '<p class="woocommerce-info">' . __('No products found matching your filters.', 'ajax-product-filter') . '</p>';
        }
        
        $products_html = ob_get_clean();
        
        wp_reset_postdata();
        
        // Send response
        wp_send_json_success(array(
            'products' => $products_html,
            'count' => $query->found_posts,
            'found_posts' => $query->found_posts,
        ));
    }
    
    private function get_filter_params() {
        $filters = array();
        
        // Get all POST data
        $post_data = $_POST;
        
        // Extract filter parameters
        foreach ($post_data as $key => $value) {
            if (strpos($key, 'apf_') === 0 && $key !== 'apf_action') {
                $filter_key = str_replace('apf_', '', $key);
                $filters[$filter_key] = $value;
            }
        }
        
        return $filters;
    }
    
    private function build_product_query($filters) {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array('relation' => 'AND'),
            'meta_query' => array('relation' => 'AND'),
        );
        
        // Process taxonomies (attributes)
        foreach ($filters as $key => $value) {
            if (empty($value)) {
                continue;
            }
            
            // Handle taxonomy filters (pa_color, pa_shape, etc.)
            if (strpos($key, 'pa_') === 0) {
                $values = is_array($value) ? $value : array($value);
                
                $args['tax_query'][] = array(
                    'taxonomy' => $key,
                    'field' => 'slug',
                    'terms' => $values,
                    'operator' => 'IN',
                );
            }
        }
        
        // Price range filter
        if (isset($filters['price_range']) && !empty($filters['price_range'])) {
            $range = explode('-', $filters['price_range']);
            
            if (count($range) === 2) {
                $args['meta_query'][] = array(
                    'key' => '_price',
                    'value' => array(floatval($range[0]), floatval($range[1])),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }
        }
        
        // Quick filters
        if (isset($filters['quick_filter']) && !empty($filters['quick_filter'])) {
            switch ($filters['quick_filter']) {
                case 'bestsellers':
                    $args['meta_key'] = 'total_sales';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                    
                case 'new_arrivals':
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                    break;
                    
                case 'price_95':
                    $args['meta_query'][] = array(
                        'key' => '_price',
                        'value' => 95,
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    );
                    break;
            }
        }
        
        // Rating filter
        if (isset($filters['rating']) && !empty($filters['rating'])) {
            $args['meta_query'][] = array(
                'key' => '_wc_average_rating',
                'value' => intval($filters['rating']),
                'type' => 'NUMERIC',
                'compare' => '>=',
            );
        }
        
        // Stock status filter
        if (isset($filters['stock_status']) && !empty($filters['stock_status'])) {
            $stock_values = is_array($filters['stock_status']) ? $filters['stock_status'] : array($filters['stock_status']);
            
            $args['meta_query'][] = array(
                'key' => '_stock_status',
                'value' => $stock_values,
                'compare' => 'IN',
            );
        }
        
        // On sale filter
        if (isset($filters['on_sale']) && $filters['on_sale'] === 'yes') {
            $args['post__in'] = array_merge(array(0), wc_get_product_ids_on_sale());
        }
        
        return apply_filters('apf_product_query_args', $args, $filters);
    }
}

new APF_Ajax();
