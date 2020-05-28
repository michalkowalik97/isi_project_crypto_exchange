@php
    $links=
    [
    "Podsumowanie"=>"/cars/".$car->id,
    "Koszty"=>"/car/".$car->id."/costs",
    "Paliwo"=>"/car/".$car->id."/fuel",
     "Statystyki"=>"/car/".$car->id."/stats"
     ];
@endphp

@isset($links)
    @foreach($links as $key => $link)

        <li class="nav-item @if(strtolower($key) == strtolower($active)) active @endif "><a class="nav-link"
                                                                                            href="{{$link}}">{{$key}}</a>
        </li>
    @endforeach
    <span class="mr-5"></span>
@endisset