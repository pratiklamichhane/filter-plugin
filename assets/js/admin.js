/**
 * Admin JavaScript
 * File: assets/js/admin.js
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Initialize color pickers
        $('.apf-color-picker').wpColorPicker();
        
        // Make filters sortable
        $('#apf-filters-list').sortable({
            handle: '.apf-drag-handle',
            axis: 'y',
            cursor: 'move',
            opacity: 0.7,
            update: function(event, ui) {
                // Update indices after sorting
                updateFilterIndices();
            }
        });
        
        // Toggle filter edit
        $(document).on('click', '.apf-edit-filter', function(e) {
            e.preventDefault();
            const $item = $(this).closest('.apf-filter-item');
            $item.find('.apf-filter-item-body').slideToggle(300);
        });
        
        // Delete filter
        $(document).on('click', '.apf-delete-filter', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this filter?')) {
                $(this).closest('.apf-filter-item').fadeOut(300, function() {
                    $(this).remove();
                    updateFilterIndices();
                });
            }
        });
        
        // Add new filter
        $('#apf-add-filter').on('click', function(e) {
            e.preventDefault();
            const template = $('#apf-filter-template').html();
            const index = $('#apf-filters-list .apf-filter-item').length;
            const newFilter = template.replace(/\{\{INDEX\}\}/g, index);
            
            $('#apf-filters-list').append(newFilter);
            const $newItem = $('#apf-filters-list .apf-filter-item').last();
            $newItem.find('.apf-filter-item-body').show();
        });
        
        // Filter type change
        $(document).on('change', '.apf-filter-type-select', function() {
            const $item = $(this).closest('.apf-filter-item-body');
            const type = $(this).val();
            
            $item.find('.apf-taxonomy-option').hide();
            $item.find('.apf-price-option').hide();
            $item.find('.apf-quick-option').hide();
            
            if (type === 'taxonomy') {
                $item.find('.apf-taxonomy-option').show();
            } else if (type === 'price_range') {
                $item.find('.apf-price-option').show();
            } else if (type === 'quick_filter') {
                $item.find('.apf-quick-option').show();
            }
        });
        
        // Add price range
        $(document).on('click', '.apf-add-price-range', function(e) {
            e.preventDefault();
            const $container = $(this).prev('.apf-price-ranges');
            const index = $container.find('.apf-price-range-row').length;
            const filterIndex = $(this).closest('.apf-filter-item').data('index');
            
            const row = `
                <div class="apf-price-range-row">
                    <input type="number" name="apf_filters[${filterIndex}][ranges][${index}][min]" 
                           placeholder="Min" class="small-text">
                    <input type="number" name="apf_filters[${filterIndex}][ranges][${index}][max]" 
                           placeholder="Max" class="small-text">
                    <input type="text" name="apf_filters[${filterIndex}][ranges][${index}][label]" 
                           placeholder="Label" class="regular-text">
                    <button type="button" class="button apf-remove-range">×</button>
                </div>
            `;
            
            $container.append(row);
        });
        
        // Remove price range
        $(document).on('click', '.apf-remove-range', function(e) {
            e.preventDefault();
            $(this).closest('.apf-price-range-row').fadeOut(200, function() {
                $(this).remove();
            });
        });
        
        // Add quick option
        $(document).on('click', '.apf-add-quick-option', function(e) {
            e.preventDefault();
            const $container = $(this).prev('.apf-quick-options');
            const index = $container.find('.apf-quick-option-row').length;
            const filterIndex = $(this).closest('.apf-filter-item').data('index');
            
            const row = `
                <div class="apf-quick-option-row">
                    <input type="text" name="apf_filters[${filterIndex}][options][${index}][value]" 
                           placeholder="Value" class="regular-text">
                    <input type="text" name="apf_filters[${filterIndex}][options][${index}][label]" 
                           placeholder="Label" class="regular-text">
                    <button type="button" class="button apf-remove-option">×</button>
                </div>
            `;
            
            $container.append(row);
        });
        
        // Remove quick option
        $(document).on('click', '.apf-remove-option', function(e) {
            e.preventDefault();
            $(this).closest('.apf-quick-option-row').fadeOut(200, function() {
                $(this).remove();
            });
        });
        
        // Update filter title in header when changed
        $(document).on('input', '.apf-filter-item-body input[name*="[title]"]', function() {
            const title = $(this).val() || 'New Filter';
            $(this).closest('.apf-filter-item').find('.apf-filter-title-text').text(title);
        });
        
        // Update filter indices after sorting or deletion
        function updateFilterIndices() {
            $('#apf-filters-list .apf-filter-item').each(function(index) {
                $(this).attr('data-index', index);
                
                // Update all input names
                $(this).find('input, select, textarea').each(function() {
                    const name = $(this).attr('name');
                    if (name) {
                        const newName = name.replace(/apf_filters\[\d+\]/, 'apf_filters[' + index + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        }
    });
    
})(jQuery);