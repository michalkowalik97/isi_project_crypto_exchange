@extends('layouts.app')
@section('headscripts')
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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
                <div class="col-12 m-1 ">
                    <div id="markets_bar"></div>
                </div>
                <div class="col-12">
                    @if(count($markets)> 0)
                        <table class="table table-condensed table-responsive-sm markets_bar mt-5" id="markets_bar_table">
                            <tr>
                                <th>Lp.</th>
                                <th class="data-title">Rynek</th>
                                <th class="data-title">Łączny zysk</th>
                            </tr>
                            @php($i=1)
                            @foreach($markets as $key => $market)
                                <tr class="data-row">
                                    <td>{{$i++}}</td>
                                    <td class="data-title">{{$key}}</td>
                                    <td class="data-value" data-value="{{number_format($market,2,'.',' ')}}">{{number_format($market,2,',',' ')}} zł</td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                    @endif
                </div>

            </div>

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
                        rowData.push(col.innerText);
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

        function drawBasic() {
            let tableData = getDataFromDataTable('markets_bar_table');
            var data = google.visualization.arrayToDataTable(tableData);

            var options = {
                title: 'Zyski na poszczególnych rynkach ',
                chartArea: {width: '50%'},
                hAxis: {
                    title: 'Zysk',
                    minValue: 0
                },
                vAxis: {
                    title: 'Rynek'
                }
            };

            var chart = new google.visualization.BarChart(document.getElementById('markets_bar'));

            chart.draw(data, options);
        }
    </script>
@endsection
