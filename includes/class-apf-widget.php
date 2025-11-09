<?php
/**
 * Filter Widget - Reads configuration from database
 * File: includes/class-apf-widget.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class APF_Filter_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'apf_filter_widget',
            __('Ajax Product Filter', 'ajax-product-filter'),
            array(
                'description' => __('Display configurable product filters with Ajax', 'ajax-product-filter'),
                'classname' => 'apf-filter-widget',
            )
        );
    }
    
    public function widget($args, $instance) {
        // Show category info on category/taxonomy pages
        if (is_product_taxonomy()) {
            $this->render_category_info($args, $instance);
            return;
        }
        
        if (!is_shop() && !is_product_taxonomy()) {
            return;
        }
        
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $this->render_filters($instance);
        
        echo $args['after_widget'];
    }
    
    private function render_category_info($args, $instance) {
        $current_term = get_queried_object();
        
        if (!$current_term || !isset($current_term->taxonomy)) {
            return;
        }
        
        echo $args['before_widget'];
        ?>
        <div class="apf-category-info-widget">
            <!-- Breadcrumb -->
            <div class="apf-breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
                <span class="apf-breadcrumb-separator">›</span>
                <span class="apf-current"><?php echo esc_html($current_term->name); ?></span>
            </div>
            
            <!-- Category Title -->
            <h1 class="apf-category-title"><?php echo esc_html($current_term->name); ?></h1>
            
            <!-- Category Description -->
            <?php if (!empty($current_term->description)): ?>
                <div class="apf-category-description">
                    <?php echo wp_kses_post($current_term->description); ?>
                    <button class="apf-read-more-btn"><?php _e('Read more', 'ajax-product-filter'); ?></button>
                </div>
            <?php endif; ?>
            
            <!-- Quiz Link -->
            <?php if (!empty($instance['show_quiz_link']) && !empty($instance['quiz_url'])): ?>
                <div class="apf-quiz-section">
                    <a href="<?php echo esc_url($instance['quiz_url']); ?>" class="apf-quiz-link-main">
                        <?php echo esc_html($instance['quiz_text'] ?? __('Take a style quiz', 'ajax-product-filter')); ?>
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
        
        // Now render the filters below
        $this->render_filters($instance);
        
        echo $args['after_widget'];
    }
    
    private function render_filters($instance) {
        $settings = get_option('apf_general_settings', array());
        $filters = get_option('apf_filter_config', array());
        
        if (empty($filters)) {
            echo '<p>' . __('No filters configured. Please configure filters in Product Filter settings.', 'ajax-product-filter') . '</p>';
            return;
        }
        ?>
        <div class="apf-filter-container apf-modern-design" id="apf-filter-container">
            
            <!-- Filter Header with Product Info -->
            <div class="apf-filter-header-section">
                <?php 
                // Get product count
                global $wp_query;
                $product_count = $wp_query->found_posts ?? 0;
                
                // Get current category/taxonomy name if available
                $category_name = '';
                if (is_product_category()) {
                    $category_name = single_term_title('', false);
                } elseif (is_product_taxonomy()) {
                    $category_name = single_term_title('', false);
                }
                ?>
                
                <?php if (!empty($category_name)): ?>
                    <!-- Category Title -->
                    <h1 class="apf-category-title"><?php echo esc_html($category_name); ?></h1>
                <?php endif; ?>
                
                <!-- Product Count Info -->
                <div class="apf-product-count-info">
                    <?php 
                    printf(
                        esc_html__('Starting at $95, including UV-blocking lenses with scratch-resistant coatings. After choosing your frames, select prescription or non-prescription lenses.', 'ajax-product-filter')
                    );
                    ?>
                </div>
                
                <!-- Read More Link -->
                <a href="#" class="apf-read-more"><?php _e('Read more', 'ajax-product-filter'); ?></a>
                
                <!-- Style Quiz Link -->
                <a href="#" class="apf-style-quiz">
                    <?php _e('Take a style quiz', 'ajax-product-filter'); ?>
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </a>
                
                <!-- Shop By Header -->
                <div class="apf-shop-by-header">
                    <h3 class="apf-shop-by-title"><?php _e('Shop By', 'ajax-product-filter'); ?></h3>
                    <span class="apf-filter-count"><?php echo esc_html($product_count); ?></span>
                </div>
            </div>
            
            <!-- Dynamic Filter Groups from Database -->
            <div class="apf-filter-groups">
                <?php foreach ($filters as $filter): ?>
                    <?php if ($filter['enabled']): ?>
                        <?php $this->render_filter_group($filter, $settings); ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <!-- Auto Price Filter - Always show -->
                <?php $this->render_auto_price_filter(); ?>
            </div>
            
            <!-- Loading Overlay -->
            <div class="apf-loading-overlay" id="apf-loading-overlay">
                <div class="apf-spinner"></div>
            </div>
            
        </div>
        <?php
    }
    
    private function render_filter_group($filter, $settings) {
        $expanded = isset($filter['expanded']) && $filter['expanded'];
        
        // Calculate selected count for this filter (will be updated via JS)
        $selected_count = 0;
        ?>
        <div class="apf-filter-group">
            <button class="apf-filter-title <?php echo $expanded ? 'active' : ''; ?>" data-target="<?php echo esc_attr($filter['id']); ?>">
                <span class="apf-filter-title-text"><?php echo esc_html($filter['title']); ?></span>
                <span class="apf-filter-selected-count" style="display:none;" data-count="0"></span>
                <svg class="apf-chevron" width="16" height="16" viewBox="0 0 16 16">
                    <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" fill="none"/>
                </svg>
            </button>
            <div class="apf-filter-content" id="filter-<?php echo esc_attr($filter['id']); ?>" style="<?php echo $expanded ? '' : 'display:none;'; ?>">
                <?php
                switch ($filter['type']) {
                    case 'taxonomy':
                        $this->render_taxonomy_filter($filter, $settings);
                        break;
                    case 'price_range':
                        $this->render_price_range_filter($filter);
                        break;
                    case 'quick_filter':
                        $this->render_quick_filter($filter);
                        break;
                    case 'rating':
                        $this->render_rating_filter($filter);
                        break;
                    case 'stock_status':
                        $this->render_stock_status_filter($filter);
                        break;
                    case 'on_sale':
                        $this->render_on_sale_filter($filter);
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    private function render_taxonomy_filter($filter, $settings) {
        $taxonomy = $filter['taxonomy'];
        $display_type = isset($filter['display_type']) ? $filter['display_type'] : 'checkbox';
        $show_count = isset($filter['show_count']) ? $filter['show_count'] : true;
        
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
        ));
        
        if (empty($terms) || is_wp_error($terms)) {
            echo '<p>' . __('No options available', 'ajax-product-filter') . '</p>';
            return;
        }
        
        if ($display_type == 'color_swatch') {
            echo '<div class="apf-color-swatches apf-swatch-grid">';
            foreach ($terms as $term) {
                $color_value = get_term_meta($term->term_id, 'color', true);
                if (!$color_value) {
                    $color_value = '#' . substr(md5($term->slug), 0, 6);
                }
                ?>
                <label class="apf-color-swatch" title="<?php echo esc_attr($term->name); ?>" data-term="<?php echo esc_attr($term->slug); ?>">
                    <input type="checkbox" name="apf_<?php echo esc_attr($taxonomy); ?>[]" value="<?php echo esc_attr($term->slug); ?>" class="apf-swatch-input">
                    <span class="apf-color-circle" style="background-color: <?php echo esc_attr($color_value); ?>"></span>
                    <span class="apf-swatch-checkmark">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3 8L6 11L13 4" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </label>
                <?php
            }
            echo '</div>';
        } elseif ($display_type == 'image_swatch') {
            echo '<div class="apf-image-swatches apf-swatch-grid">';
            foreach ($terms as $term) {
                $image_id = get_term_meta($term->term_id, 'image_id', true);
                $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : '';
                ?>
                <label class="apf-image-swatch apf-shape-swatch" title="<?php echo esc_attr($term->name); ?>" data-term="<?php echo esc_attr($term->slug); ?>">
                    <input type="checkbox" name="apf_<?php echo esc_attr($taxonomy); ?>[]" value="<?php echo esc_attr($term->slug); ?>" class="apf-swatch-input">
                    <div class="apf-swatch-inner">
                        <?php if ($image_url): ?>
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($term->name); ?>">
                        <?php else: ?>
                            <span class="apf-no-image"><?php echo esc_html(substr($term->name, 0, 2)); ?></span>
                        <?php endif; ?>
                        <span class="apf-swatch-label"><?php echo esc_html($term->name); ?></span>
                    </div>
                    <span class="apf-swatch-checkmark">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3 8L6 11L13 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </label>
                <?php
            }
            echo '</div>';
        } elseif ($display_type == 'dropdown') {
            ?>
            <select name="apf_<?php echo esc_attr($taxonomy); ?>" class="apf-dropdown-filter">
                <option value=""><?php _e('Select...', 'ajax-product-filter'); ?></option>
                <?php foreach ($terms as $term): ?>
                    <option value="<?php echo esc_attr($term->slug); ?>">
                        <?php echo esc_html($term->name); ?>
                        <?php if ($show_count): ?>(<?php echo $term->count; ?>)<?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php
        } else {
            // Checkbox or Radio
            $input_type = ($display_type == 'radio') ? 'radio' : 'checkbox';
            $input_name = ($display_type == 'radio') ? "apf_{$taxonomy}" : "apf_{$taxonomy}[]";
            
            foreach ($terms as $term) {
                ?>
                <label class="apf-filter-option">
                    <input type="<?php echo $input_type; ?>" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr($term->slug); ?>">
                    <span><?php echo esc_html($term->name); ?></span>
                    <?php if ($show_count): ?>
                        <span class="apf-count">(<?php echo $term->count; ?>)</span>
                    <?php endif; ?>
                </label>
                <?php
            }
        }
    }
    
    private function render_price_range_filter($filter) {
        $ranges = isset($filter['ranges']) ? $filter['ranges'] : array();
        
        foreach ($ranges as $index => $range) {
            $value = $range['min'] . '-' . $range['max'];
            ?>
            <label class="apf-filter-option">
                <input type="radio" name="apf_price_range" value="<?php echo esc_attr($value); ?>">
                <span><?php echo esc_html($range['label']); ?></span>
            </label>
            <?php
        }
    }
    
    private function render_auto_price_filter() {
        // Get min and max prices from store
        global $wpdb;
        
        $min_max = $wpdb->get_row("
            SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) as min_price, 
                   MAX(CAST(meta_value AS DECIMAL(10,2))) as max_price
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_price'
            AND meta_value != ''
            AND meta_value REGEXP '^[0-9]+\.?[0-9]*$'
        ");
        
        if (!$min_max || !$min_max->min_price || !$min_max->max_price) {
            return;
        }
        
        $min_price = floor($min_max->min_price);
        $max_price = ceil($min_max->max_price);
        
        $currency_symbol = get_woocommerce_currency_symbol();
        ?>
        <div class="apf-filter-group">
            <button class="apf-filter-title" data-target="auto-price">
                <?php _e('Price Range', 'ajax-product-filter'); ?>
                <svg class="apf-chevron" width="16" height="16" viewBox="0 0 16 16">
                    <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" fill="none"/>
                </svg>
            </button>
            <div class="apf-filter-content apf-price-slider-content" id="filter-auto-price" style="display:none;">
                <div class="apf-price-slider-wrapper">
                    <div class="apf-price-values">
                        <div class="apf-price-input-group">
                            <label class="apf-price-label"><?php _e('Min', 'ajax-product-filter'); ?></label>
                            <div class="apf-price-input-wrapper">
                                <span class="apf-currency-symbol"><?php echo esc_html($currency_symbol); ?></span>
                                <input type="number" 
                                       class="apf-price-input apf-price-min" 
                                       id="apf-price-min" 
                                       value="<?php echo esc_attr($min_price); ?>" 
                                       min="<?php echo esc_attr($min_price); ?>" 
                                       max="<?php echo esc_attr($max_price); ?>"
                                       step="1">
                            </div>
                        </div>
                        <span class="apf-price-separator">—</span>
                        <div class="apf-price-input-group">
                            <label class="apf-price-label"><?php _e('Max', 'ajax-product-filter'); ?></label>
                            <div class="apf-price-input-wrapper">
                                <span class="apf-currency-symbol"><?php echo esc_html($currency_symbol); ?></span>
                                <input type="number" 
                                       class="apf-price-input apf-price-max" 
                                       id="apf-price-max" 
                                       value="<?php echo esc_attr($max_price); ?>" 
                                       min="<?php echo esc_attr($min_price); ?>" 
                                       max="<?php echo esc_attr($max_price); ?>"
                                       step="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="apf-price-slider-track">
                        <div class="apf-price-slider-range" id="apf-price-slider-range"></div>
                        <input type="range" 
                               class="apf-price-range-min" 
                               id="apf-range-min"
                               min="<?php echo esc_attr($min_price); ?>" 
                               max="<?php echo esc_attr($max_price); ?>" 
                               value="<?php echo esc_attr($min_price); ?>" 
                               step="1">
                        <input type="range" 
                               class="apf-price-range-max" 
                               id="apf-range-max"
                               min="<?php echo esc_attr($min_price); ?>" 
                               max="<?php echo esc_attr($max_price); ?>" 
                               value="<?php echo esc_attr($max_price); ?>" 
                               step="1">
                    </div>
                    
                    <input type="hidden" name="apf_price_range" id="apf-price-range-hidden">
                    
                    <button type="button" class="apf-price-apply-btn" id="apf-apply-price">
                        <?php _e('Apply', 'ajax-product-filter'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_quick_filter($filter) {
        $options = isset($filter['options']) ? $filter['options'] : array();
        
        foreach ($options as $option) {
            ?>
            <label class="apf-filter-option">
                <input type="radio" name="apf_quick_filter" value="<?php echo esc_attr($option['value']); ?>">
                <span><?php echo esc_html($option['label']); ?></span>
            </label>
            <?php
        }
    }
    
    private function render_rating_filter($filter) {
        for ($i = 5; $i >= 1; $i--) {
            ?>
            <label class="apf-filter-option apf-rating-option">
                <input type="radio" name="apf_rating" value="<?php echo $i; ?>">
                <span class="apf-stars">
                    <?php for ($j = 1; $j <= 5; $j++): ?>
                        <span class="<?php echo $j <= $i ? 'filled' : ''; ?>">★</span>
                    <?php endfor; ?>
                </span>
                <span><?php printf(__('%d & up', 'ajax-product-filter'), $i); ?></span>
            </label>
            <?php
        }
    }
    
    private function render_stock_status_filter($filter) {
        ?>
        <label class="apf-filter-option">
            <input type="checkbox" name="apf_stock_status" value="instock">
            <span><?php _e('In Stock', 'ajax-product-filter'); ?></span>
        </label>
        <label class="apf-filter-option">
            <input type="checkbox" name="apf_stock_status" value="outofstock">
            <span><?php _e('Out of Stock', 'ajax-product-filter'); ?></span>
        </label>
        <?php
    }
    
    private function render_on_sale_filter($filter) {
        ?>
        <label class="apf-filter-option">
            <input type="checkbox" name="apf_on_sale" value="yes">
            <span><?php _e('Show only products on sale', 'ajax-product-filter'); ?></span>
        </label>
        <?php
    }
    
    private function get_product_count() {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        
        $query = new WP_Query($args);
        return $query->found_posts;
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $show_info = isset($instance['show_info']) ? (bool)$instance['show_info'] : false;
        $info_text = !empty($instance['info_text']) ? $instance['info_text'] : '';
        $show_quiz_link = isset($instance['show_quiz_link']) ? (bool)$instance['show_quiz_link'] : false;
        $quiz_text = !empty($instance['quiz_text']) ? $instance['quiz_text'] : '';
        $quiz_url = !empty($instance['quiz_url']) ? $instance['quiz_url'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'ajax-product-filter'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_info); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_info')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_info')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_info')); ?>"><?php _e('Show info section', 'ajax-product-filter'); ?></label>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('info_text')); ?>"><?php _e('Info Text:', 'ajax-product-filter'); ?></label>
            <textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('info_text')); ?>" 
                      name="<?php echo esc_attr($this->get_field_name('info_text')); ?>" rows="3"><?php echo esc_textarea($info_text); ?></textarea>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_quiz_link); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_quiz_link')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_quiz_link')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_quiz_link')); ?>"><?php _e('Show quiz link', 'ajax-product-filter'); ?></label>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('quiz_text')); ?>"><?php _e('Quiz Link Text:', 'ajax-product-filter'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('quiz_text')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('quiz_text')); ?>" type="text" 
                   value="<?php echo esc_attr($quiz_text); ?>">
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('quiz_url')); ?>"><?php _e('Quiz URL:', 'ajax-product-filter'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('quiz_url')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('quiz_url')); ?>" type="url" 
                   value="<?php echo esc_attr($quiz_url); ?>">
        </p>
        
        <p style="border-top: 1px solid #ddd; padding-top: 10px;">
            <strong><?php _e('Note:', 'ajax-product-filter'); ?></strong> 
            <?php _e('Configure which filters to show in Product Filter > Configure Filters', 'ajax-product-filter'); ?>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['show_info'] = !empty($new_instance['show_info']);
        $instance['info_text'] = (!empty($new_instance['info_text'])) ? sanitize_textarea_field($new_instance['info_text']) : '';
        $instance['show_quiz_link'] = !empty($new_instance['show_quiz_link']);
        $instance['quiz_text'] = (!empty($new_instance['quiz_text'])) ? sanitize_text_field($new_instance['quiz_text']) : '';
        $instance['quiz_url'] = (!empty($new_instance['quiz_url'])) ? esc_url_raw($new_instance['quiz_url']) : '';
        
        return $instance;
    }
}