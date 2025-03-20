

Vue.component("component-modal-responsables-ir", {
    template:`
 
    <div :id="'component_modal_responsables_ir_'+ index" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="cerrar_modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Seleccionar Responsable IR - Ficha #{{ index + 1}}</h4>
                </div>
                <div class="modal-body">
                    <div class="col-xs-12 col-md-12 col-lg-12" :id="'modal_body_responsable_ir_'+ index" tabindex="0">
                        <table class="table table-bordered table-hover no-mb" style="font-size: 11px; margin-top: 10px">
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
                                        <a @click="seleccionar_responsable_ir(it)" class="btn btn-success btn-xs">
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
                                <strong><i class="glyphicon glyphicon-info-sign"></i> Si el responsable IR no es uno de los propietarios, <br> dar clic en </strong>
                                <button @click="show_modal_registro_responsable_ir()" type="button" class="btn btn-success btn-xs">
                                    <i class="icon fa fa-plus"></i>
                                    <span id="demo-button-text">Registrar Responsable IR</span>
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
    </div>
    `,
    props:["responsables_ir","propietarios","index"],
    components: {
		'v-select': VueSelect.VueSelect
	},
    data(){
        return {

      

        }
    },
    mounted(){

        EventBus.$on('nuevo-responsable-ir', (data) => {
            
        });
     
      
    },
    watch:{
      
    },
    methods: {
        seleccionar_responsable_ir,
        show_modal_registro_responsable_ir,
        cerrar_modal,
    },
})

function cerrar_modal() {
    $('#component_modal_responsables_ir_'+this.index).modal('hide');
    $('.modal').css('overflow', 'auto');
    $('#table-responsable-ir-'+this.index).focus();
}

function seleccionar_responsable_ir(index) {
    const propietario = this.propietarios[index]; 
    let data = {
        title: 'Registrar Responsable IR',
        action: 'registrar',
        responsable_ir: {
            id : '',
            contrato_id : '',
            tipo_documento_id : 2,
            num_documento : propietario.num_ruc,
            nombres : propietario.nombre,
            estado_emisor : '',
            porcentaje : '',
        }
    }

    EventBus.$emit('seleccionar-propietario-responsable-ir', data);
    
    $('#component_modal_responsables_ir_'+ this.index).modal('hide');
    $('#component_modal_responsable_ir_registro_'+ this.index).modal({ backdrop: "static", keyboard: false });
    $('#modal_body_responsable_ir_registro_'+this.index).focus();
}

function show_modal_registro_responsable_ir() {
    let data = {
        title: 'Registrar Responsable IR',
        action: 'registrar',
    }
    EventBus.$emit('nuevo-registro-responsable-ir', data);
    $('#component_modal_responsables_ir_'+ this.index).modal('hide');
    $('#component_modal_responsable_ir_registro_'+ this.index).modal({ backdrop: "static", keyboard: false });
    $('#modal_body_responsable_ir_registro_'+this.index).focus();

}