# YT Custom Post Status Manager

A powerful WordPress plugin that allows you to create and manage custom post statuses with color-coded visual indicators in the admin interface.

## Features

- **Custom Post Statuses** - Create unlimited custom statuses like "In Review," "Needs Edits," "Approved"
- **Color-Coded Interface** - Each status displays with a unique color in the post list
- **Quick Edit Integration** - Custom statuses available in quick edit
- **Bulk Edit Support** - Apply custom statuses to multiple posts at once
- **Post Edit Integration** - Custom statuses in post editor status dropdown
- **Admin Filters** - Filter posts by custom status in admin list
- **Easy Management** - Add, edit, and delete statuses via admin interface
- **Default Statuses** - Includes 3 pre-configured statuses on activation
- **WPCS Compliant** - Follows WordPress Coding Standards
- **Secure** - Nonce verification, sanitization, and capability checks

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Settings → Post Status** to manage custom statuses
4. Start using custom statuses when editing posts and pages

## File Structure

```
yt-custom-post-status-manager/
├── class-yt-custom-post-status-manager.php  # Main plugin file (494 lines)
├── assets/
│   ├── css/
│   │   └── admin.css                         # Admin styles & row colors
│   └── js/
│       └── admin.js                          # Quick edit & color coding
└── README.md                                 # Documentation
```

## Usage

### Managing Statuses

#### Add New Status

1. Go to **Settings → Post Status**
2. Fill in the form:
   - **Status Slug**: Unique identifier (e.g., `in-review`)
   - **Status Label**: Display name (e.g., "In Review")
   - **Status Color**: Color code for visual identification
3. Click **Add Status**

#### Edit Status

1. Go to **Settings → Post Status**
2. Click **Edit** next to the status you want to modify
3. Update the label or color
4. Click **Update Status**

**Note:** Status slugs cannot be changed after creation to prevent breaking existing posts.

#### Delete Status

1. Go to **Settings → Post Status**
2. Click **Delete** next to the status
3. Confirm deletion

**Warning:** Deleting a status will not automatically change posts using that status. Posts will retain the status slug, but it won't be registered anymore.

### Using Custom Statuses

#### In Post Editor

1. Edit any post or page
2. In the **Publish** metabox, click **Edit** next to Status
3. Select your custom status from the dropdown
4. Update or publish the post

#### In Quick Edit

1. Go to **Posts** or **Pages** list
2. Hover over a post and click **Quick Edit**
3. Select a custom status from the Status dropdown
4. Click **Update**

#### In Bulk Edit

1. Go to **Posts** or **Pages** list
2. Select multiple posts using checkboxes
3. Choose **Edit** from Bulk Actions and click **Apply**
4. Select a custom status from the Status dropdown
5. Click **Update**

### Filtering Posts

1. Go to **Posts** or **Pages** list
2. Use the **All Statuses** dropdown at the top
3. Select a custom status to filter
4. Posts with that status will be displayed

## Default Statuses

The plugin includes 3 default statuses on activation:

| Slug | Label | Color | Use Case |
|------|-------|-------|----------|
| `in-review` | In Review | Blue (#3498db) | Content awaiting review |
| `needs-edits` | Needs Edits | Red (#e74c3c) | Content requiring revisions |
| `approved` | Approved | Green (#2ecc71) | Content approved for publication |

You can edit or delete these default statuses as needed.

## Visual Features

### Color-Coded Rows

Each post in the admin list displays a colored left border matching its status color. This provides instant visual identification of post status.

```
┌────────────────────────────────────┐
│ ┃ Post Title - In Review          │  Blue border
├────────────────────────────────────┤
│ ┃ Another Post - Needs Edits      │  Red border
├────────────────────────────────────┤
│ ┃ Final Post - Approved           │  Green border
└────────────────────────────────────┘
```

### Color Box Preview

In the settings page, each status displays a color box preview next to the hex code for easy identification.

## Technical Details

### Constants Defined

```php
YT_CPSM_VERSION  // Plugin version: 1.0.0
YT_CPSM_BASENAME // Plugin basename
YT_CPSM_PATH     // Plugin directory path
YT_CPSM_URL      // Plugin directory URL
```

### Data Storage

Custom statuses are stored in the WordPress options table:

**Option Name:** `yt_cpsm_statuses`

**Data Structure:**
```php
array(
    array(
        'slug'  => 'in-review',
        'label' => 'In Review',
        'color' => '#3498db'
    ),
    // ... more statuses
)
```

### Post Status Registration

Each custom status is registered using WordPress's `register_post_status()` function with these parameters:

```php
register_post_status( 'in-review', array(
    'label'                     => 'In Review',
    'public'                    => true,
    'exclude_from_search'       => false,
    'show_in_admin_all_list'    => true,
    'show_in_admin_status_list' => true,
    'label_count'               => _n_noop(
        'In Review <span class="count">(%s)</span>',
        'In Review <span class="count">(%s)</span>'
    )
));
```

### Main Class Methods

| Method | Description |
|--------|-------------|
| `get_instance()` | Get singleton instance |
| `init_hooks()` | Register WordPress hooks |
| `load_textdomain()` | Load translations |
| `register_custom_statuses()` | Register all custom statuses with WordPress |
| `enqueue_admin_scripts()` | Load admin CSS and JS |
| `get_statuses_for_js()` | Format statuses for JavaScript |
| `add_quick_edit_script()` | Add quick edit JavaScript |
| `add_post_status_script()` | Add post editor JavaScript |
| `get_status_label()` | Get label by slug |
| `add_admin_menu()` | Add settings page |
| `handle_status_actions()` | Process add/edit/delete actions |
| `add_status()` | Add new status |
| `edit_status()` | Update existing status |
| `delete_status()` | Remove status |
| `render_admin_page()` | Display settings page |
| `display_custom_state()` | Show status in post list |
| `add_status_filter()` | Add filter dropdown |
| `filter_posts_by_status()` | Filter posts by status |
| `add_action_links()` | Add settings link |
| `activate()` | Plugin activation |
| `deactivate()` | Plugin deactivation |

## Security Features

✅ **Implemented Security Measures:**

- **Nonce Verification** - All form submissions verified with nonces
- **Capability Checks** - Admin functions require `manage_options`
- **Sanitization** - All inputs sanitized:
  - `sanitize_title()` for slugs
  - `sanitize_text_field()` for labels
  - `sanitize_hex_color()` for colors
  - `absint()` for indices
- **Escaping** - All outputs escaped:
  - `esc_html()` for text
  - `esc_attr()` for attributes
  - `esc_url()` for URLs
  - `esc_js()` for JavaScript
- **Direct Access Prevention** - File access protection
- **Safe Redirects** - Using `wp_safe_redirect()`

## Hooks & Filters

### WordPress Core Hooks Used

The plugin hooks into:

```php
// Admin hooks
add_action( 'init', 'register_custom_statuses' );
add_action( 'admin_enqueue_scripts', 'enqueue_admin_scripts' );
add_action( 'admin_footer-edit.php', 'add_quick_edit_script' );
add_action( 'admin_footer-post.php', 'add_post_status_script' );
add_action( 'restrict_manage_posts', 'add_status_filter' );

// Display hooks
add_filter( 'display_post_states', 'display_custom_state' );
add_filter( 'parse_query', 'filter_posts_by_status' );
```

### Available for Developers

While the plugin doesn't expose custom hooks in this version, you can hook into WordPress core:

```php
// Modify registered status args
add_filter( 'register_post_status_args', 'custom_status_args', 10, 2 );
function custom_status_args( $args, $post_status ) {
    if ( 'in-review' === $post_status ) {
        $args['public'] = false; // Make status private
    }
    return $args;
}

// Run code when status is changed
add_action( 'transition_post_status', 'custom_status_transition', 10, 3 );
function custom_status_transition( $new_status, $old_status, $post ) {
    if ( 'in-review' === $new_status ) {
        // Send notification, log event, etc.
    }
}
```

## Customization

### CSS Customization

Override default styles in your theme:

```css
/* Change row border width */
.wp-list-table tbody tr[data-status-color] {
    border-left-width: 6px;
}

/* Add background color tint */
.wp-list-table tbody tr[data-status-color] {
    background-color: rgba(0, 0, 0, 0.01);
}

/* Custom color box style */
.yt-cpsm-color-box {
    width: 30px;
    height: 30px;
    border-radius: 50%;
}

/* Custom admin layout */
.yt-cpsm-container {
    flex-direction: column;
}
```

### JavaScript Customization

Extend functionality:

```javascript
jQuery(document).ready(function($) {
    // Add custom behavior when status changes
    $(document).on('change', 'select[name="_status"]', function() {
        const selectedStatus = $(this).val();
        console.log('Status changed to:', selectedStatus);

        // Add custom validation or notifications
        if (selectedStatus === 'approved') {
            alert('This post is now approved!');
        }
    });

    // Custom color highlighting
    $('#the-list tr[data-status-color]').each(function() {
        const color = $(this).attr('data-status-color');
        $(this).css('background-color', color + '10'); // Add transparency
    });
});
```

### Programmatic Status Management

```php
// Get all custom statuses
$statuses = get_option( 'yt_cpsm_statuses', array() );

// Add a status programmatically
$statuses[] = array(
    'slug'  => 'archived',
    'label' => 'Archived',
    'color' => '#95a5a6'
);
update_option( 'yt_cpsm_statuses', $statuses );

// Change a post's status
wp_update_post( array(
    'ID'          => 123,
    'post_status' => 'in-review'
) );

// Query posts by custom status
$args = array(
    'post_status' => 'in-review',
    'post_type'   => 'post'
);
$query = new WP_Query( $args );
```

## Workflow Examples

### Editorial Workflow

1. **Draft** (WordPress default) - Author writes content
2. **In Review** (Custom) - Editor reviews content
3. **Needs Edits** (Custom) - Editor requests changes
4. **Approved** (Custom) - Editor approves content
5. **Publish** (WordPress default) - Content goes live

### Development Workflow

1. **Draft** - Initial content creation
2. **In Development** - Technical implementation
3. **Testing** - QA phase
4. **Approved** - Ready for deployment
5. **Publish** - Live on site

### Custom Workflow Example

Create statuses for your needs:

```
Status Slug        | Label              | Color
-------------------|--------------------|--------
client-review      | Client Review      | #f39c12
legal-review       | Legal Review       | #9b59b6
ready-translation  | Ready Translation  | #1abc9c
translated         | Translated         | #16a085
scheduled          | Scheduled          | #34495e
```

## Troubleshooting

### Status Not Showing in Dropdown

**Cause:** JavaScript not loaded or conflict
**Solution:**
1. Clear browser cache
2. Check JavaScript console for errors
3. Deactivate other plugins to check for conflicts
4. Ensure jQuery is loaded

### Row Colors Not Displaying

**Cause:** CSS not loaded or theme conflict
**Solution:**
1. Check if `admin.css` is enqueued
2. Inspect element to verify `data-status-color` attribute
3. Check for theme CSS overrides
4. Clear WordPress cache

### Posts Not Filtering

**Cause:** Query not modified or permalink flush needed
**Solution:**
1. Re-save permalink settings
2. Deactivate and reactivate plugin
3. Check if `yt_post_status` parameter is in URL

### Deleted Status Still Visible

**Cause:** WordPress caches registered statuses
**Solution:**
1. Clear object cache if using caching plugin
2. Posts retain deleted status slugs but won't display properly
3. Manually change affected posts to different status

### Quick Edit Not Working

**Cause:** JavaScript timing or version conflict
**Solution:**
1. Check WordPress version (requires 5.8+)
2. Try refreshing the page
3. Inspect quick edit form for custom status options

## Compatibility

### WordPress Core

- **Post Types:** Works with Posts and Pages by default
- **Gutenberg:** Fully compatible
- **Classic Editor:** Fully compatible
- **Quick Edit:** Fully supported
- **Bulk Edit:** Fully supported

### Custom Post Types

To enable for custom post types, modify the filter checks in:
- `add_status_filter()` method (line ~452)
- `add_quick_edit_script()` method (line ~171)

```php
if ( ! in_array( $post_type, array( 'post', 'page', 'your_cpt' ), true ) ) {
    return;
}
```

### Plugins

Compatible with:
- Yoast SEO
- Advanced Custom Fields
- WooCommerce (for products)
- Custom Post Type UI
- Most page builders

## Performance

- **Database Queries:** Minimal impact (stores in options table)
- **Admin Load:** CSS/JS only on edit screens
- **Frontend:** No frontend impact
- **Caching:** Compatible with object caching

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Opera 76+

## Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **jQuery:** Included with WordPress
- **Permissions:** `manage_options` capability

## Uninstall

The plugin provides clean uninstall:

1. Deactivate the plugin
2. Delete the plugin files
3. Option data is automatically removed

### What Gets Deleted

- Plugin option: `yt_cpsm_statuses`
- All settings and custom statuses

### What Remains

- Posts keep their custom status slugs (but statuses won't be registered)
- You can change post statuses before uninstalling if needed

## Changelog

### 1.0.0 (2025-01-18)
- Initial release
- Add/edit/delete custom statuses
- Color-coded post list rows
- Quick edit integration
- Bulk edit support
- Post editor integration
- Admin filter dropdown
- WPCS compliant code

## License

GPL v2 or later

## Credits

Built following WordPress Plugin Handbook and WPCS guidelines.

## Author

Krasen Slavov
- Website: https://krasenslavov.com
- GitHub: https://github.com/krasenslavov

## Support

For issues and questions:
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [Post Status Documentation](https://developer.wordpress.org/reference/functions/register_post_status/)
