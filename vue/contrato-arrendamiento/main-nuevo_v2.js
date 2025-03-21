Vue.directive("numeral", {
  bind: function (el, binding) {
    console.log(el.value, binding.value);

    if (binding.value != undefined) {
      const format = binding.value || "0,0.00";
      const numeralConfig = {
        delimiter: ",",
        decimal: ".",
      };
      if (el.value == "") {
        el.value = numeral(0).format(format, numeralConfig);
      }

      el.value = numeral(el.value).format(format, numeralConfig);
      el.addEventListener("blur", function () {
        el.value = numeral(el.value).format(format, numeralConfig);
      });
    }
  },
});

// Vue App
var vm = new Vue({
  el: "#app",
  store,
  components: {
    "v-select": VueSelect.VueSelect,
  },
  data() {
    return {
      empresa_suscribe: [],
      supervisor: [],
      abogado: [],
      aprobador: [],
      cargo_aprobador: [],
      propietarios: [],
      arrendatarios: [],
      arrendamiento: {
        tipo_solicitud: "Contrato de Arrendamiento",
        tipo_contrato_id: 1,
        empresa_suscribe_id: "",
        supervisor_id: "",
        abogado_id: "",
        aprobador_id: "",
        cargo_aprobador_id: "",
        observaciones: "",
        otros_anexos: [],
      },

      empresa_suscribe_val: null,
      supervisor_val: null,
      abogado_val: null,
      aprobador_val: null,
      cargo_aprobador_val: null,

      btn_registrar: true,
      validate_errors: true,
      data_contrato: null,

      loader: false,
    };
  },
  mounted() {
    this.obtener_empresa_suscribe();
    this.obtener_supervisor();
    this.obtener_aprobador();
    this.obtener_cargo_aprobador();
    this.agregar_contrato();
    this.inicializar_funciones();
    this.obtener_abogados();
  },
  methods: {
    inicializar_funciones,
    obtener_empresa_suscribe,
    obtener_supervisor,
    obtener_aprobador,
    obtener_cargo_aprobador,
    show_modal_nuevo_propietario,
    show_modal_nuevo_arredantario,
    agregar_contrato,

    validar_contrato,
    registrar_contrato,

    auditoria_send,
    obtener_abogados,
  },
  computed: {
    contratos() {
      return this.$store.state.contratos.contratos;
    },
  },
  watch: {
    empresa_suscribe_val(newValue) {
      if (newValue == null) {
        this.arrendamiento.empresa_suscribe_id = "";
        return false;
      }
      this.arrendamiento.empresa_suscribe_id = newValue.id;

      $(this.$refs.supervisor).focus();
    },
    supervisor_val(newValue) {
      if (newValue == null) {
        this.arrendamiento.supervisor_id = "";
        return false;
      }
      this.arrendamiento.supervisor_id = newValue.id;
      $(this.$refs.abogado).focus();
    },
    aprobador_val(newValue) {
      if (newValue == null) {
        this.arrendamiento.aprobador_id = "";
        return false;
      }
      this.arrendamiento.aprobador_id = newValue.id;
    },
    cargo_aprobador_val(newValue) {
      if (newValue == null) {
        this.arrendamiento.cargo_aprobador_id = "";
        return false;
      }
      this.arrendamiento.cargo_aprobador_id = newValue.id;
    },
    abogado_val(newValue) {
      if (newValue == null) {
        this.arrendamiento.abogado_id = "";
        return false;
      }
      this.arrendamiento.abogado_id = newValue.id;
      // this.show_modal_nuevo_propietario();
    },
  },
});

function inicializar_funciones() {
  let me = this;

  setTimeout(function () {
    $(me.$refs.empresa_suscribe).focus();
  }, 3500);
}

function agregar_contrato() {
  fetch("./vue/contrato-arrendamiento/data/contrato.php")
    .then((response) => response.json())
    .then((data) => {
      this.$store.dispatch("contratos/ActionAgregarContrato", data);
    })
    .catch((error) => {
      console.error(error);
    });
}

function obtener_empresa_suscribe() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = {
    action: "obtener_empresas",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    me.empresa_suscribe = [];
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.empresa_suscribe.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_supervisor() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = {
    action: "obtener_personal_responsable",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    me.supervisor = [];
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.supervisor.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_abogados() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = {
    action: "obtener_abogados",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    me.abogado = [];
    console.log(response);
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.abogado.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_aprobador() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = {
    action: "obtener_aprobador",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    me.aprobador = [];
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.aprobador.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_cargo_aprobador() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = {
    action: "obtener_cargo_aprobador",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    me.cargo_aprobador = [];
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.cargo_aprobador.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function show_modal_nuevo_propietario() {
  EventBus.$emit("modal-registro-propietario-buscar");
  $("#component-modal-buscar-propietario").modal("show");
}

function show_modal_nuevo_arredantario() {
  EventBus.$emit("modal-registro-arredantario-buscar");
  $("#component-modal-buscar-arredantario").modal("show");
}

function validar_contrato() {
  console.log("this.arrendamiento: ", this.arrendamiento);
  console.log("this.arrendatarios: ", this.arrendatarios);

  console.log("this. this: ", this);
  this.validate_errors = true;
  // if (this.arrendatarios.length == 0) {
  //   // alertify.error(
  //   //   "Seleccione la empresa (arrendatario) que suscribe el contrato",
  //   //   5
  //   // );
  //   alertify.error("Ingrese los campos", 5);
  //   $(this.$refs.empresa_suscribe).focus();
  //   this.validate_errors = false;
  //   return false;
  // }
  // this.arrendamiento.empresa_suscribe_id = this.arrendatarios[0]["id"];

  // if (this.arrendamiento.supervisor_id.length == 0) {
  //   alertify.error("Seleccione el supervisor", 5);
  //   $(this.$refs.supervisor).focus();
  //   this.validate_errors = false;
  //   return false;
  // }

  // if (this.arrendamiento.aprobador_id.length == 0) {
  //   alertify.error("Seleccione el aprobador que suscribe el contrato", 5);
  //   $(this.$refs.aprobador).focus();
  //   this.validate_errors = false;
  //   return false;
  // }

  if (!this.abogado_val) {
    alertify.error("Seleccione un abogado", 5);
    $(this.$refs.abogado.$el).focus();
    this.validate_errors = false;
    return false;
  }

  if (this.propietarios.length == 0) {
    alertify.error("Ingrese un arriendador", 5);
    this.show_modal_nuevo_propietario();
    this.validate_errors = false;
    return false;
  }
  if (this.arrendatarios.length == 0) {
    alertify.error("Ingrese un arriendatario", 5);
    this.show_modal_nuevo_propietario();
    this.validate_errors = false;
    return false;
  }

  if (this.contratos.length == 0) {
    alertify.error("Agregue un contrato", 5);
    this.validate_errors = false;
    return false;
  }

  let index_contrato = 0;
  for (let index = 0; index < this.contratos.length; index++) {
    const element = this.contratos[index];
    var component = "componente_contrato_" + index_contrato;
    var inmueble = element.inmuebles;

    if (inmueble.departamento_id.length == 0) {
      alertify.error(
        "Seleccione un departamento del inmuble #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.departamento.focus();
      this.validate_errors = false;
      return false;
    }
    if (inmueble.provincia_id.length == 0) {
      alertify.error(
        "Seleccione una provincia del inmuble #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.provincia.focus();
      this.validate_errors = false;
      return false;
    }
    if (inmueble.distrito_id.length == 0) {
      alertify.error(
        "Seleccione un distrito del inmuble #" + (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.distrito.focus();
      this.validate_errors = false;
      return false;
    }
    if (inmueble.ubicacion.length == 0) {
      alertify.error(
        "Ingrese una ubicación del inmuble #" + (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.ubicacion.focus();
      this.validate_errors = false;
      return false;
    }
    // if (inmueble.area_arrendada.length == 0) {
    // 	alertify.error("Ingrese una área arrendada del inmuble #"+ (parseInt(index_contrato) +1) , 5);
    // 	console.log(parseInt(index_contrato) +1)
    // 	this.$refs[component][0].$refs.area_arrendada.focus();
    // 	this.validate_errors = false;
    // 	return false;
    // }
    if (inmueble.num_partida_registral.length == 0) {
      alertify.error(
        "Ingrese un n°. partida registral del inmuble #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.num_partida_registral.focus();
      this.validate_errors = false;
      return false;
    }
    if (inmueble.oficina_registral.length == 0) {
      alertify.error(
        "Ingrese una oficina registral  del inmuble #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.oficina_registral.focus();
      this.validate_errors = false;
      return false;
    }

    inmueble.tipo_compromiso_pago_arbitrios = 2;
    var condicion_economica = element.condicion_economica;

    if (condicion_economica.tipo_moneda_id.length == 0) {
      alertify.error(
        "Seleccione una moneda de la condición económica #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.tipo_moneda_id.focus();
      this.validate_errors = false;
      return false;
    }
    if (condicion_economica.pago_renta_id.length == 0) {
      alertify.error(
        "Seleccione un pago de renta de la condición económica #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.pago_renta_id.focus();
      this.validate_errors = false;
      return false;
    }
    if (condicion_economica.monto_renta.length >= 7) {
      alertify.error("Ingrese un monto de mayor cifra (4 dígitos)", 5);
      this.$refs[component][0].$refs.monto_renta.focus();
      this.validate_errors = false;
      return false;
    }
    if (
      parseInt(condicion_economica.pago_renta_id) == 2 &&
      condicion_economica.cuota_variable.length == 0
    ) {
      alertify.error(
        "Ingrese el porcentaje de venta de la condición económica #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.cuota_variable.focus();
      this.validate_errors = false;
      return false;
    }
    if (
      parseInt(condicion_economica.pago_renta_id) == 2 &&
      parseInt(condicion_economica.cuota_variable) == 0
    ) {
      alertify.error(
        "El porcentaje de venta no puede ser 0 de la condición económica #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.cuota_variable.focus();
      this.validate_errors = false;
      return false;
    }
    if (
      parseInt(condicion_economica.pago_renta_id) == 2 &&
      condicion_economica.tipo_venta_id.length == 0
    ) {
      alertify.error(
        "Seleccione el tipo de venta de la condición económica #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.tipo_venta_id.focus();
      this.validate_errors = false;
      return false;
    }

    if (condicion_economica.plazo_id.length == 0) {
      alertify.error(
        "Seleccione el periodo de la condición económica #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.plazo_id.focus();
      this.validate_errors = false;
      return false;
    }
    if (
      parseInt(condicion_economica.plazo_id) == 1 &&
      condicion_economica.cant_meses_contrato.length == 0
    ) {
      alertify.error(
        "Ingrese la vigencia del contrato de la condición económica #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.cant_meses_contrato.focus();
      this.validate_errors = false;
      return false;
    }
    if (condicion_economica.fecha_inicio.length == 0) {
      alertify.error(
        "Seleccione la fecha inicio de la condición económica #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.fecha_inicio.focus();
      this.validate_errors = false;
      return false;
    }
    if (
      parseInt(condicion_economica.plazo_id) == 1 &&
      condicion_economica.fecha_fin.length == 0
    ) {
      alertify.error(
        "Seleccione la fecha fin de la condición económica #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.fecha_fin.focus();
      this.validate_errors = false;
      return false;
    }

    console.log("element.banco_val.id: ", element.banco_val.id);
    console.log("element.num_cuenta_bancaria: ", element.num_cuenta_bancaria);
    propietario_temporal = this.propietarios[0];
    beneficiario_temporal = [
      {
        id: propietario_temporal.id,
        tipo_persona_id: propietario_temporal.tipo_persona_id,
        tipo_docu_identidad_id: propietario_temporal.tipo_docu_identidad_id,
        num_docu: propietario_temporal.num_docu,
        num_ruc: propietario_temporal.num_ruc,
        nombre: propietario_temporal.nombre,
        forma_pago_id: propietario_temporal.forma_pago_id,
        banco_id: element.banco_val.id,
        num_cuenta_bancaria: element.num_cuenta_bancaria,
      },
    ];
    condicion_economica.beneficiarios = beneficiario_temporal;

    if (condicion_economica.beneficiarios.length == 0) {
      alertify.error(
        "Ingrese los beneficiarios de la condición económica #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].show_modal_nuevo_beneficiario();
      this.validate_errors = false;
      return false;
    }

    if (condicion_economica.fecha_suscripcion.length == 0) {
      alertify.error(
        "Seleccione una fecha de suscripción de la condición económica #" +
          (parseInt(index_contrato) + 1),
        5
      );
      this.$refs[component][0].$refs.fecha_suscripcion.focus();
      this.validate_errors = false;
      return false;
    }

    index_contrato++;
  }

  if (this.validate_errors) {
    this.registrar_contrato();
  }
}
// function validar_contrato(){
// 	this.registrar_contrato();
// }

function registrar_contrato() {
  let me = this;
  let url = "sys/router/contratos/index.php";

  var data = new FormData($(this.$refs.form_contrato_nuevo_arrendamiento)[0]);
  data.append("action", "contrato_arrendamiento/registrar");
  data.append("tipo_contrato_id", this.arrendamiento.tipo_contrato_id);
  data.append("empresa_suscribe_id", this.arrendamiento.empresa_suscribe_id);
  data.append("supervisor_id", this.arrendamiento.supervisor_id);
  data.append("aprobador_id", this.arrendamiento.aprobador_id);
  data.append("cargo_aprobador_id", this.arrendamiento.cargo_aprobador_id);
  data.append("abogado_id", this.arrendamiento.abogado_id);
  data.append("observaciones", this.arrendamiento.observaciones);
  data.append("propietarios", JSON.stringify(this.propietarios));
  data.append("contratos", JSON.stringify(this.contratos));
  data.append("cant_contratos", this.contratos.length);
  data.append("otros_anexos", JSON.stringify(this.arrendamiento.otros_anexos));

  var data_auditoria = Object.fromEntries(
    Array.from(data.keys()).map((key) => [
      key,
      data.getAll(key).length > 1 ? data.getAll(key) : data.get(key),
    ])
  );

  this.auditoria_send({ proceso: "guardar_contrato", data: data_auditoria });
  this.loader = true;
  this.btn_registrar = false;

  axios({
    method: "POST",
    url: url,
    data: data,
  })
    .then(function (response) {
      me.loader = false;
      me.btn_registrar = true;
      me.auditoria_send({
        proceso: "respuesta_guardar_contrato",
        data: response.data,
      });
      console.log(response);
      // if (response.data.status == 200) {
      //   swal(
      //     {
      //       title: "Registro exitoso",
      //       text: response.data.message,
      //       html: true,
      //       type: "success",
      //       timer: 6000,
      //       closeOnConfirm: false,
      //       showCancelButton: false,
      //     },
      //     function (isConfirm) {
      //       window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
      //     }
      //   );
      //   setTimeout(function () {
      //     window.location.href = "?sec_id=contrato&sub_sec_id=solicitud";
      //   }, 5000);
      // } else {
      //   swal({
      //     title: "Error al guardar Solicitud de Arrendamiento",
      //     text: response.data.message,
      //     html: true,
      //     type: "error",
      //     timer: 6000,
      //     closeOnConfirm: true,
      //     showCancelButton: false,
      //   });
      // }
    })
    .catch(function (error) {
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
  if (!data) {
    data = {};
  }
  if (!data.proceso) {
    data.proceso = "visita";
  }
  if (!data.sec_id) {
    data.sec_id = sec_id;
  }
  if (!data.sub_sec_id) {
    data.sub_sec_id = sub_sec_id;
  }
  if (!data.item_id) {
    data.item_id = item_id;
  }
  if (!data.url) {
    data.url = window.location.href;
  }

  let me = this;
  let url = "sys/sys_auditoria.php";
  axios({
    method: "POST",
    url: url,
    data: {
      opt: "auditoria_send",
      data: data,
    },
  }).then(function (response) {});
}
