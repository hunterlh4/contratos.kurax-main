function sec_caja_clientes_depositos(){
    if(sec_id=='caja_clientes_depositos'){
        get_turno();
        sec_caja_clientes_depositos_events();
        recargar_tabla=true;

    }


}

function get_turno(){
    datat={get_turno:""};
    $.ajax({
                url: "/sys/get_caja_clientes_depositos.php",
                type: 'POST',
                data: datat,
                success: function (resp) {
                    respuesta=JSON.parse(resp);
                    var tiene_turno=respuesta.tiene_turno;
                    if(!tiene_turno){
                            swal({
                                title: "No tiene turno abierto",
                                html: "No tiene turno abierto",
                                type: "warning",
                                timer: 5000,
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

function sec_caja_clientes_depositos_events() {
    var divcontenedor=$("#div_sec_caja_clientes_depositos");
    var modal_foto=$("#fotoModal");

    tablaserver = actualizar_tbl();

    $('#btnClean').on('click', function () {
        clean();
    });

     $('#txt_telefono').bind('keypress', function (e) {
        if (e.keyCode == 13) {
            if($(".showSweetAlert").length==0){
                $(".consultar").click();
                e.preventDefault();    
            }
            
        }
    });


     $('#tbl_depositos',divcontenedor).on("click",'.validador_botones',function(){
        let deposito_id = $(this).data('id');
        let estado = $(this).data('estado');

        var save_data = {};
        save_data.item_id = item_id;
        save_data.deposito_id=deposito_id;
        save_data.estado=estado;//

        if(estado==2){
            swal({
                title: "Está seguro que desea rechazar la Solicitud "+deposito_id+"?",
                text: "",
                html: true,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Si",
                cancelButtonText:"No",
                closeOnConfirm: false,
                closeOnCancel: false
            },
            function(opt){
                if(opt){
                    $.post('/sys/get_caja_clientes_depositos.php', {
                            "sec_caja_clientes_depositos_estado": save_data
                        }, function(r) {
                            var respuesta=JSON.parse(r);
                            save_data.response = respuesta;
                            save_data.curr_login=respuesta.curr_login;
                            auditoria_send({"proceso":"sec_solicitud_deposito_rechazar","data":save_data});
                            swal({
                                title: respuesta.mensaje,
                                text: respuesta.mensaje,
                                type: "success",
                                timer: 5500,
                                closeOnConfirm: true
                            },
                            function(){
                                recarga_tabla();
                                swal.close();
                            });
                        });
                    swal.close();

                }else{
                    swal.close();
                }
            });
        }else{

            $.post('/sys/get_caja_clientes_depositos.php', {
                "sec_caja_clientes_depositos_estado": save_data
            }, function(r) {
                save_data.response = r;
                var respuesta=JSON.parse(r);
                auditoria_send({"proceso":"sec_solicitud_deposito_validar","data":save_data});
                swal({
                    title: respuesta.mensaje,
                    text: respuesta.mensaje,
                    type: "success",
                    timer: 4500,
                    closeOnConfirm: true
                },
                function(){
                    recarga_tabla();
                    swal.close();
                });
            });


        }

     })

    $('#tbl_depositos',divcontenedor).on('click', '.addFoto', function () {
        let data = $(this).data('id');
        let cantidad = $(this).data('cant');
        let tipo = $(this).data('type');//tipo foto
        let estado_solicitud=$(this).data('estado');
        let btn_imgmsg=$(this).data('btnimg');
        //let estado_solicitud=$(this).data('estado_solicitud');
        if(tipo==3){
            if(estado_solicitud=="Pendiente" || estado_solicitud=="Rechazado"){
                 swal({
                        title:"Solicitud "+data+ " "+ estado_solicitud ,
                        text: "",
                        type: "warning",
                        timer: 1500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        showConfirmButton: true
                    });
                return false;
            }else{
                $(".uploads").show();

            }

        }
        if(tipo==1){modal_foto=$("#fotoModal");}
        if(tipo==2){modal_foto=$("#comprobantevalidacionModal");}
        if(tipo==3){modal_foto=$("#transaccionModal");}

       if(tipo==1){
            if(estado_solicitud=="Validado" || estado_solicitud=="Rechazado"){
                $(".uploads",modal_foto).hide();
                $(".uploadInput",modal_foto).hide();

            }
            else{
                $(".uploads",modal_foto).show();
            }   
        }
        
        if(tipo==2){
            if(estado_solicitud=="Pendiente"){
                swal({
                        title:"Solicitud "+data+ " "+ estado_solicitud ,
                        text: "",
                        type: "warning",
                        timer: 1500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        showConfirmButton: true
                    });
                return false;

                /*$(".uploads",modal_foto).show();
                $(".uploadInput",modal_foto).show();
                $("#btn_rechazar",modal_foto).show();        */
            }
            if(estado_solicitud=="Validado"){
                $(".uploads").show();
                $(".uploadInput").hide();
                $("#btn_rechazar",modal_foto).hide();

                //$("#btn_rechazar",modal_foto).show();
            }
            if(estado_solicitud=="Rechazado"){
                swal({
                        title:"Solicitud "+data+ " "+ estado_solicitud ,
                        text: "",
                        type: "warning",
                        timer: 1500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        showConfirmButton: true
                    });
                return false;
                /*$(".uploads").show();
                $(".uploadInput").show();
                $("#btn_rechazar",modal_foto).hide();  */
            }
        }

        $(".modal_titulo",modal_foto).text("Solicitud de Validación de Depósito "+ data + " "+ estado_solicitud);
        $("#leyenda",modal_foto).text(btn_imgmsg);
        cargaImg(data, tipo,modal_foto);
        $('#id_deposito',modal_foto).val(data);
        $('#id_deposito'.modal_foto).parent().attr("data-type", tipo);
        $('#id_deposito',modal_foto).parent().attr("data-estadosolicitud", estado_solicitud);        
        modal_foto.modal('show');
    });
    $("#fotoModal,#comprobantevalidacionModal,#transaccionModal").each(function(i,e){
        modal_foto=$(e);
        modal_foto.on("shown.bs.modal",function(){
            recargar_tabla=false;
        })

        modal_foto.on("hidden.bs.modal",function(){
            $('#miniatura',modal_foto).html(" ");
            recargar_tabla=true;
            $("#imgInp",modal_foto).val('');
            deleteImgModal(modal_foto);
            $('.formUpload',modal_foto).css('display', 'block');
            $('.uploadInput',modal_foto).removeClass('desactivate');
        })

        $('#miniatura',modal_foto).on('click', '.mini', function () {
            var src = $(this).attr('src');
            src = src.replace('min_', '');
            $('#previewImg',modal_foto).attr('src', src);
        });

       $("#btn_rechazar",modal_foto).on("click",function(){
            $("#estado_dep",modal_foto).val(2);//rechaz
            $("#formUpload",modal_foto).submit();
        })
        $(".uploadInput",modal_foto).on("click",function(){
            $("#estado_dep",modal_foto).val(1);//valid
        })


        $("#formUpload",modal_foto).submit(function (e) {
            e.preventDefault();
            if (($('#imgInp',modal_foto).val() != "" && $('#imgInp',modal_foto).val() != " ") || $("#estado_dep",modal_foto).val()==2 
                || modal_foto.attr("id")=="comprobantevalidacionModal") {

                let urlget = "sys/set_caja_clientes_depositos_archivos.php";
                let photoType = $("#formUpload",modal_foto).attr("data-type");
                var dataForm = new FormData(this);

                dataForm.append("sec_registro_depositos_archivos", "sec_registro_depositos_archivos");
                dataForm.append("id_deposito", $("#id_deposito",modal_foto).val());
                dataForm.append("tipo", photoType);


                result = {};
                for (var entry of dataForm.entries())
                {
                    result[entry[0]] = entry[1];
                }
                result["files[]"]=result["files[]"].name;
                var set_data = {};
                set_data=result;

                loading(true);
                $.ajax({
                    url: urlget,
                    type: 'POST',
                    data: dataForm,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        resp=JSON.parse(data);
                        set_data.curr_login=resp.curr_login;
                        auditoria_send({"proceso":"sec_registro_depositos_archivos","data":set_data});

                        swal({
                            title:resp.mensaje,// "Registro Exitoso",
                            text: "",
                            type: "success",
                            timer: 4000,
                            closeOnConfirm: true,
                            showCancelButton: false,
                            showConfirmButton: true
                        });
                        $(modal_foto).modal("hide");
                        loading(false);
                        deleteImgModal(modal_foto);
                        recarga_tabla();
                    }
                });
            }
        });

        $('#imgInp',modal_foto).on('change', function (e) {
            var files = e.target.files;
            var filesLength = files.length;
            let conteo = $(this).data('cant').replace('{count}', filesLength);
            $('#leyenda',modal_foto).html(conteo);
            $('.uploadInput',modal_foto).attr('disabled', false);
            if (filesLength > 0) {
                $('.uploadInput',modal_foto).addClass('activate');
            } else {
                $('.uploadInput',modal_foto).addClass('desactivate');
            }

            if (filesLength <= 4) {
                for (var i = 0; i < filesLength; i++) {
                    var f = files[i];
                    var fileReader = new FileReader();
                    fileReader.onload = (function (e) {
                        var file = e.target;
                        $('#previewImg',modal_foto).attr('src', e.target.result);
                        $("<img></img>", {
                            class: "mini",
                            src: e.target.result,
                            title: file.name
                        }).insertAfter($("#minions",modal_foto));

                    });
                    fileReader.readAsDataURL(f);
                }
                var tipo_modal= $(this).closest("form").data("type");
                var estado_solicitud= $(this).closest("form").attr("data-estadosolicitud");
                if(tipo_modal!=2){
                    $('.uploadInput',modal_foto).trigger('click');
                }
                else{
                    if(estado_solicitud=="Validado"){
                        $('.uploadInput',modal_foto).trigger('click');
                    }
                }
            }
        });
    })



    $('.consultar').on('click', function () {
            var telefono=$("#txt_telefono",divcontenedor).val();
            if(telefono==""){
                    swal({
                        title: "Ingrese Teléfono",
                        text: "",
                        type: "warning",
                        timer: 2500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        showConfirmButton: true
                    },function(){
                        swal.close();
                        $("#txt_telefono").focus();
                    });         
            }else{
                $("#tbl_cliente tbody",divcontenedor).html("");
                consultar_cliente(telefono.trim());
            }
    });
    $('#btn_guardar',divcontenedor).on('click', function () {
            if($("#id",divcontenedor).length==0){
                    swal({
                        title: "Ingrese Número",
                        text: "",
                        type: "warning",
                        timer: 3500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        showConfirmButton: true
                    },function(){
                        swal.close();
                        setTimeout(function(){$("#txt_telefono").focus();  },600) ;
                    });
                    return;

            }else{

                let nro_telefono=$("#telefono",divcontenedor);
                let monto=$("#monto",divcontenedor);
                let imagen_voucher=$("#imagen_voucher",divcontenedor);
                let num_doc = $("#num_doc", divcontenedor);
                let tipo_doc = $("#tipo_doc", divcontenedor);

                if(nro_telefono.val()=="" || (nro_telefono.val()).length!=9){
                    swal({
                        title: "Ingrese Nro Teléfono - 9 dígitos",
                        text: "",
                        type: "warning",
                        timer: 2500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        onClose: function(){
                            monto.focus();
                        } ,
                        showConfirmButton: true
                        },function(opt){
                            if(opt){
                                swal.close();
                                setTimeout(function(){nro_telefono.focus();  },600) ;
                            }
                            swal.close();
                            monto.focus();
                        });
                    return false;

                }

                if (num_doc.val().length < 8 || isNaN(num_doc.val())){
                    swal({
                        title: "Ingrese un documento válido",
                        text: "",
                        type: "warning",
                        timer: 2500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        onClose: function(){
                            monto.focus();
                        } ,
                        showConfirmButton: true
                    },function(opt){
                        if(opt){
                            swal.close();
                            setTimeout(function(){monto.focus();  },600) ;
                        }
                        swal.close();
                        monto.focus();
                    });
                    return false;
                }

                if(monto.val()==""){
                    swal({
                        title: "Ingrese Monto",
                        text: "",
                        type: "warning",
                        timer: 2500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        onClose: function(){
                            monto.focus();
                        } ,
                        showConfirmButton: true
                        },function(opt){
                            if(opt){
                                swal.close();
                                setTimeout(function(){monto.focus();  },600) ;
                            }
                            swal.close();
                            monto.focus();
                        });
                    return false;

                }
                 if(imagen_voucher.val()==""){
                         swal({
                        title: "Ingrese Imagen Voucher",
                        text: "",
                        type: "warning",
                        timer: 2500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        showConfirmButton: true
                        },function(opt){
                             if(opt){
                                swal.close();
                                setTimeout(function(){imagen_voucher.focus();  },600) ;
                            }
                            swal.close();
                            imagen_voucher.focus();
                        }); 
                    return false;
                }


                swal({
                    title: "Agregar Solicitud?",
                    text: "",
                    html: true,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Aceptar",
                    cancelButtonColor: "#DD6B55",
                    cancelButtonText:"Cancelar",

                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function(opt){
                    if(opt){
                        var formData = new FormData($("#formCliente")[0]);
                        swal.close();
                        guardar_caja_cliente_deposito(formData);
                    }else{
                        swal.close();
                    }
                });

              //  var formData = new FormData($("#formCliente")[0]);
                //guardar_caja_cliente_deposito(formData);
                //clean();
            }             
    });

    $(document).on('keypress',function(e) {
        if(e.which == 13) {
            if ($("#monto").is(":focus")){
                $("#btn_guardar").click();
            }

            if ($(".txt-save-bet-id").is(":focus")){
                let id = $(".txt-save-bet-id:focus").data("id");
                $(`.btn-save-bet-id[data-id='${id}']`).click();
            }
        }
    });

    $(document).on('click', '.btn-save-bet-id', function(){
        let id = $(this).data("id")
        let bet_id = $(`.txt-save-bet-id[data-id='${id}']`).val()
        save_bet_id(id, bet_id)
    })

    $(document).on('focusin', '.txt-save-bet-id', function(){
        recargar_tabla = false;
    })

    $(document).on('focusout', '.txt-save-bet-id', function(){
        recargar_tabla = true;
    })

}

function save_bet_id(deposito_id, bet_id){
    let data = {
        save_bet_id: true,
        deposito_id: deposito_id,
        bet_id
    }

    let set_data = {
        data
    }

    loading(true)
    $.ajax({
        url: "/sys/get_caja_clientes_depositos.php",
        type: 'POST',
        data: data,
        success: function (resp) {
            loading(false)
            respuesta = JSON.parse(resp);
            if(respuesta.error==true){
                set_data.error = respuesta.error;
                set_data.error_msg = respuesta.error_msg;
                auditoria_send({"proceso":"save_bet_id_caja_cliente_deposito","data":set_data});
                setTimeout(function(){
                    swal({
                            title: respuesta.mensaje,
                            text: "",
                            type: "warning",
                            timer: 4500,
                            closeOnConfirm: true,
                            showCancelButton: false,
                            showConfirmButton: true
                        },function(){
                            swal.close();
                        }
                    );
                    return false;

                },1000);
            }
            else{
                set_data.curr_login = respuesta.curr_login;
                auditoria_send({"proceso":"save_bet_id_caja_cliente_deposito","data":set_data});

                recarga_tabla();
                swal({
                        title: respuesta.mensaje,
                        text: "",
                        type: "success",
                        timer: 4500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        showConfirmButton: true
                    },function(){
                        swal.close();
                        setTimeout(function(){$("#txt_telefono").focus() ;},700);

                    }
                );
                clean();
            }


        },
        error: function () {
        }
    });
}

function consultar_cliente(tel) {
    datat={get_caja_cliente:tel};

    var set_data = {};
    set_data=datat;

    $.ajax({
                url: "/sys/get_caja_clientes_depositos.php",
                type: 'POST',
                data: datat,
                beforeSend: function () {
                },
                complete: function () {
                },
                success: function (resp) {//  alert(datat)
                    respuesta=JSON.parse(resp);
                    objeto=respuesta.cliente;
                    set_data.curr_login=respuesta.curr_login;
                    auditoria_send({"proceso":"save_caja_cliente_deposito","data":set_data});

                    if(objeto==false){
                        swal({
                            title: respuesta.mensaje,
                            text: "",
                            type: "warning",
                            timer: 2500,
                            closeOnConfirm: true,
                            showCancelButton: false,
                            showConfirmButton: true
                        },function(){
                            swal.close();
                        });
                        return false;
                    }
                    cuenta_tipo=respuesta.cuenta_tipo;
                    var tabla_cliente=$("#tbl_cliente tbody");
                    tabla_cliente.append(
                        $("<tr>")
                        .append($("<td>").append($("<input type='text' name='telefono' id='telefono' oninput=\"this.value=this.value.replace(/[^0-9]/g,'');\" maxlength=9>") ) .append($("<input type='hidden' name='id' id='id'>") )  )
                        .append($("<td>")
                            .append($("<select name='tipo_doc' id='tipo_doc'>")
                                .append($("<option value='0'>").append("DNI"))
                                .append($("<option value='1'>").append("CE/PTP"))
                                .append($("<option value='2'>").append("PASAPORTE"))
                            )
                        )
                        .append($("<td>").append($("<input type='text' name='num_doc' id='num_doc'>") ) )
                    )

                    tabla_cliente.append(
                        $("<tr>")
                            .append($("<td class='bg-primary text-bold'>").append("NOMBRE"))
                            .append($("<td class='bg-primary text-bold'>").append("APELLIDO PATERNO"))
                            .append($("<td class='bg-primary text-bold'>").append("APELLIDO MATERNO"))
                    )

                    tabla_cliente.append(
                        $("<tr>")
                        .append($("<td>").append($("<input type='text' name='nombre' id='nombre'>") ) )
                        .append($("<td>").append($("<input type='text' name='apellido_paterno' id='apellido_paterno'>") ) )
                        .append($("<td>").append($("<input type='text' name='apellido_materno'  id='apellido_materno'>") ) )
                    )

                    tabla_cliente.append(
                        $("<tr>")
                        .append($("<td colspan='3'>").append($("<input name='imagen_voucher' type='file' id='imagen_voucher'  accept='.jpeg,.png,.jpg'>") ) )
                    )

                    tabla_cliente.append(
                        $("<tr>")
                        .append($("<td>").append($("<select  name='cuenta_tipo' id='cuenta_tipo' class='form-control input-sm'><option value=''>Seleccione Tipo</option></select>") ) )
                        .append($("<td>").append($("<select  name='cuenta_id' id='cuenta_id' class='form-control input-sm'><option  value=''>Seleccione Cuenta</option></select>") ) )
                        .append($("<td>").append($("<input type='number' class='form-control text-right' name='monto' id='monto' placeholder='Ingrese Monto'> ") ) )
                    )

                    if(objeto){
                        $.each(objeto,function(nom,val){
                            if($("#"+nom,tabla_cliente).length>0){
                                $("#"+nom,tabla_cliente).val(val);
                            }
                        })
                    }
                    else{     
                        $("#telefono",tabla_cliente).val(tel);
                    }
                    $("#telefono").focus();

                    if(cuenta_tipo){
                        $.each(cuenta_tipo,function(nom,val){
                            $("#cuenta_tipo",tabla_cliente).append($("<option value="+val.id+">"+val.nombre+"</option>") )
                        })

                    }
                    $("#cuenta_tipo").off().on("change",function(){
                        var cuenta_tipo_id=$(this).val();
                        if(cuenta_tipo_id==""){
                            $("#cuenta_id",tabla_cliente).empty();
                            $("#cuenta_id",tabla_cliente).append($("<option value=''>Seleccione Cuenta</option>") )

                            return false

                        }
                        $.ajax({
                            url: "/sys/get_caja_clientes_depositos.php",
                            type: 'POST',
                            data: {get_cuenta:cuenta_tipo_id},
                            success: function (resp) {//  alert(datat)
                               respuesta=JSON.parse(resp);                       
                               if(respuesta.cuentas){
                                        $("#cuenta_id",tabla_cliente).empty();
                                        $("#cuenta_id",tabla_cliente).append($("<option value=''>Seleccione Cuenta</option>") )

                                    $.each(respuesta.cuentas,function(nom,val){
                                        $("#cuenta_id",tabla_cliente).append($("<option value="+val.id+">"+val.nombre+"</option>") )
                                    })

                                }   
                            },
                            error: function () {
                            }
                        });
                    });
                },
                error: function () {
                }
    });
}

function guardar_caja_cliente_deposito(cliente) {
    cliente.append("save_caja_cliente_deposito",1);
    //datat={save_caja_cliente_deposito:cliente};
    datat=cliente;

    result = {};
    for (var entry of cliente.entries())
    {
        result[entry[0]] = entry[1];
    }
    result.imagen_voucher=result.imagen_voucher.name;

    var set_data = {};
    set_data=result;
    $.ajax({
            url: "/sys/get_caja_clientes_depositos.php",
            type: 'POST',
            data: datat,
            contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
            processData: false, // NEEDED, DON'T OMIT THIS
            success: function (resp) {//  alert(datat)
                respuesta=JSON.parse(resp);
                if(respuesta.error==true){
                    
                    set_data.error = respuesta.error;
                    set_data.error_msg = respuesta.error_msg;
                    auditoria_send({"proceso":"save_caja_cliente_deposito","data":set_data});
                    setTimeout(function(){
                        swal({
                            title: respuesta.mensaje,
                            text: "",
                            type: "warning",
                            timer: 4500,
                            closeOnConfirm: true,
                            showCancelButton: false,
                            showConfirmButton: true
                        },function(){
                            swal.close();
                            }
                        );
                        return false;

                    },1000);
       
                }
                else{
                    set_data.curr_login = respuesta.curr_login;
                    auditoria_send({"proceso":"save_caja_cliente_deposito","data":set_data});

                    //$("#estado_solicitud").val("Validado").change();        
                    recarga_tabla();
                    swal({
                        title: respuesta.mensaje,
                        text: "",
                        type: "success",
                        timer: 4500,
                        closeOnConfirm: true,
                        showCancelButton: false,
                        showConfirmButton: true
                    },function(){
                        swal.close();
                        setTimeout(function(){$("#txt_telefono").focus() ;},700);

                        }
                    );
                    clean();                  
                }
  
              
            },
            error: function () {
            }
    });

}

function actualizar_tbl() {

    var tabla= $("#tbl_depositos",$("#div_sec_caja_clientes_depositos"));


    permiso_validar=$("#permiso_validar").length==0?false:true;


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
                "bDestroy": false,
                colReorder: true,
                "lengthMenu": [[10, 50, 200, -1], [10, 50, 200, "Todo"]],
                "order": [[ 1, "desc" ]],
                "columnDefs":[],
                //searchDelay:1000,
                sDom:"<'row'<'col-sm-4'l><'col-sm-4 div_select_estado'><'col-sm-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
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
                    datat.cajero=true;
                    datat.estado_solicitud=$("#estado_solicitud").val()||"";
                    ajaxrepitiendo = $.ajax({
                        global: false,
                        url: "/sys/get_caja_clientes_depositos.php",
                        type: 'POST',
                        data: datat,
                        beforeSend: function () {
                             $(".dataTables_processing").hide();
                        },
                        complete: function () {
                                tablaserver.columns.adjust();

                        },
                        success: function (datos) {
                            var respuesta = JSON.parse(datos);
                            /*if(respuesta.curr_login){
                                set_data={};
                                set_data.estado=respuesta.estado;
                                set_data.curr_login=respuesta.curr_login;
                                auditoria_send({"proceso":"get_caja_clientes_depositos","data":set_data});
                            }*/
                            callback(respuesta);
                        },
                        error: function () {
                        }
                    });
                },
                columns: [
                {data:"id",nombre:"id",title:"Id"},
                {data:"created_at",nombre:"created_at",title:"Fecha"},
                {data:"cliente",nombre:"cliente",title:"Cliente"},
                {data:"bet_id", nombre:"bet_id", title:"Id Apuesta"
                    ,"render": function(data, type, row){
                        if (data){
                            return data;
                        } else {
                            let html =
                                `
                                    <div class="input-group">
                                        <input type="text" class="form-control txt-save-bet-id" data-id="${row["id"]}">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default btn-deposito-sm btn-save-bet-id" aria-label="Save" data-id="${row["id"]}">
                                                <span class="glyphicon glyphicon-floppy-disk"></span>
                                            </button> 
                                        </span>
                                    </div>                     
                                `
                            return html;
                        }

                    }
                },
                {data:"telefono",nombre:"telefono",title:"Teléfono"},
                {data:"estado",nombre:"estado",title:"Estado"
                    ,"render": function (data, type, row ) {
                        var dep_id=row["id"];
                        var estado = data; 
                        clase="";
                        if (estado=="Pendiente"){clase="text-danger pendiente";}
                        var html="<span class='"+clase+"'>"+estado+"</span>";
                        return html;
                    }                 
                },
                {data:"cuenta_tipo",nombre:"cuenta_tipo",title:"Tipo de Cuenta"},
                {data:"cuenta",nombre:"cuenta",title:"Cuenta"},
                {data:"monto",nombre:"monto",title:"Monto"},
                {data:"fotos",nombre:"archivo1",title:"Voucher Cliente"
                 ,"render": function (data, type, row ) {
                        var dep_id=row["id"];
                        var estado=row["estado"];
                        var cant = data;   
                        var clase_btn=cant>0?'primary':'danger';       
                        var html="<button class='btn btn-rounded btn-"+clase_btn+" btn-xs addFoto' data-id=" +dep_id+" data-btnimg='Agregar Voucher' data-Estado='"+estado+"' data-type =1  data-toggle='tooltip' title='Cargar Imagen de Voucher'><i class='fa fa-camera' aria-hidden='true'></i> (" +cant+ ") </button>";
                        return html;
                    }
                 },
                {data:"fotos2",nombre:"archivo2",title:"Validación"
                   ,"render": function (data, type, row ) {

                        var dep_id=row["id"];
                        var estado=row["estado"];
                        var cant = data;       
                        var clase_btn=cant>0?'primary':'danger';  
                        if(estado=="Validado" || estado=="Rechazado"){
                            var html="<button class='btn btn-rounded btn-"+clase_btn+" btn-xs addFoto' data-id=" +dep_id+" data-Estado='"+estado+"' data-btnimg='Agregar Comp. Validación'  data-type=2  data-toggle='tooltip' title='Cargar Archivo'><i class='fa fa-camera' aria-hidden='true'></i> (" +cant+ ") </button>"
                        }
                        if(estado=="Pendiente"){
                            html="";
                            if(!permiso_validar){
                                html="<button class='btn btn-rounded btn-"+clase_btn+" btn-xs addFoto' data-id=" +dep_id+" data-Estado='"+estado+"' data-btnimg='Agregar Comp. Validación'  data-type=2  data-toggle='tooltip' title='Cargar Archivo'><i class='fa fa-camera' aria-hidden='true'></i> (" +cant+ ") </button>"
                                //html="<button class='btn btn-rounded btn btn-sm text-success btn-default btn-xs addFoto' data-id=" +dep_id+" data-Estado='"+estado+"' data-btnimg='Agregar Comp. Validación' data-type =2  data-toggle='tooltip' title='Validar / Rechazar'><span class='glyphicon glyphicon-ok-circle'></span> Validar</button>";
                            }
                            else{
                                html=html+"<button class='btn btn-rounded btn btn-sm text-success btn-default btn-xs validador_botones' data-id=" +dep_id+" data-Estado='1' data-btnimg='Validar' data-type =2  data-toggle='tooltip' title='Rechazar'><span class='glyphicon glyphicon-ok-circle'></span> Validar </button> ";
                                html=html+"<button class='btn btn-rounded btn btn-sm text-warning btn-default btn-xs validador_botones' data-id=" +dep_id+" data-Estado='2' data-btnimg='Rechazar' data-type =2  data-toggle='tooltip' title='Rechazar'><span class='glyphicon glyphicon-ok-circle'></span> Rechazar</button>";
                            }
                        }
                        return html;
                    }
                 },
                {data:"archivos",nombre:"archivo3",title:"Apuesta"
                 ,"render": function (data, type, row ) {
                        var estado=row["estado"];
                        var dep_id=row["id"];
                        var cant = data;   
                        var clase_btn=cant>0?'primary':'danger';       
                        var html="<button class='btn btn-rounded btn-"+clase_btn+" btn-xs addFoto' data-id=" +dep_id+" data-btnimg='Agregar Transacción' data-Estado='"+estado+"' data-type =3  data-toggle='tooltip' title='Cargar Archivo'><i class='fa fa-camera' aria-hidden='true'></i> (" +cant+ ") </button>";
                        return html;
                    }
                },
   
                {data:"validador",nombre:"validador",title:"Validador"},
                {data:"update_user_at",nombre:"update_user_at",title:"Fecha Val."},

                ],

                "initComplete": function (settings, json) {


                    $(".div_select_estado").html('<select name="estado_solicitud" id="estado_solicitud" class="form-control input-sm"><option value="Todo">Todos</option><option value="Pendiente">Pendientes</option><option value="Validado">Validado</option><option value="Rechazado">Rechazado</option></select>');
                    recarga_tabla();

                    let cajaDepositosClienteSolicitud = localStorage.getItem('cajaDepositosClienteSolicitud');
                    if (cajaDepositosClienteSolicitud) $("#estado_solicitud").val(cajaDepositosClienteSolicitud).change();

                    $("#estado_solicitud").off("change").on("change",function(){
                        var val=$(this).val();
                        tablaserver.column(4).search(val).draw();
                        tablaserver.columns.adjust();
                        localStorage.setItem('cajaDepositosClienteSolicitud', $("#estado_solicitud").val());
                    })
                    setTimeout(function(){
                        $("#recargar").off("click").on("click",function(){
                            tablaserver.ajax.reload(null, false);
                        })
                        /*if($("#es_cajero").length>0){
                            filtro_inicio="Validado";
                        }else{
                            filtro_inicio="Pendiente";
                        }
                        $("#estado_solicitud").val(filtro_inicio).change(); */
                        tablaserver.columns.adjust();
                    },100)
          
                }

            });
        return tablaserver;
    };

function recarga_tabla(){
        let tiempo_para_recargar = 1000 * 4;
        if(typeof intervalotabla!=="undefined"){
            clearInterval(intervalotabla);
        }

        tablaserver.ajax.reload(null,false);
        intervalotabla = setInterval(
function () {
            if(recargar_tabla){
                tablaserver.ajax.reload(null, false);///  1er parametro=> callback al terminar carga  ;2nd parametro=> mantiene paginación
                tablaserver.columns.adjust();
            }
        }, tiempo_para_recargar);
}

function clean() {
    $('#formCliente').find('input[type="text"]').each(function () {
        $(this).val('');
    });
    $("#tbl_cliente tbody").html("");
    $('#txt_telefono').focus();
}

function cargaImg(id, type,modal) {
    var data = {};
    data.id = id;
    data.tipo=type;
    data.update = true;
    var ubicacion="../files_bucket/depositos/";

    $.post("sys/set_caja_clientes_depositos_archivos.php", {"get_archivos": data}, function (data) {
        loading();
        result = JSON.parse(data);
        let i = result.length;
        var modal_foto=modal;//$("#"+modal);
        for (let x = 0; x < i; x++) {
            if (typeof (result[x]['archivo']) != 'undefined') {
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

function deleteImgModal(modal) {
    let src = 'images/default_avatar2.png';
    $('#miniatura',modal).find('img').remove();
    $('#previewImg',modal).attr('src', src);
    let html = "<i class='fa fa-picture-o' aria-hidden='true'></i> <span id='leyenda'>Elegir imágenes</span>";
    $('#leyenda',modal).remove();
    $('.fa-picture-o',modal).remove();
    $('.labelbtn',modal).append(html);
}




