$(function () {
  if (sec_id === 'saldo_tls') {
    if (saldotls_tienda.length > 0) {
      swal('Estas en la tienda ' + saldotls_tienda + '.', '', 'warning');
    }
    $('input[name="saldotls_tipodoc_cliente"][value="0"]').click();
    $('input:radio[name=saldotls_tipodoc_cliente]').on('change', function () {
      
			setTimeout(function () {
				 
				$("#saldotls_numdoc_cliente").val(''); // Limpiar input
				$("#saldotls_numdoc_cliente").removeAttr('maxLength');

				var busc_tipo = $("input:radio[name='saldotls_tipodoc_cliente']:checked").val(); //Obtener valor del radio seleccionado
				// DNI
				if (parseInt(busc_tipo) == 0) {
					$("#saldotls_numdoc_cliente").attr('maxLength', '8');
				}
				// CE/PTP
				if (parseInt(busc_tipo) == 1) {
					$("#saldotls_numdoc_cliente").attr('maxLength', '9');					
				}
			 

				$("#saldotls_numdoc_cliente").focus(); // Dar foco al input
				return false;
			}, 100);
		});

    $('#saldotls_btn_consultar').click(function () {
      saldotls_buscar_cliente();
    });
    $('#saldotls_numdoc_cliente').keypress(function (event) {
      var keycode = event.keyCode ? event.keyCode : event.which;
      if (keycode == '13') {
        saldotls_buscar_cliente();
      }
    });
    $('#saldotls_btn_regresar').click(function () {
      $('#saldotls_cliente_div').hide();
      $('#saldotls_cliente_buscador_div').show();
      $('#saldotls_numdoc_cliente').val('');
    });
    $('#saldotls_btn_deposito').click(function () {
      saldotls_nuevo_deposito();
    });
    $('#saldotls_btn_retiro').click(function () {
      const data = {
        accion: 'verify_btns',
        btn: 'cash_out',
      };
      $.ajax({
        url: '/sys/set_saldo_tls.php',
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
            saldotls_nuevo_retiro();
          }
          loading('false');
        },
        error: function () {},
      });
      //saldotls_nuevo_retiro();
    });

    getLimitetls('local', saldotls_local_id).then((data)=>{
      if(data){
        $('#txt_limite_local_tls').html('Límite depósitos: S/ ' + data.limite)
      } else {
        getLimitetls('local_global', null).then((data)=>{
          if(data){
            $('#txt_limite_local_tls').html('Límite depósitos: S/ ' + data.limite)
          }
        });
      }
    });
    
  }
});


function getLimitetls(tipo, item_id){

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

function saldotls_buscar_cliente_limpiar_campos() {
  $('#saldotls_cliente_numdoc').html('');
  $('#saldotls_cliente_name').html('');
  $('#saldotls_cliente_div').hide();
}

function saldotls_buscar_cliente() {
  saldotls_buscar_cliente_limpiar_campos();
  $('#saldotls_btn_consultar').hide();
  $('#saldotls_numdoc_cliente').css('border', '');

  var tipo_doc = $("input:radio[name='saldotls_tipodoc_cliente']:checked").val();
	var num_doc = $.trim($("#saldotls_numdoc_cliente").val());

  // DNI
	if (parseInt(tipo_doc) == 0) {
		if (num_doc.length !== 8) {
      swal('Aviso', 'El número de DNI debe tener 8 dígitos.', 'info');
			$('#saldotls_numdoc_cliente').css('border', '1px solid red');
      $('#saldotls_numdoc_cliente').focus();
      $('#saldotls_btn_consultar').show();
      return false;
		}
		if (!(parseInt(num_doc) >= 1 && parseInt(num_doc) <= 99999999)) {
      swal('Aviso', 'Número de DNI inválido.', 'info');
			$('#saldotls_numdoc_cliente').css('border', '1px solid red');
      $('#saldotls_numdoc_cliente').focus();
      $('#saldotls_btn_consultar').show();
      return false;
		}
	}

  // CE/PTP
	if (parseInt(tipo_doc) == 1) {
			if (num_doc.length < 9) {
        swal('Aviso', 'El número de CE/PTP debe al menos 9 dígitos.', 'info');
				$('#saldotls_numdoc_cliente').css('border', '1px solid red');
        $('#saldotls_numdoc_cliente').focus();
        $('#saldotls_btn_consultar').show();
        return false;
			}
			if (!(parseInt(num_doc) >= 0 && parseInt(num_doc) <= 999999999)) {
        swal('Aviso', 'Número de CE/PTP inválido.', 'info');
				$('#saldotls_numdoc_cliente').css('border', '1px solid red');
        $('#saldotls_numdoc_cliente').focus();
        $('#saldotls_btn_consultar').show();
        return false;
			}
	}

  var data = {
    accion: 'obtener_cliente_tls',
    num_doc: num_doc,
    tipo_doc: tipo_doc
  };
  auditoria_send({ "proceso": "consulta_cliente_tls", "data": data });
  $.ajax({
    url: '/sys/set_saldo_tls.php',
    type: 'POST',
    data: data,
    beforeSend: function () {
      loading('true');
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      $('#saldotls_btn_consultar').show();
      var respuesta = JSON.parse(resp);
     // console.log(respuesta);
      if (parseInt(respuesta.http_code) === 400) {
        swal('Aviso', respuesta.status, 'warning');
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {
        $('#saldotls_cliente_buscador_div').hide();
        $('#saldotls_cliente_div').show();
        $('#saldotls_cliente_numdoc').html(num_doc);
        $("#saldotls_tipodoc_cli").val(tipo_doc);       
        $('#saldotls_cliente_name').html(respuesta.result);
        $("#saldotls_id_cli").val(respuesta.id);
        $("#saldotls_webid_cli").val(respuesta.web_id);
        saldotls_tbl_transacciones_listar();
        return false;
      }
    },
    error: function () {},
  });
}

function saldotls_tbl_transacciones_limpiar() {
  $('#saldotls_tbl_transacciones').html(
    '<thead>' +
      '<tr>' +
      '   <th class="text-center">Registro</th>' +
      '   <th class="text-center">Tipo</th>' +
      '   <th class="text-center">Transacción</th>' +
      '   <th class="text-center">Monto</th>' +
      '   <th class="text-center">Estado</th>' +
      //'   <th class="text-center">DNI escaneado?</th>' +
      '   <th class="text-center">Usuario</th>' +
      '   <th class="text-center">Acción</th>' +
      '</tr>' +
      '</thead>' +
      '<tbody>'
  );
}

function saldotls_tbl_transacciones_listar() {
  saldotls_tbl_transacciones_limpiar();
  var id_cli = $.trim($('#saldotls_id_cli').val());

  $.post('/sys/set_saldo_tls.php', {
    accion: 'obtener_cliente_x_transacciones',
    id_cli: id_cli,
  })
    .done(function (data) {
      try {
        //console.log(data);
        var respuesta = JSON.parse(data);
        if (parseInt(respuesta.http_code) === 200) {
          if (respuesta.result.length > 0) {
            $.each(respuesta.result, function (index, item) {
            
              var status_color = 'red';
              var btn = '';
              var status_tra ='';
              
              if (item.estado === '1' && (item.tipo_id === '2' || item.tipo_id === '1' )) {
                status_tra ='Completado';
              }

              if (item.estado === '2' && item.tipo_id === '1') {
                status_tra ='Anulado';
              }
             
              if (status_tra=== 'Completado') {
                status_color = 'green';
                btn =
                  '<button type="button" class="btn btn-primary" style="padding: 2px 5px;"' +
                  //'    onclick="ver_voucher('+variables+')">'+
                  '    title="Imprimir Voucher" onclick="saldo_tls_imprimir_voucher_exacto(' +
                  item.transaccion_id +
                  ',' +
                  item.tipo_id +
                  ')">' +
                  '<span class="fa fa-print"></span>' +
                  '</button>';
                if (parseInt(item.tipo_id) === 1 && parseInt(respuesta.btn_anular) === 1) {
                  if (parseInt(saldotls_delete) === 1) {
                    btn +=
                      ' <button type="button" class="btn btn-danger" style="padding: 2px 5px;"' +
                      '    title="Anular Transacción" onclick="saldotls_anular_transaccion(' +
                      id_cli +
                      ',' +
                      item.tipo_id +
                      ',' +
                      item.transaccion_id +
                      ')">' +
                      '<span class="fa fa-trash"></span>' +
                      '</button>';
                  }
               
                }
                            
              }else if (status_tra === 'Anulado') {
                status_color = 'red';
              }
              let scan_color = 'orange';
              if (item.scan_doc === 'ESCANEADO') {
                scan_color = 'green';
              }

              $('#saldotls_tbl_transacciones').append(
                '<tr>' +
                  '<td class="text-center">' +
                  item.fecha_creacion +
                  '</td>' +
                  '<td class="text-center">' +
                  item.tipo_tra +
                  '</td>' +
                  '<td class="text-center">' +
                  item.transaccion_id +
                  '</td>' +
                  '<td class="text-right">' +
                  item.monto +
                  '</td>' +
                  '<td class="text-center" style="color:' +
                  status_color +
                  ';">' +
                  status_tra +
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
            $('#saldotls_tbl_transacciones').append(
              '<tr>' +
                '<td colspan="7" class="text-center">NO HAY DATOS</td>' +
                '</tr>'
            );
          }
        } else {
          $('#saldotls_tbl_transacciones').append(
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

function saldotls_anular_transaccion(client_id, tipo_id, txn_id) {
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
        client_id : client_id,
        cc_id: saldotls_ccid
      };

      auditoria_send({ "proceso": "anular_transaccion", "data": send_data });
      $.post('/sys/set_saldo_tls.php', send_data)
        .done(function (data) {
          try {
            var respuesta = JSON.parse(data);
            if (parseInt(respuesta.http_code) === 200) {
              swal('Exito', 'La Transacción ha sido anulada.', 'success');
              saldotls_tbl_transacciones_listar();
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

function saldotls_ver_extorno(id_extorno) {
    var data = {};
    data.id_extorno = id_extorno;
        loading(true);
        $.post('/sys/set_saldo_tls.php', {
            accion: "ver_extorno",
            "ver_extorno": data
        }, function (resp) {
            data.response = resp;
            var response = jQuery.parseJSON(resp);
            loading();
            $("#modal_extorno").modal("show");
        });


}
function saldotls_ver_extorno(id_extorno){
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
                if( i == "saldo_tls_transaccion_id")
                {
                    style = " style='display:none'";
                }
				var html = '<div class="form-group"' + style + '>';
					html +=		'<label  class="col-xs-5 control-label" for="">';
					html += 		i;
					html +=		'</label>';
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
function saldotls_solicitar_extorno(id_saldo_tls) {
    var titulo = "¿Está seguro de realizar solicitud de extorno?";
    var save_data = {};
    save_data.id_saldo_tls = id_saldo_tls;
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
                $.post('/sys/set_saldo_tls.php', {
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
                            saldotls_tbl_transacciones_listar();
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
  if (sec_id === 'saldo_tls') {
    //Monto
    $('#saldotls_modal_deposito_monto').on({
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
    $('#saldotls_modal_deposito_btn_guardar').click(function () {
      saldotls_realizar_deposito();
    });
  }
});

function saldotls_nuevo_deposito() {
  $('#saldotls_modal_deposito_monto').val('');
  const data = {
    accion: 'verify_btns',
    btn: 'deposit',
  };
  $.ajax({
    url: '/sys/set_saldo_tls.php',
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
        $('#saldotls_modal_deposito').modal();
      }
      loading('false');
    },
    error: function () {},
  });
  //$('#saldotls_modal_deposito').modal();
}
function saldotls_realizar_deposito() {
  //console.log(saldotls_ccid);
  $('#saldotls_modal_deposito_btn_guardar').hide();

  var monto = parseFloat(
    $('#saldotls_modal_deposito_monto').val().replace(/\,/g, '')
  ).toFixed(2);
  if (!(parseFloat(monto) > 0)) {
    $('#saldotls_modal_deposito_monto').css('border', '1px solid red');
    $('#saldotls_modal_deposito_monto').focus();
    $('#saldotls_modal_deposito_btn_guardar').show();
    return false;
  }
  if (!(parseFloat(monto) >= 1.0 && parseFloat(monto) <= 3000.0)) {
    $('#saldotls_modal_deposito_monto').css('border', '1px solid red');
    $('#saldotls_modal_deposito_monto').focus();
    $('#saldotls_modal_deposito_btn_guardar').show();
    swal(
      'Aviso',
      'El monto debe ser mínimo de 1.00 y máximo de 3,000.00.',
      'warning'
    );
    return false;
  }
  var data = {
    accion: 'realizar_deposito',
    id_cli: $('#saldotls_id_cli').val(),
    tipo_doc: $('#saldotls_tipodoc_cli').val(),
    webid: $('#saldotls_webid_cli').val(),
    num_doc: $('#saldotls_numdoc_cliente').val(),
    client_name: $('#saldotls_cliente_name').html(),
    id_cajero: $('#id_cajero_tlv').val(),
    monto: monto,
    cc_id: saldotls_ccid,
  };

  auditoria_send({ "proceso": "deposito", "data": data });
  //console.log ("imprimir voucher");
  $.ajax({
    url: '/sys/set_saldo_tls.php',
    type: 'POST',
    data: data,
    beforeSend: function () {
      loading('true');
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      $('#saldotls_modal_deposito_btn_guardar').show();
      var respuesta = JSON.parse(resp);
      //console.log(respuesta);
      if (parseInt(respuesta.http_code) === 400) {
        $('#saldotls_modal_deposito').modal('hide');
        swal('Aviso', respuesta.status, 'warning');
        saldotls_tbl_transacciones_listar();
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {
        $('#saldotls_modal_deposito').modal('hide');
        $('#saldotls_cliente_buscador_div').hide();

        $('#deposito_txn').val(respuesta.operationId);
        var txn_id = $('#deposito_txn').val();
        //console.log(txn_id);
        //saldo_tls_imprimir_voucher_exacto(respuesta.cod_transaccion, 1);
      //  printOnlySignature_saldo_tls(respuesta.cod_transaccion, 1, 0);

        saldotls_tbl_transacciones_listar();
        swal('Aviso', 'El depósito fue exitoso.', 'success');
        return false;
      }
    },
    error: function () {},
  });
}

function saldo_tls_imprimir_voucher_exacto(txn_id, tipo_id) {
  var data1 = {
    accion: 'obtener_transaccion',
    txn_id: txn_id,
    tipo_id: tipo_id,
  };
  //auditoria_send({ "proceso": "obtener_cliente", "data": data });
  $.ajax({
    url: '/sys/set_saldo_tls.php',
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
      console.log(respuesta);
      if (parseInt(respuesta.http_code) === 400) {
        swal('Aviso', respuesta.status, 'warning');
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {
        if (parseInt(tipo_id) === 1) {
          $('#modal_saldotls_deposito_voucher_nrecibo').html(
            txn_id
          );
          $('#modal_saldotls_deposito_voucher_fechahora').html(
            respuesta.result.created_at
          );
          $('#modal_saldotls_deposito_voucher_datosCliente').html(
            respuesta.result.client_name + ' - ' + respuesta.result.client_id
          );
          $('#modal_saldotls_deposito_voucher_monto').html(
            respuesta.result.monto + ' PEN'
          );
          $('#modal_saldotls_deposito_voucher_tienda').html(
            respuesta.result.local_nombre
          );
          $('#modal_saldotls_deposito_voucher_direccion').html(
            respuesta.result.local_direccion
          );
          $('#div_voucher_saldo_tls').html('');
          //if ([6,9,10].includes(parseInt(saldotls_area_id))) {
          $('#div_voucher_saldo_tls').html(
            '<button id="sec_tlv_copiar_voucher_apuesta_pagada" type="button" ' +
              'class="btn btn-success btn-sm pull-right printOnlySignature_saldo_tls" ' +
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
          $('#modal_saldotls_deposito_voucher').modal();
        }
        if (parseInt(tipo_id) === 2) {
          $('#modal_saldotls_retiro_voucher_nrecibo').html(
            txn_id
          );
          $('#modal_saldotls_retiro_voucher_fechahora').html(
            respuesta.result.created_at
          );
          $('#modal_saldotls_retiro_voucher_nombreCompleto').html(
            respuesta.result.client_name
          );
          $('#modal_saldotls_retiro_voucher_DNI').html(
            respuesta.result.client_num_doc
          );
          $('#modal_saldotls_retiro_voucher_jugadorID').html(
            respuesta.result.client_id
          );
          $('#modal_saldotls_retiro_voucher_monto').html(
            respuesta.result.monto + ' PEN'
          );
          $('#modal_saldotls_retiro_voucher_tienda').html(
            respuesta.result.local_nombre
          );
          $('#modal_saldotls_retiro_voucher_direccion').html(
            respuesta.result.local_direccion
          );
          $('#div_voucher_saldo_tls_retiro').html('');
          //if ([6,9,10].includes(parseInt(saldotls_area_id))) {
          $('#div_voucher_saldo_tls_retiro').html(
            '<button id="sec_tlv_copiar_voucher_apuesta_pagada_retiro" type="button" ' +
              'class="btn btn-success btn-sm pull-right printOnlySignature_saldo_tls_retiro" ' +
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
          $('#modal_saldotls_retiro_voucher').modal();
        }
if(parseInt(tipo_id)===9){
                    $('#modal_saldotls_retiro_voucher_nrecibo').html(respuesta.result.txn_id);
                    $('#modal_saldotls_retiro_voucher_fechahora').html(respuesta.result.created_at);
                    $('#modal_saldotls_retiro_voucher_nombreCompleto').html(respuesta.result.client_name);
                    $('#modal_saldotls_retiro_voucher_DNI').html(respuesta.result.client_num_doc);
                    $('#modal_saldotls_retiro_voucher_jugadorID').html(respuesta.result.client_id);
                    $('#modal_saldotls_retiro_voucher_monto').html(respuesta.result.monto+" PEN");
                    $('#modal_saldotls_retiro_voucher_tienda').html(respuesta.result.local_nombre);
                    $('#modal_saldotls_retiro_voucher_direccion').html(respuesta.result.local_direccion);
                    $('#div_voucher_saldo_tls_retiro').html('');
                    //if ([6,9,10].includes(parseInt(saldotls_area_id))) {
                        $('#div_voucher_saldo_tls_retiro').html('<button id="sec_tlv_copiar_voucher_apuesta_pagada_retiro" type="button" '
                            +'class="btn btn-success btn-sm pull-right printOnlySignature_saldo_tls_retiro" '
                            +'data-txn_id-paper="' + txn_id + '" '
                            +'data-tipo_id-paper="' + tipo_id + '" >'
                            +'<b><i class="fa fa-print"></i> Imprimir</b>'
                            +'</button>');
                    //}
                    $('#modal_saldotls_retiro_voucher').modal();
                }        return false;
      }
    },
    error: function () {
      return false;
    },
  });
}
var saldo_tls_puedeDigitarDoc = false;
//*******************************************************************************************************************
//*******************************************************************************************************************
// REALIZAR RETIRO
//*******************************************************************************************************************
//*******************************************************************************************************************
$(function () {
  if (sec_id === 'saldo_tls') {
    $('#saldotls_modal_retiro_btn_guardar').click(function () {
      saldotls_verificar_cantidad_retiro_diario();
    });
    $('#saldotls_btn_change_disabled_to_input_num_doc').click(function () {
      const inputDocNum = document.getElementById(
        'saldotls_modal_retiro_numdoc'
      );
      if (!saldo_tls_puedeDigitarDoc) {
        //inputDocNum.disabled = false;
        const observacion = document.getElementById(
          'saldotls_modal_div_retiro_observacion'
        );
        observacion.style.display = 'block';
        $('#saldotls_modal_retiro_numdoc').val('');
        saldo_tls_puedeDigitarDoc = true;
      }
    });
    $('#saldotls_modal_retiro_numdoc').keypress(function () {
      console.log('keypress');
      if (!saldo_tls_puedeDigitarDoc) {
        delay(function () {
          if ($('#saldotls_modal_retiro_numdoc').val().length < 8) {
            $('#saldotls_modal_retiro_numdoc').val('');
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


function saldotls_nuevo_retiro() {
  $('#saldotls_modal_div_retiro_observacion').hide();
  $('#saldotls_modal_div_retiro_tra').hide();
  //document.getElementById('saldotls_modal_retiro_numdoc').disabled = true;
  saldo_tls_puedeDigitarDoc = false;
  $('#saldotls_modal_retiro_numdoc').val('');

  var data = {
    accion: 'consultar_retiro',
    id_cli: $('#saldotls_id_cli').val(),
    cc_id: saldotls_ccid,
  };
  //auditoria_send({ "proceso": "obtener_cliente", "data": data });
  $.ajax({
    url: '/sys/set_saldo_tls.php',
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
        
        $('#saldotls_modal_retiro').modal({backdrop: 'static', keyboard: false});
        $('#saldotls_modal_retiro_monto').val('');
        $('#saldotls_modal_retiro_solicitud').val('0');        
        $('#saldotls_modal_retiro_transaccion').val('');
        $('#saldotls_modal_retiro_nuevo_b').val('');
        $('#saldotls_modal_retiro_monto').removeAttr('disabled');
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {       
       // swal('Aviso', respuesta.status, 'info');

        $('#saldotls_modal_solic_retiro_transacciones').html(
          '<thead>' +
            '<tr>' +
            '   <th class="text-center">Fecha</th>' +
            '   <th class="text-center">Número de Ticket</th>' +             
            '   <th class="text-center">Local</th>' +
            '   <th class="text-center">Acción</th>' +
            '</tr>' +
            '</thead>' +
            '<tbody>'
        );
      
        $.each(respuesta.data, function (index, item) {
      
          $('#saldotls_modal_solic_retiro_transacciones').append(
            '<tr>' +
            '<td class="text-center">' +
            item.created_at +
            '</td>' +  
            '<td class="text-center">' +
            item.id +
            '</td>' +
            '<td class="text-center">' +
            respuesta.name +
            '</td>' +
            '<td class="text-center"><button type="button" class="btn btn-primary" style="padding: 2px 3px;"' +
            '    onclick="saldotls_modal_solic_retiro_solicitud(' +
            item.id +
            ')">' +
            '<span class="fa fa-eye"></span>' +
            '</button></td>' +
            '</tr>'
          );
        });

        $('#saldotls_modal_solic_retiro').modal({backdrop: 'static', keyboard: false});
        return false;
      }
    },
    error: function () {},
  });
}


function saldotls_modal_retiro_btn_cerrar() {
  id_trans = $('#saldotls_modal_retiro_transaccion').val();
  solicitud = $('#saldotls_modal_retiro_solicitud').val();

  var data = {
    accion: 'update_solic_retiro',
    id_trans: id_trans,
    solicitud: solicitud
  };
  //auditoria_send({ "proceso": "obtener_cliente", "data": data });
  $.ajax({
    url: '/sys/set_saldo_tls.php',
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
        //swal('Aviso', respuesta.status, 'error');
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {
       // swal('Aviso', respuesta.status, 'info');
        return false;
      }
    },
    error: function () {},
  });
}


function saldotls_modal_solic_retiro_btn_cerrar() {
  $('#saldotls_modal_solic_retiro').modal('hide');

  $('#saldotls_modal_retiro').modal({backdrop: 'static', keyboard: false});
  $('#saldotls_modal_retiro_monto').val('');
  $('#saldotls_modal_retiro_solicitud').val('0');        
  $('#saldotls_modal_retiro_transaccion').val('');
  $('#saldotls_modal_retiro_nuevo_b').val('');
  $('#saldotls_modal_retiro_monto').removeAttr('disabled');
  $('#saldotls_modal_retiro_btn_guardar').show();
 
}

function saldotls_modal_solic_retiro_solicitud(id) {
  $('#saldotls_modal_div_retiro_observacion').hide();

  const id_trans = document.getElementById('saldotls_modal_div_retiro_tra');
  id_trans.style.display = 'block';

  $('#saldotls_modal_retiro_monto').attr('disabled', 'disabled');
 
  //document.getElementById('saldotls_modal_retiro_numdoc').disabled = true;
  saldo_tls_puedeDigitarDoc = false;
  $('#saldotls_modal_retiro_numdoc').val('');

  var data = {
    accion: 'consultar_solic_retiro',
    id: id,
    cc_id: saldotls_ccid,
  };
  //auditoria_send({ "proceso": "obtener_cliente", "data": data });
  $.ajax({
    url: '/sys/set_saldo_tls.php',
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
        swal('Aviso', respuesta.status, 'info');
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {
 
        $('#saldotls_modal_solic_retiro').modal('hide');
 
        $('#saldotls_modal_retiro_transaccion').val(id);
        $('#saldotls_modal_retiro_nuevo_b').val(respuesta.nuevo_balance);
        $('#saldotls_modal_retiro_solicitud').val('1');
        $('#saldotls_modal_retiro_monto').val(respuesta.data);
        
        $('#saldotls_modal_retiro').modal({backdrop: 'static', keyboard: false});
        return false;
      }
    },
    error: function () {},
  });
}

function saldotls_realizar_retiro() {
  var id_trans = $('#saldotls_modal_retiro_transaccion').val();
  var solicitud = $('#saldotls_modal_retiro_solicitud').val();
  $('#saldotls_modal_retiro_btn_guardar').hide();
  $('#saldotls_modal_retiro_numdoc').css('border', '');
  $('#saldotls_modal_retiro_numdoc').focus();

  var num_doc = $('#saldotls_modal_retiro_numdoc').val();
  if (!(num_doc.length > 0)) {
    $('#saldotls_modal_retiro_numdoc').css('border', '1px solid red');
    $('#saldotls_modal_retiro_numdoc').focus();
    $('#saldotls_modal_retiro_btn_guardar').show();
    return false;
  }

  var monto = parseFloat(
    $('#saldotls_modal_retiro_monto').val().replace(/\,/g, '')
  ).toFixed(2);

  if (id_trans==''){
    if (!(parseFloat(monto) > 0)) {
      $('#saldotls_modal_retiro_monto').css('border', '1px solid red');
      $('#saldotls_modal_retiro_monto').focus();
      $('#saldotls_modal_retiro_btn_guardar').show();
      return false;
    }
    if (!(parseFloat(monto) >= 1.0 && parseFloat(monto) <= 100.0)) {
      $('#saldotls_modal_retiro_monto').css('border', '1px solid red');
      $('#saldotls_modal_retiro_monto').focus();
      $('#saldotls_modal_retiro_btn_guardar').show();
      swal(
        'Aviso',
        'El monto debe ser mínimo de 1.00 y máximo de 100.00. Para montos mayores debe realizar una solicitud.',
        'warning'
      );
      return false;
    } 
  }
  
  const inputDocNum = document.getElementById('saldotls_modal_retiro_numdoc');
  var observacion = '';
  if (saldo_tls_puedeDigitarDoc) {
    observacion = $('#saldotls_modal_retiro_observacion').val();
    $('#saldotls_modal_retiro_observacion').val(observacion);
    if (
      observacion === null ||
      observacion === '' ||
      observacion === undefined
    ) {
      alertify.warning('Debe ingresar la observación', 5);
      $('#saldotls_modal_retiro_observacion').css('border', '1px solid red');
      $('#saldotls_modal_retiro_observacion').focus();
      $('#saldotls_modal_retiro_btn_guardar').show();
      return false;
    }
  }
  if (!saldo_tls_puedeDigitarDoc) {
    //$('#saldotls_modal_retiro_observacion').val(null);
    observacion = undefined;
  }
  var data = {
    accion: 'realizar_retiro',
    id_cli: $('#saldotls_id_cli').val(),
    num_doc: $('#saldotls_numdoc_cliente').val(),
    tipo_doc: $('#saldotls_tipodoc_cli').val(),
    web_id: $('#saldotls_webid_cli').val(),
    client_name: $('#saldotls_cliente_name').html(),
    num_doc_ing: num_doc,
    monto: monto,
    solicitud:solicitud,
    cc_id: saldotls_ccid,
    id_transaccion: $('#saldotls_modal_retiro_transaccion').val(),
    nuevo_balance: $('#saldotls_modal_retiro_nuevo_b').val(),
    scan_doc: saldo_tls_puedeDigitarDoc ? '2' : '1',
    observacion: observacion,
  }; //scan_doc 1 = escaneado, 2 = digitado
  //console.log(data);
  
  auditoria_send({ "proceso": "retiro", "data": data });

  $.ajax({
    url: '/sys/set_saldo_tls.php',
    type: 'POST',
    data: data,
    beforeSend: function () {
      loading('true');
    },
    complete: function () {
      loading();
    },
    success: function (resp) {
      $('#saldotls_modal_retiro_btn_guardar').show();
      var respuesta = JSON.parse(resp);
     // console.log(respuesta);
      if (parseInt(respuesta.http_code) === 400) {
        swal('Aviso', respuesta.status, 'warning');
        saldotls_tbl_transacciones_listar();
        return false;
      }
      if (parseInt(respuesta.http_code) === 200) {
        $('#saldotls_modal_retiro').modal('hide');

        //saldo_tls_imprimir_voucher_exacto(respuesta.cod_transaccion, 2);
       // printOnlySignature_saldo_tls_retiro(respuesta.cod_transaccion, 2, 0);

        saldotls_tbl_transacciones_listar();
        swal('Aviso', 'El retiro fue exitoso.', 'success');
        return false;
      }
    },
    error: function () {},
  });
}


function saldotls_verificar_cantidad_retiro_diario() {

  var id_trans = $('#saldotls_modal_retiro_transaccion').val();

  $('#saldotls_modal_retiro_btn_guardar').hide();
  $('#saldotls_modal_retiro_numdoc').css('border', '');
  $('#saldotls_modal_retiro_numdoc').focus();

  var num_doc = $('#saldotls_modal_retiro_numdoc').val();
  if (!(num_doc.length > 0)) {
    $('#saldotls_modal_retiro_numdoc').css('border', '1px solid red');
    $('#saldotls_modal_retiro_numdoc').focus();
    $('#saldotls_modal_retiro_btn_guardar').show();
    return false;
  }

  var monto = parseFloat(
    $('#saldotls_modal_retiro_monto').val().replace(/\,/g, '')
  ).toFixed(2);

  if (id_trans==''){
    if (!(parseFloat(monto) > 0)) {
      $('#saldotls_modal_retiro_monto').css('border', '1px solid red');
      $('#saldotls_modal_retiro_monto').focus();
      $('#saldotls_modal_retiro_btn_guardar').show();
      return false;
    }
    if (!(parseFloat(monto) >= 1.0 && parseFloat(monto) <= 100.0)) {
      $('#saldotls_modal_retiro_monto').css('border', '1px solid red');
      $('#saldotls_modal_retiro_monto').focus();
      $('#saldotls_modal_retiro_btn_guardar').show();
      swal(
        'Aviso',
        'El monto debe ser mínimo de 1.00 y máximo de 100.00. Para montos mayores debe realizar una solicitud.',
        'warning'
      );
      return false;
    } 

    var data = {
      accion: 'verificar_cantidad_retiro_diario',
      id_cli: $('#saldotls_id_cli').val(),
      monto : monto
    }; 
    auditoria_send({ "proceso": "verificar_cantidad_retiro_diario", "data": data });
    $.ajax({
      url: '/sys/set_saldo_tls.php',
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
          $('#saldotls_modal_retiro').modal('hide');
          return false;
        }

        if (parseInt(respuesta.http_code) === 300) {
          swal('Aviso', respuesta.status, 'warning');
          $('#saldotls_modal_retiro_monto').focus();
          $('#saldotls_modal_retiro_btn_guardar').show();
          return false;
        }

        if (parseInt(respuesta.http_code) === 200) {
  
          saldotls_realizar_retiro();
        }
      },
      error: function () {},
    });
  }else{
    saldotls_realizar_retiro();
  }
 
  
}









//*******************************************************************************************************************
//*******************************************************************************************************************
// VOUCHER DEPÓSITO
//*******************************************************************************************************************
//*******************************************************************************************************************

$('body').on('click', '.printOnlySignature_saldo_tls', function () {
  var txn_id = $(this).attr('data-txn_id-paper');
  var tipo_id = $(this).attr('data-tipo_id-paper');
  printOnlySignature_saldo_tls(txn_id, tipo_id, 1);
});

const setImagenFirma_tls = (archivo) => {
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
const printOnlySignature_saldo_tls = (txn_id, tipo_id, valid_reimpresion) => {
  loading(true);
  var obj = {};
  var retorn = {};
  var data = {};
  var tiposDoc = ['DNI', 'CE/PTP', 'Pasaporte'];

  data.txn_id = txn_id;
  data.tipo_id = tipo_id;

  // console.log(imgbase64);
  $.post(
    '/sys/set_saldo_tls.php',
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
          doc.myText('Depósito TLS Apuesta Total', { align: 'center' }, 0, 22);
          doc.myText('Copia de Cajero', { align: 'center' }, 0, 26);
        } else if (obj.direccion.length < 134) {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 16, 73, 16);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito TLS Apuesta Total', { align: 'center' }, 0, 23);
          doc.myText('Copia de Cajero', { align: 'center' }, 0, 27);
        } else if (obj.direccion.length < 201) {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 18, 73, 18);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito TLS Apuesta Total', { align: 'center' }, 0, 24);
          doc.myText('Copia de Cajero', { align: 'center' }, 0, 28);
        } else {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 20.5, 73, 20.5);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito TLS Apuesta Total', { align: 'center' }, 0, 25);
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
        //doc.text(20, 61, 'Depósito TLS en Apuesta Total')
        doc.myText('Depósito TLS en Apuesta Total', { align: 'center' }, 0, 61);
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
          doc.myText('Depósito TLS Apuesta Total', { align: 'center' }, 0, 22);
          doc.myText('Copia de Cliente', { align: 'center' }, 0, 26);
        } else if (obj.direccion.length < 134) {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 16, 73, 16);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito TLS Apuesta Total', { align: 'center' }, 0, 23);
          doc.myText('Copia de Cliente', { align: 'center' }, 0, 27);
        } else if (obj.direccion.length < 201) {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 18, 73, 18);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito TLS Apuesta Total', { align: 'center' }, 0, 24);
          doc.myText('Copia de Cliente', { align: 'center' }, 0, 28);
        } else {
          doc.text(obj.direccion, 40, 12, { align: 'center', maxWidth: 68 }); //Centrar y máximo de espaciado
          doc.line(6, 20.5, 73, 20.5);

          doc.setFontType('bold');
          doc.setFontSize(8);
          doc.myText('Depósito TLS Apuesta Total', { align: 'center' }, 0, 25);
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
        doc.myText('Depósito TLS en Apuesta Total', { align: 'center' }, 0, 61);
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

$('body').on('click', '.printOnlySignature_saldo_tls_retiro', function () {
  var txn_id = $(this).attr('data-txn_id-paper');
  var tipo_id = $(this).attr('data-tipo_id-paper');
  printOnlySignature_saldo_tls_retiro(txn_id, tipo_id, 1);
});

/* SOLO HACE printOnlySignature DE LOS REGISTROS CON FIRMA */
const printOnlySignature_saldo_tls_retiro = (
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
    '/sys/set_saldo_tls.php',
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
