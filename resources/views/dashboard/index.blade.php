@extends('layouts.app')


@section('links')
    @component('components.mainLinks',['active'=>"Dashboard"] )
    @endcomponent
@endsection

@section('content')
    <div class="row pl-4 flex-row">
        <div class="col-2 mr-3 ">

            <a href="/exchange" class="">
                <div class="py-3 border-bottom">Giełda</div>
            </a>


            <a href="#p" class="">
                <div class="py-3 border-bottom">Portfele</div>
            </a>


        </div>
        <div class="col-9 border-left ">
            @if(!(\Illuminate\Support\Facades\Auth::user()->public_token ))
                <div class="alert w3-orange">
                    Twoje konto nie zostało jeszcze połączone z giełdą, kliknij w baner aby skonfigurować integrację z
                    giełdą.
                    <a href="/account/settings/integration/create" class="stretched-link"></a>
                </div>
            @endif
            <h3>Podsumowanie:</h3>

            <div class="row">
                <div class="col-2 m-1 border">
                    BTC
                    <hr>
                    0.32131241 BTC
                </div>

                <div class="col-2 m-1 border">
                    BTC
                    <hr>
                    0.32131241 BTC
                </div>

                <div class="col-2 m-1 border">
                    BTC
                    <hr>
                    0.32131241 BTC
                </div>

                <div class="col-2 m-1 border">
                    BTC
                    <hr>
                    0.32131241 BTC
                </div>

                <div class="col-2 m-1 border">
                    BTC
                    <hr>
                    0.32131241 BTC
                </div>

                <div class="col-2 m-1 border">
                    BTC
                    <hr>
                    0.32131241 BTC
                </div>

                <div class="col-2 m-1 border">
                    BTC
                    <hr>
                    0.32131241 BTC
                </div>

                <div class="col-2 m-1 border">
                    BTC
                    <hr>
                    0.32131241 BTC
                </div>
            </div>

        </div>
    </div>
@endsection
