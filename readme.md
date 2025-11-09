# Advanced Ajax Product Filter for WordPress

A fully configurable Ajax product filter plugin for WooCommerce with database-driven configuration, supporting custom attributes, price ranges, and multiple display types.

## Features

✅ **Fully Configurable** - All filter attributes saved in database  
✅ **Ajax Filtering** - No page reload required  
✅ **Multiple Filter Types** - Taxonomy, Price Range, Quick Filters, Rating, Stock Status, On Sale  
✅ **Multiple Display Types** - Checkbox, Radio, Color Swatches, Image Swatches, Dropdown  
✅ **Drag & Drop** - Reorder filters easily  
✅ **Responsive Design** - Mobile-friendly interface  
✅ **Custom Styling** - Configurable colors and custom CSS  
✅ **Active Filter Tags** - Show and remove active filters  
✅ **Product Count** - Display product count for each option  
✅ **WooCommerce Compatible** - Works with WooCommerce 5.0+  
✅ **Woodmart Theme Compatible** - Designed to match Woodmart aesthetics  

## Installation

### Method 1: Manual Installation

1. Download all plugin files
2. Create folder structure:
```
advanced-ajax-product-filter/
├── advanced-ajax-product-filter.php
├── includes/
│   ├── class-apf-admin.php
│   ├── class-apf-settings.php
│   ├── class-apf-frontend.php
│   ├── class-apf-ajax.php
│   └── class-apf-widget.php
├── assets/
│   ├── css/
│   │   ├── frontend.css
│   │   └── admin.css
│   └── js/
│       ├── frontend.js
│       └── admin.js
└── languages/
```

3. Zip the folder
4. Go to WordPress Admin → Plugins → Add New → Upload Plugin
5. Upload the zip file and activate

### Method 2: FTP Upload

1. Upload the `advanced-ajax-product-filter` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress

## Requirements

- WordPress 5.8 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher

## Configuration

### 1. General Settings

Navigate to **Product Filter → General** in WordPress admin:

- **Enable Ajax Filtering** - Toggle Ajax functionality
- **Show Product Count** - Display count next to filter options
- **Animation Effect** - Choose fade, slide, or no animation
- **Results Text** - Customize text shown after product count (e.g., "frames", "products")
- **Mobile Breakpoint** - Set mobile responsive breakpoint in pixels

### 2. Configure Filters

Navigate to **Product Filter → Configure Filters**:

#### Adding a New Filter

1. Click **"+ Add New Filter"**
2. Configure:
   - **Filter ID** - Unique identifier (lowercase, no spaces)
   - **Filter Title** - Display name for the filter
   - **Filter Type** - Choose from available types
   - **Enable/Disable** - Toggle filter visibility
   - **Expanded by Default** - Show filter open on page load

#### Filter Types

**Taxonomy (Attribute)**
- Select any WooCommerce attribute or custom taxonomy
- Display types: Checkbox, Radio, Color Swatch, Image Swatch, Dropdown
- Configure show/hide product count

**Price Range**
- Add custom price ranges
- Set min, max, and label for each range
- Example: $0-50, $50-100, $100+

**Quick Filters**
- Bestsellers
- New arrivals
- Custom filters (e.g., $95 frames)
- Add custom options with value and label

**Rating**
- Filter by product ratings
- 5 star to 1 star options

**Stock Status**
- In Stock
- Out of Stock

**On Sale**
- Show only products on sale

#### Reordering Filters

- Drag and drop using the ☰ handle
- Order is saved automatically

### 3. Design Settings

Navigate to **Product Filter → Design**:

- **Primary Color** - Main accent color
- **Border Color** - Filter borders and dividers
- **Background Color** - Filter container background
- **Custom CSS** - Add your own CSS styles

## Usage

### Adding Filter to Sidebar

1. Go to **Appearance → Widgets**
2. Add **Ajax Product Filter** widget to your shop sidebar
3. Configure widget:
   - Set widget title
   - Enable/disable info section
   - Add info text
   - Add quiz link (optional)

### Using Shortcode

Place the filter anywhere using shortcode:
```
[apf_filter]
```

### Widget Configuration

**Show Info Section** - Display introductory text above filters  
**Info Text** - Custom text to display  
**Show Quiz Link** - Add a link to style quiz or similar  
**Quiz Text** - Link text  
**Quiz URL** - Link destination  

## Setting Up Product Attributes

### Creating Custom Attributes

1. Go to **Products → Attributes**
2. Add new attribute (e.g., "Shape", "Color", "Frame Width")
3. Add terms to each attribute
4. Assign attributes to products

### Adding Color Values

For color swatch display:
1. Install **WooCommerce Color or Image Variation Swatches** plugin (optional)
2. Or add color values manually using term meta
3. Color picker will be available in term edit screen

### Adding Image Values

For image swatch display:
1. Edit attribute term
2. Upload image using term thumbnail field
3. Images will display automatically in filter

## Styling & Customization

### Custom CSS Examples

Add to **Design → Custom CSS**:

```css
/* Change filter font */
.apf-filter-title {
    font-family: 'Your Font', sans-serif;
    font-size: 16px;
}

/* Customize color swatches */
.apf-color-circle {
    width: 40px;
    height: 40px;
    border-radius: 8px; /* Square with rounded corners */
}

/* Add hover effect to filter options */
.apf-filter-option:hover {
    background: #f9fafb;
    padding-left: 5px;
}

/* Customize active filter tags */
.apf-filter-tag {
    background: #000;
    color: #fff;
    border-radius: 4px;
}
```

## Troubleshooting

### Filters Not Showing

1. Ensure WooCommerce is activated
2. Check if widget is added to sidebar
3. Verify you're on shop or product taxonomy page
4. Check if filters are enabled in settings

### Ajax Not Working

1. Check browser console for JavaScript errors
2. Ensure jQuery is loaded
3. Clear browser cache
4. Check WooCommerce product count

### Products Not Filtering

1. Verify products have attributes assigned
2. Check taxonomy slugs match configuration
3. Ensure product visibility is set to "catalog"
4. Check WooCommerce product status

### Color Swatches Not Displaying

1. Ensure color values are set in term meta
2. Check if display type is set to "Color Swatch"
3. Verify color hex codes are valid

## Hooks & Filters

### Filters

**apf_query_args** - Modify WP_Query arguments
```php
add_filter('apf_query_args', function($args, $filters) {
    // Modify query args
    return $args;
}, 10, 2);
```

### Actions

**apf_before_filter_render** - Before filter widget renders
```php
add_action('apf_before_filter_render', function() {
    // Your code here
});
```

**apf_after_filter_render** - After filter widget renders
```php
add_action('apf_after_filter_render', function() {
    // Your code here
});
```

## Performance Optimization

1. **Caching**: Use object caching for large product catalogs
2. **Limit Terms**: Show only popular terms if you have many
3. **Lazy Load**: Consider lazy loading filter options
4. **Minify Assets**: Minify CSS/JS in production

## Compatibility

### Tested With

- WordPress 6.0 - 6.4
- WooCommerce 7.0 - 8.0
- Woodmart Theme 7.0+
- PHP 7.4 - 8.2

### Known Compatibilities

- WPML (Multi-language)
- Polylang
- WooCommerce Multilingual
- Most page builders (Elementor, WPBakery, etc.)

## Support

For issues and feature requests, please contact your developer or create a support ticket.

## Changelog

### Version 1.0.0
- Initial release
- Configurable filters from database
- Multiple filter types
- Color and image swatches
- Ajax filtering
- Mobile responsive
- Custom styling options

## License

GPL v2 or later

## Credits

Developed for WooCommerce and Woodmart theme compatibility.