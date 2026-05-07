<!doctype html>
<html>
   <head>
      <meta charset="utf-8">
      <meta name="description" content="Customer Portal">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <title>{{config("customer_portal.company_name")}}</title>
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="stylesheet" href="/assets/fonts/feather/feather.min.css">
      <link rel="stylesheet" href="/assets/libs/flatpickr/dist/flatpickr.min.css">
      <link rel="stylesheet" href="/assets/css/theme.css">
      <link rel="stylesheet" href="/assets/css/select2.css">
      <link rel="stylesheet" href="/assets/css/bootstrap-colorpicker.min.css">
      <link rel="stylesheet" href="/assets/css/Chart.min.css">
      @if(config('customer_portal.google_analytics_enabled') && config('customer_portal.google_analytics_ga4_id'))
      <!-- Google tag (gtag.js) -->
      <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('customer_portal.google_analytics_ga4_id') }}"></script>
      <script nonce="{{ csp_nonce() }}">
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '{{ config('customer_portal.google_analytics_ga4_id') }}');
      </script>
      @endif
   </head>
