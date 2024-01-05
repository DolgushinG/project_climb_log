<div class="col-md-6">
    @if(Session::has('message'))
     <p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
  @endif
  @if(Session::has('alert-danger'))
  <p class="alert {{ Session::get('alert-class', 'alert-danger') }}">{{ Session::get('alert-danger') }}</p>
  @endif
   <div class="messages"></div>
  </div>
