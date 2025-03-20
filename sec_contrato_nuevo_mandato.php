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

    .form-control {
      border-color: #bfbfbf;
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

    .document-editor {
      border: 1px solid #ccc;
      max-width: 800px;
      margin: auto;
      padding: 10px;
      background: #fff;
    }

    .toolbar-container {
      border-bottom: 1px solid #ccc;
      padding: 5px;
    }

    .content-container {
      padding: 10px;
    }

    body {
      color: #575759;
      background-color: #f3f3f9;
    }

    .resize {
      resize: vertical;
    }

    .input-group-text2 {
      height: 100%;
      /* Para que coincida con la altura del input */
      padding: 5px 10px;
      /* Ajusta el padding si es necesario */
      font-size: 14px;
      /* Tamaño de ícono y texto más compacto */
      display: flex;
      /* Asegura que el ícono esté centrado */
      align-items: center;
      /* Centra el ícono verticalmente */
      justify-content: center;
    }

    .input-group2 {
      border: none;
      min-height: 28px;
      flex: 1;
    }

    .custom-button {
      width: 50%;
      display: block;
      margin: 0 auto;
    }

    .text-center {
      text-align: center;
    }

    @media (max-width: 768px) {

      /* Para pantallas pequeñas */
      .custom-button {
        width: 100%;
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
  <!-- CKEditor CDN -->
  <!-- <script src="https://cdn.ckeditor.com/ckeditor5/35.3.0/classic/ckeditor.js"></script> -->
  <!-- CKEditor CDN -->

  <!-- <script src="https://cdn.ckeditor.com/ckeditor5/35.3.0/classic/translations/es.js"></script> -->
  <!-- <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/decoupled-document/ckeditor.js"></script>

<div class="document-editor">
    <div class="toolbar-container"></div>
    <div class="content-container">
        <div id="editor">
            <p>Escribe aquí tu documento...</p>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        DecoupledEditor
            .create(document.querySelector("#editor"))
            .then(editor => {
                const toolbarContainer = document.querySelector(".toolbar-container");
                toolbarContainer.appendChild(editor.ui.view.toolbar.element);
            })
            .catch(error => {
                console.error("Error al inicializar CKEditor:", error);
            });
    });
</script> -->

  <div id="app" class="body-container">

    <div id="div_sec_contrato_nuevo_arrendamiento">

      <div id="loader_"></div>

      <div class="row">
        <div class="col-xs-12 text-center">
          <h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Nueva Solicitud - Mandato</h1>
        </div>
      </div>


      <div class="row">
        <div class="col-xs-12 col-md-12 col-lg-12">

          <form ref="form_contrato_nuevo_arrendamiento" id="form_contrato_nuevo_arrendamiento" name="form_contrato_nuevo_arrendamiento" method="POST" enctype="multipart/form-data" autocomplete="off">
            <!-- Inicio Panel Datos -->

            <div class="panel-body mt-1">

              <div class="panel">
                <div class="panel-heading" style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                  <div style="display: flex; align-items: center; gap: 5px;">
                    <span class="icon-wrapper">
                      <i class="fa fa-file" aria-hidden="true"></i>
                    </span>
                    <span>Datos del Contrato</span>
                  </div>

                </div>

                <div class="panel-body">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Tipo de Solicitud <span class="text-danger">(*)</span></label>
                      <input readonly type="text" v-model="arrendamiento.tipo_solicitud" class="form-control form-control-lg border rounded">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Empresa que suscribe el contrato <span class="text-danger">(*)</span></label>
                      <v-select ref="empresa_suscribe" placeholder="Seleccione una empresa" class="w-100 form-control-lg"
                        :filterable="true" label="text" :options="empresa_suscribe" v-model='empresa_suscribe_val'>
                      </v-select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Abogado <span class="text-danger">(*)</span></label>
                      <v-select ref="abogado" placeholder="Seleccione un abogado" class="w-100 form-control-lg"
                        :filterable="true" label="text" :options="abogado" v-model='abogado_val'>
                      </v-select>
                    </div>
                  </div>
                </div>

              </div>

            </div>


            <div class="panel-body">

              <div class="panel">
                <div class="panel-heading" style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                  <div style="display: flex; align-items: center; gap: 5px;">
                    <span class="icon-wrapper">
                      <i class="fa fa-user" aria-hidden="true"></i>
                    </span>
                    <span>Datos del Mandante</span>
                  </div>
                  <button v-if="propietarios.length === 0" class="btn btn-info btn-sm" @click="show_modal_nuevo_propietario" type="button">
                    <i class="icon fa fa-plus"></i> Agregar Mandante
                  </button>
                </div>

                <div class="panel-body">
                  <div class="col-md-12">
                    <br>
                    <component-propietario-listar :propietarios="propietarios"></component-propietario-listar>
                  </div>
                </div>



              </div>

            </div>

            <!-- Final Panel Proveedores -->
            <component-contrato_locacion :propietarios="propietarios" v-for="(contrato, index) in contratos" :key="index" :contrato="contrato" :index="index" :arrendamiento="arrendamiento" :ref="'componente_contrato_' + index"></component-contrato_locacion>

            <div class="row">

              <div class="col-xs-12 col-md-12 col-lg-12">
                <button v-if="btn_registrar" type="button" class="btn btn-success btn-block" @click="validar_contrato">
                  <i class="icon fa fa-save" style="display: inline-block;"></i>
                  <span id="demo-button-text"> Enviar Solicitud de Mandato</span>
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
  <link rel="stylesheet" href="css/contrato/index.css">
  <!-- Components -->
  <script src="./vue/contrato-mandato/components/component-mandato.js"></script>
  <script src="./vue/contrato-mandato/components/loader.js"></script>
  <script src="./vue/contrato-mandato/components/component-modal-propietario-buscar.js"></script>
  <script src="./vue/contrato-mandato/components/component-propietario-lista.js"></script>
  <script src="./vue/contrato-mandato/components/component-modal-propietario-registro.js"></script>
  <script src="./vue/contrato-mandato/components/component-modal-anexo.js"></script>
  <script src="./vue/contrato-mandato/components/component-modal-anexo-registro.js"></script>
  <!-- <script src="./vue/contrato-mandato/components/component-ckeditor.js"></script>  -->
  <!-- Store -->

  <script src="./vue/contrato-mandato/vuex/modules/vuex-contrato.js"></script>
  <script src="./vue/contrato-mandato/vuex/index.js"></script>
  <!-- Main JS -->
  <script src="./vue/contrato-mandato/main-nuevo_mandato.js"></script>
  <style type="text/css">
    .ck-editor__editable {
      min-height: 250px;
    }
  </style>

<?php
}
?>