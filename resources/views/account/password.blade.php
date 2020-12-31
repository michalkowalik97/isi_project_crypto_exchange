@extends('account.index')

@section('accountContent')

    <div class="my-5 col-md-7 col-sm-12">
        <h4>Zmiana hasła</h4>
        <form action="/account/settings/change/password" method="POST">
            @csrf
            <div class="form-group">
                <label for="old_password">Stare hasło</label>
                <input type="password"
                       class="form-control @error('old_password') w3-border-bottom w3-border-red w3-pale-red @enderror "
                       name="old_password" value="{{old('old_password')}}" id="old_password" required>
                @error('old_password')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="new_password">Nowe hasło</label>
                <input type="password"
                       class="form-control @error('new_password') w3-border-bottom w3-border-red w3-pale-red @enderror "
                       name="new_password" value="{{old('new_password')}}" id="new_password" required>
                @error('new_password')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Powtórz hasło</label>
                <input type="password"
                       class="form-control @error('password_confirmation') w3-border-bottom w3-border-red w3-pale-red @enderror "
                       name="password_confirmation" value="{{old('password_confirmation')}}" id="password_confirmation" required>
                @error('password_confirmation')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>



            <div class="form-group">

                <input type="submit" class="w3-button w3-black w3-hover-green" value="Zapisz">
            </div>


        </form>
    </div>

@endsection
