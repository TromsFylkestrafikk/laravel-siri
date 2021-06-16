<!doctype html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Upload SIRI xml</title>
  </head>
  <body>
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
          <label for="siri-service">Siri request handler type</label> <br />
          <select id="siri-channel" name="siri_channel">
            <option value="ET">Estimated Timetable (ET)</option>
            <option value="VM">Vehicle Monitoring (VM)</option>
            <option value="SX">Situation Exchange (SX)</option>
          </select>
        </div>
        <input type="submit" value="Emulate request" />
      </form>
    </div>
  </body>
</html>
