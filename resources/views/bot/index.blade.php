@extends('layouts.app')


@section('links')
    @component('components.mainLinks',['active'=>"Bot"] )
    @endcomponent
@endsection

@section('content')
    <div class="row pl-4 flex-row">
        @include('components.botLinks')
        <div class="col-9 border-left ">

            @if(!(\Illuminate\Support\Facades\Auth::user()->public_token ))
                @component('components.alertStrechedLink',['message'=>'Twoje konto nie zostało jeszcze połączone z giełdą, kliknij w baner aby skonfigurować integrację z
    giełdą.','href'=>'/account/settings/integration/create'])@endcomponent
            @endif


            <div class="row">
                <div class="col-12 m-1 border">
                    Tutaj na razie nic, później może jakieś podsumowanie, jakiś wykres zysków. A jak nie to się wyrzuci tą zakładkę i będą aktywne zadania jako
                    strona główna bota.
                </div>

            </div>

        </div>
    </div>
@endsection
