/**
 * @author Edwin Huarhua Chambi
 * @description Cliente Universal
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
	
	//DESARROLLO
	/*
	let _controlador = "../sec_clienteuniversal.php";
    let _controlador_laravel= "http://localhost:8090/api";
    let _autorization = "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI5OWYxYTU2Ni1kZDU5LTQxZDMtODkwOC02Y2E5YzY5MzM4ZjgiLCJqdGkiOiIxODA4ZDkwYTI5OTUzN2U4NTA5ZmY1NzNmYzlmYmE4N2NmMzUzNDE0YjllNjlmYzZhNTk2ZTU2ZDFiNDlkYWMwMmFmMTVmMzQ2ODQ5ZGE5ZSIsImlhdCI6MTY5MjYzMTQyNi4yNjI1MDUsIm5iZiI6MTY5MjYzMTQyNi4yNjI1MTIsImV4cCI6MTc4NzMyNTgyNi4yNDg3OTcsInN1YiI6IjEiLCJzY29wZXMiOltdfQ.YnfeXa9YDrXc7KVt6e2oG7gGM2a0_5houhk1zA0Fks6joDM-08S6bLUVpyRx3mMbL9tfUYoW59-fNRZKSBACtOlhXl5mNwuMOqUs4vSDb75Edv9V_yieRUQu8YoVjMn2wB1iw8_HPaTY0RlwMwgCFaeLA4GzWIcemf4PpmEhEA3uV8uDghGHLgVlvcelkq9TwWbipRk4xLNMcQosX5hHhvsyrQxSAU0cKQ0BOnnsL2pbyp3QjyFtWb9IMP0ctjzFEJ9iE6qAESri642gSuYFwPBHgVWWkYQsd-RRoFLx_atA774bTiPUQESWp3m2wahOgdiIAy-6tLw-JNdCy3KwDOEx9jffYxPle8yhihJr9jnkZOV2TT45ZAZB2Ospw2W3dqKFY4cs4p5xL94PiOxQg-1JSdk81B2omMCmR-V9J1HWOW0Z582K05AicxyOlfwOMxqMR5yrD7JXxESG46a8BQYWujIgr5sppsVHZWGfh91Ctu8m-VbxnYopcqh2Fj39oH1RaucUMz_fLWV41HTIhWY8DjMn4ix3kAsELRrr-UnXuuzvfSh4U79HaNkk-h466qYQvlnmJCMtdVOkYzlMFLlbG_tahOpxYNgjJBOTFKcH36finNZ2zBywQks3tqQmkk2-T8ask170OF41dy0ITlnm8fQlLUZ6aJ4b3Rl3GGM";
	*/
	//PRODUCCION
	let _controlador = "../sec_clienteuniversal.php";
    let _controlador_laravel= "https://idclientes.apuestatotal.com/api";
    let _autorization = "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI5OWRlMjM3ZC00NGU4LTRhMmItYjc5YS0wNDZjMDYzMDMwNGEiLCJqdGkiOiJhN2IwYzk5NGNjNmZjYmUyYzg0NDFhZDc1MTdkZjM0NzEzYTg0NDVmZGI5YjEyNWIzZDRiZTBlN2MwOWI2MWUzNzhkOGVhYWIyNjUxN2NiYyIsImlhdCI6MTY5MjgxODA5OS43OTMwNzMsIm5iZiI6MTY5MjgxODA5OS43OTMwNzYsImV4cCI6MTc4NzUxMjQ5OS43NzAyNDksInN1YiI6IjE1Iiwic2NvcGVzIjpbXX0.MlFfnPb8J7U-ZaQ-u2e6pdaALwFTqY0Czr7NNECCmfoceR60M_MeL5hZgwBBvlvQaGSdZyDGkGtpOSav320MBdGd2wRTaCfNsO85dX8o4TgSmq0uyzwoRODhjU5AoW4Jjjy2djBbW4YdU7jn8b5bNkjor3xYB-kF42733kq7nLwIwRF0w977v9yjzGljHYgq43MZW49yHm6CKXEp-88Z7Ri0a1jHEkfZXrQMwZ836g8y4qNyhiFeOi6jzjlcaK7GfcYMIkVxIIwr4ipQVOVVBs7NmJ90jfNe811MA24uJvuMG0KKj4b7HhWfoPe7XZIt8-c2-ohVYIWUZxx_XoSkoCTM3PqxORgn7DlMQYN7fro6UwouS9fNEPO9dWrIrMNPFkjll1qHel9IzicV0JTMBmawwkkGfSdTdyjP46OPaWhUaQyYCMUqZkmTAW-EqNKRIUJp-_EKcyhWZR7m5Q0xqdnpJmpq1ee01GxJwH1gXOiJnYdTVEDQaSfkNYW_Hu09-fliJT7kFo0Ya3uvg_t6cgwT54qwozG2b3Xi6QxzQmDrPVoBaa5Dtr8YSMP8nGQu2zLMJmc8QDX4avpjr5fMGT4gqqmHx4OPHHeOHObdBzD6hnTNPPQuUNLamIp-fyPDzPUCzeZlPrrpeHKOmiQ7S56cjaBlIYJp63I92Ob1yYg";
	
    Ext.Ajax.cors = true;
	var hidSession= new Ext.form.Hidden({
		name:'hid_session',
		id:'hid_session',
		value:0
	});
	///////////////////////////////////////////////////////////////////////////
	//Grid Cliente Universal
	///////////////////////////////////////////////////////////////////////////
	var dsClienteUniversal= Ext.create('Ext.data.Store', {
	    buffered: false,
	    model: 'modelClienteUniversal',
		autoLoad: true,
        pageSize: 1000,
	    proxy: {
            headers: {
                "Authorization": _autorization,
                "Access-Control-Allow-Origin": "*"
            },
            type: 'rest',
            cors: true,
            useDefaultXhrHeader: false,
            withCredentials: false,
            crossDomain: true,
			url: _controlador_laravel+'/get_client',
			method: 'POST',
			params:{
				start:0,    
				//pageSize: 10,
				limit: 50
			},
			reader:{
				type: 'json',
                totalProperty:  'totalCount',
				root: 'results'
			}
        }
	});
	dsClienteUniversal.load();
	
    function fxRendererDNI(value,metaData, record, rowIndex, colIndex, store){
		//return value.substring(11);
	    //if (record.get ( 'usu_vcodigo' )=="sin asignar") {
		value='<div style="background-color: #66ffb3"  >&nbsp;' +value+ '</div>';
		//}
		return value;
	}
    function fxRendererVerificado(value,metaData, record, rowIndex, colIndex, store){
        if(record.get('uni_verificado')=="0") return value='<div style="background-color: #ffb3b3"  >&nbsp;' +value+ '</div>';
		return value;
	}
	var colClienteUniversal= ([
		{text: 'Id', dataIndex:'uni_id', width: 80 }, 
        {text: 'hash_id', dataIndex:'uni_id_hash', width:100},
        {text: 'tip_id', dataIndex:'tip_id', width:60},
        {text: 'Tip Documento C', dataIndex:'uni_tipo_documento', width:130},
        {text: 'Tip Documento', dataIndex:'uni_tipo_documento2', width:120, renderer:fxRendererDNI},
        {text: 'Estado Televentas', dataIndex:'tip_estado_televentas', width:130},
        {text: 'Nro Documento C', dataIndex:'uni_numero_documento', width:130},
        {text: 'Nro Documento', dataIndex:'uni_numero_documento2', width:130, renderer:fxRendererDNI, align:'right'},
        {text: 'Telefono', dataIndex:'uni_telefono', width:120},
		{text: 'Nombre', dataIndex:'uni_nombres', width: 180 },
		{text: 'Paterno', dataIndex:'uni_apellido_paterno', width: 180 , align:'left', filter:{type: 'string'}},
        {text: 'Materno', dataIndex:'uni_apellido_materno', width: 180 , align:'left'},
        {text: 'Email', dataIndex:'uni_email', width: 180},
        {text: 'Fec. Nac', dataIndex:'uni_fecha_nacimiento', width: 100},
		{text: 'Direccion', dataIndex:'uni_direccion', width: 180},
        {text: 'Genero', dataIndex:'uni_genero', width: 80},
        {text: 'Origen', dataIndex:'uni_origen', width: 180},
        {text: 'DJ', dataIndex:'uni_declaracion_jurada', width: 40},
        {text: 'Terminos Condiciones', dataIndex:'uni_terminos_condiciones', width: 100, tooltip:'Terminos Condiciones'},
        {text: 'Politicamente Expuesto', dataIndex:'uni_politicamente_expuesto', width: 100, tooltip:'Politicamente Expuesto'},
        {text: 'Verificado', dataIndex:'uni_verificado', width: 100, renderer:fxRendererVerificado},
        {text: 'Etiqueta id', dataIndex:'uni_etiqueta_id', width: 80},
        {text: 'Etiqueta', dataIndex:'uni_etiqueta_nombre', width: 240},
        {text: 'Usuario Creacion', dataIndex:'usuario_creacion', width: 140},
		{text: 'Usuario Modificacion', dataIndex:'usuario_actualizacion', width: 150},
        {text: 'Fecha Creacion', dataIndex:'fecha_creacion', width: 150},
		{text: 'Fecha Modificacion', dataIndex:'fecha_actualizacion', width: 150}
    ]);

    var cmbTipoDocumento= Ext.create('Ext.form.ComboBox',{
		fieldLabel: 'Tipo Doc',
		name:'uni_tipo_documento',
		labelWidth: 80,
		width: 200,
		//inputWidth: 170,
		//labelWidth: 30,
		queryMode: 'local',
		//fieldLabel: 'Mes',  
		//hiddenName: 'sel_mes', // Este campo es importante, sin él no funciona el combo  
		valueField: 'uni_tipo_documento',  
		displayField: 'uni_tipo_documento',  
		typeAhead: true,  
		mode: 'local',  
		triggerAction: 'all',  
		selectOnFocus: true,  
		autoSelect :true,
		forceSelection: true,
		emptyText:'Tipo..',
		editable: false,
		//readOnly:true,
        value: 'DNI',
		store: new Ext.data.SimpleStore({  
			id      : 0 ,  
			fields  : [  'uni_tipo_documento' ],  
			data    : [  
				['DNI'], ['CEX'],['PAS']
			]  
		}),
		listeners:{
			scope: this,
			'select': function(combo, record, index){
				if(record.data['uni_tipo_documento']=='DNI'){
					txtBuscar.setValue("");
				}
				//alert(record);
			}
	    }
	});
    var txtBuscar= Ext.create('Ext.form.field.Text',{
		id:'txt_buscar',
		name:'txt_buscar',
		//fieldLabel:'Nro Doc',
        emptyText:'DNI',
		//labelWidth: 80,
		width:200,
		allowBlank:false,
		enableKeyEvents :true,
		//regex: /^[0-9]{8}$/,
		enforceMaxLength : true,
		//maxLength:8,
		listeners: {
			change: function(e, text, prev) {

				if (!/^[0-9]{0,8}?$/.test(text) && cmbTipoDocumento.getValue()=='DNI') 
				//if (!/^(?:\D*\d){3}\D*$/.test(text) && cmbTipoDocumento.getValue()=='DNI') 
				{   
					this.setValue(prev);
				}
			},
			specialkey: function(f,e){
			  if (e.getKey() == e.ENTER) {
				fxFindClient();	
			  }
			}
		}
	});
    var btnBuscar= new Ext.Button({
		text:'Buscar',
		scope:this,
		//disabled : true,
		handler:function (){
			fxFindClient();
		}
	});

	var gridClienteUniversal = Ext.create('Ext.grid.Panel', {
        //title: 'Cliente Universal',
        width: 1400,
        height: 670,
        border:true,
        scroll: true,
        columnLines: true,
        rowLines:true,
        stripeRows: true,
        loadMask: true,
        //margin: '0 0 10 0',
        store: dsClienteUniversal,
        columns: colClienteUniversal,
        plugins: 'gridfilters',
        bbar: Ext.create('Ext.PagingToolbar', {
			store: dsClienteUniversal,
			displayInfo: true,
			displayMsg: '{0} - {1} of {2}',
			emptyMsg: "No clients to display"
		}),
        dockedItems: [{
            xtype: 'toolbar',
            dock: 'top',
            items: [
                cmbTipoDocumento, txtBuscar, btnBuscar
            ]
        }],
		listeners: {
        	scope: this,
            selectionchange: function(model, records) {
				var rec = records[0];
		        if (rec) {
                    frmEtiqueta.loadRecord(rec);
                    btnEtiqueta.enable();
				}
			}
		}
	});	
	///////////////////////////////////////////////////////////////////////////
    //Ficha Cliente Universal
	///////////////////////////////////////////////////////////////////////////
    var hidHashId= new Ext.form.Hidden({
		name:'uni_id_hash',
		id:'uni_id_hash',
		value:0
	});
    var txtNombre= Ext.create('Ext.form.field.Text',{
		id:'uni_nombres',
		name:'uni_nombres',
		fieldLabel:'Nombres',
		labelWidth: 80,
		width:300,
		allowBlank:false,
		readOnly:true
	});
	var txtApellidoPaterno = Ext.create('Ext.form.field.Text',{
		id:'uni_apellido_paterno',
		name:'uni_apellido_paterno',
		fieldLabel:'Paterno',
		labelWidth: 80,
		width:300,
		allowBlank:false,
		readOnly:true
	});
	var txtApellidoMaterno = Ext.create('Ext.form.field.Text',{
		id:'uni_apellido_materno',
		name:'uni_apellido_materno',
		fieldLabel:'Materno',
		labelWidth: 80,
		width:300,
		allowBlank:false,
		readOnly:true
	});
	var chkVerificado = Ext.create('Ext.form.field.Checkbox',{
		id : 'uni_verificado',
		name : 'uni_verificado',
		labelWidth: 80,
		fieldLabel: 'Verificado'
		//checked:false
	});
    var dsEtiqueta= Ext.create('Ext.data.Store', {
	    buffered: false,
	    pageSize: 50,
	    model: 'modelEtiqueta',
	    proxy: {
            type: 'ajax',
			url: _controlador,
			method: 'POST',
            extraParams: {
				do: 'AJAX_OBTENER_ETIQUETAS_TELEVENTAS'
			},
			reader:{
				type: 'json',
                //headers: { 'Accept': 'application/json' },
				root: 'results'
			}
        }
	});
    dsEtiqueta.load();
    var cmbEtiqueta= new Ext.form.ComboBox({
		fieldLabel: 'Etiqueta',
		name:'uni_etiqueta_id',
		id:'uni_etiqueta_id',
		labelWidth: 80,
		width:350,
        store: dsEtiqueta,
        displayField: 'uni_etiqueta_nombre',
        typeAhead: true,
        mode: 'local',
        queryMode: 'local',
        forceSelection: true,
        triggerAction: 'all',
        emptyText:'Selecciona ..',
		editable: false,
		valueField: 'uni_etiqueta_id',
        selectOnFocus:true,
		allowBlank  :false
    });
	////////////////////////////////////////////////////////
	// BOTONES
	////////////////////////////////////////////////////////
    var btnEtiqueta= new Ext.Button({
		text:'Etiqueta',
		scope:this,
		disabled : true,
		handler:function (){
			winEtiqueta.show(this);
		}
	});

	var btnGuardarEtiqueta = new Ext.Button({
		text:'Guardar',	
		disabled : false,
		handler: function(){
			fxGuardarEtiqueta();
		} 
	});

	////////////////////////////////////////////////////////////////////////
	//FUNCIONALIDADES
	////////////////////////////////////////////////////////////////////////
    var fxFindClient = function(){
		if(txtBuscar.getValue()==""){
			dsClienteUniversal.reload({
				params:{
					do:''
				}
			});
		}else{
			dsClienteUniversal.reload({
				scope:this,
				params:{
					do: 'GET_BY_TIPDOC',
					numero_documento : txtBuscar.getValue(),
					tipo_documento : cmbTipoDocumento.getValue()
				},
				callback: function(records, operation, success) {
					if(records.length==0){
						Ext.Msg.alert('Alerta', 'Documento no encontrado.');
					}
				}
			});
		}
    }
    var fxGuardarEtiqueta = function(){
        Ext.MessageBox.show({
            title: 'Confirmacion',
            msg: '¿Esta seguro que desea asignar etiqueta?',
            buttons: Ext.MessageBox.OKCANCEL,
            icon: Ext.MessageBox.WARNING,
            fn: function(btn){
                if(btn == 'ok'){
                    Ext.Ajax.request({  
                        headers: {
                            "Authorization": _autorization,
                            "Access-Control-Allow-Origin": "*"
                        },
                        type: 'ajax',
                        cors: true,
                        useDefaultXhrHeader: false,
                        withCredentials: false,
                        crossDomain: true,
                        url: _controlador_laravel+'/register_label',  
                        method: 'POST',
                        params: {  					
                            hash_id : hidHashId.getValue(),
                            verificado: chkVerificado.getValue(),
                            etiqueta_id: cmbEtiqueta.getValue(),
                            etiqueta_nombre: cmbEtiqueta.getRawValue(),
							usuario : hidSession.getValue()
                        },
                        success:  function(response){
                            var jsonData = Ext.JSON.decode(response.responseText);
                            Ext.Msg.alert('Asignación Exitosa', jsonData.result);
							dsClienteUniversal.reload();
							btnEtiqueta.disable();
                            frmEtiqueta.reset();
                            winEtiqueta.close();
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
	Ext.Ajax.request({  
		type: 'ajax',
		url: _controlador,  
		method: 'POST',
		params:{
			do: 'SESSION'
		},
		success:  function(response){
			var jsonData = Ext.JSON.decode(response.responseText);
			hidSession.setValue(jsonData.usuario);
			//alert(jsonData.usuario);
		},
		failure: function(error) {
			alert('Error en la session.');
		},  
		timeout: 30000  
	});	
	////////////////////////////////////////////////////////////////////////
	//FORMULARIO ASIGNACION ETIQUETA
	////////////////////////////////////////////////////////////////////////
    var frmEtiqueta= Ext.create('Ext.form.Panel', {
    	//title:'Asignacion Etiqueta',
        layout: 'form',
        border: true,
        frame:false, 
        autoScroll: true,
		width: 500,
		height: 200,
        border: true,
		items:[hidHashId, txtNombre, txtApellidoPaterno, txtApellidoMaterno, chkVerificado, cmbEtiqueta],
        buttons:[btnGuardarEtiqueta]
    });
    var winEtiqueta= new Ext.create('Ext.window.Window',{
		//renderTo: document.body,
		title: 'Asignacion Etiqueta',
		applyTo:'div_cliente_universal',
		width: 510,
		height: 250,
		maximizable: false,
		modal: true,
		autoScroll:true,
		closeAction: 'hide',
		items:[frmEtiqueta],
		resizable: false,
		//minWidth: 500,
		//minHeight: 350,
		maximized: false,
		constrain: true
		//renderTo: 'div_caja'
	})

	var frmClienteUniversal = new Ext.FormPanel({ 
		name : 'frm_cliente_universal',
		id: 'frm_cliente_universal',
		frame: true,
		//labelAlign: 'left',
		title: 'Administracion Cliente Universal ',
		border:false,
		//bodyStyle:'padding:5px',
		//bodyStyle:'padding:5px 5px 0',
		//layout : "form",
		//columns : [],
		//layoutConfig : {columns:3},
		width: 1400,
		height:700,
		renderTo: 'div_general',		
		buttonAlign:'left',
		/*
		defaults: {
    		margin: '0 0 0 0' //top right bottom left (clockwise) margins of each item/column
  		},
  		*/
		items : [gridClienteUniversal]		
	});
});