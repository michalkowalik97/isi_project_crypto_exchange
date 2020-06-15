@extends('layouts.app')

@section('links')
    @component('components.mainLinks',['active'=>"Giełda"] )
    @endcomponent
@endsection

@section('headscripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>


    <script src="{{asset('js/select2.min.js')}}"></script>
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>


@endsection

@section('content')
    <div class="container">
        <div class="row">

            <form action="/select/market" method="post" class="form-autosubmit">
                @csrf

                <select name="market" class="form-control select2">
                    @foreach($markets as $market)

                        <option value="{{$market->market_code}}">{{$market->market_code}}</option>
                    @endforeach

                </select>
            </form>

        </div>
        <div class="row">

        </div>
    </div>
@endsection