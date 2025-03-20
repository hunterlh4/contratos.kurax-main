<?php
include("db_connect.php");
include("sys_login.php");

if(isset($_POST["get_pagos"])){
	$data = $_POST["get_pagos"];
	$data['offset'] = $data['limit']*$data['page'];
	 $where = "";

	// if($login["usuario_locales"]) $where.= " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
	if($data['filter'] != ""){
		$where .= " AND (
 			 pm.id LIKE '%".$data['filter']."%' OR
			 pm.fecha_proceso LIKE '%".$data['filter']."%' OR
			 t.nombre LIKE '%".$data['filter']."%' OR
			 m.nombre LIKE '%".$data['filter']."%' OR
			 pm.referencia LIKE '%".$data['filter']."%' OR
			 pm.descripcion LIKE '%".$data['filter']."%' OR
			 pm.estado LIKE '%".$data['filter']."%' OR
			 pm.fecha_pago LIKE '%".$data['filter']."%' OR
			 pm.monto LIKE '%".$data['filter']."%' OR
			 cdv.codigo LIKE '%".$data['filter']."%' OR
			 l.nombre LIKE '%".$data['filter']."%' OR
			 a.nombre LIKE '%".$data['filter']."%'
			)
		";
	}

	if($data['start_date'] != ""){
		$where .= " AND pm.fecha_pago >= '".$data["start_date"]."'";
	}

	if($data['end_date'] != ""){
		$where .= " AND pm.fecha_pago < '".date('Y-m-d', strtotime("+1 Day", strtotime($data["end_date"])))."'";
	}

		$pm_command = "SELECT
				pm.id,
				pm.fecha_proceso,
				t.nombre AS tipo,
				m.nombre AS motivo,
				SUBSTRING(pm.referencia,1,10) AS referencia,
				pm.descripcion,
				pm.estado,
				DATE_FORMAT(pm.fecha_pago,'%Y-%m-%d') AS fecha_pago,
				pm.monto,
				cdv.codigo AS cdv,
				l.nombre AS local,
				IF(trs.id=16,'IGH','FG') empresa,
				a.nombre AS autoriza
		    FROM tbl_pago_manual pm
			       INNER JOIN tbl_pago_manual_tipos t
			       LEFT JOIN tbl_pago_manual_motivos m ON m.id = pm.motivo_id
			       INNER JOIN tbl_canales_venta cdv
			       INNER JOIN tbl_locales l
			       LEFT JOIN tbl_personal_apt a ON a.id = pm.autorizacion_id
				   left join tbl_locales_redes trs on trs.id = l.red_id  
				 WHERE
		     	 pm.id IS NOT NULL
		       AND t.id = pm.tipo_id
		       AND cdv.id = pm.canal_de_venta_id
		       AND l.id = pm.local_id
			   AND pm.estado = 1
		       ".$where."
		    ORDER BY pm.fecha_proceso DESC
		    LIMIT {$data['limit']} OFFSET {$data['offset']}
		    ";

			// var_dump($pm_command);
			// die;

    $pm_query = $mysqli->query($pm_command);
    if($mysqli->error){
      print_r($mysqli->error);
      exit();
    }
    while($pm=$pm_query->fetch_assoc()){
      $pagos_manuales[$pm["id"]]=$pm;
    }

    $num_rows = $mysqli->query(
    "SELECT
        pm.id
        FROM tbl_pago_manual pm
			       INNER JOIN tbl_pago_manual_tipos t
			       INNER JOIN tbl_pago_manual_motivos m
			       INNER JOIN tbl_canales_venta cdv
			       INNER JOIN tbl_locales l
			       INNER JOIN tbl_personal_apt a
				 WHERE
		     	 pm.id IS NOT NULL
		       AND t.id = pm.tipo_id
		       AND m.id = pm.motivo_id
		       AND cdv.id = pm.canal_de_venta_id
		       AND l.id = pm.local_id
		       AND a.id = pm.autorizacion_id
		     ".$where."
        GROUP BY pm.id")->num_rows;

    $body = "";

    if(!empty($pagos_manuales)){
	    foreach ($pagos_manuales as $pago) {
	      $body .= '<tr class="tr_pm" data-id='.$pago["id"].'>';
	      $body .= '<td title='.$pago["fecha_proceso"].'>'.$pago["fecha_proceso"].'</td>';
	      $body .= '<td>'.$pago["tipo"].'</td>';
	      $body .= '<td>'.$pago["cdv"].'</td>';
	      $body .= '<td>'.$pago["local"].'</td>';
		  $body .= '<td>'.$pago["empresa"].'</td>';
	      $body .= '<td>'.$pago["motivo"].'</td>';
	      $body .= '<td>'.$pago["autoriza"].'</td>';
	      $body .= '<td>'.$pago["referencia"].'</td>';
	      $body .= '<td>'.$pago["descripcion"].'</td>';
	      $body .= '<td class="montos">'.number_format($pago["monto"],2).'</td>';
	      $body .= '<td class="fecha">'.$pago["fecha_pago"].'</td>';
	      $body .= '<td>';
	       if(array_key_exists(36,$usuario_permisos) && in_array("edit", $usuario_permisos[36])){
	      $body .= '<button class="btn btn-xs btn-primary btn_edit"><i class="glyphicon glyphicon-edit"></i></button>';
	        }
	       if(array_key_exists(36,$usuario_permisos) && in_array("delete", $usuario_permisos[36])){
	      $body .= '<button class="btn btn-xs btn-danger btn_remove"><i class="glyphicon glyphicon-remove"></i></button>';
	        }

	      $body .= '</td>';
	      $body .= '</tr>';
	    }
    }

    echo json_encode(['body' => $body, 'num_rows' => $num_rows]);
  }


?>
