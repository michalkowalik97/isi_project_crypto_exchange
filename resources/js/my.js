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

    $('.select2').select2();

    $('.toggleHiddenRow').on("click", function () {
        $('.toggleHiddenRow').toggleClass('hidden');
        $('tr').toggleClass('hidden');
    });


    //exchange fields
    exchange('buy');
    exchange('sell');

    $('.fa-lock').on('click', function () {
        $(this).removeClass('fa-lock');
        $(this).addClass('fa-lock-open');
    });

    $('.fa-lock-open').on('click', function () {
        $(this).removeClass('fa-lock-open');
        $(this).addClass('fa-lock');
    });


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

    $('.fa-lock').on('click', function () {
        $(this).removeClass('fa-lock');
        $(this).addClass('fa-lock-open');
    });

    $('.fa-lock-open').on('click', function () {
        $(this).removeClass('fa-lock-open');
        $(this).addClass('fa-lock');
    });
}