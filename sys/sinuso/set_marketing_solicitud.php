<?php
date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/sys/Pagination.php';
 

if ($_POST['action'] == "listar_solicitudes") {

	$usuario_id = $login?$login['id']:null;
	$area_usuario_id = $login ? $login['area_id'] : 0;


	$area_id = $_POST['area_id'];
	$producto_id = $_POST['producto_id'];
	$tipo_solicitud_id = $_POST['tipo_solicitud_id'];
	$estado = $_POST['estado'];
	$fecha_inicio = !Empty($_POST['fecha_inicio']) ? date("Y-m-d",strtotime($_POST['fecha_inicio'])):"";
	$fecha_fin = !Empty($_POST['fecha_fin']) ? date("Y-m-d",strtotime($_POST['fecha_fin'])):"";
 
	$where_area_usuario = "";
	$where_area = "";
	$where_producto = "";
	$where_tipo_solicitud = "";
	$where_estado = "";
	$where_fecha = "";
      
	if ( !(!Empty($area_usuario_id) && ($area_usuario_id == 6 || $area_usuario_id == 18))) {
		$where_area_usuario = " AND peg.area_id = '".$area_usuario_id."' ";
	}
	if (!Empty($area_id)){
		$where_area = " AND r.area_id = '".$area_id."' ";
	}
	if (!Empty($producto_id)){
		$where_producto = " AND r.producto_id = '".$producto_id."' ";
	}
	if (!Empty($tipo_solicitud_id)){
		$where_tipo_solicitud = " AND r.tipo_solicitud_id = '".$tipo_solicitud_id."' ";
	}
	if (!Empty($estado)){
		$where_estado = " AND r.etapa_id = '".$estado."' ";
	}
	if (!Empty($fecha_inicio) && !Empty($fecha_fin)) {
		$where_fecha = " AND r.created_at between '".$fecha_inicio."' AND '".$fecha_fin."'";
	}

    $total_query = $mysqli->query("SELECT COUNT(r.solicitud_id) as num_rows 
		FROM mkt_solicitud as r
		INNER JOIN mkt_areas AS a ON a.id = r.area_id
		INNER JOIN mkt_productos AS p ON p.id = r.producto_id
		INNER JOIN mkt_tipo_solicitud AS ts ON ts.id = r.tipo_solicitud_id

		LEFT JOIN tbl_usuarios us ON us.id = r.user_created_id
		LEFT JOIN tbl_personal_apt peg ON  peg.id = us.personal_id

		WHERE r.status = 1 
           	".$where_area."
			".$where_producto."
			".$where_tipo_solicitud."
			".$where_estado."
			".$where_fecha."
			".$where_area_usuario."
		");
       
        $resultNum = $total_query->fetch_assoc();
        $num_rows = $resultNum['num_rows'];
        $page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
        $per_page = 10;
        $numLinks = 10;
		$total_paginate = ceil($num_rows/$per_page);
		$total_paginate = $total_paginate == 0 ? 1:$total_paginate;
		$page = $page > $total_paginate ? $total_paginate:$page;
		$offset = ($page - 1) * $per_page;
        $paginate = new Pagination('sec_mkt_req_cambiar_de_pagina',$num_rows,$per_page,$numLinks,$page);
		
        ?>
 
        <?php
        $sel_query = $mysqli->query("SELECT 
		r.solicitud_id,
		r.area_id,
		r.producto_id,
		r.tipo_solicitud_id,
		r.numero,
		r.objetivo,
		r.bullet_1,
		r.bullet_2,
		r.bullet_3,
		r.bullet_4,
		r.bullet_5,
		r.req_estrategico_1,
		r.req_estrategico_2,
		r.req_estrategico_3,
		r.req_estrategico_4,
		r.req_estrategico_5,
		r.req_estrategico_6,
		r.req_estrategico_7,
		r.req_estrategico_8,
		r.sustento_req_estrategico,
		r.etapa_id,
		r.status,
		r.user_created_id,
		r.created_at,
		
		a.nombre AS nombre_area,
		p.nombre AS nombre_producto,
		ts.nombre AS nombre_solicitud,
		
		es.nombre as nombre_estado,
	
		CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_usuario,
		peg.correo AS email_usuario	
			
		FROM mkt_solicitud as r
		INNER JOIN mkt_areas AS a ON a.id = r.area_id
		INNER JOIN mkt_productos AS p ON p.id = r.producto_id
		INNER JOIN mkt_tipo_solicitud AS ts ON ts.id = r.tipo_solicitud_id
		
		INNER JOIN mkt_estado_solicitud AS es ON es.id = r.etapa_id
	
		
		LEFT JOIN tbl_usuarios us ON us.id = r.user_created_id
		LEFT JOIN tbl_personal_apt peg ON  peg.id = us.personal_id
		WHERE r.status = 1 
           	".$where_area."
			".$where_producto."
			".$where_tipo_solicitud."
			".$where_estado."
			".$where_fecha."
			".$where_area_usuario."
			ORDER BY r.created_at DESC
            LIMIT $offset,$per_page
		");

      
	?>

	<div class="w-100 text-center">
		<h5>SOLICITUD DE REQUERIMIENTO DE RETAIL</h5>
	</div>
	<br>	
    <div class="table-responsive">
        <table class="table table-striped table-hover table-condensed table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Código</th>
                    <th class="text-center">Área</th>
                    <th class="text-center">Producto</th>
                    <th class="text-center">Solicitud</th>
					<th class="text-center">Usuario</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">F. Solicitud</th>
                    <th class="text-center">Ver Detalle</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
					<th class="text-center">Código</th>
                    <th class="text-center">Área</th>
                    <th class="text-center">Producto</th>
                    <th class="text-center">Solicitud</th>
					<th class="text-center">Usuario</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">F. Solicitud</th>
                    <th class="text-center">Ver Detalle</th>
                </tr>
            </tfoot>
            <tbody>
            <?php
			while($sel=$sel_query->fetch_assoc()){			
			?>
			<tr>
				<td class="text-center"><?php echo $sel["numero"];?></td>
				<td><?php echo $sel["nombre_area"];?></td>
				<td><?php echo $sel["nombre_producto"];?></td>
				<td><?php echo $sel["nombre_solicitud"];?></td>
				<td><?php echo $sel["nombre_usuario"];?></td>
				<td class="text-center">
					<?php 
					$bg_estado_solicitud = '';
					switch ($sel["etapa_id"]) {
						case 1: $bg_estado_solicitud = 'bg-default'; break;
						case 2: $bg_estado_solicitud = 'bg-info'; break;
						case 3: $bg_estado_solicitud = 'bg-success'; break;
						case 4: $bg_estado_solicitud = 'bg-warning'; break;
					}
					?>
				   <span class="badge <?=$bg_estado_solicitud?> text-white"><?php echo $sel["nombre_estado"];?></span>
				</td>
				<td class="text-center"><?php echo $sel["created_at"];?></td>
				<td class="text-center">
					<a class="btn btn-rounded btn-primary btn-sm" 
						href="./?sec_id=marketing&amp;sub_sec_id=detalle_solicitud&id=<?php echo $sel["solicitud_id"];?>"
						title="Ver detalle">
						<i class="fa fa-eye"></i>												
					</a>
				</td>
			</tr>
			<?php
			}
			?>
            </tbody>
        </table>
    </div>

    <div class="row">
        <div class="col-md-4">
			<br>
			<?php $from = $sel_query->num_rows > 0 && $offset == 0 ? 1: $offset;?>
			<small>Mostrando del  <?=$sel_query->num_rows > 0  ? $offset + 1: $offset  ?> al <?=$offset + $sel_query->num_rows?> de <?=$num_rows?> registros.</small>
		</div>
        <div class="col-md-4 text-center">
            <?=$paginate->createLinks();?>
        </div>
        <div class="col-md-4"></div>
    </div>
<?php 
}



?>

