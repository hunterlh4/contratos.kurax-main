

Vue.component("component-modal-incrementos", {
    template:`
 
    <div :id="'component_modal_incrementos_'+ index" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="cerrar_modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ title }} - Ficha #{{index + 1}}</h4>
                </div>
                <div class="modal-body">
                    <div class="row" :id="'modal_body_incremento_'+ index" tabindex="0">
                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <table class="table table-bordered table-striped no-mb" style="font-size:12px">
                                <thead>
                                    <tr>
                                        <th width="12%" class="text-center">Valor</th>
                                        <th width="40%" class="text-center">Tipo Valor</th>
                                        <th  class="text-center">Continuidad</th>
                                        <th :class="'text-center ' + (incremento.tipo_continuidad_id == 3 ? 'hide':'')">A partir del</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input ref="valor" v-model="incremento.valor" type="number" class="form-control text-right">
                                        </td>
                                        <td>
                                            <v-select ref="tipo_valor_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_valor" :filterable="true" label="text"  v-model='tipo_valor_val'></v-select>
                                        </td>
                                        <td>
                                            <v-select ref="continuidad_id" placeholder="-- Seleccione --" class="w-100" :options="continuidad" :filterable="true" label="text"  v-model='continuidad_val'></v-select>
                                        </td>
                                        <td :class="incremento.tipo_continuidad_id == 3 ? 'hide':''">
                                            <v-select ref="a_partir_del_anio_id" placeholder="-- Seleccione --" class="w-100" :options="anios" :filterable="true" label="text"  v-model='anio_val'></v-select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table> 
                        </div>
                    </div>

                    <div class="row" v-if="action == 'nuevo'">
                        <div class="col-md-12"> <br> </div>

                        <div class="col-xs-12 col-md-4 col-lg-4">
                            <div class="form-group">
                                <div class="form-group">
                                    <button type="button" @click="agregar_incremento(false)" class="btn btn-success btn-xs btn-block">
                                        <i class="icon fa fa-plus"></i>
                                        <span>Agregar el incremento</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-md-8 col-lg-8">
                            <div class="form-group">
                                <div class="form-group">
                                    <button type="button" @click="agregar_incremento(true)" class="btn btn-success btn-xs btn-block">
                                        <i class="icon fa fa-plus"></i>
                                        <span>Agregar el incremento y seguir agregando otro incremento</span>
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
                                    <button type="button" @click="agregar_incremento(false)" class="btn btn-warning btn-xs btn-block">
                                        <i class="icon fa fa-pencil"></i>
                                        <span>Modificar el incremento</span>
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
    props:["incrementos","index"],
    components: {
		'v-select': VueSelect.VueSelect
	},
    data(){
        return {

            title:'',
            action:'',
            incremento:{
                id:'',
                contrato_id:'',
                valor:'',
                tipo_valor_id:'',
                tipo_continuidad_id:'',
                a_partir_del_anio_id:'',
                fecha_cambio:'',

                tipo_valor:'',
                tipo_continuidad:'',
                a_partir_del_anio:'',
            },

            tipo_valor: [
                {id: '1', text: 'soles o dolares (según el tipo de moneda del contrato)'},
                {id: '2', text: '% (por ciento)'},
            ],
            continuidad: [
                {id: '1', text: 'el'},
                {id: '2', text: 'anual a partir del'},
                {id: '3', text: 'anual'},
            ],
            anios: [
                {id: '1', text: 'Primer año'},
                {id: '2', text: 'Segundo año'},
                {id: '3', text: 'Tercer año'},
                {id: '4', text: 'Cuarto año'},
                {id: '5', text: 'Quinto año'},
                {id: '6', text: 'Sexto año'},
                {id: '7', text: 'Septimo año'},
                {id: '8', text: 'Octavo año'},
                {id: '9', text: 'Noveno año'},
            ],

            tipo_valor_val: null,
            continuidad_val: null,
            anio_val: null,

        }
    },
    mounted(){
        EventBus.$on('nuevo_incremento', (data) => {

            this.title = data.title;
            this.action = data.action;
            this.resetear_form();

            let me = this;
            setTimeout(function () {
                $(me.$refs.valor).on({
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

                                me.incremento.valor = new_value;
                                return new_value;
                            });
                        } else {
                            me.incremento.valor = "0.00";
                            $(event.target).val("0.00");
                        }
                    },
                });
                $(me.$refs.valor).focus();
            }, 100);
        });

        EventBus.$on('editar_incremento', (data) => {

            this.title = data.title;
            this.action = data.action;
            let index = data.index;

            let contrato_id = this.incrementos[index].contrato_id; 
            let valor = this.incrementos[index].valor; 
            let tipo_valor_id = this.incrementos[index].tipo_valor_id; 
            let tipo_continuidad_id = this.incrementos[index].tipo_continuidad_id; 
            let a_partir_del_anio_id = this.incrementos[index].a_partir_del_anio_id; 
            let fecha_cambio = this.incrementos[index].fecha_cambio; 

            let tipo_valor = this.incrementos[index].tipo_valor; 
            let tipo_continuidad = this.incrementos[index].tipo_continuidad; 
            let a_partir_del_anio = this.incrementos[index].a_partir_del_anio; 

            this.tipo_valor_val = { id: tipo_valor_id, text:tipo_valor };
            this.continuidad_val = { id: tipo_continuidad_id, text:tipo_continuidad };
            this.anio_val = { id: a_partir_del_anio_id , text:a_partir_del_anio };


            this.incremento.id = index; 
            this.incremento.contrato_id = contrato_id; 
            this.incremento.valor = valor; 
            this.incremento.tipo_valor_id = tipo_valor_id; 
            this.incremento.tipo_continuidad_id = tipo_continuidad_id; 
            this.incremento.a_partir_del_anio_id = a_partir_del_anio_id; 
            this.incremento.fecha_cambio = fecha_cambio; 

            this.incremento.tipo_valor = tipo_valor; 
            this.incremento.tipo_continuidad = tipo_continuidad; 
            this.incremento.a_partir_del_anio = a_partir_del_anio; 

            let me = this;
            setTimeout(function () {
                $(me.$refs.valor).on({
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

                                me.incremento.valor = new_value;
                                return new_value;
                            });
                        } else {
                            me.incremento.valor = "0.00";
                            $(event.target).val("0.00");
                        }
                    },
                });
                $(me.$refs.valor).focus();
            }, 100);
        });
    },
    watch:{
        tipo_valor_val (newValue){
            if (newValue == null) {
                this.incremento.tipo_valor_id = '';
                this.incremento.tipo_valor = '';
                return false;
            }
            this.incremento.tipo_valor_id = newValue.id;
            this.incremento.tipo_valor = newValue.text;
        },
        continuidad_val (newValue){
            if (newValue == null) {
                this.incremento.tipo_continuidad_id = '';
                this.incremento.tipo_continuidad = '';
                return false;
            }
            this.incremento.tipo_continuidad_id = newValue.id;
            this.incremento.tipo_continuidad = newValue.text;
            this.incremento.a_partir_del_anio_id = '';
            this.incremento.a_partir_del_anio = '';
        },
        anio_val (newValue){
            if (newValue == null) {
                this.incremento.a_partir_del_anio_id = '';
                this.incremento.a_partir_del_anio = '';
                return false;
            }
            this.incremento.a_partir_del_anio_id = newValue.id;
            this.incremento.a_partir_del_anio = newValue.text;
        },
    },
    methods: {
      agregar_incremento,
      resetear_form,
      cerrar_modal,
        
    },
})

function agregar_incremento(continuar_registro) {

    let me = this;
    var id = this.incremento.id;
	var valor = this.incremento.valor;
	var tipo_valor_id = this.incremento.tipo_valor_id;
	var tipo_continuidad_id = this.incremento.tipo_continuidad_id;
	var a_partir_del_anio_id = this.incremento.a_partir_del_anio_id;

    var tipo_valor = this.incremento.tipo_valor;
	var tipo_continuidad = this.incremento.tipo_continuidad;
	var a_partir_del_anio = this.incremento.a_partir_del_anio;

    let data_incremento = {
        id: id,
        valor: valor,
        tipo_valor_id: tipo_valor_id,
        tipo_continuidad_id: tipo_continuidad_id,
        a_partir_del_anio_id: a_partir_del_anio_id,
        tipo_valor: tipo_valor,
        tipo_continuidad: tipo_continuidad,
        a_partir_del_anio: a_partir_del_anio,
    };

	if (valor.length == 0) {
		alertify.error("Ingrese el valor", 5);
        $(me.$refs.valor).focus();
		return false;
	}

	if (tipo_valor_id.length == 0) {
		alertify.error("Seleccione el tipo de valor", 5);
        $(me.$refs.tipo_valor_id).focus();
		return false;
	}

	if (parseInt(tipo_valor_id) == 2 && valor.length > 5) {
		alertify.error("El incremento no puede ser mayor al 100%", 5);
        $(me.$refs.tipo_valor_id).focus();
		return false;
	}

	if (tipo_continuidad_id.length == 0) {
		alertify.error("Seleccione el tipo de continuidad", 5);
        $(me.$refs.tipo_continuidad_id).focus();
		return false;
	}

	if (a_partir_del_anio_id.length == 0 && parseInt(tipo_continuidad_id) != 3) {
		alertify.error("Seleccione el año del inicio del incremento", 5);
        $(me.$refs.a_partir_del_anio_id).focus();
		return false;
	}


    if (id.length == 0) {
        this.incrementos.push(data_incremento);
        alertify.success("Se ha agregado el nuevo incremento", 5);
    }else{
        this.incrementos[id].valor = valor;
        this.incrementos[id].tipo_valor_id = tipo_valor_id;
        this.incrementos[id].tipo_continuidad_id = tipo_continuidad_id;
        this.incrementos[id].a_partir_del_anio_id = a_partir_del_anio_id;
        this.incrementos[id].tipo_valor = tipo_valor;
        this.incrementos[id].tipo_continuidad = tipo_continuidad;
        this.incrementos[id].a_partir_del_anio = a_partir_del_anio;
        alertify.success("Se ha modificado el incremento", 5);
        var id_modal = 'component_modal_incrementos_' + this.index;
        $('#'+id_modal).modal('hide');
        $('.modal').css('overflow', 'auto');
    }

    if (continuar_registro) {
        this.resetear_form();
    }else{
        var id_modal = 'component_modal_incrementos_' + this.index;
        $('#'+id_modal).modal('hide');
        $('.modal').css('overflow', 'auto');
        $('#table-incrementos-'+this.index).focus();
    }
}



function resetear_form() {
    this.incremento.id = '';
    this.incremento.contrato_id = '';
    this.incremento.valor = '';
    this.incremento.tipo_valor_id = '';
    this.incremento.tipo_continuidad_id = '';
    this.incremento.a_partir_del_anio_id = '';
    this.incremento.fecha_cambio = '';

    this.incremento.tipo_valor = '';
    this.incremento.tipo_continuidad = '';
    this.incremento.a_partir_del_anio = '';

    this.tipo_valor_val = null;
    this.continuidad_val = null;
    this.anio_val = null;
}


function cerrar_modal() {
    $('#component_modal_incrementos_'+this.index).modal('hide');
    $('.modal').css('overflow', 'auto');
    $('#table-incrementos-'+this.index).focus();
}

