@extends('account.index')

@section('accountContent')
{{--integracje
    {{dump($token)}}--}}
<h4>Dodany klucz publiczny</h4>
    <table class="table">
        <tr>
            <td>{{$token}}</td>
            <td><a href="#" class="confirm btn btn-danger" data-txt="Usunięcie spowoduje niedostępność niektórych funkcji, czy chcesz kontynuować?">Usuń</a></td>
        </tr>
    </table>
@endsection