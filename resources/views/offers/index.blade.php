@extends('layouts.app')

@section('links')
    @component('components.mainLinks',['active'=>"Giełda"] )
    @endcomponent
@endsection

@section('headscripts')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet"/>

@endsection

@section('content')
    <div class="container">
        <div class="row py-5">
            <div class="col-12">
                <a href="/exchange/offers/active" class="mx-2 btn btn-success">Moje oferty</a>
                <a href="/exchange/offers/history" class="mx-2 btn btn-success">Historia</a>

            </div>
        </div>
        <div class="row">

            <form action="" method="get" class="form-autosubmit ml-2">

                <select name="market" class="select2" id="select-market">
                    <option value="">Wszystkie</option>
                    @foreach($markets as $market)

                        <option value="{{$market->id}}" {!! ($market->id == Request::get('market')) ?  'selected' : '' !!} >{{$market->market_code}}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="row">
            <div class="col-12">
                @if(count($offers) <=0)
                    @component('components.alertInfo',['message'=>'Nie znaleziono żadnej oferty.'])@endcomponent
                @else
                    <table class="table">
                        <tr>
                            <th>L.p.</th>
                            <th>Rynek</th>
                            <th>Kurs</th>
                            <th>Ilość</th>
                            <th>Typ oferty</th>
                            <th>Data złożenia</th>
                        </tr>
                        @foreach($offers as $key => $offer)
                            <tr>
                                <th>{{++$key}}</th>
                                    <th>{{$offer->market->market_code }}</th>
                                <th>{{($offer->rate + 0)}}</th>
                                <th>{{($offer->amount + 0)}}</th>
                                <th>{{$offer->getTypeTranslation()}}</th>
                                <th>{{$offer->created_at->format('d.m.Y H:i')}}</th>
                            </tr>
                        @endforeach
                    </table>
                    {{$offers->links()}}
                @endif
            </div>
        </div>

    </div>
@endsection

@include('components.notification')

@section('scripts')

@endsection