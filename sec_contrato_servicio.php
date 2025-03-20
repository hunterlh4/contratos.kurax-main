
<?php  

	$select_servicio = array(
		array("id" => "1", "Nombre" => "Categoría"),
		array("id" => "2", "Nombre" => "Tipo de Contrato")
	);

	$select_situacion = array(
		array("id" => "2", "Nombre" => "-- Todos --"),
		array("id" => "1", "Nombre" => "Activo"),
		array("id" => "0", "Nombre" => "Inactivo")
	);
?>

<style>
	fieldset.dhhBorder{
		border: 1px solid #ddd;
		padding: 0 15px 5px 15px;
		margin: 0 0 15px 0;
		box-shadow: 0px 0px 0px 0px #000;
		border-radius: 5px;
	}

	legend.dhhBorder{
		font-size: 14px;
		text-align: left;
		width: auto;
		padding: 0 10px;
		border-bottom: none;
		margin-bottom: 10px;
		text-transform: capitalize;
	}

</style>

<div class="container-fluid">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato">
					<i class="icon icon-inline glyphicon glyphicon-briefcase"></i> 
					Servicio de Proveedores -
					<select style="width:150px;display:inline;font-size: 14px;" class="form-control input_text" id="select_servicio" name="select_servicio">
							<?php foreach($select_servicio as $item) :?>
								 <option value="<?= $item["id"] ?>" ><?= $item["Nombre"] ?></option>
							<?php endforeach; ?>
					</select>
				</h1>
			</div>
		</div>
	</div>

	<div class="row mt-4 mb-2" id="div_servicio_categoria">
		<div class="col-md-4">
			<div class="modal-content">
				<form id="formulario" method="POST">
					<div class="modal-header">
						<h2 class="modal-title text-center" id="exampleModalLabel">
							<strong>Registro de Categoría</strong>
						</h2>
					</div>
					<div class="modal-body">
						<input type="hidden" name="txtidserviciocategoria" id="txtidserviciocategoria">
						
						<div class="form-group">
							<label>Nombre:</label>
							<input type="text" class="form-control" name="txtnombre" id="txtnombre" placeholder="Nombre" autocomplete="off">
						</div>
						<br>
						<div id="cont_servicio_campos_formulario">
											
						</div>

					</div>
					<br>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success" id="btnGuardar">
							<i class="fa fa-save"></i>
							Guardar
						</button>
					</div>
				</form>
			</div>
		</div>

		<div class="col-md-8">
			<div id="col-md-12">
				<div class="page-header wide">
					<div>
						<fieldset class="dhhBorder">
							<legend class="dhhBorder">Búsqueda</legend>
							
							<div class="row form-horizontal">
								<div class="col-md-8">
									<div class="form-group">
										<label class="col-md-2" for="combo_situacion">
											Estado:
										</label>
										<div class="col-md-4">
											<select class="form-control" name="cont_categoria_select_situacion" id="cont_categoria_select_situacion">
													<?php foreach($select_situacion as $item) :?>
														 <option value="<?= $item["id"] ?>" ><?= $item["Nombre"] ?></option>
													<?php endforeach; ?>
											</select>
										</div>
										
									</div>
								</div>

							</div>
						</fieldset>
					</div>
				</div>

				<div class="row mt-3" id="cont_servicio_categoria_div_tabla">
					<table id="cont_servicio_categoria_datatable" class="table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" width="100%">
						<thead>
							<th scope="col">Nombre categoría</th>
							<th scope="col">Estado</th>
							<th scope="col">Editar</th>
						</thead>
						<tbody>
							
						</tbody>
						<tfoot>
							<th scope="col">Nombre categoría</th>
							<th scope="col">Estado</th>
							<th scope="col">Editar</th>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="row mt-4 mb-2" id="div_servicio_tipo_categoria" style="display: none;">
		<div class="col-md-4">
			<div class="modal-content">
				<form id="formulariotipocategoria" method="POST">
				<div class="modal-header">
					<h2 class="modal-title text-center" id="exampleModalLabel">
						<strong>Registro tipo de contrato</strong>
					</h2>
				</div>
				<div class="modal-body">

					<input type="hidden" name="txtidserviciotipocategoria" id="txtidserviciotipocategoria">
				
					<div class="form-group">
						<label>Categoría:</label>
						<select class="form-control select2" id="txtidserviciocategoriaselect" name="txtidserviciocategoriaselect" data-live-search="true" required="">
							
						</select>
					</div>

					<div class="form-group">
						<label>Tipo contrato:</label>
						<input type="text" class="form-control" name="txtnombretipocategoria" id="txtnombretipocategoria" placeholder="Tipo contrato" autocomplete="off">
					</div>
					<br>
					<div id="cont_servicio_campos_formulario_tipo_categoria">
									
					</div>
				</div>
				<br>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success" id="btnGuardarTipoCategoria">
						<i class="fa fa-save"></i>
						Guardar
					</button>
				</div>
			</form>
			</div>
		</div>

		<div class="col-md-8">
			<div id="col-md-12">
				<div class="page-header wide">
					<div>
						<fieldset class="dhhBorder">
							<legend class="dhhBorder">Búsqueda</legend>
							
							<div class="row form-horizontal">
								<div class="col-md-8">
									<div class="form-group">
										<label class="col-md-2" for="combo_situacion">
											Estado:
										</label>
										<div class="col-md-4">
											<select class="form-control" name="cont_tipo_categoria_select_situacion" id="cont_tipo_categoria_select_situacion">
													<?php foreach($select_situacion as $item) :?>
														 <option value="<?= $item["id"] ?>" ><?= $item["Nombre"] ?></option>
													<?php endforeach; ?>
											</select>
										</div>
										
									</div>
								</div>

							</div>
						</fieldset>
					</div>
				</div>

				<div class="row mt-3" id="cont_servicio_tipo_categoria_div_tabla">
					<table id="cont_servicio_tipo_categoria_datatable" class="table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" width="100%">
						<thead>
							<th scope="col">Categoría</th>
							<th scope="col">Tipo contrato</th>
							<th scope="col">Estado</th>
							<th scope="col">Editar</th>
						</thead>
						<tbody>
							
						</tbody>
						<tfoot>
							<th scope="col">Nombre categoría</th>
							<th scope="col">Tipo contrato</th>
							<th scope="col">Estado</th>
							<th scope="col">Editar</th>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

	</div>

</div>