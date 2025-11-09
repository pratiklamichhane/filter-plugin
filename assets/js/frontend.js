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
            
            // Color and Image swatches - Let the label work naturally, then trigger Ajax
            $(document).on('change', 'input.apf-swatch-input', function() {
                const $input = $(this);
                const $swatch = $input.closest('.apf-color-swatch, .apf-image-swatch');
                
                // Update visual state
                $swatch.toggleClass('selected', $input.prop('checked'));
                
                console.log('Swatch changed:', $input.attr('name'), 'value:', $input.val(), 'checked:', $input.prop('checked'));
                
                // Trigger Ajax filtering
                self.applyFilters();
            });
            
            // Dropdown change
            $(document).on('change', '.apf-dropdown-filter', function() {
                self.applyFilters();
            });
            
            // Price slider
            self.initPriceSlider();
            
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
        
        initPriceSlider: function() {
            const self = this;
            const $minRange = $('#apf-range-min');
            const $maxRange = $('#apf-range-max');
            const $minInput = $('#apf-price-min');
            const $maxInput = $('#apf-price-max');
            const $sliderRange = $('#apf-price-slider-range');
            const $applyBtn = $('#apf-apply-price');
            
            if (!$minRange.length || !$maxRange.length) {
                return;
            }
            
            const minPrice = parseInt($minRange.attr('min'));
            const maxPrice = parseInt($maxRange.attr('max'));
            
            function updateSliderRange() {
                const minVal = parseInt($minRange.val());
                const maxVal = parseInt($maxRange.val());
                const percentMin = ((minVal - minPrice) / (maxPrice - minPrice)) * 100;
                const percentMax = ((maxVal - minPrice) / (maxPrice - minPrice)) * 100;
                
                $sliderRange.css({
                    'left': percentMin + '%',
                    'width': (percentMax - percentMin) + '%'
                });
            }
            
            // Range slider changes
            $minRange.on('input', function() {
                let minVal = parseInt($(this).val());
                let maxVal = parseInt($maxRange.val());
                
                if (minVal >= maxVal) {
                    minVal = maxVal - 1;
                    $(this).val(minVal);
                }
                
                $minInput.val(minVal);
                updateSliderRange();
            });
            
            $maxRange.on('input', function() {
                let maxVal = parseInt($(this).val());
                let minVal = parseInt($minRange.val());
                
                if (maxVal <= minVal) {
                    maxVal = minVal + 1;
                    $(this).val(maxVal);
                }
                
                $maxInput.val(maxVal);
                updateSliderRange();
            });
            
            // Number input changes
            $minInput.on('change', function() {
                let val = parseInt($(this).val()) || minPrice;
                if (val < minPrice) val = minPrice;
                if (val >= parseInt($maxInput.val())) val = parseInt($maxInput.val()) - 1;
                $(this).val(val);
                $minRange.val(val);
                updateSliderRange();
            });
            
            $maxInput.on('change', function() {
                let val = parseInt($(this).val()) || maxPrice;
                if (val > maxPrice) val = maxPrice;
                if (val <= parseInt($minInput.val())) val = parseInt($minInput.val()) + 1;
                $(this).val(val);
                $maxRange.val(val);
                updateSliderRange();
            });
            
            // Apply button
            $applyBtn.on('click', function() {
                const minVal = parseInt($minInput.val());
                const maxVal = parseInt($maxInput.val());
                $('#apf-price-range-hidden').val(minVal + '-' + maxVal);
                self.applyFilters();
            });
            
            // Initialize
            updateSliderRange();
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
            
            console.log('Collecting filters...');
            
            // Quick filter
            const quickFilter = $('input[name="apf_quick_filter"]:checked').val();
            if (quickFilter) {
                filters.quick_filter = quickFilter;
                console.log('Quick filter:', quickFilter);
            }
            
            // Taxonomies - including color swatches
            const taxonomies = ['pa_shape', 'pa_color', 'pa_gender', 'pa_frame_width', 'pa_material'];
            taxonomies.forEach(function(taxonomy) {
                const values = [];
                
                // Check both regular inputs and swatch inputs
                $('input[name="apf_' + taxonomy + '[]"]:checked, input.apf-swatch-input[name="apf_' + taxonomy + '[]"]:checked').each(function() {
                    const val = $(this).val();
                    if (val && values.indexOf(val) === -1) {
                        values.push(val);
                    }
                });
                
                if (values.length > 0) {
                    filters[taxonomy] = values;
                    console.log('Taxonomy ' + taxonomy + ':', values);
                }
            });
            
            // Price range
            const priceRange = $('input[name="apf_price_range"]:checked').val();
            if (priceRange) {
                filters.price_range = priceRange;
                console.log('Price range:', priceRange);
            }
            
            // Rating
            const rating = $('input[name="apf_rating"]:checked').val();
            if (rating) {
                filters.rating = rating;
                console.log('Rating:', rating);
            }
            
            // Stock status
            const stockStatus = [];
            $('input[name="apf_stock_status"]:checked').each(function() {
                stockStatus.push($(this).val());
            });
            if (stockStatus.length > 0) {
                filters.stock_status = stockStatus;
                console.log('Stock status:', stockStatus);
            }
            
            // On sale
            const onSale = $('input[name="apf_on_sale"]:checked').val();
            if (onSale) {
                filters.on_sale = onSale;
                console.log('On sale:', onSale);
            }
            
            // Orderby
            const orderby = $('.orderby').val();
            if (orderby) {
                filters.orderby = orderby;
                console.log('Order by:', orderby);
            }
            
            console.log('All collected filters:', filters);
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