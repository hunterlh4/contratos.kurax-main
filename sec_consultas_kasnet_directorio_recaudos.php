<?php

$recaudos = [];
$result = $mysqli->query("
SELECT
id,
codigo,
nombre_instituicion,
categoria,
canal,
dato_ingreso,
comision_usuario,
departamento,
provincia,
distrito
FROM tbl_consultas_kasnet_recaudos
");
while($r = $result->fetch_assoc()) $recaudos[] = $r;

?>
<div class="content container-fluid content_consultas">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<div class="page-title"><i class="icon icon-inline fa fa-fw fa-list"></i> Consultas - <?php echo strtoupper(str_replace("_", " ", $sub_sec_id)); ?></div>
			</div>
		</div>
	</div>

	<div class="row" class="mt-5">
		<div class="col-xs-12">
			<div class="form-group form-inline">
				<?php if(array_key_exists($menu_id,$usuario_permisos) && in_array("import", $usuario_permisos[$menu_id])): ?>
					<form id="formKdr" method="POST" enctype="multipart/form-data" style="display:none">
						<input type="file" id="fileKdrUpload" name="fileKdrUpload">
						<button type="reset" id="fileKdrUploadReset"></button>
					</form>
					<button id="btnKdrImport" class="btn btn-warning trigger-ctrli"><i class="fa fa-upload"></i> Importar</button>
				<?php endif; ?>
				<?php if(array_key_exists($menu_id,$usuario_permisos) && in_array("export", $usuario_permisos[$menu_id])): ?>
					<button id="btnKdrExport" class="btn btn-success"><i class="fa fa-download"></i> Exportar</button>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="row mt-4">
		<div class="table-responsive">
			<table id="tblKdr" class="table table-striped table-bordered table-hover" style="table-layout: fixed; width: 100%;">
				<thead>
				<tr>
					<th colspan="5" rowspan="1">
						<!-- <div class="col-xs-12 no-pad"> -->
							<div class="row">
								<div class="col-xs-12 col-sm-9 col-md-10 col-lg-10 mt-3">
									<input class="form-control kdr_search val_repli" type="text" name="kdr_search" placeholder="Buscar..." value="" id="kdr_search" autofocus="autofocus">
								</div>
								<div class="col-xs-12 col-sm-3 col-md-2 col-lg-2 mt-3">
									<button type="button" name="button" class="btn btn-block btn-default limpiar_btn"><i class="fa fa-trash-o" aria-hidden="true"></i> Limpiar</button>
								</div>
							</div>
						<!-- </div> -->
					</th>
				</tr>

				<tr class="bg-primary">
					<th class="text-light" style="width: 100px">CÓDIGO</th>
					<th class="text-light">NOMBRE INSTITUICIÓN</th>
					<th class="text-light" style="width: 200px">CATEGORÍA</th>
					<!-- <th class="text-light" style="width: 12%">CANAL</th> -->
					<th class="text-light" style="width: 200px">DATO DE INGRESO</th>
					<th class="text-light" style="width: 200px">COMISIÓN AL USUARIO FINAL</th>
					<!-- <th class="text-light" style="width: 12%">DEPARTAMENTO</th> -->
					<!-- <th class="text-light" style="width: 12%">PROVINCIA</th> -->
					<!-- <th class="text-light" style="width: 12%">DISTRITO</th> -->
				</tr>
			</thead>
			<tbody>
				<?php foreach ($recaudos as $recaudo): ?>
					<tr>
						<td><?php echo $recaudo["codigo"] ?></td>
						<td><?php echo $recaudo["nombre_instituicion"] ?></td>
						<td><?php echo $recaudo["categoria"] ?></td>
						<td><?php echo $recaudo["dato_ingreso"] ?></td>
						<td class="text-right"><?php echo $recaudo["comision_usuario"] ?></td>
						<!-- <td>@mdo</td>
						<td>@mdo</td>
						<td>@mdo</td>
						<td>@mdo</td> -->
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
