function sec_caja_clientes_depositos_val(){
    if(sec_id=='caja_clientes_depositos_val'){
        sec_caja_clientes_depositos_val_events();
        recargar_tabla=true;
    }
}
function get_turno(){
    datat={get_turno:""};
    $.ajax({
                url: "/sys/get_caja_clientes_depositos.php",
                type: 'POST',
                data: datat,
                success: function (resp) {//  alert(datat)
                    respuesta=JSON.parse(resp);
                    var tiene_turno=respuesta.tiene_turno;
                    if(!tiene_turno){
                            swal({
                                title: "No tiene turno abierto",
                                html: "No tiene turno abierto",
                                type: "warning",
                                timer: 3000,
                                closeOnConfirm: false,
                                confirmButtonText: "Abrir Turno"
                            },
                            function(){
                               window.location='/?sec_id=caja';
                            });
                            }
                },
                error: function () {
                }
            });
}

function sec_caja_clientes_depositos_val_events() {
    var divcontenedor=$("#div_sec_caja_clientes_depositos");
    var modal_foto=$("#fotoModal");

    tablaserver=actualizar_tbl_val();
    $('#btnClean').on('click', function () {
        clean();
    });

    $('#tbl_depositos',divcontenedor).on('click', '.addFoto', function () {
        // deleteImg();
        let data = $(this).data('id');
        let cantidad = $(this).data('cant');
        let tipo = $(this).data('type');


        cargaImgval(data, tipo);
        $('#id_deposito').val(data);
        $('#id_deposito').parent().attr("data-type", tipo);
        var fadein = $(this).hasClass('in');
        if (fadein == false) {
            modal_foto.modal('show');

        } else {
            modal_foto.modal('hide');
            $('#miniatura',modal_foto).html(" ");
        }
        if(tipo==2){
            $(".div_botones").show();

            $(".uploads").show();
            //$("#btn_rechazar").show();
        }else{
            $(".div_botones").hide();
            
            $(".uploads").hide();
           // $("#btn_rechazar").hide();
        }
    });

    modal_foto.on("shown.bs.modal",function(){
        recargar_tabla=false;
    })

    modal_foto.on("hidden.bs.modal",function(){
        $('#miniatura',modal_foto).html(" ");
        recargar_tabla=true;
        $("#imgInp").val('');
        deleteImg();
        $('.formUpload').css('display', 'block');
        $('.uploadInput').removeClass('desactivate');
        $('.uploadInput').removeClass('activate');
    })

    $('#miniatura',modal_foto).on('click', '.mini', function () {
        var src = $(this).attr('src');
        src = src.replace('min_', '');
        $('#previewImg').attr('src', src);
    });

    $('html').bind('keypress', function (e) {
        if (e.keyCode == 13) {
            var focused = $(':focus');
            var index = focused.index("input");
            e.preventDefault();
        }
    });

    $("#btn_rechazar").on("click",function(){
        $("#estado_dep",modal_foto).val(2);//rechaz
        $("#formUpload",modal_foto).submit();
    })

    $(".uploadInput").on("click",function(){
        $("#estado_dep",modal_foto).val(1);//valid
    })

    $("#formUpload",modal_foto).submit(function (e) {
        e.preventDefault();
        //if ($('#imgInp',modal_foto).val() != "" && $('#imgInp',modal_foto).val() != " ") {

            let urlget = "sys/set_caja_clientes_depositos_archivos.php";
            let photoType = $("#formUpload",modal_foto).attr("data-type");
            var dataForm = new FormData(this);

            dataForm.append("sec_registro_depositos_archivos", "sec_registro_depositos_archivos");
            dataForm.append("id_deposito", $("#id_deposito").val());
            dataForm.append("tipo", photoType);
            dataForm.append("estado", $("#estado_dep",modal_foto).val());
            loading(true);
            $.ajax({
                url: urlget,
                type: 'POST',
                data: dataForm,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    swal({
                        title: "Registro Exitoso",
                        text: "",
                        type: "success",
                        timer: 500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: false
                    });
                    $('#fotoModal').modal("hide");
                    loading(false);
                    deleteImg();
                    recarga_tabla();
                }
            });
        //}
    });

    $('#imgInp',modal_foto).on('change', function (e) {
        var files = e.target.files;
        var filesLength = files.length;
        // deleteImg();
        let conteo = $(this).data('cant').replace('{count}', filesLength);
        $('#leyenda').html('');
        $('#leyenda').html(conteo);
        $('.uploadInput').attr('disabled', false);

        if (filesLength > 0) {
            $('.uploadInput').addClass('activate');
        } else {
            $('.uploadInput').addClass('desactivate');
        }

        if (filesLength <= 4) {

            for (var i = 0; i < filesLength; i++) {
                var f = files[i];
                var fileReader = new FileReader();
                fileReader.onload = (function (e) {
                    var file = e.target;
                    $('#previewImg').attr('src', e.target.result);

                    $("<img></img>", {
                        class: "mini",
                        src: e.target.result,
                        title: file.name
                    }).insertAfter("#minions");

                });
                fileReader.readAsDataURL(f);
            }
        }
    });

    $('.consultar').on('click', function () {
            var telefono=$("#txt_telefono",divcontenedor).val();
            if(telefono==""){
                    swal({
                        title: "Ingrese Teléfono",
                        text: "",
                        type: "warning",
                        timer: 500,
                        closeOnConfirm: false,
                        showCancelButton: false,
                        showConfirmButton: false
                    },function(){
                        swal.close();
                        $("#txt_telefono").focus();
                    });         
            }else{
                $("#tbl_cliente tbody",divcontenedor).html("");
                consultar_cliente(telefono);
            }
    });

}


function actualizar_tbl_val() {

    var tabla= $("#tbl_depositos",$("#div_sec_caja_clientes_depositos"));
    tablaserver=tabla
                        .on('order.dt', function () {
                            $('table').css('width', '100%');
                            $('.dataTables_scrollHeadInner').css('width', '100%');
                            $('.dataTables_scrollFootInner').css('width', '100%');
                        })
                        .on('search.dt', function () {
                                $('table').css('width', '100%');
                                $('.dataTables_scrollHeadInner').css('width', '100%');
                                $('.dataTables_scrollFootInner').css('width', '100%');
                            })
                        .on('page.dt', function () {
                                $('table').css('width', '100%');
                                $('.dataTables_scrollHeadInner').css('width', '100%');
                                $('.dataTables_scrollFootInner').css('width', '100%');
                            })
            .DataTable({
                "paging": true,
                "scrollX": true,
                "sScrollX": "100%",
                "bProcessing": true,
                'processing': true,
                "language": {
                    "search": "Buscar:",
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "No se encontraron registros",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "No hay registros",
                    "infoFiltered": "(filtrado de _MAX_ total records)",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    sProcessing: ""
                },

                "bDeferRender": false,
               "autoWidth": true,
               pageResize:true,
                "bAutoWidth": true,
                "pageLength": 10,
                serverSide: true,
                "bDestroy": true,
                colReorder: true,
                "lengthMenu": [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
                "order": [[ 1, "desc" ]],
                "columnDefs":[],
                //searchDelay:1000,
                sDom:"<'row'<'col-sm-12'B>><'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
                 //sDom: 'lfrtip',
               buttons: [
                    /*{
                        text: '<span class="glyphicon glyphicon-refresh"></span>',
                        action: function ( e, dt, node, config ) {
                            tablaserver.ajax.reload(null,false);
                        }
                    }*/
                ],
                ajax: function (datat, callback, settings) {////AJAX DE CONSULTA
                    datat.sec_depositos_list=true;
                    ajaxrepitiendo = $.ajax({
                        global: false,
                        url: "/sys/get_caja_clientes_depositos.php",
                        type: 'POST',
                        data: datat,
                        beforeSend: function () {
                            $(".dataTables_processing").hide();
                        },
                        complete: function () {
                        },
                        success: function (datos) {
                            var respuesta = JSON.parse(datos);
                            callback(respuesta);
                        },
                        error: function () {
                        }
                    });
                },
                columns: [
                {data:"id",nombre:"id",title:"Id"},
                {data:"created_at",nombre:"created_at",title:"Fecha"},
                {data:"usuario",nombre:"usuario",title:"Usuario"},
                {data:"cliente",nombre:"cliente",title:"Cliente"},
                {data:"telefono",nombre:"telefono",title:"Teléfono"},
                {data:"estado",nombre:"estado",title:"Estado"},
                {data:"fotos",nombre:"archivo1",title:"Voucher"
                 ,"render": function (data, type, row ) {
                        var dep_id=row["id"];
                        var cant = data;   
                        var clase_btn=cant>0?'primary':'danger';       
                        var html="<button class='btn btn-rounded btn-"+clase_btn+" btn-xs addFoto' data-id=" +dep_id+" data-type =1  data-toggle='tooltip' title='Cargar Imagen de Voucher'><i class='fa fa-camera' aria-hidden='true'></i> (" +cant+ ") </button>";
                        return html;
                    }
                 },
                {data:"fotos2",nombre:"archivo2",title:"Foto"
                   ,"render": function (data, type, row ) {
                        var dep_id=row["id"];
                        var cant = data;   
                        var clase_btn=cant>0?'primary':'danger';       
                        var html="<button class='btn btn-rounded btn-"+clase_btn+" btn-xs addFoto' data-id=" +dep_id+" data-type =2  data-toggle='tooltip' title='Cargar Imagen'><i class='fa fa-camera' aria-hidden='true'></i> (" +cant+ ") </button>";
                        return html;
                    }
            },
                {data:"archivos",nombre:"archivo3",title:"Ticket"
                 ,"render": function (data, type, row ) {
                        var dep_id=row["id"];
                        var cant = data;   
                        var clase_btn=cant>0?'primary':'danger';       
                        var html="<button class='btn btn-rounded btn-"+clase_btn+" btn-xs addFoto' data-id=" +dep_id+" data-type =3  data-toggle='tooltip' title='Cargar Archivo'><i class='fa fa-camera' aria-hidden='true'></i> (" +cant+ ") </button>";
                        return html;
                    }
                },
   
                {data:"validador",nombre:"validador",title:"Validador"},
                {data:"update_user_at",nombre:"update_user_at",title:"Fecha Val."},

                ],

                "initComplete": function (settings, json) {
                    recarga_tabla();
                 /*   setTimeout(function(){
                        $("#incidencias_recargar").off("click").on("click",function(){
                            tablaserver.ajax.reload(null, false);
                        })
                                tablaserver.columns.adjust();
                    },100)*/
          
                }

            });
        return tablaserver;
    };

    function recarga_tabla(){
        if(typeof intervalotabla!=="undefined"){
            clearInterval(intervalotabla);
        }
        tablaserver.ajax.reload(null,false);
        intervalotabla = setInterval(
                                    function () {
                                        if(recargar_tabla){
                                            tablaserver.ajax.reload(null, false);///  1er parametro=> callback al terminar carga  ;2nd parametro=> mantiene paginación
                                        }
                                    }, 4000);
    }

    function clean() {
        $('#formCliente').find('input[type="text"]').each(function () {
            $(this).val('');
        });
        $("#tbl_cliente tbody").html("");
        $('#txt_telefono').focus();
    }


    function cargaImgval(id, type) {
        var data = {};
        data.id = id;
        data.tipo=type;
        data.update = true;
        var ubicacion="../files_bucket/depositos/";
        $.post("sys/set_caja_clientes_depositos_archivos.php", {"get_archivos": data}, function (data) {
            loading();
             result = JSON.parse(data);
            let i = result.length;
            var modal_foto=$("#fotoModal");
            for (let x = 0; x < i; x++) {
                if (typeof (result[x]['archivo']) != 'undefined') {
                    //$('#miniatura').html(" ");
                    nombre_archivo=result[x]['archivo'];
                    ext=nombre_archivo.substring(nombre_archivo.indexOf(".")+1);
                    if(ext=="pdf"){
                        $('#miniatura',modal_foto).append("<a class='mini' target='_blank' href='"+ubicacion + result[x]['archivo'] + "'>"+result[x]['archivo']+"</a><hr>");

                    }else{
                        $('#miniatura',modal_foto).append("<img class='mini' src='"+ubicacion+"min_" + result[x]['archivo'] + "' />");
                    }

                }
            }

            if (typeof (result[0]) != 'undefined') {
                $('#previewImg',modal_foto).attr('src', ubicacion + result[0]['archivo']);
            }
        });
    };


    function deleteImg() {
        var modal=$("#fotoModal");
        var modal_validar=$("#fotoModal2");
        let src = 'images/default_avatar.png';
        $('#miniatura',modal).find('img').remove();
        $('#miniatura',modal_validar).find('img').remove();
        $('#previewImg',modal).attr('src', src);
        $('#previewImg',modal_validar).attr('src', src);
        let html = "<i class='fa fa-picture-o' aria-hidden='true'></i> <span id='leyenda'>Elegir imágenes</span>";
        $('#labelbtn').html('');
        $('.labelbtn').html(html);
    }




