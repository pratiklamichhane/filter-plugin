# Advanced Ajax Product Filter - Installation Instructions

## Quick Start Guide

This is a complete WordPress/WooCommerce filter plugin that's ready to use!

### File Structure
```
advanced-ajax-product-filter/
├── advanced-ajax-product-filter.php   (Main plugin file)
├── readme.md                          (Full documentation)
├── INSTALLATION.md                    (This file)
├── includes/
│   ├── class-apf-admin.php           (Admin functionality)
│   ├── class-apf-settings.php        (Settings page)
│   ├── class-apf-frontend.php        (Frontend display)
│   ├── class-apf-ajax.php            (Ajax handlers)
│   └── class-apf-widget.php          (Filter widget)
├── assets/
│   ├── css/
│   │   ├── frontend.css              (Frontend styles)
│   │   └── admin.css                 (Admin styles)
│   └── js/
│       ├── frontend.js               (Frontend JavaScript)
│       └── admin.js                  (Admin JavaScript)
└── languages/
    └── ajax-product-filter.pot       (Translation template)
```

## Installation Steps

### Method 1: Upload via WordPress Admin (Recommended)

1. **Zip the plugin folder**
   - Navigate to the parent directory containing `filter-plugin`
   - Rename `filter-plugin` to `advanced-ajax-product-filter`
   - Create a ZIP file: `advanced-ajax-product-filter.zip`

2. **Upload to WordPress**
   - Login to WordPress Admin
   - Go to **Plugins → Add New**
   - Click **Upload Plugin**
   - Choose the ZIP file
   - Click **Install Now**
   - Click **Activate Plugin**

### Method 2: FTP Upload

1. **Upload files**
   - Connect to your site via FTP
   - Navigate to `/wp-content/plugins/`
   - Upload the entire `advanced-ajax-product-filter` folder
   
2. **Activate**
   - Login to WordPress Admin
   - Go to **Plugins**
   - Find "Advanced Ajax Product Filter"
   - Click **Activate**

### Method 3: Command Line (for developers)

```bash
# Navigate to WordPress plugins directory
cd /path/to/wordpress/wp-content/plugins/

# Create symbolic link (development) or copy folder
ln -s /projects/plugins/filter-plugin advanced-ajax-product-filter
# OR
cp -r /projects/plugins/filter-plugin advanced-ajax-product-filter

# Set permissions
chmod -R 755 advanced-ajax-product-filter

# Activate via WP-CLI
wp plugin activate advanced-ajax-product-filter
```

## Post-Installation Setup

### 1. Verify Requirements
- ✅ WordPress 5.8+
- ✅ WooCommerce 5.0+ (must be installed and active)
- ✅ PHP 7.4+

### 2. Configure Plugin

**Step 1: General Settings**
- Go to **Product Filter → General**
- Configure basic options (Ajax, animation, etc.)
- Click **Save Changes**

**Step 2: Add Filters**
- Go to **Product Filter → Configure Filters**
- Click **+ Add Filter**
- Configure each filter:
  - Set ID (e.g., `shape`, `color`)
  - Set Title (e.g., "Shape", "Frame Color")
  - Choose Type (Taxonomy, Price Range, etc.)
  - Configure display options
- Drag to reorder
- Click **Save Changes**

**Step 3: Add to Sidebar**
- Go to **Appearance → Widgets**
- Add **Ajax Product Filter** widget to Shop Sidebar
- Configure widget options
- Click **Save**

### 3. Set Up Product Attributes (if needed)

**Create Attributes:**
1. Go to **Products → Attributes**
2. Add attributes (e.g., Color, Shape, Size)
3. Add terms to each attribute
4. Assign to products

**For Color Swatches:**
- Install a color swatch plugin OR
- Add color values manually via term meta

### 4. Test the Filter

1. Visit your shop page
2. You should see the filter widget in sidebar
3. Select filter options
4. Products should filter without page reload
5. Check active filter tags display
6. Verify product count updates

## Troubleshooting

### Plugin Won't Activate
- **Error**: "Plugin could not be activated because it triggered a fatal error"
- **Solution**: Check WooCommerce is installed and active

### Filters Not Showing
- Check you're on shop or product category page
- Verify widget is added to correct sidebar
- Ensure filters are enabled in settings
- Check sidebar is registered in your theme

### Ajax Not Working
- Clear browser cache
- Check browser console for errors
- Verify jQuery is loaded
- Test with default WordPress theme

### No Products Returned
- Verify products have correct attributes
- Check taxonomy slugs match (pa_color, pa_shape, etc.)
- Ensure products are published and visible

## Default Configuration

The plugin comes with default filters:
- **Shop By** (Quick Filters)
  - Bestsellers
  - $95 frames
  - New arrivals
- **Shape** (Taxonomy - Checkbox)
- **Color** (Taxonomy - Color Swatch)
- **Frame Price** (Price Range)

You can edit or delete these in **Configure Filters**.

## Customization

### Custom CSS
Add custom styles in **Product Filter → Styling → Custom CSS**

### Filters & Hooks
Available WordPress filters and actions - see readme.md

### Template Overrides
Copy templates to your theme:
```
your-theme/
  woocommerce/
    apf/
      filter-widget.php
```

## Getting Help

1. Check **readme.md** for full documentation
2. Review troubleshooting section
3. Check browser console for errors
4. Verify WooCommerce compatibility
5. Test with default theme

## What's Included

✅ **6 Filter Types**
- Taxonomy (Product Attributes)
- Price Range
- Quick Filters (Bestsellers, New, etc.)
- Rating
- Stock Status
- On Sale

✅ **5 Display Types**
- Checkbox
- Radio
- Color Swatches
- Image Swatches  
- Dropdown

✅ **Features**
- Ajax filtering (no page reload)
- Active filter tags
- Product count
- Drag & drop reorder
- Mobile responsive
- Custom styling
- URL parameters
- Translation ready

✅ **Admin Features**
- Easy configuration interface
- Visual filter builder
- Color picker
- Live preview
- Drag & drop ordering

## Next Steps

1. ✅ Install and activate plugin
2. ✅ Configure general settings
3. ✅ Add your filters
4. ✅ Add widget to sidebar
5. ✅ Customize styling
6. ✅ Test on frontend
7. ✅ Add to other pages if needed

## Support

For questions and support:
- Read the full readme.md documentation
- Check WordPress.org forums
- Contact plugin developer

## License

GPL v2 or later - Free to use and modify!

---

**Developed by**: Pratik Lamichhane  
**Version**: 1.0.0  
**Last Updated**: 2025
