
    if ($("#sec_soporte_cliente_web") ) {
        $('#SecSoporte_cliente_web_btn_modificar').on('click', (e) => {
            e.preventDefault();
            $('#SecSoporte_cliente_web_form').submit();
            return false;
        });
    
        $("#SecSoporte_cliente_web_buscar_cliente_id").click(function (e) {
            $("#SecSoporte_cliente_web_form").validate().resetForm();
            fnc_sec_soporte_cliente_web_get_register();
    
        });
    
        $("#SecSoporte_cliente_web_btn_cancelar").click(function (e) { 
            fnc_sec_soporte_cliente_web_clear_form();
        });
    
        
        // $(".validanumericos").keypress(function (e) {
        //     if (String.fromCharCode(e.keyCode).match(/[^0-9]/g)) return false;
        // });
    
        // sec_soporte_cliente_web_setInputFilter(document.getElementById("SecSoporte_cliente_web_cliente_id"), function(value) {
        //     return /^\d*\.?\d*$/.test(value); // Allow digits and '.' only, using a RegExp
        // });

        function sec_soporte_cliente_web_setInputFilter(input){
            let value = input.value;
            let numbers = value.replace(/[^0-9]/g, "");
            input.value = numbers;
          }
    
        // function sec_soporte_cliente_web_setInputFilter(textbox, inputFilter) {
        //     ["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function(event) {
        //       textbox.addEventListener(event, function() {
        //         if (inputFilter(this.value)) {
        //           this.oldValue = this.value;
        //           this.oldSelectionStart = this.selectionStart;
        //           this.oldSelectionEnd = this.selectionEnd;
        //         } else if (this.hasOwnProperty("oldValue")) {
        //           this.value = this.oldValue;
        //           this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
        //         } else {
        //           this.value = "";
        //         }
        //       });
        //     });
        //   }
        $('#SecSoporte_cliente_web_form').validate({
            rules: {
                SecSoporte_cliente_web_numero_documento: {
                    required: true,
                    minlength: 8,
                    maxlength: 20
                    
                },
                SecSoporte_cliente_web_correo: {
                    required: true,
                },
                SecSoporte_cliente_web_telefono: {
                    required: true,
                    minlength: 9,
                }
            },
            messages: {
                SecSoporte_cliente_web_numero_documento: {
                    required: "Ingrese un número de documento",
                    minlength: "Introduzca al menos {0} caracteres",
                    maxlength: "Introduzca como máximo {0} caracteres",
                },
                SecSoporte_cliente_web_correo: {
                    required: "Ingrese un correo electrónico",
                },
                SecSoporte_cliente_web_telefono: {
                    required: "Ingrese un número de telefono",
                    minlength: "Introduzca al menos {0} caracteres",
                }
            },
            submitHandler: function (form) {
                var id_registers = $('#SecSoporte_cliente_web_id_registers').val();
                var numero_doc = $('#SecSoporte_cliente_web_numero_documento').val();
                var correo = $('#SecSoporte_cliente_web_correo').val();
                var telefono = $('#SecSoporte_cliente_web_telefono').val();
        
                var data = {
                    'numero_doc': numero_doc,
                    'correo': correo,
                    'telefono': telefono,
                    'accion': 'modificar_register'
                }
                if (parseInt(id_registers) != 0) {
                    data["id_registers"] = id_registers;
                }
        
                fnc_sec_soporte_cliente_web_get_modificar_register(data);
                return false;
            }
        });
    
    
    }


function fnc_sec_soporte_cliente_web_data() {
    var web_id = $("#SecSoporte_cliente_web_cliente_id").val();
    var data = {
        "web_id": web_id,
        "accion": 'obtener_register'
    }
    return data;
}

function fnc_sec_soporte_cliente_web_get_register() {

    auditoria_send({ "proceso": "at_web_obtener_registers", "data": fnc_sec_soporte_cliente_web_data() });
    $.ajax({
        url: "/sys/get_soporte_cliente_web.php",
        type: 'POST',
        data: fnc_sec_soporte_cliente_web_data(),
        beforeSend: function () {
            loading("true");
            $("#sec_soporte_cliente_web_contenedor").css("display","");  
        },
        complete: function () {
            loading();
        },
        success: function (resp) { //  alert(datat)
            var respuesta = JSON.parse(resp);
           // console.log(respuesta);
            if (parseInt(respuesta.http_code) == 400) {
                return false;
            }
            if (parseInt(respuesta.http_code) == 200) {
                
                $('#SecSoporte_cliente_web_numero_documento').val(respuesta.data.DocNumber);
                $('#SecSoporte_cliente_web_correo').val(respuesta.data.Email);
                $('#SecSoporte_cliente_web_telefono').val(respuesta.data.MobilePhone);
                $('#SecSoporte_cliente_web_id_registers').val(respuesta.data.id);                
                var row = '';
                row = row.concat('<th>' + respuesta.data.ClientId + '</th>');
                row = row.concat('<th>' + respuesta.data.DocNumber + '</th>');
                row = row.concat('<th>' + respuesta.data.FirstName + '</th>');
                row = row.concat('<th>' + respuesta.data.LastName + '</th>');
                row = row.concat('<th>' + respuesta.data.MobilePhone + '</th>');                
                row = row.concat('<th>' + respuesta.data.BirthDate + '</th>');
                row = row.concat('<th>' + respuesta.data.Email + '</th>');
                row = row.concat('<th>' + respuesta.data.Address + '</th>');
                $("#SecSoporte_cliente_web_table_information tbody").html("");
                $("#SecSoporte_cliente_web_table_information tbody").append('<tr>' + row + '</tr>');
                $( "#SecSoporte_cliente_web_btn_modificar" ).removeAttr( "disabled");
                return false;
            }
            if (parseInt(respuesta.http_code) == 204) {
                
                $('#SecSoporte_cliente_web_numero_documento').val('');
                $('#SecSoporte_cliente_web_correo').val('');
                $('#SecSoporte_cliente_web_telefono').val('');
                $('#SecSoporte_cliente_web_id_registers').val('0');                
                var row = '';
                row = row.concat('<th>' + '-' + '</th>');
                row = row.concat('<th>' + '-' + '</th>');
                row = row.concat('<th>' + '-' + '</th>');
                row = row.concat('<th>' + '-' + '</th>');
                row = row.concat('<th>' + '-' + '</th>');                
                row = row.concat('<th>' + '-' + '</th>');
                row = row.concat('<th>' + '-' + '</th>');
                row = row.concat('<th>' + '-' + '</th>');
                $("#SecSoporte_cliente_web_table_information tbody").html("");
                $("#SecSoporte_cliente_web_table_information tbody").append('<tr>' + row + '</tr>');
                swal("No hay registros.",'', "info");
                $("#SecSoporte_cliente_web_btn_modificar" ).prop( "disabled", true );
                return false;
            }
        },
        error: function () { }
    });
}

function fnc_sec_soporte_cliente_web_get_modificar_register(params) {
    swal({
        title: "¿Estás seguro?",
        text: "¡Los datos serán enviados para la modificación del registro!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Sí, enviar",
        cancelButtonText: "No, cancelar",
        closeOnConfirm: false,
        closeOnCancel: false
    },
        function (isConfirm) {
            if (isConfirm) {
                $.ajax({
                    type: "POST",
                    url: '/sys/get_soporte_cliente_web.php',
                    data: params,
                    beforeSend: function () {
                        loading("true");
                    },
                    complete: function () {
                        loading();
                    },
                    success: function (response) {
                        var jsonData = JSON.parse(response);
                        //$("#SecSoporte_cliente_web_id_registers").val(0);
                        if (jsonData.error == false) {
                            swal("Actualizado", jsonData.mensaje, "success");
                            
                        } else {
                            swal("Error", jsonData.mensaje, "error");
                        }
                    }
                    ,
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        var jsonData = JSON.parse(XMLHttpRequest.responseText);
                        swal("Error", jsonData.mensaje, "error");
                    }
                });

            } else {
                swal("Cancelado", "Los datos no se enviaron", "error");
            }
        });
}

function fnc_sec_soporte_cliente_web_clear_form() {
    $("#sec_soporte_cliente_web_contenedor").css("display","none");  
    $("#SecSoporte_cliente_web_table_information tbody").html("");  
    $('#SecSoporte_cliente_web_numero_documento').val('');
    $('#SecSoporte_cliente_web_correo').val('');
    $('#SecSoporte_cliente_web_telefono').val('');
    $('#SecSoporte_cliente_web_id_registers').val(0);
}
