<?php

$locales = [];
$result = $mysqli->query("SELECT l.id, l.cc_id, l.nombre FROM tbl_local_caja_detalle_tipos lcc
	INNER JOIN tbl_locales l ON l.id = lcc.local_id 
	WHERE detalle_tipos_id IN(13,17)
");
while($r = $result->fetch_assoc()) $locales[] = $r;

$zonas = [];
$result = $mysqli->query("SELECT id, nombre FROM tbl_zonas");
while($r = $result->fetch_assoc()) $zonas[] = $r;

$productos = [];
$result = $mysqli->query("SELECT * FROM tbl_canales_venta WHERE en_liquidacion = 0 AND estado = 1");
while($r = $result->fetch_assoc()) $productos[] = $r;

if($sub_sec_id=="liquidacion_productos"){
?>
<link rel="stylesheet" href="css/simplePagination.css">
<div class="container-fluid">
	<div class="row">
		<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
			<div class="form-group  has-feedback">
				<label class="control-label" for="txtLiquiFechaInicio">Fecha Inicio: </label>
				<i class="fa fa-calendar form-control-feedback"></i>
				<input type="text" value="<?php echo date('Y-m-d'); ?>" id="txtLiquiFechaInicio" name="txtLiquiFechaInicio" class="filtro form-control" placeholder="" readonly="">
			</div>
		</div>
		<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
			<div class="form-group has-feedback">
				<label class="control-label" for="txtLiquiFechaFin">Fecha Fin: </label>
				<i class="fa fa-calendar form-control-feedback"></i>
				<input type="text" value="<?php echo date('Y-m-d'); ?>" id="txtLiquiFechaFin" name="txtLiquiFechaFin" class="filtro form-control" placeholder="" readonly="">
			</div>
		</div>
		<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
			<div class="form-group">
			    <label for="cbLiquiLocales">Locales: </label>
			    <select multiple id="cbLiquiLocales" style="width: 100%" name="cbLiquiLocales" class="filtro form-control">
			    	<?php foreach ($locales as $local): ?>
			    		<option value="<?php echo $local["id"]; ?>">[<?php echo $local["cc_id"]; ?>] <?php echo $local["nombre"]; ?></option>
			    	<?php endforeach ?>
				</select>
			</div>
		</div>
		<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
			<div class="form-group">
			    <label for="cbLiquiProductos">Productos: </label>
			    <select multiple id="cbLiquiProductos" style="width: 100%" name="cbLiquiProductos" class="filtro form-control">
			    	<?php foreach ($productos as $producto): ?>
			    		<option value="<?php echo $producto["id"]; ?>"><?php echo $producto["nombre"]; ?></option>
			    	<?php endforeach ?>
		    	</select>
			</div>
		</div>
		<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
			<div class="form-group">
			    <label for="cbLiquiZonas">Zonas Comerciales: </label>
			    <select multiple id="cbLiquiZonas" style="width: 100%" name="cbLiquiZonas" class="filtro form-control">
			    	<?php foreach ($zonas as $zona): ?>
			    		<option value="<?php echo $zona["id"]; ?>"><?php echo $zona["nombre"]; ?></option>
			    	<?php endforeach ?>
			    </select>
			</div>
		</div>
		<div class="col-lg-1 col-md-4 col-sm-6 col-xs-12">
			<div class="form-group">
				<label for="cbLiquiLimit">Mostrar: </label>
				<select id="cbLiquiLimit" name="cbLiquiLimit" class="filtro form-control">
					<option value="10">10</option>
					<option value="25">25</option>
					<option value="50">50</option>
					<option value="100">100</option>
					<option value="250">250</option>
					<option value="9999">Todos</option>
				</select>
			</div>
			
		</div>
	</div>
	<div class="row"><br>
		<div class="col-xs-12 col-sm-4 col-lg-3">
			<button id="btnLiquiExportar" class="btn btn-block btn-success mt-2">EXPORTAR EXCEL <i class="fa fa-file-o"></i></button>
		</div>
		<div class="col-xs-12 col-sm-8 col-lg-9">
			<button id="btnLiquiSearch" class="btn btn-primary pull-right mt-2"><i class="fa fa-search"></i> Buscar</button>
			<button id="btnLiquiClear" style="margin-right:5px" class="btn btn-secondary pull-right mt-2"><i class="fa fa-trash"></i></button>
			<div id="liquiPagination" style="margin-top:5px" class="pull-right"></div>
		</div>
	</div>
	<hr>

	<div class="row">
		<div class="col-xs-12">
			<div class="table-responsive">
				<center>
					<table style="margin-top: -30px; width: 1200px !important;" id="tblLiquidaciones" class="table table-condensed table-bordered text-right"></table>
				</center>
			</div>
		</div>
	</div>
</div>
<?php } ?>