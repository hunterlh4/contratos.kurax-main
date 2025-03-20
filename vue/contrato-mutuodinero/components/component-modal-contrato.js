

Vue.component("component-modal-contrato", {
    template:`
    <div :id="'component_modal_contrato'" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document" style="max-width: 90%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">

					<form ref="form_contrato_nuevo_detalle_arrendamiento" id="form_contrato_nuevo_detalle_arrendamiento" name="form_contrato_nuevo_detalle_arrendamiento" method="POST" enctype="multipart/form-data" autocomplete="off">
						<div class="row">
							<component-contrato  :propietarios="propietarios" v-for="(contrato, index) in contratos" :key="index" :contrato="contrato" :index="index" :arrendamiento="arrendamiento" :ref="'componente_contrato_' + index"></component-contrato>
						</div>
						<div class="row">
							<div class="panel">
								<div class="panel-body">
									<div class="col-xs-12 col-md-12 col-lg-12">
										<button v-if="btn_registrar" type="button" class="btn btn-success btn-block" @click="validar_contrato">
										<i class="icon fa fa-save" style="display: inline-block;"></i>
										<span id="demo-button-text"> Guardar Contrato de Arrendamiento</span>
										</button>
									</div>
								</div>
							</div>
						</div>
					</form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventssana</button>
                </div>
            </div>
        </div>

		<loader :loader="loader" ref="loader"></loader>
    </div>
    `,
    components: {
		'v-select': VueSelect.VueSelect
	},
	props: ["contrato_id"],
    data(){
        return {
            data_contrato: {},
            index:0,
            propietarios:[],
            arrendamiento : {
                tipo_solicitud:'Contrato de Locación de Servicio',
                tipo_contrato_id:13,
                empresa_suscribe_id:'',
                supervisor_id:'',
                observaciones:'',
                otros_anexos:[],
            },
			btn_registrar: true,
			loader:false,
        }
    },
    created(){
        this.agregar_contrato();
		this.obtener_contrato();
    },
    mounted(){
        EventBus.$on('modal-nuevo-contrato', () => {
            $('#component_modal_contrato').modal({ backdrop: "static", keyboard: false });
        });
    },
    computed: {
		contratos() {
			return this.$store.state.contratos.contratos;
		},
	},
    watch:{
       
    },
    methods: {
		obtener_contrato,
        agregar_contrato,
        validar_contrato,
        registrar_contrato,
        auditoria_send,
    },
})

function obtener_contrato() {

	let me = this;
    let url = 'sys/router/contratos/index.php';
	let data = {
		action: 'contrato_arrendamiento/obtener_propietarios',
		contrato_id: this.contrato_id,
	}
    me.loader = true;
    axios({
        method: 'post',
        url: url,
		data: data,
    })
    .then(function (response) {
        if (response.data.status == 200) {
			response.data.result.forEach(element => {
				me.propietarios.push(element)
			});
        }else{
            alertify.error(response.data.message, 5);
        }

        me.loader = false;
    }).catch(error => {
        me.loader = false;
	});
}

function agregar_contrato() {
    let me = this;
	fetch('./vue/contrato-locacionservicio/data/contrato.php')
	.then(response => response.json())
	.then(data => {
		this.$store.dispatch('contratos/ActionAgregarContrato', data);
	})
	.catch(error => {
		console.error(error);
	});
}

function validar_contrato() {

	this.validate_errors = true;

	if (this.contratos.length == 0) {
		alertify.error("Agregue un contrato", 5);
		this.validate_errors = false;
		return false;
	}

	let index_contrato = 0;

	for (let index = 0; index < this.contratos.length; index++) {
		const element = this.contratos[index];
		var component = 'componente_contrato_'+ index_contrato;
		var inmueble = element.inmuebles;
		
		if (inmueble.departamento_id.length == 0) {
			alertify.error("Seleccione un departamento del inmuble #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.departamento.focus();
			this.validate_errors = false;
			return false;
		}
		if (inmueble.provincia_id.length == 0) {
			alertify.error("Seleccione una provincia del inmuble #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.provincia.focus();
			this.validate_errors = false;
			return false;
		}
		if (inmueble.distrito_id.length == 0) {
			alertify.error("Seleccione un distrito del inmuble #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.distrito.focus();
			this.validate_errors = false;
			return false;
		}
		if (inmueble.ubicacion.length == 0) {
			alertify.error("Ingrese una ubicación del inmuble #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.ubicacion.focus();
			this.validate_errors = false;
			return false;
		}
		if (inmueble.area_arrendada.length == 0) {
			alertify.error("Ingrese una área arrendada del inmuble #"+ (parseInt(index_contrato) +1) , 5);
			console.log(parseInt(index_contrato) +1)
			this.$refs[component][0].$refs.area_arrendada.focus();
			this.validate_errors = false;
			return false;
		}
		if (inmueble.num_partida_registral.length == 0) {
			alertify.error("Ingrese un n°. partida registral del inmuble #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.num_partida_registral.focus();
			this.validate_errors = false;
			return false;
		}
		if (inmueble.oficina_registral.length == 0) {
			alertify.error("Ingrese una oficina registral  del inmuble #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.oficina_registral.focus();
			this.validate_errors = false;
			return false;
		}
	
		if (inmueble.inmueble_servicio_agua.length == 0) {
			alertify.error("Ingrese una servicios de agua #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].agregar_nuevo_suministro_agua();
			this.validate_errors = false;
			return false;
		}
		
		for (let index_sa = 0; index_sa < inmueble.inmueble_servicio_agua.length; index_sa++) {
			const element_sa = inmueble.inmueble_servicio_agua[index_sa];
			if (element_sa.nro_suministro.length == 0){
				alertify.error("Ingrese un numero de suministro #"+ (parseInt(index_sa) +1) , 5);
				this.$refs[component][0].$refs['comp_suministro_agua_'+index_sa][0].$refs.nro_suministro.focus();
				this.validate_errors = false;
				return false;
			}

			if (element_sa.nro_suministro.length < 7) {
				alertify.error("El número de suministro de agua debe ser mayor a 6 dígitos", 5);
				this.$refs[component][0].$refs['comp_suministro_agua_'+index_sa][0].$refs.nro_suministro.focus();
				this.validate_errors = false;
				return false;
			}

			if (element_sa.tipo_compromiso_pago_id.length == 0) {
				alertify.error("Seleccione el tipo de compromiso de pago del servicio del agua del inmuble" , 5);
				this.$refs[component][0].$refs['comp_suministro_agua_'+index_sa][0].$refs.tipo_compromiso_pago_id.focus();
				this.validate_errors = false;
				return false;
			}
			if (parseInt(element_sa.tipo_compromiso_pago_id) == 1 && element_sa.monto_o_porcentaje.length == 0) {
				alertify.error("Ingrese el porcentaje del pago del servicio de agua del inmuble" , 5);
				this.$refs[component][0].$refs['comp_suministro_agua_'+index_sa][0].$refs.monto_o_porcentaje.focus();
				this.validate_errors = false;
				return false;
			}
			if (parseInt(element_sa.tipo_compromiso_pago_id) == 2 && element_sa.monto_o_porcentaje.length == 0) {
				alertify.error("Ingrese el monto fijo del pago del servicio de agua del inmuble" , 5);
				this.$refs[component][0].$refs['comp_suministro_agua_'+index_sa][0].$refs.monto_o_porcentaje.focus();
				this.validate_errors = false;
				return false;
			}
			// if (parseInt(element_sa.tipo_compromiso_pago_id) == 5) {
			// 	if (parseInt(element_sa.tipo_documento_beneficiario) == 1 && element_sa.nro_documento_beneficiario.length != 8) {
			// 		alertify.error("El nro de documento debe tener 8 digitos" , 5);
			// 		this.$refs[component][0].$refs['comp_suministro_agua_'+index_sa][0].$refs.num_docu.focus();
			// 		this.validate_errors = false;
			// 		return false;
			// 	}
			// 	if (parseInt(element_sa.tipo_documento_beneficiario) == 2 && element_sa.nro_documento_beneficiario.length != 11) {
			// 		alertify.error("El nro de documento debe tener 11 digitos" , 5);
			// 		this.$refs[component][0].$refs['comp_suministro_agua_'+index_sa][0].$refs.num_docu.focus();
			// 		this.validate_errors = false;
			// 		return false;
			// 	}
			// 	if ( (parseInt(element_sa.tipo_documento_beneficiario) == 3 || parseInt(element_sa.tipo_documento_beneficiario) == 4) && element_sa.nro_documento_beneficiario.length != 12) {
			// 		alertify.error("El nro de documento debe tener 12 digitos" , 5);
			// 		this.$refs[component][0].$refs['comp_suministro_agua_'+index_sa][0].$refs.num_docu.focus();
			// 		this.validate_errors = false;
			// 		return false;
			// 	}
			// 	if (element_sa.nombre_beneficiario.length <= 8) {
			// 		alertify.error("Ingrese el nombre del beneficiario" , 5);
			// 		this.$refs[component][0].$refs['comp_suministro_agua_'+index_sa][0].$refs.nombre_beneficiario.focus();
			// 		this.validate_errors = false;
			// 		return false;
			// 	}
			// 	if (element_sa.nro_cuenta_soles.length <= 6) {
			// 		alertify.error("Ingrese el numero de cuenta" , 5);
			// 		this.$refs[component][0].$refs['comp_suministro_agua_'+index_sa][0].$refs.nro_cuenta_beneficario.focus();
			// 		this.validate_errors = false;
			// 		return false;
			// 	}
			// }
		}
	
		if (inmueble.inmueble_servicio_luz.length == 0) {
			alertify.error("Ingrese una servicios de luz #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].agregar_nuevo_suministro_luz();
			this.validate_errors = false;
			return false;
		}

		for (let index_sa = 0; index_sa < inmueble.inmueble_servicio_luz.length; index_sa++) {
			const element_sa = inmueble.inmueble_servicio_luz[index_sa];
			if (element_sa.nro_suministro.length == 0){
				alertify.error("Ingrese un numero de suministro #"+ (parseInt(index_sa) +1) , 5);
				this.$refs[component][0].$refs['comp_suministro_luz_'+index_sa][0].$refs.nro_suministro.focus();
				this.validate_errors = false;
				return false;
			}

			if (element_sa.nro_suministro.length < 7) {
				alertify.error("El número de suministro de agua debe ser mayor a 6 dígitos", 5);
				this.$refs[component][0].$refs['comp_suministro_luz_'+index_sa][0].$refs.nro_suministro.focus();
				this.validate_errors = false;
				return false;
			}

			if (element_sa.tipo_compromiso_pago_id.length == 0) {
				alertify.error("Seleccione el tipo de compromiso de pago del servicio del luz del inmuble" , 5);
				this.$refs[component][0].$refs['comp_suministro_luz_'+index_sa][0].$refs.tipo_compromiso_pago_id.focus();
				this.validate_errors = false;
				return false;
			}
			if (parseInt(element_sa.tipo_compromiso_pago_id) == 1 && element_sa.monto_o_porcentaje.length == 0) {
				alertify.error("Ingrese el porcentaje del pago del servicio de luz del inmuble" , 5);
				this.$refs[component][0].$refs['comp_suministro_luz_'+index_sa][0].$refs.monto_o_porcentaje.focus();
				this.validate_errors = false;
				return false;
			}
			if (parseInt(element_sa.tipo_compromiso_pago_id) == 2 && element_sa.monto_o_porcentaje.length == 0) {
				alertify.error("Ingrese el monto fijo del pago del servicio de luz del inmuble" , 5);
				this.$refs[component][0].$refs['comp_suministro_luz_'+index_sa][0].$refs.monto_o_porcentaje.focus();
				this.validate_errors = false;
				return false;
			}
			// if (parseInt(element_sa.tipo_compromiso_pago_id) == 5) {
			// 	if (parseInt(element_sa.tipo_documento_beneficiario) == 1 && element_sa.nro_documento_beneficiario.length != 8) {
			// 		alertify.error("El nro de documento debe tener 8 digitos" , 5);
			// 		this.$refs[component][0].$refs['comp_suministro_luz_'+index_sa][0].$refs.num_docu.focus();
			// 		this.validate_errors = false;
			// 		return false;
			// 	}
			// 	if (parseInt(element_sa.tipo_documento_beneficiario) == 2 && element_sa.nro_documento_beneficiario.length != 11) {
			// 		alertify.error("El nro de documento debe tener 11 digitos" , 5);
			// 		this.$refs[component][0].$refs['comp_suministro_luz_'+index_sa][0].$refs.num_docu.focus();
			// 		this.validate_errors = false;
			// 		return false;
			// 	}
			// 	if ( (parseInt(element_sa.tipo_documento_beneficiario) == 3 || parseInt(element_sa.tipo_documento_beneficiario) == 4) && element_sa.nro_documento_beneficiario.length != 12) {
			// 		alertify.error("El nro de documento debe tener 12 digitos" , 5);
			// 		this.$refs[component][0].$refs['comp_suministro_luz_'+index_sa][0].$refs.num_docu.focus();
			// 		this.validate_errors = false;
			// 		return false;
			// 	}
			// 	if (element_sa.nombre_beneficiario.length <= 8) {
			// 		alertify.error("Ingrese el nombre del beneficiario" , 5);
			// 		this.$refs[component][0].$refs['comp_suministro_luz_'+index_sa][0].$refs.nombre_beneficiario.focus();
			// 		this.validate_errors = false;
			// 		return false;
			// 	}
			// 	if (element_sa.nro_cuenta_soles.length <= 6) {
			// 		alertify.error("Ingrese el numero de cuenta" , 5);
			// 		this.$refs[component][0].$refs['comp_suministro_luz_'+index_sa][0].$refs.nro_cuenta_beneficario.focus();
			// 		this.validate_errors = false;
			// 		return false;
			// 	}
			// }
		}
		
		if (inmueble.tipo_compromiso_pago_arbitrios.length == 0) {
			alertify.error("Seleccione el tipo de compromiso de pago de arbitrios del inmuble #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.tipo_compromiso_pago_arbitrios.focus();
			this.validate_errors = false;
			return false;
		}
		if (parseInt(inmueble.tipo_compromiso_pago_arbitrios) == 1 && inmueble.porcentaje_pago_arbitrios.length == 0 ) {
			alertify.error("Ingrese el porcentaje de pago de arbitrios del inmuble #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.porcentaje_pago_arbitrios.focus();
			this.validate_errors = false;
			return false;
		}
		
		var condicion_economica = element.condicion_economica;

		if (condicion_economica.tipo_moneda_id.length == 0 ) {
			alertify.error("Seleccione una moneda de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.tipo_moneda_id.focus();
			this.validate_errors = false;
			return false;
		}
		if (condicion_economica.pago_renta_id.length == 0 ) {
			alertify.error("Seleccione un pago de renta de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.pago_renta_id.focus();
			this.validate_errors = false;
			return false;
		}
		if (condicion_economica.monto_renta.length <= 4 ) {
			alertify.error("Ingrese el monto de renta pactada de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.monto_renta.focus();
			this.validate_errors = false;
			return false;
		}
		if (parseInt(condicion_economica.pago_renta_id) == 2 && condicion_economica.cuota_variable.length  == 0 ) {
			alertify.error("Ingrese el porcentaje de venta de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.cuota_variable.focus();
			this.validate_errors = false;
			return false;
		}
		if (parseInt(condicion_economica.pago_renta_id) == 2 && parseInt(condicion_economica.cuota_variable)  == 0 ) {
			alertify.error("El porcentaje de venta no puede ser 0 de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.cuota_variable.focus();
			this.validate_errors = false;
			return false;
		}
		if (parseInt(condicion_economica.pago_renta_id) == 2 && condicion_economica.tipo_venta_id.length  == 0 ) {
			alertify.error("Seleccione el tipo de venta de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.tipo_venta_id.focus();
			this.validate_errors = false;
			return false;
		}
		if (condicion_economica.afectacion_igv_id.length  == 0 ) {
			alertify.error("Seleccione el IGV en la renta de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.afectacion_igv_id.focus();
			this.validate_errors = false;
			return false;
		}
		if (condicion_economica.garantia_monto.length  < 4 ) {
			alertify.error("Ingresa un monto de garantia de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.garantia_monto.focus();
			this.validate_errors = false;
			return false;
		}
		if (condicion_economica.tipo_adelanto_id.length  == 0 ) {
			alertify.error("Seleccione un tipo de adelanto de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.tipo_adelanto_id.focus();
			this.validate_errors = false;
			return false;
		}
		if (parseInt(condicion_economica.tipo_adelanto_id) == 1 && condicion_economica.adelantos.length == 0 ) {
			alertify.error("Seleccione los adelantos de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].show_modal_adelantos();
			this.validate_errors = false;
			return false;
		}
		if (condicion_economica.impuesto_a_la_renta_id.length == 0 ) {
			alertify.error("Seleccione el impuesto a la renta de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.impuesto_a_la_renta_id.focus();
			this.validate_errors = false;
			return false;
		}
		if (condicion_economica.carta_de_instruccion_id.length == 0 ) {
			alertify.error("Seleccione si AT deposita impuesto a la renta a SUNAT, o no de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.carta_de_instruccion_id.focus();
			this.validate_errors = false;
			return false;
		}
		//if (condicion_economica.numero_cuenta_detraccion.length == 0 ) {
		//	alertify.error("SIngrese el Número de Cuenta de Detracción (Banco de la Nación) de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
		//	this.$refs[component][0].$refs.numero_cuenta_detraccion.focus();
		//	this.validate_errors = false;
		//  return false;
		//}
	
		if (condicion_economica.periodo_gracia_id.length == 0 ) {
			alertify.error("Ingrese el numero de periodo de gracias de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.periodo_gracia_id.focus();
			this.validate_errors = false;
			return false;
		}
		if (parseInt(condicion_economica.periodo_gracia_id) == 1 && condicion_economica.periodo_gracia_numero.length == 0 ) {
			alertify.error("Ingrese el numero de periodo de gracias de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.periodo_gracia_numero.focus();
			this.validate_errors = false;
			return false;
		}	
	
		if (condicion_economica.plazo_id.length == 0 ) {
			alertify.error("Seleccione el periodo de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.plazo_id.focus();
			this.validate_errors = false;
			return false;
		}
		if (parseInt(condicion_economica.plazo_id) == 1 && condicion_economica.cant_meses_contrato.length == 0) {
			alertify.error("Ingrese la vigencia del contrato de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.cant_meses_contrato.focus();
			this.validate_errors = false;
			return false;
		}
		if (condicion_economica.fecha_inicio.length == 0 ) {
			alertify.error("Seleccione la fecha inicio de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.fecha_inicio.focus();
			this.validate_errors = false;
			return false;
		}
		if (parseInt(condicion_economica.plazo_id) == 1 && condicion_economica.fecha_fin.length == 0 ) {
			alertify.error("Seleccione la fecha fin de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.fecha_fin.focus();
			this.validate_errors = false;
			return false;
		}

		if (condicion_economica.tipo_incremento_id.length == 0 ) {
			alertify.error("Seleccione el tipo de incremento de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.tipo_incremento_id.focus();
			this.validate_errors = false;
			return false;
		}
		if (parseInt(condicion_economica.tipo_incremento_id) == 1 && condicion_economica.incrementos.length == 0 ) {
			alertify.error("Ingrese los incrementos de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].show_modal_nuevo_incremento();
			this.validate_errors = false;
			return false;
		}
		if (condicion_economica.tipo_inflacion_id.length == 0 ) {
			alertify.error("Seleccione el tipo de inflación de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.tipo_inflacion_id.focus();
			this.validate_errors = false;
			return false;
		}
		if (parseInt(condicion_economica.tipo_inflacion_id) == 1 && condicion_economica.inflaciones.length == 0 ) {
			alertify.error("Ingrese las inflaciones de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].show_modal_nueva_inflacion();
			this.validate_errors = false;
			return false;
		}
		if (condicion_economica.tipo_cuota_extraordinaria_id.length == 0 ) {
			alertify.error("Seleccione el tipo de cuota extraordinaria de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.tipo_cuota_extraordinaria_id.focus();
			this.validate_errors = false;
			return false;
		}
		if (parseInt(condicion_economica.tipo_cuota_extraordinaria_id) == 1 && condicion_economica.cuotas_extraordinarias.length == 0 ) {
			alertify.error("Ingrese las cuotas extraordinarias de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].show_modal_nueva_cuota_extraordinaria();
			this.validate_errors = false;
			return false;
		}
			
		if (condicion_economica.beneficiarios.length == 0 ) {
			alertify.error("Ingrese los beneficiarios de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].show_modal_nuevo_beneficiario();
			this.validate_errors = false;
			return false;
		}

		if (condicion_economica.responsables_ir.length == 0 ) {
			alertify.error("Ingrese los responsables IR de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].show_modal_nuevo_responsable_ir();
			this.validate_errors = false;
			return false;
		}

		let porcentaje = 0;
		for (let index = 0; index < condicion_economica.responsables_ir.length; index++) {
			const responsable = condicion_economica.responsables_ir[index];
			porcentaje += parseFloat(responsable.porcentaje);
		}

		if (porcentaje != 100 ) {
			alertify.error("La suma de porcentaje del responsable IR debe ser igual a 100% de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.validate_errors = false;
			return false;
		}

		if (condicion_economica.fecha_suscripcion.length == 0 ) {
			alertify.error("Seleccione una fecha de suscripción de la condición económica #"+ (parseInt(index_contrato) +1) , 5);
			this.$refs[component][0].$refs.fecha_suscripcion.focus();
			this.validate_errors = false;
			return false;
		}

		index_contrato ++;
	}

	let me = this;
	if (this.validate_errors) {
		swal({
			title: "Esta seguro de registrar el contrato de arrendamiento?",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			confirmButtonText: "Si, estoy de acuerdo!",
			cancelButtonText: "No, cancelar",
			closeOnConfirm: true,
			closeOnCancel: true,
	
		},function (isConfirm) {
			if (isConfirm) {
				me.registrar_contrato();
			} 
		});
		
	}


}

function registrar_contrato() {

	let me = this;
	let url = 'sys/router/contratos/index.php';

	var data = new FormData($(this.$refs.form_contrato_nuevo_detalle_arrendamiento)[0]);
	data.append("contrato_id", this.contrato_id);
	data.append("action", 'contrato_arrendamiento/agregar_contrato_arrendamiento');
	data.append("contratos", JSON.stringify(this.contratos));

	var data_auditoria = Object.fromEntries(
		Array.from(data.keys()).map(key => [
		key, data.getAll(key).length > 1 ? 
			data.getAll(key) : data.get(key)
		])
	)

	this.auditoria_send({ proceso: "agregar_contrato_arrendamiento", data: data_auditoria });
	this.loader = true;
	this.btn_registrar = false;

	axios({
		method: 'POST',
		url: url,
		data: data,
	})
	.then(function (response) {
		me.loader = false;
		me.btn_registrar = true;
		me.auditoria_send({ proceso: "respuesta_agregar_contrato_arrendamiento", data: response.data });
		if (response.data.status == 200) {
			swal(
				{
					title: "Registro exitoso",
					text: response.data.message,
					html: true,
					type: "success",
					timer: 6000,
					closeOnConfirm: false,
					showCancelButton: false,
				},
				function (isConfirm) {
					location.reload()
				}
			);

			setTimeout(function () {
				location.reload()
			}, 5000);
		}else{
			swal({
				title: "Error al guardar Solicitud de Arrendamiento",
				text: response.data.message,
				html: true,
				type: "error",
				timer: 6000,
				closeOnConfirm: true,
				showCancelButton: false,
			});
		}
		

	}).catch(function (error) {
		me.loader = false;
		me.btn_registrar = true;
		swal({
			title: "Error al guardar Solicitud de Arrendamiento",
			text: error,
			html: true,
			type: "error",
			timer: 6000,
			closeOnConfirm: true,
			showCancelButton: false,
		});
	});
}

function auditoria_send(data) {

	if(!data){
		data = {};
	}
	if(!data.proceso){ data.proceso = "visita"; }
	if(!data.sec_id){ data.sec_id = sec_id; }
	if(!data.sub_sec_id){ data.sub_sec_id = sub_sec_id; }
	if(!data.item_id){ data.item_id = item_id; }
	if(!data.url){ data.url = window.location.href; }

	let me = this;
	let url = 'sys/sys_auditoria.php';
	axios({
		method: 'POST',
		url: url,
		data:  {
			"opt": 'auditoria_send',
			"data": data
		},
	})
	.then(function (response) {
	
	});

	
}
