<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'vale' AND sub_sec_id = 'sincronizacion' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	echo "No tienes permisos para acceder a este recurso";
} else {

$usuario_id = $login?$login['id']:null;

?>

<style>
	.campo_obligatorio{
		font-size: 15px;
		color: red;
	}
    .form-group{
        margin-bottom: 10px !important;
    }
    .sec_vale_sinc_datepicker {
    	min-height: 28px !important;
	}
    .select2-selection--multiple{
        max-height: 10rem !important;
        height: auto !important;
    }
    table thead tr th{
        text-align: center !important;
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/select/1.6.2/css/select.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">


<div id="div_sec_vale_nuevo"></div>

	<div id="loader_"></div>

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12 text-center">
			<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Sincronizar Vales</h1>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12">

                    <div class="row">
                        
                        <div class="col-md-12">
                            <div class="panel">
                                <div class="panel-heading">
                                    <div class="panel-title">Busqueda de Vales</div>
                                </div>
                                <div class="panel-body no-pad">
                                    <form id="frm_sincronizacion_vale" method="post">
                                        <div class="row">
                                            <div class="col-md-9">
                                                <div class="form-group">
                                                    <label for="" class="control-label">Empresa: <strong class="text-danger">*</strong> </label>
                                                    <select class="form-control select2" multiple="multiple" name="sec_vale_sinc_empresa[]" id="sec_vale_sinc_empresa"></select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="" class="control-label">Zona: <strong class="text-danger">*</strong> </label>
                                                    <select class="form-control select2" multiple="multiple" name="sec_vale_sinc_zona[]" id="sec_vale_sinc_zona">
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="" class="control-label">Empleado: </label>
                                                    <input class="form-control" name="sec_vale_sinc_empleado" id="sec_vale_sinc_empleado" type="text">
                                                    
                                                </div>
                                                <div class="form-group">
                                                    <label for="" class="control-label">DNI: </label>
                                                    <input class="form-control" name="sec_vale_sinc_dni" id="sec_vale_sinc_dni" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                            <div class="form-group">
                                                    <label for="" class="control-label">Cuotas: </label>
                                                    <select name="sec_vale_sinc_cuota" id="sec_vale_sinc_cuota" class="form-control select2">
                                                        <option value="0">- Todos -</option>
                                                        <option value="1">1 Cuota</option>
                                                        <option value="2">Mas de 1 Cuota</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="" class="control-label">Desde: <strong class="text-danger">*</strong></label>
                                                    <input type="text" readonly value="<?=date('d-m-Y')?>" class="form-control text-center sec_vale_sinc_datepicker" name="sec_vale_sinc_fecha_desde_vale" id="sec_vale_sinc_fecha_desde_vale" class="form-control">
                                                </div>
                                                <div class="form-group">
                                                    <label for="" class="control-label">Hasta: <strong class="text-danger">*</strong></label>
                                                    <input type="text" readonly value="<?=date('d-m-Y')?>" class="form-control text-center sec_vale_sinc_datepicker" name="sec_vale_sinc_fecha_hasta_vale" id="sec_vale_sinc_fecha_hasta_vale" class="form-control">
                                                </div>
                                                <div class="form-group">
                                                    <label for="control-label"></label>
                                                    <button type="submit" class="btn btn-info form-control">Buscar</button>
                                                </div>
                                            </div>
        
                                        </div>
                            
                                     
                                    </form> 
                                </div>
                            </div>

                            <div class="panel">
                                <div class="panel-heading">
                                    <div class="panel-title">Resultados</div>
                                </div>
                                <div class="panel-body no-pad">

                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered" width="100%" id="tbl_vales_a_sincronizar">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center aling-middle hidden"></th>
                                                        <th class="text-center aling-middle">Empresa</th>
                                                        <th class="text-center aling-middle">Zona</th>
                                                        <th class="text-center aling-middle">Tipo Vale</th>
                                                        <th class="text-center aling-middle">Nro Vale</th>
                                                        <th class="text-center aling-middle">Empleado</th>
                                                        <th class="text-center aling-middle">DNI</th>
                                                        <th class="text-center aling-middle">Fecha Aprobación</th>
                                                        <th class="text-center aling-middle">Fecha Vale</th>
                                                        <th class="text-center aling-middle">Monto Vale</th>
                                                        <th class="text-center aling-middle">Cuotas</th>
                                                        <th class="text-center aling-middle hidden">Observación</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>


                                <div class="col-md-4"></div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="" class="control-label">Fecha de Sincronización: </label>
                                        <input type="text" readonly value="<?=date('d-m-Y')?>" class="form-control text-center sec_vale_sinc_datepicker" name="sec_vale_sinc_fecha_sincronizacion" id="sec_vale_sinc_fecha_sincronizacion" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="control-label"></label>
                                        <button type="button" onclick="sec_vale_sinc_validar_sincronizacion()" class="btn btn-success form-control">Sincronizar</button>
                                    </div>
                                </div>

                            
                            </div>

                         
                                    
                        </div>
                    </div>
                    
             
		</div>





	</div>
</div>



<?php } ?>