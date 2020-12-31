@extends('layouts.app')


@section('links')
    @component('components.mainLinks',['active'=>"Bot"] )
    @endcomponent
@endsection

@section('headscripts')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet"/>

@endsection

@section('content')

    <div class="flex-row">
        @include('components.botLinks')
        <div class="col-12 ">
            <span class="open-nav-on-start"></span>
            @if(!(\Illuminate\Support\Facades\Auth::user()->public_token ))
                @component('components.alertStrechedLink',['message'=>'Twoje konto nie zostało jeszcze połączone z giełdą, kliknij w baner aby skonfigurować integrację z
    giełdą.','href'=>'/account/settings/integration/create'])@endcomponent
            @endif


            <div class="row justify-content-center">
                <div class="col-md-4 col-sm-12">
                    <form action="/bot/jobs" method="post">
                        @csrf
                        <div class="form-group">
                            <label>Maksymalna kwota jaką bot może obracać, jeśli na swoim koncie nie masz tyle <b>niezablokowanych</b>
                                środków zostaną użyte wszystkie wolne środki.</label>
                            <div class="input-group">
                                <input type="text" name="max_value" value="{{old('max_value')}}" placeholder="np. 100" class="form-control ">
                                <div class="input-group-append">
                                    <div class="input-group-text">PLN</div>
                                </div>
                            </div>
                            @error('max_value')
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Określ minimalny zysk na pojedynczej transakcji. Jest to wartość uwzględniająca
                                prowizję, czyli kwota jaką zarobisz na pojedynczej transakcji. <b>Uwaga!</b> ustawienie
                                zbyt wysokiej kwoty może sprawić że transakcje będą dokonywane bardzo rzadko lub w
                                skrajnym przypadku nie będą wykonywane wcale. </label>
                            <div class="input-group">
                                <input type="text" name="min_profit" value="{{old('min_profit')}}" placeholder="np. 2" class="form-control ">
                                <div class="input-group-append">
                                    <div class="input-group-text">PLN</div>
                                </div>
                            </div>
                            @error('min_profit')
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Wybierz rynek na którym chcesz zarabiać:</label>
                            <select name="market_id" value="{{old('market_id')}}" class="select2 form-control" style="width: 100%;">
                                <option value="{{null}}"></option>
                                @foreach($markets as $market)
                                    <option value="{{$market->id}}" @if(old('market_id') == $market->id) selected @endif >{{$market->market_code}}</option>
                                @endforeach
                            </select>
                            @error('market_id')
                            <span class="text-danger">{{$message}}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <input type="submit" class="w3-button w3-black w3-hover-green" value="Zapisz">
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


