

Vue.component("component-modal-adelantos", {
    template:`
 
    <div :id="'component_modal_adelantos_'+ index" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="cerrar_modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Adelantos - Ficha #{{index +1}}</h4>
                </div>
                <div class="modal-body" >
                    <div class="row">
                        <div class="col-xs-12 col-md-12 col-lg-12" :id="'modal_body_adelantos_'+ index" tabindex="0">
                            <div class="form-group">
                                <div v-for="(item, it) in tipo_mes_adelanto" :key="it">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" class="contrato_adelanto" :value="item.id" :checked="item.checked"
                                        @change="ChangeValueAdelantos(item)">
                                        {{ item.text }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" @click="cerrar_modal" class="btn btn-success">
                        <i class="icon fa fa-save"></i>
                        Guardar meses de adelanto
                    </button>
                    
                </div>
            </div>
        </div>
    </div>
    `,
    props:["adelantos","index"],
    components: {
		'v-select': VueSelect.VueSelect
	},
    data(){
        return {
            tipo_mes_adelanto:[], 
        }
    },
    mounted(){
        EventBus.$on('abrir_modal_adelantos', (data) => {
            this.resetear_tipo_mes_adelanto();
        });
    },
    computed: {
       
    },
    methods: {
        ChangeValueAdelantos,
        resetear_tipo_mes_adelanto,
        cerrar_modal,
    },
})

function ChangeValueAdelantos(value) {
    const index = this.adelantos.map(item => item.id).indexOf(value.id);
    if (index != -1) {
        this.adelantos.splice(index, 1);
    }else{
        value.checked = true;
        this.adelantos.push(value);
    }
}

function resetear_tipo_mes_adelanto() {
    
    let me = this;
	let url = 'sys/router/contratos/index.php';
    this.tipo_mes_adelanto = [];
    let data = {
        action :'obtener_meses_adelantos',
    }
	axios({
		method: 'post',
		url: url,
        data: data,
	})
	.then(function (response) {
		if (response.data.status == 200) {
			response.data.result.forEach(element => {
				me.tipo_mes_adelanto.push({
					id: element.id, text: element.nombre, checked: false
				});
			});
		}
	});


    this.adelantos.forEach(element => {
        const index = this.tipo_mes_adelanto.map(item => item.id).indexOf(element.id);
        if (index != -1) {
            this.tipo_mes_adelanto[index].checked = true;
        }
    });
}

function cerrar_modal() {
    $('#component_modal_adelantos_'+this.index).modal('hide');
    $('.modal').css('overflow', 'auto');

    $('#table-adelantos-'+ this.index).focus();
    
}



