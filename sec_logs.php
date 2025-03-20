<?php
global $mysqli;
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '" . $sec_id . "' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];
if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	include("403.php");
	return false;
}

?>
<style>
	#idTableLogsView {
		font-size: 10px;
		/* Ajusta el tamaño de la fuente según tus necesidades */
	}

	.log-container {
		display: flex;
	}

	.log-panel-container {
		flex-grow: 1;
		display: flex;
		flex-direction: column;
		position: relative;
	}

	code {
		width: 100%;
		min-width: 100%;
		height: 500px;
		min-height: 1000px;
		background-color: #415b75;
		color: #eff;
		padding: 1rem 3rem;
		margin: 1rem;
		position: absolute;
		border-radius: 0.25rem;
		counter-reset: step;
		counter-increment: step 0;
		outline: none;
		overflow: auto;
	}

	code p::before {
		position: relative;
		top: 0;
		left: -2.5rem;
		/* Ajusta este valor para aumentar el espacio a la izquierda */

		color: #b5b5b5;
		-webkit-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;
		content: counter(step);
		counter-increment: step;
	}


	/* code:focus {
			background: #2e3d44;
		}

		code p {
			position: relative;
			margin: 0.2rem;
			font-family: monospace;
			display: block;
			white-space: pre;
		}

		code p::before {
			position: absolute;
			top: 0;
			left: -1.75rem;
			color: #50646d;
			-webkit-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
			content: counter(step);
			counter-increment: step;
		} */

	code p {
		position: relative;
		margin: 0.2rem;
		font-family: monospace;
		display: block;
		white-space: pre;
	}

	.downloadButton {
		width: 100%;
		padding: 10px;
		background-color: #3498db;
		color: #fff;
		border: none;
		border-radius: 5px;
		cursor: pointer;
		transition: background-color 0.3s ease;
	}

	.downloadButton:hover {
		background-color: #2980b9;
	}
</style>
<div class="tab-pane" id="sec_id_logs">
	<div class="col-xs-12 col-md-12 col-sm-12">
		<div class="panel" id="">
			<div class="panel-heading">
				<div class="panel-title"><i class="icon fa fa-history tmuted"></i>LOGS</div>
			</div>
			<div class="container-fluid">
				<div class="row">
					<!-- Panel izquierdo más ancho -->
					<div class="col-md-4">
						<div class="panel panel-default">
							<div class="panel-heading">Listado De Logs</div>
							<div class="panel-body">
								<table id="idTableLogsView" class=" table dataTables_wrapper compact" style="width:100%;height:1000px">
									<thead>
										<tr>
											<th>Nombre</th>
											<th>Tamaño</th>
											<th>Atualizado</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<!-- Puedes llenar esta tabla con datos según tus necesidades -->
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<!-- Panel derecho con log y numeración de líneas -->
					<div class="col-md-8 log-container">
						<div class="log-panel-container">
							<div class="panel-heading">
								<button id="idSecLogDownloadButton" class="downloadButton" onclick="" style="display: none">Ver mas</button>
								<!-- <button id="idSecLogDownloadButton2" class="downloadButton" onclick="" style="display: none">DESCARGAR</button> -->
							</div>
							<div class="card">

								<!-- <div class="card-header" class="" id="idSecLogTextNameFile">Panel Derecho</div> -->
								<div class="card-body">

									<input type="hidden" id="idSecLogHiddenFrom" value="0">
									<input type="hidden" id="idSecLogHiddenTo" value="0">
									<input type="hidden" id="idSecLogHiddenName" value="0">


									<code id="logPanel" contentEditable="true">

									</code>



								</div>
								<div class="card-body"> </div>

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>