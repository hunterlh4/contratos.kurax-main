/**
 * @author Edwin Huarhua Chambi
 * @description CHECKLIST Reporte
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
	let _controlador = "../sec_checklist_cajero.php";
	let _dataPivot;
	let _titulo_grilla = "Grilla CheckList Cajero :";
	///////////////////////////////////////////////////////////////////////////
	//Combo Locales
	///////////////////////////////////////////////////////////////////////////
	var dsLocales= Ext.create('Ext.data.Store', {
	    buffered: false,
	    pageSize: 50,
	    model: 'modelLocales',
	    proxy: {
            type: 'ajax',
			url: _controlador,
			extraParams:{
				do: 'AJAX_OBTENER_LOCALES'
			},
			method: 'POST',
			reader:{
				type: 'json',
				root: 'results'
			}
        }
	});
	dsLocales.load();
	var cmbLocales= new Ext.form.ComboBox({
		fieldLabel: 'Local',
		name:'cmb_locales',
		id:'cmb_locales',
		labelWidth: 50,
		width:350,
        store: dsLocales,
        displayField: 'nombre',
        typeAhead: true,
        mode: 'local',
        queryMode: 'local',
        forceSelection: true,
        triggerAction: 'all',
        emptyText:'Selecciona ..',
		editable: true,
		valueField: 'id',
        selectOnFocus:true,
		allowBlank  :false,
		listeners:{
			scope: this,
			'select': function(combo, record, index){
				//fxCargarCampania();
				gridCheckListPivot.setTitle(_titulo_grilla + record.data['direccion']);
			},
			'beforequery': function(record){  
				record.query = new RegExp(record.query, 'i');
				record.forceAll = true;
			}
	    }
    });
	///////////////////////////////////////////////////////////////////////////
	//Combo Anio
	///////////////////////////////////////////////////////////////////////////
	var arrAnios= new Array();
	var danio_actual=new Date();
	var ianio_actual= danio_actual.getFullYear();	
	//var imes_actual =ianio_actual.getMonth();
	//alert (ianio_actual);
	for(var i=2023;i<ianio_actual+3;i++){
		arrAnios.push([i]);
	}
	var cmbAnio= Ext.create('Ext.form.ComboBox',{
		fieldLabel:'Año',
		id:'cmb_anio',
		name:'cmb_anio',
		//inputWidth: 170,
		//labelWidth: 30,
		labelWidth: 50,
		width:350,
		queryMode: 'local',
		//fieldLabel: 'Anio',  
		//hiddenName: 'sel_anio', // Este campo es importante, sin él no funciona el combo  
		valueField: 'ianio',  
		displayField: 'ianio',  
		typeAhead: true,  
		mode: 'local',  
		triggerAction: 'all',  
		selectOnFocus: true,  
		autoSelect :true,
		forceSelection: true,
		emptyText:'Anio Camp..',
		editable: true,
		value : new Date().getFullYear(),
		store: new Ext.data.SimpleStore({  
			id      : 0 ,  
			fields  : [  'ianio'],
			data    : arrAnios  
		}),
		listeners:{
			scope: this,
			'select': function(combo, record, index){
				//fxCargarCampania();
			}
			/*
			'afterrendered': function() {
				let anio = new Date().getFullYear();
				var combo = Ext.getCmp('cmb_anio');
                combo.setValue(anio);
				//	this.setValue(anio);    
			 }
			 */
	    }
	});
	var cmbMes= Ext.create('Ext.form.ComboBox',{
		fieldLabel:'Mes',
		name:'cmb_mes',
		width: 100,
		//inputWidth: 170,
		//labelWidth: 30,
		labelWidth: 50,
		width:350,
		queryMode: 'local',
		//fieldLabel: 'Mes',  
		//hiddenName: 'sel_mes', // Este campo es importante, sin él no funciona el combo  
		valueField: 'imes',  
		displayField: 'vmes',  
		typeAhead: true,  
		mode: 'local',  
		triggerAction: 'all',  
		selectOnFocus: true,  
		autoSelect :true,
		forceSelection: true,
		emptyText:'Mes Camp..',
		editable: true,
		value : new Date().getMonth() + 1,
		store: new Ext.data.SimpleStore({  
			id      : 0 ,  
			fields  : [  'imes', 'vmes' ],  
			data    : [  
			     [1, 'Enero'], [2, 'Febrero'], [3, 'Marzo'],
			     [4, 'Abril'], [5, 'Mayo'], [6, 'Junio'],
			     [7, 'Julio'], [8, 'Agosto'], [9, 'Septiembre'],
			     [10, 'Octubre'], [11, 'Noviembre'], [12, 'Diciembre']
			     
			]  
		}),
		listeners:{
			scope: this,
			'select': function(combo, record, index){
				
			}
	    }
	});

	///////////////////////////////////////////////////////////////////////////
	//Grid Checklist
	///////////////////////////////////////////////////////////////////////////
	function createModelWithCustomProxy(fields) {
		return Ext.define('modelMes' + Ext.id(), {
			extend: 'Ext.data.Model',
			fields: fields,    
			proxy: {
				type: 'memory',
				reader: {
					type: 'json',
					//totalProperty: 'tc',
					root: 'results'
				}
			}
		});
	};
	let modelDinamico;
	
	
	function fxRendererDia(value,metaData, record, rowIndex, colIndex, store){
	    /*
		if (record.get ( 'usu_vcodigo' )=="sin asignar") {
			value='<div style="background-color: #F6CECE"  >&nbsp;' +value+ '</div>';
		}
		*/
		//console.log(value);
		if(value==null){
			value='-';
		}
		if(value==0){
			value = '<div style="background-color: #F6CECE"  >&nbsp;<input type="button" value='+value+'></input></div>'
		}
		if(value==2){
			//quiere decir q fue corregido  
			value = '<div style="background-color: #73C6B6"  >&nbsp;'+value+'</div>'
		}
		return value;
	}
	
	var dsCheckListPivot= Ext.create('Ext.data.Store', {
	    buffered: false,
	    pageSize: 50,
	    //model: 'modelCheckListPivot',
		fields: [ 
			'id', 'item_numero', 'item_nombre', 'item_descripcion', 'indicador', 'estado'
		],
        //groupField:'indicador',
	    proxy: {
            type: 'ajax',
			url: _controlador,
			method: 'POST',
			extraParams: {
				do: 'AJAX_OBTENER_CHECKLIST_PIVOT'
			},
			reader:{
				type: 'json',
				root: 'results'
			}
        }
	});
	//dsCheckListPivot.load();
	//dsCuenta.load();
	var colCheckList= [
		{text: 'Id', dataIndex:'id', width: 40, hidden:true },
        {text: 'Orden', dataIndex:'item_numero', width: 60, hidden:true},
		{text: 'Nombre', dataIndex:'item_nombre', width: 280 },
        {text: 'Descripcion', dataIndex:'item_descripcion', width: 250 },
        {text: 'Indicador', dataIndex:'indicador', width: 100 }
    ];
    var colCheckList2=[
        {text: 'Id', dataIndex:'id', width: 40, hidden:true },
        {text: 'Orden', dataIndex:'item_numero', width: 60, hidden:true},
		//{text: 'Nombre', dataIndex:'item_nombre', width: 280 },
        {text: 'Descripcion', dataIndex:'item_descripcion', width: 250 },
        {text: 'Indicador', dataIndex:'indicador', width: 100 }
    ];
    let arrCheckListPivot = Array();
	var gridCheckListPivot = Ext.create('Ext.grid.Panel', {
        //title: 'Check List ' + new Date().toLocaleString(),
		//xtype: 'reconfigure-grid',
		xtype: 'locking-grid',
        id : 'grid_check_list_pivot',
        name :'grid_check_list_pivot',
		title: _titulo_grilla ,
        width: 1400,
        height: 600,
        border:true,
        scroll: true,
        columnLines: true,
        rowLines:true,
        stripeRows: true,
        loadMask: true,
		//cls:'custom-grid' ,
		viewConfig: {
			stripeRows: true
		},
		enableLocking:true,
		startCollapsed : true,
        //margin: '0 0 10 0',
        //store: dsCheckListPivot,
        //columns: colCheckList,
        //features: [filters],
        features: [{
			id: 'group',
	        //ftype: 'grouping',
			ftype: 'groupingsummary',
	        //groupHeaderTpl: '{columnName}: {name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})',
	        //groupHeaderTpl: ' {name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})',
			groupHeaderTpl: '{name}',
	        hideGroupedHeader: true,
	        startCollapsed: true
	        //id: 'fxRendererDiaarc_ianio_campania'
	    }],
        listeners: {
        	scope: this,
			cellclick: function( grid, td, cellIndex, record, tr, rowIndex, e, eOpts )  {
				let inro = cellIndex / 2 +1;
				let vnro = "id" + (inro<10 ? "0"+inro:inro);
				//let x= grid.getSelectionModel().selection(record[rowIndex]);
				let  checklistusuario_id = record.data[vnro];
				let dfecha_registro =cmbAnio.getValue() + "-" + (cmbMes.getValue()<10 ? "0"+cmbMes.getValue():cmbMes.getValue())  + "-" +(inro<10 ? "0"+inro:inro);
				
				let item_respuesta = record.data[dfecha_registro];
				if(item_respuesta==0 && dfecha_registro==formatDate(new Date()) ){
					//solo en caso de que sea respuesta 0 se reqaliza peticion AJAX para corregir la respuesta
					Ext.MessageBox.show({
						title: 'Confirmacion',
						msg: '¿Se corregira incidencia?',
						buttons: Ext.MessageBox.OKCANCEL,
						icon: Ext.MessageBox.WARNING,
						/*
						buttonText: {
							yes: 'Sí',
							no: 'No'
						},
						*/
						fn: function(btn){
							if(btn == 'ok'){
								Ext.Ajax.request({  
									url: _controlador,  
									method: 'POST',
									params: {  					
										do : 'AJAX_CORREGIR_CHECKLIST_USUARIO',
										checklistusuario_id : checklistusuario_id
									},
									success:  function(response){
										var jsonData = Ext.JSON.decode(response.responseText);
										alert(jsonData.msg);
										fxCargarChecklistUsuario();
									},
									failure: function(error) {
										alert('Error en la conexion.');
									},  
									timeout: 30000  	
								});		
							} else {
								return;
							}
						}
					});
				}
			}
		}
		
	});	
	///////////////////////////////////////////////////////////////////////////
	//Formulario Checklist
	///////////////////////////////////////////////////////////////////////////
    

	////////////////////////////////////////////////////////
	// BOTONES
	////////////////////////////////////////////////////////
	var hidIndicador= new Ext.form.Hidden({
		name:'hid_indicador',
		id:'hid_indicador',
		value:0
	});	
	var btnCargar= new Ext.Button({
		text : 'Cargar',
		handler : function(){
            //gridCheckListPivot.setColumns(colCheckList);
			fxCargarChecklistUsuario();
		}
	});
    var btnExcel= new Ext.Button({
		text : 'XLS',
		handler : function(){
			if(_dataPivot){
				let arr_copia = Array.from(_dataPivot.results);
				if(arr_copia.length>0){
					for (var key in arr_copia) {
						console.log(arr_copia[key]);
						//arr_copia[key].splice('id', 1);
						delete arr_copia[key].id;
						for (var key_interno in arr_copia[key]) {
							var valor_interno = arr_copia[key][key_interno];
							if(key_interno.substring(0, 2)=="id" ){
								delete arr_copia[key][key_interno];
							}
							//console.log(key_interno, valor_interno);
						}
					}
					let nombre_archivo = "ChecklistReporte, Local:" + cmbLocales.getRawValue() +", Fecha: "+ formatDate(new Date());
					generarExcel(arr_copia, nombre_archivo);
				}else{
					var loadingMsg = Ext.Msg.show({
						title: 'Vacio',
						message: 'sin datos',
						closable: true
					});
				}
			}else{
				var loadingMsg = Ext.Msg.show({
					title: 'Vacio',
					message: 'sin datos',
					closable: true
				});
			}
			
		}
	});
 
	////////////////////////////////////////////////////////////////////////
	//FUNCIONALIDADES
	////////////////////////////////////////////////////////////////////////
	var fxCargarChecklistUsuario= function(){
		if(cmbAnio.getValue()!=null  && cmbMes.getValue()!=null && cmbLocales.getValue()!=null){
			//let titulo = gridCheckListPivot.getTitle();
			gridCheckListPivot.setTitle(_titulo_grilla + " " + cmbAnio.getValue()+ "-" + cmbMes.getValue());
			Ext.Ajax.request({  
				url: _controlador,  
				method: 'POST',
				params: {  					
					do : 'AJAX_OBTENER_CHECKLIST_PIVOT',
					ianio : cmbAnio.getValue(),
					imes : cmbMes.getValue(),
					local_id : cmbLocales.getValue()
				},
				success:  function(response){
					Ext.Msg.alert('Carga...','Cargado');
					gridCheckListPivot.getStore().removeAll();
					var jsonData = Ext.JSON.decode(response.responseText);
					//alert(jsonData);
					_dataPivot=jsonData;
					let icolumnas = jsonData.cols;
					//let campos=new Array();
					if(_dataPivot.results.length>0){
						let campos = Object.keys(jsonData.results[0]);
						let campos_cortados = campos.slice(0, icolumnas);
						let campos_cortados_colcheck = new Array();
						let itotal_campos=campos.length;
						for (var key in campos_cortados) {
							//console.log(arr_jq_TabContents[key]);
							var iwidth = key==1 ? 260:90;
							if(key<=1){
								campos_cortados_colcheck.push({text: campos_cortados[key], dataIndex:campos_cortados[key], 
								sortable : false,
								width: iwidth, locked: true, lockable: true, renderer: fxRendererDia});

							}else{
								
								if(campos_cortados[key].substring(0, 2)=="id"){
									//Si los campos empiezan con idXX se tratancomo ocultos
									campos_cortados_colcheck.push({text: campos_cortados[key] , dataIndex:campos_cortados[key], 
									sortable : false, lockable: false, hidden:true,
									width: 50});
								}else{
									//caso contrario son visibles
									campos_cortados_colcheck.push({text: campos_cortados[key], dataIndex:campos_cortados[key], 
										sortable : false, lockable: false,
										width: iwidth, renderer: fxRendererDia, align: 'center',
										summaryType: function(records, values) {
											//values son los valores de la column
											var i = 0,
												length = values.length,
												total = 0,
												record;
											for (; i < length; ++i) {
												valor = values[i];
												if(valor == 0 || valor == 2 ){ //solo se quiere contar los ceros y 2 (corregido)
													total+=1;
												}
											}
											return total;
										},
										summaryRenderer: function(value, summaryData, dataIndex) {
											return ((value >= 1 ) ? '(' + value + ' inc)' : '-');
										},
										field: {
											xtype: 'string'
										}
									});
								}
							}
						}
						const cloneArray = items =>
							items.map(item =>
								Array.isArray(item)
								? cloneArray(item)
								: item
								);
						//const arr_copia = [..._dataPivot.results];
						const arr_copia = cloneArray(_dataPivot.results);
						var dsNuevoMes = Ext.create('Ext.data.Store', {
							groupField:'usuario',
							model: createModelWithCustomProxy(campos_cortados),
							data: arr_copia
						});
						gridCheckListPivot.reconfigure(dsNuevoMes, campos_cortados_colcheck);
						//gridCheckListPivot.getView().refresh();
					}else{
						gridCheckListPivot.getStore().removeAll();
					}
				},
				failure: function(error) {
					alert('Error en la conexion.');
				},  
				timeout: 30000  
			});	
		}else{
			Ext.Msg.alert('Parametro Invalido', 'Local, Año y Mes por favor');
		}
		
	}
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
		XLSX.utils.sheet_add_aoa(worksheet, [["usuario", "item_nombre"]], { origin: "A1" });

        /* calculate column width */
        const max_width = rows.reduce((w, r) => Math.max(w, r.usuario.length), 10);
        worksheet["!cols"] = [ { wch: max_width } ];

        /* create an XLSX file and try to save to Presidents.xlsx */
        XLSX.writeFile(workbook, _nombre_archivo + ".xlsx", { compression: true });
	}
	////////////////////////////////////////////////////////////////////////
	//FORMULARIO
	////////////////////////////////////////////////////////////////////////
	var fieldBotonera = Ext.create('Ext.form.FieldSet',{
		layout : "column",
		width: 140,
		border: false, 
		items: [{
			layout: 'vbox',
			border:false,
			columnWidth: 0.5,
			margin: '0 0 0 0',
			//baseCls:'x-plain',
			items:[btnCargar],
			
		},{
			layout: 'vbox',
			border:false,
			columnWidth: 0.5,
			//baseCls:'x-plain',
			margin: '0 0 0 0',
			items:[btnExcel]
		}
]
		//items:[btnCargar, btnExcel]	
	});
	var fieldInformacionArchivo = Ext.create('Ext.form.FieldSet',{
        //title: 'Basic Template',
		frame:true,
        width: 380,
		//layout : "form",
        //height:200,
        //html: '<hr><p><i>Informacion de archivo.. </i></p>'
		items:[cmbLocales, cmbAnio, cmbMes, fieldBotonera],
		//buttons:[btnCargar, btnExcel]	
        //items: tplDetalleArchivo
    });
	/*
	var frmCheckList = new Ext.FormPanel({ 
		name : 'frm_check_list',
		id: 'frm_check_list',
		frame: true,
		//labelAlign: 'left',
		title: 'Formulario CheckList',
		border:false,
		//bodyStyle:'padding:5px',
		//bodyStyle:'padding:5px 5px 0',
		layout : "form",
        renderTo: 'div_general',		
		//width: 350,
		//height:550,
		buttonAlign:'left',
		items : [cmbAnio,  gridCheckListPivot],
		buttons:[ btnNuevo]	
	});
    */
	
	var fieldCuerpo= new Ext.form.FormPanel({
		border: true,
		width:1400,
		height:800,
		title: 'MODULO DE CAJA CHECKLIST - REPORTE',
		//collapsible: true,
		//layout: "form",
		renderTo: 'div_general',		
		items:[fieldInformacionArchivo,  gridCheckListPivot]
	
	});
	
});