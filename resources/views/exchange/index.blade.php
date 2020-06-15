@extends('layouts.app')

@section('links')
    @component('components.mainLinks',['active'=>"Giełda"] )
    @endcomponent
@endsection

@section('content')
    <div class="container">
        <div class="row">

            <form action="/select/market" method="post" class="form-autosubmit">
                @csrf
                <select name="market">
                    <option value="btc-pln">BTC-PLN</option>
                    <option value="eth-pln">ETH-PLN</option>
                </select>
            </form>

        </div>
        <div class="row">

        </div>
    </div>
@endsection