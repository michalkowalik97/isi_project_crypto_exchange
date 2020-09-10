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
        <div class="row">
            @if($disabled)
                @component('components.alertStrechedLink',['message'=>'Twoje konto nie zostało jeszcze połączone z giełdą, nie możesz dokonywać transakcji, kliknij w baner aby skonfigurować integrację z
    giełdą.','href'=>'/account/settings/integration/create'])@endcomponent
            @endif
        </div>

        <div class="row py-5">
            <div class="col-6">
                <a href="/exchange/offers/active" class="mx-2 btn btn-success">Moje oferty</a>
                <a href="/exchange/offers/history" class="mx-2 btn btn-success">Historia</a>

            </div>
            <div class="col-5" id="balance-wrapper">
                @include('exchange.balance')
            </div>
            </div>
        <div class="row">

            <form action="/select/market" method="post" class="form-autosubmit ml-2">
                @csrf
                <select name="market" class="select2" id="select-market">
                    @foreach($markets as $market)

                        <option value="{{$market->market_code}}" {!! ($market->market_code == $selected->market_code) ?  'selected' : '' !!} >{{$market->market_code}}</option>
                    @endforeach

                </select>
            </form>
        </div>


        <div class="row my-3">
            <div class="col-12 mb-3 mb-md-0 col-md-6  border-right ">
                <h6>Kupno</h6>
                <form action="/exchange/offer/{{$selected->market_code}}/buy" method="post">
                    @csrf
                    <div class="row">

                        <div class="col-5">
                            <label>Kurs {{$selected->second_currency}}
                                <i class=" fa fa-lock-open" aria-hidden="true"></i>
                            </label>

                            <input type="text" name="ra" class="form-control buy-ra">
                        </div>

                        <div class="col-1 mb-2 align-self-end"><i class="fa fa-times" aria-hidden="true"></i></div>

                        <div class="col-5">
                            <label>Ilość {{$selected->first_currency}}
                                <i class=" fa fa-lock-open" aria-hidden="true"></i>
                            </label>
                            <input type="text" name="ca" class="form-control buy-ca">
                        </div>

                    </div>

                    <div class="row my-2">
                        <div class="col-1 offset-5"><i class="fa fa-equals" aria-hidden="true"></i></div>
                    </div>

                    <div class="row">
                        <div class="col-11">
                            <label>Wartość {{$selected->second_currency}}
                                <i class=" fa fa-lock" aria-hidden="true"></i>
                            </label>

                            <input type="text" name="res" class="form-control buy-res" disabled>
                        </div>
                    </div>

                    <div class="row my-2">
                        <div class="col-12 hidden">
                            Otrzymasz: <br>
                            <span class="font-weight-bold buy-prov">0.00000000</span>&nbsp;{{$selected->first_currency}}
                        </div>
                        <div class="col-12 my-2">
                            <input type="submit" class="form-control btn btn-info trade-btn" value="Kup"
                                   @if ($disabled) disabled @endif >
                        </div>
                    </div>

                </form>
            </div>


            <div class="col-12 col-md-6 ">
                <h6>Sprzedaż</h6>
                <form action="/exchange/offer/{{$selected->market_code}}/sell">
                    @csrf

                    <div class="row">

                        <div class="col-5">
                            <label>Kurs {{$selected->second_currency}}
                                <i class="fa fa-lock-open" aria-hidden="true"></i>
                            </label>
                            <input type="text" name="ra" class="form-control sell-ra">
                        </div>

                        <div class="col-1 mb-2 align-self-end"><i class="fa fa-times" aria-hidden="true"></i></div>

                        <div class="col-5">
                            <label>Ilość {{$selected->first_currency}}
                                <i class="fa fa-lock-open" aria-hidden="true"></i>
                            </label>
                            <input type="text" name="ca" class="form-control sell-ca">
                        </div>

                    </div>

                    <div class="row my-2">
                        <div class="col-1 offset-5"><i class="fa fa-equals" aria-hidden="true"></i></div>
                    </div>

                    <div class="row">
                        <div class="col-11">
                            <label>Wartość {{$selected->second_currency}}
                                <i class="fa fa-lock" aria-hidden="true"></i>
                            </label>
                            <input type="text" name="res" class="form-control sell-res" disabled>
                        </div>
                    </div>

                    <div class="row my-2">
                        <div class="col-12 hidden" >
                            Otrzymasz: <br>

                            <span class="font-weight-bold sell-prov">0.00000000</span>&nbsp;{{$selected->second_currency}}
                        </div>
                        <div class="col-12 my-2">
                            <input type="submit" class="form-control btn btn-success trade-btn" value="Sprzedaj"
                                   @if ($disabled) disabled @endif>
                        </div>
                    </div>


                </form>
            </div>
        </div>

        <div id="offers-wrapper">

            @include('exchange.offers')
        </div>

        <div class="row ">
            <div class="col-3 offset-5 ">

                <button class="btn btn-outline-info toggleHiddenRow ">Pokaż wszystkie</button>
                <button class="btn btn-outline-info toggleHiddenRow hidden ml-4">Ukryj</button>
            </div>
        </div>

    </div>
@endsection
@include('components.notification')

@section('scripts')
    <script>
        let select = document.getElementById('select-market');
        let market = select[select.selectedIndex].text;

        function refreshOrderbook(market) {
            let visible = $('#offers-wrapper').hasClass('all-offers-visible');
            $.get('/exchange/get/orderbook/' + market + '/' + visible, function (response) {
                if (response.success) {
                    document.getElementById('offers-list').remove();
                    document.getElementById('offers-wrapper').innerHTML = response.data;
                    document.getElementById('balance').remove();
                    document.getElementById('balance-wrapper').innerHTML = response.balance;
                }
            });
        }

        $(document).ready(function () {
            $('.trade-btn').on('click', function (e) {
                e.preventDefault();
                let form = $(this).closest('form');

                $.post(form.attr('action'), form.serialize(), function (response) {
                    if (response.success) {
                        document.getElementById('notification-message').innerText = response.message;
                        refreshOrderbook(market);
                    } else {
                        document.getElementById('notification-message').innerText = response.message;

                    }

                    let notification = document.getElementById('notification');
                    notification.style.visibility = 'visible';
                    setTimeout(function () {
                        notification.style.visibility = 'hidden';
                    }, 5000);

                })
                    .always(function () {
                        form.find("input[type=text], textarea").val("");
                    });

            });

            setInterval(function () {
                refreshOrderbook(market);
            }, 3000);
        });

    </script>
@endsection