$(function () {
  if (sec_id === 'saldo_web') {
    if (saldoweb_tienda.length > 0) {
      swal('Estas en la tienda ' + saldoweb_tienda + '.', '', 'warning');
    }

    $('#saldoweb_btn_consultar').click(function () {
      saldoweb_buscar_cliente();
    });
    $('#saldoweb_idweb').keypress(function (event) {
      var keycode = event.keyCode ? event.keyCode : event.which;
      if (keycode == '13') {
        saldoweb_buscar_cliente();
      }
    });
    $('#saldoweb_btn_regresar').click(function () {
      $('#saldoweb_cliente_div').hide();
      $('#saldoweb_cliente_buscador_div').show();
      $('#saldoweb_idweb').val('');
    });
    $('#saldoweb_btn_deposito').click(function () {
      saldoweb_nuevo_deposito();
    });
    $('#close_modal_depositar').click(function(){
      $('#saldoweb_modal_deposito_monto').css('border', '');
    })
    $('#saldoweb_btn_retiro').click(function () {
      const data = {
        accion: 'verify_btns',
        btn: 'cash_out',
      };
      $.ajax({
        url: '/sys/set_saldo_web.php',
        type: 'POST',
        data: data,
        beforeSend: function () {
          loading('true');
        },
        complete: function () {
          loading();
        },
        success: function (resp) {
          var respuesta = JSON.parse(resp);
          if (parseInt(respuesta.http_code) === 400) {
            swal('Aviso', respuesta.status, 'warning');
          }
          if (parseInt(respuesta.http_code) === 200) {
            saldoweb_nuevo_retiro();
          }
          loading('false');
        },
        error: function () {},
      });
      //saldoweb_nuevo_retiro();
    });

    getLimite('local', saldoweb_local_id).then((data)=>{
      if(data){
        $('#txt_limite_local').html('Límite depósitos: S/ ' + data.limite)
      } else {
        getLimite('local_global', null).then((data)=>{
          if(data){
            $('#txt_limite_local').html('Límite depósitos: S/ ' + data.limite)
          }
        });
      }
    });

  }
});

function getLimite(tipo, item_id){

  var get_data = {
      item_id: item_id,
      tipo_limite: tipo,
  };

  return new Promise((resolve, reject) => {
      $.ajax({
          type: "POST",
          url: '/sys/get_adm_depositos_web.php',
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

function saldoweb_buscar_cliente_limpiar_campos() {
  $('#saldoweb_cliente_idweb').html('');
  $('#saldoweb_cliente_name').html('');
  $('#saldoweb_cliente_div').hide();
}

function saldoweb_buscar_cliente() {
  saldoweb_buscar_cliente_limpiar_campos();
  $('#saldoweb_btn_consultar').hide();
  $('#saldoweb_idweb').css('border', '');

  var id_web = $('#saldoweb_idweb').val();
  if (!(parseInt(id_web) > 0)) {
    $('#saldoweb_idweb').css('border', '1px solid red');
    $('#saldoweb_idweb').focus();
    $('#saldoweb_btn_consultar').show();
    return false;
  }
  var data = {
    accion: 'obtener_cliente',
    id_web: id_web,
  };
  auditoria_send({ "proceso": "consulta_usuario", "data": data });
  $.ajax({
    url: '/sys/set_saldo_web.php',
    type: 'POST',
    data: data,
    beforeSend: function () {
      loading('true');
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      $('#saldoweb_btn_consultar').show();
      var respuesta = JSON.parse(resp);
      //console.log(respuesta);
      if (parseInt(respuesta.http_code) === 400) {
        swal('Aviso', respuesta.status, 'warning');
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {
        $('#saldoweb_cliente_buscador_div').hide();
        $('#saldoweb_cliente_div').show();
        $('#saldoweb_cliente_idweb').html(id_web);
        $('#saldoweb_cliente_name').html(respuesta.cliente_name);

        getLimite('cliente', id_web).then((data)=>{
          if(data){
            $('#saldoweb_cliente_limite').html("S/ " + data.limite)
          } else {
            getLimite('cliente_global', null).then((data)=>{
              if(data){
                $('#saldoweb_cliente_limite').html("S/ " + data.limite)
              }
            });
          }
        });

        saldoweb_tbl_transacciones_listar();
        return false;
      }
    },
    error: function () {},
  });
}

function saldoweb_tbl_transacciones_limpiar() {
  $('#saldoweb_tbl_transacciones').html(
    '<thead>' +
      '<tr>' +
      '   <th class="text-center">Registro</th>' +
      '   <th class="text-center">Tipo</th>' +
      '   <th class="text-center">Transacción</th>' +
      '   <th class="text-center">Monto</th>' +
      '   <th class="text-center">Estado</th>' +
      //'   <th class="text-center">DNI escaneado?</th>' +
      '   <th class="text-center">Usuario</th>' +
      '   <th class="text-center">Acciones</th>' +
      '</tr>' +
      '</thead>' +
      '<tbody>'
  );
}

function saldoweb_tbl_transacciones_listar() {
  saldoweb_tbl_transacciones_limpiar();
  $.post('/sys/set_saldo_web.php', {
    accion: 'obtener_cliente_x_transacciones',
    id_web: $('#saldoweb_idweb').val(),
  })
    .done(function (data) {
      try {
        //console.log(data);
        var respuesta = JSON.parse(data);
        if (parseInt(respuesta.http_code) === 200) {
          if (respuesta.result.length > 0) {
            $.each(respuesta.result, function (index, item) {
              //var variables = "'" + item.registro + "','" + item.tipo + "','"  + item.txn_id + "','" + item.monto + "','" + item.usuario + "'";
              var status_color = 'red';
              var btn = '';
              if (item.status === 'Completado') {
                status_color = 'green';
                btn =
                  '<button type="button" class="btn btn-primary" style="padding: 2px 5px;"' +
                  //'    onclick="ver_voucher('+variables+')">'+
                  '    title="Imprimir Voucher" onclick="imprimir_voucher_exacto(' +
                  item.cod_transaccion +
                  ',' +
                  item.tipo_id +
                  ')">' +
                  '<span class="fa fa-print"></span>' +
                  '</button>';
                if (parseInt(item.tipo_id) === 1) {
                  if (parseInt(saldoweb_delete) === 1) {
                    btn +=
                      ' <button type="button" class="btn btn-danger" style="padding: 2px 5px;"' +
                      '    title="Anular Transacción" onclick="saldoweb_anular_transaccion(' +
                      item.tipo_id +
                      ',' +
                      item.cod_transaccion +
                      ')">' +
                      '<span class="fa fa-trash"></span>' +
                      '</button>';
                  }
                if(!item.id_extorno)
                                {
                                    if( parseInt(saldoweb_extorno_perm) === 1 ){
                                        btn += ' <button type="button" class="btn btn-danger" style="padding: 2px 5px;"'+
                                                        '    title="Solicitar Extorno" onclick="saldoweb_solicitar_extorno(' + item.cod_transaccion + ')"> '+
                                                        '<span>E</span>'+
                                                    '</button>';
                                    }
                                }
                                else
                                {
                                    btn += ' <button type="button" class="btn btn-warning" style="padding: 2px 5px;"'+
                                                    '    title="Ver Extorno" onclick="saldoweb_ver_extorno(' + item.id_extorno + ')">'+
                                                    '<span class="fa fa-eye"></span>'+
                                                '</button>';
                                }
                            }
                            //extorno no btns
                            if(parseInt(item.tipo_id) == 3)
                            {
                                btn = "";}
              } else if (item.status === 'Fallido') {
                if (parseInt(item.tipo_id) === 1) {
                  btn =
                    '<button type="button" class="btn btn-success" style="padding: 2px 5px;"' +
                    '    title="Reenviar Solicitud" onclick="saldoweb_realizar_deposito_reintento(' +
                    item.cod_transaccion +
                    ')">' +
                    '<span class="fa fa-cloud-upload"></span>' +
                    '</button>';
                  if (parseInt(saldoweb_delete) === 1) {
                    btn +=
                      ' <button type="button" class="btn btn-danger" style="padding: 2px 5px;"' +
                      '    title="Anular Transacción" onclick="saldoweb_anular_transaccion(' +
                      item.tipo_id +
                      ',' +
                      item.cod_transaccion +
                      ')">' +
                      '<span class="fa fa-trash"></span>' +
                      '</button>';
                  }
                }
              } else if (item.status === 'Anulado') {
                status_color = 'black';
              }
              let scan_color = 'orange';
              if (item.scan_doc === 'ESCANEADO') {
                scan_color = 'green';
              }

              $('#saldoweb_tbl_transacciones').append(
                '<tr>' +
                  '<td class="text-center">' +
                  item.registro +
                  '</td>' +
                  '<td class="text-center">' +
                  item.tipo +
                  '</td>' +
                  '<td class="text-center">' +
                  item.txn_id +
                  '</td>' +
                  '<td class="text-center">S/ ' +
                  item.monto +
                  '</td>' +
                  '<td class="text-center" style="color:' +
                  status_color +
                  ';">' +
                  item.status +
                  '</td>' +
                  //'<td class="text-center" style="color:'+scan_color+';">' + item.scan_doc + '</td>' +
                  '<td class="text-center">' +
                  item.usuario +
                  '</td>' +
                  '<td class="text-center">' +
                  btn +
                  '</td>' +
                  '</tr>'
              );
            });
          } else {
            $('#saldoweb_tbl_transacciones').append(
              '<tr>' +
                '<td colspan="7" class="text-center">NO HAY DATOS</td>' +
                '</tr>'
            );
          }
        } else {
          $('#saldoweb_tbl_transacciones').append(
            '<tr>' +
              '<td colspan="7" class="text-center">NO HAY DATOS</td>' +
              '</tr>'
          );
        }
      } catch (e) {
        swal('¡Error!', e, 'error');
        console.log('Error de TRY-CATCH --> Error: ' + e);
      }
    })
    .fail(function (xhr, status, error) {
      swal('¡Error!', error, 'error');
      console.log('Error de .FAIL -- Error: ' + error);
    });
}

function saldoweb_anular_transaccion(tipo_id, txn_id) {
  swal(
    {
      title: '¿Está seguro de anular la transacción?',
      text: 'No podrá revertir esta acción.',
      showCancelButton: true,
      confirmButtonColor: '#0336FF',
      cancelButtonColor: '#d33',
      confirmButtonText: 'SI, ANULAR',
      cancelButtonText: 'NO',
      //closeOnConfirm: false,
      //,showLoaderOnConfirm: true
    },
    function () {
      
      var send_data = {
        accion: 'anular_transaccion',
        tipo_id: tipo_id,
        txn_id: txn_id,
      };

      auditoria_send({ "proceso": "anular_transaccion", "data": send_data });
      $.post('/sys/set_saldo_web.php', send_data)
        .done(function (data) {
          try {
            var respuesta = JSON.parse(data);
            if (parseInt(respuesta.http_code) === 200) {
              swal('Éxito', 'La transacción ha sido anulada.', 'success');
              saldoweb_tbl_transacciones_listar();
            } else {
              swal('¡Error!', respuesta.status, 'error');
            }
          } catch (e) {
            swal('¡Error!', e, 'error');
          }
        })
        .fail(function (xhr, status, error) {
          swal('¡Error!', error, 'error');
        });
      return false;
    }
  );
}

function saldoweb_ver_extorno(id_extorno) {
    var data = {};
    data.id_extorno = id_extorno;
        loading(true);
        $.post('/sys/set_saldo_web.php', {
            accion: "ver_extorno",
            "ver_extorno": data
        }, function (resp) {
            data.response = resp;
            var response = jQuery.parseJSON(resp);
            loading();
            $("#modal_extorno").modal("show");
        });


}
function saldoweb_ver_extorno(id_extorno){
	loading(true);
    var set_data = {};
    set_data.id = id_extorno ;
	set_data.sec_extorno_solicitud_detalle = "sec_extorno_solicitud_detalle";

	$.ajax({
		url: 'sys/set_extorno.php',
		method: 'POST',
		data: set_data,
		success: function(r){
			var obj = jQuery.parseJSON(r);
			loading();
			$("#modal_extorno").off("shown.bs.modal").on("shown.bs.modal",function(){
                $('.modal').css('overflow-y', 'auto');
                loading();
            });
            $("#modal_extorno").off("hidden.bs.modal").on("hidden.bs.modal",function(){
                $("#modal_extorno select, textarea ,input").val("");
                $("#modal_extorno form #detalles").empty();
            });
			$.each(obj.registro,function(i,e){
                if(e == null)
                {
                    return true;
                }
                var style = "";
                if( i == "saldo_web_transaccion_id")
                {
                    style = " style='display:none'";
                }
				var html = '<div class="form-group"' + style + '>';
					html +=		'<label  class="col-xs-5 control-label" for="">';
					html += 		i;
					html +=		':</label>';
					html +=		'<div class="col-xs-7">';
					html +=			'<p class="form-control-static">';
					html += 			e;
					html += 		'</p>';
					html +=		'</div>';
					html +=	'</div>';
				$("#modal_extorno form #detalles").prepend(html);
			})
            $("#modal_extorno #monto_aplicado").closest(".form-group").hide();
            /*if(obj.registro["Monto Aplicado"])
            {
                $("#modal_extorno #monto_aplicado").closest(".form-group").show();
                $("#modal_extorno #monto_aplicado").val(obj.registro["Monto Aplicado"]);
            }*/
			$("#modal_extorno #monto").val(obj.registro["Monto"]);
			$("#modal_extorno").modal("show");
		},
		complete : function (){
			loading();
		},
        error: function () {
			loading();
       	}
	});
}
function saldoweb_solicitar_extorno(id_saldo_web) {
    var titulo = "¿Está seguro de realizar solicitud de extorno?";
    var save_data = {};
    save_data.id_saldo_web = id_saldo_web;
    setTimeout(function(){
        if($("#txt_motivo").length>0){
            $("#txt_motivo").focus();
        ;}
    },300);
    swal(
        {
            title: titulo + '<br><br>La solicitud será notificada al área de soporte, tener en cuenta que la solicitud puede ser <div style="color:red;display:inline">RECHAZADA</div>. <br><span style="font-size:12px">Agregar un motivo :</span> <textarea autofocus id="txt_motivo" name="txt_motivo" autofocus class="form-control" style="display:block;font-size:11px;margin-top: -10px;" placeholder="Max 50 caracteres"></textarea>',
            text: "",
            html: true,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ok",
            cancelButtonText: "Cancelar",
            closeOnConfirm: false,
            closeOnCancel: true
        },
        function (opt) {
            save_data.motivo =  $("#txt_motivo").val();
            if (opt) {
                loading(true);
                $.post('/sys/set_saldo_web.php', {
                    accion: "solicitar_extorno",
                    "solicitar_extorno": save_data
                }, function (resp) {
                    save_data.response = resp;
                    var response = jQuery.parseJSON(resp);
                    loading();
                    if(response.error)
                    {
                        swal({
                            title : "Error!",
                            text : response.error_msg,
                            type : "error",
                            closeOnConfirm : true
                        },
                        function(){
                            swal.close();
                        });
                        return false;
                    }
                    swal({
                            title: "Solicitud de Extorno enviada",
                            text: "",
                            type: "success",
                            closeOnConfirm: true
                        },
                        function () {
                            swal.close();
                            saldoweb_tbl_transacciones_listar();
                            loading();
                    });

                    auditoria_send_promise({
                        "proceso": "solicitar_extorno",
                        "data": save_data
                    }).then(() => {
                    })

                });
            } else {
            }
        }
    );
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// REALIZAR DEPÓSITO
//*******************************************************************************************************************
//*******************************************************************************************************************
$(function () {
  if (sec_id === 'saldo_web') {
    //Monto
    $('#saldoweb_modal_deposito_monto').on({
      focus: function (event) {
        $(event.target).select();
        //console.log('focus');
      },
      blur: function (event) {
        //console.log('blur');
        if (parseFloat($(event.target).val().replace(/\,/g, '')) > 0) {
          $(event.target).val(
            parseFloat($(event.target).val().replace(/\,/g, '')).toFixed(2)
          );
          $(event.target).val(function (index, value) {
            return value
              .replace(/\D/g, '')
              .replace(/([0-9])([0-9]{2})$/, '$1.$2')
              .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ',');
          });
        } else {
          $(event.target).val('0.00');
        }
      },
    });
    $('#saldoweb_modal_deposito_btn_guardar').click(function () {
      saldoweb_realizar_deposito();
    });
  }
});

function saldoweb_nuevo_deposito() {
  $('#saldoweb_modal_deposito_monto').val('');
  const data = {
    accion: 'verify_btns',
    btn: 'deposit',
  };
  $.ajax({
    url: '/sys/set_saldo_web.php',
    type: 'POST',
    data: data,
    beforeSend: function () {
      loading('true');
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      var respuesta = JSON.parse(resp);
      if (parseInt(respuesta.http_code) === 400) {
        swal('Aviso', respuesta.status, 'warning');
      }
      if (parseInt(respuesta.http_code) === 200) {
        $('#saldoweb_modal_deposito').modal();
      }
      loading('false');
    },
    error: function () {},
  });
  //$('#saldoweb_modal_deposito').modal();
}
function saldoweb_realizar_deposito() {
  //console.log(saldoweb_ccid);
  $('#saldoweb_modal_deposito_btn_guardar').hide();

  var monto = parseFloat(
    $('#saldoweb_modal_deposito_monto').val().replace(/\,/g, '')
  ).toFixed(2);
  if (!(parseFloat(monto) > 0)) {
    $('#saldoweb_modal_deposito_monto').css('border', '1px solid red');
    $('#saldoweb_modal_deposito_monto').focus();
    $('#saldoweb_modal_deposito_btn_guardar').show();
    return false;
  }
  if (!(parseFloat(monto) >= 1.0 && parseFloat(monto) <= 3000.0)) {
    $('#saldoweb_modal_deposito_monto').css('border', '1px solid red');
    $('#saldoweb_modal_deposito_monto').focus();
    $('#saldoweb_modal_deposito_btn_guardar').show();
    swal(
      'Aviso',
      'El monto debe ser mínimo de S/ 1.00 y máximo de S/ 3,000.00.',
      'warning'
    );
    return false;
  }
  var data = {
    accion: 'realizar_deposito',
    id_web: $('#saldoweb_idweb').val(),
    client_name: $('#saldoweb_cliente_name').html(),
    monto: monto,
    cc_id: saldoweb_ccid,
  };

  auditoria_send({ "proceso": "deposito", "data": data });
  //console.log ("imprimir voucher");
  $.ajax({
    url: '/sys/set_saldo_web.php',
    type: 'POST',
    data: data,
    beforeSend: function () {
      loading('true');
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      $('#saldoweb_modal_deposito_btn_guardar').show();
      var respuesta = JSON.parse(resp);
      //console.log(respuesta);
      if (parseInt(respuesta.http_code) === 400) {
        $('#saldoweb_modal_deposito').modal('hide');
        swal('Aviso', respuesta.status, 'warning');
        saldoweb_tbl_transacciones_listar();
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {
        $('#saldoweb_modal_deposito').modal('hide');
        $('#saldoweb_cliente_buscador_div').hide();

        $('#deposito_txn').val(respuesta.operationId);
        var txn_id = $('#deposito_txn').val();
        //console.log(txn_id);
        //imprimir_voucher_exacto(respuesta.cod_transaccion, 1);
        printOnlySignature_saldo_web(respuesta.cod_transaccion, 1, 0);

        saldoweb_tbl_transacciones_listar();
        swal('Aviso', 'El depósito fue exitoso.', 'success');
        return false;
      }
    },
    error: function () {},
  });
}
function saldoweb_realizar_deposito_reintento(cod_txn) {
  if (!(parseInt(cod_txn) > 0)) {
    return false;
  }
  var data = {
    accion: 'realizar_deposito_reintento',
    id_web: $('#saldoweb_idweb').val(),
    cod_txn: cod_txn,
  };
  //auditoria_send({ "proceso": "obtener_cliente", "data": data });
  //console.log ("imprimir voucher");
  $.ajax({
    url: '/sys/set_saldo_web.php',
    type: 'POST',
    data: data,
    beforeSend: function () {
      loading('true');
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      $('#saldoweb_modal_deposito_btn_guardar').show();
      var respuesta = JSON.parse(resp);
      //console.log(respuesta);
      if (parseInt(respuesta.http_code) === 400) {
        $('#saldoweb_modal_deposito').modal('hide');
        swal('Aviso', respuesta.status, 'warning');
        saldoweb_tbl_transacciones_listar();
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {
        $('#saldoweb_modal_deposito').modal('hide');
        $('#saldoweb_cliente_buscador_div').hide();

        $('#deposito_txn').val(respuesta.operationId);
        //imprimir_voucher_exacto(cod_txn, 1);
        printOnlySignature_saldo_web(cod_txn, 1, 0);

        saldoweb_tbl_transacciones_listar();
        swal('Aviso', 'El depósito fue exitoso.', 'success');
        return false;
      }
    },
    error: function () {},
  });
}

function imprimir_voucher_exacto(txn_id, tipo_id) {
  var data1 = {
    accion: 'obtener_transaccion',
    txn_id: txn_id,
    tipo_id: tipo_id,
  };
  //auditoria_send({ "proceso": "obtener_cliente", "data": data });
  $.ajax({
    url: '/sys/set_saldo_web.php',
    type: 'POST',
    data: data1,
    beforeSend: function () {
      loading('true');
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      var respuesta = JSON.parse(resp);
      //console.log(respuesta);
      if (parseInt(respuesta.http_code) === 400) {
        swal('Aviso', respuesta.status, 'warning');
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {
        if (parseInt(tipo_id) === 1) {
          $('#modal_saldoWeb_deposito_voucher_nrecibo').html(
            respuesta.result.txn_id
          );
          $('#modal_saldoWeb_deposito_voucher_fechahora').html(
            respuesta.result.created_at
          );
          $('#modal_saldoWeb_deposito_voucher_datosCliente').html(
            respuesta.result.client_name + ' - ' + respuesta.result.client_id
          );
          $('#modal_saldoWeb_deposito_voucher_monto').html(
            respuesta.result.monto + ' PEN'
          );
          $('#modal_saldoWeb_deposito_voucher_tienda').html(
            respuesta.result.local_nombre
          );
          $('#modal_saldoWeb_deposito_voucher_direccion').html(
            respuesta.result.local_direccion
          );
          $('#div_voucher_saldo_web').html('');
          //if ([6,9,10].includes(parseInt(saldoweb_area_id))) {
          $('#div_voucher_saldo_web').html(
            '<button id="sec_tlv_copiar_voucher_apuesta_pagada" type="button" ' +
              'class="btn btn-success btn-sm pull-right printOnlySignature_saldo_web" ' +
              'data-txn_id-paper="' +
              txn_id +
              '" ' +
              'data-tipo_id-paper="' +
              tipo_id +
              '" >' +
              '<b><i class="fa fa-print"></i> Imprimir</b>' +
              '</button>'
          );
          //}
          $('#modal_saldoWeb_deposito_voucher').modal();
        }
        if (parseInt(tipo_id) === 2) {
          $('#modal_saldoWeb_retiro_voucher_nrecibo').html(
            respuesta.result.txn_id
          );
          $('#modal_saldoWeb_retiro_voucher_fechahora').html(
            respuesta.result.created_at
          );
          $('#modal_saldoWeb_retiro_voucher_nombreCompleto').html(
            respuesta.result.client_name
          );
          $('#modal_saldoWeb_retiro_voucher_DNI').html(
            respuesta.result.client_num_doc
          );
          $('#modal_saldoWeb_retiro_voucher_jugadorID').html(
            respuesta.result.client_id
          );
          $('#modal_saldoWeb_retiro_voucher_monto').html(
            respuesta.result.monto + ' PEN'
          );
          $('#modal_saldoWeb_retiro_voucher_tienda').html(
            respuesta.result.local_nombre
          );
          $('#modal_saldoWeb_retiro_voucher_direccion').html(
            respuesta.result.local_direccion
          );
          $('#div_voucher_saldo_web_retiro').html('');
          //if ([6,9,10].includes(parseInt(saldoweb_area_id))) {
          $('#div_voucher_saldo_web_retiro').html(
            '<button id="sec_tlv_copiar_voucher_apuesta_pagada_retiro" type="button" ' +
              'class="btn btn-success btn-sm pull-right printOnlySignature_saldo_web_retiro" ' +
              'data-txn_id-paper="' +
              txn_id +
              '" ' +
              'data-tipo_id-paper="' +
              tipo_id +
              '" >' +
              '<b><i class="fa fa-print"></i> Imprimir</b>' +
              '</button>'
          );
          //}
          $('#modal_saldoWeb_retiro_voucher').modal();
        }
if(parseInt(tipo_id)===3){
                    $('#modal_saldoWeb_retiro_voucher_nrecibo').html(respuesta.result.txn_id);
                    $('#modal_saldoWeb_retiro_voucher_fechahora').html(respuesta.result.created_at);
                    $('#modal_saldoWeb_retiro_voucher_nombreCompleto').html(respuesta.result.client_name);
                    $('#modal_saldoWeb_retiro_voucher_DNI').html(respuesta.result.client_num_doc);
                    $('#modal_saldoWeb_retiro_voucher_jugadorID').html(respuesta.result.client_id);
                    $('#modal_saldoWeb_retiro_voucher_monto').html(respuesta.result.monto+" PEN");
                    $('#modal_saldoWeb_retiro_voucher_tienda').html(respuesta.result.local_nombre);
                    $('#modal_saldoWeb_retiro_voucher_direccion').html(respuesta.result.local_direccion);
                    $('#div_voucher_saldo_web_retiro').html('');
                    //if ([6,9,10].includes(parseInt(saldoweb_area_id))) {
                        $('#div_voucher_saldo_web_retiro').html('<button id="sec_tlv_copiar_voucher_apuesta_pagada_retiro" type="button" '
                            +'class="btn btn-success btn-sm pull-right printOnlySignature_saldo_web_retiro" '
                            +'data-txn_id-paper="' + txn_id + '" '
                            +'data-tipo_id-paper="' + tipo_id + '" >'
                            +'<b><i class="fa fa-print"></i> Imprimir</b>'
                            +'</button>');
                    //}
                    $('#modal_saldoWeb_retiro_voucher').modal();
                }        return false;
      }
    },
    error: function () {
      return false;
    },
  });
}
var saldo_web_puedeDigitarDoc = false;
//*******************************************************************************************************************
//*******************************************************************************************************************
// REALIZAR RETIRO
//*******************************************************************************************************************
//*******************************************************************************************************************
$(function () {
  if (sec_id === 'saldo_web') {
    $('#saldoweb_modal_retiro_btn_guardar').click(function () {
      saldoweb_realizar_retiro();
    });
    $('#saldoweb_btn_change_disabled_to_input_num_doc').click(function () {
      const inputDocNum = document.getElementById(
        'saldoweb_modal_retiro_numdoc'
      );
      if (!saldo_web_puedeDigitarDoc) {
        //inputDocNum.disabled = false;
        const observacion = document.getElementById(
          'saldoweb_modal_div_retiro_observacion'
        );
        observacion.style.display = 'block';
        $('#saldoweb_modal_retiro_numdoc').val('');
        saldo_web_puedeDigitarDoc = true;
      }
    });
    $('#saldoweb_modal_retiro_numdoc').keypress(function () {
      console.log('keypress');
      if (!saldo_web_puedeDigitarDoc) {
        delay(function () {
          if ($('#saldoweb_modal_retiro_numdoc').val().length < 8) {
            $('#saldoweb_modal_retiro_numdoc').val('');
          }
        }, 20);
      }
    });
    var delay = (function () {
      var timer = 0;
      return function (callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
      };
    })();
  }
});
function saldoweb_nuevo_retiro() {
  $('#saldoweb_modal_div_retiro_observacion').hide();
  //document.getElementById('saldoweb_modal_retiro_numdoc').disabled = true;
    saldo_web_puedeDigitarDoc = false;
  $('#saldoweb_modal_retiro_numdoc').val('');

  var data = {
    accion: 'consultar_retiro',
    id_web: $('#saldoweb_idweb').val(),
    cc_id: saldoweb_ccid,
  };
  //auditoria_send({ "proceso": "obtener_cliente", "data": data });
  $.ajax({
    url: '/sys/set_saldo_web.php',
    type: 'POST',
    data: data,
    beforeSend: function () {
      loading('true');
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      var respuesta = JSON.parse(resp);
      //console.log(respuesta);
      if (parseInt(respuesta.http_code) === 400) {
        swal('Aviso', respuesta.status, 'warning');
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {
        var temp_monto = parseFloat(respuesta.amount / 100)
          .toFixed(2)
          .replace(/\D/g, '')
          .replace(/([0-9])([0-9]{2})$/, '$1.$2')
          .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ',');
        $('#saldoweb_modal_retiro_transaccion').val(respuesta.idTransaccion);
        $('#saldoweb_modal_retiro_monto').val(temp_monto);
        $('#saldoweb_modal_retiro').modal();
        return false;
      }
    },
    error: function () {},
  });
}

function saldoweb_realizar_retiro() {
  id_trans = $('#saldoweb_modal_retiro_transaccion').val();

  $('#saldoweb_modal_retiro_btn_guardar').hide();
  $('#saldoweb_modal_retiro_numdoc').css('border', '');
  $('#saldoweb_modal_retiro_numdoc').focus();
  var num_doc = $('#saldoweb_modal_retiro_numdoc').val();
  if (!(num_doc.length > 0)) {
    $('#saldoweb_modal_retiro_numdoc').css('border', '1px solid red');
    $('#saldoweb_modal_retiro_numdoc').focus();
    $('#saldoweb_modal_retiro_btn_guardar').show();
    return false;
  }
  const inputDocNum = document.getElementById('saldoweb_modal_retiro_numdoc');
  var observacion = '';
  if (saldo_web_puedeDigitarDoc) {
    observacion = $('#saldoweb_modal_retiro_observacion').val();
    // observacion = observacion.trim();
    console.log(observacion);
    $('#saldoweb_modal_retiro_observacion').val(observacion);
    if (
      observacion === null ||
      observacion === '' ||
      observacion === undefined
    ) {
      alertify.warning('Debe ingresar la observación', 5);
      $('#saldoweb_modal_retiro_observacion').css('border', '1px solid red');
      $('#saldoweb_modal_retiro_observacion').focus();
      $('#saldoweb_modal_retiro_btn_guardar').show();
      return false;
    }
    /*if (observacion.length < 12){
            alertify.warning('La observación debe tener mínimo 12 caracteres',5);
            $('#saldoweb_modal_retiro_observacion').css('border', '1px solid red');
            $('#saldoweb_modal_retiro_observacion').focus();
            $('#saldoweb_modal_retiro_btn_guardar').show();
            return false;
        }*/
  }
  if (!saldo_web_puedeDigitarDoc) {
    //$('#saldoweb_modal_retiro_observacion').val(null);
    observacion = undefined;
  }
  var data = {
    accion: 'realizar_retiro',
    id_web: $('#saldoweb_idweb').val(),
    client_name: $('#saldoweb_cliente_name').html(),
    num_doc: num_doc,
    cc_id: saldoweb_ccid,
    id_transaccion: $('#saldoweb_modal_retiro_transaccion').val(),
    scan_doc: saldo_web_puedeDigitarDoc ? '2' : '1',
    observacion: observacion,
  }; //scan_doc 1 = escaneado, 2 = digitado
  //console.log(data);
  
  auditoria_send({ "proceso": "retiro", "data": data });

  $.ajax({
    url: '/sys/set_saldo_web.php',
    type: 'POST',
    data: data,
    beforeSend: function () {
      loading('true');
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      $('#saldoweb_modal_retiro_btn_guardar').show();
      var respuesta = JSON.parse(resp);
      console.log(respuesta);
      if (parseInt(respuesta.http_code) === 400) {
        swal('Aviso', respuesta.status, 'warning');
        saldoweb_tbl_transacciones_listar();
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {
        $('#saldoweb_modal_retiro').modal('hide');

        //imprimir_voucher_exacto(respuesta.cod_transaccion, 2);
        printOnlySignature_saldo_web_retiro(respuesta.cod_transaccion, 2, 0);

        saldoweb_tbl_transacciones_listar();
        swal('Aviso', 'El retiro fue exitoso.', 'success');
        return false;
      }
    },
    error: function () {},
  });
}












//*******************************************************************************************************************
//*******************************************************************************************************************
// VOUCHER DEPÓSITO
//*******************************************************************************************************************
//*******************************************************************************************************************

$('body').on('click', '.printOnlySignature_saldo_web', function () {
  var txn_id = $(this).attr('data-txn_id-paper');
  var tipo_id = $(this).attr('data-tipo_id-paper');
  printOnlySignature_saldo_web(txn_id, tipo_id, 1);
});

const setImagenFirma = (archivo) => {
  let promesa = new Promise((res, rej) => {
    let urlImage = '../files_bucket/registros/firmas/' + archivo;
    $('#imgFirmaRecurso').attr('src', urlImage);
    let imgHtml = $('#imgFirmaRecurso');

    setTimeout(() => {
      if (imgHtml.attr('src') === urlImage) {
        res(true);
      } else {
        rej(false);
      }
    }, 1000);
  });

  return promesa;
};

/* SOLO HACE printOnlySignature DE LOS REGISTROS CON FIRMA */
const printOnlySignature_saldo_web = (txn_id, tipo_id, valid_reimpresion) => {
  loading(true);
  var obj = {};
  var retorn = {};
  var data = {};
  var tiposDoc = ['DNI', 'CE/PTP', 'Pasaporte'];

  data.txn_id = txn_id;
  data.tipo_id = tipo_id;

  // console.log(imgbase64);
  $.post(
    '/sys/set_saldo_web.php',
    { obtener_transaccion: data, accion: 'obtener_transaccion' },
    function (datas) {
      loading();
      response = JSON.parse(datas);
      //console.log('llego al post');
      if (response.http_code == '200') {
        retorn = response.result;
        obj.txn_id = retorn.txn_id;
        obj.cc_id = retorn.cc_id;
        obj.created_at = retorn.created_at;
        obj.client_id = retorn.client_id;
        obj.client_name = retorn.client_name;
        obj.monto = retorn.monto;
        obj.nombre = retorn.local_nombre;
        obj.direccion = retorn.local_direccion;
        obj.texto = 'texto test';
        obj.textoLegalMarketing = 'legal marketing test';
        obj.textoLegalClienteDB = 'legal cliente test';

        /* console.log(retorn.local);
            console.log(imgbase64); */

        //*******************************************************************************************************************
        //*******************************************************************************************************************
        // CENTRAR TEXTO EN JSPDF
        //*******************************************************************************************************************
        //*******************************************************************************************************************

        (function (API) {
          API.myText = function (txt, options, x, y) {
            options = options || {};
            /* Use the options align property to specify desired text alignment
             * Param x will be ignored if desired text alignment is 'center'.
             * Usage of options can easily extend the function to apply different text
             * styles and sizes
             */
            if (options.align == 'center') {
              // Get current font size
              var fontSize = this.internal.getFontSize();

              // Get page width
              var pageWidth = this.internal.pageSize.width;

              // Get the actual text's width
              /* You multiply the unit width of your string by your font size and divide
               * by the internal scale factor. The division is necessary
               * for the case where you use units other than 'pt' in the constructor
               * of jsPDF.
               */
              txtWidth =
                (this.getStringUnitWidth(txt) * fontSize) /
                this.internal.scaleFactor;

              // Calculate text's x coordinate
              x = (pageWidth - txtWidth) / 2;
            }

            // Draw text at x,y
            this.text(txt, x, y);
          };
        })(jsPDF.API);

        //*******************************************************************************************************************
        //*******************************************************************************************************************
        // CENTRAR TEXTO EN JSPDF (FIN)
        //*******************************************************************************************************************
        //*******************************************************************************************************************

        /*var doc = new jsPDF('p', 'mm', [80, 160])*/
        var docAux = new jsPDF('p', 'mm', [200, 200]);
        let marketingLines = docAux
          .setFont()
          .setFontSize(6.3)
          .splitTextToSize(obj.textoLegalMarketing, 65).length;
        let dbLines = docAux
          .setFont()
          .setFontSize(6.3)
          .splitTextToSize(obj.textoLegalClienteDB, 65).length;
        let baseHeight = 52 + dbLines * 2.7 + 10 + 30;
        let docHeight =
          obj.redes == 1 ? baseHeight + marketingLines * 2.7 : baseHeight;
        //let docHeight = obj.redes == 1 ? 190 : 150;

        var cRatio = 2.83;
        var doc = new jsPDF('p', 'mm', [80 * cRatio, docHeight * cRatio]);
        var docfin = new jsPDF('p', 'mm', [80 * cRatio, 160 * cRatio]);

        /*  if (obj.redes == 0) {
                    doc.deletePage(1);
                    doc.addPage([80, 80], 'portrait');
                    //doc = docfin;
                }*/

        let justifyTextOption = {
          align: 'justify',
          maxWidth: 65,
        };

        doc.setFontSize(6.5);
        doc.setFontType('bold');
        doc.myText(obj.nombre, { align: 'center' }, 0, 8);
        doc.setFontSize(6.2);
        doc.setFontType('normal');

        if (obj.direccion.length < 67) {
          doc.text(obj.direccion, 40, 14, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 15, 73, 15);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito Web Apuesta Total', { align: 'center' }, 0, 22);
          doc.myText('Copia de Cajero', { align: 'center' }, 0, 26);
        } else if (obj.direccion.length < 134) {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 16, 73, 16);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito Web Apuesta Total', { align: 'center' }, 0, 23);
          doc.myText('Copia de Cajero', { align: 'center' }, 0, 27);
        } else if (obj.direccion.length < 201) {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 18, 73, 18);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito Web Apuesta Total', { align: 'center' }, 0, 24);
          doc.myText('Copia de Cajero', { align: 'center' }, 0, 28);
        } else {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 20.5, 73, 20.5);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito Web Apuesta Total', { align: 'center' }, 0, 25);
          doc.myText('Copia de Cajero', { align: 'center' }, 0, 29);
        }

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.text(6, 33, 'N° de Recibo: ');
        local = doc
          .setFont()
          .setFontSize(8)
          .setFontType('normal')
          .splitTextToSize(obj.txn_id, 45);
        doc.text(26, 33, local);

        local = doc
          .setFont()
          .setFontSize(8)
          .setFontType('normal')
          .splitTextToSize(obj.created_at, 45);
        doc.text(42, 33, local);

        if (parseInt(valid_reimpresion) === 1) {
          doc.setFontType('bold');
          doc.setFontSize(7);
          doc.text(6, 37, 'Reimpresión: ');
          doc.setFontType('normal');
          doc.setFontSize(7);
          doc.text(23, 37, response.fecha_hora_actual);
        }

        doc.setFontType('bold');
        doc.setFontSize(8);
        doc.text(6, 43, 'Datos del cliente:');
        doc.setFontType('normal');
        doc.setFontSize(6.7);
        doc.myText(
          obj.client_name + ' - ' + obj.client_id,
          { align: 'center' },
          0,
          48
        );

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.text(6, 56, 'Fundamento y finalidad del recibo:');
        doc.setFontType('normal');
        //doc.text(20, 61, 'Depósito Web en Apuesta Total')
        doc.myText('Depósito Web en Apuesta Total', { align: 'center' }, 0, 61);
        doc.line(6, 62, 73, 62);

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.text(6, 70, 'Monto:');
        doc.setFontType('normal');
        doc.text(16, 70, obj.monto + ' PEN');

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.myText('PARA GANAR HAY QUE CREER', { align: 'center' }, 0, 82);

        doc.addPage([80 * cRatio, docHeight * cRatio], 'p');

        doc.setFontSize(6.5);
        doc.setFontType('bold');
        doc.myText(obj.nombre, { align: 'center' }, 0, 8);
        doc.setFontSize(6.2);
        doc.setFontType('normal');

        if (obj.direccion.length < 67) {
          doc.text(obj.direccion, 40, 14, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 15, 73, 15);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito Web Apuesta Total', { align: 'center' }, 0, 22);
          doc.myText('Copia de Cliente', { align: 'center' }, 0, 26);
        } else if (obj.direccion.length < 134) {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 16, 73, 16);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito Web Apuesta Total', { align: 'center' }, 0, 23);
          doc.myText('Copia de Cliente', { align: 'center' }, 0, 27);
        } else if (obj.direccion.length < 201) {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 18, 73, 18);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito Web Apuesta Total', { align: 'center' }, 0, 24);
          doc.myText('Copia de Cliente', { align: 'center' }, 0, 28);
        } else {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 20.5, 73, 20.5);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito Web Apuesta Total', { align: 'center' }, 0, 25);
          doc.myText('Copia de Cliente', { align: 'center' }, 0, 29);
        }

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.text(6, 33, 'N° de Recibo: ');
        //doc.text(39, 35, 'Fecha: ')

        local = doc
          .setFont()
          .setFontSize(8)
          .setFontType('normal')
          .splitTextToSize(obj.txn_id, 45);
        doc.text(26, 33, local);

        local = doc
          .setFont()
          .setFontSize(8)
          .setFontType('normal')
          .splitTextToSize(obj.created_at, 45);
        doc.text(42, 33, local);

        if (parseInt(valid_reimpresion) === 1) {
          doc.setFontType('bold');
          doc.setFontSize(7);
          doc.text(6, 37, 'Reimpresión: ');
          doc.setFontType('normal');
          doc.setFontSize(7);
          doc.text(23, 37, response.fecha_hora_actual);
        }

        doc.setFontType('bold');
        doc.setFontSize(8);
        doc.text(6, 43, 'Datos del cliente:');
        doc.setFontType('normal');
        doc.setFontSize(6.7);
        doc.myText(
          obj.client_name + ' - ' + obj.client_id,
          { align: 'center' },
          0,
          48
        );
        //doc.line(6, 49, 85, 49)

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.text(6, 56, 'Fundamento y finalidad del recibo:');
        doc.setFontType('normal');
        doc.myText('Depósito Web en Apuesta Total', { align: 'center' }, 0, 61);
        doc.line(6, 62, 73, 62);

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.text(6, 70, 'Monto:');
        doc.setFontType('normal');
        doc.text(16, 70, obj.monto + ' PEN');

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.myText('PARA GANAR HAY QUE CREER', { align: 'center' }, 0, 82);

        docfin = doc;
        docfin.autoPrint();
        docfin.save('' + obj.txn_id + '.pdf');
        window.open(docfin.output('bloburl'), '_blank');

        loading(false);
      } else {
        console.log(result.error);
      }
    }
  );
};

//*******************************************************************************************************************
//*******************************************************************************************************************
// VOUCHER RETIRO
//*******************************************************************************************************************
//*******************************************************************************************************************

$('body').on('click', '.printOnlySignature_saldo_web_retiro', function () {
  var txn_id = $(this).attr('data-txn_id-paper');
  var tipo_id = $(this).attr('data-tipo_id-paper');
  printOnlySignature_saldo_web_retiro(txn_id, tipo_id, 1);
});

/* SOLO HACE printOnlySignature DE LOS REGISTROS CON FIRMA */
const printOnlySignature_saldo_web_retiro = (
  txn_id,
  tipo_id,
  valid_reimpresion
) => {
  loading(true);
  var obj = {};
  var retorn = {};
  var data = {};
  var tiposDoc = ['DNI', 'CE/PTP', 'Pasaporte'];

  data.txn_id = txn_id;
  data.tipo_id = tipo_id;

  // console.log(imgbase64);
  $.post(
    '/sys/set_saldo_web.php',
    { obtener_transaccion: data, accion: 'obtener_transaccion' },
    function (datas) {
      loading();
      response = JSON.parse(datas);
      //console.log('llego al post');
      if (response.http_code == '200') {
        retorn = response.result;
        obj.txn_id = retorn.txn_id;
        obj.client_num_doc = retorn.client_num_doc;
        obj.cc_id = retorn.cc_id;
        obj.created_at = retorn.created_at;
        obj.client_id = retorn.client_id;
        obj.client_name = retorn.client_name;
        obj.client_num_doc = retorn.client_num_doc;
        obj.monto = retorn.monto;
        obj.nombre = retorn.local_nombre;
        obj.direccion = retorn.local_direccion;
        obj.texto = 'texto test';
        obj.textoLegalMarketing = 'legal marketing test';
        obj.textoLegalClienteDB = 'legal cliente test';

        /* console.log(retorn.local);
            console.log(imgbase64); */

        //*******************************************************************************************************************
        //*******************************************************************************************************************
        // CENTRAR TEXTO EN JSPDF
        //*******************************************************************************************************************
        //*******************************************************************************************************************

        (function (API) {
          API.myText = function (txt, options, x, y) {
            options = options || {};
            /* Use the options align property to specify desired text alignment
             * Param x will be ignored if desired text alignment is 'center'.
             * Usage of options can easily extend the function to apply different text
             * styles and sizes
             */
            if (options.align == 'center') {
              // Get current font size
              var fontSize = this.internal.getFontSize();

              // Get page width
              var pageWidth = this.internal.pageSize.width;

              // Get the actual text's width
              /* You multiply the unit width of your string by your font size and divide
               * by the internal scale factor. The division is necessary
               * for the case where you use units other than 'pt' in the constructor
               * of jsPDF.
               */
              txtWidth =
                (this.getStringUnitWidth(txt) * fontSize) /
                this.internal.scaleFactor;

              // Calculate text's x coordinate
              x = (pageWidth - txtWidth) / 2;
            }

            // Draw text at x,y
            this.text(txt, x, y);
          };
        })(jsPDF.API);

        //*******************************************************************************************************************
        //*******************************************************************************************************************
        // CENTRAR TEXTO EN JSPDF (FIN)
        //*******************************************************************************************************************
        //*******************************************************************************************************************

        /*var doc = new jsPDF('p', 'mm', [80, 160])*/
        var docAux = new jsPDF('p', 'mm', [200, 200]);
        let marketingLines = docAux
          .setFont()
          .setFontSize(6.3)
          .splitTextToSize(obj.textoLegalMarketing, 65).length;
        let dbLines = docAux
          .setFont()
          .setFontSize(6.3)
          .splitTextToSize(obj.textoLegalClienteDB, 65).length;
        let baseHeight = 52 + dbLines * 2.7 + 10 + 30;
        let docHeight =
          obj.redes == 1 ? baseHeight + marketingLines * 2.7 : baseHeight;
        //let docHeight = obj.redes == 1 ? 190 : 150;

        var cRatio = 2.83;
        var doc = new jsPDF('p', 'mm', [80 * cRatio, docHeight * cRatio]);
        var docfin = new jsPDF('p', 'mm', [80 * cRatio, 160 * cRatio]);

        let centerTextOption = {
          align: 'justify',
          maxWidth: 65,
        };

        doc.setFontSize(6.5);
        doc.setFontType('bold');
        doc.myText(obj.nombre, { align: 'center' }, 0, 8);
        doc.setFontSize(6.2);
        doc.setFontType('normal');

        if (obj.direccion.length < 67) {
          doc.text(obj.direccion, 40, 14, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 15, 73, 15);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Solicitud de Pago de Retiro', { align: 'center' }, 0, 22);
          doc.myText('Copia de Cajero', { align: 'center' }, 0, 26);
        } else if (obj.direccion.length < 134) {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 16, 73, 16);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Solicitud de Pago de Retiro', { align: 'center' }, 0, 23);
          doc.myText('Copia de Cajero', { align: 'center' }, 0, 27);
        } else if (obj.direccion.length < 201) {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 18, 73, 18);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Solicitud de Pago de Retiro', { align: 'center' }, 0, 24);
          doc.myText('Copia de Cajero', { align: 'center' }, 0, 28);
        } else {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 20.5, 73, 20.5);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Solicitud de Pago de Retiro', { align: 'center' }, 0, 25);
          doc.myText('Copia de Cajero', { align: 'center' }, 0, 29);
        }

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.text(6, 33, 'N° de Recibo: ');
        //doc.text(39, 36, 'Fecha: ')

        local = doc
          .setFont()
          .setFontSize(8)
          .setFontType('normal')
          .splitTextToSize(obj.txn_id, 45);
        doc.text(26, 33, local);

        local = doc
          .setFont()
          .setFontSize(8)
          .setFontType('normal')
          .splitTextToSize(obj.created_at, 45);
        doc.text(49, 33, local);

        if (parseInt(valid_reimpresion) === 1) {
          doc.setFontType('bold');
          doc.setFontSize(7);
          doc.text(6, 37, 'Reimpresión: ');
          doc.setFontType('normal');
          doc.setFontSize(7);
          doc.text(23, 37, response.fecha_hora_actual);
        }

        doc.setFontType('bold');
        doc.setFontSize(8);
        doc.text(6, 43, 'Datos del cliente:');

        doc.setFontSize(7);
        doc.text(7, 47, 'Nombre: ');
        doc.text(7, 51, 'DNI: ');
        doc.text(7, 55, 'ID de jugador: ');
        doc.setFontSize(6.5);
        doc.setFontType('normal');
        doc.text(18, 47, obj.client_name);
        doc.text(18, 51, obj.client_num_doc);
        doc.text(25, 55, obj.client_id);

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.text(6, 60, 'Fundamento y finalidad del recibo:');
        doc.setFontType('normal');
        doc.myText('Solicitud de Pago de Retiro', { align: 'center' }, 0, 64);
        doc.line(6, 65, 73, 65);

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.text(6, 70, 'Monto:');
        doc.setFontType('normal');
        doc.text(16, 70, obj.monto + ' PEN');

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.line(6, 82, 73, 82);
        doc.setFontType('normal');
        doc.myText('Firma y DNI del cliente', { align: 'center' }, 0, 85);

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.myText('PARA GANAR HAY QUE CREER', { align: 'center' }, 0, 90);

        doc.addPage([80 * cRatio, docHeight * cRatio], 'p');

        doc.setFontSize(6.5);
        doc.setFontType('bold');
        doc.myText(obj.nombre, { align: 'center' }, 0, 8);
        doc.setFontSize(6.2);
        doc.setFontType('normal');

        if (obj.direccion.length < 67) {
          doc.text(obj.direccion, 40, 14, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 15, 73, 15);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Solicitud de Pago de Retiro', { align: 'center' }, 0, 22);
          doc.myText('Copia de Cliente', { align: 'center' }, 0, 26);
        } else if (obj.direccion.length < 134) {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 16, 73, 16);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Solicitud de Pago de Retiro', { align: 'center' }, 0, 23);
          doc.myText('Copia de Cliente', { align: 'center' }, 0, 27);
        } else if (obj.direccion.length < 201) {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 18, 73, 18);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Solicitud de Pago de Retiro', { align: 'center' }, 0, 24);
          doc.myText('Copia de Cliente', { align: 'center' }, 0, 28);
        } else {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 20.5, 73, 20.5);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Solicitud de Pago de Retiro', { align: 'center' }, 0, 25);
          doc.myText('Copia de Cliente', { align: 'center' }, 0, 29);
        }

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.text(6, 33, 'N° de Recibo: ');
        //doc.text(39, 36, 'Fecha: ')

        local = doc
          .setFont()
          .setFontSize(8)
          .setFontType('normal')
          .splitTextToSize(obj.txn_id, 45);
        doc.text(26, 33, local);

        local = doc
          .setFont()
          .setFontSize(8)
          .setFontType('normal')
          .splitTextToSize(obj.created_at, 45);
        doc.text(49, 33, local);

        if (parseInt(valid_reimpresion) === 1) {
          doc.setFontType('bold');
          doc.setFontSize(7);
          doc.text(6, 37, 'Reimpresión: ');
          doc.setFontType('normal');
          doc.setFontSize(7);
          doc.text(23, 37, response.fecha_hora_actual);
        }

        doc.setFontType('bold');
        doc.setFontSize(8);
        doc.text(6, 43, 'Datos del cliente:');

        doc.setFontSize(7);
        doc.text(7, 47, 'Nombre: ');
        doc.text(7, 51, 'DNI: ');
        doc.text(7, 55, 'ID de jugador: ');
        doc.setFontSize(6.5);
        doc.setFontType('normal');
        doc.text(18, 47, obj.client_name);
        doc.text(18, 51, obj.client_num_doc);
        doc.text(25, 55, obj.client_id);

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.text(6, 60, 'Fundamento y finalidad del recibo:');
        doc.setFontType('normal');
        doc.myText('Solicitud de Pago de Retiro', { align: 'center' }, 0, 64);
        doc.line(6, 65, 73, 65);

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.text(6, 70, 'Monto:');
        doc.setFontType('normal');
        doc.text(16, 70, obj.monto + ' PEN');

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.line(6, 82, 73, 82);
        doc.setFontType('normal');
        doc.myText('Firma y DNI del cliente', { align: 'center' }, 0, 85);

        doc.setFontSize(8);
        doc.setFontType('bold');
        doc.myText('PARA GANAR HAY QUE CREER', { align: 'center' }, 0, 90);

        docfin = doc;
        docfin.autoPrint();
        docfin.save('' + obj.txn_id + '.pdf');
        window.open(docfin.output('bloburl'), '_blank');

        loading(false);
      } else {
        console.log(result.error);
      }
    }
  );
};
