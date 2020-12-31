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
    <span class="open-nav-on-start"></span>

    <div class="container">
        @include('components.exchangeLinks')


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
                <h4>Oferty</h4>
                @if(count($offers) <=0)
                    @component('components.alertInfo',['message'=>'Nie znaleziono żadnej oferty.'])@endcomponent
                @else
                    <table class="table table-responsive-sm">
                        <tr>
                            <th>L.p.</th>
                            <th>Rynek</th>
                            <th>Kurs</th>
                            <th>Kurs transakcji</th>
                            <th>Ilość</th>
                            <th>Typ oferty</th>
                            <th>Status</th>
                            <th>Data złożenia</th>
                            <th>Data realizacji</th>

                        </tr>
                        @php($i = \App\Helpers\Helper::getFirstRecordNumber(50))
                        @foreach($offers as $key => $offer)
                            <tr>
                                <td>{{$i++}}</td>
                                <td>{{$offer->market->market_code }}</td>
                                <td>{{($offer->rate + 0)}}</td>
                                <td>{{($offer->realise_rate) ? $offer->realise_rate + 0 : $offer->rate +0}}</td>
                                <td>{!! $offer->displayAmount()!!}</td>
                                <td>{{$offer->getTypeTranslation()}}</td>
                                <td>@if($offer->trashed()) Anulowana @elseif($offer->completed )Zrealizowana @endif </td>
                                <td>{{$offer->created_at->format('d.m.Y H:i')}}</td>
                                <td>{{$offer->updated_at->format('d.m.Y H:i')}}</td>

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
