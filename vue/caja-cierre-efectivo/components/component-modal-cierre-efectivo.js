

Vue.component("component-modal-inflaciones", {
    template:`
 
    <div id="modal_caja_cierre_efectivo" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="cerrar_modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Detalle Efectivo</h4>
                </div>
                <div class="modal-body">
                    
                



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
        
    },
    mounted(){
        
    },
    watch:{
        
    },
    methods: {
        obtener_tipo_periodicidad,
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
    $('#modal_caja_cierre_efectivo').modal('hide');
    $('.modal').css('overflow', 'auto');
}
