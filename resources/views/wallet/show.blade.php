@extends('layouts.app')


@section('links')
    @component('components.mainLinks',['active'=>"Dashboard"] )
    @endcomponent
@endsection

@section('content')
    <div class="row pl-4 flex-row">
        @include('components.dashboardLinks')
        <div class="col-9 border-left ">
            {{$wallet->name}}
        </div>
    </div>
@endsection