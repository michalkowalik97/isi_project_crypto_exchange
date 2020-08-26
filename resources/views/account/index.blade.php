@extends('layouts.app')

@section('links')
    @component('components.mainLinks',['active'=>""] )
    @endcomponent
@endsection

@section('content')
    <div class="row pl-4 flex-row">
        <div class="col-2 mr-3 ">

            <a href="/account/settings/change/password" class="">
                <div class="py-3 border-bottom">Zmień hasło</div>
            </a>


            <a href="/account/settings/integration" class="">
                <div class="py-3 border-bottom">Integracja</div>
            </a>

            <a href="/account/settings/f2a" class="">
                <div class="py-3 border-bottom">2FA</div>
            </a>


        </div>
        <div class="col-9 border-left ">
            @yield('accountContent')


        </div>
    </div>
@endsection
