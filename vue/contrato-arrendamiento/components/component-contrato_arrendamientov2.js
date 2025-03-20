const contrato = {
  template: `
    <section>


    <!-- Inicio Panel Ficha de Condiciones -->
    <div class=" " :ref="'panel_contrato_' + index">
      
                <div class="panel-body mt-1">
                    <!-- Inicio Panel Innmuebles -->
                    <div class="panel"> 
                    <div class="panel-heading">
                        <span class="icon-wrapper">
                            <i class="fa fa-home" aria-hidden="true"></i>
                        </span> 
                        Datos Del Inmueble
                </div>
                <div class="panel-body">
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Departamento:</label>
                            <v-select ref="departamento" placeholder="-- Seleccione --" class="w-100" :options="departamentos" :filterable="true" label="text"  v-model='departamento_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Provincia:</label>
                            <v-select ref="provincia" placeholder="-- Seleccione --" class="w-100" :options="provincias" :filterable="true" label="text"  v-model='provincia_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Distrito:</label>
                            <v-select ref="distrito" placeholder="-- Seleccione --" class="w-100" :options="distritos" :filterable="true" label="text"  v-model='distrito_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Ubicación del Inmueble:</label>
                            <input ref="ubicacion" class="form-control" v-model="contrato.inmuebles.ubicacion" type="text">
                        </div>
                    </div>
                  
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">N° Partida Registral:</label>
                            <input ref="num_partida_registral" class="form-control" v-model="contrato.inmuebles.num_partida_registral"  type="text">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Oficina Registral (Sede):</label>
                            <input ref="oficina_registral" class="form-control" v-model="contrato.inmuebles.oficina_registral" type="text">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">Observaciones (De ser el caso, por ejemplo, cargas):</label>
                            <textarea ref="oficina_registral" class="form-control resize" v-model="contrato.inmuebles.observaciones_mueble" rows="2" type="text"></textarea>
                        </div>
                    </div>

                    
                </div>
                <!-- Inicio seccion Vigencia --> 
                <div class="panel-heading">
                        <span class="icon-wrapper">
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                        </span>  
                        Plazo
                </div>


                <div class="panel-body">
                     
                    <div :class="'col-md-4 ' + (contrato.condicion_economica.plazo_id == 1 ? 'show':'hide')">
                        <div class="form-group">
                            <label for="">Vigencia del Contrato en Meses:</label>
                            <input ref="cant_meses_contrato" v-model="cant_meses_contrato" type="number" class="form-control">
                        </div>
                    </div>

                    <div :class="'col-md-3 ' + ('hide')">
                        <div class="form-group">
                            <label for="">Vigencia del Contrato (Solo Lectura):</label>
                            <input ref="vigencia_contrato_lectura" disabled v-model="contrato.condicion_economica.vigencia_contrato_lectura" type="text" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Fecha de Inicio:</label>
                            <input ref="fecha_inicio" v-model="fecha_inicio_val" type="text" class="form-control text-center flatpickr-nuevo">
                        </div>
                    </div>

                    <div :class="'col-md-4 ' + (contrato.condicion_economica.plazo_id == 1 ? 'show':'hide')">
                        <div class="form-group">
                            <label for="">Fecha de Fin:</label>
                            <input ref="fecha_fin" v-model="fecha_fin_val"  type="text" class="form-control text-center flatpickr-nuevo">
                        </div>
                    </div>
                </div>


                 
                <div class="panel-heading">
                        <span class="icon-wrapper">
                            <i class="fa fa-usd" aria-hidden="true"></i>
                        </span> 
                        Renta
                </div>


                <div class="panel-body">
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Moneda del Contrato:</label>
                            <v-select ref="tipo_moneda_id" placeholder="-- Seleccione --" class="w-100" :options="monedas" :filterable="true" label="text"  v-model='moneda_val'></v-select>
                        </div>
                    </div> 

                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="">Monto en números:</label>
                            <input   ref="monto_renta" v-model="contrato.condicion_economica.monto_renta" class="form-control text-right" type="text">
                        </div>
                    </div>
                      <!-- <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Monto en letras:</label>
                            <input ref="monto_renta" disabled v-model="contrato.condicion_economica.monto_renta" class="form-control text-right" type="text">
                        </div> 
                    </div>-->
                    <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="monto">Nombre del Banco:</label>
                                  <v-select ref="banco_id" placeholder="-- Seleccione --" class="w-100" :options="bancos" :filterable="true" label="text"  v-model='contrato.banco_val'></v-select>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-12">
                            <div class="form-group">
                                <label for="num_cuenta_bancaria">N° de cuenta bancaria:</label>
                                  <input class="form-control" v-model="contrato.num_cuenta_bancaria" type="number"> 
                            </div>
                        </div> 
                </div>  
 

                <div class="panel-heading">
                        <span class="icon-wrapper">
                          <i class="fa fa-file-text-o" aria-hidden="true"></i>
                        </span> 
                        De la condición, del destino y de las mejoras introducidas al inmueble
                </div>


                <div class="panel-body">
                      <div class="col-md-12 col-sm-12">
                            <div class="form-group">
                                <label for="num_cuenta_bancaria">El inmueble arrendado será destinado a:</label>
                                  <textarea class="form-control" v-model="contrato.inmueble_destinado" type="text" rows="2"></textarea> 
                            </div>
                        </div> 
                </div>


                <div class="panel-heading">
                        <span class="icon-wrapper">
                       <i class="fa fa-check-square-o" aria-hidden="true"></i>
                        </span> 
                        Suscripción
                </div>


                <div class="panel-body">
                      <div class="col-md-4 col-sm-12">
                           <div class="form-group">
                <label for="fecha_suscripcion" class="form-label">Fecha de Suscripción del Contrato</label>
                  <div class="input-group align-items-center">
                    
                        <input 
                            ref="fecha_suscripcion"
                            v-model="contrato.condicion_economica.fecha_suscripcion"
                            type="text" 
                            class="form-control flatpickr-nuevo"
                            id="fecha_suscripcion"
                            placeholder="Selecciona una fecha"
                        >
                        <span class="input-group-text">
                            <i class="fa fa-calendar"></i>
                        </span>
                   </div>
</div>
                        </div> 
                </div>
            </div>  
            <!-- Final Panel Fecha Suscripcion -->
 
        </div>
        
    </div>
    <!-- Final Panel Ficha de Condiciones -->

    
    <component-modal-beneficiarios :propietarios="propietarios" :index="index" :beneficiarios="contrato.condicion_economica.beneficiarios"></component-modal-beneficiarios>
    <component-modal-beneficiario-registro :propietarios="propietarios" :index="index" :beneficiarios="contrato.condicion_economica.beneficiarios"></component-modal-beneficiario-registro> 
    <component-modal-anexo :index="index" :otros_anexos="contrato.otros_anexos"></component-modal-anexo>
    <component-modal-anexo-registro :index="index" :otros_anexos="contrato.otros_anexos"></component-modal-anexo-registro>
    <link rel="stylesheet" href="css/contrato/index.css">
    </section>
    `,
  components: {
    "v-select": VueSelect.VueSelect,
  },
  props: ["contrato", "index", "arrendamiento", "propietarios", "arrendatarios"],
  data() {
    return {
      departamentos: [],
      provincias: [],
      distritos: [],
      compromiso_pago_servicios: [],
      compromiso_pago_arbitrios: [],
      monedas: [],
      pago_renta: [],
      tipo_venta: [],
      igv_venta: [],
      tipo_adelanto: [],
      tipo_impuesto_renta: [],
      carta_instruccion: [],
      periodo_gracia: [],
      tipo_periodo: [],
      tipo_incremento: [],
      tipo_inflacion: [],
      tipo_cuota_extraordinaria: [],

      departamento_val: null,
      provincia_val: null,
      distrito_val: null,

      moneda_val: null,
      pago_renta_val: null,
      tipo_venta_val: null,

      tipo_periodo_val: { id: "1", nombre: "Periodo Definido" },
      cant_meses_contrato: "",
      fecha_inicio_val: "",
      fecha_fin_val: "",
      tipo_incremento_val: null,
      tipo_inflacion_val: null,
      tipo_cuota_extraordinaria_val: null,

      label_form: {
        monto_o_porcentaje_luz: "",
        monto_o_porcentaje_agua: "",
      },
      bancos: [],
    };
  },
  created() {
    this.obtener_departamentos();
    this.obtener_compromiso_pago_servicios();
    this.obtener_compromiso_pago_arbitrios();
    this.obtener_monedas();
    this.obtener_pago_renta();
    this.obtener_tipo_venta();
    this.obtener_igv_renta();
    this.obtener_tipo_adelanto();
    this.obtener_impuesto_renta();
    this.obtener_carta_instruccion();
    this.obtener_periodo_gracia();
    this.obtener_tipo_periodo();
    this.obtener_tipo_incremento();
    this.obtener_tipo_inflacion();
    this.obtener_tipo_cuota_extraordinaria();
    this.obtener_autodetraccion();
    this.obtener_bancos();
  },
  mounted() {
    this.inicializar_funciones();
    flatpickr(".flatpickr-nuevo", {
      dateFormat: "d-m-Y",
      locale: "es",
    });
  },
  methods: {
    agregar_contrato() {
      fetch("./vue/contrato-arrendamiento/data/contrato.php")
        .then((response) => response.json())
        .then((data) => {
          this.$store.dispatch("contratos/ActionAgregarContrato", data);
          alertify.success("Se ha agregado una nueva ficha de contrato", 5);
        })
        .catch((error) => {
          console.error(error);
        });
    },
    eliminar_contrato(index) {
      this.$store.dispatch("contratos/ActionEliminarContrato", index);
      alertify.success("Se ha eliminado la ficha de contrato seleccionada", 5);
    },
    inicializar_funciones,

    obtener_departamentos,
    obtener_provincias,
    obtener_distritos,
    obtener_compromiso_pago_servicios,
    obtener_compromiso_pago_arbitrios,
    obtener_monedas,
    obtener_pago_renta,
    obtener_tipo_venta,
    obtener_igv_renta,
    obtener_tipo_adelanto,
    obtener_impuesto_renta,
    obtener_carta_instruccion,
    obtener_periodo_gracia,
    obtener_tipo_periodo,
    obtener_tipo_incremento,
    obtener_tipo_inflacion,
    obtener_tipo_cuota_extraordinaria,
    obtener_autodetraccion,
    show_modal_modificar_adelantos,
    calcular_monto_segun_impuesto,
    calcular_fecha_fin_vigencia,
    calcular_vigencia_anios_y_meses,
    calcular_meses,
    //suministros
    //agregar_nuevo_suministro_agua,
    eliminar_suministro_agua,

    //agregar_nuevo_suministro_luz,
    eliminar_suministro_luz,

    show_modal_adelantos,
    //incrementos
    show_modal_nuevo_incremento,
    show_modal_modificar_incremento,
    eliminar_incremento,
    //inflaciones
    show_modal_nueva_inflacion,
    show_modal_modificar_inflacion,
    eliminar_inflacion,
    //cuotas extraordinarias
    show_modal_nueva_cuota_extraordinaria,
    show_modal_modificar_cuota_extraordinaria,
    eliminar_cuota_extraordinaria,
    //beneficario
    show_modal_nuevo_beneficiario,
    show_modal_modificar_beneficiario,
    eliminar_beneficiario,
    //responsable ir
    show_modal_nuevo_responsable_ir,
    show_modal_modificar_responsable_ir,
    eliminar_responsable_ir,

    show_modal_anexo,
    borrar_anexo,
    cargar_otros_anexos,
    obtener_bancos,
  },
  computed: {
    contratos() {
      return this.$store.state.contratos.contratos;
    },
  },
  watch: {
    departamento_val(newValue) {
      if (newValue == null) {
        this.contrato.inmuebles.departamento_id = "";
        this.contrato.inmuebles.provincia_id = "";
        this.contrato.inmuebles.distrito_id = "";
        this.contrato.inmuebles.ubigeo_id = "";
        this.provincias = [];
        this.distritos = [];
        this.provincia_val = null;
        this.distrito_val = null;
        return false;
      }
      this.contrato.inmuebles.departamento_id = newValue.id;
      this.obtener_provincias();
      this.contrato.inmuebles.provincia_id = "";
      this.contrato.inmuebles.distrito_id = "";
      this.contrato.inmuebles.ubigeo_id = "";
      this.$refs.provincia.focus();
    },
    provincia_val(newValue) {
      if (newValue == null) {
        this.contrato.inmuebles.provincia_id = "";
        this.contrato.inmuebles.distrito_id = "";
        this.contrato.inmuebles.ubigeo_id = "";
        this.distritos = [];
        this.distrito_val = null;
        return false;
      }

      this.contrato.inmuebles.provincia_id = newValue.id;
      this.obtener_distritos();
      this.contrato.inmuebles.distrito_id = "";
      this.contrato.inmuebles.ubigeo_id = "";
      this.$refs.distrito.focus();
    },
    distrito_val(newValue) {
      if (newValue == null) {
        this.contrato.inmuebles.ubigeo_id = "";
        return false;
      }
      this.contrato.inmuebles.distrito_id = newValue.id;
      this.contrato.inmuebles.ubigeo_id =
        this.contrato.inmuebles.departamento_id + this.contrato.inmuebles.provincia_id + this.contrato.inmuebles.distrito_id;

      this.$refs.ubicacion.focus();
    },

    compromiso_pago_agua_val(newValue) {
      if (newValue == null) {
        this.contrato.inmuebles.tipo_compromiso_pago_agua = "";
        this.contrato.inmuebles.monto_o_porcentaje_agua = "";
        return false;
      }
      this.contrato.inmuebles.tipo_compromiso_pago_agua = newValue.id;
      if (
        this.contrato.inmuebles.tipo_compromiso_pago_agua == 1 ||
        this.contrato.inmuebles.tipo_compromiso_pago_agua == 2 ||
        this.contrato.inmuebles.tipo_compromiso_pago_agua == 6 ||
        this.contrato.inmuebles.tipo_compromiso_pago_agua == 7
      ) {
        if (this.contrato.inmuebles.tipo_compromiso_pago_agua == 1) {
          this.label_form.monto_o_porcentaje_agua = "(%) del recibo de agua";
        } else if (this.contrato.inmuebles.tipo_compromiso_pago_agua == 2) {
          this.label_form.monto_o_porcentaje_agua = "Monto fijo del servicio de agua";
        } else if (this.contrato.inmuebles.tipo_compromiso_pago_agua == 6) {
          this.label_form.monto_o_porcentaje_agua = "Monto base del servicio de agua";
        } else if (this.contrato.inmuebles.tipo_compromiso_pago_agua == 7) {
          this.label_form.monto_o_porcentaje_agua = "Monto a facturar del servicio de agua";
        }
        let me = this;
        setTimeout(function () {
          $(me.$refs.monto_o_porcentaje_agua).on({
            focus: function (event) {
              $(event.target).select();
            },
            blur: function (event) {
              var tipo_compromiso_pago_agua = me.contrato.inmuebles.tipo_compromiso_pago_agua;
              if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                if (parseInt(tipo_compromiso_pago_agua) != 1) {
                  $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
                  $(event.target).val(function (index, value) {
                    var new_value = value
                      .replace(/\D/g, "")
                      .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                      .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                    me.contrato.inmuebles.monto_o_porcentaje_agua = new_value;
                    return new_value;
                  });
                }
              } else {
                if (parseInt(tipo_compromiso_pago_agua) != 1) {
                  me.contrato.inmuebles.monto_o_porcentaje_agua = "0.00";
                  $(event.target).val("0.00");
                } else {
                  me.contrato.inmuebles.monto_o_porcentaje_agua = "0";
                  $(event.target).val("0");
                }
              }
            },
          });
          $(me.$refs.monto_o_porcentaje_agua).unmask();
          if (me.contrato.inmuebles.tipo_compromiso_pago_agua == 1) {
            $(me.$refs.monto_o_porcentaje_agua).mask("00");
          }
          $(me.$refs.monto_o_porcentaje_agua).focus();
        }, 100);
      } else {
        this.contrato.inmuebles.monto_o_porcentaje_agua = "";
        this.label_form.monto_o_porcentaje_agua = "";
      }
    },
    compromiso_pago_luz_val(newValue) {
      if (newValue == null) {
        this.contrato.inmuebles.tipo_compromiso_pago_luz = "";
        this.contrato.inmuebles.monto_o_porcentaje_luz = "";
        return false;
      }
      this.contrato.inmuebles.tipo_compromiso_pago_luz = newValue.id;
      if (
        this.contrato.inmuebles.tipo_compromiso_pago_luz == 1 ||
        this.contrato.inmuebles.tipo_compromiso_pago_luz == 2 ||
        this.contrato.inmuebles.tipo_compromiso_pago_luz == 6 ||
        this.contrato.inmuebles.tipo_compromiso_pago_luz == 7
      ) {
        if (this.contrato.inmuebles.tipo_compromiso_pago_luz == 1) {
          this.label_form.monto_o_porcentaje_luz = "(%) del recibo de luz";
        } else if (this.contrato.inmuebles.tipo_compromiso_pago_luz == 2) {
          this.label_form.monto_o_porcentaje_luz = "Monto fijo del servicio de luz";
        } else if (this.contrato.inmuebles.tipo_compromiso_pago_luz == 6) {
          this.label_form.monto_o_porcentaje_luz = "Monto base del servicio de luz";
        } else if (this.contrato.inmuebles.tipo_compromiso_pago_luz == 7) {
          this.label_form.monto_o_porcentaje_luz = "Monto a facturar del servicio de luz";
        }
        let me = this;
        setTimeout(function () {
          $(me.$refs.monto_o_porcentaje_luz).on({
            focus: function (event) {
              $(event.target).select();
            },
            blur: function (event) {
              var tipo_compromiso_pago_luz = me.contrato.inmuebles.tipo_compromiso_pago_luz;
              if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                if (parseInt(tipo_compromiso_pago_luz) != 1) {
                  $(event.target).val(parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2));
                  $(event.target).val(function (index, value) {
                    var new_value = value
                      .replace(/\D/g, "")
                      .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                      .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                    me.contrato.inmuebles.monto_o_porcentaje_luz = new_value;
                    return new_value;
                  });
                }
              } else {
                if (parseInt(tipo_compromiso_pago_luz) != 1) {
                  me.contrato.inmuebles.monto_o_porcentaje_luz = "0.00";
                  $(event.target).val("0.00");
                } else {
                  me.contrato.inmuebles.monto_o_porcentaje_luz = "0";
                  $(event.target).val("0");
                }
              }
            },
          });
          $(me.$refs.monto_o_porcentaje_luz).unmask();
          if (me.contrato.inmuebles.tipo_compromiso_pago_luz == 1) {
            $(me.$refs.monto_o_porcentaje_luz).mask("00");
          }
          $(me.$refs.monto_o_porcentaje_luz).focus();
        }, 100);
      } else {
        this.contrato.inmuebles.monto_o_porcentaje_luz = "";
        this.label_form.monto_o_porcentaje_luz = "";
      }
    },
    compromiso_pago_arbitrio_val(newValue) {
      if (newValue == null) {
        this.contrato.inmuebles.tipo_compromiso_pago_arbitrios = "";
        this.contrato.inmuebles.porcentaje_pago_arbitrios = "";
        return false;
      }
      this.contrato.inmuebles.tipo_compromiso_pago_arbitrios = newValue.id;

      let me = this;
      setTimeout(function () {
        if (me.contrato.inmuebles.tipo_compromiso_pago_arbitrios == 1) {
          $(me.$refs.porcentaje_pago_arbitrios).mask("00");
        }
        $(me.$refs.porcentaje_pago_arbitrios).focus();
      }, 100);
    },
    moneda_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.tipo_moneda_id = "";
        return false;
      }
      this.contrato.condicion_economica.tipo_moneda_id = newValue.id;

      let me = this;
      setTimeout(function () {
        $(me.$refs.monto_renta).focus();
      }, 100);
      this.calcular_monto_segun_impuesto();
    },
    pago_renta_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.pago_renta_id = "";
        return false;
      }
      this.contrato.condicion_economica.pago_renta_id = newValue.id;

      let me = this;
      setTimeout(function () {
        if (newValue.id == 2) {
          $(me.$refs.monto_renta).focus();

          $(me.$refs.cuota_variable).on({
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

                  me.contrato.inmuebles.cuota_variable = new_value;
                  return new_value;
                });
              } else {
                me.contrato.inmuebles.cuota_variable = "0";
                $(event.target).val("0");
              }
            },
          });
        }
      }, 100);
    },
    tipo_venta_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.tipo_venta_id = "";
        return false;
      }
      this.contrato.condicion_economica.tipo_venta_id = newValue.id;
    },
    igv_renta_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.afectacion_igv_id = "";
        return false;
      }
      this.contrato.condicion_economica.afectacion_igv_id = newValue.id;
    },
    tipo_adelanto_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.tipo_adelanto_id = "";
        return false;
      }
      this.contrato.condicion_economica.tipo_adelanto_id = newValue.id;

      if (newValue.id == 1) {
        this.show_modal_adelantos();
      } else {
        this.contrato.condicion_economica.adelantos = [];
        this.$refs.impuesto_a_la_renta_id.focus();
      }
    },
    tipo_impuesto_renta_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.impuesto_a_la_renta_id = "";
        return false;
      }
      this.contrato.condicion_economica.impuesto_a_la_renta_id = newValue.id;
      this.calcular_monto_segun_impuesto();
    },
    carta_instruccion_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.carta_de_instruccion_id = "";
        return false;
      }
      this.contrato.condicion_economica.carta_de_instruccion_id = newValue.id;
      this.calcular_monto_segun_impuesto();
    },
    periodo_gracia_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.periodo_gracia_id = "";
        return false;
      }
      this.contrato.condicion_economica.periodo_gracia_id = newValue.id;
      let me = this;
      setTimeout(function () {
        if (me.contrato.condicion_economica.periodo_gracia_id == 1) {
          $(me.$refs.periodo_gracia_numero).mask("000");
          $(me.$refs.periodo_gracia_numero).focus();
        }
      }, 100);
    },
    tipo_periodo_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.plazo_id = "";
        return false;
      }
      this.contrato.condicion_economica.plazo_id = newValue.id;
      let me = this;
      setTimeout(function () {
        if (me.contrato.condicion_economica.plazo_id == 1) {
          $(me.$refs.cant_meses_contrato);
        } else if (me.contrato.condicion_economica.plazo_id == 2) {
          $(me.$refs.fecha_inicio).focus();
          me.cant_meses_contrato = "";
          me.fecha_fin_val = "";
        }
      }, 200);
    },
    cant_meses_contrato(newValue) {
      this.contrato.condicion_economica.cant_meses_contrato = newValue;
      this.calcular_vigencia_anios_y_meses();
      this.calcular_fecha_fin_vigencia();
    },
    fecha_inicio_val(newValue) {
      this.calcular_fecha_fin_vigencia();
    },
    fecha_fin_val(newValue) {
      this.calcular_meses();
    },
    tipo_incremento_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.tipo_incremento_id = "";
        return false;
      }
      this.contrato.condicion_economica.tipo_incremento_id = newValue.id;
      if (newValue.id == 1) {
        this.show_modal_nuevo_incremento();
      } else {
        this.contrato.condicion_economica.incrementos = [];
      }
    },
    tipo_inflacion_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.tipo_inflacion_id = "";
        return false;
      }
      this.contrato.condicion_economica.tipo_inflacion_id = newValue.id;
      if (newValue.id == 1) {
        this.show_modal_nueva_inflacion();
      } else {
        this.contrato.condicion_economica.inflaciones = [];
      }
    },
    tipo_cuota_extraordinaria_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.tipo_cuota_extraordinaria_id = "";
        return false;
      }
      this.contrato.condicion_economica.tipo_cuota_extraordinaria_id = newValue.id;
      if (newValue.id == 1) {
        this.show_modal_nueva_cuota_extraordinaria();
      } else {
        this.contrato.condicion_economica.cuotas_extraordinarias = [];
      }
    },
  },
};

Vue.component("component-contrato", contrato);

function show_modal_modificar_adelantos() {
  EventBus.$emit("abrir_modal_adelantos", {});
  $("#component_modal_adelantos_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_adelantos_" + this.index).focus();
}

function inicializar_funciones() {
  let me = this;

  setTimeout(function () {
    $(me.$refs.monto_renta).on({
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

            me.contrato.condicion_economica.monto_renta = new_value;
            return new_value;
          });
        } else {
          me.contrato.condicion_economica.monto_renta = "0.00";
          $(event.target).val("0.00");
        }
      },
    });

    $(me.$refs.garantia_monto).on({
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

            me.contrato.condicion_economica.garantia_monto = new_value;
            return new_value;
          });
        } else {
          me.contrato.condicion_economica.garantia_monto = "0.00";
          $(event.target).val("0.00");
        }
      },
    });

    $(me.$refs.area_arrendada).on({
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
            me.contrato.inmuebles.area_arrendada = new_value;
            return new_value;
          });
        } else {
          me.contrato.inmuebles.area_arrendada = "0.00";
          $(event.target).val("0.00");
        }
      },
    });
  }, 3000);

  setTimeout(function () {
    $(me.$refs.cant_meses_contrato).mask("000");
  }, 5000);
}

function obtener_departamentos() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = {
    action: "obtener_departartamentos",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    me.departamentos = [];
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.departamentos.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_provincias() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.provincias = [];
  this.provincia_val = null;
  let data = {
    action: "obtener_provincias_segun_departamento",
    departamento_id: this.contrato.inmuebles.departamento_id,
  };
  if (data.departamento_id.length == 0) {
    return false;
  }
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.provincias.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_distritos() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.distritos = [];
  this.distrito_val = null;
  let data = {
    action: "obtener_distritos_segun_provincia",
    departamento_id: this.contrato.inmuebles.departamento_id,
    provincia_id: this.contrato.inmuebles.provincia_id,
  };
  if (data.provincia_id.length == 0) {
    return false;
  }
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.distritos.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_compromiso_pago_servicios() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.compromiso_pago_servicios = [];
  let data = {
    action: "obtener_tipo_compromiso_pago",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.compromiso_pago_servicios.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_compromiso_pago_arbitrios() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.compromiso_pago_arbitrios = [];
  let data = {
    action: "obtener_tipo_compromiso_pago_arbitrio",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.compromiso_pago_arbitrios.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_monedas() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.monedas = [];
  let data = {
    action: "obtener_moneda_de_contrato",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.monedas.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_pago_renta() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.pago_renta = [];
  let data = {
    action: "obtener_tipo_pago_renta",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        if (element.id == 1) {
          me.pago_renta_val = {
            id: element.id,
            text: element.nombre,
          };
        }
        me.pago_renta.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_tipo_venta() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_venta = [];
  let data = {
    action: "obtener_tipo_venta",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.tipo_venta.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_igv_renta() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.igv_venta = [];
  let data = {
    action: "obtener_tipo_afectacion_igv",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.igv_venta.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_tipo_adelanto() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_adelanto = [];
  let data = {
    action: "obtener_tipo_adelantos",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.tipo_adelanto.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_impuesto_renta() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_impuesto_renta = [];
  let data = {
    action: "obtener_tipo_impuesto_a_la_renta",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.tipo_impuesto_renta.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_carta_instruccion() {
  this.carta_instruccion = [
    { id: 1, text: "Si" },
    { id: 2, text: "No" },
  ];
}
function obtener_autodetraccion() {
  this.tipo_autodetraccion = [
    { id: 1, text: "Si (CONCAR)" },
    { id: 2, text: "No (SISPAC)" },
  ];
}
function calcular_monto_segun_impuesto() {
  let me = this;
  let url = "sys/router/contratos/index.php";

  let data = {
    action: "calcular_monto_segun_impuesto",
    tipo_moneda_id: this.contrato.condicion_economica.tipo_moneda_id,
    monto_renta: this.contrato.condicion_economica.monto_renta,
    impuesto_a_la_renta_id: this.contrato.condicion_economica.impuesto_a_la_renta_id,
    carta_de_instruccion_id: this.contrato.condicion_economica.carta_de_instruccion_id,
  };

  if (
    data.tipo_moneda_id.length == 0 ||
    data.monto_renta.length == 0 ||
    data.impuesto_a_la_renta_id.length == 0 ||
    data.impuesto_a_la_renta_id == 4 ||
    data.impuesto_a_la_renta_id == 5 ||
    data.carta_de_instruccion_id.length == 0
  ) {
    me.contrato.condicion_economica.view_ir_detalle = false;
    me.contrato.condicion_economica.ir_detalle.renta_neta = "";
    me.contrato.condicion_economica.ir_detalle.renta_bruta = "";
    me.contrato.condicion_economica.ir_detalle.impuesto_a_la_renta = "";
    me.contrato.condicion_economica.ir_detalle.detalle = "";
    return false;
  }

  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      me.contrato.condicion_economica.view_ir_detalle = true;
      const renta_neta = response.data.result.renta_neta;
      const renta_bruta = response.data.result.renta_bruta;
      const impuesto_a_la_renta = response.data.result.impuesto_a_la_renta;
      const detalle = response.data.result.detalle;

      me.contrato.condicion_economica.ir_detalle.renta_neta = renta_neta;
      me.contrato.condicion_economica.ir_detalle.renta_bruta = renta_bruta;
      me.contrato.condicion_economica.ir_detalle.impuesto_a_la_renta = impuesto_a_la_renta;
      me.contrato.condicion_economica.ir_detalle.detalle = detalle;
    } else {
      me.contrato.condicion_economica.view_ir_detalle = false;
      me.contrato.condicion_economica.ir_detalle.renta_neta = "";
      me.contrato.condicion_economica.ir_detalle.renta_bruta = "";
      me.contrato.condicion_economica.ir_detalle.impuesto_a_la_renta = "";
      me.contrato.condicion_economica.ir_detalle.detalle = "";
    }
  });
}

function obtener_periodo_gracia() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.periodo_gracia = [];
  let data = {
    action: "obtener_tipo_periodo_de_gracia",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        if (element.id == 1) {
          me.plazo_id = { id: element.id, text: element.nombre };
        }
        me.periodo_gracia.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_tipo_periodo() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_periodo = [];
  let data = {
    action: "obtener_tipo_periodo",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        if (element.id == 1) {
          me.tipo_periodo_val = { id: element.id, text: element.nombre };
        }
        me.tipo_periodo.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function calcular_fecha_fin_vigencia() {
  const fecha_inicio = flatpickr.parseDate(this.fecha_inicio_val, "d-m-Y");
  const meses = parseInt(this.contrato.condicion_economica.cant_meses_contrato);
  this.contrato.condicion_economica.fecha_inicio = this.fecha_inicio_val;

  if (!isNaN(meses) && fecha_inicio != undefined) {
    const end = new Date(fecha_inicio.getFullYear(), fecha_inicio.getMonth() + meses, fecha_inicio.getDate());
    this.fecha_fin_val = flatpickr.formatDate(end, "d-m-Y");
    this.contrato.condicion_economica.fecha_fin = this.fecha_fin_val;
  } else {
    this.fecha_fin_val = "";
    this.contrato.condicion_economica.fecha_fin = this.fecha_fin_val;
  }
}

function calcular_vigencia_anios_y_meses() {
  let meses = this.contrato.condicion_economica.cant_meses_contrato;
  if (meses == 0 || meses == "") {
    this.contrato.condicion_economica.vigencia_contrato_lectura = "0 años y 0 meses";
  } else if (meses < 12) {
    this.contrato.condicion_economica.vigencia_contrato_lectura = meses + " meses";
  } else {
    var anio = parseInt(meses / 12);
    var meses_restantes = meses % 12;

    if (anio == 0) {
      anio = "";
    } else if (anio == 1) {
      anio = anio + " año";
    } else if (anio > 1) {
      anio = anio + " años";
    }

    if (meses_restantes == 0) {
      meses_restantes = "";
    } else if (meses_restantes == 1) {
      meses_restantes = " y " + meses_restantes + " mes";
    } else if (meses_restantes > 1) {
      meses_restantes = " y " + meses_restantes + " meses";
    }
    this.contrato.condicion_economica.vigencia_contrato_lectura = anio + meses_restantes;
  }
}

function calcular_meses() {
  const fecha_inicio = flatpickr.parseDate(this.fecha_inicio_val, "d-m-Y");
  const fecha_fin = flatpickr.parseDate(this.fecha_fin_val, "d-m-Y");
  if (fecha_inicio != "" && fecha_fin != "") {
    const start = flatpickr.parseDate(fecha_inicio, "d-m-Y");
    const end = flatpickr.parseDate(fecha_fin, "d-m-Y");
    const months = end.getMonth() - start.getMonth() + 12 * (end.getFullYear() - start.getFullYear());
    this.contrato.condicion_economica.cant_meses_contrato = months;
    this.calcular_vigencia_anios_y_meses();
  }
}

function obtener_tipo_incremento() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_incremento = [];
  let data = {
    action: "obtener_tipo_incrementos",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.tipo_incremento.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_tipo_inflacion() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_inflacion = [];
  let data = {
    action: "obtener_tipo_inflacion",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.tipo_inflacion.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_tipo_cuota_extraordinaria() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_cuota_extraordinaria = [];
  let data = {
    action: "obtener_tipo_cuota_extraordinaria",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.tipo_cuota_extraordinaria.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function agregar_nuevo_suministro_agua() {
  this.contrato.inmuebles.inmueble_servicio_agua.push({
    contrato_id: "",
    inmueble_id: "",
    tipo_servicio_id: "",
    nro_suministro: "",
    tipo_compromiso_pago_id: "",
    monto_o_porcentaje: "",
    tipo_documento_beneficiario: 1,
    nombre_beneficiario: "",
    nro_documento_beneficiario: "",
    nro_cuenta_soles: "",
  });
}

function eliminar_suministro_agua(index) {
  this.contrato.inmuebles.inmueble_servicio_agua.splice(index, 1);
}

function agregar_nuevo_suministro_luz() {
  this.contrato.inmuebles.inmueble_servicio_luz.push({
    contrato_id: "",
    inmueble_id: "",
    tipo_servicio_id: "",
    nro_suministro: "",
    tipo_compromiso_pago_id: "",
    monto_o_porcentaje: "",
    tipo_documento_beneficiario: 1,
    nombre_beneficiario: "",
    nro_documento_beneficiario: "",
    nro_cuenta_soles: "",
  });
}

function eliminar_suministro_luz(index) {
  this.contrato.inmuebles.inmueble_servicio_luz.splice(index, 1);
}

function show_modal_adelantos() {
  let data = {};
  EventBus.$emit("abrir_modal_adelantos", data);
  $("#component_modal_adelantos_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_adelantos_" + this.index).focus();
}

function show_modal_nuevo_incremento() {
  let data = {
    title: "Registrar Incremento",
    action: "nuevo",
  };
  EventBus.$emit("nuevo_incremento", data);

  $("#component_modal_incrementos_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_incremento_" + this.index).focus();
}

function show_modal_modificar_incremento(index_incremento) {
  let data = {
    title: "Modificar Incremento",
    index: index_incremento,
    action: "modificar",
  };
  EventBus.$emit("editar_incremento", data);

  $("#component_modal_incrementos_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_incremento_" + this.index).focus();
}

function eliminar_incremento(index) {
  this.contrato.condicion_economica.incrementos.splice(index, 1);
  alertify.success("Se ha eliminado el incremento", 5);
}

function show_modal_nueva_inflacion() {
  let data = {
    title: "Nueva Inflación",
    action: "nuevo",
  };
  EventBus.$emit("nueva_inflacion", data);

  $("#component_modal_inflaciones_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_inflaciones_" + this.index).focus();
}

function show_modal_modificar_inflacion(index_inflacion) {
  let data = {
    title: "Modificar Inflación",
    index: index_inflacion,
    action: "modificar",
  };
  EventBus.$emit("editar_inflacion", data);

  $("#component_modal_inflaciones_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_inflaciones_" + this.index).focus();
}

function eliminar_inflacion(index) {
  this.contrato.condicion_economica.inflaciones.splice(index, 1);
  alertify.success("Se ha eliminado la inflación", 5);
}

function show_modal_nueva_cuota_extraordinaria() {
  let data = {
    title: "Nueva Cuota Extraordinaria",
    action: "nuevo",
  };
  EventBus.$emit("nueva_cuota_extraordinaria", data);
  $("#component_modal_cuotas_extraordinarias_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_cuotas_extraordinarias_" + this.index).focus();
}

function show_modal_modificar_cuota_extraordinaria(index_inflacion) {
  let data = {
    title: "Modificar Cuota Extraordinaria",
    index: index_inflacion,
    action: "modificar",
  };
  EventBus.$emit("editar_cuota_extraordinaria", data);
  $("#component_modal_cuotas_extraordinarias_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_cuotas_extraordinarias_" + this.index).focus();
}

function eliminar_cuota_extraordinaria(index) {
  this.contrato.condicion_economica.cuotas_extraordinarias.splice(index, 1);
  alertify.success("Se ha eliminado la cuota extraordinaria", 5);
}

function show_modal_nuevo_beneficiario() {
  EventBus.$emit("abrir_modal_beneficiarios", {});
  $("#component_modal_beneficiarios_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_beneficiarios_" + this.index).focus();
}

function show_modal_modificar_beneficiario(index_beneficiario) {
  const beneficiario = this.contrato.condicion_economica.beneficiarios[index_beneficiario];

  let data = {
    title: "Modificar Beneficiario",
    action: "registrar",
    beneficiario: {
      id: beneficiario.id,
      contrato_id: "",
      tipo_persona_id: beneficiario.tipo_persona_id,
      tipo_docu_identidad_id: beneficiario.tipo_docu_identidad_id,
      num_docu: beneficiario.num_docu,
      nombre: beneficiario.nombre,
      forma_pago_id: beneficiario.forma_pago_id,
      banco_id: beneficiario.banco_id,
      num_cuenta_bancaria: beneficiario.num_cuenta_bancaria,
      num_cuenta_cci: beneficiario.num_cuenta_cci,
      tipo_monto_id: beneficiario.tipo_monto_id,
      monto: beneficiario.monto,
    },
  };

  EventBus.$emit("modificar-beneficiario", data);
  $("#component_modal_beneficiario_registro_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_beneficiario_registro_" + this.index).focus();
}

function eliminar_beneficiario(index) {
  this.contrato.condicion_economica.beneficiarios.splice(index, 1);
  alertify.success("Se ha eliminado el beneficiario", 5);
}

function show_modal_nuevo_responsable_ir() {
  let data = {
    title: "Nuevo Responsable IR",
    action: "nuevo",
  };
  EventBus.$emit("nuevo-responsable-ir", data);
  $("#component_modal_responsables_ir_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_responsable_ir_" + this.index).focus();
}

function show_modal_modificar_responsable_ir(index_responsable_ir) {
  const responsable_ir = this.contrato.condicion_economica.responsables_ir[index_responsable_ir];

  let data = {
    title: "Modificar Responsable IR",
    action: "registrar",
    responsable_ir: {
      id: responsable_ir.id,
      contrato_id: responsable_ir.contrato_id,
      tipo_documento_id: responsable_ir.tipo_documento_id,
      num_documento: responsable_ir.num_documento,
      nombres: responsable_ir.nombres,
      estado_emisor: responsable_ir.estado_emisor,
      porcentaje: responsable_ir.porcentaje,
    },
  };

  EventBus.$emit("modificar-registro-responsable-ir", data);

  $("#component_modal_responsable_ir_registro_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_responsable_ir_registro_" + this.index).focus();
}

function eliminar_responsable_ir(index) {
  this.contrato.condicion_economica.responsables_ir.splice(index, 1);
  alertify.success("Se ha eliminado el responsable IR", 5);
}

function show_modal_anexo() {
  EventBus.$emit("show-modal-anexos");
  $("#component-modal-anexo-" + this.index).modal("show");
  $("#modal_body_anexo_" + this.index).focus();
}

function borrar_anexo(index) {
  this.contrato.otros_anexos.splice(index, 1);
}

function cargar_otros_anexos(index) {
  var file = this.$refs["otro_anexo_" + index][0].files[0];
  this.contrato.otros_anexos[index].file_name = file.name.substring(0, file.name.lastIndexOf("."));
  this.contrato.otros_anexos[index].file_size = file.size;
  this.contrato.otros_anexos[index].file_extension = file.name.split(".").reverse()[0];
}
function obtener_bancos() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.bancos = [];
  let data = {
    action: "obtener_bancos",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.bancos.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}
