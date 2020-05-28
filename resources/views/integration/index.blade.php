@extends('account.index')

@section('accountContent')
    {{--integracje
        {{dump($token)}}--}}
    <h4>Dodany klucz publiczny</h4>
    <table class="table">
        <tr>
            <td>{{$token}}</td>

            <td>
                <form action="/account/settings/integration" method="post">
                    @csrf
                    @method("DELETE")
                    <button class="confirm btn btn-danger" {{--class="w3-button w3-hover-red w3-black w3-small text-decoration-none confirm"--}}
                    data-txt="Usunięcie spowoduje niedostępność niektórych funkcji, czy chcesz kontynuować?"><i
                                class="fas fa-trash text-white"></i> Usuń</a>
                    </button>
                </form>
            {{--    <a href="#" class="confirm btn btn-danger"
                   data-txt="Usunięcie spowoduje niedostępność niektórych funkcji, czy chcesz kontynuować?"><i
                            class="fas fa-trash text-white"></i> Usuń</a>--}}
            </td>
        </tr>
    </table>
@endsection