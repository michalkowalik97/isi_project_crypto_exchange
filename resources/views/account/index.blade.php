@extends('layouts.app')

@section('links')
    @component('components.mainLinks',['active'=>""] )
    @endcomponent
@endsection

@section('content')
    <span class="open-nav-on-start"></span>

    <div class="<!--row pl-4 --> flex-row d-flex justify-content-end">
        @include('components.accountLinks')

        {{--  <div class="col-2 mr-3 ">

              <a href="/account/settings/change/password" class="">
                  <div class="py-3 border-bottom">Zmień hasło</div>
              </a>


              <a href="/account/settings/integration" class="">
                  <div class="py-3 border-bottom">Integracja</div>
              </a>

          --}}{{--    <a href="/account/settings/f2a" class="">
                  <div class="py-3 border-bottom">2FA</div>
              </a>--}}{{--


          </div>--}}
        <div class="col-md-9 col-sm-12">
            @yield('accountContent')


        </div>
    </div>
@endsection
