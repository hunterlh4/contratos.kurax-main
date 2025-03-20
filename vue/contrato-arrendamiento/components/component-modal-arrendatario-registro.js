//ARRENDATARIO REGISTRO 1B
Vue.component("component-modal-arrendatario-registro", {
  template: `
 
    <div id="component-modal-registro-arrendatario" class="modal" data-keyboard="false">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ title }} </h4>
                </div>
                <div class="modal-body">
                   
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Tipo de Persona:</label>
                                <v-select :disabled="true"  autocomplete ref="tipo_persona_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_persona" :filterable="true" label="text"  v-model='tipo_persona_val'></v-select>
                                
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Nombre / Razón Social del arrendatario:</label>
                                <input ref="nombre" type="text" class="form-control" v-model="arrendatario.nombre">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Tipo de documento de identidad:</label>
                                <v-select :disabled="true" ref="tipo_docu_identidad_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_docu_identidad" :filterable="true" label="text"  v-model='tipo_docu_identidad_val'></v-select>
                            </div>
                        </div>
                        <div class="col-md-12" v-if="arrendatario.tipo_docu_identidad_id != 2">
                            <div class="form-group">
                                <label for="">{{label.num_docu}}:</label>
                                <input ref="num_docu" type="text" class="form-control mask_dni_agente" :maxlength="atributos.maxlength_num_docu" v-model="arrendatario.num_docu">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Número de RUC del arrendatario:</label>
                                <input ref="num_ruc" type="text" class="form-control" maxlength="11" v-model="arrendatario.num_ruc">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="">Domicilio del arrendatario:</label>
                                <input ref="direccion" type="text" class="form-control" v-model="arrendatario.direccion">
                            </div>
                        </div>
                        <div class="col-md-12" v-if="arrendatario.tipo_persona_id == 2">
                            <div class="form-group">
                                <label for="">Representante Legal:</label>
                                <input ref="representante_legal"  type="text" class="form-control" v-model="arrendatario.representante_legal">
                            </div>
                        </div>
                        <div class="col-md-12" v-if="arrendatario.tipo_persona_id == 2">
                            <div class="form-group">
                                <label for="">N° Partida Registral de la empresa:</label>
                                <input ref="num_partida_registral" type="text" class="form-control" v-model="arrendatario.num_partida_registral">
                            </div>
                        </div> 
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
                    <button type="button" class="btn btn-success" @click="validar_formulario">
                        <i class="icon fa fa-plus"></i>
                        {{ arrendatario.id ? 'Guardar cambios' : 'Agregar arrendatario' }}
                    </button>
                    
                </div>
            </div>
        </div>

        <loader :loader="loader" ref="loader"></loader>
    </div>
    `,
  props: ["arrendatarios"],
  components: {
    "v-select": VueSelect.VueSelect,
  },
  data() {
    return {
      //options
      title: "",
      tipo_persona: [{ id: 2, text: "Persona Jurídica" }],
      tipo_docu_identidad: [
        { id: 1, text: "DNI" },
        { id: 2, text: "RUC" },
        // { id: 3, text: "Pasaporte" },
        // { id: 4, text: "Carnet de Extranjeria" },
      ],
      tipo_persona_contacto: [
        { id: 1, text: "El arrendatario es la persona de contacto" },
        { id: 2, text: "El arrendatario no es la persona de contacto" },
      ],
      //options vale
      tipo_persona_val: { id: 2, text: "Persona Jurídica" },
      tipo_docu_identidad_val: { id: 1, text: "DNI" },
      tipo_persona_contacto_val: null,
      //data
      title: "",
      arrendatario: {
        id: "",
        tipo_persona_id: 2,
        tipo_docu_identidad_id: "",
        num_docu: "",
        num_ruc: "",
        nombre: "",
        direccion: "",
        representante_legal: "",
        num_partida_registral: "",
        // tipo_persona_contacto: "",
        // contacto_nombre: "",
        // contacto_telefono: "",
        contacto_email: "",
        usuario_id: "",
      },
      label: {
        num_docu: "Número de DNI del arrendatario",
      },
      atributos: {
        maxlength_num_docu: 8,
      },

      loader: false,
    };
  },
  mounted() {
    EventBus.$on("modal-registro-arrendatario", (data) => {
      this.show_modal_propietario(data);
    });
    EventBus.$on("modal-modificar-arrendatario", (data) => {
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
    registrar_arrendatario,
    resetear_form_propietario,
    obtener_propietario,
    modificar_propietario,
  },
  watch: {
    tipo_persona_val(newValue) {
      if (newValue == null) {
        this.arrendatario.tipo_persona_id = "";
        this.arrendatario.representante_legal = "";
        this.arrendatario.num_partida_registral = "";
        return false;
      }
      this.arrendatario.tipo_persona_id = newValue.id;
      if (newValue.id == 1) {
        this.arrendatario.representante_legal = "";
        this.arrendatario.num_partida_registral = "";
      }
    },
    tipo_docu_identidad_val(newValue) {
      if (newValue == null) {
        this.arrendatario.tipo_docu_identidad_id = "";
        return false;
      }
      this.arrendatario.tipo_docu_identidad_id = newValue.id;
      this.label.num_docu = "Número de " + newValue.text + " del arrendatario";

      var input_num_docu = $(this.$refs.num_docu);
      if (newValue.id == 1) {
        input_num_docu.attr("maxlength", "8");
        input_num_docu.mask("00000000");
        this.arrendatario.num_docu = this.arrendatario.num_docu.substr(0, 8);
      } else if (newValue.id == 2) {
        input_num_docu.attr("maxlength", "11");
        input_num_docu.mask("00000000000");
        this.arrendatario.num_docu = this.arrendatario.num_docu.substr(0, 11);
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
        this.arrendatario.tipo_persona_contacto = "";
        return false;
      }
      this.arrendatario.tipo_persona_contacto = newValue.id;
      if (newValue.id == 2) {
        this.arrendatario.contacto_nombre = "";
      }
    },
  },
});

function validar_formulario() {
  if (this.arrendatario.tipo_persona_id.length == 0) {
    alertify.error("Seleccione un tipo de persona", 5);
    $(this.$refs.tipo_persona_id).focus();
    return false;
  }
  if (this.arrendatario.nombre.length == 0) {
    alertify.error("Ingrese un nombre", 5);
    $(this.$refs.nombre).focus();
    return false;
  }
  if (this.arrendatario.tipo_docu_identidad_id.length == 0) {
    alertify.error("Seleccione un tipo de documento", 5);
    $(this.$refs.tipo_docu_identidad_id).focus();
    return false;
  }
  if (
    this.arrendatario.tipo_docu_identidad_id == 1 &&
    this.arrendatario.num_docu.length != 8
  ) {
    alertify.error(
      "El número de DNI debe tener 8 dígitos, no " +
        this.arrendatario.num_docu.length +
        " dígitos",
      5
    );
    $(this.$refs.num_docu).focus();
    return false;
  }
  if (
    this.arrendatario.tipo_docu_identidad_id == 3 &&
    this.arrendatario.num_docu.length != 12
  ) {
    alertify.error(
      "El número de Pasaporte debe tener 12 dígitos, no " +
        this.arrendatario.num_docu.length +
        " dígitos",
      5
    );
    $(this.$refs.num_docu).focus();
    return false;
  }

  if (
    this.arrendatario.tipo_docu_identidad_id == 4 &&
    this.arrendatario.num_docu.length != 12
  ) {
    alertify.error(
      "El número de Carnet de Ext. debe tener 12 dígitos, no " +
        this.arrendatario.num_docu.length +
        " dígitos",
      5
    );
    $(this.$refs.num_docu).focus();
    return false;
  }
  if (this.arrendatario.num_ruc.length != 11) {
    alertify.error(
      "El número de RUC debe tener 11 dígitos, no " +
        this.arrendatario.num_ruc.length +
        " dígitos",
      5
    );
    $(this.$refs.num_ruc).focus();
    return false;
  }

  if (this.arrendatario.direccion.length < 10) {
    alertify.error("Ingrese el dirección completa del arrendatario", 5);
    $(this.$refs.direccion).focus();
    return false;
  }
  if (
    parseInt(this.arrendatario.tipo_persona_id) == 2 &&
    this.arrendatario.representante_legal.length == 0
  ) {
    alertify.error("Ingrese el representante legal", 5);
    $(this.$refs.representante_legal).focus();
    return false;
  }

  if (
    parseInt(this.arrendatario.tipo_persona_id) === 2 &&
    (this.arrendatario.num_partida_registral == null ||
      this.arrendatario.num_partida_registral.length === 0)
  ) {
    alertify.error(
      "Ingrese el número de la Partida Registral de la empresa",
      5
    );
    this.$nextTick(() => {
      this.$refs.num_partida_registral?.focus();
    });
    return false;
  }

  console.log("llego 5");

  if (this.arrendatario.id.length == 0) {
    console.log("funcion registrar");
    this.registrar_arrendatario();
  } else {
    console.log("funcion modificar");
    this.modificar_propietario();
  }
}

function registrar_arrendatario() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  // let data = this.arrendatario;
  let data = { ...this.arrendatario, action: "registrar_arrendatario" };
  // data.action = "registrar_arrendatario";
  me.loader = true;

  console.log("Datos enviados para registro:", JSON.stringify(data, null, 2));

  // axios({
  //   method: "post",
  //   url: url,
  //   data: data,
  // })
  axios
    .post(url, data)
    .then(function (response) {
      console.log("Respuesta del servidor:", response.data);
      if (response.data.status == 200) {
        me.arrendatarios.push(response.data.message, 5);
        me.resetear_form_propietario();
        "#component-modal-registro-arrendatario".modal("hide");
      } else {
        alertify.error(response.data.message, 5);
      }
      me.loader = false;
    })
    .catch((error) => {
      me.loader = false;
    });
}

function registrar_propietario() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = this.arrendatario;
  data.action = "registrar_propietario";
  me.loader = true;
  axios({
    method: "post",
    url: url,
    data: data,
  })
    .then(function (response) {
      console.log(response);
      if (response.data.status == 200) {
        me.arrendatarios.push(response.data.result);
        alertify.success(response.data.message, 5);
        me.resetear_form_propietario();
        $("#component-modal-registro-arrendatario").modal("hide");
      } else {
        alertify.error(response.data.message, 5);
      }

      me.loader = false;
    })
    .catch((error) => {
      me.loader = false;
    });
}

function modificar_propietario_antiguo() {
  //loading(true);
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = this.propietario;
  data.action = "modificar_propietario_prueba";
  me.loader = true;
  axios({
    method: "post",
    url: url,
    data: data,
  })
    .then(function (response) {
      let cleanData = JSON.parse(response.data.replace(/(<([^>]+)>)/gi, "")); // Elimina etiquetas HTML
      console.log("🔄 Respuesta del servidor:", response);
      console.log("✅ Datos parseados correctamente:", cleanData);
      if (response.data.status == 200) {
        console.log("respuesta de servidor", response);
        const index = me.arrendatarios
          .map((item) => item.id)
          .indexOf(response.data.result.id);

        me.arrendatarios[index].tipo_persona_id =
          response.data.result.tipo_persona_id;
        me.arrendatarios[index].tipo_docu_identidad_id =
          response.data.result.tipo_docu_identidad_id;
        me.arrendatarios[index].num_docu = response.data.result.num_docu;
        me.arrendatarios[index].num_ruc = response.data.result.num_ruc;
        me.arrendatarios[index].nombre = response.data.result.nombre;
        me.arrendatarios[index].direccion = response.data.result.direccion;
        me.arrendatarios[index].representante_legal =
          response.data.result.representante_legal;
        me.arrendatarios[index].num_partida_registral =
          response.data.result.num_partida_registral;
        me.arrendatarios[index].tipo_persona_contacto =
          response.data.result.tipo_persona_contacto;
        me.arrendatarios[index].contacto_nombre =
          response.data.result.contacto_nombre;
        me.arrendatarios[index].contacto_telefono =
          response.data.result.contacto_telefono;
        me.arrendatarios[index].contacto_email =
          response.data.result.contacto_email;

        alertify.success(response.data.message, 5);
        me.resetear_form_propietario();
        $("#component-modal-registro-arrendatario").modal("hide");
      } else {
        console.log("error normal");
        alertify.error(response.data.message, 5);
      }
      me.loader = false;
    })
    .catch((error) => {
      console.log("error catch");
      me.loader = false;
    });
}

function modificar_propietario() {
  //loading(true);
  let me = this;
  let url = "sys/router/contratos/index.php";
  // let data = this.propietario;
  let data = { ...this.arrendatario };
  data.action = "modificar_arrendatario";
  me.loader = true;
  console.log("📤 Enviando datos al servidor:", data); // 🔍 Ver qué se envía
  axios({
    method: "post",
    url: url,
    data: data,
  })
    .then(function (response) {
      console.log("🔄 Respuesta del servidor:", response);

      if (response.data.status == 200) {
        console.log("respuesta de servidor", response);
        const index = me.arrendatarios
          .map((item) => item.id)
          .indexOf(response.data.result.id);

        me.arrendatarios[index].tipo_persona_id =
          response.data.result.tipo_persona_id;
        me.arrendatarios[index].tipo_docu_identidad_id =
          response.data.result.tipo_docu_identidad_id;
        me.arrendatarios[index].num_docu = response.data.result.num_docu;
        me.arrendatarios[index].num_ruc = response.data.result.num_ruc;
        me.arrendatarios[index].nombre = response.data.result.nombre;
        me.arrendatarios[index].direccion = response.data.result.direccion;
        me.arrendatarios[index].representante_legal =
          response.data.result.representante_legal;
        me.arrendatarios[index].num_partida_registral =
          response.data.result.num_partida_registral;
        me.arrendatarios[index].tipo_persona_contacto =
          response.data.result.tipo_persona_contacto;
        me.arrendatarios[index].contacto_nombre =
          response.data.result.contacto_nombre;
        me.arrendatarios[index].contacto_telefono =
          response.data.result.contacto_telefono;
        me.arrendatarios[index].contacto_email =
          response.data.result.contacto_email;
      } else {
        console.log("⚠️ Error del servidor", response.data.message);
        alertify.error(response.data.message, 5);
      }
      me.loader = false;
    })
    .catch((error) => {
      console.log("error catch");
      me.loader = false;
    });
}

function validar_email_valido(email) {
  var regex =
    /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return regex.test(email);
}

function show_modal_propietario(data) {
  console.log("abrir modal registrar Arrendatario");
  this.title = data.title;
  this.resetear_form_propietario();
}

function resetear_form_propietario() {
  this.tipo_persona_val = { id: 2, text: "Persona Jurídica" };
  this.tipo_docu_identidad_val = { id: 1, text: "DNI" };
  this.tipo_persona_contacto_val = null;
  this.arrendatario.id = "";
  this.arrendatario.tipo_persona_id = 2;
  this.arrendatario.tipo_docu_identidad_id = "";
  this.arrendatario.num_docu = "";
  this.arrendatario.num_ruc = "";
  this.arrendatario.nombre = "";
  this.arrendatario.direccion = "";
  this.arrendatario.representante_legal = "";
  this.arrendatario.num_partida_registral = "";
  this.arrendatario.tipo_persona_contacto = "";
  this.arrendatario.contacto_nombre = "";
  this.arrendatario.contacto_telefono = "";
  this.arrendatario.contacto_email = "";
  this.arrendatario.usuario_id = "";
}

// function show_modal_propietario(data) {
//   this.title = data.title;
//   this.resetear_form_propietario();
// }

function obtener_propietario(data) {
  this.title = data.title;
  this.arrendatario = data.arrendatario;

  this.tipo_persona_val = this.tipo_persona.find((item) => item.id == 2);
  this.tipo_docu_identidad_val = this.tipo_docu_identidad.find(
    (item) => item.id == 1
  );

  if (this.arrendatario.contacto_nombre == this.arrendatario.nombre) {
    this.tipo_persona_contacto_val = this.tipo_persona_contacto.find(
      (item) => item.id == 1
    );
    this.arrendatario.tipo_persona_contacto = 1;
  } else {
    this.tipo_persona_contacto_val = this.tipo_persona_contacto.find(
      (item) => item.id == 2
    );
    this.arrendatario.tipo_persona_contacto = 2;
  }
}
