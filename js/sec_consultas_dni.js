function sec_consultas_dni(){
	// console.log("sec_consultas_dni");
	localStorage.removeItem("last_dni")
	$("#btnDNISearch").on('click', function(event) {
		event.preventDefault();
		// console.log("#btnDNISearch.click");
		sec_consultas_dni_get($('#txtDNINumber').val());
		$('#txtDNINumber').get(0).focus();
	});
	$('#txtDNINumber').on('keypress',function(e) {
		if(e.which == 13) {
			e.preventDefault();
			// console.log("#txtDNINumber.keypress:13");
			sec_consultas_dni_get($('#txtDNINumber').val());
		}
	});

	//UPLOAD
		$('#btnDNIUpload').off('click').on('click', function(event) {
			// console.log("#btnDNIUpload.click");
			event.preventDefault();
			$('#fileDNIUpload').click();
		});
		$('#fileDNIUpload').off('change').on('change', function(event) {
			// console.log("#fileDNIUpload.change");
			event.preventDefault();
			$('#formDNI').submit();
			$('#fileDNIUpload').val('');
		});
		$('#formDNI').on('submit', function(event) {
			event.preventDefault();
			// console.log("#formDNI.submit");
			var formData = new FormData(this);
			sec_consultas_dni_upload(formData);
		});
	//END UPLOAD
}
function sec_consultas_dni_get(dni){
	// console.log("sec_consultas_dni_get:"+dni);
	loading(true);
	localStorage.removeItem("last_dni")
	if(localStorage.getItem("last_dni") == dni){
		// sleep(1);
		setTimeout(function(){ 
			loading();
		}, 300);
		// console.log("sec_consultas_dni_get_same_last:"+dni);
	}else{
		localStorage.setItem("last_dni",dni);
		data = {};
		data.dni = dni;
		auditoria_send({"proceso":"consultas_dni_show_dni","data":data});
		$.post('sys/get_consultas.php', {"show_dni":data}, function(r) {
			try{
				response = JSON.parse(r);
				sec_consultas_dni_process_response(response);
			}catch(err){
				// console.log(err);
				// console.log(r);
			}
			loading();
		});
	}
}
function sec_consultas_dni_upload(formData) {
	// console.log("sec_consultas_dni_upload");
	// console.log(formData);
	loading(true);
	localStorage.removeItem("last_dni")
	auditoria_send({"proceso":"consultas_dni_show_massive","data":""});
	$.ajax({
		url: "sys/get_consultas.php",
		type: "POST",
		data: formData,
		cache: false,
		contentType: false,
		processData:false,
		success: function(r) {
			try{
				response = JSON.parse(r);
				sec_consultas_dni_process_response(response);
			}catch(err){
				// console.log(err);
				// console.log(r);
			}
			loading();
		}
	});
}

function sec_consultas_dni_process_response(response){
	if(response.code == 200){
		$('#dniContent').html(response.message);
	}else{
		swal("Alerta", response.message, "warning");
	}
}

function  sec_consultas_dni_change_log(user_id) {
	 
	$('#modalConsultaChangeLog').modal('show');
	$('#modal_title_consulta_historico').html((user_id + ' - Historial de Cambios').toUpperCase());
	sec_consultas_change_log_datatable(user_id);
}
 
function sec_consultas_get(user_id) {
    $("#sec_consulta_modal_guardar_titulo").text("Editar");

    let data = {
			user_id : user_id,
            accion:'consulta_user_get'
        }

    $.ajax({
            url:  "/sys/get_consultas.php",
            type: "POST",
            data:  data,
            beforeSend: function () {
            loading("true");
            },
            complete: function () {
            loading();
            },
            success: function (resp) {
            var respuesta = JSON.parse(resp);
            if (respuesta.status == 200) {	

                $('#form_modal_sec_consulta_dni_param_id').val(respuesta.result.id);
                $('#form_modal_sec_consulta_dni_param_dni').val(respuesta.result.dni);
				$('#form_modal_sec_consulta_dni_param_nombres').val(respuesta.result.nombre);
                $('#form_modal_sec_consulta_dni_param_apellido_paterno').val(respuesta.result.apellido_paterno);
                $('#form_modal_sec_consulta_dni_param_apellido_materno').val(respuesta.result.apellido_materno);

            	$("#sec_consulta_modal_editar").modal("show");
                }
            else
                {
                swal({
                        title: 'Error',
                        text: respuesta.message,
                        html:true,
                        type: "warning",
                        closeOnConfirm: false,
                        showCancelButton: false
                    });
    
                    return false;
                }    
            },
            error: function (resp, status) {},
            });
    }

 function sec_consultas_change_log_datatable(user_id) {
    if (sec_id == "consultas" && sub_sec_id == "dni") {
        
        var data = {
            accion: "consulta_change_log",
            user_id:user_id
            };
        $("#consulta_change_log_div_tabla").show();
        
        $('#consulta_change_log_div_tabla tfoot th').each(function () {
            var title = $(this).text();
            $(this).html('<input type="text" style="width:100%;" placeholder="Buscar ' + title + '" />');
            });
        
        tabla = $("#consulta_change_log_datatable").dataTable({
                    language: {
                    decimal: "",
                    emptyTable: "No existen registros",
                    info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
                    infoEmpty: "Mostrando 0 a 0 de 0 entradas",
                    infoFiltered: "",
                    infoPostFix: "",
                    thousands: ",",
                    lengthMenu: "Mostrar _MENU_ entradas",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    search: "Filtrar:",
                    zeroRecords: "Sin resultados",
                    paginate: {
                        first: "Primero",
                        last: "Ultimo",
                        next: "Siguiente",
                        previous: "Anterior",
                        },
                    aria: {
                    sortAscending: ": activate to sort column ascending",
                    sortDescending: ": activate to sort column descending",
                        },
                    buttons: {
                        pageLength: {
                                _: "Mostrar %d Resultados",
                                '-1': "Tout afficher"
                            }
                        },
                        
                        },
                    scrollY: true,
                    scrollX: true,
                    dom: 'Bfrtip',
                    buttons: [
                            'pageLength',
                        ],
                    aProcessing: true,
                    aServerSide: true,
                    ajax: {
                        url: "/sys/get_consultas.php",
                        data: data,
                        type: "POST",
                        dataType: "json",
                        error: function(e) {
                        },
                    },
                    createdRow: function(row, data, dataIndex) {
                        if (data[0] === 'error') {
                            $('td:eq(0)', row).attr('colspan', 6);
                            $('td:eq(0)', row).attr('align', 'center');
                            $('td:eq(0)', row).addClass('text-center');
                            $('td:eq(1)', row).addClass('text-center');
                            $('td:eq(2)', row).addClass('text-center');
                            $('td:eq(3)', row).addClass('text-center');
                            $('td:eq(4)', row).addClass('text-center');
                            $('td:eq(5)', row).addClass('text-center');
                            this.api().cell($('td:eq(0)', row)).data(data[1]);
                        }
                    },
                    columnDefs: [{
                        className: "text-center",
                        targets: "_all"
                    }],
                    bDestroy: true,
                    aLengthMenu: [10, 20, 30, 40, 50, 100],
                    initComplete: function () {
                        // Ocultar la barra de búsqueda
                        $('.dataTables_filter').css('display', 'none');
                    },
                }).DataTable();
            }
    }

$("#sec_consulta_modal_editar .btn_guardar").off("click").on("click",function(){

    
        var param_nombre = $("#form_modal_sec_consulta_dni_param_nombres").val();
        var param_apellido_paterno = $("#form_modal_sec_consulta_dni_param_apellido_paterno").val();
		var param_apellido_materno = $("#form_modal_sec_consulta_dni_param_apellido_materno").val();

        if (param_nombre.length ==0) {
            alertify.error('Ingrese el nombre', 5);
            $("#form_modal_sec_consulta_dni_param_nombres").focus();
            return false;
        }else if (param_nombre.length <=2) {
            alertify.error('Ingrese 3 caracteres como minimo en el nombre', 5);
            $("#form_modal_sec_consulta_dni_param_nombres").focus();
            return false;
        }

		if (param_apellido_paterno.length == 0) {
            alertify.error('Ingrese el apellido paterno', 5);
            $("#form_modal_sec_consulta_dni_param_apellido_paterno").focus();
            return false;
        }else if (param_apellido_paterno.length <=2) {
            alertify.error('Ingrese 3 caracteres como minimo para el apellido paterno', 5);
            $("#form_modal_sec_consulta_dni_param_apellido_paterno").focus();
            return false;
        }


		if (param_apellido_materno.length ==0) {
            alertify.error('Ingrese el apellido materno', 5);
            $("#form_modal_sec_consulta_dni_param_apellido_materno").focus();
            return false;
        }else if (param_apellido_materno.length <=2) {
            alertify.error('Ingrese 3 caracteres como minimo para el apellido materno', 5);
            $("#form_modal_sec_consulta_dni_param_apellido_materno").focus();
            return false;
        }

        

    sec_consulta_user_save();     

    })

	function sec_consulta_user_save(){
        var id = $('#form_modal_sec_consulta_dni_param_id').val();
        var dni = $('#form_modal_sec_consulta_dni_param_dni').val();

        //// HISTORICO DE CAMBIOS - DATOS DE FORMA DE PAGO  //////////////////////////////////////////////////////////////////
        var nombres = $("#form_modal_sec_consulta_dni_param_nombres").val().trim();
        var apellido_paterno = $("#form_modal_sec_consulta_dni_param_apellido_paterno").val().trim();
        var apellido_materno = $('#form_modal_sec_consulta_dni_param_apellido_materno').val().trim();

        if(id == 0)
        {
            // CREAR
            aviso = "¿Está seguro de registrar el cliente?";
            titulo = "Registrar";
        }
        else
        {
            // EDITAR
            aviso = "¿Está seguro de editar el cliente?";
            titulo = "Editar";
        }
        
        swal({
                    title: titulo,
                    text: aviso,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#529D73",
                    confirmButtonText: "SI",
                    cancelButtonText: "NO",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
            function (isConfirm) {
                if(isConfirm){
                    var dataForm = new FormData($("#Frm_UsuarioDni")[0]);
                    dataForm.append("accion", "consultas_dni_user_save");
                    dataForm.append("id", id);
                    dataForm.append("nombres", nombres);
                    dataForm.append("apellido_paterno", apellido_paterno);
                    dataForm.append("apellido_materno", apellido_materno);
       
                    //auditoria_send({ "respuesta": "comprobante_guardar", "data": dataForm });
                    loading("true");

                    $.ajax({
                    url: "sys/set_consultas_dni.php",
                    type: 'POST',
                    data: dataForm,
                    cache: false,
                    contentType: false,
                    processData:false,
                    beforeSend: function() {
                        loading("true");
                        },
                    complete: function() {
                        loading();
                           },
                    success: function(resp) {
                        loading("false");
                        var respuesta = JSON.parse(resp);
                        if (parseInt(respuesta.http_code) == 400) {
                            swal({
                                title: respuesta.titulo,
                                text: respuesta.descripcion,
                                html:true,
                                type: "warning",
                                closeOnConfirm: false,
                                showCancelButton: false
                                });
                            return false;
                        }
            
                        if (parseInt(respuesta.http_code) == 200) {
                            swal({
                                title: respuesta.titulo,
                                text:  respuesta.descripcion,
                                html:true,
                                type: "success",
                                closeOnConfirm: false,
                                showCancelButton: false
                                });
                                //$('#Frm_UsuarioDni')[0].reset();
                                //$("#form_modal_sec_consulta_dni_param_id").val(0);
                                $("#sec_consulta_modal_editar").modal("hide");
                                var dni = $('#form_modal_sec_consulta_dni_param_dni').val();
                                sec_consultas_dni_get(dni);
                                return false;
                            }      
                        },
                    error: function() {}
                        });
                }else{
                        alertify.error('No se guardaron los cambios',5);
                        return false;
                }
            });
    }