<span id="balance">Twoje saldo: @foreach($wallets as $wallet)
    {{App\Helpers\Helper::displayFloats ($wallet->available_founds,$wallet->type)}} {{$wallet->currency}} &nbsp;
@endforeach
</span>