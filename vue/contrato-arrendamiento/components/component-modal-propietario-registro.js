// ARRENDADOR REGISTRO 1A
Vue.component("component-modal-propietario-registro", {
  template: `
 
    <div id="component-modal-registro-propietario" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ title }}</h4>
                </div>
                <div class="modal-body">
                   
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Tipo de Persona:</label>
                                <v-select autocomplete ref="tipo_persona_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_persona" :filterable="true" label="text"  v-model='tipo_persona_val'></v-select>
                                
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Nombre / Razón Social del propietario: 1</label>
                                <input ref="nombre" type="text" class="form-control" v-model="propietario.nombre">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Tipo de documento de identidad:</label>
                                <v-select ref="tipo_docu_identidad_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_docu_identidad" :filterable="true" label="text"  v-model='tipo_docu_identidad_val'></v-select>
                            </div>
                        </div>
                        <div class="col-md-12" v-if="propietario.tipo_docu_identidad_id != 2">
                            <div class="form-group">
                                <label for="">{{label.num_docu}}:</label>
                                <input ref="num_docu" type="text" class="form-control mask_dni_agente" :maxlength="atributos.maxlength_num_docu" v-model="propietario.num_docu">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Número de RUC del propietario:</label>
                                <input ref="num_ruc" type="text" class="form-control" maxlength="11" v-model="propietario.num_ruc">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Domicilio del propietario:</label>
                                <input ref="direccion" type="text" class="form-control" v-model="propietario.direccion">
                            </div>
                        </div>
                        <div class="col-md-12" v-if="propietario.tipo_persona_id == 2">
                            <div class="form-group">
                                <label for="">Representante Legal:</label>
                                <input ref="representante_legal"  type="text" class="form-control" v-model="propietario.representante_legal">
                            </div>
                        </div>
                        <div class="col-md-12" v-if="propietario.tipo_persona_id == 2">
                            <div class="form-group">
                                <label for="">N° Partida Registral de la empresa:</label>
                                <input ref="num_partida_registral" type="text" class="form-control" v-model="propietario.num_partida_registral">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Persona de contacto:</label>
                                <v-select ref="tipo_persona_contacto"  placeholder="-- Seleccione --" class="w-100" :options="tipo_persona_contacto" :filterable="true" label="text"  v-model='tipo_persona_contacto_val'></v-select>
                            </div>
                        </div>
                        <div class="col-md-12" v-if="propietario.tipo_persona_contacto == 2">
                            <div class="form-group">
                                <label for="">Nombre de la persona de contacto:</label>
                                <input ref="contacto_nombre" type="text" class="form-control" v-model="propietario.contacto_nombre">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Teléfono de la persona de contacto:</label>
                                <input ref="contacto_telefono" type="text" maxlength="9" class="form-control" v-model="propietario.contacto_telefono">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Mail de la persona de contacto:</label>
                                <input ref="contacto_email" type="text" class="form-control" v-model="propietario.contacto_email">
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
                    <button type="button" v-if="propietario.id.length == 0" class="btn btn-success" @click="validar_formulario">
                        <i class="icon fa fa-plus"></i>
                        Agregar propietario
                    </button>
                    <button type="button" v-if="propietario.id.length > 0" class="btn btn-success" @click="validar_formulario">
                        <i class="icon fa fa-plus"></i>
                        Guardar cambios
                    </button>
                    
                </div>
            </div>
        </div>

        <loader :loader="loader" ref="loader"></loader>
    </div>
    `,
  props: ["propietarios"],
  components: {
    "v-select": VueSelect.VueSelect,
  },
  data() {
    return {
      //options
      tipo_persona: [
        { id: 1, text: "Persona Natural" },
        { id: 2, text: "Persona Jurídica" },
      ],
      tipo_docu_identidad: [
        { id: 1, text: "DNI" },
        { id: 2, text: "RUC" },
        { id: 3, text: "Pasaporte" },
        { id: 4, text: "Carnet de Extranjeria" },
      ],
      tipo_persona_contacto: [
        { id: 1, text: "El propietario es la persona de contacto" },
        { id: 2, text: "El propietario no es la persona de contacto" },
      ],
      //options vale
      tipo_persona_val: null,
      tipo_docu_identidad_val: null,
      tipo_persona_contacto_val: null,
      //data
      title: "",
      propietario: {
        id: "",
        tipo_persona_id: "",
        tipo_docu_identidad_id: "",
        num_docu: "",
        num_ruc: "",
        nombre: "",
        direccion: "",
        representante_legal: "",
        num_partida_registral: "",
        tipo_persona_contacto: "",
        contacto_nombre: "",
        contacto_telefono: "",
        contacto_email: "",
        usuario_id: "",
      },
      label: {
        num_docu: "Número de DNI del propietario",
      },
      atributos: {
        maxlength_num_docu: 8,
      },

      loader: false,
    };
  },
  mounted() {
    EventBus.$on("modal-registro-propietario", (data) => {
      this.show_modal_propietario(data);
    });
    EventBus.$on("modal-modificar-propietario", (data) => {
      this.resetear_form_propietario();
      this.obtener_propietario(data);
    });
  },
  computed: {},
  methods: {
    show_modal_propietario,
    validar_formulario,
    validar_email_valido,
    registrar_propietario,
    resetear_form_propietario,
    obtener_propietario,
    modificar_propietario,
  },
  watch: {
    tipo_persona_val(newValue) {
      if (newValue == null) {
        this.propietario.tipo_persona_id = "";
        this.propietario.representante_legal = "";
        this.propietario.num_partida_registral = "";
        return false;
      }
      this.propietario.tipo_persona_id = newValue.id;
      if (newValue.id == 1) {
        this.propietario.representante_legal = "";
        this.propietario.num_partida_registral = "";
      }
    },
    tipo_docu_identidad_val(newValue) {
      if (newValue == null) {
        this.propietario.tipo_docu_identidad_id = "";
        return false;
      }
      this.propietario.tipo_docu_identidad_id = newValue.id;
      this.label.num_docu = "Número de " + newValue.text + " del propietario";

      var input_num_docu = $(this.$refs.num_docu);
      if (newValue.id == 1) {
        input_num_docu.attr("maxlength", "8");
        input_num_docu.mask("00000000");
        this.propietario.num_docu = this.propietario.num_docu.substr(0, 8);
      } else if (newValue.id == 2) {
        input_num_docu.attr("maxlength", "11");
        input_num_docu.mask("00000000000");
        this.propietario.num_docu = this.propietario.num_docu.substr(0, 11);
      } else if (newValue.id == 3) {
        input_num_docu.attr("maxlength", "12");
        input_num_docu.mask("000000000000");
      } else if (newValue.id == 4) {
        input_num_docu.attr("maxlength", "12");
        input_num_docu.mask("000000000000");
      }
    },
    tipo_persona_contacto_val(newValue) {
      if (newValue == null) {
        this.propietario.tipo_persona_contacto = "";
        return false;
      }
      this.propietario.tipo_persona_contacto = newValue.id;
      if (newValue.id == 2) {
        this.propietario.contacto_nombre = "";
      }
    },
  },
});

function validar_formulario() {
  if (this.propietario.tipo_persona_id.length == 0) {
    alertify.error("Seleccione un tipo de persona", 5);
    $(this.$refs.tipo_persona_id).focus();
    return false;
  }
  if (this.propietario.nombre.length == 0) {
    alertify.error("Ingrese un nombre", 5);
    $(this.$refs.nombre).focus();
    return false;
  }
  if (this.propietario.tipo_docu_identidad_id.length == 0) {
    alertify.error("Seleccione un tipo de documento", 5);
    $(this.$refs.tipo_docu_identidad_id).focus();
    return false;
  }
  if (
    this.propietario.tipo_docu_identidad_id == 1 &&
    this.propietario.num_docu.length != 8
  ) {
    alertify.error(
      "El número de DNI debe tener 8 dígitos, no " +
        this.propietario.num_docu.length +
        " dígitos",
      5
    );
    $(this.$refs.num_docu).focus();
    return false;
  }
  if (
    this.propietario.tipo_docu_identidad_id == 3 &&
    this.propietario.num_docu.length != 12
  ) {
    alertify.error(
      "El número de Pasaporte debe tener 12 dígitos, no " +
        this.propietario.num_docu.length +
        " dígitos",
      5
    );
    $(this.$refs.num_docu).focus();
    return false;
  }
  if (
    this.propietario.tipo_docu_identidad_id == 4 &&
    this.propietario.num_docu.length != 12
  ) {
    alertify.error(
      "El número de Carnet de Ext. debe tener 12 dígitos, no " +
        this.propietario.num_docu.length +
        " dígitos",
      5
    );
    $(this.$refs.num_docu).focus();
    return false;
  }
  if (this.propietario.num_ruc.length != 11) {
    alertify.error(
      "El número de RUC debe tener 11 dígitos, no " +
        this.propietario.num_ruc.length +
        " dígitos",
      5
    );
    $(this.$refs.num_ruc).focus();
    return false;
  }
  if (this.propietario.direccion.length < 10) {
    alertify.error("Ingrese el dirección completa del propietario", 5);
    $(this.$refs.direccion).focus();
    return false;
  }
  if (
    parseInt(this.propietario.tipo_persona_id) == 2 &&
    this.propietario.representante_legal.length == 0
  ) {
    alertify.error("Ingrese el representante legal", 5);
    $(this.$refs.representante_legal).focus();
    return false;
  }
  if (
    parseInt(this.propietario.tipo_persona_id) == 2 &&
    this.propietario.num_partida_registral.length == 0
  ) {
    alertify.error(
      "Ingrese el número de la Partida Registral de la empresa",
      5
    );
    $(this.$refs.num_partida_registral).focus();
    return false;
  }
  if (this.propietario.tipo_persona_contacto.length == 0) {
    alertify.error("Seleccione el tipo de persona contacto", 5);
    $(this.$refs.tipo_persona_contacto).focus();
    return false;
  }

  if (
    parseInt(this.propietario.tipo_persona_contacto) == 2 &&
    this.propietario.contacto_nombre.length == 0
  ) {
    alertify.error("Ingrese el nombre del contacto", 5);
    $(this.$refs.contacto_nombre).focus();
    return false;
  }
  if (this.propietario.contacto_telefono.length < 8) {
    alertify.error("Ingrese el número telefónico del contaco", 5);
    $(this.$refs.contacto_telefono).focus();
    return false;
  }
  if (
    this.propietario.contacto_email.length > 0 &&
    !validar_email_valido(this.propietario.contacto_email)
  ) {
    alertify.error("El formato del correo electrónico es incorrecto", 5);
    $(this.$refs.contacto_email).focus();
    return false;
  }

  if (this.propietario.id.length == 0) {
    this.registrar_propietario();
  } else {
    this.modificar_propietario();
  }
}

function registrar_propietario() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = this.propietario;
  data.action = "registrar_propietario";
  me.loader = true;
  axios({
    method: "post",
    url: url,
    data: data,
  })
    .then(function (response) {
      if (response.data.status == 200) {
        me.propietarios.push(response.data.result);
        alertify.success(response.data.message, 5);
        me.resetear_form_propietario();
        $("#component-modal-registro-propietario").modal("hide");
      } else {
        alertify.error(response.data.message, 5);
      }

      me.loader = false;
    })
    .catch((error) => {
      me.loader = false;
    });
}

function modificar_propietario() {
  //loading(true);
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = this.propietario;
  data.action = "modificar_propietario";
  me.loader = true;
  axios({
    method: "post",
    url: url,
    data: data,
  })
    .then(function (response) {
      if (response.data.status == 200) {
        const index = me.propietarios
          .map((item) => item.id)
          .indexOf(response.data.result.id);

        me.propietarios[index].tipo_persona_id =
          response.data.result.tipo_persona_id;
        me.propietarios[index].tipo_docu_identidad_id =
          response.data.result.tipo_docu_identidad_id;
        me.propietarios[index].num_docu = response.data.result.num_docu;
        me.propietarios[index].num_ruc = response.data.result.num_ruc;
        me.propietarios[index].nombre = response.data.result.nombre;
        me.propietarios[index].direccion = response.data.result.direccion;
        me.propietarios[index].representante_legal =
          response.data.result.representante_legal;
        me.propietarios[index].num_partida_registral =
          response.data.result.num_partida_registral;
        me.propietarios[index].tipo_persona_contacto =
          response.data.result.tipo_persona_contacto;
        me.propietarios[index].contacto_nombre =
          response.data.result.contacto_nombre;
        me.propietarios[index].contacto_telefono =
          response.data.result.contacto_telefono;
        me.propietarios[index].contacto_email =
          response.data.result.contacto_email;

        alertify.success(response.data.message, 5);
        me.resetear_form_propietario();
        $("#component-modal-registro-propietario").modal("hide");
      } else {
        alertify.error(response.data.message, 5);
      }
      me.loader = false;
    })
    .catch((error) => {
      me.loader = false;
    });
}

function validar_email_valido(email) {
  var regex =
    /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return regex.test(email);
}

function show_modal_propietario(data) {
  this.title = data.title;
  this.resetear_form_propietario();
}

function resetear_form_propietario() {
  this.tipo_persona_val = null;
  this.tipo_docu_identidad_val = null;
  this.tipo_persona_contacto_val = null;

  this.propietario.id = "";
  this.propietario.tipo_persona_id = "";
  this.propietario.tipo_docu_identidad_id = "";
  this.propietario.num_docu = "";
  this.propietario.num_ruc = "";
  this.propietario.nombre = "";
  this.propietario.direccion = "";
  this.propietario.representante_legal = "";
  this.propietario.num_partida_registral = "";
  this.propietario.tipo_persona_contacto = "";
  this.propietario.contacto_nombre = "";
  this.propietario.contacto_telefono = "";
  this.propietario.contacto_email = "";
  this.propietario.usuario_id = "";
}

function show_modal_propietario(data) {
  this.title = data.title;
  this.resetear_form_propietario();
}

function obtener_propietario(data) {
  this.title = data.title;
  this.propietario = data.propietario;

  this.tipo_persona_val = this.tipo_persona.find(
    (item) => item.id == this.propietario.tipo_persona_id
  );
  this.tipo_docu_identidad_val = this.tipo_docu_identidad.find(
    (item) => item.id == this.propietario.tipo_docu_identidad_id
  );

  if (this.propietario.contacto_nombre == this.propietario.nombre) {
    this.tipo_persona_contacto_val = this.tipo_persona_contacto.find(
      (item) => item.id == 1
    );
    this.propietario.tipo_persona_contacto = 1;
  } else {
    this.tipo_persona_contacto_val = this.tipo_persona_contacto.find(
      (item) => item.id == 2
    );
    this.propietario.tipo_persona_contacto = 2;
  }
}
