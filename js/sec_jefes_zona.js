$("#guardar_jefe_zona").on('click', function(e){
    e.preventDefault();
    update_jefe();
});

function obtener_id (id_modal, zona_modal, id_responsable){
    im=id_modal;
    zm=zona_modal;
    $("#update_zona").val(im);
    $("#id_zona_span").text(zm);

    $("#update_jefe").val(id_responsable).change();
}

function update_jefe (){
    Swal.fire({
        title: '¡Cuidado!',
        text: "¿Está seguro(a) de cambiar el Jefe de Zona?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ¡cámbialo!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            loading(true);
            var get_data = {};
            get_data.accion = "buscar_otras_zonas";
            get_data.update_jefe = $('select[name="update_jefe"]').val();
            $.ajax({
                method: "POST",
                url:"sys/get_jefes_zona.php",
                data: get_data,
                success: function(response){
                    loading(false);
                    var jsonData = JSON.parse(response);
					if (jsonData.error == true) {
                        var mensaje = "El Jefe de Zona: '" + $('select[name="update_jefe"] option:selected').text() + "' ya es jefe de: '" + jsonData.otra_zona + "'. ¿Está seguro(a) de cambiar el jefe de Zona?";
                        Swal.fire({
                            title: '¡Atención!',
                            text: mensaje,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí',
                            cancelButtonText: 'No',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                loading(true);
                                sec_jefes_zona_actualiza_jefe();
                            }
                        })
					} else {
                        loading(true);
                        sec_jefes_zona_actualiza_jefe();
                    }
                }
            });            
        }
    });
}

function sec_jefes_zona_actualiza_jefe() {
    $.ajax({
        method: "POST",
        url:"sys/get_jefes_zona.php",
        data: $("#frm_update_zona").serialize(),
        success: function(e){
            loading(false);
            if(e){
                Swal.fire({
                    title: 'Registro actualizado',
                    text: "El Jefe de Zona ha sido actualizado",
                    icon: 'success',
                    confirmButtonText: 'Recargar Página',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                })
            } else{
                Swal.fire(
                    'Error',
                    'El cambio no pudo realizarse',
                    'error'
                )
            }
        }
    });
    return false;
}

function sec_jefes_zona_obtener_id_zona_sub_gerente(id_modal, zona_modal)
{
    im = id_modal;
    zm = zona_modal;
    $("#update_sub_gerente_zona").val(im);
    $("#id_zona_sub_gerente").text(zm);
}

$("#guardar_zona_sub_gerente").on('click', function(e){
    e.preventDefault();
    
    var dataForm = new FormData($("#frm_update_zona_sub_gerente")[0]);
    dataForm.append("accion","guardar_zona_sub_gerente");

    Swal.fire({
        title: '¡Cuidado!',
        text: "¿Está seguro(a) de cambiar el Sub Gerente de Zona?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, ¡cámbialo!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed)
        {
            $.ajax({
                method: "POST",
                url:"sys/get_jefes_zona.php",
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    loading("true");
                },
                complete: function() {
                    loading();
                },
                success: function(e){
                    
                    if(e)
                    {
                        Swal.fire({
                            title: 'Registro actualizado',
                            text: "El Sub Gerente de Zona ha sido actualizado",
                            icon: 'success',
                            confirmButtonText: 'Recargar Página',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        }).then((result) => {
                            if (result.isConfirmed)
                            {
                                location.reload();
                            }
                        })
                    } 
                    else
                    {
                        Swal.fire(
                            'Error',
                            'El cambio no pudo realizarse',
                            'error'
                        )
                    }
                }
            });
            return false;
        }
    });
    

});


