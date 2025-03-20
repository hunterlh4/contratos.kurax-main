/**
 * @author Edwin Huarhua
 * @description archivo donde se definen los modelos para uso en frontend
 */
Ext.define('modelCuenta', {
    extend: 'Ext.data.Model',
    fields: [
    	{name: 'cta_id', type:'int'}, {name: 'pla_id', type:'int'}, {name: 'cta_cargo', type:'float'},
    	{name: 'cta_abono', type:'float'},{name: 'cta_origen' }, {name: 'cta_documento'},
    	{name: 'cta_observacion'}, {name: 'cta_fecha_hora'}
	]
});
Ext.define('modelUsuario', {
	extend: 'Ext.data.Model',
	fields: [
		{name: 'usuario_id', type:'int'},{name: 'nombre_completo'}
	]
});
Ext.define('modelGrupoCorreoUsuario', {
    extend: 'Ext.data.Model',
    fields: [
    	{name: 'gcu_id', type:'int'}, {name: 'usuario'}, {name: 'nombre_completo'}, {name: 'id_grupo_correo'}, {name: 'id_usuario'}, {name: 'tipo_receptor'},
		{name: 'gcu_estado'}, {name: 'correo'}
	]
});
Ext.define('modelGrupoCorreo', {
    extend: 'Ext.data.Model',
    fields: [
    	{name: 'id', type:'int'}, {name: 'nombre'}, {name: 'descripcion'}, {name: 'estado'}
	]
});
Ext.define('modelCheckList', {
    extend: 'Ext.data.Model',
    fields: [
    	{name: 'id', type:'int'}, {name: 'item_numero', type:'int'}, {name: 'item_nombre'}, {name: 'item_descripcion'}, {name: 'indicador'},
		{name: 'estado', type:'int'}, {name: 'chk_respuesta', type:'bool'},{name:'grupo_correo_id', type:'int'}, {name:'grupo_correo_nombre'}
	]
});

Ext.define('modelLocales', {
    extend: 'Ext.data.Model',
    fields: [
    	{name: 'id', type:'int'}, {name: 'canal_id', type:'int'}, {name: 'red_id', type:'int'}, {name: 'zona_id', type:'int'}, 
		{name: 'nombre'}, {name: 'direccion'}, {name: 'estado'}
	]
});
//--------------------------------------------------------------------------------------------------------//
//USUARIOSLOGS  : tbl_auditoria
Ext.define('modelAuditoria', {
    extend: 'Ext.data.Model',
    fields: [
    	{name: 'id', type:'int'}, 
		{name: 'fecha_registro'},
		{name: 'usuario_id'},
		{name: 'proceso'},
		{name: 'ip'},
		{name: 'sec_id'},
		{name: 'sub_sec_id'},
		{name: 'item_id'},
		{name: 'data'},
		{name: 'url'},
		{name: 'sistema'},
		{name: 'login'},
		{name: 'geolocation'},
		{name: 'estado'}
	]
});
//--------------------------------------------------------------------------------------------------------//
// AGENTES - LOCALES
//--------------------------------------------------------------------------------------------------------//
Ext.define('modelLocalesAgente', {
    extend: 'Ext.data.Model',
    fields: [
    	{name: 'zona_id', type:'int'}, 
		{name: 'zona_nombre'}, {name: 'zona_razon_social'},
		{name: 'local_id', type:'int'}, {name: 'local_cc_id', type:'int'}, {name: 'local_nombre'},
		{name: 'operativo'}, {name: 'fecha_inicio_operacion'}, {name: 'fecha_fin_operacion'}
	]
});
//Modelos para Cliente Universal
Ext.define('modelClienteUniversal', {
    extend: 'Ext.data.Model',
    fields: [
    	{name:'uni_id', type:'int'}, {name: 'uni_id_hash'}, {name: 'tip_id'}, {name: 'uni_tipo_documento'}, {name: 'uni_tipo_documento2'}, 
		{name:'tip_estado_televentas'}, {name:'uni_numero_documento'}, {name:'uni_numero_documento2'}, 
		{name:'uni_telefono'},
		{name:'uni_email'}, {name:'uni_nombres'}, {name:'uni_apellido_paterno'}, {name:'uni_apellido_materno'}, {name:'uni_nacionalidad'},
		{name:'uni_fecha_nacimiento'}, {name:'uni_direccion'}, {name:'uni_genero'}, {name:'uni_origen'}, {name:'uni_declaracion_jurada'}, 
		{name:'uni_terminos_condiciones'}, {name:'uni_politicamente_expuesto'}, 
		{name:'uni_etiqueta_id'},{name:'uni_etiqueta_nombre'},
		{name:'uni_estado'}, {name:'usuario_creacion'}, {name:'usuario_actualizacion'}, 
		{name:'fecha_creacion'}, {name:'fecha_actualizacion'}
	]
});

Ext.define('modelEtiqueta', {
    extend: 'Ext.data.Model',
    fields: [
    	{name: 'uni_etiqueta_id', type:'int'}, {name: 'uni_etiqueta_nombre'}
	]
});