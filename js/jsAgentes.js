/**
 * @author Edwin Huarhua Chambi
 * @description Grid AGENTES
 */
Ext.QuickTips.init();
Ext.Loader.setConfig({enabled: true});
//Ext.Loader.setPath('Ext.ux', 'ext-6.2.0/examples/ux');
Ext.require([
    'Ext.grid.*',
    'Ext.data.*',
    'Ext.panel.*',
    'Ext.layout.container.Border',
	'Ext.grid.filters.Filters'
]);
Ext.onReady(function(){
	let _controlador = "../sec_agentes.php";
	///////////////////////////////////////////////////////////////////////////
	//Grid Agentes
	///////////////////////////////////////////////////////////////////////////
    var cmbOperativo= Ext.create('Ext.form.ComboBox',{
		fieldLabel: 'Estado',
		name:'operativo',
		labelWidth: 80,
		width: 210,
		//inputWidth: 170,
		//labelWidth: 30,
		queryMode: 'local',
		//fieldLabel: 'Mes',  
		//hiddenName: 'sel_mes', // Este campo es importante, sin él no funciona el combo  
		valueField: 'id_estado',  
		displayField: 'estado_local',  
		typeAhead: true,  
		mode: 'local',  
		triggerAction: 'all',  
		selectOnFocus: true,  
		autoSelect :true,
		forceSelection: true,
		emptyText:'Estado..',
		editable: false,
		readOnly:false,
        value:100,
		store: new Ext.data.SimpleStore({  
			id      : 0 ,  
			fields  : [ 'id_estado', 'estado_local' ],  
			data    : [  
				['100','TODOS'],['1', 'OPERATIVO'], ['0', 'INOPERATIVO'], ['2', 'CERRADO']
			]  
		}),
        listeners:{
			scope: this,
			'select': function(combo, record, index){
				//gridLocalAgente.setTitle("Agentes:") + cmbOperativo.getRawValue();
			}
	    }
	});
    
    var btnFiltrar = new Ext.Button({
		text : 'Filtrar',
		handler : function(){
            dsLocalAgente.clearFilter();
            gridLocalAgente.setTitle("Agentes: " + cmbOperativo.getRawValue());
            if(cmbOperativo.getValue()=="100"){
                //dsLocalAgente.clearFilter();
                dsLocalAgente.filterBy(function(r1) {
                    return r1.data.operativo != "";
                });
            }else{
                dsLocalAgente.filter('operativo', cmbOperativo.getValue());
            }
		}
	});
    var btnExcel= new Ext.Button({
		text : 'XLS',
		handler : function(){
            var arr_data=dsLocalAgente.getData();
            var arr_model_agentes = new Array();
            //alert(arr_data.items.length);

            for (var i = 0; i < arr_data.items.length; i++) {
                let _data=arr_data.items[i].data;
                //console.log(_data.id);
                delete _data['id'];
                delete _data['operativo'];
                //arr_model_agentes.push(_data);
				arr_model_agentes.push({
					'Id': _data.zona_id,
					'Zona': _data.zona_nombre,
					'Local id': _data.local_id,
					'Centro Costo': _data.local_cc_id,
					'Local': _data.local_nombre,
					'Fecha Inicio': _data.fecha_inicio_operacion,
					'Fecha Fin': _data.fecha_fin_operacion
				});
            }
            //var arr2= dsLocalAgente.getRange();
            //alert(arr_model_agentes.length);
            generarExcel(arr_model_agentes, "REPORTE_AGENTES_"+cmbOperativo.getRawValue()+ "_"+ formatDate(new Date()));
		}
	});
	var dsLocalAgente= Ext.create('Ext.data.Store', {
	    buffered: false,
	    pageSize: 500,
	    model: 'modelLocalesAgente',
	    proxy: {
            type: 'ajax',
            //url: '../borrar.php?action=AJAX_OBTENER_GRUPO_CORREO',
			url: _controlador,
			method: 'POST',
			extraParams: {
				do: 'AJAX_OBTENER_AGENTES'
			},
			reader:{
				type: 'json',
				root: 'results'
			}
        }
	});
	dsLocalAgente.load();
	var colLocalAgente= ([
        {
            xtype: 'rownumberer',
            text:'#',
            width: 40,
            sortable: false
            //locked: true
        },
		{text: 'Id', dataIndex:'zona_id', width: 50},
        {text: 'Zona', dataIndex: 'zona_nombre', width: 120},
        {text: 'Razon Social', dataIndex: 'zona_razon_social', width: 120, hidden:true},
		{text: 'Local id', dataIndex: 'local_id', width: 80},
		{text: 'Centro Costo', dataIndex: 'local_cc_id', width: 100},
        {text: 'Local', dataIndex: 'local_nombre', width: 250, filter:{type: 'string'}},
        {text: 'Operativo', dataIndex: 'operativo', width: 80, hidden:true},
        {text: 'Fecha Inicio', dataIndex: 'fecha_inicio_operacion', width: 150},
        {text: 'Fecha Fin', dataIndex: 'fecha_fin_operacion', width: 150}
    ]);
	var gridLocalAgente = Ext.create('Ext.grid.Panel', {
        title: 'Agentes:TODOS',
        width: 1000,
        height: 600,
        border:true,
        scroll: true,
        columnLines: true,
        rowLines:true,
        stripeRows: true,
        loadMask: true,
        //margin: '0 0 10 0',
        store: dsLocalAgente,
        columns: colLocalAgente,
		enableLocking:false,
        //renderTo: 'div_general',
        plugins: 'gridfilters',
		listeners: {
        	scope: this,
            selectionchange: function(model, records){
				
			}
		}
	});	
	////////////////////////////////////////////////////////
	// BOTONES
	////////////////////////////////////////////////////////
	
	////////////////////////////////////////////////////////////////////////
	//FUNCIONALIDADES
	////////////////////////////////////////////////////////////////////////
    function formatDate(date) {
		var d = new Date(date),
			month = '' + (d.getMonth() + 1),
			day = '' + d.getDate(),
			year = d.getFullYear();
	
		if (month.length < 2) 
			month = '0' + month;
		if (day.length < 2) 
			day = '0' + day;
	
		return [year, month, day].join('-');
	}
	/**
	 * 
	 * @param {*} rows 
	 * funcionalidad para generar desde front end, archivos xls, basico.
	 * origen "https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"
	 */
	function generarExcel(rows, _nombre_archivo){
        /* generate worksheet and workbook */
        const worksheet = XLSX.utils.json_to_sheet(rows);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Dates");

        /* fix headers */
        //XLSX.utils.sheet_add_aoa(worksheet, [["Nombre", "Edad"]], { origin: "A1" });
		XLSX.utils.sheet_add_aoa(worksheet, [["Id", "Zona", "Local id", "Centro Costo"]], { origin: "A1" });

        /* calculate column width */
        //const max_width = rows.reduce((w, r) => Math.max(w, r.zona_nombre.length), 15);
        //worksheet["!cols"] = [ { wch: max_width } ];
        worksheet["!cols"] = [ { wch: 10 }, { wch: 15 }, { wch: 10 }, { wch: 10 }, { wch: 50 }, { wch: 20 }, {wch:20} ];
        

        /* create an XLSX file and try to save to Presidents.xlsx */
        XLSX.writeFile(workbook, _nombre_archivo + ".xlsx", { compression: true });
	}
    ////////////////////////////////////////////////////////////////////////
	//FORMULARIO
	//////////////////////////////////////////////////////////////////////// 
	var frmAgentes = new Ext.FormPanel({ 
		name : 'frm_agentes',
		id: 'frm_agentes',
		frame: true,
		//labelAlign: 'left',
		//title: 'Grupo Correo ',
		border:false,
        //layout: 'form',
		width: 1100,
		height:650,
		renderTo: 'div_general',		
		buttonAlign:'left',
		items : [cmbOperativo, btnFiltrar, btnExcel, gridLocalAgente]
		//buttons:[ btnNuevo, btnSubmit, btnModificar]	
	});
	
});