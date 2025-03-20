

Vue.component("component-modal-inflaciones", {
    template:`
 
    <div :id="'component_modal_inflaciones_'+ index" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="cerrar_modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ title }} - Ficha #{{index +1}}</h4>
                </div>
                <div class="modal-body">
                    <div class="row" :id="'modal_body_inflaciones_'+ index" tabindex="0">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo Valor</label>
                                <v-select ref="tipo_periodicidad_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_periodicidad" :filterable="true" label="text"  v-model='tipo_periodicidad_val'></v-select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-6 col-lg-6" v-if="inflacion.tipo_periodicidad_id == 1">
							<div class="form-group">
								<div class="control-label">Periodicidad del ajuste (Ejemplo: 1 año, 6 meses.):  <span class="campo_obligatorio_v2">(*)</span>:</div>
								<div class="">
									<div class="col-md-6" style="padding:0px;">
										<input ref="numero" type="number" v-model="inflacion.numero" class="form-control">
									</div>
									<div class="col-md-6" style="padding:0px;">
										<v-select ref="tipo_anio_mes_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_anio_mes" :filterable="true" label="text"  v-model='tipo_anio_mes_val'></v-select>
								    </div>
							    </div>
                            </div>
						</div>
                        <div class="col-md-3" >
                            <div class="form-group">
                                <label>Porcentaje Añadido:</label>
                                <input ref="porcentaje_anadido" v-model="inflacion.porcentaje_anadido" class="form-control text-right">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tope Inflación:</label>
                                <input ref="tope_inflacion" v-model="inflacion.tope_inflacion" class="form-control text-right">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Minimo Inflación:</label>
                                <input ref="minimo_inflacion" v-model="inflacion.minimo_inflacion" class="form-control text-right">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Aplicación</label>
                                <v-select ref="tipo_aplicacion_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_aplicacion" :filterable="true" label="text"  v-model='tipo_aplicacion_val'></v-select>
                            </div>
                        </div>
                        
                    </div>
                

                    <div class="row" v-if="action == 'nuevo'">
                        <div class="col-md-12"> <br> </div>

                        <div class="col-xs-12 col-md-4 col-lg-4">
                            <div class="form-group">
                                <div class="form-group">
                                    <button type="button" @click="guardar_inflacion(false)" class="btn btn-success btn-xs btn-block">
                                        <i class="icon fa fa-plus"></i>
                                        <span>Agregar Inflación</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-8 col-lg-8">
                            <div class="form-group">
                                <div class="form-group">
                                    <button type="button" @click="guardar_inflacion(true)" class="btn btn-success btn-xs btn-block">
                                        <i class="icon fa fa-plus"></i>
                                        <span>Agregar el inflación y seguir agregando otro inflación</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row" v-if="action == 'modificar'">
                        <div class="col-md-12"> <br> </div>

                        <div class="col-md-4"></div>
                        <div class="col-xs-12 col-md-4 col-lg-4">
                            <div class="form-group">
                                <div class="form-group">
                                    <button type="button" @click="guardar_inflacion(false)" class="btn btn-warning btn-xs btn-block">
                                        <i class="icon fa fa-pencil"></i>
                                        <span>Modificar la inflación</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" @click="cerrar_modal">Cerrar Ventana</button>
                </div>
            </div>
        </div>
    </div>
    `,
    props:["inflaciones","index"],
    components: {
		'v-select': VueSelect.VueSelect
	},
    data(){
        return {

            title:'',
            action:'',
            inflacion:{
                id:'',
                contrato_id:'',
                fecha:'',
                tipo_periodicidad_id:'',
                numero:'',
                tipo_anio_mes_id:'',
                moneda_id:'',
                porcentaje_anadido:'',
                tope_inflacion:'',
                minimo_inflacion:'',
                tipo_aplicacion_id:'',
           
                tipo_periodicidad:'',
                tipo_anio_mes:'',
                tipo_aplicacion:'',
            },

            tipo_periodicidad: [],
            tipo_anio_mes: [],
            tipo_aplicacion: [],
    
            tipo_periodicidad_val: null,
            tipo_anio_mes_val: null,
            tipo_aplicacion_val: null,
        }
    },
    created(){
        this.obtener_tipo_periodicidad();
        this.obtener_anio_mes();
        this.obtener_tipo_aplicacion();
    },
    mounted(){
        EventBus.$on('nueva_inflacion', (data) => {

            this.title = data.title;
            this.action = data.action;
            this.resetear_form();

            let me = this;
            setTimeout(function () {
                $(me.$refs.porcentaje_anadido).on({
                    focus: function (event) {
                        $(event.target).select();
                    },
                    blur: function (event) {
                        if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                            $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
                            $(event.target).val(function (index, value) {
                                var new_value = value
                                    .replace(/\D/g, "")
                                    .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                                me.inflacion.porcentaje_anadido = new_value;
                                return new_value;
                            });
                        } else {
                            me.inflacion.porcentaje_anadido = "0.00";
                            $(event.target).val("0.00");
                        }
                    },
                });
                $(me.$refs.tope_inflacion).on({
                    focus: function (event) {
                        $(event.target).select();
                    },
                    blur: function (event) {
                        if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                            $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
                            $(event.target).val(function (index, value) {
                                var new_value = value
                                    .replace(/\D/g, "")
                                    .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                                me.inflacion.tope_inflacion = new_value;
                                return new_value;
                            });
                        } else {
                            me.inflacion.tope_inflacion = "0.00";
                            $(event.target).val("0.00");
                        }
                    },
                });
                $(me.$refs.minimo_inflacion).on({
                    focus: function (event) {
                        $(event.target).select();
                    },
                    blur: function (event) {
                        if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                            $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
                            $(event.target).val(function (index, value) {
                                var new_value = value
                                    .replace(/\D/g, "")
                                    .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                                me.inflacion.minimo_inflacion = new_value;
                                return new_value;
                            });
                        } else {
                            me.inflacion.minimo_inflacion = "0.00";
                            $(event.target).val("0.00");
                        }
                    },
                });
            }, 1000);
           
        });

        EventBus.$on('editar_inflacion', (data) => {

            this.title = data.title;
            this.action = data.action;
            let index = data.index;

            let id = index;
            let contrato_id = this.inflaciones[index].contrato_id;
            let fecha = this.inflaciones[index].fecha;
            let tipo_periodicidad_id = this.inflaciones[index].tipo_periodicidad_id;
            let numero = this.inflaciones[index].numero;
            let tipo_anio_mes_id = this.inflaciones[index].tipo_anio_mes_id;
            let moneda_id = this.inflaciones[index].moneda_id;
            let porcentaje_anadido = this.inflaciones[index].porcentaje_anadido;
            let tope_inflacion = this.inflaciones[index].tope_inflacion;
            let minimo_inflacion = this.inflaciones[index].minimo_inflacion;
            let tipo_aplicacion_id = this.inflaciones[index].tipo_aplicacion_id;
            let tipo_periodicidad = this.inflaciones[index]. tipo_periodicidad;
            let tipo_anio_mes = this.inflaciones[index]. tipo_anio_mes;
            let tipo_aplicacion = this.inflaciones[index]. tipo_aplicacion;

            this.tipo_periodicidad_val = { id: tipo_periodicidad_id, text:tipo_periodicidad };
            this.tipo_anio_mes_val = { id: tipo_anio_mes_id, text:tipo_anio_mes };
            this.tipo_aplicacion_val = { id: tipo_aplicacion_id , text:tipo_aplicacion };

            this.inflacion.id = id;
            this.inflacion.contrato_id = contrato_id;
            this.inflacion.fecha = fecha;
            this.inflacion.tipo_periodicidad_id = tipo_periodicidad_id;
            this.inflacion.numero = numero;
            this.inflacion.tipo_anio_mes_id = tipo_anio_mes_id;
            this.inflacion.moneda_id = moneda_id;
            this.inflacion.porcentaje_anadido = porcentaje_anadido;
            this.inflacion.tope_inflacion = tope_inflacion;
            this.inflacion.minimo_inflacion = minimo_inflacion;
            this.inflacion.tipo_aplicacion_id = tipo_aplicacion_id;
            this.inflacion.tipo_periodicidad = tipo_periodicidad;
            this.inflacion.tipo_anio_mes = tipo_anio_mes;
            this.inflacion.tipo_aplicacion = tipo_aplicacion;

            
            let me = this;
            setTimeout(function () {
     
                $(me.$refs.porcentaje_anadido).on({
                    focus: function (event) {
                        $(event.target).select();
                    },
                    blur: function (event) {
                        if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                            $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
                            $(event.target).val(function (index, value) {
                                var new_value = value
                                    .replace(/\D/g, "")
                                    .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                                me.inflacion.porcentaje_anadido = new_value;
                                return new_value;
                            });
                        } else {
                            me.inflacion.porcentaje_anadido = "0.00";
                            $(event.target).val("0.00");
                        }
                    },
                });
                $(me.$refs.tope_inflacion).on({
                    focus: function (event) {
                        $(event.target).select();
                    },
                    blur: function (event) {
                        if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                            $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
                            $(event.target).val(function (index, value) {
                                var new_value = value
                                    .replace(/\D/g, "")
                                    .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                                me.inflacion.tope_inflacion = new_value;
                                return new_value;
                            });
                        } else {
                            me.inflacion.tope_inflacion = "0.00";
                            $(event.target).val("0.00");
                        }
                    },
                });
                $(me.$refs.minimo_inflacion).on({
                    focus: function (event) {
                        $(event.target).select();
                    },
                    blur: function (event) {
                        if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                            $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
                            $(event.target).val(function (index, value) {
                                var new_value = value
                                    .replace(/\D/g, "")
                                    .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                                me.inflacion.minimo_inflacion = new_value;
                                return new_value;
                            });
                        } else {
                            me.inflacion.minimo_inflacion = "0.00";
                            $(event.target).val("0.00");
                        }
                    },
                });


            }, 1000);
        });
    },
    watch:{
        tipo_periodicidad_val(newValue){
            if (newValue == null) {
                this.inflacion.tipo_periodicidad_id = '';
                this.inflacion.tipo_periodicidad = '';

                this.inflacion.numero = '';
                this.inflacion.tipo_anio_mes_id = '';
                this.inflacion.tipo_anio_mes = '';
                this.tipo_anio_mes_val = null;
                return  false;
            }
            this.inflacion.tipo_periodicidad_id = newValue.id;
            this.inflacion.tipo_periodicidad = newValue.text;
            if (newValue.id == 2) {
                this.inflacion.numero = '';
                this.inflacion.tipo_anio_mes_id = '';
                this.inflacion.tipo_anio_mes = '';
                this.tipo_anio_mes_val = null;
            }
        },
        tipo_anio_mes_val(newValue){
            if (newValue == null) {
                this.inflacion.tipo_anio_mes_id = '';
                this.inflacion.tipo_anio_mes = '';
                return  false;
            }
            this.inflacion.tipo_anio_mes_id = newValue.id;
            this.inflacion.tipo_anio_mes = newValue.text;
        },
        tipo_aplicacion_val(newValue){
            if (newValue == null) {
                this.inflacion.tipo_aplicacion_id = '';
                this.inflacion.tipo_aplicacion = '';
                return  false;
            }
            this.inflacion.tipo_aplicacion_id = newValue.id;
            this.inflacion.tipo_aplicacion = newValue.text;
        },
    },
    methods: {
        obtener_tipo_periodicidad,
        obtener_anio_mes,
        obtener_tipo_aplicacion,
        guardar_inflacion,
        resetear_form,
        cerrar_modal,
    },
})


function obtener_tipo_periodicidad() {
    let me = this;
	let url = 'sys/router/contratos/index.php';
    let data = {
        action: 'obtener_tipo_periodicidad',
    }
    this.tipo_periodicidad = [];
	axios({
		method: 'post',
		url: url,
        data: data,
	})
	.then(function (response) {
		if (response.data.status == 200) {
			response.data.result.forEach(element => {
				me.tipo_periodicidad.push({
					id: element.id, text: element.nombre 
				});
			});
		}
	});
}


function obtener_anio_mes() {
    let me = this;
	let url = 'sys/router/contratos/index.php';
    this.tipo_anio_mes = [];
    let data = {
        action: 'obtener_tipo_anio_mes',
    }
	axios({
		method: 'post',
		url: url,
        data: data,
	})
	.then(function (response) {
		if (response.data.status == 200) {
			response.data.result.forEach(element => {
				me.tipo_anio_mes.push({
					id: element.id, text: element.nombre 
				});
			});
		}
	});
}

function obtener_tipo_aplicacion() {
    let me = this;
	let url = 'sys/router/contratos/index.php';
    this.tipo_aplicacion = [];
    let data = {
        action : 'obtener_tipo_aplicacion',
    }
	axios({
		method: 'post',
		url: url,
        data: data,
	})
	.then(function (response) {
		if (response.data.status == 200) {
			response.data.result.forEach(element => {
				me.tipo_aplicacion.push({
					id: element.id, text: element.nombre 
				});
			});
		}
	});
}

function guardar_inflacion(continuar_registro) {

    let me = this;
    let id = this.inflacion.id;
    let contrato_id = this.inflacion.contrato_id;
    let fecha = this.inflacion.fecha;
    let tipo_periodicidad_id = this.inflacion.tipo_periodicidad_id;
    let numero = this.inflacion.numero;
    let tipo_anio_mes_id = this.inflacion.tipo_anio_mes_id;
    let moneda_id = this.inflacion.moneda_id;
    let porcentaje_anadido = this.inflacion.porcentaje_anadido;
    let tope_inflacion = this.inflacion.tope_inflacion;
    let minimo_inflacion = this.inflacion.minimo_inflacion;
    let tipo_aplicacion_id = this.inflacion.tipo_aplicacion_id;


    let tipo_periodicidad = this.inflacion.tipo_periodicidad;
    let tipo_anio_mes = this.inflacion.tipo_anio_mes;
    let tipo_aplicacion = this.inflacion.tipo_aplicacion;


    if (tipo_periodicidad_id.length == 0) {
		alertify.error('Seleccione un tipo de valor',5);
        $(me.$refs.tipo_periodicidad_id).focus();
		return false;
	}

    if (tipo_periodicidad_id == 1) {
        if (numero.length == 0) {
			alertify.error('Ingrese un numero',5);
            $(me.$refs.numero).focus();
			return false;
		}
		if (tipo_anio_mes_id.length == 0) {
			alertify.error('seleccione una mes/año',5);
			$(me.$refs.tipo_anio_mes_id).focus();
			return false;
		}
	}

	if (tipo_aplicacion_id.length == 0) {
		alertify.error('Seleccione un tipo de aplicación',5);
        $(me.$refs.tipo_aplicacion_id).focus();
		return false;
	}

    if (id.length == 0) {
        let data_inflacion = {
            id:id,
            contrato_id:contrato_id,
            fecha:fecha,
            tipo_periodicidad_id:tipo_periodicidad_id,
            numero:numero,
            tipo_anio_mes_id:tipo_anio_mes_id,
            moneda_id:moneda_id,
            porcentaje_anadido:porcentaje_anadido,
            tope_inflacion:tope_inflacion,
            minimo_inflacion:minimo_inflacion,
            tipo_aplicacion_id:tipo_aplicacion_id,
            tipo_periodicidad : tipo_periodicidad,
            tipo_anio_mes : tipo_anio_mes,
            tipo_aplicacion : tipo_aplicacion,
        };
        this.inflaciones.push(data_inflacion);
        alertify.success("Se ha agregado la nueva inflación", 5);
    }else{
        this.inflaciones[id].contrato_id = contrato_id;
        this.inflaciones[id].fecha = fecha;
        this.inflaciones[id].tipo_periodicidad_id = tipo_periodicidad_id;
        this.inflaciones[id].numero = numero;
        this.inflaciones[id].tipo_anio_mes_id = tipo_anio_mes_id;
        this.inflaciones[id].moneda_id = moneda_id;
        this.inflaciones[id].porcentaje_anadido = porcentaje_anadido;
        this.inflaciones[id].tope_inflacion = tope_inflacion;
        this.inflaciones[id].minimo_inflacion = minimo_inflacion;
        this.inflaciones[id].tipo_aplicacion_id = tipo_aplicacion_id;
        this.inflaciones[id].tipo_periodicidad = tipo_periodicidad;
        this.inflaciones[id].tipo_anio_mes = tipo_anio_mes;
        this.inflaciones[id].tipo_aplicacion = tipo_aplicacion;
        alertify.success("Se ha modificado la inflación", 5);
        var id_modal = 'component_modal_inflaciones_' + this.index;
        $('#'+id_modal).modal('hide');
        $('.modal').css('overflow', 'auto');
    }
    if (continuar_registro) {
        this.resetear_form();
    }else{
        var id_modal = 'component_modal_inflaciones_' + this.index;
        $('#'+id_modal).modal('hide');
        $('.modal').css('overflow', 'auto');
        $('#table-inflaciones-'+this.index).focus();
    }


}

function resetear_form() {
    this.inflacion.id = '';
    this.inflacion.contrato_id = '';
    this.inflacion.fecha = '';
    this.inflacion.tipo_periodicidad_id = '';
    this.inflacion.numero = '';
    this.inflacion.tipo_anio_mes_id = '';
    this.inflacion.moneda_id = '';
    this.inflacion.porcentaje_anadido = '';
    this.inflacion.tope_inflacion = '';
    this.inflacion.minimo_inflacion = '';
    this.inflacion.tipo_aplicacion_id = '';

    this.inflacion.tipo_periodicidad = '';
    this.inflacion.tipo_anio_mes = '';
    this.inflacion.tipo_aplicacion = '';

    this.tipo_periodicidad_val =  null;
    this.tipo_anio_mes_val =  null;
    this.tipo_aplicacion_val =  null;
}

function cerrar_modal() {
    $('#component_modal_inflaciones_'+this.index).modal('hide');
    $('.modal').css('overflow', 'auto');
    $('#table-inflaciones-'+this.index).focus();
}
