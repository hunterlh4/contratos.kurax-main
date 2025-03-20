function sec_recaudacion_transacciones_bancarias() {
  console.log('sec_recaudacion_transacciones_bancarias');
  var input_file = false;
  sec_rtb_events();
  var highlight_this_item = localStorage.getItem('highlight_this_item');
  localStorage.removeItem('highlight_this_item');
  $(highlight_this_item).addClass('bg-success', 1000);
  window.setTimeout(function () {
    $(highlight_this_item).stop().animate(
      {
        backgroundColor: '#FFFFFF',
      },
      1000
    );
  }, 1000);
  // $("#table_transacciones_bancarias").DataTable({
  // 	"order": [[ 1, "desc" ]]
  // });
}
function sec_rtb_events() {
  console.log('sec_rtb_events');

  $('.transacciones_bancarias_btn')
    .off()
    .click(function (event) {
      var btn_data = $(this).data();
      console.log(btn_data);
      sec_rtb_modal(btn_data.opt);

      // TESTING
      // var test_data = {};
      // 	test_data.banco_id = 12;
      // 	test_data.moneda_id = 1;
      // 	test_data.file_id = 1891;
      // sec_rtb_process_file(test_data);
      // /TESTING
    });
  // $(".transacciones_bancarias_btn").last().click();

  // transaccion_bancaria_modal makeme_datepicker
  $('#transaccion_bancaria_modal .makeme_datepicker')
    .datepicker({
      dateFormat: 'dd-mm-yy',
      changeMonth: true,
      changeYear: true,
    })
    .on('change', function (ev) {
      $(this).datepicker('hide');
      var newDate = $(this).datepicker('getDate');
      $('input[data-real-date=' + $(this).attr('id') + ']').val(
        $.format.date(newDate, 'yyyy-MM-dd')
      );
    });

  $('.close_btn')
    .off()
    .click(function (event) {
      sec_rtb_modal();
    });
  $('#transaccion_bancaria_modal .save_btn')
    .off()
    .click(function (event) {
      sec_rtb_save();
    });
  $('.trans_view_btn')
    .off()
    .click(function (event) {
      var view_data = {};
      view_data.opt = 'assig';
      view_data.ids = {};
      view_data.ids[0] = $(this).data('id');
      sec_rtb_view(view_data);

      // sec_rtb_view($(this).data());
    });
  // $(".trans_view_btn").first().click();
  // var trans_view_btn_data = {};
  // trans_view_btn_data.id = '2e4676e8753bb6df318260cf78b9cab9';
  // sec_rtb_view(trans_view_btn_data);

  $('.trans_ass_btn')
    .off()
    .click(function (event) {
      var view_data = {};
      view_data.opt = 'assig';
      view_data.ids = {};
      view_data.ids[0] = $(this).data('id');
      sec_rtb_assig_modal(view_data);
    });
  // $(".trans_ass_btn").first().click();

  $('.trans_ass_batch_btn')
    .off()
    .click(function (event) {
      var view_data = {};
      view_data.opt = 'assig';
      view_data.ids = {};
      // view_data.ids[0]=$(this).data("id");

      // var view_data = {};
      // view_data.ids = {}
      $('.checkbox_me').each(function (index, el) {
        var checkbox = $(el).find('input[type=checkbox]');
        if (checkbox.is(':checked')) {
          console.log();
          view_data.ids[index] = checkbox.data('id');
        }
      });

      sec_rtb_assig_modal(view_data);
    });

  $('.trans_hide_btn, .trans_show_btn')
    .off()
    .click(function (event) {
      sec_rtb_hide($(this).data());
    });

  $('#transaccion_bancaria_modal')
    .on('show.bs.modal', function () {
      // $('#myInput').focus()
    })
    .on('hide.bs.modal', function () {
      $('input[type=text].save_item, textarea.save_item')
        .removeAttr('readonly')
        .val('');
      $('.radio_banco_id')
        .removeAttr('checked')
        .removeAttr('disabled')
        .parent('label')
        .removeClass('active')
        .removeClass('hidden');
      $('#radio_banco_id_12')
        .stop()
        .prop('checked', 'checked')
        .parent()
        .addClass('active');
      $('.radio_moneda_id')
        .removeAttr('checked')
        .removeAttr('disabled')
        .parent('label')
        .removeClass('active')
        .removeClass('hidden');
      $('#radio_moneda_id_1')
        .stop()
        .prop('checked', 'checked')
        .parent()
        .addClass('active');
      $('#transaccion_bancaria_modal .makeme_datepicker')
        .datepicker('setDate', new Date())
        .trigger('change')
        .datepicker('option', 'disabled', false);
      $('.selectpicker.save_item').selectpicker('val', 0);
      $('.save_item').removeAttr('disabled');
      $('.save_item')
        .parents('.form-group')
        .removeClass('has-error')
        .removeClass('has-success');
    });
  $('#transaccion_bancaria_import_modal').on('show.bs.modal', function () {
    $('#transaccion_bancaria_import_modal .radio_banco_id')
      .removeAttr('checked')
      .removeAttr('disabled')
      .parent('label')
      .removeClass('active')
      .removeClass('hidden');
    $('#transaccion_bancaria_import_modal #radio_banco_id_12')
      .stop()
      .prop('checked', 'checked')
      .parent()
      .addClass('active');
    $('#transaccion_bancaria_import_modal .radio_moneda_id')
      .removeAttr('checked')
      .removeAttr('disabled')
      .parent('label')
      .removeClass('active')
      .removeClass('hidden');
    $('#transaccion_bancaria_import_modal #radio_moneda_id_1')
      .stop()
      .prop('checked', 'checked')
      .parent()
      .addClass('active');
  });

  $('.import_btn')
    .off()
    .click(function (event) {
      sec_rtb_import();
    });
  $('.trans_add_local_btn')
    .off()
    .click(function (event) {
      console.log('trans_add_local_btn:click');
      trans_add_local();
    });
  $('.make_me_select2').select2();

  $('.checkbox_me')
    .off()
    .click(function (event) {
      var checkbox = $(this).find('input[type=checkbox]');
      var checkbox_icon = $(this).find('.checkbox_icon');
      if (checkbox.is(':checked')) {
        checkbox.prop('checked', false);
        checkbox_icon
          .removeClass('glyphicon-check')
          .addClass('glyphicon-unchecked');
        checkbox.closest('.trans_item').removeClass('bg-success');
      } else {
        checkbox.prop('checked', true);
        checkbox_icon
          .removeClass('glyphicon-unchecked')
          .addClass('glyphicon-check');
        checkbox.closest('.trans_item').addClass('bg-success');
      }
    });

  // $(".checkbox_me:eq(0)").click();
  // $(".checkbox_me:eq(1)").click();
  // $(".checkbox_me:eq(2)").click();
  // $(".checkbox_me:eq(3)").click();
  // $(".checkbox_me:eq(4)").click();
  // $(".checkbox_me:eq(5)").click();
  // $(".checkbox_me:eq(6)").click();
  // $(".checkbox_me:eq(7)").click();
  // $(".checkbox_me:eq(8)").click();
  // $(".checkbox_me:eq(9)").click();

  // $(".transacciones_bancarias_btn[data-opt=assig]").click();

  // $("#table_transacciones_bancarias .trans_item").click(function(event) {
  // 	event.preventDefault();
  // 	var checkbox = $(this).find("input[type=checkbox]");
  // 0var checkbox_icon = $(this).find(".checkbox_icon");
  // 	console.log(checkbox);
  // 	if(checkbox.is(':checked')){
  // 		checkbox.prop('checked', false);
  // 		checkbox_icon.removeClass('glyphicon-check').addClass('glyphicon-unchecked');
  // 		$(this).removeClass('bg-success');
  // 	}else{
  // 		checkbox.prop('checked', true);
  // 		checkbox_icon.removeClass('glyphicon-unchecked').addClass('glyphicon-check');
  // 		$(this).addClass('bg-success');
  // 	}
  // });

  // recaudacion_transacciones_bancarias_locales_list_table
  $('#recaudacion_transacciones_bancarias_locales_list_table_search')
    .off()
    .val('')
    .on('change keyup paste click', function () {
      var search_input = $(this);
      var searchTerm = force_plain_text($(this).val());
      // console.log(searchTerm);
      var holder_id = $(this).data('holder');
      // console.log(holder_id);
      $('#' + holder_id + ' tr')
        .stop()
        .hide();
      $('#' + holder_id + ' td').each(function (index, el) {
        var h3_text = force_plain_text($(el).html());
        // console.log(h3_text);
        var n = h3_text.indexOf(searchTerm);
        if (n >= 0) {
          $(el).parent('tr').stop().show();
        }
      });
      localStorage.setItem(
        'recaudacion_transacciones_bancarias_locales_list_table_search',
        $(this).val()
      );

      $(this)
        .parent()
        .find('.search_clear_btn')
        .off()
        .click(function (event) {
          console.log('search_clear_btn:click');
          search_input.val('').change().focus();
        });
    })
    .val(
      localStorage.getItem(
        'recaudacion_transacciones_bancarias_locales_list_table_search'
      )
    )
    .change();
  // .click()
  // .focus()
  // $(".list_search_input");
  // $(".recaudacion_transacciones_bancarias_locales_list_table_clear_btn")
  // 	.off()
  // 	.click(function(event) {
  // 		console.log("recaudacion_transacciones_bancarias_locales_list_table_clear_btn:click");
  // 		$("#recaudacion_transacciones_bancarias_locales_list_table_search").val("").change().focus();
  // 	});
}

function sec_rtb_assig_modal(api_data) {
  console.log('sec_rtb_assig_modal');
  if (api_data == 'hide') {
    $('#sec_rtb_assig_modal').modal('hide');
    $('#un_used_local_list_holder').html('');
  } else {
    console.log(api_data);
    // var api_data = view_data;
    api_data.opt = 'assig';
    loading(true);
    $.post(
      'api/?html',
      {
        where: 'transaccion_bancaria',
        data: api_data,
      },
      function (r) {
        try {
          $('#un_used_local_list_holder').html(r);
          $('#sec_rtb_assig_modal').modal('show');
          sec_rtb_assig_modal_events();
          loading();
        } catch (err) {
          ajax_error(true, r, err); //opt,response,catch-error
        }
        // auditoria_send({"proceso":"sec_rtb_load_periodos","data":api_data});
      }
    );
  }
}
function asc_sort(a, b) {
  return $(b).text() < $(a).text() ? 1 : -1;
}
function dec_sort(a, b) {
  return $(b).text() > $(a).text() ? 1 : -1;
}
function sec_rtb_assig_modal_events() {
  console.log('sec_rtb_assig_modal_events');

  $('#sec_rtb_assig_modal .close_btn')
    .off()
    .click(function (event) {
      sec_rtb_assig_modal('hide');
    });
  $('#list_used li').sort(asc_sort).appendTo('#list_used');
  $('#list_unused li').sort(asc_sort).appendTo('#list_unused');

  // if($( "#list_unused, #list_used" ).sortable( "instance" )){
  // 	$( "#list_unused, #list_used" ).sortable( "destroy" );
  // }
  // $( "#list_unused, #list_used" ).sortable({
  // 	connectWith: ".un_used_local_list"
  // }).disableSelection();
  $('.move_btn')
    .off()
    .click(function (event) {
      var btn = $(this);
      var itm = $(this).parent('li');
      var new_itm = itm.clone();
      itm.remove();
      if (btn.hasClass('move_right_btn')) {
        console.log('hasClass:move_right_btn');
        // btn.addClass('class_name')
        $('#list_used').append(new_itm);
        new_itm.find('.move_left_btn').removeClass('hidden');
        new_itm.find('.move_right_btn').addClass('hidden');
        // $("#list_used").sortable("refresh");
      } else {
        console.log('NO hasClass:move_right_btn');
        $('#list_unused').append(new_itm);
        new_itm.find('.move_right_btn').removeClass('hidden');
        new_itm.find('.move_left_btn').addClass('hidden');
        // $("#list_unused").sortable("refresh");
      }
      sec_rtb_assig_modal_events();
    });

  $('.single_searcher').each(function (index, el) {
    var search_input = $(this);
    var holder_id = $(this).data('holder_id');
    var item_class = $(this).data('item_class');
    var item_where = $(this).data('where');
    var search_clear_btn = $(this).parent().find('.search_clear_btn');
    search_clear_btn.off().click(function (event) {
      search_input.val('').change().focus();
    });
    search_input
      .off()
      .on('change keyup paste click', function () {
        var searchTerm = force_plain_text(search_input.val());
        $('#' + holder_id + ' .' + item_class).each(function (index, itm) {
          $(itm).stop().hide();
          var item_text = force_plain_text(
            $(itm)
              .find('.' + item_where)
              .html()
          );
          var n = item_text.indexOf(searchTerm);
          if (n >= 0) {
            $(itm).stop().show();
          }
        });
      })
      .click();
  });

  $('.assig_save_btn')
    .off()
    .click(function (event) {
      sec_rtb_assig_save();
    });
}
function sec_rtb_assig_save() {
  console.log('sec_rtb_assig_save');
  loading(true);

  var save_data = {};
  save_data.trans = {};
  $('#un_used_local_list_holder .trans_item').each(function (index, el) {
    save_data.trans[index] = $(el).data('at_unique_id');
  });
  save_data.locales = {};
  $('#un_used_local_list_holder #list_used .itm_local').each(function (
    index,
    el
  ) {
    save_data.locales[index] = $(el).data('local_id');
  });
  // num_locales = Object.keys(save_data.locales).length;
  // console.log(num_locales);
  // if(num_locales){
  $.post(
    'sys/set_data.php',
    {
      opt: 'sec_rtb_assig_save',
      data: save_data,
    },
    function (r) {
      try {
        var obj = jQuery.parseJSON(r);
        console.log(obj);
        loading();
        swal(
          {
            title: 'Guardado!',
            text: '',
            type: 'success',
            timer: 600,
            closeOnConfirm: false,
          },
          function () {
            swal.close();
            m_reload();
          }
        );
      } catch (err) {
        console.log(r);
        // ajax_error(true,r,err);//opt,response,catch-error
      }
      // auditoria_send({"proceso":"recaudacion_transbanc_save","data":save_data});
    }
  );
  // }else{
  // 	loading();
  // 	swal({
  // 		title: 'Error',
  // 		text: 'Seleccione al menos un local.',
  // 		type: "warning",
  // 		timer: 2000,
  // 	}, function(){
  // 		swal.close();
  // 	});
  // }

  console.log(save_data);
}

function trans_add_local() {
  console.log('trans_add_local');

  var trans_importe = Number($('#label_importe').html());

  var new_local_nombre = $('#select_new_local_id').find(':selected').html();
  var new_local_id = $('#select_new_local_id').find(':selected').val();
  var new_monto = Number($('#input_new_monto').val());

  var sum_montos = 0;

  if (new_local_id) {
    if (new_monto) {
      $('.new_monto').each(function (index, el) {
        sum_montos += Number($(el).html());
      });
      console.log(sum_montos + new_monto);
      if (sum_montos + new_monto > trans_importe) {
        swal(
          {
            title: 'Error',
            text: 'La suma de los montos no puede superar el importe bancario.',
            type: 'warning',
            timer: 2000,
          },
          function () {
            swal.close();
            loading();
            $('#input_new_monto').parent('.form-group').addClass('has-error');
            $('#input_new_monto').focus();
          }
        );
      } else {
        $('#input_new_monto').parents('.form-group').removeClass('has-error');
        var new_tr = $('<tr class="count_me">');
        new_tr.append(
          '<td class="new_local" data-local_id="' +
            new_local_id +
            '">' +
            new_local_nombre +
            '</td>'
        );
        new_tr.append(
          '<td class="new_monto">' + new_monto.toFixed(2) + '</td>'
        );
        new_tr.append(
          '<td><button class="btn btn-xs btn-warning trans_rem_local_btn"><i class="glyphicon glyphicon-remove"></i></button></td>'
        );
        console.log(new_tr);
        $('#trans_div_holder tbody').append(new_tr);
        $('.trans_rem_local_btn')
          .off()
          .click(function (event) {
            console.log('trans_rem_local_btn:click');
            $(this).closest('tr').remove();
          });
      }
    } else {
      swal(
        {
          title: 'Error',
          text: 'Ingrese un monto mayor a 0',
          type: 'warning',
          timer: 600,
        },
        function () {
          swal.close();
          loading();
          $('#input_new_monto').parent('.form-group').addClass('has-error');
          $('#input_new_monto').focus();
        }
      );
    }
  } else {
    swal(
      {
        title: 'Error',
        text: 'Seleccione un local',
        type: 'warning',
        timer: 600,
      },
      function () {
        swal.close();
        loading();
        $('#select_new_local_id').select2('open');
      }
    );
  }
}
function sec_rtb_load_local_deuda(btn) {
  console.log('sec_rtb_load_local_deuda');
  // console.log(btn);
  loading(true);
  var get_data = {};
  get_data.year = $('#select_year').val();
  get_data.mes = $('#select_mes').val();
  get_data.rango = $('#select_periodo').val();
  get_data.local_id = $(btn).data('local_id');
  $.post(
    '/sys/get_recaudacion_transacciones_bancarias.php',
    {
      opt: 'load_local_deuda',
      data: get_data,
    },
    function (r) {
      try {
        $('#deudas_holder_tbody').append(r);
        sec_rtb_load_local_deuda_events();
      } catch (err) {
        ajax_error(true, r, err); //opt,response,catch-error
      }
      // auditoria_send({"proceso":"sec_rtb_load_locales","data":get_data});
    }
  );
}
function sec_rtb_load_local_deuda_events() {
  console.log('sec_rtb_load_local_deuda_events');
  loading();

  $('#deudas_holder_tbody .remove_local_btn')
    .off()
    .click(function (event) {
      event.preventDefault();
      var local_id = $(this).data('local_id');
      swal(
        {
          title: '¿Seguro?',
          text: '',
          type: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Si!',
          cancelButtonText: 'No',
          closeOnConfirm: false,
          closeOnCancel: true,
        },
        function (isConfirm) {
          if (isConfirm) {
            swal.close();
            $('#deuda_local_' + local_id).remove();
            $(
              '#locales_list_table tr[data-local_id=' + local_id + ']'
            ).removeClass('bg-success');
            sec_rtb_limpiar();
          }
        }
      );
    });
  $('#panel_deuda .repartir_btn')
    .off()
    .click(function (event) {
      console.log('repartir_btn:click');
      sec_rtb_repartir($(this));
    });
  $('#panel_deuda .limpiar_btn')
    .off()
    .click(function (event) {
      console.log('limpiar_btn:click');
      sec_rtb_limpiar();
    });
  // $("#panel_deuda .repartir_btn").first().click();

  $('#panel_deuda .remove_locales_btn')
    .off()
    .click(function (event) {
      console.log('remove_locales_btn:click');
      // swal({
      // 	title: '¿Seguro?',
      // 	text: '',
      // 	type: 'warning',
      // 	showCancelButton: true,
      // 	confirmButtonText: 'Si!',
      // 	cancelButtonText: 'No',
      // 	closeOnConfirm: false,
      // 	closeOnCancel: true
      // }, function(isConfirm){
      // 	if (isConfirm){
      // 		swal.close();
      $('.deuda_local').remove();
      $('#locales_list_table tr').removeClass('bg-success');
      sec_rtb_limpiar();
      // 	}
      // });
    });
}
function sec_rtb_limpiar() {
  console.log('sec_rtb_limpiar');
  $('.amort').val('');
  $('.td_deuda_abono').html('0.00');
  $('.td_deuda_saldo').html('0.00');
  $('#tit_usado').html('0.00');
  $('#tit_restante').html('0.00');
}
function sec_rtb_repartir(btn) {
  console.log('sec_rtb_repartir');

  var trans_importe = Number($('#trans_importe').val());
  var trans_usado = Number($('#trans_usado').val());
  var trans_restante = Number($('#trans_restante').val());

  $('#trans_info_table #tit_importe').html(trans_importe.toFixed(2));
  $('#trans_info_table #tit_usado').html(trans_usado.toFixed(2));
  $('#trans_info_table #tit_restante').html(trans_restante.toFixed(2));

  var tmp_usado = trans_usado;
  var tmp_restante = trans_restante;

  $('#deudas_holder_tbody tr').each(function (index, el) {
    var tr_id = $(el).attr('id');
    var td_deuda_total = Number($(el).find('.td_deuda_total').html());
    var td_deuda_abono = Number($(el).find('.td_deuda_abono').html());
    var td_deuda_saldo = Number($(el).find('.td_deuda_saldo').html());

    $('#' + tr_id + ' .deuda_repartir').each(function (index, el) {
      var monto = Number($(el).find('.monto').val());
      var amort = $(el).find('.amort').val();
      if (amort == '') {
        amort = null;
      } else {
        amort = Number(amort);
      }
      if (td_deuda_total > 0) {
        if (monto) {
          if (monto > 0) {
            if (amort == null) {
              if (tmp_restante > monto) {
                amort = monto;
              } else {
                amort = Number(tmp_restante);
              }
            } else {
            }
          } else {
            amort = 0;
          }
        } else {
          amort = 0;
        }
      } else {
        amort = 0;
      }
      $(el).find('.amort').val(amort.toFixed(2));
      tmp_usado += amort;
      tmp_restante = trans_importe - tmp_usado;
    });
  });

  $('#deudas_holder_tbody tr .td_deuda_abono').html('0.00');
  $('#deudas_holder_tbody tr').each(function (index, el) {
    var tr_id = $(el).attr('id');
    var td_deuda_total = Number($(el).find('.td_deuda_total').html());
    var td_deuda_abono = Number($(el).find('.td_deuda_abono').html());
    var td_deuda_saldo = Number($(el).find('.td_deuda_saldo').html());

    $('#' + tr_id + ' .deuda_repartir .amort').each(function (index, el) {
      td_deuda_abono += Number($(el).val());
    });

    td_deuda_saldo = td_deuda_total - td_deuda_abono;
    $(el).find('.td_deuda_abono').html(td_deuda_abono.toFixed(2));
    $(el).find('.td_deuda_saldo').html(td_deuda_saldo.toFixed(2));
  });

  $('#trans_info_table #tit_usado').html(tmp_usado.toFixed(2));
  $('#trans_info_table #tit_restante').html(tmp_restante.toFixed(2));
}
function sec_rtb_trans_update_view(trans) {
  console.log('sec_rtb_repartir_update');
  // $("#trans_importe").val();
  // $("#trans_usado").val(trans.usado.toFixed(2));
  // $("#trans_restante").val(trans.restante.toFixed(2));
  // $("#view_trans_importe").html();
  // $("#view_trans_usado").html(trans.usado.toFixed(2));
  // $("#view_trans_restante").html(trans.restante.toFixed(2));

  $('#trans_info_table #tit_importe').html(trans.importe.toFixed(2));
  $('#trans_info_table #tit_usado').html(trans.usado.toFixed(2));
  $('#trans_info_table #tit_restante').html(trans.restante.toFixed(2));
}
function force_plain_text(t) {
  return t.replace(/(<([^>]+)>)/gi, '').toUpperCase();
}
function sec_rtb_load_periodos() {
  console.log('sec_rtb_load_periodos');
  var api_data = {};
  api_data.year = $('#select_year').val();
  api_data.mes = $('#select_mes').val();
  loading(true);
  $.post(
    'api/?json',
    {
      where: 'cobranzas_periodos',
      data: api_data,
    },
    function (r) {
      try {
        var obj = jQuery.parseJSON(r);
        console.log(obj);
        $('#select_periodo').html('');
        $.each(obj.data, function (index, val) {
          $('#select_periodo').append(
            $('<option>', {
              value: val.periodo_rango,
              text: val.periodo_rango,
            })
          );
        });
        console.log('sec_rtb_load_periodos:done');
        loading();
        // $("#panel_deuda #load_list_btn").first().click();
      } catch (err) {
        ajax_error(true, r, err); //opt,response,catch-error
      }
      auditoria_send({ proceso: 'sec_rtb_load_periodos', data: api_data });
    }
  );
}
function sec_rtb_save() {
  console.log('sec_rtb_save');
  loading(true);
  var save_data = {};
  save_data.trans_unique_id = $('#trans_at_unique_id').val();
  save_data.periodo_year = $('#select_year').val();
  save_data.periodo_mes = $('#select_mes').val();
  save_data.periodo_rango = $('#select_periodo').val();
  save_data.pago_tipo_id = 1;
  // save_data.estado = 1;
  save_data.locales = {};

  $('#deudas_holder_tbody tr').each(function (tr_index, tr_el) {
    var tr_id = $(tr_el).attr('id');
    var local = {};
    local.local_id = $(tr_el).data('local_id');
    local.deudas = {};
    $('#' + tr_id + ' .deuda_repartir').each(function (td_index, td_el) {
      console.log(td_el);
      var deuda = {};
      // deuda.deuda_unique_id = $(td_el).data("deuda_unique_id");
      deuda.deuda_tipo = $(td_el).data('deuda_tipo');
      deuda.deuda_tipo_id = $(td_el).data('deuda_tipo_id');
      deuda.monto = $(td_el).find('.monto').val();
      deuda.amort = $(td_el).find('.amort').val();
      local.deudas[td_index] = deuda;
    });

    save_data.locales[local.local_id] = local;
  });

  $.post(
    'sys/set_data.php',
    {
      opt: 'recaudacion_transbanc_save',
      data: save_data,
    },
    function (r) {
      try {
        var obj = jQuery.parseJSON(r);
        console.log(obj);
        // sec_rtb_modal();
        loading();
        swal(
          {
            title: 'Guardado!',
            text: '',
            type: 'success',
            timer: 600,
            closeOnConfirm: false,
          },
          function () {
            // 	localStorage.setItem("highlight_this_item", "tr#trans_"+save_data.at_unique_id);
            swal.close();
            // loading();
            m_reload();
            // 	// $("tr#trans_"+save_data.at_unique_id).addClass('bg-success',1000);
            // 	// window.setTimeout(function(){
            // 	// 	$("tr#trans_"+save_data.at_unique_id).stop().animate({
            // 	// 		backgroundColor: "#FFFFFF"
            // 	// 		}, 1000);
            // 	// }, 1000);
          }
        );
      } catch (err) {
        console.log(r);
        // ajax_error(true,r,err);//opt,response,catch-error
      }
      auditoria_send({
        proceso: 'recaudacion_transbanc_save',
        data: save_data,
      });
    }
  );

  // save_data.locales.estado = 1;
  console.log(save_data);
}
function sec_rtb_save_OLD() {
  console.log('sec_rtb_save');
  loading(true);
  var save_data = {};
  save_data.at_unique_id = $(
    '#transaccion_bancaria_modal input[name=at_unique_id]'
  ).val();
  save_data.data = {};
  save_data.data.estado = 1;
  var error = false;
  $('#transaccion_bancaria_modal .save_item').each(function (index, el) {
    var i = $(el).attr('name');
    var v = $(el).val();
    if ($(el).is('input') || $(el).is('textarea') || $(el).is('select')) {
      if ($(el).attr('type') == 'radio') {
        if ($(el).prop('checked')) {
          if ($(el).prop('disabled')) {
          } else {
            save_data.data[i] = v;
          }
          $(el)
            .parents('.form-group')
            .removeClass('has-error')
            .addClass('has-success');
        }
      } else {
        if (v) {
          $(el)
            .parents('.form-group')
            .removeClass('has-error')
            .addClass('has-success');
          if ($(el).prop('disabled')) {
          } else {
            save_data.data[i] = v;
          }
        } else {
          if (
            ['input_local', 'textarea_comentario'].indexOf($(el).attr('id')) >=
            0
          ) {
            $(el)
              .parents('.form-group')
              .removeClass('has-error')
              .addClass('has-success');
          } else {
            $(el)
              .parents('.form-group')
              .addClass('has-error')
              .removeClass('has-success');
            if (!error) {
              $(el).focus();
            }
            error = $(el);
          }
        }
      }
    }
  });
  save_data.div = {};
  $('#trans_div_holder tbody tr.count_me').each(function (index, el) {
    var new_div = {};
    new_div.local_id = $(el).children('td.new_local').data('local_id');
    new_div.monto = $(el).children('td.new_monto').html();
    save_data.div[index] = new_div;
  });
  // error=true;
  if (error) {
  } else {
    $.post(
      'sys/set_data.php',
      {
        opt: 'recaudacion_div_trans_bancaria',
        data: save_data,
      },
      function (r) {
        try {
          var obj = jQuery.parseJSON(r);
          console.log(obj);
          sec_rtb_modal();
          loading();
          swal(
            {
              title: 'Guardado!',
              text: '',
              type: 'success',
              timer: 500,
              closeOnConfirm: false,
            },
            function () {
              localStorage.setItem(
                'highlight_this_item',
                'tr#trans_' + save_data.at_unique_id
              );
              loading(true);
              swal.close();
              m_reload();
              // $("tr#trans_"+save_data.at_unique_id).addClass('bg-success',1000);
              // window.setTimeout(function(){
              // 	$("tr#trans_"+save_data.at_unique_id).stop().animate({
              // 		backgroundColor: "#FFFFFF"
              // 		}, 1000);
              // }, 1000);
            }
          );
        } catch (err) {
          ajax_error(true, r, err); //opt,response,catch-error
        }
        // auditoria_send({"proceso":"recaudacion_div_trans_bancaria","data":save_data});
      }
    );
  }
  console.log(save_data);
}
function sec_rtb_view(get_data) {
  console.log('sec_rtb_view');
  console.log(get_data);
  loading(true);
  $.post(
    '/sys/get_recaudacion_transacciones_bancarias.php',
    {
      opt: 'transaccion_bancaria',
      data: get_data,
    },
    function (r) {
      try {
        $('#trans_holder').html(r);
        console.log('transaccion_bancaria:done');
        sec_rtb_modal('view');
        loading();
      } catch (err) {
        ajax_error(true, r, err); //opt,response,catch-error
      }
      // auditoria_send({"proceso":"transaccion_bancaria","data":get_data});
    }
  );
}
function sec_rtb_modal(opt) {
  console.log('sec_rtb_modal');
  // console.log(opt);
  if (opt) {
    if (opt == 'new') {
      $('#transaccion_bancaria_modal').modal('show');
    } else if (opt == 'view') {
      $('#transaccion_bancaria_modal').modal('show');
    } else if (opt == 'assig') {
      console.log('assig!!!');

      var view_data = {};
      view_data.ids = {};
      $('.checkbox_me').each(function (index, el) {
        var checkbox = $(el).find('input[type=checkbox]');
        if (checkbox.is(':checked')) {
          console.log();
          view_data.ids[index] = checkbox.data('id');
        }
      });
      console.log(view_data);
      // view_data.id = '2e4676e8753bb6df318260cf78b9cab9';
      sec_rtb_view(view_data);
      // $("#transaccion_bancaria_modal").modal("show");
    } else if (opt == 'import') {
      $('#transaccion_bancaria_import_modal').modal('show');
    }
    sec_rtb_modal_events(opt);
  } else {
    $('#transaccion_bancaria_modal').modal('hide');
    $('#transaccion_bancaria_import_modal').modal('hide');
  }
}
function sec_rtb_modal_events(opt) {
  console.log('sec_rtb_modal_events');
  console.log(opt);
  if (opt == 'new') {
  } else if (opt == 'assig') {
    // $(".checkbox_me").each(function(index, el) {
    // 	var checkbox = $(el).find("input[type=checkbox]");
    // 	if(checkbox.is(':checked')){
    // 		console.log(checkbox.closest(".trans_item"));
    // 	}
    // });
  } else if (opt == 'view') {
    $('#select_mes')
      .off()
      .change(function (event) {
        sec_rtb_load_periodos();
      });
    $('#select_mes').first().change(); //SI DEJAR ESTE!!!

    $('#panel_deuda #load_list_btn')
      .off()
      .click(function (event) {
        sec_rtb_load_locales();
      });
    setTimeout(function () {
      // $("#panel_deuda #load_list_btn").first().click();
    }, 100);

    $('.make_me_select2').select2();

    if ((collapse_panel_id = localStorage.getItem('make_me_collapse_body'))) {
      $('#' + collapse_panel_id + ' .panel-body').removeClass('in');
      $('#' + collapse_panel_id + ' .icon-panel-collapse').addClass(
        'collapsed'
      );
    }

    $('.make_me_collapse_body').each(function (index, el) {
      var panel_id = $(el).attr('id');
      console.log(panel_id);
      $('#' + panel_id + ' .icon-panel-collapse')
        .off()
        .click(function (event) {
          if ($('#' + panel_id + ' .panel-body').hasClass('in')) {
            $('#' + panel_id + ' .panel-body').removeClass('in');
            $('#' + panel_id + ' .icon-panel-collapse').addClass('collapsed');
            localStorage.setItem('make_me_collapse_body', panel_id);
          } else {
            $('#' + panel_id + ' .panel-body').addClass('in');
            $('#' + panel_id + ' .icon-panel-collapse').removeClass(
              'collapsed'
            );
            localStorage.removeItem('make_me_collapse_body');
          }
        });
    });
  } else if (opt == 'import') {
    $('.uploader_file_name').show();
    $('.hide_while_uploading').show();
    $('.filename').hide();
    input_file = $('#import_file');
    input_file.val('');
    input_file.off().change(function (e) {
      console.log('input_file:change');
      // files = e.target.files;
      // console.log(files);
      $.each(input_file.prop('files'), function (index, val) {
        console.log(val);
        $('.uploader_file_name').hide();
        $('.filename').show();
        $('.filename span').html(val.name);
        $('.change_file_btn')
          .off()
          .click(function (event) {
            sec_rtb_modal_events();
          });
      });
    });
  }
}
function sec_rtb_load_locales() {
  console.log('sec_rtb_load_locales');
  loading(true);
  var get_data = {};
  get_data.year = $('#select_year').val();
  get_data.mes = $('#select_mes').val();
  get_data.rango = $('#select_periodo').val();
  $.post(
    '/sys/get_recaudacion_transacciones_bancarias.php',
    {
      opt: 'load_locales',
      data: get_data,
    },
    function (r) {
      try {
        $('#locales_list_table').html(r);
        $('#deudas_holder_tbody').html('');
        sec_rtb_load_locales_events();
      } catch (err) {
        ajax_error(true, r, err); //opt,response,catch-error
      }
      // auditoria_send({"proceso":"sec_rtb_load_locales","data":get_data});
    }
  );
}
function sec_rtb_load_locales_events() {
  console.log('sec_rtb_load_locales_events');
  loading();
  $('.list_search_input')
    .val('')
    .off()
    .on('change keyup paste click', function () {
      var searchTerm = force_plain_text($(this).val());
      var holder_id = $(this).data('holder');
      $('#' + holder_id + ' tr')
        .stop()
        .hide();
      $('#' + holder_id + ' td').each(function (index, el) {
        var h3_text = force_plain_text($(el).html());
        var n = h3_text.indexOf(searchTerm);
        if (n >= 0) {
          $(el).parent('tr').stop().show();
        }
      });
    });

  $('.list_search_input').focus();

  $('#locales_list_table tr')
    .off()
    .click(function (event) {
      event.preventDefault();
      if ($(this).hasClass('bg-success')) {
        swal(
          {
            title: 'Error!',
            text: 'Ese local ya ha sido asignado.',
            type: 'warning',
            timer: 1000,
          },
          function () {
            swal.close();
          }
        );
      } else {
        $('.locales_list_holder').scrollTop(0);
        var scroll_to_el = $(this).position();
        $('.locales_list_holder').scrollTop(scroll_to_el.top - 44);
        console.log(scroll_to_el);
        $(this).addClass('bg-success');
        sec_rtb_load_local_deuda($(this));
      }
    });
  // data-local_id="<?php echo $local_id;?>"

  // console.log(scroll_to_el);
  //
  // $("#locales_list_table tr[data-local_id="+"88"+"]").first().click(); //farma
  // $("#locales_list_table tr[data-local_id="+"91"+"]").first().click();
  // $("#locales_list_table tr[data-local_id="+"142"+"]").first().click();
  // $("#locales_list_table tr[data-local_id="+"143"+"]").first().click();
  // $("#locales_list_table tr[data-local_id="+"144"+"]").first().click();

  setTimeout(function () {
    // sec_rtb_repartir();
  }, 100);
  setTimeout(function () {
    // sec_rtb_save();
  }, 200);
}
function sec_rtb_hide(data) {
  console.log('sec_rtb_hide');
  console.log(data);
  // swal({
  // 	title: '¿Seguro?',
  // 	text: '',
  // 	type: 'warning',
  // 	showCancelButton: true,
  // 	confirmButtonText: 'Si, proceder!',
  // 	cancelButtonText: 'No, cancelar!',
  // 	closeOnConfirm: false,
  // 	closeOnCancel: true
  // }, function(isConfirm){
  // 	if (isConfirm){
  // 		swal.close();
  loading(true);

  $.post(
    'sys/set_data.php',
    {
      opt: 'recaudacion_hide_trans_bancaria',
      data: data,
    },
    function (r) {
      try {
        var obj = jQuery.parseJSON(r);
        console.log(obj);

        // swal({
        // 	title: "Listo!",
        // 	text: "",
        // 	type: "success",
        // 	timer: 400,
        // 	closeOnConfirm: false
        // },
        // function(){
        // 	swal.close();
        $('tr#trans_' + data.id)
          .addClass('bg-danger')
          .hide(1000);
        loading();
        // m_reload();
        // });
      } catch (err) {
        swal(
          {
            title: 'Error en la base de datos',
            type: 'info',
            timer: 2000,
          },
          function () {
            swal.close();
            loading();
          }
        );
        console.log(r);
        //loading();
      }
      auditoria_send({
        proceso: 'recaudacion_hide_trans_bancaria',
        data: data,
      });
    }
  );
  // 	}
  // });
}
function sec_rtb_import() {
  console.log('sec_rtb_import');

  var save_data = {};
  save_data['tabla'] = 'tbl_repositorio_transacciones_bancarias';
  // save_data["item_id"]="sec_rtb_import";
  // save
  $('#transaccion_bancaria_import_modal .import_input').each(function (
    index,
    el
  ) {
    var i = $(el).attr('name');
    var v = $(el).val();
    if ($(el).is('input')) {
      if ($(el).attr('type') == 'radio') {
        if ($(el).prop('checked')) {
          if ($(el).prop('disabled')) {
          } else {
            save_data[i] = v;
          }
          $(el)
            .parents('.form-group')
            .removeClass('has-error')
            .addClass('has-success');
        }
      }
    }
  });
  console.log(save_data);
  if (input_file.prop('files').length) {
    input_file.simpleUpload('sys/sys_recaudacion_upload.php', {
      init: function (info) {
        console.log('init');
        console.log(info);
      },
      data: save_data,
      finish: function (info) {
        console.log('finish');
        console.log(info);
      },
      start: function (file) {
        console.log('start');
        console.log(file);
        $('.hide_while_uploading').hide();
      },
      progress: function (progress) {
        console.log('progress');
        console.log(progress);
        var pro_per = progress.toFixed(0) + '%';
        $('.progress_import .progress-bar').width(pro_per).html(pro_per);
      },
      success: function (data) {
        console.log('success');
        console.log(data);
        try {
          var obj = jQuery.parseJSON(data);
          save_data.file_id = obj.file_id;
          sec_rtb_process_file(save_data);
        } catch (err) {
          console.log(r);
          swal('Error!', 'error error', 'danger');
        }
      },
      error: function (error) {
        console.log('error');
        console.log(error);
      },
    });
    auditoria_send({
      proceso: 'recaudacion_import_trans_bancaria',
      data: save_data,
    });
  } else {
    swal('Error!', 'Seleccione un archivo', 'warning');
  }
}
function sec_rtb_process_file(pro_data) {
  console.log('sec_rtb_process_file');
  console.log(pro_data);
  var pro_per = '15%';
  $('.progress_process .progress-bar').width(pro_per).html(pro_per);
  // var save_data = {};
  // 	save_data.file_id = file_id;

  // var send_data = {};
  // 	send_data.banco_id = 12;
  // 	send_data.moneda_id = 1;
  // 	send_data.file_id = 1539;
  $.post(
    'sys/set_data.php',
    {
      opt: 'recaudacion_process_trans_bancaria',
      data: pro_data,
    },
    function (r) {
      try {
        var obj = jQuery.parseJSON(r);
        console.log(obj);
        if (obj.error) {
          var error_msg = 'Error!';
          if (obj.error_msg) {
            console.log(
              'error_msg:::::::::::::::::::::::::::::::::::::::::::::::'
            );
            console.log(obj.error_msg);
            error_msg = obj.error_msg;
          }
          // if(obj.error=="archivo_banco"){
          // 	error_msg="El archivo no corresponde al banco.";
          // }
          if (obj.error_data) {
            console.log(
              'error_data:::::::::::::::::::::::::::::::::::::::::::::::'
            );
            console.log(obj.error_data);
          }
          swal(
            {
              title: 'Error!',
              text: error_msg,
              type: 'warning',
              timer: 3000,
              html: true,
              closeOnConfirm: false,
            },
            function () {
              swal.close();
              // m_reload();
            }
          );
          pro_per = '0%';
        } else {
          pro_per = '100%';
          swal(
            {
              title: 'Guardado!',
              text: '',
              type: 'success',
              timer: 600,
              closeOnConfirm: false,
            },
            function () {
              swal.close();
              m_reload();
            }
          );
        }
        $('.progress_process .progress-bar').width(pro_per).html(pro_per);
      } catch (err) {
        console.log(r);
        ajax_error(true, r, err); //opt,response,catch-error
      }
      // auditoria_send({"proceso":"recaudacion_add_trans_bancaria","data":pro_data});
    }
  );

  // auditoria_send({"proceso":"recaudacion_process_import_trans_bancaria","data":pro_data});
}
