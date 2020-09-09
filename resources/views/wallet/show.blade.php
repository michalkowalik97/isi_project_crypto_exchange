@extends('layouts.app')


@section('links')
    @component('components.mainLinks',['active'=>"Dashboard"] )
    @endcomponent
@endsection

@section('content')
    <div class="row pl-4 flex-row">
        @include('components.dashboardLinks')
        <div class="col-9 border-left ">
            {{$wallet->name}} <br>
            Wszystkie środki: {{$wallet->all_founds + 0}} <br>
            Dostępne środki: {{$wallet->available_founds + 0}} <br>
            Zablokowane środki: {{$wallet->locked_founds + 0}} <br>
            @if($wallet->type=='cash')
                <div class="m-3">
                    <a href="/wallets/paypal/{{$wallet->id}}" class=" btn btn-info">Doładuj konto</a>
                </div>
            @endif
        </div>

    </div>
@endsection

@section('scripts')


@endsection