# Test Changes - Swatch Fix & Auto Price Filter

## Changes Made

### 1. **Fixed Swatch Selection (Color & Image Swatches)**

**Problem:** Swatches were not working because we were preventing the default label behavior with `e.preventDefault()`. Labels naturally toggle their associated checkboxes, so we were interfering with that.

**Solution:** Simplified the code to let the label work naturally, then listen for the checkbox change event:

```javascript
// Before (NOT WORKING):
$(document).on('click', '.apf-color-swatch', function(e) {
    e.preventDefault(); // <-- THIS WAS THE PROBLEM!
    e.stopPropagation();
    // Manual checkbox toggling...
});

// After (WORKING):
$(document).on('change', 'input.apf-swatch-input', function() {
    const $input = $(this);
    const $swatch = $input.closest('.apf-color-swatch, .apf-image-swatch');
    $swatch.toggleClass('selected', $input.prop('checked'));
    console.log('Swatch changed:', $input.attr('name'), 'value:', $input.val(), 'checked:', $input.prop('checked'));
    self.applyFilters();
});
```

**Why It Works:**
- Labels with `for` attribute or wrapping inputs automatically toggle checkboxes when clicked
- We were blocking this natural behavior with `preventDefault()`
- Now we let the browser handle the checkbox state, then react to the change
- Much cleaner and more reliable

### 2. **Added Auto Price Filter**

**Feature:** Automatically generates price ranges based on actual product prices in your store.

**How It Works:**
1. Queries database to find min/max prices of all products
2. Divides the range into 5 equal segments
3. Displays as radio buttons with formatted price labels
4. Always shows (even if admin hasn't configured price filters)
5. Uses same `apf_price_range` name so works with existing Ajax handler

**Code Added:**
- New method: `render_auto_price_filter()` in `class-apf-widget.php`
- Queries: `SELECT MIN/MAX(meta_value) FROM postmeta WHERE meta_key = '_price'`
- Auto-calculates ranges: 5 ranges covering min to max price
- Formats with WooCommerce currency: `wc_price($amount)`

**Example Output:**
If your products range from $10 to $200, it creates:
- $10 - $48
- $48 - $86
- $86 - $124
- $124 - $162
- $162 - $200

## Testing Steps

### Test Swatches (Critical)
1. Open your site with the filter plugin in sidebar
2. Look for color or image swatches
3. Click a swatch
4. **Expected:** Swatch gets border + checkmark, products filter immediately
5. Open browser console (F12)
6. You should see: `Swatch changed: apf_pa_color[] value: blue checked: true`
7. Click another swatch
8. **Expected:** Products update to show items matching both selections
9. Click same swatch again to deselect
10. **Expected:** Products update to remove that filter

### Test Auto Price Filter
1. Scroll to bottom of filter sidebar
2. Look for "Price Range" section
3. Click the accordion to expand it
4. **Expected:** See 5 price ranges based on your products
5. Click a price range
6. **Expected:** Products filter to show only items in that price range
7. Open console (F12)
8. You should see: `Collecting filters...` and `Price range: 10-48`

### Test Other Filters Still Work
1. Test dropdown filters - should work as before
2. Test checkbox filters - should work as before
3. Test radio button filters - should work as before
4. Ensure all filters work together

## Console Debugging

Open browser console (F12) and watch for these messages:

**When clicking swatches:**
```
Swatch changed: apf_pa_color[] value: blue checked: true
Collecting filters...
Taxonomy pa_color: ["blue"]
All collected filters: {pa_color: ["blue"]}
```

**When selecting price:**
```
Collecting filters...
Price range: 10-48
All collected filters: {price_range: "10-48"}
```

**If you see errors:**
- `jQuery is not defined` - jQuery not loaded
- `$ is not a function` - jQuery conflict
- No console logs at all - JavaScript not loading or APF.init() not running

## Files Modified

1. **assets/js/frontend.js** - Simplified swatch handlers
2. **includes/class-apf-widget.php** - Added auto price filter method

## Why This Works Better

### Swatch Fix:
- **Before:** 40+ lines of complex click handling with preventDefault
- **After:** 8 lines letting browser do its job
- **Result:** More reliable, works with all browsers, less code

### Auto Price Filter:
- **Before:** Admin had to manually configure price ranges
- **After:** Automatic based on actual product prices
- **Result:** Always accurate, no configuration needed, updates automatically

## If Still Not Working

### Swatches Issue:
1. Check if `<label>` wraps the `<input>` (it should)
2. Verify `class="apf-swatch-input"` on checkbox
3. Check console for JavaScript errors
4. Try clicking the checkbox directly (not the label) - does it work then?
5. Clear browser cache and WordPress cache

### Price Filter Issue:
1. Check if you have products with prices set
2. Verify WooCommerce is active
3. Check console for PHP errors in Network tab (F12 > Network)
4. Look for the filter in widget HTML source

## Browser Cache Warning

⚠️ **IMPORTANT:** Clear your browser cache or hard refresh (Ctrl+Shift+R / Cmd+Shift+R) to load the new JavaScript!

Old cached JavaScript will still have the broken `e.preventDefault()` code.
