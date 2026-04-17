<!DOCTYPE html>
<html lang="{{$language}}">
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <meta name="csrf-token" content="{{ csrf_token() }}">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <meta name="description" content="Customer Portal">
      <title>{{config("customer_portal.company_name")}}</title>
      <link rel="stylesheet" media="all" href="/assets/css/theme-root.css">
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