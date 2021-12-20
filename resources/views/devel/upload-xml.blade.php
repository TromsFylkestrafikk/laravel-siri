<!doctype html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('siri/js/manifest.js')}}"></script>
    <script src="{{ asset('siri/js/vendor.js')}}"></script>
    <title>Upload SIRI xml</title>
  </head>
  <body>
    <h1>The app, from blade</h1>
    <div id="app"></div>
    <script src="{{ asset('siri/js/app.js')}}"></script>
  </body>
</html>
