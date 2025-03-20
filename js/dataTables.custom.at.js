       
       var data = {};
        data.filtro={};
        //año mes dia
        data.filtro.fecha_inicio= "2017-04-01";
        data.filtro.fecha_fin= "2017-04-02";
        data.filtro={}
 
        /*data.filtro.locales={};
        data.filtro.locales[0] = 200; */        
        data.where="liquidaciones";


        $.ajax({
            type: "POST",
            url: "http://192.168.10.56:7080/api/?json",
            data:data
        })
         .done(function( responsedata, textStatus, jqXHR ) {
            console.log(responsedata);
            var obj = jQuery.parseJSON(responsedata);
            console.log(obj);
            var datafinal=[];
            var i = 0;
            $.each(obj, function(index, val) {
               $.each(val, function(index1, val1) {
                        //var newObject={};
                        $.each(val1.liquidaciones, function(index2, val2) {
                          //console.log(val1.local_nombre);                               
                            $.each(val2, function(index3, val3) {
                               var newObject=[val1.local_nombre,val1.dias_procesados,val3.canal_de_venta.codigo,val3.total_depositado,val3.total_anulado_retirado,val3.total_apostado,val3.total_ganado,val3.total_pagado,val3.total_produccion,val3.total_depositado_web,val3.total_retirado_web,val3.total_caja_web,val3.total_cliente,val3.total_freegames,val3.pagado_en_otra_tienda,val3.pagado_de_otra_tienda,val3.total_pagos_fisicos,val3.caja_fisico];
                                 datafinal[i] =  newObject;
                          i++;
                        });
                      });

               });
            });

                DisplayStudentsCurriculumTableData(datafinal);
});




function DisplayStudentsCurriculumTableData(model) {


    var heightdoc = window.innerHeight;
    var heightnavbar= $(".navbar-header").height();
    var heighttable =heightdoc-heightnavbar-150;

    var curriculumStudentsDataTable = $('#example').removeAttr('width').DataTable({       
    responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal( {
                    header: function ( row ) {
                        var data = row.data();
                        return 'Details for '+data[0]+' '+data[1];
                    }
                } ),
                renderer: function ( api, rowIdx, columns ) {
                    var data = $.map( columns, function ( col, i ) {
                        return '<tr>'+
                                '<td>'+col.title+':'+'</td> '+
                                '<td>'+col.data+'</td>'+
                            '</tr>';
                    } ).join('');
 
                    return $('<table/>').append( data );
                }
            }
        },
      fixedHeader: {
            header: true
      },
      bRetrieve: true,
      sPaginationType: "full_numbers",
      paging: true,
      bProcessing: true,
      bAutoWidth: true,
      bStateSave: false,
      sScrollY: heighttable, 
      //sScrollX: "400%", 
      //sScrollXInner: "400%", 
      //bScrollCollapse: true,    
      bPaginate: true, 
      bFilter: true,
      Sorting: [[1, 'asc']], 
      rowsGroup: [0,1],
      data:model,
      fixedColumns:   {
        leftColumns: 2
      },
      //dataSrc: "",
      //ajax:"datosfull.txt",
      columnDefs: [
        { className: "columasControl", "targets": [ 0,1] },
        { className: "columasControlhead","targets": [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17] }
      ],  
      aoColumns: [
          {
            "model": "nombrelocales",
            sDefaultContent: ""

          },     
          {
            "model": "diasprocesados",
            sDefaultContent: ""
          },               
          {
            "model": "canalventaid",
            sDefaultContent: "",
            className: ""     
          },      
          {
            "model": "totaldepositado",
            sDefaultContent: "",
            className: ""       
          },
          {
            "model": "totalanuladoretirado",
            sDefaultContent: ""

          },
          {
            "model": "totalapostado",
            sDefaultContent: ""
          },      
          {
            "model": "totalganado",
            sDefaultContent: "",
            className: ""     
          },      
          {
            "model": "totalpagado",
            sDefaultContent: "",
            className: ""       
          },
          {
            "model": "totalproduccion",
            sDefaultContent: "",
            className: ""       
          }, 
          {
            "model": "totaldepositadoweb",
            sDefaultContent: ""

          },
          {
            "model": "totalretiradoweb",
            sDefaultContent: ""
          },      
          {
            "model": "totalcajaweb",
            sDefaultContent: "",
            className: ""     
          },      
          {
            "model": "totalcliente",
            sDefaultContent: "",
            className: ""       
          },
          {
            "model": "totalfreegames",
            sDefaultContent: "",
            className: ""       
          },
          {
            "model": "pagadoenotratienda",
            sDefaultContent: ""

          },
          {
            "model": "pagadodeotratienda",
            sDefaultContent: ""
          },      
          {
            "model": "totalpagosfisicos",
            sDefaultContent: "",
            className: ""     
          },      
          {
            "model": "cajafisico",
            sDefaultContent: "",
            className: ""       
          }

         ],      
        pageLength: '18',
    });
  curriculumStudentsDataTable.columns.adjust().draw().responsive.recalc();

}