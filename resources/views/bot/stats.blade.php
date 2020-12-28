@extends('layouts.app')
@section('headscripts')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet"/>

@endsection

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
                <div class="col-12">
                    <form action="" method="get" class="form-inline mb-5">

                        <div class="form-group mx-1">
                            <label>Od</label>
                            <input type="date" name="from" class="form-control"
                                   value="{{old('from',request()->get('from'))}}">
                        </div>

                        <div class="form-group mx-1">
                            <label>Do</label>
                            <input type="date" name="to" class="form-control"
                                   value="{{old('to',request()->get('to'))}}">
                        </div>


                        <div class="form-group mx-1">
                            <input type="submit" class="form-control btn btn-success mx-1 my-sm-1" value="Filtruj">
                            <a href="/bot/stats" class="btn btn-danger mx-1 my-sm-1">Resetuj filtry</a>
                            {{--<input type="reset" class="form-control btn btn-danger mx-1 my-sm-1" value="Resetuj">--}}
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-12 m-1 ">
                    <h4>Zyski na poszczególnych rynkach</h4>
                </div>
                <div class="col-12 m-1 ">
                    @if(count($markets)> 0)
                        <div id="markets_bar"></div>
                    @else
                        <div class="alert alert-info">Nie znaleziono żadnych wyników.</div>
                    @endif
                </div>
                <div class="col-12">
                    @if(count($markets)> 0)
                        <table class="table table-condensed table-responsive-sm markets_bar mt-5"
                               id="markets_bar_table">
                            <tr>
                                <th>Lp.</th>
                                <th class="data-title" data-value="Rynek">Rynek</th>
                                <th class="data-title" data-value="Łączny zysk">Łączny zysk</th>
                            </tr>
                            @php($i=1)
                            @php($sum=0)
                            @foreach($markets as $key => $market)
                                <tr class="data-row">
                                    <td>{{$i++}}</td>
                                    <td class="data-title" data-value="{{$key}}">{{$key}}</td>
                                    <td class="data-value"
                                        data-value="{{number_format($market,2,'.',' ')}}">{{number_format($market,2,',',' ')}}
                                        zł
                                    </td>
                                </tr>
                                @php($sum+=$market)
                            @endforeach
                            <tr>

                                <td colspan="2"><b>SUMA</b></td>
                                <td><b>{{number_format($sum,2,',',' ')}} zł</b></td>
                            </tr>
                        </table>
                    @endif
                </div>

            </div>
            <div class="groups-separator"></div>

            <div class="row">
                <div class="col-12 m-1">
                    <h4>Zysk z podziałem na dni</h4>
                </div>

                <div class="col-12 m-1 ">
                    @if(count($dailyJobProfits)> 0)
                        <div id="days_line"></div>
                    @else
                        <div class="alert alert-info">Nie znaleziono żadnych wyników.</div>
                    @endif
                </div>
                <div class="col-12">
                    @if(count($dailyJobProfits)> 0)
                        <table class="table table-condensed table-responsive-sm days_line mt-5"
                               id="days_line_table">
                            <tr>
                                <th>Lp.</th>
                                <th class="data-title" data-value="Data">Data</th>
                                <th class="data-title" data-value="Zysk">Zysk</th>
                            </tr>
                            @php($i=1)
                            @php($sum=0)
                            @foreach($dailyJobProfits as $key => $dailyProfit)
                                <tr class="data-row">
                                    <td>{{$i++}}</td>
                                    <td class="data-title" data-value="{{$key}}">{{$key}}</td>
                                    <td class="data-value"
                                        data-value="{{number_format($dailyProfit,2,'.',' ')}}">{{number_format($dailyProfit,2,',',' ')}}
                                        zł
                                    </td>
                                </tr>
                                @php($sum+=$dailyProfit)
                            @endforeach
                            <tr>

                                <td colspan="2"><b>SUMA</b></td>
                                <td><b>{{number_format($sum,2,',',' ')}} zł</b></td>
                            </tr>
                        </table>
                    @endif
                </div>
            </div>

            <div class="groups-separator"></div>

            <div class="row">
                <div class="col-12 m-1">
                    <h4>Dzienny zysk z podziałem na rynki</h4>
                </div>

                <div class="col-12 m-1 ">
                    @if(count($dailyJobProfits)> 0)
                        <div id="days_market_line"></div>
                    @else
                        <div class="alert alert-info">Nie znaleziono żadnych wyników.</div>
                    @endif
                </div>
                <div class="col-12">
                    @if(count($dailyJobProfitsByMarket)> 0)
                        <table class="table table-condensed table-responsive-sm days_line mt-5"
                               id="days_market_line_table">
                            <tr>
                                <th>Lp.</th>
                                <th class="data-title" data-value="Data">Data</th>
                                <th class="data-title" data-value="Rynek">Rynek</th>
                                <th class="data-title" data-value="Zysk">Zysk</th>
                            </tr>
                            @php($i=1)
                            @php($sum=0)
                            @foreach($dailyJobProfitsByMarket as $date => $marketsProfit)
                                @foreach($marketsProfit as $market =>$profit)
                                    <tr class="data-row
                                        @if(array_key_last($marketsProfit)== $market && array_key_last($dailyJobProfitsByMarket)!= $date)
                                        tr-border-bottom-bold
@endif

                                    @if(array_key_first($dailyJobProfitsByMarket) == $date)
                                        labels-tr
@endif
                                        ">
                                        <td>{{$i++}}</td>
                                        <td class="data-title" data-value="{{$date}}">{{$date}}</td>
                                        <td class=" label-td" data-value="{{$market}}">{{$market}}</td>
                                        <td class="data-value"
                                            data-title="{{$date}}"
                                            data-value="{{number_format($profit,2,'.',' ')}}">{{number_format($profit,2,',',' ')}}
                                            zł
                                        </td>
                                    </tr>
                                    @php($sum+=$profit)
                                @endforeach
                            @endforeach
                            <tr>

                                <td colspan="3"><b>SUMA</b></td>
                                <td><b>{{number_format($sum,2,',',' ')}} zł</b></td>
                            </tr>
                        </table>
                    @endif
                </div>
            </div>

            <div class="groups-separator"></div>

            <div class="row">
                <div class="col-12 m-1">
                    <h4>Zyski poszczególnych zadań bota</h4>
                </div>
                @if(count($jobStonks)<= 0)
                    <div class="col-12 m-1">
                        <div class="alert alert-info">Nie znaleziono żadnych wyników.</div>
                    </div>
                @endif
                <div class="col-12 ">
                    @if(count($jobStonks)> 0)
                        <table class="table table-condensed table-responsive-sm markets_bar mt-5"
                               id="jobs_bar_table">
                            <tr>
                                <th>Lp.</th>
                                <th>Rynek</th>
                                <th>Minilany zysk</th>
                                <th>Maksymalna kwota</th>
                                <th>Łączny zysk</th>
                            </tr>
                            @php($i=1)
                            @php($sum=0)
                            @foreach($jobStonks as $key => $job)
                                <tr class="data-row">
                                    <td>{{$i++}}</td>
                                    <td>
                                        <a href="/bot/jobs/{{$job['job']->id}}"
                                           target="_blank">{{$job['job']->market->market_code}}</a></td>
                                    <td>{{$job['job']->min_profit}}zł</td>
                                    <td>{{$job['job']->max_value}}zł</td>
                                    <td class="data-value"
                                        data-value="{{number_format($job['profit'],2,'.',' ')}}">{{number_format($job['profit'],2,',',' ')}}
                                        zł
                                    </td>
                                </tr>
                                @php($sum+=$job['profit'])
                            @endforeach
                            <tr>

                                <td colspan="4"><b>SUMA</b></td>
                                <td><b>{{number_format($sum,2,',',' ')}} zł</b></td>
                            </tr>
                        </table>
                    @endif
                </div>
            </div>

            <div class="groups-separator"></div>


        </div>
    </div>
@endsection
@section('scripts')
    <script>
        google.charts.load('current', {packages: ['corechart', 'bar']});
        google.charts.setOnLoadCallback(drawBasic);

        function getDataFromDataTable(tableId) {
            let table = document.getElementById(tableId);

            let data = [];

            for (var i = 0, row; row = table.rows[i]; i++) {
                let rowData = [];
                for (var j = 0, col; col = row.cells[j]; j++) {
                    if (col.classList.contains('data-title')) {
                        rowData.push(col.getAttribute('data-value'));
                    }
                    if (col.classList.contains('data-value')) {
                        rowData.push(parseFloat(col.getAttribute('data-value')));
                    }
                }
                if (rowData.length > 0) {
                    data.push(rowData);
                }
            }
            return data;
        }

        function getDataForMultipleChart(tableId) {
            let table = document.getElementById(tableId);

            let data = [];
            let labels = ['X'];
            for (var i = 0, row; row = table.rows[i]; i++) {
                if (row.classList.contains('labels-tr')) {
                    for (var j = 0, col; col = row.cells[j]; j++) {
                        if (col.classList.contains('label-td')) {
                            labels.push(col.getAttribute('data-value'));
                        }
                    }
                }
            }
            data.push(labels);


            for ( i = 0; row = table.rows[i]; i++) {
                let rowData = [];
                for ( j = 0; col = row.cells[j]; j++) {

                    if (col.classList.contains('data-value')) {
                        let found = false;
                        let title = col.getAttribute('data-title');
                        data.forEach(function (item, index) {
                            if (item[0] == title) {
                                item.push(parseFloat(col.getAttribute('data-value')));
                                found = true;
                            }
                        });
                        if (found === false) {

                            data.push(
                                [
                                    col.getAttribute('data-title'),
                                (parseFloat(col.getAttribute('data-value')))
                                ]);
                        }
                    }
                }
            /*    if (rowData.length > 0) {
                    data.push(rowData);
                }*/
            }

            return data;
        }

        function drawBasic() {
            if (document.getElementById('markets_bar') == null) {
                return false;
            }

            let marketsBarData = google.visualization.arrayToDataTable(getDataFromDataTable('markets_bar_table'));
            let daysLineData = google.visualization.arrayToDataTable(getDataFromDataTable('days_line_table'));
            let daysLineMarketData = google.visualization.arrayToDataTable(getDataForMultipleChart('days_market_line_table'));
        //    console.log(daysLineMarketData);
            let options = {
                title: 'Zyski na poszczególnych rynkach ',
                chartArea: {width: '75%'},
                hAxis: {
                    title: 'Zysk',
                    minValue: 0
                },
                vAxis: {
                    title: 'Rynek'
                }
            };

            let marketsBarchart = new google.visualization.BarChart(document.getElementById('markets_bar'));
            marketsBarchart.draw(marketsBarData, options);

            let daysLine = new google.visualization.LineChart(document.getElementById('days_line'));
            options.title = 'Zysk z podziałem na dni';
            options.vAxis.title = 'Zysk';
            options.hAxis.title = 'Dzień';
            daysLine.draw(daysLineData, options);

            let daysMarketLine = new google.visualization.ColumnChart(document.getElementById('days_market_line'));
            daysMarketLine.draw(daysLineMarketData, options);

        }
    </script>
@endsection
