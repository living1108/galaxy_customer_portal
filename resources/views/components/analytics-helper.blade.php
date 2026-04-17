@if($analytics->isEnabled())
<script nonce="{{ csp_nonce() }}">
    // Initialize analytics helper
    window.analyticsHelper = window.analyticsHelper || {
        trackEvent: function(eventName, properties) {
            if (!window.gtag) {
                console.warn('Google Analytics not initialized yet');
                return;
            }
            
            // Validate event name (must be alphanumeric + underscore, max 40 chars)
            if (!/^[a-zA-Z0-9_]+$/.test(eventName) || eventName.length > 40) {
                console.warn('Invalid analytics event name: ' + eventName);
                return;
            }
            
            gtag('event', eventName, properties || {});
        },
        
        fireQueuedEvents: function(events) {
            if (!Array.isArray(events) || events.length === 0) {
                return;
            }
            
            events.forEach(function(event) {
                window.analyticsHelper.trackEvent(event.name, event.properties);
            });
        }
    };

    // Wait for gtag to be ready before firing queued events
    var waitForGtag = function() {
        if (window.gtag && Array.isArray(window._pendingAnalyticsEvents) && window._pendingAnalyticsEvents.length > 0) {
            window.analyticsHelper.fireQueuedEvents(window._pendingAnalyticsEvents);
        } else if (!window.gtag) {
            // Try again after 100ms
            setTimeout(waitForGtag, 100);
        }
    };

    // Start waiting for gtag with a small initial delay
    setTimeout(waitForGtag, 50);
</script>
@endif
