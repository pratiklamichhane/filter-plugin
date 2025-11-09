/**
 * Frontend JavaScript
 * File: assets/js/frontend.js
 */

(function($) {
    'use strict';
    
    const APF = {
        init: function() {
            this.filterContainer = $('#apf-filter-container');
            this.productsContainer = $('.products');
            this.loadingOverlay = $('#apf-loading-overlay');
            this.activeFiltersContainer = $('#apf-active-filters');
            this.productCount = $('#apf-product-count');
            
            this.bindEvents();
            this.initAccordions();
        },
        
        bindEvents: function() {
            const self = this;
            
            // Filter toggle
            $(document).on('click', '.apf-filter-title', function(e) {
                e.preventDefault();
                const $this = $(this);
                const target = $this.data('target');
                const $content = $('#filter-' + target);
                
                $this.toggleClass('active');
                $content.slideToggle(300);
            });
            
            // Hide filters button
            $(document).on('click', '#apf-hide-filters', function(e) {
                e.preventDefault();
                self.filterContainer.addClass('hidden');
            });
            
            // Filter changes
            $(document).on('change', '.apf-filter-option input', function() {
                self.applyFilters();
            });
            
            // Color swatches - Fixed to trigger Ajax
            $(document).on('click', '.apf-color-swatch', function(e) {
                e.preventDefault();
                const $swatch = $(this);
                const $input = $swatch.find('input.apf-swatch-input');
                
                // Toggle checkbox state
                const isChecked = $input.prop('checked');
                $input.prop('checked', !isChecked);
                $swatch.toggleClass('selected', !isChecked);
                
                // Trigger Ajax filtering
                self.applyFilters();
            });
            
            // Image/Shape swatches
            $(document).on('click', '.apf-image-swatch', function(e) {
                e.preventDefault();
                const $swatch = $(this);
                const $input = $swatch.find('input.apf-swatch-input');
                
                // Toggle checkbox state
                const isChecked = $input.prop('checked');
                $input.prop('checked', !isChecked);
                $swatch.toggleClass('selected', !isChecked);
                
                // Trigger Ajax filtering
                self.applyFilters();
            });
            
            // Dropdown change
            $(document).on('change', '.apf-dropdown-filter', function() {
                self.applyFilters();
            });
            
            // Remove active filter
            $(document).on('click', '.apf-remove-filter', function(e) {
                e.preventDefault();
                const filterType = $(this).data('type');
                const filterValue = $(this).data('value');
                
                self.removeFilter(filterType, filterValue);
            });
            
            // Clear all filters
            $(document).on('click', '.apf-clear-all', function(e) {
                e.preventDefault();
                self.clearAllFilters();
            });
            
            // Prevent form submission
            $(document).on('submit', '.apf-filter-container form', function(e) {
                e.preventDefault();
            });
        },
        
        initAccordions: function() {
            // Open first filter group by default
            $('.apf-filter-title').first().addClass('active');
            $('.apf-filter-content').first().show();
        },
        
        applyFilters: function() {
            const self = this;
            const filters = this.collectFilters();
            
            // Update URL
            this.updateURL(filters);
            
            // Show loading
            this.showLoading();
            
            // Ajax request
            $.ajax({
                url: apf_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'apf_filter_products',
                    nonce: apf_params.nonce,
                    ...filters
                },
                success: function(response) {
                    if (response.success) {
                        self.updateProducts(response.data.products);
                        self.updateCount(response.data.count);
                        self.updateActiveFilters(filters);
                        self.hideLoading();
                        
                        // Scroll to products
                        $('html, body').animate({
                            scrollTop: self.productsContainer.offset().top - 100
                        }, 500);
                    }
                },
                error: function() {
                    self.hideLoading();
                    alert('An error occurred. Please try again.');
                }
            });
        },
        
        collectFilters: function() {
            const filters = {};
            
            // Quick filter
            const quickFilter = $('input[name="apf_quick_filter"]:checked').val();
            if (quickFilter) {
                filters.quick_filter = quickFilter;
            }
            
            // Taxonomies
            const taxonomies = ['pa_shape', 'pa_color', 'pa_gender', 'pa_frame_width', 'pa_material'];
            taxonomies.forEach(function(taxonomy) {
                const values = [];
                $('input[name="apf_' + taxonomy + '[]"]:checked').each(function() {
                    values.push($(this).val());
                });
                if (values.length > 0) {
                    filters[taxonomy] = values;
                }
            });
            
            // Price range
            const priceRange = $('input[name="apf_price_range"]:checked').val();
            if (priceRange) {
                filters.price_range = priceRange;
            }
            
            // Orderby
            const orderby = $('.orderby').val();
            if (orderby) {
                filters.orderby = orderby;
            }
            
            return filters;
        },
        
        updateProducts: function(html) {
            this.productsContainer.fadeOut(200, () => {
                this.productsContainer.html(html);
                this.productsContainer.fadeIn(200);
            });
        },
        
        updateCount: function(count) {
            this.productCount.text(count);
        },
        
        updateActiveFilters: function(filters) {
            const self = this;
            const $inner = this.activeFiltersContainer.find('.apf-active-filters-inner');
            $inner.empty();
            
            let hasFilters = false;
            
            // Quick filter
            if (filters.quick_filter) {
                hasFilters = true;
                const label = $('input[value="' + filters.quick_filter + '"]').parent().text().trim();
                $inner.append(this.createFilterTag('quick_filter', filters.quick_filter, label));
            }
            
            // Taxonomies
            const taxonomies = ['pa_shape', 'pa_color', 'pa_gender', 'pa_frame_width', 'pa_material'];
            taxonomies.forEach(function(taxonomy) {
                if (filters[taxonomy] && filters[taxonomy].length > 0) {
                    hasFilters = true;
                    filters[taxonomy].forEach(function(value) {
                        const label = $('input[value="' + value + '"]').parent().text().trim();
                        $inner.append(self.createFilterTag(taxonomy, value, label));
                    });
                }
            });
            
            // Price range
            if (filters.price_range) {
                hasFilters = true;
                const label = $('input[value="' + filters.price_range + '"]').parent().text().trim();
                $inner.append(this.createFilterTag('price_range', filters.price_range, label));
            }
            
            // Clear all button
            if (hasFilters) {
                $inner.append('<button class="apf-clear-all">Clear all</button>');
                this.activeFiltersContainer.slideDown(200);
            } else {
                this.activeFiltersContainer.slideUp(200);
            }
        },
        
        createFilterTag: function(type, value, label) {
            return `
                <span class="apf-filter-tag">
                    ${label}
                    <button class="apf-remove-filter" data-type="${type}" data-value="${value}">
                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                            <path d="M9 3L3 9M3 3L9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </span>
            `;
        },
        
        removeFilter: function(type, value) {
            if (type === 'quick_filter' || type === 'price_range' || type === 'apf_rating') {
                $('input[name="' + type + '"][value="' + value + '"]').prop('checked', false);
            } else {
                $('input[name="' + type + '[]"][value="' + value + '"]').prop('checked', false);
            }
            this.applyFilters();
        },
        
        clearAllFilters: function() {
            $('.apf-filter-option input').prop('checked', false);
            $('.apf-color-swatch').removeClass('selected');
            this.applyFilters();
        },
        
        showLoading: function() {
            this.loadingOverlay.addClass('active');
        },
        
        hideLoading: function() {
            this.loadingOverlay.removeClass('active');
        },
        
        updateURL: function(filters) {
            if (!window.history || !window.history.pushState) {
                return;
            }
            
            const url = new URL(window.location);
            url.search = '';
            
            // Add filters to URL
            for (const key in filters) {
                if (Array.isArray(filters[key])) {
                    filters[key].forEach(value => {
                        url.searchParams.append(key, value);
                    });
                } else {
                    url.searchParams.set(key, filters[key]);
                }
            }
            
            window.history.pushState({}, '', url);
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        if ($('#apf-filter-container').length) {
            APF.init();
        }
    });
    
})(jQuery);