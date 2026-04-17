<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AnalyticsService
{
    /**
     * Queue of events to be injected into the page on next render
     */
    private array $queuedEvents = [];

    /**
     * Configuration for analytics
     */
    private array $config;

    /**
     * Session key for storing events
     */
    private const SESSION_KEY = '_analytics_events';

    public function __construct()
    {
        $this->config = [
            'enabled' => config('customer_portal.google_analytics_enabled', false),
            'ga_id' => config('customer_portal.google_analytics_ga4_id', ''),
            'track_user_id' => config('analytics.track_user_id', false),
            'track_user_email' => config('analytics.track_user_email', false),
            'suppress_pii' => config('analytics.suppress_pii', true),
        ];

        // Load any queued events from session (via redirect flash data)
        $this->queuedEvents = session('_pending_analytics_events', []);
    }

    /**
     * Queue an event to be fired on page load
     */
    public function queueEvent(string $eventName, array $properties = []): void
    {
        if (!$this->config['enabled']) {
            return;
        }

        if (!$this->isValidEventName($eventName)) {
            Log::warning('Invalid analytics event name: ' . $eventName);
            return;
        }

        // Filter properties for PII if needed
        if ($this->config['suppress_pii']) {
            $properties = $this->redactPII($properties);
        }

        $event = [
            'name' => $eventName,
            'properties' => $properties,
        ];
        
        $this->queuedEvents[] = $event;
    }

    /**
     * Get all queued events and clear the queue
     */
    public function flushQueuedEvents(): array
    {
        $events = $this->queuedEvents;
        $this->queuedEvents = [];
        return $events;
    }

    /**
     * Check if analytics is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'];
    }

    /**
     * Get GA4 ID
     */
    public function getGa4Id(): string
    {
        return $this->config['ga_id'];
    }

    /**
     * Validate event name (GA4 rules: alphanumeric + underscore, max 40 chars)
     */
    private function isValidEventName(string $eventName): bool
    {
        if (strlen($eventName) > 40) {
            return false;
        }

        return preg_match('/^[a-zA-Z0-9_]+$/', $eventName) === 1;
    }

    /**
     * Redact personally identifiable information from properties
     */
    private function redactPII(array $properties): array
    {
        $piiFields = ['email', 'password', 'phone', 'ssn', 'credit_card'];

        foreach ($piiFields as $field) {
            unset($properties[$field]);
        }

        // Allow explicit user_id and email only if configured
        if (!$this->config['track_user_id']) {
            unset($properties['user_id']);
        }

        if (!$this->config['track_user_email']) {
            unset($properties['email']);
        }

        return $properties;
    }
}
