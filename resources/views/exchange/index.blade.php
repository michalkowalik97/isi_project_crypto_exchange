@extends('layouts.app')

@section('links')
    @component('components.mainLinks',['active'=>"Giełda"] )
    @endcomponent
@endsection

@section('headscripts')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        .hidden {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row">

            <form action="/select/market" method="post" class="form-autosubmit">
                @csrf

                <select name="market" class="select2">
                    @foreach($markets as $market)

                        <option value="{{$market->market_code}}" {!! ($market->market_code == $selected->market_code) ?  'selected' : '' !!} >{{$market->market_code}}</option>
                    @endforeach

                </select>
            </form>

        </div>

        <div class="row my-3">

        </div>

        <div class="row my-3">
            <div class="col-12 mb-3 mb-md-0 col-md-6  border-right ">
                <h6>Kupno</h6>
                <form action="/exchange/buy/{{$selected}}">
                    @csrf
                    <div class="row">

                        <div class="col-5">
                            <label>Kurs {{$selected->second_currency}}</label>
                            <input type="text" class="form-control">
                        </div>

                        <div class="col-1 mb-2 align-self-end"><i class="fa fa-times" aria-hidden="true"></i></div>

                        <div class="col-5">
                            <label>Kurs {{$selected->first_currency}}</label>
                            <input type="text" class="form-control">
                        </div>

                    </div>

                    <div class="row my-2">
                        <div class="col-1 offset-5"><i class="fa fa-equals" aria-hidden="true"></i></div>
                    </div>

                    <div class="row">
                        <div class="col-11">
                            <label>Wartość {{$selected->second_currency}}</label>
                            <input type="text" class="form-control">
                        </div>
                    </div>

                    <div class="row my-2">
                        <div class="col-12">
                            Otrzymasz: <br>
                            <span class="font-weight-bold">0.00000000</span>&nbsp;{{$selected->first_currency}}
                        </div>
                        <div class="col-12 my-2">
                            <input type="submit" class="form-control btn btn-info" value="Kup">
                        </div>
                    </div>

                </form>
            </div>


            <div class="col-12 col-md-6 ">
                <h6>Sprzedaż</h6>
                <form action="/exchange/sell/{{$selected}}">
                    @csrf

                    <div class="row">

                        <div class="col-5">
                            <label>Kurs {{$selected->second_currency}}</label>
                            <input type="text" class="form-control">
                        </div>

                        <div class="col-1 mb-2 align-self-end"><i class="fa fa-times" aria-hidden="true"></i></div>

                        <div class="col-5">
                            <label>Kurs {{$selected->first_currency}}</label>
                            <input type="text" class="form-control">
                        </div>

                    </div>

                    <div class="row my-2">
                        <div class="col-1 offset-5"><i class="fa fa-equals" aria-hidden="true"></i></div>
                    </div>

                    <div class="row">
                        <div class="col-11">
                            <label>Wartość {{$selected->second_currency}}</label>
                            <input type="text" class="form-control">
                        </div>
                    </div>

                    <div class="row my-2">
                        <div class="col-12">
                            Otrzymasz: <br>
                            <span class="font-weight-bold">0.00000000</span>&nbsp;{{$selected->second_currency}}
                        </div>
                        <div class="col-12 my-2">
                            <input type="submit" class="form-control btn btn-success" value="Sprzedaj">
                        </div>
                    </div>


                </form>
            </div>
        </div>

        <div class="row my-5 ">
            <div class="col-6 border-right">
                <h6>Oferty kupna</h6>
                <table class="table table-hover ">
                    <tr>
                        <td>Kurs</td>
                        <td>Ilość</td>
                    </tr>

                    @foreach($orderbook->buy as $key => $buy)
                        <tr class="@if($key > 10 )hidden @endif">
                            <td>{{$buy->ra}}</td>
                            <td>{{$buy->ca}}</td>
                        </tr>
                    @endforeach
                </table>
            </div>


            <div class="col-6">
                <h6>Oferty sprzedaży</h6>

                <table class="table table-hover ">
                    <tr>
                        <td>Kurs</td>
                        <td>Ilość</td>
                    </tr>

                    @foreach($orderbook->sell as $key => $sell)
                        <tr class="@if($key > 10 )hidden @endif">
                            <td>{{$sell->ra}}</td>
                            <td>{{$sell->ca}}</td>
                        </tr>
                    @endforeach
                </table>
            </div>

        </div>
        <div class="row ">
            <div class="col-3 offset-5 ">

                <button class="btn btn-outline-info toggleHiddenRow ">Pokaż wszystkie</button>
                <button class="btn btn-outline-info toggleHiddenRow hidden ml-4">Ukryj</button>
            </div>
        </div>

    </div>
@endsection