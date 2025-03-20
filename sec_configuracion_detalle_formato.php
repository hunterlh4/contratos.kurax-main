<?php
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
$menu_consultar = isset($menu_id_consultar["id"]) ? $menu_id_consultar["id"] : 0;
$permiso_ver = false;
if (isset($usuario_permisos[$menu_id]) && is_array($usuario_permisos[$menu_id])) {
	$permiso_ver = in_array("view", $usuario_permisos[$menu_id]);
}



$idformato = $_GET["id"];
$adenta_id_temporal = '';
$resolucion_id_temporal = '';

$permiso_editar_contrato_firmado = false;
if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("editar_contrato_firmado", $usuario_permisos[$menu_consultar]))) {
	$permiso_editar_contrato_firmado = true;
}

global $mysqli;
$menu_id = "";
$area_id = $login ? $login['area_id'] : 0;
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];


if (!($area_id == 21 || $area_id == 33 || $area_id == 6 || $permiso_ver)) {
	echo "No tienes permisos para acceder a este recurso";
	die();
} else {

	$query = "SELECT nombre, codigo, descripcion, tipo_contrato_id, contenido FROM cont_formato WHERE idformato = ?";
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("i", $idformato);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$contenido = isset($row["contenido"]) ? $row["contenido"] : "<p>Escribe aquí tu documento...</p>";
	$nombre = $row["nombre"];
	$codigo = $row["codigo"];
	$descripcion = $row["descripcion"];
	$tipo_contrato_id = $row["tipo_contrato_id"];
?>



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

	<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/translations/es.js"></script>
	<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/decoupled-document/ckeditor.js"></script>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.4.0/purify.min.js"></script>
	<script>
		// document.addEventListener("DOMContentLoaded", function() {
		// 	DecoupledEditor
		// 		.create(document.querySelector("#editor"))
		// 		.then(editor => {
		// 			const toolbarContainer = document.querySelector(".toolbar-container");
		// 			toolbarContainer.appendChild(editor.ui.view.toolbar.element);
		// 		})
		// 		.catch(error => {
		// 			console.error("Error al inicializar CKEditor:", error);
		// 		});
		// });
		let editorInstance;

		document.addEventListener("DOMContentLoaded", function() {
			DecoupledEditor
				.create(document.querySelector("#editor"), {
					language: "es",
					toolbar: [
						"heading", "|",
						"fontSize", "fontColor", "fontBackgroundColor", "|",
						"bold", "italic", "underline", "strikethrough", "|",
						"alignment", "|",
						"bulletedList", "numberedList", "|",
						"outdent", "indent", "|",
						"blockQuote", "|",
						"insertTable", "|",
						"undo", "redo"
					],
					fontSize: {
						options: [
							10, 12, 14, 16, 18, 20, 24, 30, 36
						],
						supportAllValues: true // Permite cualquier valor de tamaño
					}
				})
				.then(editor => {
					const toolbarContainer = document.querySelector(".toolbar-container");
					toolbarContainer.appendChild(editor.ui.view.toolbar.element);
					editorInstance = editor;
				})
				.catch(error => {
					console.error("Error al inicializar CKEditor:", error);
				});
		});

		// Función para generar el PDF correctamente
	</script>

	<div id="app" class="body-container">

		<div id="div_sec_contrato_nuevo_arrendamiento">

			<div id="loader_"></div>

			<div class="row">
				<div class="col-xs-12 text-center">
					<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Formato - <?php echo $nombre . " (versión " . $codigo . ")"; ?></h1>
				</div>
			</div>

			<div class="button-container">
				<div class="row align-items-center">
					<!-- Etiqueta y campo en una sola línea -->
					<div class="col-md-9 col-sm-12 d-flex align-items-center">
						<label for="descripcion" class="mb-0">Descripción:</label>
						<input type="text" id="descripcion_formato" class="form-control  " value="<?php echo htmlspecialchars($descripcion); ?>">
					</div>
					<!-- Botones alineados -->
					<div class="col-md-3 col-sm-12 text-md-end text-center mt-2 mt-md-0">
						<button class="btn btn-primary me-2" onclick="actualizarFormato(<?php echo $idformato; ?>,<?php echo $tipo_contrato_id; ?>,'<?php echo $nombre; ?>')">Actualizar Formato</button>
						<button class="btn btn-success" onclick="generatePDF()">Descargar formato</button>

					</div>



				</div>
			</div>

			<div class="document-editor">

				<div class="toolbar-container"></div>
				<div class="content-container">
					<div id="editor"><?php echo $contenido; ?></div>
				</div>
			</div>

			<loader :loader="loader" ref="loader"></loader>

			<!-- FIN MODALS -->

		</div>
	</div>
	</div>
	</div>
	<link rel="stylesheet" href="css/contrato/index.css">
	<!-- Components -->

	<style type="text/css">
		/* .ck-editor__editable {
			min-height: 250px;
		} */
		.pdf-container {
			font-size: 16px !important;
			/* Aumentar el tamaño de fuente */
			line-height: 1.5 !important;
		}

		.document-editor {
			border: 1px solid #ccc;
			border-radius: 5px;
			padding: 10px;
			background: #f5f5f5;
		}

		.content-container {
			width: 25.3cm;
			min-height: 29.7cm;
			padding: 2cm;
			margin: auto;
			background: white;
			box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
		}

		.ck-editor__editable {
			min-height: 29.7cm;
			/* Altura de A4 */
			padding: 2cm;
			padding-top: 0.5cm;
			/* Márgenes similares a un documento */
			background: white;
			box-shadow: none;
		}



		.button-container {
			margin-bottom: 15px;
		}

		.button-container .row {
			display: flex;
			align-items: center;
			flex-wrap: wrap;
		}

		label {
			white-space: nowrap;
		}

		.form-control {
			width: 100%;
			max-width: 100%;
			padding: 8px;
			border: 1px solid #ccc;
			border-radius: 5px;
		}

		.button-container .btn {
			margin-top: 5px;
		}

		body {
			font-family: Arial, sans-serif;
		}

		b,
		strong {
			font-weight: bold !important;
		}
	</style>

<?php
}
?>