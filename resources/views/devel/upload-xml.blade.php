<!doctype html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('siri/js/vendor.js')}}"></script>
    <script src="{{ asset('siri/js/app.js')}}"></script>
    <title>Upload SIRI xml</title>
  </head>
  <body>
    <h1>Emulate SIRI service delivery</h1>
    <div class="content">
      @if ($errors->any())
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      <form method="POST" action="{{ route('emulate.store') }}" enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="form-input form-file">
          <label for="siri-xml">Valid SIRI XML file.</label> <br />
          <input type="file" id="siri-xml" name="siri-xml" />
        </div>
        <div class="form-input form-select">
          <label for="siri-subscription">Use this subscription</label> <br />
          <select id="siri-subscription" name="id">
            @foreach ($subscriptions as $subscription)
              <option value="{{ $subscription->id }}">{{ $subscription->channel }} – {{ $subscription->subscription_url }}</option>
            @endforeach
          </select>
        </div>
        <input type="submit" value="Emulate request" />
      </form>
    </div>
  </body>
</html>
