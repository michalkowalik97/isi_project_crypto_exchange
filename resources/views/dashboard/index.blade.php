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
                @if($wallets && count($wallets) > 0)
                    @foreach($wallets as $wallet)
                        <div class="col-md-3 col-sm-12 m-1 border">
                            <b><a href="wallets/{{$wallet->id}}"
                                  class="stretched-link text-decoration-none">{{$wallet->name}}</a></b>
                            <hr>
                            Środki:
                            <b>  {{App\Helpers\Helper::displayFloats ($wallet->all_founds ,$wallet->type) }}  {{$wallet->currency}}</b>
                            <br/>

                            Dostępne środki:
                            <b>  {{App\Helpers\Helper::displayFloats ($wallet->available_founds ,$wallet->type) }}  {{$wallet->currency}}</b>
                            <br/>
                            Zablokowane środki:
                            <b>  {{App\Helpers\Helper::displayFloats ($wallet->locked_founds ,$wallet->type) }}  {{$wallet->currency}}</b>
                            <br/>

                        </div>

                    @endforeach
                @endif
            </div>

        </div>
    </div>
@endsection
