function sec_recaudacion_pagos_manuales() {
    sec_recaudacion_pagos_manuales_events();

    $(window).resize(function () {
        var wit = $(window).width();
        if (wit > 1376) {
            $('.descrip').css('width', '471.8px');
            $('#table-pagos_manuales').css('width', '1832px');
            if ($('body').hasClass('expanded')) {
                $('.descrip').css('width', '326.8px');
                $('#table-pagos_manuales').css('width', '1687px');
            }
        } else {
            $('.descrip').css('width', '166px');
            $('#table-pagos_manuales').css('width', '1278px');
            if ($('body').hasClass('expanded')) {
                $('.descrip').css('width', '166.14px');
                $('#table-pagos_manuales').css('width', '1133px');
            }
        }
    });
}


function sec_recaudacion_pagos_manuales_events() {
    $(window).scroll(function () {
        $('.nose').scrollLeft(function () {
        });
    });

    // $('#btn_cargar_plantilla').on('click', function() {
    //     console.log("error");
    //     var $errorCell = $('#table_import_pagos_manuales').find('td.bg-pg-error').first();
    //     console.log($errorCell);
    //     if ($errorCell.length) {
    //       $('html, body').animate({
    //         scrollTop: $errorCell.offset().top
    //       }, 500);
    //       $errorCell.focus();
    //     }
    // });

    

    $('#txtPagosStartDate').datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: 'now',
        onSelect: function () {
            loading(true);
            list_pagos_manuales(0);
        }
    });

    $('#txtPagosEndDate').datepicker({
        dateFormat: 'yy-mm-dd',
        maxDate: 'now',
        onSelect: function (dateText, inst) {
            loading(true);
            list_pagos_manuales(0);
        }
    });

    $('#btnClearPagosDate').on('click', function (event) {
        event.preventDefault();

        $('#txtPagosStartDate').val("");
        $('#txtPagosEndDate').val("");

        loading(true);
        list_pagos_manuales(0);
    });

    $('.sidebar-collapse').on('click', function () {
        tablaColapse();
    });

    tablaColapse();

    function tablaColapse() {
        var resolucion = $(window).width();
        var reso;
        if (resolucion > 769) {
            reso = ((resolucion * 29) / 100);
        } else {
            reso = 200;
        }
        var tamDes;
        var datos = {}

        if ($('body').hasClass('expanded')) {
            $("#table-pagos_manuales").width($(window).width() - 235);
            $(".descrip").css('width', reso - 230);
            tamDes = reso - 230;
        } else {
            $('#contendMsj').hide();
            if (resolucion > 769) {
                $("#table-pagos_manuales").width($(window).width() - 90);
                $(".descrip").css('width', reso - 85);
                tamDes = reso - 85;
            } else {
                $("#table-pagos_manuales").width($(window).width() - 90);
                $(".descrip").css('width', reso);
                tamDes = reso;
            }
        }
        var tamtab = $("#table-pagos_manuales").width();
        datos = {"resolucion": resolucion, "porcentaje": reso, "tamtabla": tamtab, "tamDesc": tamDes};
        // console.log(datos);
    }

    //list_pagos_manuales(0);

    $("#table-pagos_manuales").fixMe({
        "footer": false,
        "marginTop": 50,
        "zIndex": 1,
        "bgColor": "white",
        "bgHeaderColor": "white"
    });

    $(".recaudacion_add_pago_manual_btn")
        .off()
        .click(function (event) {
            recaudacion_add_pago_manual_modal("show");
        });
    // $(".recaudacion_add_pago_manual_btn").click();
    $("body")
        .off("#table-pagos_manuales .btn_edit")
        .on("click", "#table-pagos_manuales .btn_edit", function (event) {
            var id = $(this).closest('.tr_pm').data("id");
            recaudacion_edit_pago_manual_modal(id);
        });
    // $("#table-pagos_manuales .btn_edit").first().click();
    $("body")
        .off("#table-pagos_manuales .btn_remove")
        .on("click", "#table-pagos_manuales .btn_remove", function (event) {
            var id = $(this).closest('.tr_pm').data("id");

            
            swal({
                title: "¿Esta seguro de eliminar el pago manual?",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si',
                cancelButtonText: 'No',
                closeOnConfirm: false,
                closeOnCancel: true
            },function(isConfirm){
                if (isConfirm) {
                    recaudacion_delete_pago_manual(id);
                }
            });
           
        });

    $("#btnExportPagoManual")
        .off()
        .on('click', function (event) {
            event.preventDefault();

            var get_data = {};
            get_data.start_date = $('#txtPagosStartDate').val();
            get_data.end_date = $('#txtPagosEndDate').val();

            loading(true);
            $.ajax({
                url: '/export/recaudacion_pagos_manuales.php',
                type: 'post',
                data: get_data
            })
                .done(function (dataresponse) {
                    var obj = JSON.parse(dataresponse);
                    window.open(obj.path);
                    loading();
                })
                .fail(function (e) {

                });
        });

    $("#recaudacion_importar_modal").on("hidden.bs.modal", function () {
        $("input[name='archivo']").val('');
    })

    $("#btnImportPagoManual").off("click").on("click", function () {
        $("#recaudacion_importar_modal").modal("show");
    })

    $("#btn_importar")
        .off("click")
        .on('click', function (event) {
            event.preventDefault();
            var dataForm = new FormData($("#form_import")[0]);
            dataForm.append("importar_archivo", "importar_archivo");
            $.ajax({
                url: '/import/recaudacion_pagos_manuales.php',
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    var obj = JSON.parse(response);
                    loading();
                    swal({
                        title: obj.swal_title + "<br>",
                        html: true,
                        text: "<div><strong>" + obj.msg + "</strong></div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>" + obj.msg_error + "<div>",
                        type: "success",
                        closeOnConfirm: true
                    }, function () {
                        m_reload();
                        swal.close();
                    });
                },
                beforeSend: function () {
                    loading(true);
                },
                complete: function () {
                    loading(false);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    swal({
                        title: errorThrown,
                        html: true,
                        text: jqXHR.responseText,
                        type: textStatus,
                        closeOnConfirm: true
                    }, function () {
                        $("input[name='archivo']").val('');
                        swal.close();
                    })
                }
            })
        });

    $("#btn_descargar_plantilla")
        .off("click")
        .on('click', function (event) {
            event.preventDefault();
            let dataForm = new FormData();
            dataForm.append("descargar_plantilla", "descargar_plantilla");
            $.ajax({
                url: '/import/recaudacion_pagos_manuales.php',
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    loading(false);
                    let result = JSON.parse(response);
                    if (result) {
                        let $a = $("<a>");
                        let file = result.file;
                        $a.attr("href", file);
                        $('body').append($a);
                        let file_name = result.file_name;
                        if (!file_name) {
                            file_name = 'plantilla_pagos_manuales.xls';
                        }
                        $a.attr("download", file_name);
                        $a[0].click();
                        $a.remove();
                    }
                },
                beforeSend: function () {
                    loading(true);
                },
                complete: function () {
                    loading(false);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    loading(false);
                    swal({
                        title: errorThrown,
                        html: true,
                        text: jqXHR.responseText,
                        type: textStatus,
                        closeOnConfirm: true
                    }, function () {
                        $("input[name='archivo']").val('');
                        swal.close();
                    })
                }
            })
        });

    // -----------

    if ($("#table-pagos_manuales").length) {
        list_pagos_manuales(0);
    }

    $("#txtPagosFilter").on("keyup", function () {
        list_pagos_manuales(0);
    });

    $('#cbPagosLimit').on('change', function (event) {
        loading(true);
        list_pagos_manuales(0);
    });
}


function list_pagos_manuales(page) {
    var get_data = {};
    var limit = $("#cbPagosLimit option:selected").val();
    var filter = $("#txtPagosFilter").val();

    if (limit <= 0) {
        limit = 10;
    }

    get_data.page = page;
    get_data.limit = limit;
    get_data.filter = filter;
    get_data.start_date = $('#txtPagosStartDate').val();
    get_data.end_date = $('#txtPagosEndDate').val();

    $.post('sys/get_recaudacion_pagos_manuales_list.php', {"get_pagos": get_data}, function (response) {
        try {
            let result = JSON.parse(response);
            $("#table-pagos_manuales > tbody").html(result.body);
            $("#paginationPagosMensuales").pagination({
                items: result.num_rows / limit,
                currentPage: page + 1,
                itemsOnpage: limit,
                cssStyle: 'light-theme',
                onPageClick: function (pageNumber, event) {
                    event.preventDefault();
                    list_pagos_manuales(pageNumber - 1);
                }
            });
        } catch (Error) {

        }
        loading();
    });
}


function recaudacion_add_pago_manual_modal(opt) {
    $("#recaudacion_add_pago_manual_modal").modal(opt);
    if (opt == "show") {
        $("#recaudacion_add_pago_manual_modal .set_data[name=tipo_id]")
            .off()
            .change(function (event) {
                $(".set_data[name='cdv_id']").val(16).change();
                // recaudacion_add_pago_manual_modal_show_fields();
                // var tipo = $(this).val();
                // $(".show_field input, .show_field select").val("");
                // if(tipo=="recargaweb"){

                // }
            });
        $("#recaudacion_add_pago_manual_modal .cerrar_btn")
            .off()
            .click(function (event) {
                recaudacion_add_pago_manual_modal("hide");
            });
        $("#recaudacion_add_pago_manual_modal .add_btn")
            .off()
            .click(function (event) {
                recaudacion_add_pago_manual();
            });
        // $("#recaudacion_add_pago_manual_modal .add_btn").click();

        recaudacion_add_pago_manual_modal_show_fields();
        $("#recaudacion_add_pago_manual_modal .select2")
            .select2({
                witdh: "100%"
            });
        $("#recaudacion_add_pago_manual_modal .set_data[name=monto]").focus();
        
        $(".pm_datepicker").datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true,
            beforeShowDay: function(date) {
                var day = date.getDay();
                // Habilitar solo los días miércoles (3) y jueves (4)
                if (day === 3 || day === 4) {
                    return [true, ""];
                } else {
                    return [false, "", "Día no disponible"];
                }
            }
        }).on("change", function (ev) {
            $(this).datepicker('hide');
            var newDate = $(this).datepicker("getDate");
            $("input[data-real-date=" + $(this).attr("id") + "]").val($.datepicker.formatDate("yy-mm-dd", newDate));
        });
    }
}


function recaudacion_add_pago_manual_modal_show_fields() {
    $(".set_data[name='at_unique_id']").val("new").change();
    $(".set_data[name='tipo_id']").val(1).change();
    $(".set_data[name='cdv_id']").val(16).change();
    $(".set_data[name='monto']").val("").change().focus();
    var newDate = Date();
    $(".set_data[name='fecha']").val($.format.date(newDate, "yyyy-MM-dd")).change();
    $("#input_text-add_pm_created").val($.format.date(newDate, "dd-MM-yyyy")).change();
    $(".set_data[name='local_id']").val(1).change();
    $(".set_data[name='motivo_id']").val(0).change();
    $(".set_data[name='autoriza_id']").val(0).change();
    $(".set_data[name='referencia']").val("").change();
    $(".set_data[name='descripcion']").val("").change();
}

function recaudacion_add_pago_manual() {
    var set_data = {};
    // set_data.values = {};
    $("#recaudacion_add_pago_manual_modal .set_data").each(function (index, el) {
        var col = $(el).attr("name");
        var val = $(el).val();
        set_data[col] = val;
    });

    var fecha = $(".set_data[name=fecha]").val();
    var dia = new Date(fecha).getDay();
    if (!(dia == "2" || dia == "3")) {  // Verificar si el día es miércoles (2) o viernes (3)
        swal({
            title: "Error!",
            text: "La fecha ingresada debe ser un miercoles o jueves",
            type: "warning",
            timer: 5000,
            closeOnConfirm: true
        },
        function () {
            swal.close();
        });
        return false;
    } 
    
    if ($(".set_data[name=monto]").val() == "" || $(".set_data[name=monto]").val() == 0) {
        swal({
                title: "Error!",
                text: "Ingresar monto.",
                type: "warning",
                timer: 5000,
                closeOnConfirm: true
            },
            function () {
                swal.close();
            });
        return false;
    }

    //si el canal de venta es bingo
    if (set_data['cdv_id'] === "30") {
        auditoria_send({"proceso": "add_pago_manual", "data": set_data});
        $.post('/sys/set_recaudacion_pagos_manuales.php', {
            "add_pago_manual_bingo": set_data
        }, function (r) {
            loading();
            if (r == "no_pm") {
                swal({
                        title: "Error!",
                        text: "No se guardó el pago manual.",
                        type: "warning",
                        timer: 3000,
                        closeOnConfirm: true
                    },
                    function () {
                        swal.close();
                    });
            } else {
                swal({
                        title: "Guardado",
                        text: "",
                        type: "success",
                        timer: 800,
                        closeOnConfirm: true
                    },
                    function () {
                        // auditoria_send({"proceso":"save_item","data":save_data});
                        loading(true);
                        m_reload();
                        swal.close();
                    });
            }
        });
    } else //si el canal de venta es hipica
    if (set_data['cdv_id'] === "34") {
        auditoria_send({"proceso": "add_pago_manual", "data": set_data});
        $.post('/sys/set_recaudacion_pagos_manuales.php', {
            "add_pago_manual_hipica": set_data
        }, function (r) {
            loading();
            if (r == "no_pm") {
                swal({
                        title: "Error!",
                        text: "No se guardó el pago manual.",
                        type: "warning",
                        timer: 3000,
                        closeOnConfirm: true
                    },
                    function () {
                        swal.close();
                    });
            } else {
                swal({
                        title: "Guardado",
                        text: "",
                        type: "success",
                        timer: 800,
                        closeOnConfirm: true
                    },
                    function () {
                        // auditoria_send({"proceso":"save_item","data":save_data});
                        loading(true);
                        m_reload();
                        swal.close();
                    });
            }
        });
    }else {
        //si canal de venta es diferente a el canal de venta
        $("#recaudacion_add_pago_manual_modal").modal("hide");

        auditoria_send({"proceso": "add_pago_manual", "data": set_data});
        $.post('/sys/set_recaudacion_pagos_manuales.php', {
            "add_pago_manual": set_data
        }, function (r) {
            loading();
            if (r == "no_pm") {
                swal({
                        title: "Error!",
                        text: "No se guardó el pago manual.",
                        type: "warning",
                        timer: 3000,
                        closeOnConfirm: true
                    },
                    function () {
                        // $("#abrir_turno_modal .input_text[name='"+name+"']").addClass("bg-danger",500, function(){ $("#abrir_turno_modal .input_text[name='"+name+"']").removeClass("bg-danger",1500,false); } );
                        // custom_highlight($("#abrir_turno_modal .input_text[name='"+name+"']"));
                        swal.close();
                    });
            } else {
                swal({
                        title: "Guardado",
                        text: "",
                        type: "success",
                        timer: 800,
                        closeOnConfirm: true
                    },
                    function () {
                        // auditoria_send({"proceso":"save_item","data":save_data});
                        loading(true);
                        m_reload();
                        swal.close();
                    });
            }
        });
    }
}

function recaudacion_edit_pago_manual_modal(id) {
    if (id) {
        loading(true);
        var get_data = {};
        get_data.id = id;

        $("#recaudacion_add_pago_manual_modal").modal("show");

        $.post('/sys/get_recaudacion_pagos_manuales.php', {
            "get_pago_manual": get_data// $command.=" ON DUPLICATE KEY UPDATE ";
            // $uqn=0;
            // foreach ($pm as $key => $value) {
            // 	if($uqn>0) { $command.=", "; }
            // 	$command.= $key." = ".$value."";
            // 	$uqn++;
            // }
        }, function (r) {
            try {
                var obj = jQuery.parseJSON(r);
                $.each(obj, function (index, val) {
                    (index == 'canal_de_venta_id') ? (index = 'cdv_id') : (index);
                    $("#recaudacion_add_pago_manual_modal .set_data[name='" + index + "']").val(val).change();
                });
                $("#input_text-add_pm_created").val(obj.fecha_pago_datepicker);
                $("#input_text-add_pm_created").datepicker({
                    dateFormat: 'dd-mm-yy',
                    changeMonth: true,
                    changeYear: true,
                    beforeShowDay: function(date) {
                        var day = date.getDay();
                        // Habilitar solo los días miércoles (3) y jueves (4)
                        if (day === 3 || day === 4) {
                            return [true, ""];
                        } else {
                            return [false, "", "Día no disponible"];
                        }
                    }
                }).on("change", function (ev) {
                    $(this).datepicker('hide');
                    var newDate = $(this).datepicker("getDate");
                    $("input[data-real-date=" + $(this).attr("id") + "]").val($.datepicker.formatDate("yy-mm-dd", newDate));
                });

                // $("#input_text-add_pm_created").datepicker("setDate","10/12/2012");
            } catch (err) {
            }
            loading();
        });
        $("#recaudacion_add_pago_manual_modal .cerrar_btn")
            .off()
            .click(function (event) {
                recaudacion_edit_pago_manual_modal();
            });
        $("#recaudacion_add_pago_manual_modal .add_btn")
            .off()
            .click(function (event) {
                recaudacion_add_pago_manual();
            });

    } else {
        recaudacion_add_pago_manual_modal_show_fields();
        $("#recaudacion_add_pago_manual_modal").modal("hide");
    }
}

function recaudacion_delete_pago_manual(id) {
	var data = {
		"delete_pago_manual": "delete_pago_manual",
		"id_pago_manual": id,
	}
	auditoria_send({ "proceso": "delete_pago_manual", "data": data });
	$.ajax({
		url: "/sys/set_recaudacion_pagos_manuales.php",
		type: 'POST',
		data: data,
		beforeSend: function() {
			loading("true");
		},
		complete: function() {
			loading();
		},
		success: function(resp) {
			var respuesta = JSON.parse(resp);
			auditoria_send({ "proceso": "delete_pago_manual", "data": respuesta });
			
			if (parseInt(respuesta.status) == 200) {
				swal('Aviso', respuesta.message, 'success');
				setTimeout(() => {
                    m_reload();
                }, 2000);
			}else{
                swal('Aviso', respuesta.message, 'warning');
                return false;
                
            }
		},
		error: function() {}
	});
}

function fncGetFileSecPagosManuales() {
	var formData = new FormData();
    $('#btn_cargar_plantilla').hide();
	formData.append('previsualizar_plantilla', 'previsualizar_plantilla');
	var file = document.getElementById('file-input-pago-manuales').files.length;
	if (file > 0) {
        var archivo_pago_manual = document.getElementById('file-input-pago-manuales').files[0];
        if (archivo_pago_manual) {
            formData.append("archivo", archivo_pago_manual);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(archivo_pago_manual);
            document.getElementById('archivo-pago-manuales').files = dataTransfer.files;
        }
		document.getElementById('file-input-pago-manuales').value = "";

		$.ajax({
			type: "POST",
			data: formData,
			url: 'import/recaudacion_pagos_manuales.php',
			contentType: false,
			processData: false,
			cache: false,
            beforeSend: function( xhr ) {
                loading(true);
            },
            complete: function(){
                loading(false);
            },
			success: function (response) {
				var jsonData = JSON.parse(response);
				if (jsonData.status == 200) {
                    fnc_pago_manual_previsualizar_plantilla(jsonData.result);
                    $('#btn_cargar_plantilla').show();
                }else{
                    $('#btn_cargar_plantilla').hide();
                    $("#table_import_pagos_manuales").DataTable().destroy();
                    $('#table_import_pagos_manuales tbody').empty();
                    document.getElementById('file-input-pago-manuales').value = "";
                    document.getElementById('archivo-pago-manuales').value = "";
                    swal({
                        title: "",
                        html: true,
                        text: "<div><strong>"+jsonData.title+"</strong></div><div style='overflow:auto;max-height:220px;text-align:left;padding-left:3px'>" + jsonData.msg + "<div>",
                        type: "error",
                        closeOnConfirm: true
                    }, function () {
                       
                    })
                    
                }

			}
		});
	}
}

function fnc_pago_manual_previsualizar_plantilla(data) {
    $("#table_import_pagos_manuales").DataTable().destroy();
    $('#table_import_pagos_manuales tbody').empty();
    var table = $("#table_import_pagos_manuales")
    .dataTable({
    bDestroy: true,
    data: data,
    responsive: true,
    order: [[0, 'asc']],
    pageLength: 50,
    columns: [
        { data: "index", className: "text-center" },
        { data: "tipo", className: "text-center" },
        { data: "motivo", className: "text-left" }, 
        { data: "referencia", className: "text-left" }, 
        { data: "descripcion", className: "text-left" },
        { data: "fecha_pago", className: "text-left" },
        { data: "monto", className: "text-center" },
        { data: "cdv", className: "text-center" },
        { data: "local", className: "text-center" },
        { data: "autoriza", className: "text-center" },
    ],
    createdRow: function(row, data, dataIndex) {
        
        $('td', row).eq(0).addClass('bg-pg-success');
        
        if (data.errors.tipo) {
            $('td', row).eq(1).addClass('bg-pg-error');
        }else{
            $('td', row).eq(1).addClass('bg-pg-success');
        }
        if (data.errors.motivo) {
            $('td', row).eq(2).addClass('bg-pg-error');
        }else{
            $('td', row).eq(2).addClass('bg-pg-success');
        }
        if (data.errors.referencia) {
            $('td', row).eq(3).addClass('bg-pg-error');
        }else{
            $('td', row).eq(3).addClass('bg-pg-success');
        }
        if (data.errors.descripcion) {
            $('td', row).eq(4).addClass('bg-pg-error');
        }else{
            $('td', row).eq(4).addClass('bg-pg-success');
        }
        if (data.errors.fecha_pago) {
            $('td', row).eq(5).addClass('bg-pg-error');
        }else{
            $('td', row).eq(5).addClass('bg-pg-success');
        }
        if (data.errors.monto) {
            $('td', row).eq(6).addClass('bg-pg-error');
        }else{
            $('td', row).eq(6).addClass('bg-pg-success');
        }
        if (data.errors.cdv) {
            $('td', row).eq(7).addClass('bg-pg-error');
        }else{
            $('td', row).eq(7).addClass('bg-pg-success');
        }
        if (data.errors.local) {
            $('td', row).eq(8).addClass('bg-pg-error');
        }else{
            $('td', row).eq(8).addClass('bg-pg-success');
        }
        if (data.errors.autoriza) {
            $('td', row).eq(9).addClass('bg-pg-error');
        }else{
            $('td', row).eq(9).addClass('bg-pg-success');
        }
        },
    language: {
        decimal: "",
        emptyTable: "Tabla vacia",
        info: "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
        infoEmpty: "Mostrando 0 a 0 de 0 entradas",
        infoFiltered: "(filtered from _MAX_ total entradas)",
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
        }
        
    },
    scrollY: true,
    scrollX: true,
    dom: 'Bfrtip',
    buttons: [
        'pageLength',
    ],
    })
    .DataTable();
}

function fnc_pago_manual_importar_archivo() {
    /// VALIDAR QUE TODO LA DATA ESTE CORRECTO ANTES DE IMPORTAR
    var table_pm = $('#table_import_pagos_manuales').DataTable();
    var $errorCells = table_pm.cells('.bg-pg-error').nodes();
    if ($errorCells.length) {
        var errorCell = $errorCells[0];
        var errorCellIndex = table_pm.cell(errorCell).index();
        var errorRowIndex = errorCellIndex.row;

        // Change to the page that contains the error cell
        var page = Math.floor(errorRowIndex / table_pm.page.info().length);
        table_pm.page(page).draw(false);

        // After changing the page, focus the cell
        setTimeout(function() {
        var $errorCell = $(table_pm.cell(errorRowIndex, errorCellIndex.column).node());
        $('html, body').animate({
            scrollTop: $errorCell.offset().top
        }, 500);
        $errorCell.focus();
        }, 0);
        swal({
            title: "",
            html: true,
            text: "<div><strong>El archivo subido cuenta con algunas observaciones</strong></div><br></div><div style='overflow:auto;max-height:220px;text-align:center;padding-left:3px'>Revisar lo que esta en color rojo en el archivo subido y corrregirlo.<div>",
            type: "error",
            closeOnConfirm: true
        })

        return false;
    }


    var dataForm = new FormData($("#form_import")[0]);
    dataForm.append("importar_archivo", "importar_archivo");
    $.ajax({
        url: '/import/recaudacion_pagos_manuales.php',
        type: 'POST',
        data: dataForm,
        cache: false,
        contentType: false,
        processData: false,
        success: function (response) {
            var obj = JSON.parse(response);
            if (obj.status == 200) {
                swal({
                    title: obj.title + "<br>",
                    html: true,
                    text: "<div><strong>" + obj.msg + "</strong></div>",
                    type: "success",
                    closeOnConfirm: true
                }, function () {
                    m_reload();
                    swal.close();
                });
            }else{
                swal({
                    title: obj.title + "<br>",
                    html: true,
                    text: "<div><strong>" + obj.msg + "</strong></div>",
                    type: "error",
                    closeOnConfirm: true
                }, function () {
                    swal.close();
                });
            }
           
        },
        beforeSend: function () {
            loading(true);
        },
        complete: function () {
            loading(false);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            swal({
                title: errorThrown,
                html: true,
                text: jqXHR.responseText,
                type: textStatus,
                closeOnConfirm: true
            }, function () {
                swal.close();
            })
        }
    })
}