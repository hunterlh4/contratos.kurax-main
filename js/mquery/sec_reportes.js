var modelo = false;
var table_dt = false;
var anio = false;
var mes = false;
var dia = false;
var all_periods="";
var d = new Date();
var time_report = d.getTime();
var array_meses=[];
function sec_reportes_display_datatable_tickets(model) {
    var heightdoc = window.innerHeight;
    var heightnavbar= $(".navbar-header").height();
    var heighttable =heightdoc-heightnavbar-150;
    var table_dt = $('#datatable_reporte_tickets').DataTable({       
          //responsive:true,
          fixedColumns:{leftColumns: 1},
          dom: 'Blftip',
          buttons: [
              { 
                  extend: 'copy',
                  text:'Copiar',
                  className: 'copiarButton'
               },
              { 
                  extend: 'csv',
                  text:'CSV',
                  className: 'csvButton' 
              },
              {   extend: 'excel',
                  text:'Excel',
                  className: 'excelButton' 
              },                    
              {
                  extend: 'colvis',
                  text:'Visibilidad',
                  className:'visibilidadButton',
                  postfixButtons: [ 'colvisRestore' ]
              }
          ],
          fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
              if ( aData[20] == "Medium Risk (MR)" ){
                  //$('td', nRow).css('background-color','rgb(255, 165, 0');
              }
              else if (aData[20] == "Medium Risk (MR)"){
                 //$('td', nRow).css('background-color','rgb(144, 238, 144)');
              }
          },
          footerCallback: function(){
    			    var api = this.api(),
    			    columns = [4,5,6,7,8,9,10,13,14,15,17]; 
    			    for (var i = 0; i < columns.length; i++) {
    			        var total =  api.column(columns[i], {filter: 'applied'}).data().sum().toFixed(2);
    			        var total_pagina = api.column(columns[i], { filter: 'applied', page: 'current' }).data().sum().toFixed(2);
    			        if (total<0 && total_pagina<0){
    			            $('tfoot th').eq(columns[i]).html('Total:<br><span style="color:red; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total) +'<span><br>');
    			            $('tfoot th').eq(columns[i]).append('Pagina:<br><span style="color:red; font-weight: bold; font-size: 11px !important;"> ' + formatonumeros(total_pagina)+'<span>');
    			        }
    			        else{
    			            $('tfoot th').eq(columns[i]).html('Total:<br><span style="color:green; font-weight: bold; font-size: 11px !important;">'+formatonumeros(total) +'<span><br>');
    			            $('tfoot th').eq(columns[i]).append('Pagina:<br><span style="color:green; font-weight: bold; font-size: 11px !important;"> ' + formatonumeros(total_pagina)+'<span>');                  
    			        }
    			    }
          },
          createdRow: function ( row, data, index ) {
              if ( data[20] == "Medium Risk (MR)" ) {
                  $('td', row).eq(20).addClass('medium_risk_rm');
              }
          },            
          bRetrieve: true,
          sPaginationType: "full_numbers",
          lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],  
          //pageLength: 10, 
          paging: true,
          searching: true,
          //lengthChange: true,
          //ordering: true,      
          //bProcessing: true,
          //bAutoWidth: true,
          //bStateSave: true,
          sScrollY: heighttable, 
          sScrollX: "100%", 
          sScrollXInner: "150%", 
          bScrollCollapse: true,       
          //bPaginate: true, 
          //bFilter: true,
          Sorting: [[1, 'asc']], 
          bSort: false,
          data:model,
          columnDefs: [
              { className: "columasControl", "targets": [ 0,1] },
              { className: "columasControlhead","targets": [2] },
              { className: "columa_sport_book_group1","targets": [20] },
              { className: "columa_numeros","targets": [0,2,4,5,6,7,8,9,10,12,13,14,15,17] },        
              { sortable: false,"class": "index",targets: [0]},
              {
                aTargets: [4,5,6,7,8,9,10,13,14,15,17],
                fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                   if ( sData < "0" ) {
                                    $(nTd).css('color', 'red')
                                    $(nTd).css('font-weight', 'bold')
                  }
                }
              }        
          ], 
          
          columns:[
              {
                "model": "BET ID",
                sDefaultContent: ""

              },      
              {
                "model": "BET NUMBER",
                sDefaultContent: ""
              },
              {
                "model": "PLAYER ID",
                sDefaultContent: ""

              },
              {
                "model": "CURRENCY",
                sDefaultContent: ""

              },                     
              {
                "model": "STAKE",
                sDefaultContent: ""
              },
              {
                "model": "STAKE IN",
                sDefaultContent: ""
              }, 
              {
                "model": "ODDS",
                sDefaultContent: "",
                className: ""     
              },
              {
                "model": "WINNINGS",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "WINNINGS IN (PEN)",
                sDefaultContent: ""
              },
              {
                "model": "BONUS AMOUNT",
                sDefaultContent: ""

              },           
              {
                "model": "BONUS TYPE",
                sDefaultContent: "",
                className: ""     
              },
              {
                "model": "WAGERING BONUS BE (PEN)",
                sDefaultContent: "",
                className: ""       
              },          
              {
                "model": "BET TYPE",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "SYSTEM MIN COUNT",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "CASHDESK",
                sDefaultContent: ""

              },
              {
                "model": "BETSHOP",
                sDefaultContent: ""
              }, 
              {
                "model": "CASH DESK INFO",
                sDefaultContent: "",
                className: ""     
              },
              {
                "model": "BETSHOP INFO",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "STATE",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "SOURCE",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "FIRST NAME",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "LAST NAME",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "USERNAME",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "SPORTSBOOK GROUP",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "ESTERNAL ID",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "IP ADDRESS",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "CREATED",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "CALC DATE",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "IS LIVE",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "IS TEST",
                sDefaultContent: "",
                className: ""       
              },
              {
                "model": "PAID DATE",
                sDefaultContent: "",
                className: ""     
              },      
              {
                "model": "PAID CASH DESK NAME",
                sDefaultContent: "",
                className: ""       
              }
                                                                       
              ],       
          pageLength: '32',
         
          language:{
              "decimal":        ".",
              "thousands":      ",",            
              "emptyTable":     "Tabla vacia",
              "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
              "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
              "infoFiltered":   "(filtered from _MAX_ total entradas)",
              "infoPostFix":    "",
              "thousands":      ",",
              "lengthMenu":     "Mostrar _MENU_ entradas",
              "loadingRecords": "Cargando...",
              "processing":     "Procesando...",
              "search":         "Filtrar:",
              "zeroRecords":    "Sin resultados",
              "paginate": {
                  "first":      "Primero",
                  "last":       "Ultimo",
                  "next":       "Siguiente",
                  "previous":   "Anterior"
              },
              "aria": {
                  "sortAscending":  ": activate to sort column ascending",
                  "sortDescending": ": activate to sort column descending"
              },
              "buttons": {
                "copyTitle": 'Contenido Copiado',
                "copySuccess": {
                    _: '%d filas copiadas',
                    1: '1 fila copiada'
                }
              }             
          }        
    });
    table_dt.clear().draw();
    table_dt.rows.add(model).draw();
    table_dt.columns.adjust().draw();
    loading();
}
function sec_reportes_get_tickets(){
              loading(true);
              var data = {};
              data.filtro={};
              data.where="tickets";          
              $(".filtro_text_input_time").each( function(index, element){
                     data.filtro[$( this ).attr("name")] = $('input[name="date_by"]:checked').val();
              });               
              $( ".filtro_text_input" ).each( function( index, element ){
                     if($(this ).val()){
                            data.filtro[$(this ).attr("name")] = $( this ).val();
                     }  
              });
              data.filtro.tipo=1;
              data.pagina = 0;
              data.numero = 10;
              console.log(data);
              $.ajax({
                data: data,
                type: "POST",
                url: "/api/?json",
              })
              .done(function(responsedata, textStatus, jqXHR ) {
                     //console.log(responsedata);
                     var obj = jQuery.parseJSON(responsedata);
                     //console.log(obj);
                     var datafinal=[];
                     var i = 0;
                     var vacio ="";
                     $.each(obj.data, function(index, val) {
                     //console.log(val);
                     
                            var newObject=[
                                 val.bet_id,
                                 val.bet_number,
                                 val.player_id,
                                 val.currency,
                                 val.stake,

                                 val.stakes_in,
                                 val.odds,
                                 val.winnings,
                                 val.winnings_in,
                                 val.bonus_amount,

                                 val.vacio,
                                 val.vacio,
                                 val.type,
                                 val.vacio,
                                 val.cashdesk,

                                 val.betshop,
                                 val.cash_desk_info,
                                 val.betshop_info,
                                 val.state,
                                 val.source,

                                 val.first_name,
                                 val.last_name,
                                 val.username,
                                 val.sportsbook_group,
                                 val.external_id,

                                 val.ip_address,
                                 val.created,
                                 val.calc_date,
                                 val.is_live,
                                 val.is_test,

                                 val._paiddate_,
                                 val.paid_cash_desk_name                                 
                            ];
                            datafinal[i] =  newObject;
                            i++;
                     });
                     //console.log(datafinal);
                     sec_reportes_display_datatable_tickets(datafinal);
              });
              $('#collapseOne').on('hide.bs.collapse', function () {
                    $(".bootstrap-timepicker-widget").css("display","none");
              })
              $("#collapseOne").collapse('hide');
              $('#collapseOne').on('show.bs.collapse', function () {
                    $(".bootstrap-timepicker-widget").css("display","block");
              })
              $("#collapseTwo").collapse('show');
}
function sec_reportes_mostrar_datatable(model){
    table_dt = $('#tabla_sec_reporte_modelo_uno').DataTable({ 
        //responsive: false, 
        fixedHeader: {
          header: true,
          footer:false
        },
        fixedColumns:{
          leftColumns: 6
        },        
        bRetrieve: true,
        lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50, 100, "Todos"]],
        //iDisplayLength: 10,
        //bInfo :true,
        //iDisplayLength: 10,       
        //lengthChange: true,
        searching: true,
        //ordering: true,
        paging: true,      
        //bProcessing:false,
        //bAutoWidth: false,
        //bStateSave: false,
        sScrollY: false, 
        sScrollX: "100%", 
        sScrollXInner: "100%",      
        bScrollCollapse: true, 
        sPaginationType: "full_numbers",
        //bStateSave: false, 
        //bFilter: true,
        Sorting: [[1, 'asc']], 
        bSort: true,
        data:model,
        dom: 'Blftip',
        buttons: [
              { 
                  extend: 'copy',
                  text:'Copiar',
                  className: 'copiarButton'
               },
              { 
                  extend: 'csv',
                  text:'CSV',
                  className: 'csvButton' 
              },
              {   extend: 'excel',
                  text:'Excel',
                  className: 'excelButton' 
              }, 
              /*                   
              {
                  text: 'Email',
                  className:'emailButton',
                  action: function ( e, dt, node, config ) {
                      //$('#modal_sec_recaudacion_modelo_uno').modal('toggle');
                  }
              },
              */
              {
                  extend: 'colvis',
                  text:'Visibilidad',
                  className:'visibilidadButton',
                  postfixButtons: [ 'colvisRestore' ]
              }
        ],
        fnRowCallback: function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
          if(aData[0] && aData[1]){
              $('td', nRow).css('background-color','#337ab7');
          }
          if ( aData[2] == "Total" )
          {
              $('td', nRow).css('background-color','#9BDFFD','important');
              $('td', nRow).css('color','#080FFC'); 
              $('td', nRow).css('font-weight','800');                       
          }
          else if ( aData[2] != "Total" )
          {
             $('td', nRow).css('background-color', '#fafafa','important');
          }
        },
          columnDefs: [
            /*{ className: "columna_numero_meses","targets": [7,8,9,10,11,12,13,14,15,16,17,18] }, */
            /*
            { className: "columna_ubicacion","targets": [1] },
            { className: "columna_tipo","targets": [2] },  
            { className: "columna_tipo_admin","targets": [3] },
            { className: "columna_tipo_punto","targets": [4] }, 
            { className: "columna_qty","targets": [5] },                                                                    
            { className: "columnas_2017 ","targets": [6,7,8,9,10,11,12,13,14,15] },
            { className: "columnas_enero ", "targets": [16,17,18,19,20,21,22,23,24,25] }, 
            { className: "columnas_febrero", "targets": [26,27,28,29,30,31,32,33,34,35] }, 
            { className: "columnas_marzo", "targets": [36,37,38,39,40,41,42,43,44,45] },
            { className: "columnas_abril", "targets": [46,47,48,49,50,51,52,53,54,55] },
            { className: "columnas_mayo", "targets": [56,57,58,59,60,61,62,63,64,65] },
            { className: "", "targets": [8] },        
            { className: "","targets": [3,4,5,6,7,8,9,10,11,12,13,14,15,16,17]},
            { sortable: false,"class": "index",targets: [0]},
            { sortable: true, "targets": [0] },
            {
              aTargets: [3,4,5,6,7,8,9,10,11,12,13,14,15,16,17],
                fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                   if ( sData < "0" ) {
                            $(nTd).css('color', 'red')
                            $(nTd).css('font-weight', 'bold')
                  }
              }
            } */        
        ],
          language:{
              "decimal":        ".",
              "thousands":      ",",            
              "emptyTable":     "Tabla vacia",
              "info":           "Mostrando del _START_ al _END_ de _TOTAL_ entradas.",
              "infoEmpty":      "Mostrando 0 a 0 de 0 entradas",
              "infoFiltered":   "(filtered from _MAX_ total entradas)",
              "infoPostFix":    "",
              "thousands":      ",",
              "lengthMenu":     "Mostrar _MENU_ entradas",
              "loadingRecords": "Cargando...",
              "processing":     "Procesando...",
              "search":         "Filtrar:",
              "zeroRecords":    "Sin resultados",
              "paginate": {
                  "first":      "Primero",
                  "last":       "Ultimo",
                  "next":       "Siguiente",
                  "previous":   "Anterior"
              },
              "aria": {
                  "sortAscending":  ": activate to sort column ascending",
                  "sortDescending": ": activate to sort column descending"
              },
              "buttons": {
                  "copyTitle": 'Contenido Copiado',
                  "copySuccess": {
                      _: '%d filas copiadas',
                      1: '1 fila copiada'
                  }
              }               
          }                                  
    });
    table_dt.clear().draw();
    table_dt.rows.add(model).draw();
    table_dt.columns.adjust().draw(); 
    loading();
    console.log("MUESTRA!");
}
function sec_reportes_get_reportes(){
    loading(true);
    var get_reportes_data = {};
    get_reportes_data.where = "reporte_betbar";
    get_reportes_data.filtro = {};
    get_reportes_data.filtro.fecha_inicio = $('.inicio_fecha_reportes').val();
    get_reportes_data.filtro.fecha_fin = $('.fin_fecha_reportes').val();      
    $.ajax({
      type: "POST",
      url: "/api/?json",
      data: get_reportes_data      
    })
    .done(function(responsedata, textStatus, jqXHR ) {
      //console.log(responsedata);
      var obj = jQuery.parseJSON(responsedata);
      console.log(obj);
      sec_reportes_process_data(obj);
    })
    .fail(function( jqXHR, textStatus, errorThrown ) {
      if ( console && console.log ) {
        console.log( "La solicitud reportes a fallado: " +  textStatus);
      }
    });  
}
function sec_reportes_process_data(obj){
      var nombre_mes = ["Enero", "Febrero", "Marzo", "Abril", "Mayo","Junio", "Julio", "Agosto", "Setiembre", "Octubre", "Noviembre", "Diciembre"];  
      try{
        var obj = jQuery.parseJSON(r);
      }catch(err){
      }
      var cols = Object();
        cols["total_apostado"]="Dinero Apostado";
        cols["total_ganado"]="Premios Pagados y x Pagar";
        cols["por_pagar"]="Premios x Pagar";
        cols["net_win"]="Net Win T";
        cols["hold"]="Hold%";
        cols["num_tickets"]="Tickets Emitidos";
        cols["apuesta_x_ticket"]="Apuesta x Ticket";
        cols["num_tickets"]="% Ticket Premiados";
        cols["total_depositado_web"]="Dinero Depositado Web";
        cols["total_retirado_web"]="Dinero Retirado Web";
      var cdv = Object();
        cdv[15]="Web";
        cdv[16]="PBET";
        cdv[17]="SBT-Negocios";
        cdv[18]="JV Global Bet";
        cdv[20]="SBT-BC";
        cdv[21]="JV Golden Race";
      var period_inicio = false;
      var period_fin = false;
      var period_arr = Object();
      $.each(obj.resumen, function(year_index, year_data) {
        $.each(year_data, function(month_year, month_data) {
          if(!period_inicio){
            period_inicio = year_index+""+month_year;
          }
          period_fin = year_index+""+month_year;
          period_arr[year_index+""+month_year]=true;
        });
      });
      var html_table = $("<table class='tabla_reportes table-hover'>").attr("id","reporte_apuestas").attr("width","100%");
      var html_tr = $("<tr>");
        html_tr.append('<th rowspan="3" class="cabecera_canal_venta">CANAL DE VENTA</th>');
        html_tr.append('<th rowspan="3" class="cabecera_local">LOCAL</th>');
        html_tr.append('<th rowspan="3" class="cabecera_tipo">TIPO</th>');
        html_tr.append('<th rowspan="3" class="cabecera_tipo_administracion">TIPO ADMIN. </th>');
        html_tr.append('<th rowspan="3" class="cabecera_tipo_punto">TIPO DE PUNTO</th>');
        html_tr.append('<th rowspan="3" class="cabecera_qty">QTY</th>');
        $.each(obj.resumen, function(year_index, year_data) {
          var year_th_td = $("<th class='cabecera_anio' id='cabeceraanio_"+year_index+"'>").attr("rowspan","1").attr("colspan",(Object.keys(year_data).length * Object.keys(cols).length)).html("<button class='btn_show_year' id='"+year_index+"'>+</button><button class='btn_hide_year' id='"+year_index+"'>-</button>"+year_index);
          html_tr.append(year_th_td);
        });
      html_table.append(html_tr);
      var html_tr = $("<tr>");
        $.each(obj.resumen, function(year_index, year_data) {
          $.each(year_data, function(month_year, month_data) {
            all_periods+=year_index+''+month_year+'_';
            var month_th_td = $("<th class='cabecera_mes cabecera_meses_del_anio' id='cabecera_"+year_index+''+month_year+"'>").attr("colspan",Object.keys(cols).length).html("<button class='btn_show_month' id='btn_show_month_"+year_index+''+month_year+"'>+</button><button class='btn_hide_month' id='btn_hide_month_"+year_index+''+month_year+"'>-</button>"+nombre_mes[parseInt(month_year)-1]);
            html_tr.append(month_th_td);
          });
        });
      html_table.append(html_tr);
      var html_tr = $("<tr>");
        $.each(obj.resumen, function(year_index, year_data) {
          $.each(year_data, function(month_year, month_data) {
            $.each(cols, function(col_index, col_data) {
              var options="";
              if (col_index=="total_apostado") {
                options ="<th class='cabecera_dinero_apostado'>" ;      
              }else if(col_index=="total_ganado"){
                options ="<th class='cabecera_premios_pagados_por_pagar cabeceras"+year_index+''+month_year+" oculto'>" ; 
              }else if(col_index=="por_pagar"){
                options ="<th class='cabecera_premios_por_pagar cabeceras"+year_index+''+month_year+" oculto'>" ;                 
              }else if(col_index=="net_win"){
                options ="<th class='cabecera_net_win_t cabeceras"+year_index+''+month_year+" oculto'>" ;                 
              }else if(col_index=="hold"){
                options ="<th class='cabecera_hold cabeceras"+year_index+''+month_year+" oculto'>" ;                 
              }else if(col_index=="num_tickets"){
                options ="<th class='cabecera_tickets_emitidos cabeceras"+year_index+''+month_year+" oculto'>" ;                 
              }else if(col_index=="apuesta_x_ticket"){
                options ="<th class='cabecera_apuesta_por_ticket cabeceras"+year_index+''+month_year+" oculto'>" ;                 
              }else if(col_index=="num_tickets"){
                options ="<th class='cabecera_ticket_premiados cabeceras"+year_index+''+month_year+" oculto'>" ;                 
              }else if(col_index=="total_depositado_web"){
                options ="<th class='cabecera_dinero_depositado_web cabeceras"+year_index+''+month_year+" oculto'>" ;                 
              }else if(col_index=="total_retirado_web"){
                options ="<th class='cabecera_dinero_retirado_web cabeceras"+year_index+''+month_year+" oculto'>" ;                 
              }

              var year_th_td = $(options).html(col_data);
              html_tr.append(year_th_td);
            });
          });
        });
      html_table.append(html_tr);
      var new_obj = Array();
      $.each(obj.resumen, function(year_index, months_data) {
        $.each(months_data, function(month_index, csdv_data) {
          $.each(csdv_data, function(cdv_id, locales_data) {
            $.each(locales_data, function(local_id, local_data) {
              var local = $.extend({},local_data);
                local.year = year_index;
                local.month = month_index;
                local.period = year_index+""+month_index;
              new_obj.push(local);
            });
          });
        });
      });
      var totales_array = Array();
      $.each(obj.totales, function(year_index, months_data) {
        $.each(months_data, function(month_index,csdv_data) {
          $.each(csdv_data, function(cdv_id, total_local_data) {
                if (cdv_id!="total") {
                    if (month_index!="total" ) {
                        var total = $.extend({},total_local_data);
                        total.year = year_index;                
                        total.month = month_index;
                        total.period = year_index+""+month_index; 
                        total.canal_de_venta_id= cdv_id;                
                        totales_array.push(total);                                
                    }
                }
          });
        });
      });
      var super_total_array = Array();
      $.each(obj.totales, function(year_index, months_data) {
        $.each(months_data, function(month_index,csdv_data) {
          $.each(csdv_data, function(cdv_id, total_local_data) {
                if (cdv_id=="total") {
                    if (month_index!="total" ) {
                        var super_total = $.extend({},total_local_data);
                        super_total.year = year_index;                
                        super_total.month = month_index;
                        super_total.period = year_index+""+month_index; 
                        super_total.canal_de_venta_id= cdv_id;                
                        super_total_array.push(super_total); 
                    }                                   
                }
          });
        });
      });
      var obj_by_period = Object();
      $.each(new_obj, function(n_in, n_val) {
        if(!obj_by_period[n_val.period]){
          obj_by_period[n_val.period]=Object();
        }
        obj_by_period[n_val.period][n_val.local_id]=n_val;
      });
      var obj_total_by_period = Object();
      $.each(totales_array, function(n_in,n_val) {
        if (!obj_total_by_period[n_val.period]) {
            obj_total_by_period[n_val.period]=Object();
        };
        obj_total_by_period[n_val.period][n_val.canal_de_venta_id]=n_val;
      });
      var obj_super_total_by_period = Object();
      $.each(super_total_array, function(n_in,n_val) {
        if (!obj_super_total_by_period[n_val.period]) {
            obj_super_total_by_period[n_val.period]=Object();
        };
        obj_super_total_by_period[n_val.period][n_val.canal_de_venta_id]=n_val;
      });
      $.each(cdv, function(cdv_index, cdv_nombre) {
          $.each(new_obj, function(obj_index, obj_data) {
              if(obj_data.canal_de_venta_id == cdv_index){
                  if(obj_data.period == period_fin){
                      var html_tr = $("<tr class='clickable-row ' id='"+cdv_index+"'>");
                      html_tr.append('<td class="nombre_canal_de_venta_reporte">'+cdv_nombre+'</td>');
                      html_tr.append('<td class="nombre_local_reporte">'+obj_data.nombre+'</td>');
                      html_tr.append('<td class="mes_reporte">-</td>');
                      html_tr.append('<td class="periodo_reporte">-</td>');
                      html_tr.append('<td class="tercero_reporte">Tercero</td>');
                      html_tr.append('<td class="num_reporte">-</td>');
                      $.each(period_arr, function(period_index, period_val) {
                        $.each(cols, function(col_index, col_data) {
                            if(obj_by_period[period_index][obj_data.local_id]){
                              if(obj_by_period[period_index][obj_data.local_id][col_index]){
                                  if(col_index=="total_apostado"){
                                        html_tr.append('<td class="mostrado">'+obj_by_period[period_index][obj_data.local_id][col_index]+'</td>');
                                  }else{
                                        html_tr.append('<td class="'+period_index+' oculto">'+obj_by_period[period_index][obj_data.local_id][col_index]+'</td>');
                                  }
                              }else{
                                  if(col_index=="total_apostado"){
                                      html_tr.append('<td class="mostrado">0</td>');
                                  }else{
                                      html_tr.append('<td class="'+period_index+' oculto">0</td>');
                                  }
                              }
                            }else{
                                if(col_index=="total_apostado"){                    
                                      html_tr.append('<td class="mostrado">0</td>');
                                }else{
                                      html_tr.append('<td class="'+period_index+' oculto">0</td>');
                                }
                            }
                        });
                      });
                      html_table.append(html_tr);   
                      if (new_obj.length > obj_index+1) {
                                    var next_object = new_obj[obj_index+1];
                                    if (obj_data.canal_de_venta_id.localeCompare(next_object.canal_de_venta_id) != 0) {
                                    var html_tr1 = $("<tr class='total_reporte clickable-row' id='"+cdv_index+"' >");
                                        html_tr1.append('<td colspan="6" class="etiqueta_total">Total Canal '+cdv_nombre+'</td>');                          
                                    $.each(period_arr, function(period_index, period_val) {
                                        $.each(cols, function(col_index, col_data) {
                                          if(obj_total_by_period[period_index][obj_data.canal_de_venta_id]){
                                                if(obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]){
                                                    if (col_index=="total_apostado") {
                                                        html_tr1.append('<td class="mostrado">'+obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]+'</td>');
                                                    }else{
                                                        html_tr1.append('<td class="'+period_index +'  oculto">'+obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]+'</td>');  
                                                    }
                                                }else{
                                                  if (col_index=="total_apostado") {
                                                      html_tr1.append('<td class="mostrado">0</td>');
                                                  }else{
                                                      html_tr1.append('<td class="'+period_index+' oculto">0</td>');
                                                  }
                                                }
                                          }else{
                                                if (col_index=="total_apostado") {
                                                      html_tr1.append('<td class="mostrado">0</td>');  
                                                }else{
                                                      html_tr1.append('<td class="'+period_index+' oculto">0</td>');
                                                }
                                          }
                                        });
                                    });

                                    }
                      };
                      if(new_obj.length -1 == obj_index){
                          var html_tr1 = $("<tr class='total_reporte clickable-row' id='"+cdv_index+"'>");
                              html_tr1.append('<td colspan="6" class="etiqueta_total">Total Canal '+cdv_nombre+'</td>');
                              $.each(period_arr, function(period_index, period_val) {
                                  $.each(cols, function(col_index, col_data) {
                                    if(obj_total_by_period[period_index][obj_data.canal_de_venta_id]){
                                          if(obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]){
                                              if (col_index=="total_apostado") {
                                                  html_tr1.append('<td class="mostrado">'+obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]+'</td>');
                                              }else{
                                                  html_tr1.append('<td class="'+period_index+' oculto" >'+obj_total_by_period[period_index][obj_data.canal_de_venta_id][col_index]+'</td>');    
                                              }


                                          }else{
                                              if(col_index=="total_apostado") {
                                                  html_tr1.append('<td class="mostrado">0</td>');
                                              }else{
                                                  html_tr1.append('<td class="'+period_index+' oculto" >0</td>');
                                              }

                                          }
                                    }else{
                                          if(col_index=="total_apostado") {
                                                html_tr1.append('<td class="mostrado">0</td>');
                                          }else{
                                                html_tr1.append('<td class="'+period_index+' oculto" >0</td>');
                                          }

                                    }
                                  });
                              });
                      }


                      html_table.append(html_tr1); 

                  }
              }
          });
      });

      var html_tr2 = $("<tr class='total_reporte clickable-row'>");
      html_tr2.append('<td colspan="6" class="etiqueta_total">Total Canales</td>');

      $.each(obj_super_total_by_period, function(index_stotal, val_stotal) {
            html_tr2.append('<td class="mostrado">'+val_stotal.total.total_apostado+'</td>');
            html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.total_ganado+'</td>');
            html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.por_pagar+'</td>');
            html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.net_win+'</td>');
            html_tr2.append('<td class="'+val_stotal.total.period+' oculto">0</td>');                  
            html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.num_tickets+'</td>');
            html_tr2.append('<td class="'+val_stotal.total.period+' oculto">0</td>');            
            html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.total_depositado_web+'</td>');
            html_tr2.append('<td class="'+val_stotal.total.period+' oculto">'+val_stotal.total.total_retirado_web+'</td>');
      });
      html_table.append(html_tr2); 

      $(".tabla_contenedor_reportes").html(html_table);
      loading();
      sec_reportes_events();
}
function sec_reportes(){
    if(sec_id=="reportes"){
        console.log("sec_reportes");
        sec_reportes_settings();
        sec_reportes_get_reportes();
    }
}
function sec_reportes_settings(){
    console.log("sec_reportes_settings");
    $('.select_picker_periodo_de_tiempo').selectpicker();
    $('.select_picker_paiddate').selectpicker();      
    $('.selectpicker_sportbook_group').selectpicker();
    $('.selectpicker_bet_type').selectpicker();
    $('.selectpicker_state').selectpicker();
    $('.selectpicker_source').selectpicker();
    $('.selectpicker_is_live').selectpicker();
    $('.selectpicker_is_test').selectpicker();
    $('.selectpicker_currency').selectpicker();
    $('.selectpicker_cashdesk').selectpicker();
    $('.selectpicker_cash_desk_info').selectpicker();
    $('.selectpicker_betshop').selectpicker();
    $('.selectpicker_betshop_info').selectpicker();
    $('.selectpicker_bonus_type').selectpicker(); 
    $('.selectpicker_is_cash_desk_paid').selectpicker();  
    $('.proveedores').selectpicker();

    $('#form_time_desde').timepicker({
        minuteStep:10,
        showSeconds: true,
        secondStep:15
    });
    $('#form_time_hasta').timepicker({
        minuteStep:10,
        showSeconds:true,
        secondStep:15       
    });
}
function sec_reportes_events(){

    $(".btn_filtrar_reporte").on("click",function(e) {
        sec_reportes_get_reportes();
    });

    //collapse - expand table months
       
    $(".oculto").show();  
    $(".cabecera_mes").attr("colspan","9");
    $(".btn_hide_month").show();
    $(".btn_show_month").hide();
    $(".btn_show_month").on("click",function(){
        var current_period = $(this).attr("id").split("_")[3];
        $("#btn_show_month_"+current_period).hide();
        $("#btn_hide_month_"+current_period).show();
        $("#cabecera_"+current_period).attr("colspan",9);
        $(".cabeceras"+current_period).show();
        $("."+current_period).show();                                
    });
    $(".btn_hide_month").on("click",function(){
        var current_period = $(this).attr("id").split("_")[3];
        $("#btn_hide_month_"+current_period).hide();
        $("#btn_show_month_"+current_period).show();
        $("#cabecera_"+current_period).attr("colspan",1);
        $(".cabeceras"+current_period).hide();
        $("."+current_period).hide();                
    });

    //collapse - expand table years 
    $(".btn_hide_year").show();
    $(".btn_show_year").hide();
    var array_all_periods=all_periods.slice(0, -1).split("_");
    $.each(array_all_periods, function(index_period, current_period) {
        $(".btn_show_year").on("click",function(){
            $(".btn_show_year").hide();
            $(".btn_hide_year").show();
            $("#btn_show_month_"+current_period).hide();
            $("#btn_hide_month_"+current_period).show();
            var current_year = $(this).attr("id");
            $("#cabecera_"+current_period).attr("colspan",9);
            $(".cabeceras"+current_period).show();
            $("."+current_period).show();
        });    
        $(".btn_hide_year").on("click",function(){
            $(".btn_hide_year").hide();
            $(".btn_show_year").show();
            $("#btn_hide_month_"+current_period).hide();
            $("#btn_show_month_"+current_period).show();            
            var current_year = $(this).attr("id"); 
            $("#cabecera_"+current_period).attr("colspan",1);
            $(".cabeceras"+current_period).hide();
            $("."+current_period).hide(); 
        });
    });

    $("#reporte_apuestas").tableExport({
            headings: true,                    
            footers: true,                     
            formats: ["xlsx","xls", "csv", "txt"],    
            fileName: "reporte_apuestas",                    
            bootstrap: true,                  
            position: "top",
            exportButtons: true
    });


    $('td').each(function() {
        var cellvalue = $(this).html();
        if ( cellvalue < 0) {
            $(this).wrapInner('<strong class="negative_number"></strong>');    
        }
    });

    $('.tabla_reportes').on('click', '.clickable-row', function(event) {
        $(this).addClass('active').siblings().removeClass('active');
    });
}
$(function() {
	$('.select_picker_periodo_de_tiempo').on('change', function(){
		var selected = $(this).find("option:selected").val();
		if (selected==6) {
    	//$('.form_date_desde > .form-control').prop('disabled', false);
    	//$('.form_date_hasta > .form-control').prop('disabled', false);	
    	$('.iconofecha').css("cursor","not-allowed","important");        						        			
			$(".input_time_desde_ocultar_mostrar").css("width","73%");
			$(".input_time_hasta_ocultar_mostrar").css("width","73%");        
			$(".ocultar_mostrar_timepicker").css("display", "block");
			$(".span_mostrar_timepicker").css("padding","8px").click();
			$(".bootstrap-timepicker-widget").css("z-index","1");
		}else{
    	/*
      $('.form_date_desde').datetimepicker('remove');
      $('.form_date_desde > .form-control').prop('disabled', true);	
    	$('.form_date_hasta').datetimepicker('remove');
      $('.form_date_hasta > .form-control').prop('disabled', true);	            		
			
      */
      $(".ocultar_mostrar_timepicker").css("display", "none");
			$(".bootstrap-timepicker-widget").css("z-index","-1");
		}
	});
});

function formatonumeros(x) {
  //console.log("formatonumeros");
  if (x) {
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }else{
    return 0;
  }
}
function getcurrentdate(){
  //console.log("getcurrentdate");
  var today = new Date();
  var dd = today.getDate();
  var mm = today.getMonth()+1; //January is 0!
  var yyyy = today.getFullYear();
  if(dd<10) {dd = '0'+dd} 
  if(mm<10) {mm = '0'+mm} 
  today =  dd+ '-'+ mm+ '-'+yyyy ;
  return today;
} 
function gettomorrowdate(){
  //console.log("gettomorrowdate");
  var today = new Date();
  var dd = today.getDate()+1;
  var mm = today.getMonth()+1; //January is 0!
  var yyyy = today.getFullYear();
  if(dd<10) {dd = '0'+dd} 
  if(mm<10) {mm = '0'+mm} 
  today =  dd+ '-'+ mm+ '-'+yyyy ;
  return today;
}
$(".recaudacion_datepicker")
  .datepicker("destroy")
  .datepicker({
    dateFormat:'dd-mm-yy',
    changeYear: true
  })
  .on("change", function(ev) {
    $(this).datepicker('hide');
    var newDate = $(this).datepicker("getDate");
    $("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
});
$(".recaudacion_datepicker_tickets")
  .datepicker("destroy")
  .datepicker({
    dateFormat:'dd-mm-yy'
  })
  .on("change", function(ev) {
    $(this).datepicker('hide');
    var newDate = $(this).datepicker("getDate");
    $("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
});
$.datepicker.regional['es'] = {
     closeText: 'Cerrar',
     prevText: '< Ant',
     nextText: 'Sig >',
     currentText: 'Hoy',
     monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
     monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
     dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
     dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
     dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
     weekHeader: 'Sm',
     dateFormat: 'dd-mm-yy',
     firstDay: 1,
     isRTL: false,
     showMonthAfterYear: false,
     yearSuffix: ''
 };
$.datepicker.setDefaults($.datepicker.regional['es']);