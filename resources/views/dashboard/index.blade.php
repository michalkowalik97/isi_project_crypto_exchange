@extends('layouts.app')


@section('links')
    @component('components.mainLinks',['active'=>"Dashboard"] )
    @endcomponent
@endsection

@section('content')
    <div class="row pl-4 flex-row">
        @include('components.dashboardLinks')
        <div class="col-9 border-left ">

            @if(!(\Illuminate\Support\Facades\Auth::user()->public_token ))
                @component('components.alertStrechedLink',['message'=>'Twoje konto nie zostało jeszcze połączone z giełdą, kliknij w baner aby skonfigurować integrację z
    giełdą.','href'=>'/account/settings/integration/create'])@endcomponent
            @endif
            <h3>Podsumowanie:</h3>

            <div class="row">

                @forelse($wallets as $wallet)
                    <div class="col-3 m-1 border">
                        <b><a href="wallets/{{$wallet->id}}"
                              class="stretched-link text-decoration-none">{{$wallet->name}}</a></b>
                        <hr>
                        Środki:
                        <b>  {{$wallet->all_founds}}  {{$wallet->currency}}</b> <br/>

                        Dostępne środki:
                        <b>  {{$wallet->available_founds}}  {{$wallet->currency}}</b> <br/>
                        Zablokowane środki:
                        <b>  {{$wallet->locked_founds}}  {{$wallet->currency}}</b> <br/>

                    </div>
                @empty

                @endforelse
                {{--      <div class="col-2 m-1 border">
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
                      </div>--}}
            </div>

        </div>
    </div>
@endsection
