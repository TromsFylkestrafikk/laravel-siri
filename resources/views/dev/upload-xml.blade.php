<!doctype html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('siri/js/manifest.js')}}"></script>
    <script src="{{ asset('siri/js/vendor.js')}}"></script>
    @env('local')
    @routes
    @endenv
    <title>Upload SIRI xml</title>
  </head>
  <body>
    <div id="app"></div>
    <script src="{{ asset('siri/js/app.js')}}"></script>
  </body>
</html>
