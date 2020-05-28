@extends('account.index')

@section('accountContent')
    <div>
        Aby dodać integrację potrzebujesz konta na giełdzie <a href="https://bitbay.net/pl"
                                                               class="text-decoration-none w3-text-blue"
                                                               target="_blank">BitBay</a>, konto jest całkowicie
        darmowe.
        <br>
        Jeśli posiadasz już konto zaloguj się i przejdź do ustawień konta, a następnie przejdź do zakładki <a
                href="https://auth.bitbay.net/settings/api" class="text-decoration-none w3-text-blue"
                target="_blank">API</a>.
        <br>Kliknij przycisk "Dodaj nowy klucz" i zaznacz opcje:
        <ul>
            <li>Pobieranie listy portfeli</li>
            <li>Edytowanie portfeli</li>
            <li>Historia</li>
            <li>Pobieranie konfiguracji rynków i wystawianie ofert</li>
            <li>Zmiania konfiguracji rynków i zarządzanie ofertami</li>
        </ul>
        Po utworzeniu klucza zostaną wyświetlone dwa kody qr, a pod nimi klucze. <br><br>
        <span class="text-danger"> Uwaga!</span> klucz prywanty będzie widoczny tylko raz po utworzeniu, należy go
        skopiować i wkleić do formularza poniżej.
        <br> Jeśli zamkniesz okienko z kluczami nie będzie możliwe dokończenie integracji z giełdą, należy wtedy usunąć
        utworzony klucz i powtórzyć wszystkie opisane czynności.
    </div>

    <div class="my-5">
        <h4>Podaj wygenerowane klucze</h4>
        <form action="/account/settings/integration" method="POST">
            @csrf
            <div class="form-group">
                <label for="klucz_publiczny">Klucz publiczny</label>
                <input type="text"
                       class="form-control @error('klucz_publiczny') w3-border-bottom w3-border-red w3-pale-red @enderror "
                       name="klucz_publiczny" value="{{old('klucz_publiczny')}}" id="klucz_publiczny" required>
                @error('klucz_publiczny')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="klucz_prywatny">Klucz prywatny</label>
                <input type="text"
                       class="form-control  @error('klucz_prywatny') w3-border-bottom w3-border-red w3-pale-red @enderror "
                       name="klucz_prywatny" value="{{old('klucz_prywatny')}}" id="klucz_prywatny" required>
                @error('klucz_prywatny')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">

                <input type="submit" class="w3-button w3-black w3-hover-green" value="Zapisz">
            </div>


        </form>
    </div>

@endsection