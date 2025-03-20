var reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage = false;
var reportes_pagados_de_otras_tiendas_fin_fecha_localstorage = false;
var $table_pdot = false;
function sec_reportes_pagados_de_otras_tiendas() {
  console.log('sec_reportes_pagados_de_otras_tiendas');
  loading(true);
  sec_reportes_pagados_de_otras_tiendas_settings();
  sec_reportes_pagados_de_otras_tiendas_events();
  sec_reportes_pagados_de_otras_tiendas_get_data_reporte();
  sec_reportes_pagados_de_otras_tiendas_get_canales_venta();
  sec_reportes_pagados_de_otras_tiendas_get_locales();
}
function sec_reportes_pagados_de_otras_tiendas_get_canales_venta() {
  var data = {};
  data.what = {};
  data.what[0] = 'id';
  data.what[1] = 'codigo';
  data.where = 'canales_de_venta';
  data.filtro = {};
  auditoria_send({
    proceso: 'sec_reportes_pagados_de_otras_tiendas_get_canales_venta',
    data: data,
  });
  $.ajax({
    data: data,
    type: 'POST',
    dataType: 'json',
    url: '/api/?json',
  })
    .done(function (data, textStatus, jqXHR) {
      if (console && console.log) {
        $.each(data.data, function (index, val) {
          canales_de_venta[val.id] = val.codigo;
          var new_option = $('<option>');
          $(new_option).val(val.id);
          $(new_option).html(val.codigo);
          $('.canal_venta_reporte_pagados_de_otras_tiendas').append(new_option);
        });
        $('.canal_venta_reporte_pagados_de_otras_tiendas').select2({
          closeOnSelect: false,
        });
      }
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
      if (console && console.log) {
        console.log('La solicitud canales de ventas a fallado: ' + textStatus);
      }
    });
}
function sec_reportes_pagados_de_otras_tiendas_get_locales() {
  var data = {};
  data.what = {};
  data.what[0] = 'id';
  data.what[1] = 'nombre';
  data.where = 'locales';
  data.filtro = {};
  auditoria_send({
    proceso: 'sec_reportes_pagados_de_otras_tiendas_get_locales',
    data: data,
  });
  $.ajax({
    data: data,
    type: 'POST',
    dataType: 'json',
    url: '/api/?json',
  })
    .done(function (data, textStatus, jqXHR) {
      if (console && console.log) {
        $.each(data.data, function (index, val) {
          var new_option = $('<option>');
          $(new_option).val(val.id);
          $(new_option).html(val.nombre);
          $('.local_reporte_pagados_de_otras_tiendas').append(new_option);
        });
        $('.local_reporte_pagados_de_otras_tiendas').select2({
          closeOnSelect: false,
        });
      }
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
      if (console && console.log) {
        console.log('La solicitud locales a fallado: ' + textStatus);
      }
    });
}
function sec_reportes_pagados_de_otras_tiendas_events() {
  $('.btn_filtrar_reporte_pagados_de_otras_tiendas')
    .off()
    .on('click', function () {
      sec_reportes_pagados_de_otras_tiendas_get_data_reporte();
      loading(true);
    });
  $('.btn_export_pagados_en_de_xlsx')
    .off()
    .on('click', function () {
      var reinit = $table_pdot.floatThead('destroy');
      sec_reportes_ejecutar_reporte_pagados_de_otras_tiendas('xlsx');
      sec_reportes_get_table_to_export(
        'xlsxbtn',
        'xportxlsx',
        'xlsx',
        'reporte_pagados_de_otras_tiendas.xlsx'
      );
      reinit();
    });
  $('.btn_export_pagados_en_de_xls')
    .off()
    .on('click', function () {
      var reinit = $table_pdot.floatThead('destroy');
      sec_reportes_ejecutar_reporte_pagados_de_otras_tiendas(
        'biff2',
        'reporte_pagados_de_otras_tiendas.xls'
      );
      sec_reportes_get_table_to_export(
        'biff2btn',
        'xportbiff2',
        'biff2',
        'reporte_pagados_de_otras_tiendas.xls'
      );
      reinit();
    });

  $table_pdot = $('#tabla_pagados_de_otras_tiendas');
  $table_pdot.floatThead({
    top: 50,
  });
  $('td').each(function () {
    var cellvalue = $(this).html();
    if (cellvalue < 0) {
      $(this).wrapInner('<strong class="negative_number_pdot"></strong>');
    }
  });
}
function sec_reportes_pagados_de_otras_tiendas_settings() {
  $('.local_reporte_pagados_de_otras_tiendas').select2({
    closeOnSelect: false,
    allowClear: true,
  });
  $('.canal_venta_reporte_pagados_de_otras_tiendas').select2({
    closeOnSelect: false,
    allowClear: true,
  });
  $('.red_reporte_pagados_de_otras_tiendas').select2({
    closeOnSelect: false,
    allowClear: true,
  });
  $('.reportes_pagados_de_otras_tiendas_datepicker')
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
  reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage =
    localStorage.getItem(
      'reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage'
    );
  if (reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage) {
    var reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage_new =
      moment(
        reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage
      ).format('DD-MM-YYYY');
    $('#input_text-reportes_pagados_de_otras_tiendas_inicio_fecha')
      .datepicker(
        'setDate',
        reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage_new
      )
      .trigger('change');
  }

  reportes_pagados_de_otras_tiendas_fin_fecha_localstorage =
    localStorage.getItem(
      'reportes_pagados_de_otras_tiendas_fin_fecha_localstorage'
    );
  if (reportes_pagados_de_otras_tiendas_fin_fecha_localstorage) {
    var reportes_pagados_de_otras_tiendas_fin_fecha_localstorage_new = moment(
      reportes_pagados_de_otras_tiendas_fin_fecha_localstorage
    ).format('DD-MM-YYYY');
    $('#input_text-reportes_pagados_de_otras_tiendas_fin_fecha')
      .datepicker(
        'setDate',
        reportes_pagados_de_otras_tiendas_fin_fecha_localstorage_new
      )
      .trigger('change');
  }
}
function sec_reportes_pagados_de_otras_tiendas_get_data_reporte() {
  //
  var get_pagados_en_de_data = {};
  get_pagados_en_de_data.where = 'pagados_de_otras_tiendas';
  get_pagados_en_de_data.filtro = {};
  get_pagados_en_de_data.filtro.fecha_inicio = $(
    '.reportes_pagados_de_otras_tiendas_inicio_fecha'
  ).val();
  get_pagados_en_de_data.filtro.fecha_fin = $(
    '.reportes_pagados_de_otras_tiendas_fin_fecha'
  ).val();
  get_pagados_en_de_data.filtro.locales = $(
    '.local_reporte_pagados_de_otras_tiendas'
  ).val();
  get_pagados_en_de_data.filtro.canales_de_venta = $(
    '.canal_venta_reporte_pagados_de_otras_tiendas'
  ).val();
  get_pagados_en_de_data.filtro.red_id = $(
    '.red_reporte_pagados_de_otras_tiendas'
  ).val();

  localStorage.setItem(
    'reportes_pagados_de_otras_tiendas_inicio_fecha_localstorage',
    get_pagados_en_de_data.filtro.fecha_inicio
  );
  localStorage.setItem(
    'reportes_pagados_de_otras_tiendas_fin_fecha_localstorage',
    get_pagados_en_de_data.filtro.fecha_fin
  );
  auditoria_send({
    proceso: 'sec_reportes_pagados_de_otras_tiendas_get_data_reporte',
    data: get_pagados_en_de_data,
  });
  $.ajax({
    url: '/api/?json',
    type: 'POST',
    data: get_pagados_en_de_data,
  })
    .done(function (dataresponse) {
      var obj = JSON.parse(dataresponse);
      console.log(obj);
      sec_reportes_pagados_de_otras_tiendas_create_table(obj);
    })
    .fail(function () {
      console.log('error reportes pagados en otras tiendas');
    })
    .always(function () {
      console.log('complete reportes pagados en otras tiendas');
    });
}
function sec_reportes_pagados_de_otras_tiendas_create_table(obj) {
  var html =
    "<table id='tabla_pagados_de_otras_tiendas' class='tabla_pagados_de_otras_tiendas' width='100%' >";
  html += "<thead style='background-color:#70ad47; color:#fafafa !important;'>";
  html += '<tr>';
  html += "<th class='sec_rep_pdot_tienda_pago_th'>TIENDA DE PAGO</th>";
  html += "<th class='sec_rep_pdot_tienda_origen_th'>TIENDA DE ORIGEN</th>";
  html +=
    "<th class='sec_rep_pdot_cantidad_pago_th'>PAGOS DE OTRAS TIENDAS</th>";
  html += '</tr>';
  html += '</thead>';
  html += '<tbody>';

  $.each(obj.data, function (index, val_detalles) {
    html += '<tr>';
    html +=
      "<td class='sec_rep_pdot_tienda_pago'>" + val_detalles.pago + '</td>';
    html +=
      "<td class='sec_rep_pdot_tienda_origen'>" + val_detalles.origen + '</td>';
    html +=
      "<td class='sec_rep_pdot_cantidad_pagado'>" +
      val_detalles.pagado +
      '</td>';
    html += '</tr>';
  });

  html += '</tbody>';
  html += '</table>';
  $('.tabla_contenedor_reporte_pagados_de_otras_tiendas').html(html);
  sec_reportes_pagados_de_otras_tiendas_events();
  loading();
}

function sec_reportes_ejecutar_reporte_pagados_de_otras_tiendas(type, fn) {
  return sec_reportes_export_table_to_excel(
    'tabla_pagados_de_otras_tiendas',
    type || 'xlsx',
    fn
  );
}
function sec_reportes_validar_exportacion_pagados_de_otras_tiendas(s) {
  if (typeof ArrayBuffer !== 'undefined') {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);
    for (var i = 0; i != s.length; ++i) view[i] = s.charCodeAt(i) & 0xff;
    return buf;
  } else {
    var buf = new Array(s.length);
    for (var i = 0; i != s.length; ++i) buf[i] = s.charCodeAt(i) & 0xff;
    return buf;
  }
}
function sec_reportes_export_table_to_excel(id, type, fn) {
  var wb = XLSX.utils.table_to_book(
    document.getElementById(id),
    { raw: true },
    { sheet: 'Sheet JS' }
  );
  var wbout = XLSX.write(wb, { bookType: type, bookSST: true, type: 'binary' });
  var fname = fn || 'tabla_pagados_de_otras_tiendas.' + type;
  try {
    saveAs(
      new Blob(
        [sec_reportes_validar_exportacion_pagados_de_otras_tiendas(wbout)],
        { type: 'application/octet-stream' }
      ),
      fname
    );
  } catch (e) {
    if (typeof console != 'undefined') console.log(e, wbout);
  }
  return wbout;
}
function sec_reportes_get_table_to_export(pid, iid, fmt, ofile) {
  if (fallback) {
    if (document.getElementById(iid))
      document.getElementById(iid).hidden = true;
    Downloadify.create(pid, {
      swf: 'media/downloadify.swf',
      downloadImage: 'download.png',
      width: 100,
      height: 30,
      filename: ofile,
      data: function () {
        var o = sec_reportes_ejecutar_reporte_pagados_de_otras_tiendas(
          fmt,
          ofile
        );
        return window.btoa(o);
      },
      transparent: false,
      append: false,
      dataType: 'base64',
      onComplete: function () {
        alert('Your File Has Been Saved!');
      },
      onCancel: function () {
        alert('You have cancelled the saving of this file.');
      },
      onError: function () {
        alert(
          'You must put something in the File Contents or there will be nothing to save!'
        );
      },
    });
  } //else document.getElementById(pid).innerHTML = "";
}
