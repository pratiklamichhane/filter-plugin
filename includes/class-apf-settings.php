<?php
/**
 * Settings Page Class
 * File: includes/class-apf-settings.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class APF_Settings {
    
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    public function register_settings() {
        register_setting('apf_general_settings_group', 'apf_general_settings', array($this, 'sanitize_general_settings'));
        register_setting('apf_filter_config_group', 'apf_filter_config', array($this, 'sanitize_filter_config'));
        register_setting('apf_style_settings_group', 'apf_style_settings', array($this, 'sanitize_style_settings'));
    }
    
    public function sanitize_general_settings($input) {
        $sanitized = array();
        
        $sanitized['enable_ajax'] = isset($input['enable_ajax']) ? 'yes' : 'no';
        $sanitized['show_count'] = isset($input['show_count']) ? 'yes' : 'no';
        $sanitized['animation'] = sanitize_text_field($input['animation']);
        $sanitized['filter_position'] = sanitize_text_field($input['filter_position']);
        $sanitized['mobile_breakpoint'] = intval($input['mobile_breakpoint']);
        $sanitized['results_text'] = sanitize_text_field($input['results_text']);
        $sanitized['show_active_filters'] = isset($input['show_active_filters']) ? 'yes' : 'no';
        $sanitized['enable_url_params'] = isset($input['enable_url_params']) ? 'yes' : 'no';
        
        return $sanitized;
    }
    
    public function sanitize_filter_config($input) {
        if (!is_array($input)) {
            return array();
        }
        
        $sanitized = array();
        
        foreach ($input as $key => $filter) {
            $sanitized[$key] = array(
                'id' => sanitize_key($filter['id']),
                'title' => sanitize_text_field($filter['title']),
                'type' => sanitize_text_field($filter['type']),
                'enabled' => isset($filter['enabled']) ? true : false,
                'expanded' => isset($filter['expanded']) ? true : false,
            );
            
            if (isset($filter['taxonomy'])) {
                $sanitized[$key]['taxonomy'] = sanitize_text_field($filter['taxonomy']);
            }
            
            if (isset($filter['display_type'])) {
                $sanitized[$key]['display_type'] = sanitize_text_field($filter['display_type']);
            }
            
            if (isset($filter['show_count'])) {
                $sanitized[$key]['show_count'] = (bool)$filter['show_count'];
            }
            
            if (isset($filter['ranges']) && is_array($filter['ranges'])) {
                $sanitized[$key]['ranges'] = array();
                foreach ($filter['ranges'] as $range) {
                    $sanitized[$key]['ranges'][] = array(
                        'min' => floatval($range['min']),
                        'max' => floatval($range['max']),
                        'label' => sanitize_text_field($range['label']),
                    );
                }
            }
            
            if (isset($filter['options']) && is_array($filter['options'])) {
                $sanitized[$key]['options'] = array();
                foreach ($filter['options'] as $option) {
                    $sanitized[$key]['options'][] = array(
                        'value' => sanitize_text_field($option['value']),
                        'label' => sanitize_text_field($option['label']),
                    );
                }
            }
        }
        
        return $sanitized;
    }
    
    public function sanitize_style_settings($input) {
        $sanitized = array();
        
        $sanitized['primary_color'] = sanitize_hex_color($input['primary_color']);
        $sanitized['button_bg_color'] = sanitize_hex_color($input['button_bg_color']);
        $sanitized['button_text_color'] = sanitize_hex_color($input['button_text_color']);
        $sanitized['custom_css'] = wp_kses_post($input['custom_css']);
        
        return $sanitized;
    }
    
    public static function render_settings_page() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        ?>
        <div class="wrap apf-settings-wrap">
            <h1><?php _e('Product Filter Settings', 'ajax-product-filter'); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=apf-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('General', 'ajax-product-filter'); ?>
                </a>
                <a href="?page=apf-settings&tab=filters" class="nav-tab <?php echo $active_tab == 'filters' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Configure Filters', 'ajax-product-filter'); ?>
                </a>
                <a href="?page=apf-settings&tab=styling" class="nav-tab <?php echo $active_tab == 'styling' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Styling', 'ajax-product-filter'); ?>
                </a>
            </h2>
            
            <?php
            switch ($active_tab) {
                case 'general':
                    self::render_general_tab();
                    break;
                case 'filters':
                    self::render_filters_tab();
                    break;
                case 'styling':
                    self::render_styling_tab();
                    break;
            }
            ?>
        </div>
        <?php
    }
    
    private static function render_general_tab() {
        $settings = get_option('apf_general_settings', array());
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('apf_general_settings_group'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Enable Ajax Filtering', 'ajax-product-filter'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="apf_general_settings[enable_ajax]" value="yes" <?php checked($settings['enable_ajax'] ?? 'yes', 'yes'); ?>>
                            <?php _e('Enable Ajax filtering (no page reload)', 'ajax-product-filter'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Show Product Count', 'ajax-product-filter'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="apf_general_settings[show_count]" value="yes" <?php checked($settings['show_count'] ?? 'yes', 'yes'); ?>>
                            <?php _e('Show product count next to filter options', 'ajax-product-filter'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Animation Effect', 'ajax-product-filter'); ?></th>
                    <td>
                        <select name="apf_general_settings[animation]">
                            <option value="fade" <?php selected($settings['animation'] ?? 'fade', 'fade'); ?>><?php _e('Fade', 'ajax-product-filter'); ?></option>
                            <option value="slide" <?php selected($settings['animation'] ?? 'fade', 'slide'); ?>><?php _e('Slide', 'ajax-product-filter'); ?></option>
                            <option value="none" <?php selected($settings['animation'] ?? 'fade', 'none'); ?>><?php _e('None', 'ajax-product-filter'); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Filter Position', 'ajax-product-filter'); ?></th>
                    <td>
                        <select name="apf_general_settings[filter_position]">
                            <option value="left" <?php selected($settings['filter_position'] ?? 'left', 'left'); ?>><?php _e('Left Sidebar', 'ajax-product-filter'); ?></option>
                            <option value="right" <?php selected($settings['filter_position'] ?? 'left', 'right'); ?>><?php _e('Right Sidebar', 'ajax-product-filter'); ?></option>
                            <option value="top" <?php selected($settings['filter_position'] ?? 'left', 'top'); ?>><?php _e('Top', 'ajax-product-filter'); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Mobile Breakpoint', 'ajax-product-filter'); ?></th>
                    <td>
                        <input type="number" name="apf_general_settings[mobile_breakpoint]" value="<?php echo esc_attr($settings['mobile_breakpoint'] ?? '768'); ?>" class="small-text"> px
                        <p class="description"><?php _e('Screen width below which mobile layout is activated', 'ajax-product-filter'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Results Text', 'ajax-product-filter'); ?></th>
                    <td>
                        <input type="text" name="apf_general_settings[results_text]" value="<?php echo esc_attr($settings['results_text'] ?? 'products'); ?>" class="regular-text">
                        <p class="description"><?php _e('Text to display after product count (e.g., "products", "items", "frames")', 'ajax-product-filter'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Show Active Filters', 'ajax-product-filter'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="apf_general_settings[show_active_filters]" value="yes" <?php checked($settings['show_active_filters'] ?? 'yes', 'yes'); ?>>
                            <?php _e('Display active filter tags', 'ajax-product-filter'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Enable URL Parameters', 'ajax-product-filter'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="apf_general_settings[enable_url_params]" value="yes" <?php checked($settings['enable_url_params'] ?? 'yes', 'yes'); ?>>
                            <?php _e('Update URL with filter parameters (allows bookmarking filtered results)', 'ajax-product-filter'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }
    
    private static function render_filters_tab() {
        $filters = get_option('apf_filter_config', array());
        $taxonomies = get_object_taxonomies('product', 'objects');
        ?>
        <form method="post" action="options.php" id="apf-filters-form">
            <?php settings_fields('apf_filter_config_group'); ?>
            
            <div class="apf-filters-manager">
                <p><?php _e('Drag and drop to reorder filters. Configure each filter type and display options.', 'ajax-product-filter'); ?></p>
                
                <div id="apf-filters-list" class="apf-filters-list">
                    <?php foreach ($filters as $index => $filter): ?>
                        <?php self::render_filter_item($index, $filter, $taxonomies); ?>
                    <?php endforeach; ?>
                </div>
                
                <p>
                    <button type="button" class="button button-secondary" id="apf-add-filter">
                        <?php _e('+ Add Filter', 'ajax-product-filter'); ?>
                    </button>
                </p>
            </div>
            
            <?php submit_button(); ?>
        </form>
        
        <!-- Filter Template -->
        <script type="text/html" id="apf-filter-template">
            <?php self::render_filter_item('{{INDEX}}', array(), $taxonomies, true); ?>
        </script>
        <?php
    }
    
    private static function render_filter_item($index, $filter = array(), $taxonomies = array(), $is_template = false) {
        $filter = wp_parse_args($filter, array(
            'id' => '',
            'title' => '',
            'type' => 'taxonomy',
            'enabled' => true,
            'expanded' => false,
            'taxonomy' => '',
            'display_type' => 'checkbox',
            'show_count' => true,
            'ranges' => array(),
            'options' => array(),
        ));
        ?>
        <div class="apf-filter-item" data-index="<?php echo esc_attr($index); ?>">
            <div class="apf-filter-item-header">
                <span class="apf-drag-handle">☰</span>
                <span class="apf-filter-item-title"><?php echo esc_html($filter['title'] ?: __('New Filter', 'ajax-product-filter')); ?></span>
                <div class="apf-filter-item-actions">
                    <label class="apf-enabled-toggle">
                        <input type="checkbox" name="apf_filter_config[<?php echo $index; ?>][enabled]" value="1" <?php checked($filter['enabled'], true); ?>>
                        <?php _e('Enabled', 'ajax-product-filter'); ?>
                    </label>
                    <button type="button" class="button button-small apf-edit-filter"><?php _e('Edit', 'ajax-product-filter'); ?></button>
                    <button type="button" class="button button-small apf-delete-filter"><?php _e('Delete', 'ajax-product-filter'); ?></button>
                </div>
            </div>
            
            <div class="apf-filter-item-body" style="display: none;">
                <table class="form-table">
                    <tr>
                        <th><?php _e('Filter ID', 'ajax-product-filter'); ?></th>
                        <td>
                            <input type="text" name="apf_filter_config[<?php echo $index; ?>][id]" value="<?php echo esc_attr($filter['id']); ?>" class="regular-text" required>
                            <p class="description"><?php _e('Unique identifier (use lowercase and underscores)', 'ajax-product-filter'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><?php _e('Filter Title', 'ajax-product-filter'); ?></th>
                        <td>
                            <input type="text" name="apf_filter_config[<?php echo $index; ?>][title]" value="<?php echo esc_attr($filter['title']); ?>" class="regular-text" required>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><?php _e('Filter Type', 'ajax-product-filter'); ?></th>
                        <td>
                            <select name="apf_filter_config[<?php echo $index; ?>][type]" class="apf-filter-type-select">
                                <option value="taxonomy" <?php selected($filter['type'], 'taxonomy'); ?>><?php _e('Taxonomy (Attributes)', 'ajax-product-filter'); ?></option>
                                <option value="price_range" <?php selected($filter['type'], 'price_range'); ?>><?php _e('Price Range', 'ajax-product-filter'); ?></option>
                                <option value="quick_filter" <?php selected($filter['type'], 'quick_filter'); ?>><?php _e('Quick Filter', 'ajax-product-filter'); ?></option>
                                <option value="rating" <?php selected($filter['type'], 'rating'); ?>><?php _e('Rating', 'ajax-product-filter'); ?></option>
                                <option value="stock_status" <?php selected($filter['type'], 'stock_status'); ?>><?php _e('Stock Status', 'ajax-product-filter'); ?></option>
                                <option value="on_sale" <?php selected($filter['type'], 'on_sale'); ?>><?php _e('On Sale', 'ajax-product-filter'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr class="apf-taxonomy-option" style="<?php echo $filter['type'] === 'taxonomy' ? '' : 'display:none;'; ?>">
                        <th><?php _e('Select Taxonomy', 'ajax-product-filter'); ?></th>
                        <td>
                            <select name="apf_filter_config[<?php echo $index; ?>][taxonomy]">
                                <option value=""><?php _e('Select...', 'ajax-product-filter'); ?></option>
                                <?php foreach ($taxonomies as $tax): ?>
                                    <?php if (strpos($tax->name, 'pa_') === 0): ?>
                                        <option value="<?php echo esc_attr($tax->name); ?>" <?php selected($filter['taxonomy'], $tax->name); ?>>
                                            <?php echo esc_html($tax->label); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    
                    <tr class="apf-taxonomy-option" style="<?php echo $filter['type'] === 'taxonomy' ? '' : 'display:none;'; ?>">
                        <th><?php _e('Display Type', 'ajax-product-filter'); ?></th>
                        <td>
                            <select name="apf_filter_config[<?php echo $index; ?>][display_type]">
                                <option value="checkbox" <?php selected($filter['display_type'], 'checkbox'); ?>><?php _e('Checkbox', 'ajax-product-filter'); ?></option>
                                <option value="radio" <?php selected($filter['display_type'], 'radio'); ?>><?php _e('Radio', 'ajax-product-filter'); ?></option>
                                <option value="color_swatch" <?php selected($filter['display_type'], 'color_swatch'); ?>><?php _e('Color Swatch', 'ajax-product-filter'); ?></option>
                                <option value="image_swatch" <?php selected($filter['display_type'], 'image_swatch'); ?>><?php _e('Image Swatch', 'ajax-product-filter'); ?></option>
                                <option value="dropdown" <?php selected($filter['display_type'], 'dropdown'); ?>><?php _e('Dropdown', 'ajax-product-filter'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr class="apf-taxonomy-option" style="<?php echo $filter['type'] === 'taxonomy' ? '' : 'display:none;'; ?>">
                        <th><?php _e('Show Count', 'ajax-product-filter'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="apf_filter_config[<?php echo $index; ?>][show_count]" value="1" <?php checked($filter['show_count'], true); ?>>
                                <?php _e('Display product count', 'ajax-product-filter'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr class="apf-price-option" style="<?php echo $filter['type'] === 'price_range' ? '' : 'display:none;'; ?>">
                        <th><?php _e('Price Ranges', 'ajax-product-filter'); ?></th>
                        <td>
                            <div class="apf-price-ranges">
                                <?php if (!empty($filter['ranges'])): ?>
                                    <?php foreach ($filter['ranges'] as $range_index => $range): ?>
                                        <div class="apf-price-range-row">
                                            <input type="number" name="apf_filter_config[<?php echo $index; ?>][ranges][<?php echo $range_index; ?>][min]" 
                                                   value="<?php echo esc_attr($range['min']); ?>" placeholder="Min" class="small-text">
                                            <input type="number" name="apf_filter_config[<?php echo $index; ?>][ranges][<?php echo $range_index; ?>][max]" 
                                                   value="<?php echo esc_attr($range['max']); ?>" placeholder="Max" class="small-text">
                                            <input type="text" name="apf_filter_config[<?php echo $index; ?>][ranges][<?php echo $range_index; ?>][label]" 
                                                   value="<?php echo esc_attr($range['label']); ?>" placeholder="Label" class="regular-text">
                                            <button type="button" class="button apf-remove-range">×</button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button button-secondary apf-add-price-range"><?php _e('+ Add Range', 'ajax-product-filter'); ?></button>
                        </td>
                    </tr>
                    
                    <tr class="apf-quick-option" style="<?php echo $filter['type'] === 'quick_filter' ? '' : 'display:none;'; ?>">
                        <th><?php _e('Quick Filter Options', 'ajax-product-filter'); ?></th>
                        <td>
                            <div class="apf-quick-options">
                                <?php if (!empty($filter['options'])): ?>
                                    <?php foreach ($filter['options'] as $opt_index => $option): ?>
                                        <div class="apf-quick-option-row">
                                            <input type="text" name="apf_filter_config[<?php echo $index; ?>][options][<?php echo $opt_index; ?>][value]" 
                                                   value="<?php echo esc_attr($option['value']); ?>" placeholder="Value" class="regular-text">
                                            <input type="text" name="apf_filter_config[<?php echo $index; ?>][options][<?php echo $opt_index; ?>][label]" 
                                                   value="<?php echo esc_attr($option['label']); ?>" placeholder="Label" class="regular-text">
                                            <button type="button" class="button apf-remove-option">×</button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button button-secondary apf-add-quick-option"><?php _e('+ Add Option', 'ajax-product-filter'); ?></button>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><?php _e('Expanded by Default', 'ajax-product-filter'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="apf_filter_config[<?php echo $index; ?>][expanded]" value="1" <?php checked($filter['expanded'], true); ?>>
                                <?php _e('Show filter content expanded on page load', 'ajax-product-filter'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }
    
    private static function render_styling_tab() {
        $settings = get_option('apf_style_settings', array());
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('apf_style_settings_group'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Primary Color', 'ajax-product-filter'); ?></th>
                    <td>
                        <input type="text" name="apf_style_settings[primary_color]" value="<?php echo esc_attr($settings['primary_color'] ?? '#2563eb'); ?>" class="apf-color-picker">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Button Background', 'ajax-product-filter'); ?></th>
                    <td>
                        <input type="text" name="apf_style_settings[button_bg_color]" value="<?php echo esc_attr($settings['button_bg_color'] ?? '#000000'); ?>" class="apf-color-picker">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Button Text Color', 'ajax-product-filter'); ?></th>
                    <td>
                        <input type="text" name="apf_style_settings[button_text_color]" value="<?php echo esc_attr($settings['button_text_color'] ?? '#ffffff'); ?>" class="apf-color-picker">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Custom CSS', 'ajax-product-filter'); ?></th>
                    <td>
                        <textarea name="apf_style_settings[custom_css]" rows="10" class="large-text code"><?php echo esc_textarea($settings['custom_css'] ?? ''); ?></textarea>
                        <p class="description"><?php _e('Add custom CSS styles to override default styling', 'ajax-product-filter'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }
}

new APF_Settings();
