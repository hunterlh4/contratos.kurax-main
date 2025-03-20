function registro_foto_premios(){
    if(sec_id=="registro_foto_premios"){
        registro_foto_premios_events();
    }
}

function registro_foto_premios_events(){

    $('#imgInp').on('change', function(e){
        var files = e.target.files;
        var filesLength = files.length;
        // deleteImg();
       let conteo = $(this).data('cant').replace('{count}', filesLength);
       $('#leyenda').html('');
       $('#leyenda').html(conteo);
       $('.uploadInput').attr('disabled',false);

         if(filesLength>0){
             $('.uploadInput').addClass('activate');
             $('.uploadInput').removeClass('desactivate');
             $('.uploadInput').trigger('click');
         }else{
             $('.uploadInput').addClass('desactivate');
             $('.uploadInput').removeClass('activate');
         }

    });

    function loadImage(bool){
        var loadHtml = "<div class='lds-ring'><div></div><div></div><div></div><div></div></div>";
        var normal = "<i class='fa fa-picture-o' aria-hidden='true'></i> <br> <span id='leyenda'>Elegir imagenes</span>";
        var check = "<div class='divcheck'><i class='check'></i></div>";
        if(bool==true){
            $('.inputfile').addClass('load');
            $('.labelbtn').html('');
            $('.labelbtn').html(loadHtml);
        }else{
            //$('.inputfile').removeClass('load');
            $('.labelbtn').html('');
            $('.labelbtn').html(check);
        }
    };

    $("#formUpload").submit(function(e){
        e.preventDefault();
        console.log('llego la carga');
        if( $('#imgInp').val() != "" && $('#imgInp').val() != " " ){

        let urlget = "sys/set_registro_fotos_jackpot.php";
        var dataForm = new FormData(this);

        dataForm.append("sec_registro_fotos_jackpot","sec_registro_fotos_jackpot");
        loadImage(true);
            $.ajax({
                url: urlget,
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data){
                    loadImage(false);
                    swal({
                        title: "Registro Exitoso",
                        text: "",
                        type: "success",
                        timer: 1000,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: false
                    });

                    window.setTimeout(function(){
                        location.href = '/?action=logout';
                    } ,1500);

                }
            });
          }
       });

}
