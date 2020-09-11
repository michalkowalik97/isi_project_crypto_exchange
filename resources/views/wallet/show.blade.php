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
            @if($offers && count($offers)>0)
                <h4></h4>
                <table class="table my-5">
                    <tr>
                        <th>L.p.</th>
                        <th>Rynek</th>
                        <th>Kurs</th>
                        <th>Kurs transakcji</th>
                        <th>Ilość</th>
                        <th>Typ oferty</th>
                        <th>Status</th>
                        <th>Data złożenia</th>
                    </tr>


                    @foreach($offers as $key => $offer)
                        <tr>
                            <td>{{++$key}}</td>
                            <td>{{$offer->market->market_code}}</td>
                            <td>{{App\Helpers\Helper::displayFloats ($offer->rate)}}</td>
                            <td>{{(!$offer->completed) ? 'nd' : (($offer->realise_rate) ? $offer->realise_rate + 0 : $offer->rate +0) }}</td>
                            <td>{!! $offer->displayAmount()!!}</td>
                            <td>{{$offer->getTypeTranslation()}}</td>
                            <td>@if($offer->trashed()) Anulowana @elseif($offer->completed )Zrealizowana @else
                                    Złożona @endif </td>
                            <td>{{$offer->created_at->format('d.m.Y H:i')}}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>

    </div>
@endsection

@section('scripts')


@endsection