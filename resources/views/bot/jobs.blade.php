@extends('layouts.app')


@section('links')
    @component('components.mainLinks',['active'=>"Bot"] )
    @endcomponent
@endsection

@section('content')
    <div class="row pl-4 flex-row">
        @include('components.botLinks')
        <div class="col-9 border-left ">

            @if(!(\Illuminate\Support\Facades\Auth::user()->public_token ))
                @component('components.alertStrechedLink',['message'=>'Twoje konto nie zostało jeszcze połączone z giełdą, kliknij w baner aby skonfigurować integrację z
    giełdą.','href'=>'/account/settings/integration/create'])@endcomponent
            @endif


            <div class="row">

                <div class="col-12 m-1">
                    <a href="/bot/jobs/new" class="btn btn-info">Dodaj</a>
                </div>

            </div>
            <div class="row mt-5">
                <div class="col-12">
                    @if(count($jobs)>0)
                        <table class="table table-condensed">
                            <thead>
                            <tr>
                                <th>L.p.</th>
                                <th>Rynek</th>
                                <th>Maksymalna kwota inwestycji</th>
                                <th>Minimalny zysk</th>
                                <th>Status</th>
                                <th>Akcje</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($jobs as $key => $job)
                                <tr>
                                    <td>{{++$key}}</td>
                                    <td>{{$job->market->market_code}}</td>
                                    <td>{{number_format($job->max_value,2,',','')}} PLN</td>
                                    <td>{{number_format($job->min_profit,2,',','')}} PLN</td>
                                    <td>{!! ($job->active) ? 'Aktywne' : 'Nieaktywne' !!}</td>
                                    <td>
                                        <a href="/bot/jobs/{{$job->id}}" class="btn btn-secondary">Szczegóły</a>
                                        <a href="/bot/jobs/{{$job->id}}/toggle/active" class="btn btn-info confirm"
                                           >{!! ($job->active) ? 'Wyłącz' : 'Włącz' !!}</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        Nie posiadasz żadnych aktywnych zadań bota.
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
