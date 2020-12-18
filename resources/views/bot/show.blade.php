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

            @if($job->history && count($job->history) > 0)
                <div class="row">
                    <h3>Historia transakcji</h3>
                    <div class="col-12 m-1 border">
                        <table class="table">
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
                                    @php($i=1)
                            @foreach($job->history as $history)
                                <tr>
                                    <td>{{$i++}}</td>
                                    <td>{{$history->offer->market->market_code }}</td>
                                    <td>{{($history->offer->rate + 0)}}</td>
                                    <td>{{($history->offer->realise_rate) ? $history->offer->realise_rate + 0 : $history->offer->rate +0}}</td>
                                    <td>{!! $history->offer->displayAmount()!!}</td>
                                    <td>{{$history->offer->getTypeTranslation()}}</td>
                                    <td>@if($history->offer->trashed()) Anulowana @elseif($history->offer->completed )Zrealizowana @endif </td>
                                    <td>{{$history->offer->created_at->format('d.m.Y H:i')}}</td>

                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
