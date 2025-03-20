

Vue.component("component-modal-beneficiarios", {
    template:`
 
    <div :id="'component_modal_beneficiarios_'+ index" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="cerrar_modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Seleccionar Beneficiario - Ficha #{{ index + 1}}</h4>
                </div>
                <div class="modal-body">
                    <div class="col-xs-12 col-md-12 col-lg-12" :id="'modal_body_beneficiarios_'+ index" tabindex="0">
                        <table ref="table_modal" class="table table-bordered table-hover no-mb" style="font-size: 11px; margin-top: 10px">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Nombre / Razón Social</th>
                                    <th class="text-center">DNI o Pasaporte</th>
                                    <th class="text-center">RUC</th>
                                    <th class="text-center">Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, it) in propietarios" :key="it">
                                    <td class="text-center">{{ it + 1}}</td>
                                    <td>{{ item.nombre }}</td>
                                    <td>{{ item.num_docu }}</td>
                                    <td>{{ item.num_ruc }}</td>
                                    <td class="text-center">
                                        <a @click="seleccionar_propietario(it)" class="btn btn-success btn-xs">
                                            <i class="fa fa-plus"></i> Agregar al propietario como
                                            beneficiario
                                        </a>
                                    </td>
                                </tr>
                    
                            </tbody>
                        </table>
                    </div>
                    <div class="col-xs-12 col-md-12 col-lg-12">
                        <br>
                    </div>
                    <div class="col-xs-12 col-md-12 col-lg-12 text-center">
                        <div class="form-group">
                            <div class="alert alert-warning" role="alert">
                                <strong><i class="glyphicon glyphicon-info-sign"></i> Si el beneficiario no es uno de los propietarios, <br> dar clic en </strong>
                                <button @click="show_modal_registro_beneficiario()" type="button" class="btn btn-success btn-xs">
                                    <i class="icon fa fa-plus"></i>
                                    <span id="demo-button-text">Registrar Beneficiario</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" @click="cerrar_modal">Cerrar Ventana</button>
                </div>
            </div>
        </div>
        <loader :loader="loader" ref="loader"></loader>
    </div>
    `,
    props:["beneficiarios","propietarios","index"],
    components: {
		'v-select': VueSelect.VueSelect
	},
    data(){
        return {
            loader:false,
        }
    },
    mounted(){
        EventBus.$on('abrir_modal_beneficiarios', (data) => {
           
        });
    },
    watch:{
      
    },
    methods: {
        seleccionar_propietario,
        show_modal_registro_beneficiario,
        cerrar_modal,
    },
})

function seleccionar_propietario(index) {
    const propietario = this.propietarios[index]; 
    this.loader = true;
    let data = {
        title: 'Registrar Beneficiario',
        action: 'registrar',
        beneficiario: {
            id : '',
            contrato_id : '',
            tipo_persona_id : propietario.tipo_persona_id,
            tipo_docu_identidad_id : propietario.tipo_docu_identidad_id,
            num_docu : propietario.num_docu,
            nombre : propietario.nombre,
            forma_pago_id : '',
            banco_id : '',
            num_cuenta_bancaria : '',
            num_cuenta_cci : '',
            tipo_monto_id : '',
            monto : '',
        }
    }
    EventBus.$emit('seleccionar-propietario-beneficiario', data);
    $('#component_modal_beneficiarios_'+ this.index).modal('hide');
    $('#component_modal_beneficiario_registro_'+ this.index).modal({ backdrop: "static", keyboard: false });
    $('#modal_body_beneficiario_registro_'+this.index).focus();
  
    this.loader = false;
}

function show_modal_registro_beneficiario() {
    this.loader = true;
    let data = {
        title: 'Registrar Beneficiario',
        action: 'registrar',
    }
    EventBus.$emit('nuevo-beneficiario', data);
    $('#component_modal_beneficiarios_'+ this.index).modal('hide');
    $('#component_modal_beneficiario_registro_'+ this.index).modal({ backdrop: "static", keyboard: false });
    $('#modal_body_beneficiario_registro_'+this.index).focus();
    this.loader = false;
}

function cerrar_modal() {
    $('#component_modal_beneficiarios_'+this.index).modal('hide');
    $('.modal').css('overflow', 'auto');
    $('#table-beneficiarios-'+this.index).focus();
}