$(document).ready(function () {

    $('#red_id-tls').change(function () {
        var locales = getLocales($(this).val());
        $("#local_id-tls").html("<option value='_all_' selected>Todos (puede demorar)</option>")
        locales.forEach(e => {
            let option = new Option(e.nombre, e.id, false, false)
            $("#local_id-tls").append(option)
        })
    })

    $('#red_id-tls').change();

    $('.btn-get-limites-tls').click(function (e) {
        loading(true);
        var tipo_limite = e.currentTarget.dataset.tipoLimite
        var estados = $('#estado_limite_local-tls').val()
        console.log('estados');
console.log(estados);
        // var tipo_limite = 'local'
        
        
        switch (tipo_limite) {
            case 'local':
                var local_id = $('#local_id-tls').val()
                var red_id = $('#red_id-tls').val()
                var data = {
                    'get_limites_config_saldos_tls': {
                        'item_id': local_id,
                        'tipo_limite': tipo_limite,
                        'red_id': red_id,
                        'estados': estados
                    }
                }
                console.log(data);
                getLimitesLocales(data);

                break;

            default:
                break;
        }


    })

    $('.btn-asignar-limite-global-local-tls').click(function (e) {
        var tipo_limite = e.currentTarget.dataset.tipoLimite
        getLimite(tipo_limite, null).then((data)=> {
            $('#limite-global-local-tls').val(data.limite);
            $('#modal-asignar-limite-global-titulo-tls').html('Editar: ' + data.tipo_descripcion)
        });
        
        $('#modal-tipo-limite-tls').val(tipo_limite)
        $('#monto_limite_global_local-tls').val('')
        $('#modal_asigna_limite_global_tls').modal("show");

    })

    $('.btn-guardar-limite-global-tls').click(function(e){
        var tipo_limite = $('#modal-tipo-limite-tls').val()
        var item_id = null;
        var nombre =  '';
        var limite =  $("#monto_limite_global_local-tls").val();
        var estado =  1;
        console.log(limite);
        guardar_limite(tipo_limite, item_id, nombre, limite, estado)
    })



    $('.btn-guardar-limite-item-tls').click(function(){

        var tipo_limite = $('#modal-tipo-limite-item-tls').val() // 'local';
        var item_id = $('#modal-item-id-tls').val() // local_id o idweb
        var nombre =  $('#modal-item-nombre-tls').val()
        var limite =  $("#monto_limite_item-tls").val();
        var estado =  1;
        console.log(limite);
        guardar_limite(tipo_limite, item_id, nombre, limite, estado)
    })


    $('.btn-historial-limite-global-local-tls').click(function(e){
        var limite_id = e.currentTarget.dataset.limiteId
        var descripcion_tipo_limite = e.currentTarget.dataset.limiteDescripcionTipo
        $('#title_modal_historico_limite_saldo_web-tls').html(descripcion_tipo_limite + ' - Histórico')
        show_modal_get_historio_limite(limite_id)
    })

   

    // $('#opt_tab_local').click();

    $("#nuevo_limite_global_transaccion-tls, #monto_limite_global_local-tls, #monto_limite_item-tls").on({
        "focus": function (event) {
            $(event.target).select();
        },
        "keyup": function (event) {
            $(event.target).val(function (index, value ) {
                return formatMoneda(value)
            });
        }
    });

})

function formatMoneda(number){
   // number = (parseFloat(number)).toFixed(2)
    number = number.replace(/\D/g, '')
                    .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ',');

    return number;
}

function fn_botones(){

    $('.btn-activar-limite-tls').click(function(e){
        var id = e.currentTarget.dataset.id
        var estado = e.currentTarget.dataset.estado
        var new_estado = '';
        var msg_confirm = '';
        var btn_msg_confirm = '';

        switch (estado) {
			case "0":
				new_estado = 1;
				msg_confirm = 'Se activará este límite.'
				btn_msg_confirm = 'Sí, activar';
				break;
			case "1":
				new_estado = 0;
				msg_confirm = 'Se desactivará este límite.'
				btn_msg_confirm = 'Sí, desactivar';
				break;
			default:
				new_estado = 0;
				break;
		}

        swal({
			title: "¿Estás seguro?",
			text: msg_confirm,
			type: "info",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: btn_msg_confirm,
			cancelButtonText: "No, cancelar",
			closeOnConfirm: false,
			closeOnCancel: true,

		},
			function (isConfirm) {

                if (isConfirm) {
					console.log('confirmooo')

					change_estado_limite(id, new_estado);

				} else {
					console.log('nooooo')
				}
			});
    })

    $('.btn-edit-limite-tls').click(function(e){
        var id = e.currentTarget.dataset.id
        var item_id = e.currentTarget.dataset.itemId
        var tipo_limite = e.currentTarget.dataset.tipoLimite
        var nombre_local = e.currentTarget.dataset.nombre
        
        console.log('edit: ' + item_id)

        $('#monto_limite_item-tls').val('')
        $('#modal-item-id-tls').val(item_id)

        getLimite(tipo_limite, item_id)
            .then((data) => {
                console.log(data)
                $('#modal_input_limite_anterior_item-tls').val(data.limite)
            })
            .catch((error) => {
                console.log(error)
            })

        var titulo = '<b>Límite por ' + tipo_limite + ' - ' + nombre_local + '</b>';
        var pregunta = '¿Desea establecer un límite personalizado para este ' + tipo_limite + '?'
        popup_registrar_limite_personalizado(false, titulo, tipo_limite, pregunta);
    })
    
    $('.btn-get-historico-limite-tls').click(function(e){
        var id = e.currentTarget.dataset.id
        var nombre = e.currentTarget.dataset.nombre
        $('#title_modal_historico_limite_saldo_web-tls').html(nombre + ' - Histórico')
        show_modal_get_historio_limite(id)
    })
    
}

function getLimite(tipo, item_id){
    var local_id = $('#local_id-tls').val()
    var estado =  1;

    var get_data = {
        item_id: item_id,
        tipo_limite: tipo,
    };

    return new Promise((resolve, reject) => {
        $.ajax({
            type: "POST",
            url: '/sys/get_adm_saldo_tls.php',
            async: false,
            data: {
                "get_limite": get_data
            },
            success: function (response) {
                console.log(response)
                response = (response);
                if (response.status == 200) {
                    resolve(response.limite)
                } else {
                }

            },
            error: function (error) {
              reject(error)
            },
            dataType: "json"
        });
    })
}

function getLocales(obj) {
    console.log("red_id: " + obj)
    var get_data = {
        red_id: obj
    };

    var locales = {};

    $.ajax({
        type: "POST",
        url: '/sys/get_adm_saldo_tls.php',
        async: false,
        data: {
            "get_locales_config_saldos_web": get_data
        },
        success: function (response) {
            response = (response);
            if (response.error == undefined) {
                locales = response

            } else {
                console.log(response)
            }
        },
        dataType: "json"
    });

    return locales;

}


function getLimitesLocales(data) {

    tabla = $("#table_limites_por_local-tls").dataTable(
        {
            language: {
                "decimal": "",
                "emptyTable": "No existen registros",
                "info": "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered": "(filtered from _MAX_ total entradas)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Filtrar:",
                "zeroRecords": "Sin resultados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }
            },
            "aProcessing": true,
            "aServerSide": true,
            "ajax":
            {
                url: "/sys/get_adm_saldo_tls.php",
                data: data,
                type: "POST",
                dataType: "json",
                beforeSend: function (xhr) {
                    if (1) {
                        // loading(true);

                    } else {
                        loading(false);
                        xhr.abort();
                        swal({
                            title: "Ocurrió un error",
                            text: 'Debe selecionar fechas',
                            type: "error",
                            closeOnConfirm: true
                        });
                    }
                },
                complete: function (r) {
                    
                    loading(false);
                    var result = r.responseJSON
                    console.log(result)
                    if (result.status == 500) {
                        swal({
                            title: "Ocurrió un error",
                            text: result.msg,
                            type: "error",
                            closeOnConfirm: true
                        });

                    } else if (result.status == 200) {
                        //mostrar data
                        // console.log(result)
                    } else if (result.status == 201) {
                        
                       
                        getLimite('local_global', null)
                            .then((data) => {
                                console.log(data)
                                $('#modal_input_limite_anterior_item-tls').val(data.limite)
                            })
                            .catch((error) => {
                                console.log(error)
                            })
                        
                        $('#txt_limite_global_anterior-tls').html('(Límite global)')
                        
                        var nombre_local = $("#local_id-tls option:selected").text();
                        $('#modal-item-id-tls').val($('#local_id-tls').val())
                        $('#modal-item-nombre-tls').val(nombre_local)
                        var titulo = '<h4 class="modal-title" id=""><b>El local ' + nombre_local + ' no tiene un límite personalizado</b></h4>'
                        var pregunta = '¿Desea establecer un límite personalizado para este local?'

                        //mostrar popup para registrar limite del local seleccionado
                        popup_registrar_limite_personalizado(true, titulo, 'local', pregunta);
                    }

                    fn_botones()
                    $('.paginate_button').click(function(){
                        fn_botones()
                    })
                },
                error: function (e) {
                    console.log(e.responseText);
                }
            },
            columnDefs:
                [
                    {
                        className: 'text-center',
                        targets: [0, 1, 2, 3, 4]
                    },
                    {
                        width: "",
                        className: "text-left",
                        targets: 0
                    },
                    {
                        width: "", targets: 1
                    },
                    {
                        width: "100px", targets: 4
                    },
                    { "defaultContent": "-", "targets": "_all" }
                ],
            "columns": [
                { "aaData": "0" },
                { "aaData": "1" },
                { "aaData": "2" },
                { "aaData": "3" },
                { "aaData": "4" },
            ],
            "bDestroy": true,
            order: [[3, 'desc']],
            aLengthMenu: [10, 20, 30, 40, 50, 100]
        }
    ).DataTable();


    tabla.on('search.dt', function() {
        fn_botones();
    });
}

function guardar_limite(tipo_limite, item_id, nombre, limite, estado){

    limite = parseFloat(limite.replace(/\,/g, '')).toFixed(2)
    var get_data = {
        tipo_limite: tipo_limite,
        item_id: item_id,
        nombre: nombre,
        limite: limite,
        estado: estado,
    };


    $.ajax({
        type: "POST",
        url: '/sys/get_adm_saldo_tls.php',
        async: false,
        data: {
            "guardar_limite_saldos_tls": get_data
        },
        success: function (result) {

            if (result.status == 200) {
                swal({
                    title: "OK",
                    text: result.msg,
                    type: "success",
                    closeOnConfirm: true
                }, function(){
                    $('#modal_asigna_limite_personalizado-tls').modal('hide')  
                    $('#modal_asigna_limite_global_tls').modal('hide')  
                    
                    $('#monto_limite_item-tls').val('')
                    if (tipo_limite.includes('local')) {
                        $('#get-limites-local-tls').click();
                        if(tipo_limite == 'local_global'){
                            getLimite(tipo_limite, null).then((data)=> {
                                $('#limite_local_diario_actual-tls').html('S/ ' + data.limite)
                            });
                        }
                    }
                   // m_reload();
                });
            } else if (result.status == 400){
                swal({
                    title: "Error: No se guardó",
                    text: result.msg,
                    type: "warning",
                    closeOnConfirm: true
                }, function(){
                    //m_reload();
                });
            }
        },
        dataType: "json"
    });

    return true;
} 

function popup_registrar_limite_personalizado(nuevo, titulo, tipo_limite, pregunta){
    
    if(nuevo){
    } else {
    }
    
    $('#txttitle_modal_limite_item-tls').html(titulo);
    $('#modal-tipo-limite-item-tls').val(tipo_limite);
    $('#modal-pregunta-titulo-tls').html(pregunta);
    $('#modal_asigna_limite_personalizado-tls').modal('show')  
}

function show_modal_get_historio_limite(limite_id) {

    var get_data = {
        limite_id: limite_id
    };

	$.ajax({
        url: "/sys/get_adm_saldo_tls.php",
        type: "POST",
        data: {
            "get_historico_limite": get_data
        },
        success: function (datos) {
            var respuesta = JSON.parse(datos);
			if (respuesta.status == 200) {
                 
                    $('#modal_historico_limites-tls').modal('show');
                    $('#sec_table_historico_limites-tls').html(respuesta.result);
                 
			}
        },
        error: function () {
            // Manejar el error si es necesario
        }
    });
}

function change_estado_limite(limite_id, new_state){
    var get_data = {
		'limite_id' : limite_id,
        'new_state' : new_state
	}
	
	$.ajax({
		url: "/sys/get_adm_saldo_tls.php",
		type: "POST",
		data: {
            "update_estado_limite": get_data
        },
		success: function (data) {
			var respuesta = JSON.parse(data);
			console.log(respuesta)
			if (respuesta.status == 200) {
				swal("OK", respuesta.message, "success");
				setTimeout(() => {
					window.location.reload();
				}, 2000);

			} else if (respuesta.status == 500) {
				swal("Error", respuesta.message, "warning");
			}
		},
		always: function (data) {
			loading();
		}
	});
}

