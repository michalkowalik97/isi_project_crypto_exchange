$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});


function exchange(select) {
    $('.' + select + '-ra').on('change', function (e) {
        let res = ($('.' + select + '-ra').val()) * ($('.' + select + '-ca').val());
        $('.' + select + '-res').val(res);
        $('.' + select + '-prov').html((($('.' + select + '-ca').val() * 0.9957)));
    });

    $('.' + select + '-ca').on('change', function (e) {
        let res = ($('.' + select + '-ra').val()) * ($('.' + select + '-ca').val());
        $('.' + select + '-res').val(res);
        $('.' + select + '-prov').html((($('.' + select + '-ca').val() * 0.9957)));

    });

/*    $('.fa-lock').on('click', function () {
        $(this).removeClass('fa-lock');
        $(this).addClass('fa-lock-open');
    });

    $('.fa-lock-open').on('click', function () {
        $(this).removeClass('fa-lock-open');
        $(this).addClass('fa-lock');
    });*/
}


$(document).ready(function () {
    $(".confirm").click(function (e) {
        var txt = $(this).data("txt");
        txt = (txt == undefined) ? "Operacja wymaga potwierdzenia" : txt;
        var c = confirm(txt);
        if (c == true) {

        } else {
            return false;
        }
    });


    $("#uploadImage").on("change", function (e) {

        var oFReader = new FileReader();
        oFReader.readAsDataURL(document.getElementById("uploadImage").files[0]);

        oFReader.onload = function (oFREvent) {
            document.getElementById("uploadPreview").src = oFREvent.target.result;

        };
    });


    $(".form-autosubmit").on("change", function (e) {
        e.preventDefault();
        this.submit();
    });

    if ($('.select2').length > 0) {
        $('.select2').select2();
    }

    $('.toggleHiddenRow').on("click", function () {
        if ($('#offers-wrapper').hasClass('all-offers-visible')){
            $('.toggleHiddenRow').toggleClass('hidden');
            $('.more-results').addClass('hidden');
            $('#offers-wrapper').removeClass('all-offers-visible');
        } else{
            $('.toggleHiddenRow').toggleClass('hidden');
            $('.more-results').removeClass('hidden');
            $('#offers-wrapper').addClass('all-offers-visible');
        }

    });


    //exchange fields
    exchange('buy');
    exchange('sell');

if($('input[name=app_env]').val() =='local') {
    ///exchange/offers/check
    setInterval(function () {
        $.get('/exchange/offers/check');
    }, 5000);

    setInterval(function () {
        $.get('/cron/stonks/maker');
    }, (30000));
}
});
