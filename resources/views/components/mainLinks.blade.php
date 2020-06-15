@php
    $links=
    [

    "Dashboard"=>'/',
    "Giełda"=>'/exchange',
    /*""=>'#',*/
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