<?php
global $mysqli;
$menu_id = "";
$area_id = $login ? $login['area_id'] : 0;
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1");
while ($r = $result->fetch_assoc())
  $menu_id = $r["id"];

$permiso_ver = in_array("nuevo_contrato_arrendamiento", $usuario_permisos[$menu_id]);

if (!($area_id == 21 || $area_id == 33 || $area_id == 6 || $permiso_ver)) {
  echo "No tienes permisos para acceder a este recurso";
} else {
?>
  <style>
    .panel {
      margin-bottom: 5px !important;
    }

    .title-panel {
      font-weight: 700 !important;
    }


    .loader-container {
      background: rgba(0, 0, 0, 0.5);
      /* Background color with transparency */
      position: fixed;
      /* Position the overlay relative to the viewport */
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      /* High z-index to overlay on top of other content */
    }

    .loader-template {
      border: 2px solid;
      border-color: transparent #FFF;
      width: 48px;
      height: 48px;
      border-radius: 50%;
      display: inline-block;
      position: relative;
      box-sizing: border-box;
      animation: rotation 2s linear infinite;
    }

    .loader-template::after {
      content: '';
      box-sizing: border-box;
      position: absolute;
      left: 50%;
      top: 50%;
      border: 24px solid;
      border-color: transparent rgba(255, 255, 255, 0.15);
      border-radius: 50%;
      transform: translate(-50%, -50%);
    }

    @keyframes rotation {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }
  </style>


  <link rel="stylesheet" href="./vue/assets/css/flatpickr.min.css">
  <link rel="stylesheet" href="./vue/assets/css/vue-select.css">

  <!--  QUERY CDN-->
  <script src="./vue/assets/js/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
  <!-- Vue CDN -->
  <script src="./vue/assets/js/vue.js"></script>
  <!-- Vuex CDN -->
  <script src="./vue/assets/js/vuex.js"></script>
  <!-- FLATPICKR CDN -->
  <script src="./vue/assets/js/flatpickr.js"></script>
  <script src="./vue/assets/js/flatpickr-es.js"></script>
  <!-- AXIOS CDN -->
  <script src="./vue/assets/js/axios.min.js"></script>
  <!-- EventBus -->
  <script src="./vue/contrato-arrendamiento/event.bus.js"></script>
  <!-- Vue-Select -->
  <script src="./vue/assets/js/vue-select.js"></script>
  <script src="./vue/assets/js/numeral.min.js"></script>

  <div id="app">

    <div id="div_sec_contrato_nuevo_arrendamiento">

      <div id="loader_"></div>

      <div class="row">
        <div class="col-xs-12 text-center">
          <h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Nueva Solicitud - Arrendamiento</h1>
        </div>
      </div>

      <div class="row">
        <div class="col-xs-12 col-md-12 col-lg-12">

          <form ref="form_contrato_nuevo_arrendamiento" id="form_contrato_nuevo_arrendamiento" name="form_contrato_nuevo_arrendamiento" method="POST" enctype="multipart/form-data" autocomplete="off">
            <!-- Inicio Panel Datos -->
            <div class="panel">
              <div class="panel-heading">
                <div class="panel-title">Datos del Contrato</div>
              </div>
              <div class="panel-body">


                <div class="col-md-4">
                  <div class="form-group">
                    <label for="">Tipo de Solicitud <span class="text-danger">(*)</span></label>
                    <input disabled type="text" v-model="arrendamiento.tipo_solicitud" class="form-control">
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="">Empresa que suscribe el contrato <span class="text-danger">(*)</span></label>
                    <v-select ref="empresa_suscribe" autocomplete placeholder="Seleccione una empresa" class="w-100" :filterable="true" label="text" :options="empresa_suscribe" v-model='empresa_suscribe_val'></v-select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="">Supervisor <span class="text-danger">(*)</span></label>
                    <v-select ref="supervisor" placeholder="Seleccione una empresa" class="w-100" :filterable="true" label="text" class="text-dark" :options="supervisor" v-model='supervisor_val'></v-select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="">Aprobación de <span class="text-danger">(*)</span></label>
                    <v-select ref="aprobador" placeholder="Seleccione un aprobador" class="w-100" :filterable="true" label="text" class="text-dark" :options="aprobador" v-model='aprobador_val'></v-select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label for="">Cargo del aprobador <span class="text-danger">(*)</span></label>
                    <v-select ref="cargo_aprobador" placeholder="Seleccione un cargo del aprobador" class="w-100" :filterable="true" label="text" class="text-dark" :options="cargo_aprobador" v-model='cargo_aprobador_val'></v-select>
                  </div>
                </div>

              </div>
            </div>
            <!-- Final Panel Datos -->

            <!-- Inicio Panel Proveedores -->
            <div class="panel">
              <div class="panel-heading">
                <div class="col-md-10">
                  <div class="panel-title">Datos del Propietario</div>
                </div>
                <div class="col-md-2 text-right">
                  <button class="btn btn-info btn-sm" @click="show_modal_nuevo_propietario" type="button"> <i class="icon fa fa-plus"></i> Agregar propietario</button>
                </div>
              </div>
              <div class="panel-body">
                <div class="col-md-12">
                  <br>
                  <component-propietario-listar :propietarios="propietarios"></component-propietario-listar>
                </div>
              </div>
            </div>
            <!-- Final Panel Proveedores -->


            <component-contrato :propietarios="propietarios" v-for="(contrato, index) in contratos" :key="index" :contrato="contrato" :index="index" :arrendamiento="arrendamiento" :ref="'componente_contrato_' + index"></component-contrato>




            <div class="row">
              <div class="panel">
                <div class="panel-body">
                  <div class="col-xs-12 col-md-12 col-lg-12">
                    <button v-if="btn_registrar" type="button" class="btn btn-success btn-block" @click="validar_contrato">
                      <i class="icon fa fa-save" style="display: inline-block;"></i>
                      <span id="demo-button-text"> Enviar Solicitud de Arrendamiento</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>



          </form>


          <!-- INICIO MODALS -->
          <component-modal-propietario-buscar :propietarios="propietarios" ref="ComponentPropietarioBuscar"></component-modal-propietario-buscar>
          <component-modal-propietario-registro :propietarios="propietarios" ref="ComponentPropietarioRegistro"></component-modal-propietario-registro>

          <loader :loader="loader" ref="loader"></loader>

          <!-- FIN MODALS -->

        </div>
      </div>
    </div>
  </div>

  <!-- Components -->
  <script src="./vue/contrato-arrendamiento/components/component-contrato.js"></script>
  <script src="./vue/contrato-arrendamiento/components/loader.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-modal-propietario-buscar.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-propietario-lista.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-modal-propietario-registro.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-modal-adelantos.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-modal-incrementos.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-modal-inflaciones.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-modal-cuotas-extraordinarias.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-modal-beneficiarios.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-modal-beneficiario-registro.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-modal-anexo.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-modal-anexo-registro.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-modal-responsables-ir.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-modal-responsable-ir-registro.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-inmueble-suministro-agua.js"></script>
  <script src="./vue/contrato-arrendamiento/components/component-inmueble-suministro-luz.js"></script>
  <!-- Store -->

  <script src="./vue/contrato-arrendamiento/vuex/modules/vuex-contrato.js"></script>
  <script src="./vue/contrato-arrendamiento/vuex/index.js"></script>
  <!-- Main JS -->
  <script src="./vue/contrato-arrendamiento/main-nuevo.js"></script>

<?php
}
?>