# Admin Pagination Component Usage Guide

## Overview
The `<x-admin.pagination>` component provides consistent, professional pagination across all admin pages with your orange theme colors.

## Basic Usage

### 1. Simple Pagination
```blade
<!-- Replace existing pagination with: -->
<x-admin.pagination :paginator="$purchases" />
```

### 2. Custom Info Text
```blade
<!-- For different entity types -->
<x-admin.pagination :paginator="$customers" info-text="customers" />
<x-admin.pagination :paginator="$products" info-text="products" />
<x-admin.pagination :paginator="$sales" info-text="sales" />
<x-admin.pagination :paginator="$suppliers" info-text="suppliers" />
```

### 3. Hide Info Text
```blade
<!-- When you don't want to show the "Showing X to Y of Z entries" text -->
<x-admin.pagination :paginator="$items" :show-info="false" />
```

### 4. Different Sizes
```blade
<!-- Small pagination (default) -->
<x-admin.pagination :paginator="$items" size="sm" />

<!-- Medium pagination -->
<x-admin.pagination :paginator="$items" size="md" />

<!-- Large pagination -->
<x-admin.pagination :paginator="$items" size="lg" />
```

## Implementation Steps for Each Page

### Step 1: Update Controller
Ensure your controller uses `paginate()` with `withQueryString()`:

```php
// In your controller's index method
public function index(Request $request)
{
    $query = YourModel::query();
    
    // Add your filters here...
    
    $items = $query->paginate(20)->withQueryString();
    
    return view('admin.your-page.index', compact('items'));
}
```

### Step 2: Replace Pagination in Blade
Replace your existing pagination code with:

```blade
<!-- Old pagination code - REMOVE THIS -->
@if($items->hasPages())
    <div class="card-footer">
        <!-- ... old pagination HTML ... -->
    </div>
@endif

<!-- New pagination component - USE THIS -->
<x-admin.pagination :paginator="$items" info-text="your-entity-name" />
```

### Step 3: Remove Old CSS
Remove any custom pagination CSS from your blade files since the component includes its own styling.

## Pages to Update

### 1. Purchases (✅ Already Done)
```blade
<x-admin.pagination :paginator="$purchases" info-text="purchases" />
```

### 2. Sales
```blade
<x-admin.pagination :paginator="$sales" info-text="sales" />
```

### 3. Customers
```blade
<x-admin.pagination :paginator="$customers" info-text="customers" />
```

### 4. Suppliers
```blade
<x-admin.pagination :paginator="$suppliers" info-text="suppliers" />
```

### 5. Products
```blade
<x-admin.pagination :paginator="$products" info-text="products" />
```

### 6. Users
```blade
<x-admin.pagination :paginator="$users" info-text="users" />
```

### 7. Reports/Activity Logs
```blade
<x-admin.pagination :paginator="$activities" info-text="activities" />
```

## Features Included

✅ **Orange Theme Colors** - Matches your primary color scheme
✅ **Responsive Design** - Works on all screen sizes
✅ **Smart Page Range** - Shows max 5 pages with ellipsis for large datasets
✅ **Hover Effects** - Beautiful hover and focus states
✅ **Accessibility** - Proper ARIA labels and keyboard navigation
✅ **Consistent Styling** - Same look across all pages
✅ **Performance Optimized** - CSS loaded only once with @once directive

## Advanced Features

### Smart Page Range Logic
- Shows up to 5 page numbers
- Adds ellipsis (...) for large page counts
- Always shows first and last pages when needed
- Centers current page in the range

### Responsive Behavior
- Stacks vertically on mobile devices
- Adjusts button sizes for touch screens
- Centers pagination on small screens

## Benefits

1. **Consistency** - Same pagination design across all pages
2. **Maintainability** - Update once, changes everywhere
3. **Performance** - Optimized CSS loading
4. **Accessibility** - Built-in ARIA support
5. **Responsive** - Mobile-first design
6. **Theme Integration** - Uses your orange primary colors

## Example Implementation

```blade
{{-- Before --}}
@if($customers->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-between">
            <div>Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} entries</div>
            {{ $customers->links() }}
        </div>
    </div>
@endif

{{-- After --}}
<x-admin.pagination :paginator="$customers" info-text="customers" />
```

That's it! The component handles everything else automatically.
