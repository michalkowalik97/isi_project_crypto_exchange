@php
if(!isset($visible)){
$visible=false;
}
@endphp
<div class="row my-5" id="offers-list">
    <div class="col-6 border-right">
        <h6>Oferty kupna</h6>
        <table class="table table-hover table-responsive-sm">
            <tr>
                <td>Kurs</td>
                <td>Ilość</td>
            </tr>

            @foreach($orderbook->buy as $key => $buy)
                <tr class="@if($key > 10 ) @if(!$visible) hidden @endif more-results @endif">
                    <td>{{($buy->ra + 0)}}</td>
                    <td>{{App\Helpers\Helper::displayFloats ($buy->ca )}}</td>
                </tr>
            @endforeach
        </table>
    </div>


    <div class="col-6">
        <h6>Oferty sprzedaży</h6>

        <table class="table table-hover table-responsive-sm">
            <tr>
                <td>Kurs</td>
                <td>Ilość</td>
            </tr>

            @foreach($orderbook->sell as $key => $sell)
                <tr class="@if($key > 10 )@if(!$visible) hidden @endif more-results @endif">
                    <td>{{($sell->ra + 0)}}</td>
                    <td>{{App\Helpers\Helper::displayFloats ($sell->ca )}}</td>
                </tr>
            @endforeach
        </table>
    </div>

</div>
