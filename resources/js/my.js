$(document).ready(function() {
    $(".confirm").click(function (e) {
        var txt = $(this).data("txt");
        txt = (txt == undefined ) ? "Operacja wymaga potwierdzenia" : txt;
        var c =  confirm(txt);
        if (c == true){

        } else{
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

})

});