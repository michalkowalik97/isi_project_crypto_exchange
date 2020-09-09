@extends('layouts.app')


@section('links')
    @component('components.mainLinks',['active'=>"Dashboard"] )
    @endcomponent
@endsection



@section('content')
    <div class="row pl-4 flex-row">
        @include('components.dashboardLinks')
        <div class="col-9 border-left ">
            <form action="">
                <div class="form-group col-4">
                    @csrf
                    <label for="">Podaj kwotę doładowania</label>
                    <input type="number" class="form-control" name="value" id="value">
                    <input type="hidden" value="{{$id ?? ''}}" id="wallet">
                </div>

            </form>
            <div id="paypal-button-container" class="col-4"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script
            src="https://www.paypal.com/sdk/js?client-id=AbyyRrE2WovmfWeTFXKwC3qLIRSzy1lJp7GASrmNsL053P9YJXPE2lU-6GJFmi3d4PM9cXDOsYZerR3g&currency=PLN"> // Required. Replace SB_CLIENT_ID with your sandbox client ID.
    </script>
    <script>
        paypal.Buttons({
            createOrder: function (data, actions) {
                let value = document.getElementById('value').value;

                if (value === '') {
                    alert('Podaj kwotę');
                    return false;
                }
                // This function sets up the details of the transaction, including the amount and line item details.
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: value
                        }
                    }]
                });
            },
            onApprove: function (data, actions) {

                // This function captures the funds from the transaction.
                return actions.order.capture().then(function (details) {
                    console.log(details);
                    if (details.status === "COMPLETED") {
                        $.post('/wallets/paypal', {
                            wallet: document.getElementById('wallet').value,
                            amount: document.getElementById('value').value
                        },function (response) {
                            console.log(response);
                            if (response.success){
                                alert(response.message);
                            } else{
                                alert(response.message);
                            }
                        }).fail(function () {
                           alert("Wystąpił błąd, spróbój ponownie.");
                        });
                    }
                    document.getElementById('value').value = '';

                });
            }
        }).render('#paypal-button-container');
        // This function displays Smart Payment Buttons on your web page.
    </script>
@endsection