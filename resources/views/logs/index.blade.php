@extends('layouts.app')


@section('links')
    @component('components.mainLinks',['active'=>"Dashboard"] )
    @endcomponent
@endsection

@section('content')
    <div class="row pl-4 flex-row">
        @include('components.dashboardLinks')
        <div class="col-9 border-left ">

            <div class="row">
                @if($files && count($files) > 0)

                    <ul>
                        @foreach($files as $file)
                            <li><a href="/logs/download/{{$file}}" target="_blank">{{$file}}</a></li>
                        @endforeach
                    </ul>

                @endif
            </div>

        </div>
    </div>
@endsection
