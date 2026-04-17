# Google Analytics 4 Custom Events

This document explains how to trigger custom Google Analytics 4 events from both the backend and frontend.

## Backend Events

Backend events are queued on the server and injected into the page on load. They survive redirects automatically.

### Basic Usage

In any controller or service:

```php
use Illuminate\Support\Facades\App;

// Get the analytics service
$analytics = app('AnalyticsService');

// Queue a custom event
$analytics->queueEvent('event_name', [
    'property_1' => 'value',
    'property_2' => 123,
]);
```

### Event Naming

Event names must follow GA4 rules:
- **Alphanumeric characters and underscores only**
- **Maximum 40 characters**
- **Examples:** `user_login`, `payment_completed`, `invoice_downloaded`, `profile_updated`

Invalid names will be silently logged (no error shown to users).

### Common Backend Events

#### User Login
```php
$analytics->queueEvent('user_login', [
    'user_id' => $user->id,
    'timestamp' => now()->toIso8601String(),
]);
```

#### Payment Completed
```php
$analytics->queueEvent('payment_completed', [
    'transaction_id' => $payment->id,
    'amount' => $payment->amount,
    'currency' => 'USD',
    'payment_method' => 'credit_card',
]);
```

#### Invoice Downloaded
```php
$analytics->queueEvent('invoice_downloaded', [
    'invoice_id' => $invoice->id,
    'invoice_number' => $invoice->number,
    'amount' => $invoice->total,
]);
```

#### Support Ticket Created
```php
$analytics->queueEvent('support_ticket_created', [
    'ticket_id' => $ticket->id,
    'category' => $ticket->category,
    'priority' => $ticket->priority,
]);
```

#### User Logout
```php
$analytics->queueEvent('user_logout', [
    'user_id' => $user->id,
]);
```

### Configuration

Control PII (Personally Identifiable Information) tracking in [config/analytics.php](config/analytics.php):

```php
return [
    'track_user_id' => env('ANALYTICS_TRACK_USER_ID', false),      // Include user ID
    'track_user_email' => env('ANALYTICS_TRACK_USER_EMAIL', false), // Include email
    'suppress_pii' => env('ANALYTICS_SUPPRESS_PII', true),         // Redact PII by default
];
```

Set in `.env`:
```
ANALYTICS_TRACK_USER_ID=true
ANALYTICS_TRACK_USER_EMAIL=false
ANALYTICS_SUPPRESS_PII=true
```

### PII Redaction

By default, the following fields are automatically redacted:
- `email`
- `password`
- `phone`
- `ssn`
- `credit_card`

If `suppress_pii=true` (default), `user_id` and `email` fields are also removed unless explicitly enabled in config.

## Frontend Events

Frontend events are triggered in real-time as users interact with the page.

### Basic Usage

In any JavaScript:

```javascript
// Track a click event
window.analyticsHelper.trackEvent('button_clicked', {
    button_name: 'subscribe',
    page_section: 'hero',
});

// Track a form interaction
window.analyticsHelper.trackEvent('form_interaction', {
    form_name: 'contact_form',
    field_name: 'email',
});

// Track a feature usage
window.analyticsHelper.trackEvent('feature_used', {
    feature_name: 'export_data',
    export_format: 'csv',
});
```

### Event Validation

Events are validated on the frontend:
- Invalid names (non-alphanumeric/underscore) will log a warning and not fire
- Max 40 character names are enforced
- Invalid events don't break the page (gracefully ignored)

### Common Frontend Events

#### Page Section Viewed
```javascript
window.analyticsHelper.trackEvent('section_viewed', {
    section_name: 'pricing_table',
    scroll_depth: 50,
});
```

#### Feature Toggle Used
```javascript
window.analyticsHelper.trackEvent('feature_toggle', {
    feature_name: 'dark_mode',
    state: 'enabled',
});
```

#### Search Performed
```javascript
window.analyticsHelper.trackEvent('search_performed', {
    search_query: 'billing invoices',
    result_count: 12,
});
```

#### Button Clicked
```javascript
document.getElementById('export-btn').addEventListener('click', function() {
    window.analyticsHelper.trackEvent('export_initiated', {
        export_type: 'invoices',
        date_range: 'last_30_days',
    });
});
```

## Service Provider Integration

The analytics service is automatically available in:
- **Controllers:** `app('AnalyticsService')`
- **Views:** `$analytics` (via service provider view sharing)
- **Anywhere:** `app('AnalyticsService')`

## How It Works

### Backend → Frontend Flow

1. User triggers action (e.g., login)
2. Backend queues event: `$analytics->queueEvent('user_login', [...])`
3. Event is passed to redirected page via Laravel's `redirect()->with()`
4. View injects `window._pendingAnalyticsEvents` with queued events
5. Analytics helper waits for `gtag()` to initialize
6. Events fire to Google Analytics 4

### Real-Time Flow

1. User interacts (e.g., click, form input)
2. JavaScript calls `window.analyticsHelper.trackEvent(...)`
3. Event validates and fires immediately via `gtag()`
4. Data sent to Google Analytics 4

## Debug Mode

### Enable Analytics Debugging

1. Open Google Analytics 4 Property Settings
2. Enable "Enhanced measurement" in Admin → Data Streams
3. Use the [Google Analytics Debugger](https://chrome.google.com/webstore/detail/google-analytics-debugger/jnkmfdileelhofjcicakflprklnpljga) Chrome extension
4. Check Network tab → filter by "collect" to see event payloads

### Without Debug Extension

Events will still be sent but may be batched. Use Network tab filter:
- Look for requests to `www.google-analytics.com/g/collect`
- Inspect the `ep` parameter in the request body (contains event properties)

## Error Handling

- **Invalid event names:** Logged to Laravel logs at WARNING level, silently ignored in frontend
- **GA not initialized:** Events wait up to 500ms for `gtag()` to load, then fire when ready
- **Invalid properties:** No validation on properties, GA4 handles validation server-side

## Examples

### Complete Controller Example

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function downloadInvoice(Request $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        // Verify user can download this invoice
        $this->authorize('view', $invoice);
        
        // Track the download
        $analytics = app('AnalyticsService');
        $analytics->queueEvent('invoice_downloaded', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->number,
            'total_amount' => $invoice->total,
            'currency' => $invoice->currency,
        ]);
        
        return response()->download($invoice->filepath);
    }
    
    public function sendPayment(Request $request)
    {
        $payment = Payment::create($request->validated());
        
        // Process payment...
        
        $analytics = app('AnalyticsService');
        $analytics->queueEvent('payment_submitted', [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'method' => $payment->method,
        ]);
        
        return redirect()->route('billing.payments')
            ->with('success', 'Payment submitted successfully');
    }
}
```

### Complete View Example

```blade
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Track when user expands a section
        document.querySelectorAll('.expandable-section').forEach(function(section) {
            section.addEventListener('click', function() {
                window.analyticsHelper.trackEvent('section_expanded', {
                    section_id: this.id,
                    section_title: this.dataset.title,
                });
            });
        });
        
        // Track filter changes
        document.querySelectorAll('.filter-select').forEach(function(select) {
            select.addEventListener('change', function() {
                window.analyticsHelper.trackEvent('filter_applied', {
                    filter_type: this.dataset.filterType,
                    filter_value: this.value,
                });
            });
        });
    });
</script>
```

## Limitations

- Events queue per-request on the backend (not persisted between page loads if not redirected)
- Only works when GA4 is enabled in system settings
- Event properties are passed as-is to GA4 (no additional processing)
- Maximum 25 event properties per event (GA4 limitation)

## Support

For issues:
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Check browser console for JavaScript warnings
3. Use GA4 debugger to verify events are reaching Google Analytics
4. Verify GA4 ID is set in system settings
