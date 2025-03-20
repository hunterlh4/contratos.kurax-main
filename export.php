<?php
include("sys/db_connect.php");
include("sys/sys_login.php");
$to_xls = false;
if ($to_xls) {
	$file = "export_" . time() . ".xls";
	//header('Content-Type: text/csv; charset=utf-8');
	header("Content-type: application/vnd.ms-excel; charset=utf-8");
	header("Content-Disposition: attachment; filename=$file");
	header('Cache-Control: max-age=0');/**/
}

if (isset($_GET["export"])) {
	extract($_GET);

	$ubigeos_arr = array();
	$ubigeos_query = $mysqli->query("SELECT id,cod_depa,cod_prov,cod_dist,nombre FROM tbl_ubigeo");
	while ($ubg = $ubigeos_query->fetch_assoc()) {
		$ubg_id = $ubg["cod_depa"] . $ubg["cod_prov"] . $ubg["cod_dist"];
		$ubigeos_arr[$ubg_id] = $ubg["nombre"];
	}
	//print_r($ubigeos_arr);
?>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<table border="1">
		<?php
		if ($export == "tbl_contratos") {
			$list_query = $mysqli->query("SELECT 
					con.id, con.estado, con.descripcion, con.fecha_registro, con.fecha_inicio_contrato, con.fecha_fin_contrato, con.tiempo_de_contrato, con.juegos_virtuales, con.apuestas_deportivas, con.terminales, con.recargas_web, con.amortizacion_semanal, con.incluye_igv, con.pagador, con.limite_monto, con.corte_no_pago
					,con_tipo.nombre AS contrato_tipo
					,cli.nombre, cli.razon_social, cli.tipo_cliente_id, cli.dni, cli.ruc
					,cli_tipo.nombre AS cliente_tipo
					,loc.tipo_id, loc.nombre AS nombre_de_local
					,loctipo.nombre AS tipo_de_local
					,canal.nombre AS canal_de_venta
					,liq_mod.nombre AS periodo_liquidacion
					,con_tipo_red.nombre AS tipo_red
					,fac_doc_tipo.nombre AS documento_tipo
					,mon.sigla AS moneda
					,lim_pagos.nombre AS limite_pagos
					,neg_tip.nombre AS devo_negativa
						FROM tbl_contratos con
						LEFT JOIN tbl_contrato_tipos con_tipo ON (con_tipo.id = con.tipo_contrato_id)
						LEFT JOIN tbl_clientes cli ON (cli.id = con.cliente_id)
						LEFT JOIN tbl_cliente_tipos cli_tipo ON (cli_tipo.id = cli.tipo_cliente_id)
						LEFT JOIN tbl_locales loc ON (loc.id = con.local_id)
						LEFT JOIN tbl_local_tipo loctipo ON (loctipo.id = loc.tipo_id)
						LEFT JOIN tbl_canales_venta canal ON (canal.id = con.canal_de_venta_id)
						LEFT JOIN tbl_liquidacion_modalidad liq_mod ON (liq_mod.id = con.periodo_liquidacion_id)
						LEFT JOIN tbl_contrato_tipos_de_red con_tipo_red ON (con_tipo_red.id = con.red_tipo_id)
						LEFT JOIN tbl_facturacion_documento_tipos fac_doc_tipo ON (fac_doc_tipo.id = con.documento_tipo_id)
						LEFT JOIN tbl_moneda mon ON (mon.id = con.moneda_id)
						LEFT JOIN tbl_contrato_pago_premios_limite lim_pagos ON (lim_pagos.id = con.limite_pago_id)
						LEFT JOIN tbl_contrato_negativo_devolucion_tipos neg_tip ON (neg_tip.id = con.negativo_devolucion_tipo_id)
						");
			$table_cols = array();
			$list = array();
			while ($li = $list_query->fetch_assoc()) {
				$li["cliente"] = $li["nombre"];
				//$li["dni"]=$li["dni"];
				//$li["razon_social"]=$li["razon_social"];
				//$li["ruc"]=$li["ruc"];
				/*if($li["tipo_cliente_id"]==2){
						$li["cliente"]=$li["razon_social"];
						$li["dni_o_ruc"]=$li["ruc"];
					}elseif($li["tipo_cliente_id"]==1){
						$li["cliente"]=$li["nombre"];
						$li["dni_o_ruc"]=$li["dni"];
					}else{
						if($li["nombre"]){
							$li["cliente"]=$li["nombre"];
						}else{
							$li["cliente"]=$li["razon_social"];					
						}
						if($li["dni"]){
							$li["dni_o_ruc"]=$li["dni"];
						}else{
							$li["dni_o_ruc"]=$li["ruc"];
						}
					}*/
				($li["juegos_virtuales"] ? $li["juegos_virtuales"] = "Si" : $li["juegos_virtuales"] = "No");
				($li["apuestas_deportivas"] ? $li["apuestas_deportivas"] = "Si" : $li["apuestas_deportivas"] = "No");
				($li["terminales"] ? $li["terminales"] = "Si" : $li["terminales"] = "No");
				($li["recargas_web"] ? $li["recargas_web"] = "Si" : $li["recargas_web"] = "No");

				($li["amortizacion_semanal"] ? $li["amortizacion_semanal"] = "Si" : $li["amortizacion_semanal"] = "No");
				($li["incluye_igv"] ? $li["incluye_igv"] = "Si" : $li["incluye_igv"] = "No");

				($li["pagador"] ? $li["pagador"] = "Operador Principal" : $li["pagador"] = "Agente");

				($li["corte_no_pago"] ? $li["corte_no_pago"] = "Si" : $li["corte_no_pago"] = "No");
				//($li["estado"] ? $li["estado"]="Si" : $li["estado"]="No");	
				if ($li["estado"] == 2) {
					$li["estado"] = "Terminado";
				} elseif ($li["estado"] == 1) {
					$li["estado"] = "Con Contrato";
				} else {
					$li["estado"] = "Pendiente";
				}

				$list[] = $li;
			}
			$list_cols = array();
			$list_cols["id"] = "ID";
			$list_cols["fecha_registro"] = "Fecha de Registro";
			$list_cols["canal_de_venta"] = "Canal de venta";
			$list_cols["contrato_tipo"] = "Tipo de Contrato";
			$list_cols["fecha_inicio_contrato"] = "Fecha de Inicio";
			$list_cols["fecha_fin_contrato"] = "Fecha de Fin";
			$list_cols["tiempo_de_contrato"] = "Tiempo de contrato";
			$list_cols["cliente_tipo"] = "Tipo de Cliente";
			$list_cols["cliente"] = "Cliente";
			$list_cols["dni"] = "DNI";
			$list_cols["razon_social"] = "Razón Social";
			$list_cols["ruc"] = "RUC";
			//$list_cols["dni_o_ruc"]="DNI/RUC";
			$list_cols["tipo_de_local"] = "Tipo de Local";
			$list_cols["nombre_de_local"] = "Nombre de Local";

			$list_cols["juegos_virtuales"] = "Juegos Virtuales";
			$list_cols["apuestas_deportivas"] = "Apuestas Deportivas";
			$list_cols["terminales"] = "Terminales";
			$list_cols["recargas_web"] = "Recargas Web";

			$list_cols["amortizacion_semanal"] = "Amortización Semanal";
			$list_cols["incluye_igv"] = "Pago Incluye IGV";
			$list_cols["pagador"] = "Pagador";
			$list_cols["periodo_liquidacion"] = "Periodo Liquidacion";
			$list_cols["tipo_red"] = "Tipo Red";
			$list_cols["documento_tipo"] = "Tipo de Documento";
			$list_cols["moneda"] = "Moneda";

			$list_cols["limite_pagos"] = "Limite Pagos";
			$list_cols["limite_monto"] = "Limite Monto";
			$list_cols["devo_negativa"] = "Devolucion Negativa";
			$list_cols["corte_no_pago"] = "Corte No Pago";

			$list_cols["estado"] = "Estado";

			if (@$type == "lista") {
				$list_cols["opciones"] = "Opciones";
				$view_cols = array("id", "fecha_registro", "canal_de_venta", "contrato_tipo", "cliente");
				if (isset($_POST["contratos_list_cols_submit"])) {
					if (isset($_POST["contratos_list_cols"])) {
						$view_cols = $_POST["contratos_list_cols"];
					} else {
						$view_cols = array();
					}
				} elseif (isset($_COOKIE["contratos_list_cols"])) {
					$view_cols = json_decode($_COOKIE['contratos_list_cols'], true);
				} else {
				}
				$view_cols[] = "id";
				$view_cols[] = "estado";
				foreach ($list_cols as $key => $value) {
					if (in_array($key, $view_cols)) {
						$list_cols_show[$key] = $value;
					}
				}
			} elseif (@$type == "excel") {
				require_once 'phpexcel/classes/PHPExcel.php';
				$query = "
						SELECT
							con.id AS 'ID'
							,con.fecha_registro AS 'FECHA REGISTRO'
							,CONCAT('[',cli.id,']',' ',cli.nombre) AS 'NOMBRE CLIENTE'
							,cli.dni AS 'DNI'
							,CONCAT('[',con.local_id,']',' ',l.nombre) AS 'NOMBRE DE LA TIENDA'
							,l.direccion AS 'DIRECCIÓN DE LA TIENDA'
							,u.nombre  AS 'DEPARTAMENTO'
							,u2.nombre AS 'PROVINCIA'
							,u3.nombre AS 'DISTRITO'
							FROM tbl_contratos con
							LEFT JOIN tbl_clientes cli ON (cli.id = con.cliente_id)
							LEFT JOIN tbl_locales l ON (l.id = con.local_id)
							LEFT JOIN tbl_ubigeo u ON (
							u.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
							u.cod_prov = SUBSTRING(l.ubigeo_id, 3, 2) AND
							u.cod_dist = SUBSTRING(l.ubigeo_id, 5, 2)
							)
							LEFT JOIN tbl_ubigeo u2 ON (
							u2.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
							u2.cod_prov = SUBSTRING(l.ubigeo_id, 3, 2) AND
							u2.cod_dist = '00'
							)
							LEFT JOIN tbl_ubigeo u3 ON (
							u3.cod_depa = SUBSTRING(l.ubigeo_id, 1, 2) AND
							u3.cod_prov = '00' AND
							u3.cod_dist = '00')
						";
				$query_result = $mysqli->query($query);
				$contratos = [];
				if ($mysqli->error) {
					echo $mysqli->error;
					die;
				}
				while ($r = $query_result->fetch_assoc()) {
					$contratos[] = $r;
				}
				if (count($contratos) > 0) {
					$headers = [];
					foreach ($contratos[0] as $key => $value) {
						$headers[] = $key;
					}
					array_unshift($contratos, $headers);
				}
				//echo "<pre>";print_r($contratos[0]);echo "</pre>";die();
				$doc = new PHPExcel();
				$doc->setActiveSheetIndex(0);
				$doc->getActiveSheet()->fromArray($contratos);

				$filename = "reporte_contratos_al_" . date("Ymdhis") . ".xls";
				$excel_path = '/var/www/html/export/contratos/' . $filename;

				$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
				$objWriter->save($excel_path);
				header('Location: /export/contratos/' . $filename);
			} else {
				$list_cols_show = $list_cols;
			}
		?>

		<?php
		} elseif ($export == "tbl_locales") {
			$list_query = $mysqli->query("SELECT l.id                                               
												, l.cc_id
												, IF(TRIM(l.latitud) = '' OR l.latitud IS NULL OR TRIM(l.longitud) = '' OR l.longitud IS NULL, NULL, CONCAT(TRIM(l.latitud), ',', TRIM(l.longitud))) AS coordenadas
												, l.nombre
												, l.direccion
												, l.descripcion
												, l.email
												, l.area
												, l.ubigeo_id
												,lt.nombre AS tipo
												,cl.nombre AS cliente_nombre
												,cl.razon_social AS cliente_razon_social
												,el.nombre AS estado_legal
												,lp.nombre AS agente
											FROM tbl_locales l
											LEFT JOIN tbl_local_tipo lt ON (lt.id = l.tipo_id)
											LEFT JOIN tbl_clientes cl ON (cl.id = l.cliente_id)
											LEFT JOIN tbl_local_estado_legal el ON (el.id = l.estado_legal_id)
											LEFT JOIN tbl_personal_apt lp ON  (lp.id = l.asesor_id)
											WHERE l.estado = '1' AND l.id <> 1367 AND l.id <> 1198 AND l.id <> 1197 AND l.id <> 1368 AND l.id <> 1199 AND l.id <> 1532 AND l.id <> 1 AND l.id <> 786 AND l.id <> 781 AND l.id <> 784 AND l.id <> 785 AND l.id <> 780 AND l.id <> 782 AND l.id <> 787 AND l.id <> 783        
											ORDER BY l.nombre ASC
											");
			$list = array();
			while ($li = $list_query->fetch_assoc()) {
				if ($li["cliente_razon_social"]) {
					$li["cliente"] = $li["cliente_razon_social"];
				} else {
					$li["cliente"] = $li["cliente_nombre"];
				}
				$li["departamento"] = @$ubigeos_arr[substr($li["ubigeo_id"], 0, 2) . "0000"];
				$li["provincia"] = @$ubigeos_arr[substr($li["ubigeo_id"], 0, 4) . "00"];;
				$li["distrito"] = @$ubigeos_arr[$li["ubigeo_id"]];
				$list[] = $li;
			}
			$list_cols = array();
			$list_cols["id"] = "ID";
			$list_cols["cc_id"] = "CC";
			$list_cols["nombre"] = "Nombre";
			$list_cols["cliente"] = "Cliente";
			$list_cols["tipo"] = "Tipo";
			$list_cols["descripcion"] = "Descripción";
			$list_cols["email"] = "Correo";
			$list_cols["area"] = "Área";
			$list_cols["direccion"] = "Dirección";
			$list_cols["ubigeo_id"] = "Ubigeo";
			$list_cols["departamento"] = "Departamento";
			$list_cols["provincia"] = "Provincia";
			$list_cols["distrito"] = "Distrito";
			$list_cols["coordenadas"] = "Coordenadas";
			$list_cols["estado_legal"] = "Estado Legal";
			$list_cols["agente"] = "Asesor";
			$list_cols_show = $list_cols;
		} elseif ($export == "tbl_locales_detallado") {
			/*
				$list_query=$mysqli->query("SELECT l.id                                               
												, l.cc_id
												, IF(TRIM(l.latitud) = '' OR l.latitud IS NULL OR TRIM(l.longitud) = '' OR l.longitud IS NULL, NULL, CONCAT(TRIM(l.latitud), ',', TRIM(l.longitud))) AS coordenadas
												, l.nombre
												, l.direccion
												, l.descripcion
												, l.email
												, l.phone
												, l.area
												, l.ubigeo_id
												, CASE WHEN l.trastienda = 1 THEN 'Si' ELSE 'No' END as trastienda
												,lt.nombre AS tipo
												,cl.nombre AS cliente_nombre
												,l.zona_id
												,z.nombre AS zona
												,cl.razon_social AS cliente_razon_social
												,el.nombre AS estado_legal
												,lp.nombre AS agente
												,IF(
													TRIM(l.latitud) <> '' AND TRIM(l.longitud) <> '',
													CONCAT('https://www.google.com/maps?q=', TRIM(l.latitud), ',', TRIM(l.longitud)),
													NULL
													) AS enlace_mapa 
												,IFNULL(e.num_terminales_kasnet,0) as num_terminales_kasnet
												,IFNULL(e.num_tv_apuestas_virtuales,0) as num_tv_apuestas_virtuales
												,IFNULL(e.num_tv_apuestas_deportivas,0) as num_tv_apuestas_deportivas
												,IFNULL(c.num_cpu,0) as num_cpu
												,IFNULL(c.num_monitores,0) as num_monitores
												,IFNULL(c.num_autoservicios,0) as num_autoservicios
												,IFNULL(c.num_allinone,0) as num_allinone
												,IFNULL(c.num_terminales_hibrido,0) as num_terminales_hibrido
												,IFNULL(c.num_terminales_antiguo,0) as num_terminales_antiguo
												,s.internet_proveedor_id
												,p.nombre AS internet_proveedor_nombre
												,s.internet_tipo_id
												,t.nombre AS internet_tipo_nombre
												,IFNULL(s.num_decos_internet,0) as num_decos_internet
												,IFNULL(s.num_decos_directv,0) as num_decos_directv
												,GROUP_CONCAT(DISTINCT CASE WHEN wd.id = 1 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS monday
												,GROUP_CONCAT(DISTINCT CASE WHEN wd.id = 2 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS tuesday
												,GROUP_CONCAT(DISTINCT CASE WHEN wd.id = 3 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS wednesday
												,GROUP_CONCAT(DISTINCT CASE WHEN wd.id = 4 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS thursday
												,GROUP_CONCAT(DISTINCT CASE WHEN wd.id = 5 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS friday
												,GROUP_CONCAT(DISTINCT CASE WHEN wd.id = 6 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS saturday
												,GROUP_CONCAT(DISTINCT CASE WHEN wd.id = 0 THEN CONCAT(hd.start_shift, '-', hd.end_shift) END) AS sunday
                                                ,IFNULL(lc.conteo_cajas, 0) as conteo_cajas
												,g.nombre_subgerente
												,jc.jefe_comercial
												,jc.telefono as js_telefono
												,s.supervisor
												,s.telefono as s_telefono
												,s.correo as s_correo
												,l.estado
												,l.fecha_inicio_operacion
											FROM tbl_locales l
											INNER JOIN (
												SELECT zona_id, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_subgerente FROM tbl_personal_apt WHERE cargo_id=29
												) AS g ON g.zona_id = l.zona_id 
											INNER JOIN (
												SELECT zona_id, telefono, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS jefe_comercial FROM tbl_personal_apt WHERE cargo_id=16
												) AS jc ON jc.zona_id = l.zona_id
											INNER JOIN (
												SELECT zona_id, telefono, correo, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) AS supervisor FROM tbl_personal_apt WHERE cargo_id=4
												) AS s ON s.zona_id = l.zona_id 
											LEFT JOIN tbl_local_tipo lt ON (lt.id = l.tipo_id)
											LEFT JOIN tbl_clientes cl ON (cl.id = l.cliente_id)
											LEFT JOIN tbl_zonas z ON (z.id = l.zona_id)
											LEFT JOIN tbl_local_estado_legal el ON (el.id = l.estado_legal_id)
											LEFT JOIN tbl_personal_apt lp ON  (lp.id = l.asesor_id)                                 
											LEFT JOIN tbl_locales_equipos e ON l.id = e.local_id
											LEFT JOIN tbl_locales_equipos_computo c ON l.id = c.local_id
											LEFT JOIN tbl_locales_servicios s ON l.id = s.local_id
											LEFT JOIN tbl_internet_proveedor p ON s.internet_proveedor_id = p.id
											LEFT JOIN tbl_internet_tipo t ON s.internet_tipo_id = t.id
											INNER JOIN (SELECT local_id, MAX(started_at) AS started_at 
												FROM tbl_locales_horarios 
												GROUP BY local_id) AS lh_max ON lh_max.local_id = l.id
											INNER JOIN tbl_locales_horarios AS lh ON lh.local_id = l.id AND lh.started_at = lh_max.started_at
											INNER JOIN tbl_horarios AS h ON h.id = lh.horario_id
											INNER JOIN tbl_horarios_dias AS hd ON hd.horario_id = h.id
											INNER JOIN tbl_weekdays AS wd ON wd.id = hd.weekday_id
                                            LEFT JOIN (SELECT local_id, COUNT(id) AS conteo_cajas 
												FROM tbl_local_cajas 
												GROUP BY local_id) AS lc ON lc.local_id = l.id
											WHERE l.estado = '1' AND l.id <> 1367 AND l.id <> 1198 AND l.id <> 1197 AND l.id <> 1368 AND l.id <> 1199 AND l.id <> 1532 AND l.id <> 1 AND l.id <> 786 AND l.id <> 781 AND l.id <> 784 AND l.id <> 785 AND l.id <> 780 AND l.id <> 782 AND l.id <> 787 AND l.id <> 783        
											GROUP BY l.id
											ORDER BY l.nombre ASC
											");
				$list=array();
				while ($li=$list_query->fetch_assoc()) {
					if($li["cliente_razon_social"]){
						$li["cliente"]=$li["cliente_razon_social"];
					}else{
						$li["cliente"]=$li["cliente_nombre"];
					}
					$li["departamento"]=@$ubigeos_arr[substr($li["ubigeo_id"], 0,2)."0000"];
					$li["provincia"]=@$ubigeos_arr[substr($li["ubigeo_id"], 0,4)."00"];;
					$li["distrito"]=@$ubigeos_arr[$li["ubigeo_id"]];			
					$list[]=$li;
				}
				$list_cols = array();
					$list_cols["cc_id"]="CC";
					$list_cols["nombre"]="Nombre";
					$list_cols["departamento"]="Departamento";
					$list_cols["provincia"]="Provincia";
					$list_cols["distrito"]="Distrito";
					$list_cols["direccion"]="Dirección";
					$list_cols["enlace_mapa"]="Ubicación G. Maps";
					$list_cols["internet_proveedor_nombre"]="Proveedor de Internet";
					$list_cols["internet_tipo_nombre"]="Tipo de Internet";
					$list_cols["num_decos_internet"]="# decos de Internet";
					$list_cols["num_decos_directv"]="# de decos de directv";
					$list_cols["num_cpu"]="# de CPU";
					$list_cols["num_monitores"]="# de Monitores";
					$list_cols["num_terminales_kasnet"]="# de terminales de KASNET";
					$list_cols["trastienda"]="Trastienda";
					$list_cols["monday"]="Lunes";
					$list_cols["tuesday"]="Martes";
					$list_cols["wednesday"]="Miercoles";
					$list_cols["thursday"]="Jueves";
					$list_cols["friday"]="Viernes";
					$list_cols["saturday"]="Sabado";
					$list_cols["sunday"]="Domingo";
					$list_cols["conteo_cajas"]="# de cajas operativas";
					$list_cols["num_autoservicios"]="# de autoservicios";
					$list_cols["num_allinone"]="# de AIO";
					$list_cols["num_terminales_hibrido"]="# de terminales híbrido";
					$list_cols["num_terminales_antiguo"]="# de terminales antiguos";
					$list_cols["num_tv_apuestas_virtuales"]="# de televisores virtuales";
					$list_cols["num_tv_apuestas_deportivas"]="# de televisores apuestas deportivas";
					//-------------------------
					$list_cols["nombre_subgerente"]="Jefatura (subgerente)";
					$list_cols["zona"]="Zona";
					$list_cols["jefe_comercial"]="Jefe Comercial";
					$list_cols["js_telefono"]="Celular del jefe comercial";
					$list_cols["supervisor"]="Supervisor";
					$list_cols["s_telefono"]="Celular del supervisor";
					$list_cols["s_correo"]="Correo del supervisor";
					$list_cols["phone"]="Celular de la tienda";
					$list_cols["email"]="Correo de la tienda";
					$list_cols["estado"]="Status diario";
					$list_cols["fecha_inicio_operacion"]="Fecha de apertura";

					//$list_cols["cliente"]="Cliente";
					//$list_cols["tipo"]="Tipo";
					//$list_cols["descripcion"]="Descripcion";
					//$list_cols["email"]="Correo";
					//$list_cols["area"]="Area";
					//$list_cols["ubigeo_id"]="Ubigeo";
					
					//$list_cols["coordenadas"]="Coordenadas";
					//$list_cols["estado_legal"]="Estado Legal";
					//$list_cols["agente"]="Asesor";
				$list_cols_show=$list_cols;
			*/
		} elseif ($export == "tbl_tickets_por_pagar") {

			$where_local_id = '';
			if ($local != "_all_") {
				$where_local_id = " AND l.id = '" . $local . "'";
			}
			$locales_select = implode(",", $login["usuario_locales"]);
			if ($locales_select != '') {
				$where_local_id = " AND l.id IN (" . implode(",", $login["usuario_locales"]) . ")";
			}

			$caja_command = "
				SELECT 
				l.nombre                          AS local_name,
				count(l.id) 						 AS cantidad,
				sum(D.col_Amount)                 AS ganado
				FROM   bc_apuestatotal.at_BetPendingPay  AS D
					LEFT JOIN bc_apuestatotal.tbl_CashDesk AS CD
							ON  D.col_CashDeskId = CD.col_id
					LEFT JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp
							ON  lp.proveedor_id = CD.col_id
					LEFT JOIN wwwapuestatotal_gestion.tbl_locales l
							ON  l.id = lp.local_id
				WHERE  D.col_Created >= '{$ini}'
					AND D.col_CashDeskId IS NOT          NULL
					{$where_local_id}
					GROUP BY
				l.id
				";

			//echo $caja_command; exit();
			$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
			$caja_query = $mysqli->query($caja_command);
			if ($mysqli->error) {
				print_r($mysqli->error);
				exit();
			}
			$mysqli->query("COMMIT");

			$list = array();
			while ($li = $caja_query->fetch_assoc()) {

				$list[] = $li;
			}
			$list_cols = array();
			//$list_cols["created"]="FECHA";
			$list_cols["local_name"] = "LOCAL";
			$list_cols["cantidad"] = "CANTIDAD";
			$list_cols["ganado"] = "GANADO";
			$list_cols_show = $list_cols;
		} elseif ($export == "tbl_destinatario") {
			$list_query = $mysqli->query("SELECT l.id
												, l.nombre
												, l.correo
												, l.estado
												
											FROM tbl_destinatario l
											WHERE l.estado = '1'
											ORDER BY l.nombre ASC
											");
			$list = array();
			while ($li = $list_query->fetch_assoc()) {
				if ($li["estado"] == 0) {
					$li["estado"] = "INACTIVO";
				} else {
					$li["estado"] = "ACTIVO";
				}
				$list[] = $li;
			}
			$list_cols = array();
			$list_cols["id"] = "ID";
			$list_cols["nombre"] = "NOMBRE";
			$list_cols["correo"] = "CORREO";
			$list_cols["estado"] = "ESTADO";
			$list_cols_show = $list_cols;
		} elseif ($export == "tbl_caja_eliminada") {

			$cajas_sql_command = "select id, fecha_registro,data,login from tbl_auditoria where proceso='sec_caja_eliminar' and date(fecha_registro) >='" . $ini . "' and date(fecha_registro) <='" . $fin . "' ";
			//$cajas_sql_command.= " and lc.local_id = '".$local_id."'";
			//echo $cajas_sql_command;
			$cajas_sql_command .= " order by fecha_registro desc";
			$cajas_sql_query = $mysqli->query($cajas_sql_command);
			if ($mysqli->error) {
				echo "ERROR: ";
				print_r($mysqli->error);
				exit();
			}

			$cdv = array();
			$i = 0;
			$list = array();

			$locales_query = "SELECT id, cc_id FROM tbl_locales where cc_id IS NOT NULL and red_id = 1";
			$result = $mysqli->query($locales_query);
			while ($row = $result->fetch_assoc()) $locales[$row["id"]] = $row["cc_id"];

			if ($local == "_all_") {
				while ($row_selected = $cajas_sql_query->fetch_assoc()) {

					$data2 = json_decode($row_selected['data'], true);
					$caja = json_decode($data2['response'], true);
					$fechaapertura = strtotime(date("d-m-Y", strtotime($caja['caja']['fecha_apertura'])));
					$mensaje = 'Sin Mensaje';
					if (array_key_exists('mensaje', $data2)) {
						$mensaje = $data2['mensaje'];
					};
					$localID = $caja['caja']['local_id'];
					$usuarioElimina = json_decode($row_selected['login'], true);

					$filearr = explode("]", $caja['caja']["usuario_nombre"]);
					$nombreusuario = "";
					if (count($filearr) == 2) {
						$nombreusuario = $filearr[1];
					} else {
						$nombreusuario = $caja['caja']["usuario_nombre"];
					}

					$list[$i]['fecha_registro'] = $row_selected["fecha_registro"];
					$list[$i]['usuario_elimina'] = $usuarioElimina['nombre'] . " " . $usuarioElimina['apellido_paterno'];
					$list[$i]['local_nombre'] = substr($caja['caja']['local_nombre'], strpos($caja['caja']['local_nombre'], " "));
					$list[$i]['cc_id'] = isset($locales[$caja['caja']["local_id"]]) ? $locales[$caja['caja']["local_id"]] : "";
					$list[$i]['usuario_nombre'] = $nombreusuario;
					$list[$i]['caja_nombre'] = $caja['caja']["caja_nombre"];
					$list[$i]['turno'] = $caja['caja']["turno"];
					$list[$i]['fecha_operacion'] = $caja['caja']["fecha_operacion"];
					$list[$i]['fecha_apertura'] = $caja['caja']["fecha_apertura"];
					$list[$i]['fecha_cierre'] = $caja['caja']["fecha_cierre"];
					$list[$i]['mensaje'] = $mensaje;
					$list[$i]['estado_nombre'] = $caja['caja']["estado_nombre"];

					$i++;
				}
			} else {
				while ($row_selected = $cajas_sql_query->fetch_assoc()) {

					$data2 = json_decode($row_selected['data'], true);
					$caja = json_decode($data2['response'], true);
					$fechaapertura = strtotime(date("d-m-Y", strtotime($caja['caja']['fecha_apertura'])));
					$mensaje = 'Sin Mensaje';
					if (array_key_exists('mensaje', $data2)) {
						$mensaje = $data2['mensaje'];
					};
					$localID = $caja['caja']['local_id'];
					$usuarioElimina = json_decode($row_selected['login'], true);

					if ($local == $localID) {
						$filearr = explode("]", $caja['caja']["usuario_nombre"]);
						$nombreusuario = "";
						if ($filearr[1]) {
							$nombreusuario = $filearr[1];
						}


						$list[$i]['fecha_registro'] = $row_selected["fecha_registro"];
						$list[$i]['usuario_elimina'] = $usuarioElimina['nombre'] . " " . $usuarioElimina['apellido_paterno'];
						$list[$i]['local_nombre'] = substr($caja['caja']['local_nombre'], strpos($caja['caja']['local_nombre'], " "));
						$list[$i]['cc_id'] = isset($locales[$caja['caja']["local_id"]]) ? $locales[$caja['caja']["local_id"]] : "";
						$list[$i]['usuario_nombre'] = $nombreusuario;
						$list[$i]['caja_nombre'] = $caja['caja']["caja_nombre"];
						$list[$i]['turno'] = $caja['caja']["turno"];
						$list[$i]['fecha_operacion'] = $caja['caja']["fecha_operacion"];
						$list[$i]['fecha_apertura'] = $caja['caja']["fecha_apertura"];
						$list[$i]['fecha_cierre'] = $caja['caja']["fecha_cierre"];
						$list[$i]['mensaje'] = $mensaje;
						$list[$i]['estado_nombre'] = $caja['caja']["estado_nombre"];

						$i++;
					}
				}
			}

			$list_cols = array();
			$list_cols["fecha_registro"] = "FECHA ELIMINACION";
			$list_cols["usuario_elimina"] = "USUARIO ELIMINACION";
			$list_cols["local_nombre"] = "LOCAL";
			$list_cols["cc_id"] = "CENTRO COSTO";
			$list_cols["usuario_nombre"] = "USUARIO";
			$list_cols["caja_nombre"] = "CAJA";
			$list_cols["turno"] = "TURNO";
			$list_cols["fecha_operacion"] = "FECHA OPERACION";
			$list_cols["fecha_apertura"] = "FECHA APERTURA";
			$list_cols["fecha_cierre"] = "FECHA CIERRE";
			$list_cols["mensaje"] = "MENSAJE";
			$list_cols["estado_nombre"] = "ESTADO";
			$list_cols_show = $list_cols;
		} elseif ($export == "tbl_historial_monto") {

			$cajas_sql_command = "select id, fecha_registro,data,login from tbl_auditoria where proceso='locales_guardar_monto_inicial' and date(fecha_registro) >='" . $ini . "' and date(fecha_registro) <='" . $fin . "' ";
			//$cajas_sql_command.= " and lc.local_id = '".$local_id."'";
			//echo $cajas_sql_command;
			$cajas_sql_command .= " order by fecha_registro desc";
			$cajas_sql_query = $mysqli->query($cajas_sql_command);
			if ($mysqli->error) {
				echo "ERROR: ";
				print_r($mysqli->error);
				exit();
			}

			$rowlocal_ = "";
			if ($local == "_all_") {
				$rowlocal_ = "Todos";
			} else {
				$local_ = $mysqli->query("SELECT l.id, l.nombre FROM tbl_locales l WHERE l.id = '" . $local . "'")->fetch_assoc();
				$rowlocal_ = $local_['nombre'];
			}

			$i = 0;
			$list = array();

			if ($local == "_all_") {
				while ($row_selected = $cajas_sql_query->fetch_assoc()) {

					$data2 = json_decode($row_selected['data'], true);
					$caja = json_decode($data2['item_id'], true);
					$localID = $caja;
					$local_ = $mysqli->query("SELECT l.id, l.nombre FROM tbl_locales l WHERE l.id = '" . $localID . "'")->fetch_assoc();
					$rowlocalnombre = $local_['nombre'];
					$monto_anterior = 0;
					if (isset($data2["config"]["monto_anterior"])) {
						$monto_anterior = $data2["config"]["monto_anterior"];
					}
					if ($monto_anterior == "") {
						$monto_anterior = 0;
					}
					$monto = $data2["config"]["monto_inicial"];
					if (!is_numeric($monto)) {
						$monto = str_replace(",", "", $monto);
						if (!is_numeric($monto)) $monto = 0;
					}
					$usuarioElimina = json_decode($row_selected['login'], true);
					$list[$i]['fecha_registro'] = $row_selected["fecha_registro"];
					$list[$i]['monto_anterior'] = number_format($monto_anterior, 2, '.', false);
					$list[$i]['monto_inicial'] = number_format($monto, 2, '.', false);
					$list[$i]['usuario_elimina'] = Utf8_ansi($usuarioElimina['nombre'] . " " . $usuarioElimina['apellido_paterno']);
					$list[$i]['local_nombre'] = '[' . $localID . '] ' . $rowlocalnombre;

					$i++;
				}
			} else {
				while ($row_selected = $cajas_sql_query->fetch_assoc()) {

					$data2 = json_decode($row_selected['data'], true);
					$caja = json_decode($data2['item_id'], true);
					$localID = $caja;
					$local_ = $mysqli->query("SELECT l.id, l.nombre FROM tbl_locales l WHERE l.id = '" . $localID . "'")->fetch_assoc();
					$rowlocalnombre = $local_['nombre'];
					$monto_anterior = 0;
					if (isset($data2["config"]["monto_anterior"])) {
						$monto_anterior = $data2["config"]["monto_anterior"];
					}
					$monto = $data2["config"]["monto_inicial"];
					if ($monto_anterior == "") {
						$monto_anterior = 0;
					}
					$monto = $data2["config"]["monto_inicial"];
					if (!is_numeric($monto)) {
						$monto = str_replace(",", "", $monto);
						if (!is_numeric($monto)) $monto = 0;
					}
					$usuarioElimina = json_decode($row_selected['login'], true);

					if ($local == $localID) {
						$list[$i]['fecha_registro'] = $row_selected["fecha_registro"];
						$list[$i]['monto_anterior'] = number_format($monto_anterior, 2, '.', false);
						$list[$i]['monto_inicial'] = number_format($monto, 2, '.', false);
						$list[$i]['usuario_elimina'] = Utf8_ansi($usuarioElimina['nombre'] . " " . $usuarioElimina['apellido_paterno']);
						$list[$i]['local_nombre'] = '[' . $localID . '] ' . $rowlocalnombre;

						$i++;
					}
				}
			};

			$list_cols = array();
			$list_cols["fecha_registro"] = "FECHA EDICION";
			$list_cols["usuario_elimina"] = "USUARIO EDICION";
			$list_cols["local_nombre"] = "LOCAL";
			$list_cols["monto_anterior"] = "MONTO ANTERIOR";
			$list_cols["monto_inicial"] = "MONTO";


			$list_cols_show = $list_cols;
		} elseif ($export == "tbl_clientes") {
			$list_query = $mysqli->query("SELECT c.id, c.dni, c.nombre, c.ruc, c.razon_social, c.email, c.telefono, c.celular, c.direccion, c.numero_cuenta, c.infocorp
												,tipo.nombre AS cliente_tipo
												,ba.nombre AS banco
											FROM tbl_clientes c
											LEFT JOIN tbl_cliente_tipos tipo ON (tipo.id = c.tipo_cliente_id)
											LEFT JOIN tbl_bancos ba ON (ba.id = c.banco_id)
											WHERE c.estado = '1'
											ORDER BY c.id DESC
											");
			$list = array();
			while ($li = $list_query->fetch_assoc()) {
				($li["infocorp"] ? $li["infocorp"] = "Si" : $li["infocorp"] = "No");
				$list[] = $li;
			}
			$list_cols = array();
			$list_cols["id"] = "ID";
			$list_cols["cliente_tipo"] = "Tipo";
			$list_cols["dni"] = "DNI";
			$list_cols["nombre"] = "Nombre";
			$list_cols["ruc"] = "RUC";
			$list_cols["razon_social"] = "Razon Social";
			$list_cols["email"] = "Correo";
			$list_cols["telefono"] = "Telefono Fijo";
			$list_cols["celular"] = "Telefono Celular";
			$list_cols["direccion"] = "Direccion";
			$list_cols["banco"] = "Banco";
			$list_cols["numero_cuenta"] = "Cuenta";
			$list_cols["infocorp"] = "Infocorp";

			$list_cols_show = $list_cols;
		} elseif ($export == "cont_contrato_servicio_publico") {

			// buscar por periodo
			if ($buscar_por == 1) {

				$id_local = $id_local;
				$id_empresa = $id_empresa;
				$id_jefe_comercial = $id_jefe_comercial;
				$id_supervisor = $id_supervisor;
				$periodo = $periodo;
				$fec_vcto_desde = date("Y-m-d", strtotime($fec_vcto_desde));
				$fec_vcto_hasta = date("Y-m-d", strtotime($fec_vcto_hasta));
				// $pendientes = $btn_pendientes;
				$tipo_servicio = $tipo_servicio;
				$estado = $estado;


				$where_periodo = "";
				$where_local = "";
				$where_empresa = "";
				$where_jefe_comercial = "";
				$where_supervisor = "";
				$having_fec_vcto = "";
				$having_pendientes = "";
				$where_estado = "";
				$where_tipo_servicio = "";

				if ($id_local != 0) {
					$where_local = " AND lc.id = " . $id_local;
				}
				if ($id_empresa != 0) {
					$where_empresa = " AND c.empresa_suscribe_id = " . $id_empresa;
				}
				if ($id_jefe_comercial != 0) {
					$where_jefe_comercial = " AND tus.id = " . $id_jefe_comercial;
				}
				if ($id_supervisor != 0) {
					$where_supervisor = " AND tuss.id = " . $id_supervisor;
				}
				if ($tipo_servicio != 0) {
					$where_tipo_servicio = " AND sp.id_tipo_servicio_publico = " . $tipo_servicio;
				}
				if ($periodo != 0) {
					$anio = substr($periodo, 0, 4);
					$mes = substr($periodo, 5, 2);
					$where_periodo = " AND month(sp.periodo_consumo) = " . $mes . " and year(sp.periodo_consumo) = " . $anio;
				}
				if (!empty($estado) && $estado != 0) {
					if ($estado != 9) {
						$where_estado = " AND sp.estado = " . $estado;
					}
				}


				$query = "
					SELECT 
					lc.id AS local_id,
					IFNULL(lc.cc_id,'0') AS centro_costo, 
					lc.nombre AS local_nombre,
					i.tipo_compromiso_pago_agua,
					i.tipo_compromiso_pago_luz,
					
					concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS jefe_comercial,
					concat( IFNULL(tpp.nombre, ''),' ', IFNULL(tpp.apellido_paterno, ''),' ', IFNULL(tpp.apellido_materno, '')) AS supervisor,
					r.nombre AS empresa
					
					FROM tbl_locales AS lc
					INNER JOIN cont_contrato AS c ON c.contrato_id = lc.contrato_id
					INNER JOIN cont_inmueble AS  i ON i.contrato_id = c.contrato_id
					INNER JOIN tbl_razon_social AS  r ON r.id = c.empresa_suscribe_id
					
					LEFT JOIN tbl_usuarios_locales tuls ON tuls.local_id = lc.id AND tuls.estado = 1
					LEFT JOIN tbl_usuarios tus ON tuls.usuario_id = tus.id AND tus.estado = 1
					INNER JOIN tbl_personal_apt tp ON tp.id = tus.personal_id AND tp.area_id = 21 AND tp.cargo_id = 16 AND tp.estado = 1
					
					LEFT JOIN tbl_usuarios_locales tulss ON tulss.local_id = lc.id AND tulss.estado = 1
					LEFT JOIN tbl_usuarios tuss ON tulss.usuario_id = tuss.id AND tuss.estado = 1
					INNER JOIN tbl_personal_apt tpp ON tpp.id = tuss.personal_id AND tpp.area_id = 21 AND tpp.cargo_id = 4 AND tpp.estado = 1

					
					WHERE lc.red_id = 1
					AND lc.estado = 1
					AND ((i.tipo_compromiso_pago_agua <> 8 AND i.tipo_compromiso_pago_agua <> 0 ) OR 
					(i.tipo_compromiso_pago_luz <> 8 AND i.tipo_compromiso_pago_luz <> 0 ))

					" . $where_local . "
					" . $where_empresa . "
					" . $where_jefe_comercial . "
					" . $where_supervisor . "
				
					GROUP BY lc.id 
					ORDER BY local_nombre ASC
					";
				$result = array();

				// echo $query;
				// exit();
				$list_query = $mysqli->query($query);

				$list_proc_registros = array();
				while ($li = $list_query->fetch_assoc()) {

					$query_sp = "
						SELECT sp.id, sp.id_tipo_servicio_publico, sp.periodo_consumo, sp.fecha_emision, sp.fecha_vencimiento, sp.monto_total, sp.estado,
						esp.nombre as nombre_estado 
						FROM cont_local_servicio_publico AS sp
						LEFT JOIN cont_tipo_estado_servicio_publico AS esp ON sp.estado = esp.id
						WHERE sp.status = 1
						AND sp.id_local = " . $li['local_id'] . "
						" . $where_tipo_servicio . "
						" . $where_periodo . "
						ORDER BY sp.id DESC
						";


					$list_query_sp = $mysqli->query($query_sp);
					$recibo = array('id_tipo_servicio_publico' => '', 'periodo_consumo' => '', 'id_estado_luz' => '', 'id_estado_agua' => '', 'fec_vcto_recibo_agua' => '', 'fec_vcto_recibo_luz' => '', 'estado_recibo_agua' => '', 'estado_recibo_luz' => '', 'id_recibo_agua' => '', 'id_recibo_luz' => '', 'recibo_agua' => '', 'recibo_luz' => '');
					while ($sp = $list_query_sp->fetch_assoc()) {
						if ($sp['id_tipo_servicio_publico'] == 2) {
							$recibo['id_estado_agua'] =  $sp['estado'];
							$recibo['estado_recibo_agua'] =  $sp['nombre_estado'];
							$recibo['id_recibo_agua'] = $sp['id'];
							$recibo['recibo_agua'] = $sp['monto_total'];
							$recibo['fec_vcto_recibo_agua'] = $sp['fecha_vencimiento'];
						}
						if ($sp['id_tipo_servicio_publico'] == 1) {
							$recibo['id_estado_luz'] =  $sp['estado'];
							$recibo['estado_recibo_luz'] =  $sp['nombre_estado'];
							$recibo['id_recibo_luz'] = $sp['id'];
							$recibo['recibo_luz'] = $sp['monto_total'];
							$recibo['fec_vcto_recibo_luz'] = $sp['fecha_vencimiento'];
						}
						$recibo['periodo_consumo'] = $sp['periodo_consumo'];
						$recibo['id_tipo_servicio_publico'] = $sp['id_tipo_servicio_publico'];
					}

					$agregar = false;
					if ($estado == 0) { //SE AGREGA TODOS
						$agregar = true;
					} else if ($estado == 9) { // SIN RECIBO
						if ($tipo_servicio == 0) {
							$agregar = $recibo['id_estado_agua'] == "" || $recibo['id_estado_luz'] == "" ? true : false;
						} else if ($tipo_servicio == 1) {
							$agregar = $recibo['id_estado_luz'] == "" ? true : false;
						} else if ($tipo_servicio == 2) {
							$agregar = $recibo['id_estado_agua'] == "" ? true : false;
						}
					} else {
						if ($recibo['id_estado_agua'] == $estado || $recibo['id_estado_luz'] == $estado) {
							$agregar = true;
						}
					}
					if ($agregar) {
						array_push($list_proc_registros, array(
							'jefe_comercial' => $li['jefe_comercial'],
							'centro_costo' => $li['centro_costo'],
							'local_id' => $li['local_id'],
							'local_nombre' => $li['local_nombre'],
							'supervisor' => $li['supervisor'],
							'empresa' => $li['empresa'],
							'periodo_consumo' => $recibo['periodo_consumo'],
							'fec_vcto_recibo_luz' => $recibo['fec_vcto_recibo_luz'],
							'fec_vcto_recibo_agua' => $recibo['fec_vcto_recibo_agua'],
							'tipo_compromiso_pago_agua' => $li['tipo_compromiso_pago_agua'],
							'tipo_compromiso_pago_luz' => $li['tipo_compromiso_pago_luz'],
							'estado_recibo_agua' => !empty($recibo['estado_recibo_agua']) ? $recibo['estado_recibo_agua'] : 'SIN RECIBO',
							'estado_recibo_luz' => !empty($recibo['estado_recibo_luz']) ? $recibo['estado_recibo_luz'] : 'SIN RECIBO',
							'id_recibo_agua' => $recibo['id_recibo_agua'],
							'id_recibo_luz' => $recibo['id_recibo_luz'],
							'recibo_agua' => $recibo['recibo_agua'],
							'recibo_luz' => $recibo['recibo_luz'],
							'id_estado_agua' => $recibo['id_estado_agua'],
							'id_estado_luz' => $recibo['id_estado_luz'],
						));
					}
				}

				$meses_abrev = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
				$list = array();
				for ($i = 0; $i < count($list_proc_registros); $i++) {
					$anio = intval(date("Y", strtotime($list_proc_registros[$i]['periodo_consumo'])));
					$mes = intval(date("m", strtotime($list_proc_registros[$i]['periodo_consumo'])));
					$li['periodo_consumo'] = $meses_abrev[$mes - 1] . " del " . $anio;
					$list[] = $list_proc_registros[$i];
				}
				$list_cols = array();
				$list_cols["centro_costo"] = "C.C.";
				$list_cols["local_nombre"] = "Local";
				$list_cols["empresa"] = "Empresa";
				$list_cols["jefe_comercial"] = "Jefe Comercial";
				$list_cols["supervisor"] = "Supervisor";
				$list_cols["periodo_consumo"] = "Periodo";
				if ($tipo_servicio == 0 || $tipo_servicio == 1) {
					$list_cols["recibo_luz"] = "Servicio de Luz";
					$list_cols["fec_vcto_recibo_luz"] = "Fec. Vcto. Luz";
					$list_cols["estado_recibo_luz"] = "Estado Luz";
				}

				if ($tipo_servicio == 0 || $tipo_servicio == 2) {
					$list_cols["recibo_agua"] = "Servicio de Agua";
					$list_cols["fec_vcto_recibo_agua"] = "Fec. Vcto. Agua";
					$list_cols["estado_recibo_agua"] = "Estado Agua";
				}
				$list_cols_show = $list_cols;
			} else if ($buscar_por == 2) { //buscar por fechas

				$id_local = $id_local;
				$id_empresa = $id_empresa;
				$id_jefe_comercial = $id_jefe_comercial;
				$id_supervisor = $id_supervisor;
				$periodo = $periodo;
				$fec_vcto_desde = date("Y-m-d", strtotime($fec_vcto_desde));
				$fec_vcto_hasta = date("Y-m-d", strtotime($fec_vcto_hasta));
				// $pendientes = $btn_pendientes;
				$tipo_servicio = $tipo_servicio;
				$estado = $estado;


				$where_periodo = "";
				$where_local = "";
				$where_empresa = "";
				$where_jefe_comercial = "";
				$where_supervisor = "";
				$having_fec_vcto = "";
				$having_pendientes = "";
				$where_estado = "";
				$where_tipo_servicio = "";

				if ($id_local != 0) {
					$where_local = " AND lc.id = " . $id_local;
				}
				if ($id_empresa != 0) {
					$where_empresa = " AND c.empresa_suscribe_id = " . $id_empresa;
				}
				if ($id_jefe_comercial != 0) {
					$where_jefe_comercial = " AND tus.id = " . $id_jefe_comercial;
				}
				if ($id_supervisor != 0) {
					$where_supervisor = " AND tuss.id = " . $id_supervisor;
				}
				if ($tipo_servicio != 0) {
					$where_tipo_servicio = " AND sp.id_tipo_servicio_publico = " . $tipo_servicio;
				}
				if ($periodo != 0) {
					$anio = substr($periodo, 0, 4);
					$mes = substr($periodo, 5, 2);
					$where_periodo = " AND month(sp.periodo_consumo) = " . $mes . " and year(sp.periodo_consumo) = " . $anio;
				}
				if (!empty($estado) && $estado != 0) {
					if ($estado != 9) {
						$where_estado = " AND sp.estado = " . $estado;
					}
				}
				if ($fec_vcto_desde != "" && $fec_vcto_hasta != "") {
					$having_fec_vcto = " AND (sp.fecha_vencimiento between '" . $fec_vcto_desde . "' and '" . $fec_vcto_hasta . "')";
				}


				$query = "SELECT sp.id, sp.id_tipo_servicio_publico, sp.periodo_consumo, sp.fecha_emision, sp.fecha_vencimiento, sp.monto_total, sp.estado,
						esp.nombre as nombre_estado,
						tl.id AS local_id,ifnull(tl.cc_id,'0') AS centro_costo, tl.nombre local_nombre,
					i.tipo_compromiso_pago_agua,
					i.tipo_compromiso_pago_luz,
					
					concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS jefe_comercial,
					concat( IFNULL(tpp.nombre, ''),' ', IFNULL(tpp.apellido_paterno, ''),' ', IFNULL(tpp.apellido_materno, '')) AS supervisor,
					tsp.nombre as tipo_servicio, r.nombre as empresa 
					
					FROM cont_local_servicio_publico AS sp
					INNER JOIN cont_tipo_servicio_publico AS tsp ON sp.id_tipo_servicio_publico = tsp.id
					INNER JOIN tbl_locales  AS tl  ON sp.id_local = tl.id
					INNER JOIN cont_contrato AS c ON c.contrato_id = tl.contrato_id
					INNER JOIN cont_inmueble AS  i ON i.contrato_id = c.contrato_id
					INNER JOIN tbl_razon_social AS r ON r.id = c.empresa_suscribe_id 

				

					LEFT JOIN tbl_usuarios_locales tuls ON tuls.local_id = tl.id AND tuls.estado = 1
					LEFT JOIN tbl_usuarios tus ON tuls.usuario_id = tus.id AND tus.estado = 1
					INNER JOIN tbl_personal_apt tp ON tp.id = tus.personal_id AND tp.area_id = 21 AND tp.cargo_id = 16 AND tp.estado = 1
					
					LEFT JOIN tbl_usuarios_locales tulss ON tulss.local_id = tl.id AND tulss.estado = 1
					LEFT JOIN tbl_usuarios tuss ON tulss.usuario_id = tuss.id AND tuss.estado = 1
					INNER JOIN tbl_personal_apt tpp ON tpp.id = tuss.personal_id AND tpp.area_id = 21 AND tpp.cargo_id = 4 AND tpp.estado = 1



					LEFT JOIN cont_tipo_estado_servicio_publico AS esp ON sp.estado = esp.id
					WHERE sp.status = 1 and (esp.id <> 3 AND esp.id <> 5)
					" . $where_local . "
					" . $where_empresa . "
					" . $where_jefe_comercial . "
					" . $where_supervisor . "
					" . $where_tipo_servicio . "
					" . $where_estado . "
					" . $having_fec_vcto . "
					GROUP BY sp.id
					";

				$list_query = $mysqli->query($query);
				$list_proc_registros = array();
				while ($sp = $list_query->fetch_assoc()) {
					array_push($list_proc_registros, array(
						'id_tipo_servicio_publico' => $sp['id_tipo_servicio_publico'],
						'tipo_servicio' => $sp['tipo_servicio'],
						'jefe_comercial' => $sp['jefe_comercial'],
						'centro_costo' => $sp['centro_costo'],
						'local_id' => $sp['local_id'],
						'local_nombre' => $sp['local_nombre'],
						'supervisor' => $sp['supervisor'],
						'empresa' => $sp['empresa'],
						'periodo_consumo' => $sp['periodo_consumo'],
						'fecha_vencimiento' => $sp['fecha_vencimiento'],
						'tipo_compromiso_pago_agua' => $sp['tipo_compromiso_pago_agua'],
						'tipo_compromiso_pago_luz' => $sp['tipo_compromiso_pago_luz'],
						'estado' => $sp['nombre_estado'],
						'id_recibo' => $sp['id'],
						'monto_total' => $sp['monto_total'],
					));
				}
				$meses_abrev = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
				$list = array();
				for ($i = 0; $i < count($list_proc_registros); $i++) {
					$anio = intval(date("Y", strtotime($list_proc_registros[$i]['periodo_consumo'])));
					$mes = intval(date("m", strtotime($list_proc_registros[$i]['periodo_consumo'])));
					$li['periodo_consumo'] = $meses_abrev[$mes - 1] . " del " . $anio;
					$list[] = $list_proc_registros[$i];
				}
				$list_cols = array();
				$list_cols["centro_costo"] = "C. C.";
				$list_cols["local_nombre"] = "Local";
				$list_cols["jefe_comercial"] = "Jefe Comercial";
				$list_cols["empresa"] = "Empresa";
				$list_cols["supervisor"] = "Supervisor";
				$list_cols["periodo_consumo"] = "Periodo";
				$list_cols["tipo_servicio"] = "Tipo Servicio";
				$list_cols["monto_total"] = "Monto";
				$list_cols["fecha_vencimiento"] = "Fec. Vcto.";
				$list_cols["estado"] = "Estado";

				$list_cols_show = $list_cols;
			}
		} elseif ($export == "cont_contrato_servicio_publico_tesoreria") {


			$id_local = $local_id;
			$id_empresa = $id_empresa;
			$id_jefe_comercial = $id_jefe_comercial;
			$id_supervisor = $id_supervisor;
			$periodo = $periodo;
			$fec_vcto_desde = date("Y-m-d", strtotime($fec_vcto_desde));
			$fec_vcto_hasta = date("Y-m-d", strtotime($fec_vcto_hasta));
			// $pendientes = $btn_pendientes;
			$tipo_servicio = $tipo_servicio;
			$estado = $estado;

			$where_periodo = "";
			$where_local = "";
			$where_empresa = "";
			$where_jefe_comercial = "";
			$where_supervisor = "";
			$having_fec_vcto = "";
			$having_pendientes = "";
			$where_estado = "";
			$where_tipo_servicio = "";

			if ($id_local != 0) {
				$where_local = " AND tl.id = " . $id_local;
			}
			if ($id_empresa != 0) {
				$where_empresa = " AND c.empresa_suscribe_id = " . $id_empresa;
			}
			if ($id_jefe_comercial != 0) {
				$where_jefe_comercial = " AND tus.id = " . $id_jefe_comercial;
			}
			if ($id_supervisor != 0) {
				$where_supervisor = " AND tuss.id = " . $id_supervisor;
			}
			if ($tipo_servicio != 0) {
				$where_tipo_servicio = " AND sp.id_tipo_servicio_publico = " . $tipo_servicio;
			}
			if ($periodo != 0 && $buscar_por == 1) {
				$anio = substr($periodo, 0, 4);
				$mes = substr($periodo, 5, 2);
				$where_periodo = " AND month(sp.periodo_consumo) = " . $mes . " and year(sp.periodo_consumo) = " . $anio;
			}

			if ($fec_vcto_desde != "" && $fec_vcto_hasta != "" && $buscar_por == 2) {
				$having_fec_vcto = " AND (sp.fecha_vencimiento between '" . $fec_vcto_desde . "' and '" . $fec_vcto_hasta . "')";
			}



			$query = "SELECT sp.id, sp.id_tipo_servicio_publico, sp.periodo_consumo, sp.fecha_emision, sp.fecha_vencimiento, sp.total_pagar, sp.monto_total, sp.estado,
				esp.nombre as nombre_estado,
				tl.id AS local_id,IFNULL(tl.cc_id,'0') AS centro_costo, tl.nombre AS local_nombre,
				i.tipo_compromiso_pago_agua,
				i.tipo_compromiso_pago_luz,
				
				concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS jefe_comercial,
				concat( IFNULL(tpp.nombre, ''),' ', IFNULL(tpp.apellido_paterno, ''),' ', IFNULL(tpp.apellido_materno, '')) AS supervisor,
				tsp.nombre as tipo_servicio, r.nombre AS empresa,
				case when sp.id_tipo_servicio_publico = 1 
				then IFNULL(i.num_suministro_luz,'') else IFNULL(i.num_suministro_agua,'') end as numero_suministro
				
				FROM cont_local_servicio_publico AS sp
				INNER JOIN cont_tipo_servicio_publico AS tsp ON sp.id_tipo_servicio_publico = tsp.id
				INNER JOIN tbl_locales  AS tl  ON sp.id_local = tl.id
				INNER JOIN cont_contrato AS c ON c.contrato_id = tl.contrato_id
				INNER JOIN cont_inmueble AS  i ON i.contrato_id = c.contrato_id
				INNER JOIN tbl_razon_social AS r ON r.id = c.empresa_suscribe_id 
			
				LEFT JOIN tbl_usuarios_locales tuls ON tuls.local_id = tl.id AND tuls.estado = 1
				LEFT JOIN tbl_usuarios tus ON tuls.usuario_id = tus.id AND tus.estado = 1
				INNER JOIN tbl_personal_apt tp ON tp.id = tus.personal_id AND tp.area_id = 21 
				AND tp.cargo_id = 16 AND tp.estado = 1
			
				LEFT JOIN tbl_usuarios_locales tulss ON tulss.local_id = tl.id AND tulss.estado = 1
				LEFT JOIN tbl_usuarios tuss ON tulss.usuario_id = tuss.id AND tuss.estado = 1
				INNER JOIN tbl_personal_apt tpp ON tpp.id = tuss.personal_id AND tpp.area_id = 21 
				AND tpp.cargo_id = 4 AND tpp.estado = 1
			
			
				LEFT JOIN cont_tipo_estado_servicio_publico AS esp ON sp.estado = esp.id
				WHERE sp.status = 1 AND sp.estado = 2
				" . $where_local . "
				" . $where_empresa . "
				" . $where_jefe_comercial . "
				" . $where_supervisor . "
				" . $where_tipo_servicio . "
				" . $where_periodo . "
				" . $having_fec_vcto . "
				GROUP BY sp.id
				";


			$list_query = $mysqli->query($query);
			$list_proc_registros = array();
			while ($sp = $list_query->fetch_assoc()) {
				array_push($list_proc_registros, array(
					'id_tipo_servicio_publico' => $sp['id_tipo_servicio_publico'],
					'tipo_servicio' => $sp['tipo_servicio'],
					'jefe_comercial' => $sp['jefe_comercial'],
					'centro_costo' => $sp['centro_costo'],
					'local_id' => $sp['local_id'],
					'local_nombre' => $sp['local_nombre'],
					'supervisor' => $sp['supervisor'],
					'numero_suministro' => $sp['numero_suministro'],

					'empresa' => $sp['empresa'],
					'periodo_consumo' => $sp['periodo_consumo'],
					'fecha_vencimiento' => $sp['fecha_vencimiento'],
					'tipo_compromiso_pago_agua' => $sp['tipo_compromiso_pago_agua'],
					'tipo_compromiso_pago_luz' => $sp['tipo_compromiso_pago_luz'],
					'estado' => $sp['nombre_estado'],
					'id_recibo' => $sp['id'],
					'monto_total' => $sp['total_pagar'],
				));
			}

			$meses_abrev = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$anio = intval(date("Y", strtotime($list_proc_registros[$i]['periodo_consumo'])));
				$mes = intval(date("m", strtotime($list_proc_registros[$i]['periodo_consumo'])));
				$li['periodo_consumo'] = $meses_abrev[$mes - 1] . " del " . $anio;
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols["centro_costo"] = "C. C.";
			$list_cols["local_nombre"] = "Local";
			$list_cols["jefe_comercial"] = "Jefe Comercial";
			$list_cols["empresa"] = "Empresa";
			$list_cols["supervisor"] = "Supervisor";
			$list_cols["periodo_consumo"] = "Periodo";
			$list_cols["tipo_servicio"] = "Tipo Servicio";
			$list_cols["monto_total"] = "Monto";
			$list_cols["fecha_vencimiento"] = "Fec. Vcto.";
			$list_cols["estado"] = "Estado";

			$list_cols_show = $list_cols;
		} elseif ($export == "reporte_servicio_publico") {

			$id_local = $id_local;
			$id_empresa = $id_empresa;
			$id_jefe_comercial = $id_jefe_comercial;
			$id_supervisor = $id_supervisor;
			$periodo = $periodo;
			$fec_vcto_desde = date("Y-m-d", strtotime($fec_vcto_desde));
			$fec_vcto_hasta = date("Y-m-d", strtotime($fec_vcto_hasta));
			$tipo_servicio = $tipo_servicio;
			$estado = $estado;

			$where_periodo = "";
			$where_local = "";
			$where_empresa = "";
			$where_jefe_comercial = "";
			$where_supervisor = "";
			$having_fec_vcto = "";
			$having_pendientes = "";
			$where_estado = "";
			$where_tipo_servicio = "";

			if ($id_local != 0) {
				$where_local = " AND tl.id = " . $id_local;
			}
			if ($id_empresa != 0) {
				$where_empresa = " AND tl.razon_social_id = " . $id_empresa;
			}
			if ($id_jefe_comercial != 0) {
				$where_jefe_comercial = " AND tus.id = " . $id_jefe_comercial;
			}
			if ($id_supervisor != 0) {
				$where_supervisor = " AND tuss.id = " . $id_supervisor;
			}
			if ($tipo_servicio != 0) {
				$where_tipo_servicio = " AND sp.id_tipo_servicio_publico = " . $tipo_servicio;
			}
			if ($periodo != 0 && $buscar_por == 1) {
				$anio = substr($periodo, 0, 4);
				$mes = substr($periodo, 5, 2);
				$where_periodo = " AND month(sp.periodo_consumo) = " . $mes . " and year(sp.periodo_consumo) = " . $anio;
			}

			if ($fec_vcto_desde != "" && $fec_vcto_hasta != "" && $buscar_por == 2) {
				$having_fec_vcto = " AND (sp.fecha_vencimiento between '" . $fec_vcto_desde . "' and '" . $fec_vcto_hasta . "')";
			}
			if ($fec_vcto_desde != "" && $fec_vcto_hasta != "" && $buscar_por == 3) {
				$having_fec_vcto = " AND (sp.fecha_pago between '" . $fec_vcto_desde . "' and '" . $fec_vcto_hasta . "')";
			}

			if ($estado != 0) {
				$where_estado = " AND sp.estado = " . $estado;
			}



			$query = "SELECT sp.id, c.contrato_id, sp.id_tipo_servicio_publico, sp.periodo_consumo, sp.fecha_emision, sp.fecha_vencimiento, sp.fecha_pago, sp.total_pagar, sp.monto_total, sp.estado,
				esp.nombre as nombre_estado,
				tl.id AS local_id,IFNULL(tl.cc_id,'0') AS centro_costo, tl.nombre AS local_nombre,
				i.tipo_compromiso_pago_agua,
				i.tipo_compromiso_pago_luz,
				
				concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS jefe_comercial,
				concat( IFNULL(tpp.nombre, ''),' ', IFNULL(tpp.apellido_paterno, ''),' ', IFNULL(tpp.apellido_materno, '')) AS supervisor,
				tsp.nombre as tipo_servicio, r.nombre AS empresa,
				case when sp.id_tipo_servicio_publico = 1 
				then IFNULL(i.num_suministro_luz,'') else IFNULL(i.num_suministro_agua,'') end as numero_suministro,
				case when sp.id_tipo_servicio_publico = 1
				then IFNULL(lspe2.razon_social,'') else IFNULL(lspe1.razon_social,'') end as empresa_servicio,
				case when sp.id_tipo_servicio_publico = 1
				then IFNULL(lspe2.ruc,'') else IFNULL(lspe1.ruc,'') end as ruc_servicio
				
				
				FROM cont_local_servicio_publico AS sp
				INNER JOIN cont_tipo_servicio_publico AS tsp ON sp.id_tipo_servicio_publico = tsp.id
				INNER JOIN tbl_locales  AS tl  ON sp.id_local = tl.id
				INNER JOIN cont_contrato AS c ON c.contrato_id = tl.contrato_id
				INNER JOIN cont_inmueble AS  i ON i.contrato_id = c.contrato_id
				INNER JOIN tbl_razon_social AS r ON r.id = tl.razon_social_id 
			
				LEFT JOIN tbl_usuarios_locales tuls ON tuls.local_id = tl.id AND tuls.estado = 1
				LEFT JOIN tbl_usuarios tus ON tuls.usuario_id = tus.id AND tus.estado = 1
				INNER JOIN tbl_personal_apt tp ON tp.id = tus.personal_id AND tp.area_id = 21 
				AND tp.cargo_id = 16 AND tp.estado = 1
			
				LEFT JOIN tbl_usuarios_locales tulss ON tulss.local_id = tl.id AND tulss.estado = 1
				LEFT JOIN tbl_usuarios tuss ON tulss.usuario_id = tuss.id AND tuss.estado = 1
				INNER JOIN tbl_personal_apt tpp ON tpp.id = tuss.personal_id AND tpp.area_id = 21 
				AND tpp.cargo_id = 4 AND tpp.estado = 1

				LEFT JOIN cont_local_servicio_publico_empresas lspe1 ON i.id_empresa_servicio_agua = lspe1.id
				LEFT JOIN cont_local_servicio_publico_empresas lspe2 ON i.id_empresa_servicio_luz = lspe2.id
			
				LEFT JOIN cont_tipo_estado_servicio_publico AS esp ON sp.estado = esp.id
				WHERE sp.status = 1
				" . $where_local . "
				" . $where_empresa . "
				" . $where_jefe_comercial . "
				" . $where_supervisor . "
				" . $where_tipo_servicio . "
				" . $where_periodo . "
				" . $where_estado . "
				" . $having_fec_vcto . "
				GROUP BY sp.id
				";


			$list_query = $mysqli->query($query);
			$list_proc_registros = array();
			while ($sp = $list_query->fetch_assoc()) {

				array_push($list_proc_registros, array(

					'id_tipo_servicio_publico' => $sp['id_tipo_servicio_publico'],
					'tipo_servicio' => $sp['tipo_servicio'],
					'jefe_comercial' => $sp['jefe_comercial'],
					'centro_costo' => $sp['centro_costo'],
					'local_id' => $sp['local_id'],
					'local_nombre' => $sp['local_nombre'],
					'supervisor' => $sp['supervisor'],
					'numero_suministro' => $sp['numero_suministro'],
					'ruc_servicio' => $sp['ruc_servicio'],
					'empresa_servicio' => $sp['empresa_servicio'],

					'empresa' => $sp['empresa'],
					'periodo_consumo' => $sp['periodo_consumo'],
					'fecha_vencimiento' => $sp['fecha_vencimiento'],
					'fecha_pago' => $sp['fecha_pago'],
					'tipo_compromiso_pago_agua' => $sp['tipo_compromiso_pago_agua'],
					'tipo_compromiso_pago_luz' => $sp['tipo_compromiso_pago_luz'],
					'estado' => $sp['nombre_estado'],
					'id_recibo' => $sp['id'],
					'monto_total' => $sp['total_pagar'],
				));
			}

			$meses_abrev = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$anio = intval(date("Y", strtotime($list_proc_registros[$i]['periodo_consumo'])));
				$mes = intval(date("m", strtotime($list_proc_registros[$i]['periodo_consumo'])));
				$li['periodo_consumo'] = $meses_abrev[$mes - 1] . " del " . $anio;
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols["centro_costo"] = "C. C.";
			$list_cols["local_nombre"] = "Local";
			$list_cols["empresa"] = "Razón Social";
			// $list_cols["jefe_comercial"]="Jefe Comercial";
			// $list_cols["supervisor"]="Supervisor";
			$list_cols["periodo_consumo"] = "Periodo";
			$list_cols["ruc_servicio"] = "Ruc Servicio";
			$list_cols["empresa_servicio"] = "Empresa Servicio";
			$list_cols["tipo_servicio"] = "Tipo Servicio";
			$list_cols["numero_suministro"] = "Nro Suministro";
			$list_cols["fecha_vencimiento"] = "Fec. Vcto.";
			$list_cols["fecha_pago"] = "Fec. Pago";
			$list_cols["monto_total"] = "Monto";
			$list_cols["estado"] = "Estado";

			$list_cols_show = $list_cols;
		} elseif ($export == "solicitud_derivados_at") {

			$where_estado = "";
			$where_zona = "";
			$where_tienda = "";
			$where_fecha_inicio = "";
			$where_fecha_fin = "";


			if (!empty($busqueda_estado)) {
				$where_estado = " AND inci.estado_vt  in ('" . str_replace(",", "','", $busqueda_estado) . "')";
			}

			if (!empty($busqueda_zona)) {
				$where_zona = " AND z.nombre in ('" . str_replace(",", "','", $busqueda_zona) . "')";
			}

			if (!empty($busqueda_tienda)) {
				$where_tienda = " AND  l.nombre in ('" . str_replace(",", "','", $busqueda_tienda) . "')";
			}

			if (!empty($busqueda_fecha_inicio)) {
				$where_fecha_inicio = " AND inci.created_at >= '" . $busqueda_fecha_inicio . "'";
			}

			if (!empty($busqueda_fecha_fin)) {
				$where_fecha_fin = " AND inci.created_at <= date_add('" . $busqueda_fecha_fin . "', interval 1 day)";
			}


			$query = " 
				SELECT 
				inci.created_at,
				inci.id AS id,
				z.nombre AS zona,
				l.nombre AS local,
				inci.incidencia_txt, 
				inci.equipo,
				inci.nota_tecnico,
				inci.recomendacion,
				inci.estado_vt,
				inci.fecha_cierre_vt    
				FROM wwwapuestatotal_gestion.tbl_soporte_incidencias inci     
				LEFT JOIN tbl_locales l ON  l.id = inci.local_id    
				LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
				where inci.recomendacion = 'Visita Técnica' and inci.estado=1
			
				" . $where_estado . "
				" . $where_zona . " 
				" . $where_tienda . "
				" . $where_fecha_inicio . "
				" . $where_fecha_fin . " 
			
				order by inci.created_at DESC
				";
			$list_query = $mysqli->query($query);
			$list_proc_registros = array();
			while ($sp = $list_query->fetch_assoc()) {

				$estado = trim($sp['estado_vt']);
				if (empty($estado)) {
					$estado = "Derivado";
				}

				array_push($list_proc_registros, array(

					'created_at' => $sp['created_at'],
					'zona' => $sp['zona'],
					'local' => $sp['local'],
					'incidencia_txt' => $sp['incidencia_txt'],
					'equipo' => $sp['equipo'],
					'recomendacion' => $sp['recomendacion'],
					'nota_tecnico' => $sp['nota_tecnico'],
					'estado_vt' => $estado,
					'fecha_cierre_vt' => $sp['fecha_cierre_vt'],

				));
			}
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols["created_at"] = "Fecha Ingreso";
			$list_cols["zona"] = "Zona";
			$list_cols["local"] = "Tienda";
			$list_cols["incidencia_txt"] = "Descrip. Incidente";
			$list_cols["equipo"] = "Equipo";
			$list_cols["recomendacion"] = "Recomendación";
			$list_cols["nota_tecnico"] = "Nota para el técnico";
			$list_cols["estado_vt"] = "Estado";
			$list_cols["fecha_cierre_vt"] = "Fecha Cierre";
			$list_cols_show = $list_cols;
		} elseif ($export == "reporte_tipo_balance") {


			$where_fecha_inicio = "";
			$where_fecha_fin = "";
			$where_tipo_transaccion = "";
			$where_local_balance = "";
			$where_cajero_balance = "";
			$where_tipo_balance = "";
			$where_motivo_balance = "";
			$where_juego_balance = "";


			if (!empty($fecha_inicio)) {
				$where_fecha_inicio .= "  AND tra.created_at >= '" . $fecha_inicio . "'";
			}

			if (!empty($fecha_fin)) {
				$where_fecha_fin .= "  AND tra.created_at <= date_add('" . $fecha_fin . "', interval 1 day)";
			}


			if ((int) $tipo_transaccion > 0) {
				$where_tipo_transaccion = " AND tra.tipo_id='" . $tipo_transaccion . "' ";
			}

			if (!empty($local) && $local != "0") {

				$where_local_balance = " AND ( loc.cc_id='" . $local . "' OR ce_ssql.cc_id='" . $local . "' ) ";
			}

			if (!empty($cajero) && $cajero != "0") {
				$where_cajero_balance .= "  AND tra.id_cajero_balance = '" . $cajero . "'";
			}


			if ((int) $tipo_balance > 0) {
				$where_tipo_balance .= "  AND tra.id_tipo_balance = '" . $tipo_balance . "'";
			}



			if ((int) $motivo_balance > 0) {
				$where_motivo_balance .= "  AND tra.id_motivo_balance = '" . $motivo_balance . "'";
			}

			if ((int) $juego_balance > 0) {
				$where_juego_balance .= "  AND tra.id_juego_balance = '" . $juego_balance . "'";
			}

			$query = "SELECT
				tra.id, 
				tra.tipo_id cod_tipo_transaccion,
				ttt.nombre tipo_transaccion,
				tra.created_at fecha_hora_registro, 
				IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente, 
				IFNULL(cli.num_doc, '') num_doc,
				IFNULL(cli.web_id, '') web_id,
				IFNULL(tra.monto, 0) AS monto,	 
				UPPER(IFNULL(loc.nombre, ce_ssql.nombre)) local_cierre,
				IFNULL(caj.id, ce.id) id_turno_cierre,
				IFNULL(caj.turno_id, ce.turno_id) turno_cierre, 
				IFNULL(tra.observacion_cajero, '') observacion_cajero,
				mtv.motivo,
				jg.nombre as juego_balance,
				sp_bl.nombre as supervisor_balance,
				tra.id_tipo_balance,
				tra.id_tj_balance as id_trans_juego,
				tra.observacion_cajero,
				CONCAT(
				IF
					( LENGTH( pl_bl.apellido_paterno ) > 0, CONCAT( UPPER( pl_bl.apellido_paterno ), ' ' ), '' ),
				IF
					( LENGTH( pl_bl.apellido_materno ) > 0, CONCAT( UPPER( pl_bl.apellido_materno ), ' ' ), '' ),
				IF
					( LENGTH( pl_bl.nombre ) > 0, UPPER( pl_bl.nombre ), '' ) 
				) cajero_balance  
			FROM
				tbl_televentas_clientes_transaccion tra
				LEFT JOIN tbl_televentas_clientes_tipo_transaccion ttt ON ttt.id = tra.tipo_id
				LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id  
				LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id 
				LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
				LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
				LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id  
				LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
				LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 	 
				LEFT JOIN tbl_televentas_motivo_balances mtv ON mtv.id = tra.id_motivo_balance
				LEFT JOIN tbl_televentas_tipo_juego jg ON jg.id = tra.id_juego_balance
				LEFT JOIN tbl_personal_apt sp_bl ON sp_bl.id = tra.id_supervisor_balance
				LEFT JOIN tbl_usuarios usu_bl ON usu_bl.id = tra.id_cajero_balance
				LEFT JOIN tbl_personal_apt pl_bl ON pl_bl.id = usu_bl.personal_id
			WHERE
			(
				(tra.tipo_id IN (1,2,4,5,14,15,17,18,19,20) and tra.estado=1) 
				OR (tra.tipo_id = 9 and tra.estado = 2)
				OR (tra.tipo_id = 21 and tra.estado = 2)
				OR (tra.tipo_id = 14 and tra.estado <> 3)
				OR (tra.tipo_id = 32)
			) AND tra.tipo_id in (17, 18)
			
			$where_tipo_transaccion 
			$where_local_balance 
			$where_cajero_balance 
			$where_tipo_balance 
			$where_motivo_balance 
			$where_juego_balance 
			$where_fecha_inicio 
			$where_fecha_fin 

			 ORDER BY tra.id desc 
				";
			$list_query = $mysqli->query($query);
			$list_proc_registros = array();

			$i = 0;
			while ($sp = $list_query->fetch_assoc()) {
				$i = $i + 1;

				if ($sp['id_tipo_balance'] == 4) {
					$nombre_tipo_balance = 'Balance NO RETIRABLE';
				} elseif ($sp['id_tipo_balance'] == 5) {
					$nombre_tipo_balance = 'Balance RETIRABLE';
				} elseif ($sp['id_tipo_balance'] == 6) {
					$nombre_tipo_balance = 'Bono AT';
				} else {
					$nombre_tipo_balance = '';
				}



				array_push($list_proc_registros, array(
					'n' => $i,
					'fecha_hora_registro' => $sp['fecha_hora_registro'],
					'nombre_tipo_balance' => $nombre_tipo_balance,
					'tipo_transaccion' => $sp['tipo_transaccion'],
					'motivo' => $sp['motivo'],
					'juego_balance' => $sp['juego_balance'],
					'id_trans_juego' => $sp['id_trans_juego'],
					'cliente' => $sp['cliente'],
					'num_doc' => $sp['num_doc'],
					'web_id' => $sp['web_id'],
					'local_cierre' => $sp['local_cierre'],
					'supervisor_balance' => $sp['supervisor_balance'],
					'cajero_balance' => $sp['cajero_balance'],
					'monto' => $sp['monto'],
					'observacion_cajero' => $sp['observacion_cajero'],

				));
			}
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols["n"] = "#";
			$list_cols["fecha_hora_registro"] = "Fecha Hora Registro";
			$list_cols["nombre_tipo_balance"] = "Tipo Balance";
			$list_cols["tipo_transaccion"] = "Modo de Balance";
			$list_cols["motivo"] = "Motivo";
			$list_cols["juego_balance"] = "Juego";
			$list_cols["id_trans_juego"] = "Id Transacción";
			$list_cols["cliente"] = "Cliente";
			$list_cols["num_doc"] = "Num Documento";
			$list_cols["web_id"] = "Id Cliente";
			$list_cols["local_cierre"] = "Local";
			$list_cols["supervisor_balance"] = "Supervisor";
			$list_cols["cajero_balance"] = "Promotor";
			$list_cols["monto"] = "Monto";
			$list_cols["observacion_cajero"] = "Observación";
			$list_cols_show = $list_cols;
		} elseif ($export == "reporte_hist_fusion") {


			$where_fecha_inicio = "";
			$where_fecha_fin = "";
			$where_cliente = "";
			$where_usuario = "";


			if (!empty($fec_inicio)) {
				$where_fecha_inicio .= "  AND hf.created_at >= '" . $fec_inicio . "'";
			}

			if (!empty($fec_fin)) {
				$where_fecha_fin .= "  AND hf.created_at <= date_add('" . $fec_fin . "', interval 1 day)";
			}

			if (!empty($cliente) && $cliente != "0") {
				$where_cliente .= "  AND hf.cliente_id_s = '" . $cliente . "'";
			}
			if (!empty($usuario) && $usuario != "0") {
				$where_usuario .= "  AND hf.usuario_id = '" . $usuario . "'";
			}


			$query = "SELECT
                hf.id,
                hf.usuario_id,
				IFNULL(usu_cajero.usuario, '') AS usuario_cajero,
				hf.cliente_id_f,
				hf.tipo_doc_f as tipo_doc,
				if (hf.tipo_doc_f = '0', 'DNI' , if (hf.tipo_doc_f = '1', 'CE/PTP' , 'PASAPORTE')) as tipo_doc_nomb,
				hf.num_doc_f as num_doc,
				hf.telefono_f as telefono,   
				hf.player_id_f as player_id,
				hf.web_id_f as web_id,
				hf.web_full_name_f as web_full_name,   
				hf.cliente_id_s,
				hf.cliente_f AS cliente,
				hf.correo_f as correo,
				date_format(hf.created_at, '%Y-%m-%d %H:%i') as fecha_formatead,
				hf.created_at
				from tbl_televentas_log_fusiones hf
				LEFT JOIN tbl_usuarios usu_cajero ON usu_cajero.id = hf.usuario_id
				WHERE  hf.id <> '' and hf.result=1
                $where_cliente
                $where_usuario
                $where_fecha_inicio
                $where_fecha_fin
                ORDER BY hf.created_at ASC
                ";
			$list_query = $mysqli->query($query);
			$list_proc_registros = array();

			$i = 0;
			while ($sp = $list_query->fetch_assoc()) {
				$i = $i + 1;

				$responsable = strtoupper($sp['usuario_cajero']);
				$cliente_s = strtoupper($sp['cliente']);

				array_push($list_proc_registros, array(
					'n' => $i,
					'created_at' => $sp['created_at'],
					'tipo_doc_nomb' => $sp['tipo_doc_nomb'],
					'num_doc_f' => $sp['num_doc'],
					'cliente_f' => $cliente_s,
					'correo_f' => $sp['correo'],
					'telefono_f' => $sp['telefono'],
					'player_id_f' => $sp['player_id'],
					'web_id_f' => $sp['web_id'],
					'web_full_name_f' => $sp['web_full_name'],
					'responsable' => $responsable,

				));
			}
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols["n"] = "#";
			$list_cols["created_at"] = "Fecha";
			$list_cols["tipo_doc_nomb"] = "Tipo Doc";
			$list_cols["num_doc_f"] = "Num Doc";
			$list_cols["cliente_f"] = "Cliente";
			$list_cols["correo_f"] = "Correo";
			$list_cols["telefono_f"] = "Teléfono";
			$list_cols["player_id_f"] = "Player ID";
			$list_cols["web_id_f"] = "ID Web";
			$list_cols["web_full_name_f"] = "Web Full Name";
			$list_cols["responsable"] = "Responsable";
			$list_cols_show = $list_cols;
		} elseif ($export == "reporte_del_dep") {

			$where_caja = "";
			$where_fecha_inicio = "";
			$where_fecha_fin = "";
			$where_motivo = "";
			$where_cajero = "";

			if (!empty($caja) && $caja != "0") {
				$where_caja = " AND caj.local_caja_id IN (" . $caja . ")";
			}


			if (!empty($fec_inicio)) {
				$where_fecha_inicio .= "  AND tct6.created_at >= '" . $fec_inicio . "'";
			}

			if (!empty($fec_fin)) {
				$where_fecha_fin .= "  AND tct6.created_at <= date_add('" . $fec_fin . "', interval 1 day)";
			}

			if (!empty($motivo) && $motivo != "0") {
				$where_motivo .= "  AND tct.id_motivo_dev = '" . $motivo . "'";
			}
			if (!empty($cajero) && $cajero != "0") {
				$where_cajero .= "  AND tct6.user_id = '" . $cajero . "'";
			}


			$query = "SELECT
				tct.id,
				tct.created_at fecha_hora_registro,
				IFNULL(tct.registro_deposito, '') registro_deposito,
				tct6.created_at as fecha_eliminacion,
				tct.user_id cajero_user_id,
				IFNULL(usu_cajero.usuario, '') AS usuario_cajero,
				tct6.id,
				tct6.user_id eliminador_user_id,
				IFNULL(usu_eliminador.usuario, '') AS usuario_eliminador,
				tct26.id,
				tct26.user_id validador_user_id,
				IFNULL(usu_validador.usuario, '') AS usuario_validador,
				loc_caj.nombre as caja,
				loc.nombre as nombre_local, 
				IFNULL(tct.monto_deposito, 0) AS deposito,
				IFNULL( ttr.tipo_rechazo, '' ) tipo_rechazo,
				IFNULL( tct.web_id, '' ) web_id,
				IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente
				from tbl_televentas_clientes_transaccion tct
				LEFT JOIN tbl_televentas_clientes cli ON cli.id = tct.cliente_id
				LEFT JOIN tbl_usuarios usu_cajero ON usu_cajero.id = tct.user_id
				left join tbl_televentas_clientes_transaccion tct6 on tct6.transaccion_id=tct.id and tct6.tipo_id=6
				LEFT JOIN tbl_usuarios usu_eliminador ON usu_eliminador.id = tct6.user_id
				left join tbl_televentas_clientes_transaccion tct26 on tct26.transaccion_id=tct.id and tct26.tipo_id=26
				LEFT JOIN tbl_usuarios usu_validador ON usu_validador.id = tct26.user_id
				LEFT JOIN tbl_caja caj ON caj.id = tct.turno_id
				LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
				LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
				left join tbl_televentas_tipo_rechazo ttr on tct.id_motivo_dev = ttr.id
				WHERE tct.tipo_id=1 AND tct.estado=3 
				$where_caja
				$where_motivo
				$where_cajero
				$where_fecha_inicio
				$where_fecha_fin
				ORDER BY tct.created_at ASC
				";
			$list_query = $mysqli->query($query);
			$list_proc_registros = array();

			$i = 0;
			while ($sp = $list_query->fetch_assoc()) {
				$i = $i + 1;
				$local_caja = $sp['nombre_local'] . ' - ' . $sp['caja'];

				array_push($list_proc_registros, array(
					'n' => $i,
					'local_caja' => $local_caja,
					'registro_deposito' => $sp['registro_deposito'],
					'fecha_eliminacion' => $sp['fecha_eliminacion'],
					'web_id' => $sp['web_id'],
					'cliente' => $sp['cliente'],
					'usuario_cajero' => $sp['usuario_cajero'],
					'deposito' => $sp['deposito'],
					'tipo_rechazo' => $sp['tipo_rechazo'],
					'usuario_eliminador' => $sp['usuario_eliminador'],
					'usuario_validador' => $sp['usuario_validador'],

				));
			}
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols["n"] = "#";
			$list_cols["local_caja"] = "Nombre local - Caja";
			$list_cols["registro_deposito"] = "Fecha depósito";
			$list_cols["fecha_eliminacion"] = "Fecha eliminación";
			$list_cols["web_id"] = "ID Web";
			$list_cols["cliente"] = "Cliente";
			$list_cols["usuario_cajero"] = "Cajero";
			$list_cols["deposito"] = "Importe";
			$list_cols["tipo_rechazo"] = "Motivo";
			$list_cols["usuario_eliminador"] = "Eliminador";
			$list_cols["usuario_validador"] = "Validador";
			$list_cols_show = $list_cols;
		} elseif ($export == "reporte_tercero_aut") {


			$where_fecha_inicio = "";
			$where_fecha_fin = "";
			$where_cajero = "";
			$where_cliente = "";


			if (!empty($fec_inicio)) {
				$where_fecha_inicio .= "  AND tit.created_at >= '" . $fec_inicio . "'";
			}

			if (!empty($fec_fin)) {
				$where_fecha_fin .= "  AND tit.created_at <= date_add('" . $fec_fin . "', interval 1 day)";
			}


			if (!empty($cajero) && $cajero != "0") {
				$where_cajero .= "  AND tit.id_cajero = '" . $cajero . "'";
			}

			if (!empty($cliente) && $cliente != "0") {
				$where_cliente .= "  AND tit.id_cliente = '" . $cliente . "'";
			}


			$query = "SELECT  
				cli.id,
				if (cli.tipo_doc = '0', 'DNI' , if (cli.tipo_doc = '1', 'CE/PTP' , 'PASAPORTE')) as tipo_doc_nomb,
				cli.num_doc, 
				CONCAT_WS(' ', cli.nombre, cli.apellido_paterno, cli.apellido_materno) AS cliente ,
				tit.dni_titular,
				tit.nombre_apellido_titular,
				CONCAT_WS(' ', pl.nombre, pl.apellido_paterno, pl.apellido_materno) AS cajero,
				tit.created_at		
				FROM wwwapuestatotal_gestion.tbl_televentas_titular_abono tit 
				Left Join tbl_televentas_clientes cli on  cli.id = tit.id_cliente
				left JOIN tbl_usuarios u ON u.id = tit.id_cajero
				left JOIN tbl_personal_apt pl ON pl.id = u.personal_id
				Where tit.estado in (1,0)
				$where_cajero
				$where_cliente
				$where_fecha_inicio
				$where_fecha_fin 
				ORDER BY cli.nombre asc
				";
			$list_query = $mysqli->query($query);
			$list_proc_registros = array();

			$i = 0;
			while ($sp = $list_query->fetch_assoc()) {
				$i = $i + 1;


				array_push($list_proc_registros, array(
					'n' => $i,
					'tipo_doc_nomb' => $sp['tipo_doc_nomb'],
					'num_doc' => $sp['num_doc'],
					'cliente' => $sp['cliente'],
					'dni_titular' => $sp['dni_titular'],
					'nombre_apellido_titular' => $sp['nombre_apellido_titular'],
					'cajero' => $sp['cajero'],
					'created_at' => $sp['created_at'],

				));
			}
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols["n"] = "#";
			$list_cols["tipo_doc_nomb"] = "Tipo Doc.";
			$list_cols["num_doc"] = "Num. Doc.";
			$list_cols["cliente"] = "Cliente Titular";
			$list_cols["dni_titular"] = "Num Doc. 3ro Aut.";
			$list_cols["nombre_apellido_titular"] = "Nombres y apellidos 3ro Aut.";
			$list_cols["cajero"] = "Cajero Registrador";
			$list_cols["created_at"] = "Fecha - hora Registro";
			$list_cols_show = $list_cols;
		} elseif ($export == "reporte_tlv_clientes") {

			$usuario_id = $login ? $login['id'] : 0;
			$cargo_id = $login ? $login['cargo_id'] : 0;
			$area_id = $login ? $login['area_id'] : 0;

			$where_fecha_inicio = "";
			$where_fecha_fin = "";

			$where_fecha_inicio = " AND DATE(c.created_at) >= '" . $SecRepTel_fecha_inicio . "' ";
			$where_fecha_fin = " AND DATE(c.created_at) <= '" . $SecRepTel_fecha_fin . "' ";

			$where_users_test = "";
			if ((int)$area_id !== 6) {
				$where_users_test = "	
					AND IFNULL(c.web_id, '') not in ('3333200', '71938219') 
					AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028) 
					";
			}



			$query = "
					SELECT 
						IFNULL(u.usuario, '') usuario_created,
						c.created_at fecha_hora_registro,
					    DATE(MAX(tra.created_at)) fecha_ultimo_movimiento,
					    TIME(MAX(tra.created_at)) hora_ultimo_movimiento,
					    IFNULL( IF (c.cc_id = 3900, 'TELEVENTAS', l.nombre ), '' ) local_nombre,
					    IFNULL(c.web_id, '') web_id,
					    IFNULL(c.telefono, '' ) telefono,
					    CASE WHEN c.tipo_doc = 0 THEN 'DNI' ELSE 'CE/PTP/PASAPORTE' END tipo_doc,
					    IFNULL(c.num_doc, '') num_doc,
						IFNULL(CONCAT( c.nombre, ' ', IFNULL( c.apellido_paterno, '' ), ' ', IFNULL( c.apellido_materno, '' ) ), '') cliente,
					    IFNULL(c.fec_nac, '') fecha_nacimiento,
						SUM(IF(tra.tipo_id = 26 AND tra.estado = 1, tra.monto, 0)) total_deposito,
						SUM(IF(tra.tipo_id = 10, tra.monto, 0)) total_bono,
						SUM(IF(tra.tipo_id = 2 and tra.estado = 1, tra.monto, 0)) total_recarga,
						SUM(IF(tra.tipo_id = 26 AND tra.estado = 1, 1, 0)) cont_deposito,
						SUM(IF(tra.tipo_id = 10, 1, 0)) cont_bono,
						SUM(IF(tra.tipo_id = 2 and tra.estado = 1, 1, 0)) cont_recarga,
					    IFNULL(b.balance, 0) balance
					FROM tbl_televentas_clientes_transaccion tra
					INNER JOIN tbl_televentas_clientes c ON tra.cliente_id = c.id
					INNER JOIN tbl_televentas_clientes_balance b ON tra.cliente_id = b.cliente_id AND b.tipo_balance_id = 1
					LEFT JOIN tbl_locales l ON l.cc_id = c.cc_id
					LEFT JOIN tbl_usuarios u ON c.created_user_id = u.id
					WHERE
						(
							(tra.tipo_id IN (1,2,10,26) and tra.estado=1)
						) 
						AND DATE( c.created_at ) > '2021-08-01' 
						" . $where_users_test . " 
						" . $where_fecha_inicio . " 
						" . $where_fecha_fin . " 
					GROUP BY tra.cliente_id";

			$list_query = $mysqli->query($query);
			$list_proc_registros = array();
			while ($sp = $list_query->fetch_assoc()) {

				array_push($list_proc_registros, array(
					'fecha_hora_registro' => $sp['fecha_hora_registro'],
					'usuario_created' => $sp['usuario_created'],
					'fecha_ultimo_movimiento' => $sp['fecha_ultimo_movimiento'],
					'hora_ultimo_movimiento' => $sp['hora_ultimo_movimiento'],
					'local_nombre' => $sp['local_nombre'],
					'web_id' => $sp['web_id'],
					'telefono' => $sp['telefono'],
					'tipo_doc' => $sp['tipo_doc'],
					'num_doc' => $sp['num_doc'],
					'cliente' => $sp['cliente'],
					'fecha_nacimiento' => $sp['fecha_nacimiento'],
					'total_deposito' => $sp['total_deposito'],
					'total_bono' => $sp['total_bono'],
					'total_recarga' => $sp['total_recarga'],
					'cont_deposito' => $sp['cont_deposito'],
					'cont_bono' => $sp['cont_bono'],
					'cont_recarga' => $sp['cont_recarga'],
					'balance' => $sp['balance'],
				));
			}
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols["fecha_hora_registro"] = "Registro";
			$list_cols["usuario_created"] = "Usuario Promotor";
			$list_cols["fecha_ultimo_movimiento"] = "Fecha Ultimo Movimiento";
			$list_cols["hora_ultimo_movimiento"] = "Hora Ultimo Movimiento";
			$list_cols["local_nombre"] = "Local";
			$list_cols["web_id"] = "WEB-ID";
			$list_cols["telefono"] = "Telefono";
			$list_cols["tipo_doc"] = "Tipo de Documento";
			$list_cols["num_doc"] = "Numero de Documento";
			$list_cols["cliente"] = "Nombre de Cliente";
			$list_cols["fecha_nacimiento"] = "Fecha de Nacimiento";
			$list_cols["total_deposito"] = "Total Depósito";
			$list_cols["total_bono"] = "Total Bono";
			$list_cols["total_recarga"] = "Total Recarga";
			$list_cols["cont_deposito"] = "Cantidad Depositos";
			$list_cols["cont_bono"] = "Cantidad Bonos";
			$list_cols["cont_recarga"] = "Cantidad Recargas";
			$list_cols["balance"] = "Balance";
			$list_cols_show = $list_cols;
		} elseif ($export == "reporte_por_vigencia") {

			$where_empresa = "";
			$where_centro_costo = "";
			$where_renta_m = "";
			$where_nombre_tienda = "";
			$where_ubigeo = "";
			$where_direccion = "";

			$where_fech_suscripcion = "";
			$where_fecha_inicio = "";
			$where_fecha_fin = "";
			$where_n_adendas = "";
			$where_estado = "";

			if (!empty($empresa) && $empresa != "0") {
				$where_empresa = " AND rs.id IN (" . $empresa . ")";
			}
			if (!empty($centro_costo) && $centro_costo != "0") {
				$where_centro_costo = "  AND c.cc_id LIKE '%" . $centro_costo . "%'";
			}
			if (!empty($renta_m) && $renta_m != "0") {
				$where_renta_m = "  AND ce.monto_renta like '%" . $renta_m . "%'";
			}
			if (!empty($nomb_tienda) && $nomb_tienda != "0") {
				$where_nombre_tienda = "  AND c.nombre_tienda LIKE '%" . $nomb_tienda . "%'";
			}
			if (!empty($direccion) && $direccion != "0") {
				$where_direccion = "  AND i.ubicacion LIKE '%" . $direccion . "%'";
			}
			if (!empty($departamento) && $departamento != "0") {
				$where_ubigeo .= "  AND dp.cod_depa = '" . $departamento . "'";
			}
			if (!empty($provincia) && $provincia != "0") {
				$where_ubigeo .= "  AND pr.cod_prov = '" . $provincia . "'";
			}
			if (!empty($distrito) && $distrito != "0") {
				$where_ubigeo .= "  AND dt.cod_dist = '" . $distrito . "'";
			}


			if (!empty($fec_suscrip)) {
				$where_fech_suscripcion .= "  AND ce.fecha_suscripcion = '" . $fec_suscrip . "'";
			}

			if (!empty($fec_inicio)) {
				$where_fecha_inicio .= "  AND ce.fecha_inicio = '" . $fec_inicio . "'";
			}

			if (!empty($fec_fin)) {
				$where_fecha_fin .= "  AND ce.fecha_fin = '" . $fec_fin . "'";
			}


			if (!empty($n_adendas) && $n_adendas != "0") {
				$where_n_adendas .= "  AND ad.num_adendas = '" . $n_adendas . "'";
			}
			if (!empty($estado) && $estado != "0") {
				$where_estado .= "  AND c.etapa_id = '" . $estado . "'";
			}


			$query = "SELECT c.contrato_id as id_item, rs.nombre AS empresa, c.nombre_tienda, c.cc_id, 
				ce.monto_renta,
					dp.nombre AS departamento, pr.nombre AS provincia, dt.nombre AS distrito, i.ubicacion AS direccion, IFNULL(ad.num_adendas,0) AS numero_adendas
					,ce.fecha_inicio, ce.fecha_fin, ce.fecha_suscripcion, c.etapa_id as etapa_est, 
					 
					CASE 
						
						WHEN c.etapa_id = 1 And c.etapa_conta_id is null THEN 'Pendiente'
						WHEN c.etapa_id = 5 And c.etapa_conta_id is null  THEN 'Firmado'	
						WHEN c.etapa_id = 5 And c.etapa_conta_id = 1  THEN 'Firmado y legalizado'		  
					END as estado_v
					FROM cont_contrato AS c
					INNER JOIN cont_inmueble i ON i.contrato_id = c.contrato_id
					INNER JOIN cont_condicion_economica ce on c.contrato_id = ce.contrato_id	
					LEFT JOIN (
					SELECT contrato_id, count(*) AS num_adendas
				FROM cont_adendas
				WHERE procesado = 1
				GROUP BY contrato_id) AS ad ON c.contrato_id = ad.contrato_id	 
					INNER JOIN tbl_razon_social AS rs ON rs.id = c.empresa_suscribe_id
					INNER JOIN tbl_ubigeo AS dp ON dp.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND dp.cod_prov = '00' AND dp.cod_dist = '00' 
					INNER JOIN tbl_ubigeo AS pr ON pr.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND SUBSTRING(i.ubigeo_id, 3, 2) = pr.cod_prov AND pr.cod_dist = '00'
					INNER JOIN tbl_ubigeo AS dt ON dt.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND SUBSTRING(i.ubigeo_id, 3, 2) = dt.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = dt.cod_dist
					where c.status = 1
				$where_empresa
				$where_centro_costo
				$where_renta_m
				$where_nombre_tienda 
				$where_direccion
				$where_ubigeo 		
				$where_n_adendas
				$where_estado 
				$where_fech_suscripcion
				$where_fecha_inicio
				$where_fecha_fin
				";
			$list_query = $mysqli->query($query);
			$list_proc_registros = array();
			while ($sp = $list_query->fetch_assoc()) {
				$cant_meses_contrato = "";
				$fecha_inicio = trim($sp['fecha_inicio']);
				$fecha_fin = trim($sp['fecha_fin']);
				if (!(empty($fecha_inicio) || empty($fecha_fin))) {
					$inicio = $fecha_inicio . " 00:00:00";
					$fin = $fecha_fin . " 23:59:59";
					$datetime1 = new DateTime($inicio);
					$datetime2 = new DateTime($fin);
					$datetime2->modify('+1 day');
					$interval = $datetime2->diff($datetime1);
					$intervalMeses = $interval->format("%m");
					$intervalAnos = $interval->format("%y") * 12;
					$cant_meses_contrato = $intervalMeses + $intervalAnos;
					$cant_meses_contrato = sec_contrato_detalle_solicitud_de_meses_a_anios_y_meses($cant_meses_contrato);
				}

				array_push($list_proc_registros, array(
					'empresa' => $sp['empresa'],
					'cc_id' => $sp['cc_id'],
					'monto_renta' => $sp['monto_renta'],
					'nombre_tienda' => $sp['nombre_tienda'],
					'departamento' => $sp['departamento'],
					'provincia' => $sp['provincia'],
					'distrito' => $sp['distrito'],
					'direccion' => $sp['direccion'],
					'fecha_suscripcion' => $sp['fecha_suscripcion'],
					'vigencia' => $cant_meses_contrato,
					'fecha_inicio' => $sp['fecha_inicio'],
					'fecha_fin' => $sp['fecha_fin'],
					'numero_adendas' => $sp['numero_adendas'],
					'estado_v' => $sp['estado_v'],
				));
			}
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols["empresa"] = "Empresa";
			$list_cols["cc_id"] = "C.C.";
			$list_cols["monto_renta"] = "Renta Mensual";
			$list_cols["nombre_tienda"] = "Nombre Tienda";
			$list_cols["departamento"] = "Departamento";
			$list_cols["provincia"] = "Provincia";
			$list_cols["distrito"] = "Distrito";
			$list_cols["direccion"] = "Dirección";
			$list_cols["fecha_suscripcion"] = "Fecha suscripción";
			$list_cols["vigencia"] = "Vigencia";
			$list_cols["fecha_inicio"] = "Fecha Inicio";
			$list_cols["fecha_fin"] = "Fecha Fin";
			$list_cols["numero_adendas"] = "Nº Adendas";
			$list_cols["estado_v"] = "Estado";
			$list_cols_show = $list_cols;
		} elseif ($export == "reporte_por_centro_de_costos") {

			$where_local = " AND lc.red_id IN (1,9,7)";
			$where_centro_costo = "";
			$where_arrendatario = "";
			$where_nombre_tienda = "";
			$where_ubigeo = "";
			$where_direccion = "";

			if (!empty($local) && $local != "0") {
				$where_local = " AND lc.red_id IN (" . $local . ")";
			}
			if (!empty($centro_costo) && $centro_costo != "0") {
				$where_centro_costo = "  AND lc.cc_id LIKE '%" . $centro_costo . "%'";
			}
			if (!empty($arrendatario) && $arrendatario != "0") {
				$where_arrendatario = "  AND lc.razon_social_id = '" . $arrendatario . "'";
			}
			if (!empty($nombre_tienda) && $nombre_tienda != "0") {
				$where_nombre_tienda = "  AND lc.nombre LIKE '%" . $nombre_tienda . "%'";
			}
			if (!empty($direccion) && $direccion != "0") {
				$where_direccion = "  AND lc.direccion LIKE '%" . $direccion . "%'";
			}
			if (!empty($departamento) && $departamento != "0") {
				$where_ubigeo .= "  AND dp.cod_depa = '" . $departamento . "'";
			}
			if (!empty($provincia) && $provincia != "0") {
				$where_ubigeo .= "  AND pr.cod_prov = '" . $provincia . "'";
			}
			if (!empty($distrito) && $distrito != "0") {
				$where_ubigeo .= "  AND dt.cod_dist = '" . $distrito . "'";
			}

			$query = "SELECT lc.id, lc.red_id, rs.nombre AS arrendatario, lc.nombre, lc.cc_id, 
				dp.nombre AS departamento, pr.nombre AS provincia, dt.nombre AS distrito, lc.direccion
				FROM tbl_locales lc
				INNER JOIN cont_contrato AS c ON c.contrato_id = lc.contrato_id
				INNER JOIN tbl_razon_social AS rs ON rs.id = lc.razon_social_id
				INNER JOIN tbl_ubigeo AS dp ON dp.cod_depa = SUBSTRING(lc.ubigeo_id, 1, 2) AND dp.cod_prov = '00' AND dp.cod_dist = '00' 
				INNER JOIN tbl_ubigeo AS pr ON pr.cod_depa = SUBSTRING(lc.ubigeo_id, 1, 2) AND SUBSTRING(lc.ubigeo_id, 3, 2) = pr.cod_prov AND pr.cod_dist = '00'
				INNER JOIN tbl_ubigeo AS dt ON dt.cod_depa = SUBSTRING(lc.ubigeo_id, 1, 2) AND SUBSTRING(lc.ubigeo_id, 3, 2) = dt.cod_prov AND SUBSTRING(lc.ubigeo_id, 5, 2) = dt.cod_dist
				where lc.estado = 1 AND lc.cc_id != ''
				$where_local
				$where_centro_costo
				$where_arrendatario
				$where_nombre_tienda
				$where_ubigeo
				$where_direccion
				";
			$list_query = $mysqli->query($query);
			$list_proc_registros = array();
			while ($sp = $list_query->fetch_assoc()) {
				$name_local = '';
				switch ($sp['red_id']) {
					case '1':
						$name_local = 'Tienda';
						break;
					case '9':
						$name_local = 'Casino';
						break;
					case '7':
						$name_local = 'Tambo';
						break;
				}
				array_push($list_proc_registros, array(
					'local' => $name_local,
					'arrendatario' => $sp['arrendatario'],
					'cc_id' => $sp['cc_id'],
					'nombre' => $sp['nombre'],
					'departamento' => $sp['departamento'],
					'provincia' => $sp['provincia'],
					'distrito' => $sp['distrito'],
					'direccion' => $sp['direccion'],
				));
			}
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols["local"] = "LOCAL";
			$list_cols["arrendatario"] = "EMPRESA";
			$list_cols["cc_id"] = "C.C.";
			$list_cols["nombre"] = "NOMBRE";
			$list_cols["departamento"] = "DEPARTAMENTO";
			$list_cols["provincia"] = "PROVINCIA";
			$list_cols["distrito"] = "DISTRITO";
			$list_cols["direccion"] = "DIRECCIÓN";
			$list_cols_show = $list_cols;
		} elseif ($export == "cont_contrato_interno") {

			$where_empresa_1 = "";
			$where_empresa_2 = "";
			$where_area = "";
			$where_fechas = "";
			$where_fecha_solicitud = "";
			$where_fecha_inicio = "";

			if (!empty($empresa_1) && $empresa_1 != "0") {
				$where_empresa_1 = " AND c.empresa_suscribe_id = " . $empresa_1;
			}
			if (!empty($empresa_2) && $empresa_2 != "0") {
				$where_empresa_2 = " AND c.empresa_grupo_at_2 = " . $empresa_2;
			}
			if (!empty($area) && $area != "0") {
				$where_area = " AND ar.id = " . $area;
			}

			if (!empty($fecha_inicio_solicitud) && !empty($fecha_fin_solicitud)) {
				$where_fecha_solicitud = " AND c.created_at BETWEEN '$fecha_inicio_solicitud 00:00:00' AND '$fecha_fin_solicitud 23:59:59'";
			} elseif (!empty($fecha_inicio_solicitud)) {
				$where_fecha_solicitud = " AND c.created_at >= '$fecha_inicio_solicitud 00:00:00'";
			} elseif (!empty($fecha_fin_solicitud)) {
				$where_fecha_solicitud = " AND c.created_at <= '$fecha_fin_solicitud 23:59:59'";
			}

			if (!empty($fecha_inicio_inicio) && !empty($fecha_fin_inicio)) {
				$where_fecha_inicio = " AND c.fecha_inicio BETWEEN '$fecha_inicio_inicio 00:00:00' AND '$fecha_fin_inicio 23:59:59'";
			} elseif (!empty($fecha_inicio_inicio)) {
				$where_fecha_inicio = " AND c.fecha_inicio >= '$fecha_inicio_inicio 00:00:00'";
			} elseif (!empty($fecha_fin_inicio)) {
				$where_fecha_inicio = " AND c.fecha_inicio <= '$fecha_fin_inicio 23:59:59'";
			}

			$where_fecha_aprobacion = '';
			if (!empty($fecha_inicio_aprobacion) && !empty($fecha_fin_aprobacion)) {
				$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_interno between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
			} elseif (!empty($fecha_inicio_aprobacion)) {
				$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_interno >= '$fecha_inicio_aprobacion 00:00:00'";
			} elseif (!empty($fecha_fin_aprobacion)) {
				$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_interno <= '$fecha_fin_aprobacion 23:59:59'";
			}
			$where_director_aprobacion	=	"";

			if (!empty($director_aprobacion_id)) {
				$where_director_aprobacion = " AND ( ( c.aprobado_por = " . $director_aprobacion_id . " AND (c.check_gerencia_interno =1 and c.fecha_atencion_gerencia_interno IS NULL AND c.aprobacion_gerencia_interno=0) ) OR c.aprobado_por = " . $director_aprobacion_id . " ) ";
			}



			$query = "
				SELECT
					c.contrato_id, 
					c.empresa_suscribe_id,
					rs1.nombre AS empresa_at1,
					rs2.nombre AS empresa_at2,
					c.detalle_servicio,
					CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
					concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
					per.correo AS usuario_creacion_correo,
					c.fecha_inicio,
					ar.nombre AS area_creacion,
					c.check_gerencia_interno,
					c.fecha_atencion_gerencia_interno,
					c.aprobacion_gerencia_interno,
					co.sigla AS sigla_correlativo,
					c.codigo_correlativo,
					c.observaciones,
					c.created_at,
					CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
					c.renovacion_automatica,
					c.estado_resolucion,
					c.fecha_vencimiento_indefinida_id,
					c.fecha_vencimiento_proveedor
				FROM 
					cont_contrato c
					INNER JOIN cont_periodo p ON c.periodo = p.id
					INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
					INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
					INNER JOIN tbl_areas ar ON per.area_id = ar.id
					INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
					INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
					LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					LEFT JOIN tbl_usuarios ud ON c.aprobado_por = ud.id
					LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
				WHERE 
					c.status = 1 
					AND c.etapa_id = 5 
					AND c.tipo_contrato_id = 7 
					$where_empresa_1
					$where_empresa_2
					$where_area
					$where_fecha_solicitud
					$where_fecha_inicio
					$where_fecha_aprobacion
					$where_director_aprobacion
				ORDER BY c.contrato_id DESC
				";
			$list_query = $mysqli->query($query);
			$list_proc_registros = array();
			while ($sp = $list_query->fetch_assoc()) {
				$fecha_vencimiento_proveedor = '';
				$estado_contractual = '';

				if ((int) $sp['fecha_vencimiento_indefinida_id'] == 1) {
					$fecha_vencimiento_proveedor = 'Indefinida';
					$estado_contractual = 'Vigente';
				} else {

					$fechaObj1 = new DateTime($sp['fecha_vencimiento_proveedor']);
					$fechaObj2 = new DateTime(date('Y-m-d'));

					if ($sp['estado_resolucion'] == 2) {
						$estado_contractual = 'Resuelto';
					} else {
						if ($fechaObj1 > $fechaObj2) {
							$estado_contractual = 'Vigente';
						} else {
							$estado_contractual = 'Vencido';
						}
					}
					$fecha_vencimiento_proveedor = $sp['fecha_vencimiento_proveedor'];
				}

				$fecha = new DateTime($sp['fecha_inicio']);
				$fecha_convertido = $fecha->format('Y-m-d');
				$renovacion_automatica = $sp['renovacion_automatica'] == 1 ? 'SI' : 'NO';
				array_push($list_proc_registros, array(
					'codigo' => $sp['sigla_correlativo'] . $sp['codigo_correlativo'],
					'area' => $sp['area_creacion'],
					'usuario' => $sp['usuario_creacion'],
					'empresa_at1' => $sp['empresa_at1'],
					'empresa_at2' => $sp['empresa_at2'],
					'fecha_solicitud' => $sp['created_at'],
					'fecha' => $fecha_convertido,
					'fecha_fin' => $fecha_vencimiento_proveedor,
					'estado_contractual' => $estado_contractual,
					'nombre_del_director_a_aprobar' =>  $sp['nombre_del_director_a_aprobar'],
					'fecha_atencion_gerencia_interno' =>  $sp['fecha_atencion_gerencia_interno'],
					'renovacion_automatica' => $renovacion_automatica

				));
			}
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols["codigo"] = "CODIGO";
			$list_cols["area"] = "AREA SOLICITANTE";
			$list_cols["usuario"] = "SOLICITANTE";
			$list_cols["empresa_at1"] = "EMPRESA GRUPO AT 1";
			$list_cols["empresa_at2"] = "EMPRESA GRUPO AT 2";
			$list_cols["fecha_solicitud"] = "FECHA SOLICITUD";
			$list_cols["fecha"] = "FECHA INICIO";
			$list_cols["fecha_fin"] = "FECHA FIN";
			$list_cols["estado_contractual"] = "ESTADO CONTRACTUAL";
			$list_cols["renovacion_automatica"] = "RENOVACIÓN AUTOMÁTICA";
			$list_cols["nombre_del_director_a_aprobar"] = "APROBANTE";
			$list_cols["fecha_atencion_gerencia_interno"] = "F. APROBACION";
			$list_cols_show = $list_cols;
		}
		//////////////////////////////////////////////////////////////////////////
		elseif ($export == "cont_contrato_arrendamiento") {
			// Filtros del usuario
			$fecha_inicio = isset($_GET['fecha_inicio_solicitud']) ? $_GET['fecha_inicio_solicitud'] : '';
			$fecha_fin = isset($_GET['fecha_fin_solicitud']) ? $_GET['fecha_fin_solicitud'] : '';
			$empresa = isset($_GET['id_empresa']) ? $_GET['id_empresa'] : '';

			// Construcción de condiciones WHERE
			$where_fecha = '';
			$where_empresa = '';

			if (!empty($fecha_inicio) && !empty($fecha_fin)) {
				$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
			}

			if (!empty($empresa)) {
				$where_empresa = " AND c.empresa_suscribe_id = '$empresa'";
			}

			// Consulta SQL optimizada para Contrato de Arrendamiento
			$query = "
				SELECT 
					CONCAT(IFNULL(co.sigla, ''), IFNULL(c.codigo_correlativo, '')) AS Código,
					i.ubicacion AS 'Ubicación del Inmueble',
					DATE_FORMAT(c.created_at, '%d-%m-%Y') AS 'F. Solicitud',
					DATE_FORMAT(ce.fecha_suscripcion, '%d-%m-%Y') AS 'F. Suscripción',
					IFNULL(es.nombre, 'En Proceso') AS Estado,
					CASE 
						WHEN c.estado_aprobacion = 0 THEN 'En Solicitud'
						WHEN c.estado_aprobacion = 1 THEN 'Aprobado'
						ELSE c.estado_aprobacion 
					END AS 'Estado Aprobación',
					cb.nombre AS Arrendador,
					cb.num_docu AS 'DNI Arrendador',
					rs.nombre AS Arrendatario,
					rs.num_ruc AS 'RUC Arrendatario',
					ce.monto_renta AS 'Monto de Renta',
					tm.nombre AS 'Moneda',	
					ce.fecha_inicio AS 'F. Inicio',
					ce.fecha_fin AS 'F. Fin',
					ce.cant_meses_contrato AS 'Meses de Contrato',
					CASE 
						WHEN ce.renovacion_automatica = 1 THEN 'Sí'
						WHEN ce.renovacion_automatica = 2 THEN 'No'
						ELSE 'No especificado'
					END AS 'Renovación Automática',
					b.nombre AS 'Banco',
					e.situacion AS 'Situación de Etapa',
					CONCAT('http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitudV2&id=', c.contrato_id) AS 'Ver Detalle'
				FROM 
					cont_contrato c
				INNER JOIN 
					cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
				INNER JOIN 
					cont_inmueble i ON c.contrato_id = i.contrato_id AND i.contrato_detalle_id = ce.contrato_detalle_id
				INNER JOIN 
					cont_etapa e ON c.etapa_id = e.etapa_id
				LEFT JOIN 
					cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
				LEFT JOIN 
					cont_estado_solicitud es ON es.id = c.estado_solicitud
				LEFT JOIN 
					cont_beneficiarios cb ON c.contrato_id = cb.contrato_id
				LEFT JOIN 
					tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
				LEFT JOIN 
					tbl_moneda tm ON ce.tipo_moneda_id = tm.id
				LEFT JOIN 
					tbl_bancos b ON cb.banco_id = b.id
				WHERE 
					c.tipo_contrato_id = 1 
					AND c.status = 1 
					AND c.estado_aprobacion = 1
					$where_empresa
					$where_fecha
				ORDER BY 
					c.created_at DESC;
			";

			// Ejecutar la consulta
			$list_query = $mysqli->query($query);
			$list = [];

			// Procesar resultados
			while ($li = $list_query->fetch_assoc()) {
				$list[] = $li;
			}

			// Columnas a mostrar
			$list_cols = [
				"Código" => "Código",
				"Ubicación del Inmueble" => "Ubicación del Inmueble",
				"Monto de Renta" => "Monto de Renta",
				"Moneda" => "Moneda",
				"Banco" => "Banco",
				"F. Solicitud" => "F. Solicitud",
				"F. Suscripción" => "F. Suscripción",
				"Estado" => "Estado",
				"Estado Aprobación" => "Estado Aprobación",
				"Arrendador" => "Arrendador",
				"DNI Arrendador" => "DNI Arrendador",
				"Arrendatario" => "Arrendatario",
				"RUC Arrendatario" => "RUC Arrendatario",
				"F. Inicio" => "F. Inicio",
				"F. Fin" => "F. Fin",
				"Meses de Contrato" => "Meses de Contrato",
				"Renovación Automática" => "Renovación Automática",
				"Situación de Etapa" => "Situación de Etapa",
				"Ver Detalle" => "Ver Detalle"
			];

			$list_cols_show = $list_cols;
		} elseif ($export == "cont_contrato_locacion") {
			// Filtros del usuario
			$fecha_inicio = isset($_GET['fecha_inicio_solicitud']) ? $_GET['fecha_inicio_solicitud'] : '';
			$fecha_fin = isset($_GET['fecha_fin_solicitud']) ? $_GET['fecha_fin_solicitud'] : '';
			$empresa = isset($_GET['id_empresa']) ? $_GET['id_empresa'] : '';

			// Construcción de condiciones WHERE
			$where_fecha = '';
			$where_empresa = '';

			if (!empty($fecha_inicio) && !empty($fecha_fin)) {
				$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
			}

			if (!empty($empresa)) {
				$where_empresa = " AND c.empresa_suscribe_id = '$empresa'";
			}

			// Consulta SQL optimizada para Contrato de Locación de Servicio
			$query = "
				SELECT 
					 CONCAT(IFNULL(co.sigla, ''), IFNULL(c.codigo_correlativo, '')) AS Código,
					cl.nombreservicio AS 'Nombre del Servicio',
					DATE_FORMAT(c.created_at, '%d-%m-%Y') AS 'F. Solicitud',
					DATE_FORMAT(cl.fechainicio, '%d-%m-%Y') AS 'F. Inicio',
					DATE_FORMAT(cl.fechafin, '%d-%m-%Y') AS 'F. Fin',
					IFNULL(es.nombre, 'En Proceso') AS Estado,
					CASE 
						WHEN c.estado_aprobacion = 0 THEN 'En Solicitud'
						WHEN c.estado_aprobacion = 1 THEN 'Aprobado'
						ELSE c.estado_aprobacion 
					END AS 'Estado Aprobación',
					cb.nombre AS Locador,
					cb.num_docu AS 'DNI Locador',
					rs.nombre AS Locatario,
					rs.num_ruc AS 'RUC Locatario',
					ce.monto AS 'Monto',
					tm.nombre AS 'Moneda',
					e.situacion AS 'Situación de Etapa',
					CONCAT('http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitud_locacion_servicio&id=', c.contrato_id) AS 'Ver Detalle'
				FROM 
					cont_contrato c
					INNER JOIN cont_locacion cl ON c.contrato_id = cl.idcontrato
					INNER JOIN cont_contraprestacion ce ON c.contrato_id = ce.contrato_id 
					AND ce.STATUS = 1
					INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
					LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
					LEFT JOIN cont_propietario prop ON c.contrato_id = prop.contrato_id
					LEFT JOIN cont_persona cb ON cb.id = prop.persona_id
					LEFT JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
					LEFT JOIN tbl_moneda tm ON ce.moneda_id = tm.id
				WHERE 
					c.tipo_contrato_id = 13
					AND c.status = 1 
					AND c.estado_aprobacion = 1 
					$where_empresa
					$where_fecha
				ORDER BY 
					c.created_at DESC;
			";
			// var_dump( $query );
			// die("query");

			// Ejecutar la consulta
			$list_query = $mysqli->query($query);
			$list = [];

			// Procesar resultados
			while ($li = $list_query->fetch_assoc()) {
				$list[] = $li;
			}

			// Columnas a mostrar
			$list_cols = [
				"Código" => "Código",
				"Nombre del Servicio" => "Nombre del Servicio",
				"Monto" => "Monto",
				"Moneda" => "Moneda",
				"F. Solicitud" => "F. Solicitud",
				"F. Inicio" => "F. Inicio",
				"F. Fin" => "F. Fin",
				"Estado" => "Estado",
				"Estado Aprobación" => "Estado Aprobación",
				"Locador" => "Locador",
				"DNI Locador" => "DNI Locador",
				"Locatario" => "Locatario",
				"RUC Locatario" => "RUC Locatario",
				"Situación de Etapa" => "Situación de Etapa",
				"Ver Detalle" => "Ver Detalle"
			];

			$list_cols_show = $list_cols;
		} elseif ($export == "cont_contrato_mandato") {
			// Filtros del usuario
			$fecha_inicio = isset($_GET['fecha_inicio_solicitud']) ? $_GET['fecha_inicio_solicitud'] : '';
			$fecha_fin = isset($_GET['fecha_fin_solicitud']) ? $_GET['fecha_fin_solicitud'] : '';
			$empresa = isset($_GET['id_empresa']) ? $_GET['id_empresa'] : '';

			// Construcción de condiciones WHERE
			$where_fecha = '';
			$where_empresa = '';

			if (!empty($fecha_inicio) && !empty($fecha_fin)) {
				$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
			}

			if (!empty($empresa)) {
				$where_empresa = " AND c.empresa_suscribe_id = '$empresa'";
			}

			// Consulta SQL optimizada para Contrato de Mandato
			$query = "
				SELECT 
					CONCAT(
						IFNULL( co.sigla, '' ),
					IFNULL( c.codigo_correlativo, '' )) AS Código,
					cm.mandante_antecedente AS 'Antecedentes del Mandante',
					cm.mandataria_objetivo AS 'Objetivo del Mandato',
					cm.mandataria_retribucion AS 'Retribución del Mandato',
					DATE_FORMAT( c.created_at, '%d-%m-%Y' ) AS 'F. Solicitud',
					DATE_FORMAT( cm.fecha_inicio, '%d-%m-%Y' ) AS 'F. Inicio',
					DATE_FORMAT( cm.fecha_fin, '%d-%m-%Y' ) AS 'F. Fin',
					cm.plazo_duracion AS 'Plazo de Duración',
					IFNULL( es.nombre, 'En Proceso' ) AS Estado,
					CASE
						WHEN c.estado_aprobacion = 0 THEN
						'En Solicitud' 
						WHEN c.estado_aprobacion = 1 THEN
						'Aprobado' ELSE c.estado_aprobacion 
					END AS 'Estado Aprobación',
					per.nombre AS Mandante,
					per.num_docu AS 'DNI Mandante',
					rs.nombre AS Mandatario,
					rs.num_ruc AS 'RUC Mandatario',
					e.situacion AS 'Situación de Etapa',
					CONCAT( 'http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitud_mandato&id=', c.contrato_id ) AS 'Ver Detalle' 
				FROM 
					cont_contrato c
					INNER JOIN cont_mandato cm ON c.contrato_id = cm.idcontrato
					INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
					LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
					LEFT JOIN cont_propietario prop ON c.contrato_id = prop.contrato_id
					LEFT JOIN cont_persona per ON per.id = prop.persona_id
					LEFT JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
				WHERE 
					c.tipo_contrato_id = 14  
					AND c.status = 1 
					AND c.estado_aprobacion = 1  
					$where_empresa
					$where_fecha
				ORDER BY 
					c.created_at DESC;
			";
			// var_dump($query);
			// die("query");

			// Ejecutar la consulta
			$list_query = $mysqli->query($query);
			$list = [];

			// Procesar resultados
			while ($li = $list_query->fetch_assoc()) {
				$list[] = $li;
			}

			// Columnas a mostrar
			$list_cols = [
				"Código" => "Código",
				"Mandante" => "Mandante",
				"DNI Mandante" => "DNI Mandante",
				"Mandatario" => "Mandatario",
				"RUC Mandatario" => "RUC Mandatario",
				"F. Solicitud" => "F. Solicitud",
				"F. Inicio" => "F. Inicio",
				"F. Fin" => "F. Fin",
				"Plazo de Duración" => "Plazo de Duración",
				"Antecedentes del Mandante" => "Antecedentes del Mandante",
				"Objetivo del Mandato" => "Objetivo del Mandato",
				"Retribución del Mandato" => "Retribución del Mandato",
				"Estado" => "Estado",
				"Estado Aprobación" => "Estado Aprobación",
				"Situación de Etapa" => "Situación de Etapa",
				"Ver Detalle" => "Ver Detalle"
			];

			$list_cols_show = $list_cols;
		} elseif ($export == "cont_contrato_mutuodinero") {
			// Filtros del usuario
			$fecha_inicio = isset($_GET['fecha_inicio_solicitud']) ? $_GET['fecha_inicio_solicitud'] : '';
			$fecha_fin = isset($_GET['fecha_fin_solicitud']) ? $_GET['fecha_fin_solicitud'] : '';
			$empresa = isset($_GET['id_empresa']) ? $_GET['id_empresa'] : '';

			// Construcción de condiciones WHERE
			$where_fecha = '';
			$where_empresa = '';

			if (!empty($fecha_inicio) && !empty($fecha_fin)) {
				$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
			}

			if (!empty($empresa)) {
				$where_empresa = " AND c.empresa_suscribe_id = '$empresa'";
			}

			// Consulta SQL optimizada para Contrato de Mutuo de Dinero
			$query = "
				SELECT 
					CONCAT(IFNULL(co.sigla, ''), IFNULL(c.codigo_correlativo, '')) AS Código,
					cm.mutuante_descripcion AS 'Descripción del Mutuante',
					cm.mutuatario_descripcion AS 'Descripción del Mutuatario',
					cm.tasa_interes AS 'Tasa de Interés',
					cm.plazo_devolucion AS 'Plazo de Devolución',
					DATE_FORMAT(c.created_at, '%d-%m-%Y') AS 'F. Solicitud',
					DATE_FORMAT(c.fecha_suscripcion_contrato, '%d-%m-%Y') AS 'F. Suscripción',
					IFNULL(es.nombre, 'En Proceso') AS Estado,
					CASE 
						WHEN c.estado_aprobacion = 0 THEN 'En Solicitud'
						WHEN c.estado_aprobacion = 1 THEN 'Aprobado'
						ELSE c.estado_aprobacion 
					END AS 'Estado Aprobación',
					per.nombre AS Mutuante,
					per.num_docu AS 'DNI Mutuante',
					rs.nombre AS Mutuatario,
					rs.num_ruc AS 'RUC Mutuatario',
					ce.monto AS 'Monto',
					tm.nombre AS 'Moneda',
					e.situacion AS 'Situación de Etapa',
					CONCAT('http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitud_mutuo_dinero&id=', c.contrato_id) AS 'Ver Detalle'
				FROM 
					cont_contrato c
				INNER JOIN 
					cont_mutuodinero cm ON c.contrato_id = cm.idcontrato
				INNER JOIN 
					cont_contraprestacion ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
				INNER JOIN 
					cont_etapa e ON c.etapa_id = e.etapa_id
				LEFT JOIN 
					cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
				LEFT JOIN 
					cont_estado_solicitud es ON es.id = c.estado_solicitud
				LEFT JOIN 
					cont_propietario prop ON c.contrato_id = prop.contrato_id
				LEFT JOIN 
					cont_persona per ON per.id = prop.persona_id
				LEFT JOIN 
					tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
				LEFT JOIN 
					tbl_moneda tm ON ce.moneda_id = tm.id
				WHERE 
					c.tipo_contrato_id = 15  -- Tipo de contrato para Mutuo de Dinero
					AND c.status = 1 
					AND c.estado_aprobacion = 1  -- Solo contratos aprobados
					$where_empresa
					$where_fecha
				ORDER BY 
					c.created_at DESC;
			";

			// Ejecutar la consulta
			$list_query = $mysqli->query($query);
			$list = [];

			// Procesar resultados
			while ($li = $list_query->fetch_assoc()) {
				$list[] = $li;
			}

			// Columnas a mostrar
			$list_cols = [
				"Código" => "Código",
				"Mutuante" => "Mutuante",
				"DNI Mutuante" => "DNI Mutuante",
				"Mutuatario" => "Mutuatario",
				"RUC Mutuatario" => "RUC Mutuatario",
				"F. Solicitud" => "F. Solicitud",
				"F. Suscripción" => "F. Suscripción",
				"Monto" => "Monto",
				"Moneda" => "Moneda",
				"Tasa de Interés" => "Tasa de Interés",
				"Plazo de Devolución" => "Plazo de Devolución",
				"Descripción del Mutuante" => "Descripción del Mutuante",
				"Descripción del Mutuatario" => "Descripción del Mutuatario",
				"Estado" => "Estado",
				"Estado Aprobación" => "Estado Aprobación",
				"Situación de Etapa" => "Situación de Etapa",
				"Ver Detalle" => "Ver Detalle"
			];

			$list_cols_show = $list_cols;
		}

		///////////////////////////////////////////////////////////////////////////
		// ERICK HAROLD: INICIO REPORTE EXCEL CONTRATO - LOCALES
		elseif ($export == "cont_contrato") {

			$user_id = $login ? $login['id'] : null;
			$area_id = $login ? $login['area_id'] : 0;

			$query = "
				SELECT 
					CONCAT(co.sigla, c.codigo_correlativo) AS codigo, 
					c.contrato_id,
					c.cc_id,
					UPPER(c.nombre_tienda) AS nombre_tienda,
					i.area_arrendada,
					r.nombre AS empresa_suscribe,
					c.fecha_solicitud,
					dp.nombre AS departamento, 
					pr.nombre AS provincia, 
					dt.nombre AS distrito, 
					i.ubicacion,
					i.num_partida_registral,
					i.direccion_municipal ,
					ce.fecha_suscripcion,
					e.etapa_id,
					e.nombre AS etapa,
					e.descripcion AS etapa_descripcion,
					e.situacion AS etapa_situacion,
					ce.monto_renta,
					m.nombre AS tipo_moneda,
					ce.fecha_inicio,
					ce.fecha_fin,
					ce.num_alerta_vencimiento,
					c.created_at,
					ce.renovacion_automatica,
					rz.nombre AS nombre_arrendador,
					CASE
						WHEN c.etapa_id = 1 AND c.etapa_conta_id IS NULL THEN 'Pendiente'
						WHEN c.etapa_id = 5 AND c.etapa_conta_id IS NULL THEN 'Firmado'
						WHEN c.etapa_id = 5 AND c.etapa_conta_id = 1 THEN 'Firmado y legalizado'
					END AS estado_v,
					cd.estado_resolucion
				FROM
					cont_contrato c
					INNER JOIN cont_contrato_detalle as cd ON cd.contrato_id = c.contrato_id
					INNER JOIN cont_inmueble i ON cd.id = i.contrato_detalle_id AND i.status = 1
					INNER JOIN cont_condicion_economica ce ON cd.id = ce.contrato_detalle_id AND ce.status = 1 
					INNER JOIN tbl_moneda m ON ce.tipo_moneda_id = m.id
					LEFT JOIN tbl_locales as l ON l.contrato_id = c.contrato_id
					INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
					INNER JOIN tbl_areas a ON e.area_id = a.id
					INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
					INNER JOIN tbl_ubigeo AS dp ON dp.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND dp.cod_prov = '00' AND dp.cod_dist = '00' 
					INNER JOIN tbl_ubigeo AS pr ON pr.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND SUBSTRING(i.ubigeo_id, 3, 2) = pr.cod_prov AND pr.cod_dist = '00'
					INNER JOIN tbl_ubigeo AS dt ON dt.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND SUBSTRING(i.ubigeo_id, 3, 2) = dt.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = dt.cod_dist
					LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					LEFT JOIN tbl_razon_social rz ON  rz.id = c.empresa_suscribe_id
				WHERE c.status IN (1,2) AND c.etapa_id = 5 
				";

			$where_empresa = '';
			$where_nombre_tienda = '';
			$where_centro_costos = '';
			$where_moneda = '';
			$where_ubigeo = '';
			$where_fecha_solicitud = '';
			$where_fecha_inicio = '';
			$where_fecha_suscripcion = '';
			$where_locales = '';
			$where_etapa = '';

			if (!empty($id_empresa)) {
				$where_empresa = " AND c.empresa_suscribe_id = '" . $id_empresa . "' ";
			}
			if ($login["usuario_locales"]) {
				$where_locales = " AND l.id IN (" . implode(",", $login["usuario_locales"]) . ")";
			}


			if (!empty($nombre_tienda)) {
				$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . urldecode($nombre_tienda) . "%' ";
			}

			if (!empty($centro_costos)) {
				$where_centro_costos = " AND c.cc_id LIKE '%" . urldecode($centro_costos) . "%' ";
			}

			if (!empty($moneda)) {
				$where_moneda = " AND m.id = '" . $moneda . "' ";
			}

			if (!empty($id_departamento)) {
				$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
			}

			if (!empty($fecha_inicio_solicitud) && !empty($fecha_fin_solicitud)) {
				$where_fecha_solicitud = " AND c.created_at BETWEEN '$fecha_inicio_solicitud 00:00:00' AND '$fecha_fin_solicitud 23:59:59'";
			} elseif (!empty($fecha_inicio_solicitud)) {
				$where_fecha_solicitud = " AND c.created_at >= '$fecha_inicio_solicitud 00:00:00'";
			} elseif (!empty($fecha_fin_solicitud)) {
				$where_fecha_solicitud = " AND c.created_at <= '$fecha_fin_solicitud 23:59:59'";
			}

			if (!empty($fecha_inicio_inicio) && !empty($fecha_fin_inicio)) {
				$where_fecha_inicio = " AND ce.fecha_inicio BETWEEN '$fecha_inicio_inicio 00:00:00' AND '$fecha_fin_inicio 23:59:59'";
			} elseif (!empty($fecha_inicio_inicio)) {
				$where_fecha_inicio = " AND ce.fecha_inicio >= '$fecha_inicio_inicio 00:00:00'";
			} elseif (!empty($fecha_fin_inicio)) {
				$where_fecha_inicio = " AND ce.fecha_inicio <= '$fecha_fin_inicio 23:59:59'";
			}

			if (!empty($fecha_inicio_suscripcion) && !empty($fecha_fin_suscripcion)) {
				$where_fecha_suscripcion = " AND ce.fecha_suscripcion BETWEEN '$fecha_inicio_suscripcion 00:00:00' AND '$fecha_fin_suscripcion 23:59:59'";
			} elseif (!empty($fecha_inicio_suscripcion)) {
				$where_fecha_suscripcion = " AND ce.fecha_suscripcion >= '$fecha_inicio_suscripcion 00:00:00'";
			} elseif (!empty($fecha_fin_suscripcion)) {
				$where_fecha_suscripcion = " AND ce.fecha_suscripcion <= '$fecha_fin_suscripcion 23:59:59'";
			}

			if ($etapa == "1") { //Firmado
				$where_etapa = " AND cd.estado_resolucion <> 2";
			} else if ($etapa == "2") { // Resuelto
				$where_etapa = " AND cd.estado_resolucion = 2";
			}


			$query .= $where_empresa;
			$query .= $where_nombre_tienda;
			$query .= $where_centro_costos;
			$query .= $where_moneda;
			$query .= $where_ubigeo;
			$query .= $where_fecha_solicitud;
			$query .= $where_fecha_inicio;
			$query .= $where_fecha_suscripcion;
			$query .= $where_locales;
			$query .= $where_etapa;


			$list_query = $mysqli->query($query);


			$list = array();

			while ($li = $list_query->fetch_assoc()) {
				$li['renovacion_automatica'] = $li['renovacion_automatica'] == 1 ? 'SI' : 'NO';
				if ($li['fecha_fin'] < date('Y-m-d')) {
					$li['vigencia'] = 'VENCIDA';
				} else {
					$li['vigencia'] = 'VIGENTE';
				}

				if ($li['estado_resolucion'] == 2) {
					$li['estado_resolucion'] = 'Resuelto';
				} else {
					$li['estado_resolucion'] = $li['estado_v'];
				}

				$list[] = $li;
			}

			$list_cols = array();
			$list_cols["codigo"] = "Código";
			$list_cols["cc_id"] = "Centro de Costos";
			$list_cols["nombre_arrendador"] = "Arrendador";
			$list_cols["nombre_tienda"] = "Tienda";
			$list_cols["area_arrendada"] = "Área (m2)";
			$list_cols["departamento"] = "Departamento";
			$list_cols["provincia"] = "Provincia";
			$list_cols["distrito"] = "Distrito";
			$list_cols["ubicacion"] = "Ubicacion inmueble";
			$list_cols["direccion_municipal"] = "Dirección municipal";
			$list_cols["empresa_suscribe"] = "Empresa Arrendataria";
			$list_cols["fecha_suscripcion"] = "F. suscripcion";
			$list_cols["monto_renta"] = "Monto renta";
			$list_cols["tipo_moneda"] = "Tipo moneda";
			$list_cols["created_at"] = "F. solicitud";
			$list_cols["fecha_suscripcion"] = "F. suscripción";
			$list_cols["fecha_inicio"] = "F. inicio contrato";
			$list_cols["fecha_fin"] = "F. fin contrato";
			$list_cols["renovacion_automatica"] = "Renovación Automática";
			$list_cols["vigencia"] = "Vigencia";
			$list_cols["estado_resolucion"] = "Etapa";

			$list_cols_show = $list_cols;
		}

		// ERICK HAROLD: FIN REPORTE EXCEL CONTRATO - LOCALES

		//YONATHAN MAMANI C.: INICIO REPORTE DE SOLICITUD DE CONTRATO
		elseif ($export == "cont_contrato_solicitud") {
			include '/var/www/html/sys/set_contrato_seguimiento_proceso.php';
			// $area_id = $login ? $login['area_id'] : 0;
			$area_id =  0;
			$usuario_permisos = array();
			$usuario_permisos_query = $mysqli->query("SELECT p.usuario_id, p.menu_id, b.boton
														FROM tbl_permisos p
														LEFT JOIN tbl_botones b ON (p.boton_id = b.id)
														WHERE p.usuario_id = '" . $login["id"] . "' AND p.estado = '1'");
			while ($usu_per = $usuario_permisos_query->fetch_assoc()) {
				$usuario_permisos[$usu_per["menu_id"]][] = $usu_per["boton"];
			}
			$area_id =  $_GET['area_id'];
			$cargo_id = $_GET['cargo_id'];
			// if ($tipo_contrato_id == 12) {
			// echo "Solicitud de Contratos de Arrendamiento";
			// $empresa = $empresa;
			// $area_solicitante = $area;
			// $ruc = $ruc;
			// $razon_social = $razon_social;
			// $moneda = $moneda;
			// $nombre_tienda = trim($nombre_tienda);

			// if (isset($estado_sol) && $estado_sol != "undefined") {
			// 	$estado_sol = $estado_sol;
			// } else {
			// 	$estado_sol = '';
			// }

			// $id_departamento = $id_departamento;
			// $id_provincia = $id_provincia;
			// $id_distrito = $id_distrito;


			// $where_empresa = "";
			// $where_area_solicitante = "";
			// $where_ruc = "";
			// $where_razon_social = "";
			// $where_moneda = "";
			// $where_fecha = '';
			// $where_ubigeo = '';
			// $where_nombre_tienda = '';
			// $where_estado_sol = '';
			// $where_estado_sol_v2 = '';

			// if (!empty($empresa)) {
			// 	$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
			// }

			// if (!empty($area_solicitante)) {
			// 	$where_area_solicitante = " AND a.id = '" . $area_solicitante . "' ";
			// }

			// if (!empty($ruc)) {
			// 	$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
			// }

			// if (!empty($razon_social)) {
			// 	$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
			// }

			// if (!empty($moneda)) {
			// 	$where_moneda = " AND ce.tipo_moneda_id = '" . $moneda . "'";
			// }

			// if (!empty($fecha_inicio) && !empty($fecha_fin)) {
			// 	$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
			// } elseif (!empty($fecha_inicio)) {
			// 	$where_fecha = " AND c.created_at >= '$fecha_inicio 00:00:00'";
			// } elseif (!empty($fecha_fin)) {
			// 	$where_fecha = " AND c.created_at <= '$fecha_fin 23:59:59'";
			// }

			// if (!empty($id_departamento)) {
			// 	$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
			// }

			// if (!empty($nombre_tienda)) {
			// 	$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
			// }

			// if (!empty($estado_sol)) {
			// 	$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
			// }

			// if (!empty($estado_sol_v2)) {
			// 	if ((int) $estado_sol_v2 === 1) {
			// 		$where_estado_sol_v2 = " AND ( c.cancelado_id != 1 || c.cancelado_id IS NULL )";
			// 	} elseif ((int) $estado_sol_v2 === 2) {
			// 		$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
			// 	}
			// }

			// $query = "SELECT 
			// 			CONCAT(IFNULL(co.sigla,''),IFNULL(c.codigo_correlativo,'')) AS numero,
			// 			c.nombre_tienda, 
			// 			c.cc_id,
			// 			i.ubicacion,
			// 			DATE_FORMAT(c.created_at,'%d-%m-%Y') AS fecha_solicitud, 	
			// 			ce.fecha_suscripcion,
			// 			CASE
			// 				WHEN c.verificar_giro  = '1' THEN 'Si'
			// 				ELSE 'No'
			// 			END AS giro,
			// 			es.nombre AS nombre_estado_solicitud,
			// 			CONCAT(c.dias_habiles, ' días hábiles') as dias_habiles,
			// 			c.cancelado_id
			// 		FROM 
			// 			cont_contrato c
			// 			INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
			// 			INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.contrato_detalle_id = ce.contrato_detalle_id
			// 			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			// 			INNER JOIN tbl_areas a ON e.area_id = a.id
			// 			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			// 			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
			// 		WHERE 
			// 			c.tipo_contrato_id = 1 
			// 			AND c.status = 1 
			// 			AND ((e.etapa_id = 1 AND c.estado_aprobacion = 0))
			// 		" . $where_empresa . "
			// 		" . $where_area_solicitante . "
			// 		" . $where_ruc . "
			// 		" . $where_razon_social . "
			// 		" . $where_moneda . "
			// 		" . $where_fecha . "
			// 		" . $where_ubigeo . "
			// 		" . $where_nombre_tienda . "
			// 		" . $where_estado_sol . "
			// 		" . $where_estado_sol_v2 . "
			// 		ORDER BY c.created_at DESC";



			// $list_query = $mysqli->query($query);
			// $list = array();

			// while ($li = $list_query->fetch_assoc()) {
			// 	if ($li["cancelado_id"] == 1) {
			// 		$li["nombre_estado_solicitud"] = 'cancelado';
			// 	} elseif ($area_id == '33') {
			// 		$li["nombre_estado_solicitud"] = $li["nombre_estado_solicitud"];
			// 	} else {
			// 		$li["nombre_estado_solicitud"] = 'En proceso';
			// 	}

			// 	if ($area_id == '33') {
			// 		$li["dias_habiles"] = $li["dias_habiles"];
			// 	} else {
			// 		$li["dias_habiles"] = '';
			// 	}
			// 	$list[] = $li;
			// }

			// $list_cols = array();
			// $list_cols["numero"] = "Código";

			// $list_cols["ubicacion"] = "Ubicación del Inmueble";
			// $list_cols["fecha_solicitud"] = "F. Solicitud";
			// $list_cols["fecha_suscripcion"] = "F. Suscripción";

			// $list_cols["nombre_estado_solicitud"] = "Estado";
			// $list_cols_show = $list_cols;
			// }

			if ($tipo_contrato_id == 12) {
				// Filtros del usuario
				$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
				$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
				$empresa = isset($_GET['empresa']) ? $_GET['empresa'] : '';
				// var_dump($empresa);
				// die("excel empresa");

				// Construcción de condiciones WHERE
				$where_fecha = '';
				$where_empresa = '';

				if (!empty($fecha_inicio) && !empty($fecha_fin)) {
					$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
				}

				if (!empty($empresa)) {
					$where_empresa = " AND c.empresa_suscribe_id = '$empresa'";
				}

				// Consulta SQL optimizada
				$query = "
					SELECT
						CONCAT(
							IFNULL( co.sigla, '' ),
						IFNULL( c.codigo_correlativo, '' )) AS Código,
						i.ubicacion AS 'Ubicación del Inmueble',
						DATE_FORMAT( c.created_at, '%d-%m-%Y' ) AS 'F. Solicitud',
						DATE_FORMAT( ce.fecha_suscripcion, '%d-%m-%Y' ) AS 'F. Suscripción',
						IFNULL(es.nombre, 'En Proceso') AS Estado,
						CASE
							WHEN c.estado_aprobacion = 0 THEN 'En Solicitud'
							ELSE c.estado_aprobacion 
						END AS 'Estado Aprobacion',
						cb.nombre AS Arrendador,
						cb.num_docu AS 'DNI Arrendador',
						rs.nombre AS Arrendatario,
						rs.num_ruc AS 'RUC Arrendatario',
						CONCAT( 'http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitudV2&id=', c.contrato_id ) AS 'Ver Detalle' 
					FROM
						cont_contrato c
						INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id 
						AND ce.STATUS = 1
						INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id 
						AND i.contrato_detalle_id = ce.contrato_detalle_id
						INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
						INNER JOIN tbl_areas a ON e.area_id = a.id
						LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
						LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
						LEFT JOIN cont_beneficiarios cb ON c.contrato_id = cb.contrato_id
						LEFT JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id 
					WHERE
						c.tipo_contrato_id = 1 
						AND ((e.etapa_id = 1 AND c.estado_aprobacion = 0))
						AND c.STATUS = 1 
						" . $where_empresa . "
						" . $where_fecha . "
					ORDER BY
						c.created_at DESC;
				";
				// var_dump($query);
				// die("excel rip");

				// Ejecutar la consulta
				$list_query = $mysqli->query($query);
				$list = [];

				// Procesar resultados
				while ($li = $list_query->fetch_assoc()) {
					$list[] = $li;
				}

				// Columnas a mostrar
				$list_cols = [
					"Código" => "Código",
					"Ubicación del Inmueble" => "Ubicación del Inmueble",
					"F. Solicitud" => "F. Solicitud",
					"F. Suscripción" => "F. Suscripción",
					"Estado" => "Estado",
					"Estado Aprobacion" => "Estado Aprobación",
					"Arrendador" => "Arrendador",
					"DNI Arrendador" => "DNI Arrendador",
					"Arrendatario" => "Arrendatario",
					"RUC Arrendatario" => "RUC Arrendatario",
					"Ver Detalle" => "Ver Detalle"
				];
				$list_cols_show = $list_cols;

				// print_r(value: $list);
			}

			if ($tipo_contrato_id == 13) {
				// Filtros del usuario
				$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
				$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
				$empresa = isset($_GET['empresa']) ? $_GET['empresa'] : '';

				// Construcción de condiciones WHERE
				$where_fecha = '';
				$where_empresa = '';

				if (!empty($fecha_inicio) && !empty($fecha_fin)) {
					$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
				}

				if (!empty($empresa)) {
					$where_empresa = " AND c.empresa_suscribe_id = '$empresa'";
				}

				// Consulta SQL optimizada
				$query = "
					SELECT
						CONCAT(
							IFNULL( co.sigla, '' ),
						IFNULL( c.codigo_correlativo, '' )) AS Código,
						cl.nombreservicio AS 'Nombre del Servicio',
						DATE_FORMAT( c.created_at, '%d-%m-%Y' ) AS 'F. Solicitud',
						DATE_FORMAT( cl.fechainicio, '%d-%m-%Y' ) AS 'F. Inicio',
						DATE_FORMAT( cl.fechafin, '%d-%m-%Y' ) AS 'F. Fin',
						IFNULL( es.nombre, 'En Proceso' ) AS Estado,
					CASE
							
							WHEN c.estado_aprobacion = 0 THEN
							'En Solicitud' ELSE c.estado_aprobacion 
						END AS 'Estado Aprobación',
						per.nombre AS Locador,
						per.num_docu AS 'DNI Locador',
						rs.nombre AS Locatario,
						rs.num_ruc AS 'RUC Locatario',
						cl.locador_descripcion AS 'Descripción del Locador',
						cl.locatario_descripcion AS 'Descripción del Locatario',
						cl.locador_funciones AS 'Funciones del Locador',
						CONCAT( 'http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitud_locacion_servicio&id=', c.contrato_id ) AS 'Ver Detalle' 
					FROM
						cont_contrato c
						INNER JOIN cont_locacion cl ON c.contrato_id = cl.idcontrato
						INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
						LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
						LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
						LEFT JOIN cont_propietario cb ON c.contrato_id = cb.contrato_id
						LEFT JOIN cont_persona per ON per.id = cb.persona_id
						LEFT JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id 
					WHERE
						c.tipo_contrato_id = 13 
						AND c.STATUS = 1 
						AND ((
								e.etapa_id = 1 
								AND c.estado_aprobacion = 0 
							)) 
					ORDER BY
						c.created_at DESC;
			";
				// var_dump($query);
				// die("excel rip");

				// Ejecutar la consulta
				$list_query = $mysqli->query($query);
				$list = [];

				// Procesar resultados
				while ($li = $list_query->fetch_assoc()) {
					$list[] = $li;
				}

				// Columnas a mostrar
				$list_cols = [
					"Código" => "Código",
					"Nombre del Servicio" => "Nombre del Servicio",
					"F. Solicitud" => "F. Solicitud",
					"F. Inicio" => "F. Inicio",
					"F. Fin" => "F. Fin",
					"Estado" => "Estado",
					"Estado Aprobación" => "Estado Aprobación",
					"Locador" => "Locador",
					"DNI Locador" => "DNI Locador",
					"Locatario" => "Locatario",
					"RUC Locatario" => "RUC Locatario",
					"Descripción del Locador" => "Descripción del Locador",
					"Descripción del Locatario" => "Descripción del Locatario",
					"Funciones del Locador" => "Funciones del Locador",
					"Ver Detalle" => "Ver Detalle"
				];
				$list_cols_show = $list_cols;

				// print_r(value: $list);
			}

			if ($tipo_contrato_id == 14) {
				// Filtros del usuario
				$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
				$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
				$empresa = isset($_GET['empresa']) ? $_GET['empresa'] : '';

				// Construcción de condiciones WHERE
				$where_fecha = '';
				$where_empresa = '';

				if (!empty($fecha_inicio) && !empty($fecha_fin)) {
					$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
				}

				if (!empty($empresa)) {
					$where_empresa = " AND c.empresa_suscribe_id = '$empresa'";
				}

				$query = "
					SELECT 
						CONCAT(IFNULL(co.sigla, ''), IFNULL(c.codigo_correlativo, '')) AS Código,
						cm.mandante_antecedente AS 'Antecedentes del Mandante',
						cm.mandataria_objetivo AS 'Objetivo del Mandato',
						cm.mandataria_retribucion AS 'Retribución del Mandato',
						DATE_FORMAT(c.created_at, '%d-%m-%Y') AS 'F. Solicitud',
						DATE_FORMAT(cm.fecha_inicio, '%d-%m-%Y') AS 'F. Inicio',
						DATE_FORMAT(cm.fecha_fin, '%d-%m-%Y') AS 'F. Fin',
						cm.plazo_duracion AS 'Plazo de Duración',
						IFNULL(es.nombre, 'En Proceso') AS Estado,
						CASE 
							WHEN c.estado_aprobacion = 0 THEN 'En Solicitud'
							ELSE c.estado_aprobacion 
						END AS 'Estado Aprobación',
						per.nombre AS Mandante,  
						per.num_docu AS 'DNI Mandante',
						rs.nombre AS Mandatario,  
						rs.num_ruc AS 'RUC Mandatario',
						CONCAT('http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitud_mandato&id=', c.contrato_id) AS 'Ver Detalle'
					FROM 
						cont_contrato c
					INNER JOIN 
						cont_mandato cm ON c.contrato_id = cm.idcontrato
					INNER JOIN 
						cont_etapa e ON c.etapa_id = e.etapa_id
					LEFT JOIN 
						cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					LEFT JOIN 
						cont_estado_solicitud es ON es.id = c.estado_solicitud
					LEFT JOIN 
						cont_propietario cb ON c.contrato_id = cb.contrato_id
					LEFT JOIN 
						cont_persona per ON per.id = cb.persona_id  
					LEFT JOIN 
						tbl_razon_social rs ON c.empresa_suscribe_id = rs.id 
					WHERE 
						c.tipo_contrato_id = 14 
						AND c.status = 1 
						AND ((e.etapa_id = 1 AND c.estado_aprobacion = 0))
						$where_empresa
						$where_fecha
					ORDER BY 
						c.created_at DESC;
				";
				// var_dump($query);
				// die("excel rip");

				// Ejecutar la consulta
				$list_query = $mysqli->query($query);
				$list = [];

				// Procesar resultados
				while ($li = $list_query->fetch_assoc()) {
					$list[] = $li;
				}

				$list_cols = [
					"Código" => "Código",
					"Antecedentes del Mandante" => "Antecedentes del Mandante",
					"Objetivo del Mandato" => "Objetivo del Mandato",
					"Retribución del Mandato" => "Retribución del Mandato",
					"F. Solicitud" => "F. Solicitud",
					"F. Inicio" => "F. Inicio",
					"F. Fin" => "F. Fin",
					"Plazo de Duración" => "Plazo de Duración",
					"Estado" => "Estado",
					"Estado Aprobación" => "Estado Aprobación",
					"Mandante" => "Mandante",
					"DNI Mandante" => "DNI Mandante",
					"Mandatario" => "Mandatario",
					"RUC Mandatario" => "RUC Mandatario",
					"Ver Detalle" => "Ver Detalle"
				];
				$list_cols_show = $list_cols;
			}

			if ($tipo_contrato_id == 15) {
				// Filtros del usuario
				$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
				$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
				$empresa = isset($_GET['empresa']) ? $_GET['empresa'] : '';

				// Construcción de condiciones WHERE
				$where_fecha = '';
				$where_empresa = '';

				if (!empty($fecha_inicio) && !empty($fecha_fin)) {
					$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
				}

				if (!empty($empresa)) {
					$where_empresa = " AND c.empresa_suscribe_id = '$empresa'";
				}

				$query = "
					SELECT 
						CONCAT(IFNULL(co.sigla, ''), IFNULL(c.codigo_correlativo, '')) AS Código,
						cm.mutuante_descripcion AS 'Descripción del Mutuante',
						cm.mutuatario_descripcion AS 'Descripción del Mutuatario',
						cm.tasa_interes AS 'Tasa de Interés',
						cm.plazo_devolucion AS 'Plazo de Devolución',
						DATE_FORMAT(c.created_at, '%d-%m-%Y') AS 'F. Solicitud',
						DATE_FORMAT(c.fecha_suscripcion_contrato, '%d-%m-%Y') AS 'F. Suscripción',
						IFNULL(es.nombre, 'En Proceso') AS Estado,
						CASE 
							WHEN c.estado_aprobacion = 0 THEN 'En Solicitud'
							ELSE c.estado_aprobacion 
						END AS 'Estado Aprobación',
						per.nombre AS Mutuante,
						per.num_docu AS 'DNI Mutuante',
						rs.nombre AS Mutuatario,
						rs.num_ruc AS 'RUC Mutuatario',
						CONCAT('http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitud_mutuo_dinero&id=', c.contrato_id) AS 'Ver Detalle'
					FROM 
						cont_contrato c
					INNER JOIN 
						cont_mutuodinero cm ON c.contrato_id = cm.idcontrato
					INNER JOIN 
						cont_etapa e ON c.etapa_id = e.etapa_id
					LEFT JOIN 
						cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					LEFT JOIN 
						cont_estado_solicitud es ON es.id = c.estado_solicitud
					LEFT JOIN 
						cont_propietario cb ON c.contrato_id = cb.contrato_id
					LEFT JOIN 
						cont_persona per ON per.id = cb.persona_id
					LEFT JOIN 
						tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
					WHERE 
						c.tipo_contrato_id = 15 
						AND c.status = 1 
						AND ((e.etapa_id = 1 AND c.estado_aprobacion = 0))
						$where_empresa
						$where_fecha
					ORDER BY 
						c.created_at DESC;
				";
				// var_dump($query);
				// die("excel rip");

				// Ejecutar la consulta
				$list_query = $mysqli->query($query);
				$list = [];

				// Procesar resultados
				while ($li = $list_query->fetch_assoc()) {
					$list[] = $li;
				}

				$list_cols = [
					"Código" => "Código",
					"Descripción del Mutuante" => "Descripción del Mutuante",
					"Descripción del Mutuatario" => "Descripción del Mutuatario",
					"Tasa de Interés" => "Tasa de Interés",
					"Plazo de Devolución" => "Plazo de Devolución",
					"F. Solicitud" => "F. Solicitud",
					"F. Suscripción" => "F. Suscripción",
					"Estado" => "Estado",
					"Estado Aprobación" => "Estado Aprobación",
					"Mutuante" => "Mutuante",
					"DNI Mutuante" => "DNI Mutuante",
					"Mutuatario" => "Mutuatario",
					"RUC Mutuatario" => "RUC Mutuatario",
					"Ver Detalle" => "Ver Detalle"
				];
				$list_cols_show = $list_cols;
			}


			if ($tipo_contrato_id == 3) {
				// Filtros del usuario
				$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
				$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
				$empresa = isset($_GET['empresa']) ? $_GET['empresa'] : '';
				// var_dump($empresa);
				// die("excel empresa");

				// Construcción de condiciones WHERE
				$where_fecha = '';
				$where_empresa = '';

				if (!empty($fecha_inicio) && !empty($fecha_fin)) {
					$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
				}

				if (!empty($empresa)) {
					$where_empresa = " AND c.empresa_suscribe_id = '$empresa'";
				}

				// Consulta SQL optimizada
				$query = "
					SELECT 
						CONCAT('Adenda N° ', IFNULL(ca.codigo, ''), '-A', IFNULL(c.codigo_correlativo, '')) AS Código,
						ca.created_at AS 'F. Solicitud',
						IFNULL(es.nombre, 'Pendiente') AS Estado,
						CASE 
							WHEN c.estado_aprobacion = 0 THEN 'En Solicitud'
							ELSE c.estado_aprobacion 
						END AS 'Estado Aprobación',
						cb.nombre AS Arrendador,
						cb.num_docu AS 'DNI Arrendador',
						rs.nombre AS Arrendatario,
						rs.num_ruc AS 'RUC Arrendatario',
						u.usuario AS Solicitante,
					CONCAT('http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitudV2&id=', c.contrato_id, '&adenda_id=', ca.id) AS 'Ver Detalle'
					FROM 
						cont_adendas ca
					INNER JOIN 
						cont_contrato c ON ca.contrato_id = c.contrato_id
					INNER JOIN 
						cont_etapa e ON c.etapa_id = e.etapa_id
					LEFT JOIN 
						cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					LEFT JOIN 
						cont_estado_solicitud es ON es.id = c.estado_solicitud
					LEFT JOIN 
						cont_beneficiarios cb ON c.contrato_id = cb.contrato_id
					LEFT JOIN 
						tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
					LEFT JOIN 
						tbl_usuarios u ON ca.user_created_id = u.id
					WHERE 
						ca.procesado = 0 
						AND ca.status = 1 
						AND c.tipo_contrato_id = 1 
						AND c.status = 1 
						$where_empresa
						$where_fecha
					ORDER BY 
						ca.created_at DESC;
				";
				// var_dump($query);
				// die("excel rip");

				// Ejecutar la consulta
				$list_query = $mysqli->query($query);
				$list = [];

				// Procesar resultados
				while ($li = $list_query->fetch_assoc()) {
					$list[] = $li;
				}

				// Columnas a mostrar
				$list_cols = [
					"Código" => "Código",
					"Solicitante" => "Solicitante",
					"F. Solicitud" => "F. Solicitud",
					"Estado" => "Estado",
					"Arrendador" => "Arrendador",
					"DNI Arrendador" => "DNI Arrendador",
					"Arrendatario" => "Arrendatario",
					"RUC Arrendatario" => "RUC Arrendatario",
					"Ver Detalle" => "Ver Detalle"
				];
				$list_cols_show = $list_cols;
				// 			var_dump($list_cols_show);
				// die("excel rip");

				// print_r(value: $list);
			}

			if ($tipo_contrato_id == 16) {
				// Filtros del usuario
				$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
				$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
				$empresa = isset($_GET['empresa']) ? $_GET['empresa'] : '';
				// var_dump($empresa);
				// die("excel empresa");

				// Construcción de condiciones WHERE
				$where_fecha = '';
				$where_empresa = '';

				if (!empty($fecha_inicio) && !empty($fecha_fin)) {
					$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
				}

				if (!empty($empresa)) {
					$where_empresa = " AND c.empresa_suscribe_id = '$empresa'";
				}

				// Consulta SQL optimizada
				$query = "
					SELECT 
						CONCAT('Adenda N° ', IFNULL(ca.codigo, ''), '-LS', IFNULL(c.codigo_correlativo, '')) AS Código,
						ca.created_at AS 'F. Solicitud',
						IFNULL(es.nombre, 'Pendiente') AS Estado,
						CASE 
							WHEN c.estado_aprobacion = 0 THEN 'En Solicitud'
							ELSE c.estado_aprobacion 
						END AS 'Estado Aprobación',
					per.nombre AS Locador,
					per.num_docu AS 'DNI Locador',
					rs.nombre AS Locatario,
					rs.num_ruc AS 'RUC Locatario',
						u.usuario AS Solicitante,
					CONCAT('http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitud_locacion_servicio&id=', c.contrato_id, '&adenda_id=', ca.id) AS 'Ver Detalle'
					FROM 
						cont_adendas ca
					INNER JOIN 
						cont_contrato c ON ca.contrato_id = c.contrato_id
					INNER JOIN 
						cont_etapa e ON c.etapa_id = e.etapa_id
					LEFT JOIN 
						cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					LEFT JOIN 
						cont_estado_solicitud es ON es.id = c.estado_solicitud
					LEFT JOIN cont_propietario cp ON c.contrato_id = cp.contrato_id
					LEFT JOIN cont_persona per ON per.id = cp.persona_id
					LEFT JOIN 
						tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
					LEFT JOIN 
						tbl_usuarios u ON ca.user_created_id = u.id
					WHERE 
						ca.procesado = 0 
						AND ca.status = 1 
						AND c.tipo_contrato_id = 13 
						AND c.status = 1 
						$where_empresa
						$where_fecha
					ORDER BY 
						ca.created_at DESC;
				";
				// var_dump($query);
				// die("excel rip");

				// Ejecutar la consulta
				$list_query = $mysqli->query($query);
				$list = [];

				// Procesar resultados
				while ($li = $list_query->fetch_assoc()) {
					$list[] = $li;
				}

				// Columnas a mostrar
				$list_cols = [
					"Código" => "Código",
					"Solicitante" => "Solicitante",
					"F. Solicitud" => "F. Solicitud",
					"Estado" => "Estado",
					"Locador" => "Locador",
					"DNI Locador" => "DNI Locador",
					"Locatario" => "Locatario",
					"RUC Locatario" => "RUC Locatario",
					"Ver Detalle" => "Ver Detalle"
				];
				$list_cols_show = $list_cols;
				// 			var_dump($list_cols_show);
				// die("excel rip");

				// print_r(value: $list);
			}

			if ($tipo_contrato_id == 17) {
				// Filtros del usuario
				$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
				$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
				$empresa = isset($_GET['empresa']) ? $_GET['empresa'] : '';
				// var_dump($empresa);
				// die("excel empresa");

				// Construcción de condiciones WHERE
				$where_fecha = '';
				$where_empresa = '';

				if (!empty($fecha_inicio) && !empty($fecha_fin)) {
					$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
				}

				if (!empty($empresa)) {
					$where_empresa = " AND c.empresa_suscribe_id = '$empresa'";
				}

				// Consulta SQL optimizada
				$query = "
					SELECT 
						CONCAT('Adenda N° ', IFNULL(ca.codigo, ''), '-M', IFNULL(c.codigo_correlativo, '')) AS Código,
						ca.created_at AS 'F. Solicitud',
						IFNULL(es.nombre, 'Pendiente') AS Estado,
					CASE
						WHEN c.estado_aprobacion = 0 THEN 'En Solicitud' 
						WHEN c.estado_aprobacion = 1 THEN 'Aprobado'
						ELSE c.estado_aprobacion
					END AS 'Estado Aprobación',
					per.nombre AS Mutuante,
					per.num_docu AS 'DNI Mutuante',
					rs.nombre AS Mutuatario,
					rs.num_ruc AS 'RUC Mutuatario',
						u.usuario AS Solicitante,
					CONCAT('http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitud_mandato&id=', c.contrato_id, '&adenda_id=', ca.id) AS 'Ver Detalle'
					FROM 
						cont_adendas ca
					INNER JOIN 
						cont_contrato c ON ca.contrato_id = c.contrato_id
					INNER JOIN 
						cont_etapa e ON c.etapa_id = e.etapa_id
					LEFT JOIN 
						cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					LEFT JOIN 
						cont_estado_solicitud es ON es.id = c.estado_solicitud
					LEFT JOIN cont_propietario cp ON c.contrato_id = cp.contrato_id
					LEFT JOIN cont_persona per ON per.id = cp.persona_id
					LEFT JOIN 
						tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
					LEFT JOIN 
						tbl_usuarios u ON ca.user_created_id = u.id
					WHERE 
						ca.procesado = 0 
						AND ca.status = 1 
						AND c.tipo_contrato_id = 14 
						AND c.status = 1 
						$where_empresa
						$where_fecha
					ORDER BY 
						ca.created_at DESC;
				";
				// var_dump($query);
				// die("excel rip");

				// Ejecutar la consulta
				$list_query = $mysqli->query($query);
				$list = [];

				// Procesar resultados
				while ($li = $list_query->fetch_assoc()) {
					$list[] = $li;
				}

				// Columnas a mostrar
				$list_cols = [
					"Código" => "Código",
					"Solicitante" => "Solicitante",
					"F. Solicitud" => "F. Solicitud",
					"Estado Aprobación" => "Estado Aprobación",
					"Mutuante" => "Mandante",
					"DNI Mutuante" => "DNI Mandante",
					"Mutuatario" => "Mandatario",
					"RUC Mutuatario" => "RUC Mandatario",
					"Ver Detalle" => "Ver Detalle"
				];
				$list_cols_show = $list_cols;
			}

			if ($tipo_contrato_id == 18) {
				// Filtros del usuario
				$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
				$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
				$empresa = isset($_GET['empresa']) ? $_GET['empresa'] : '';
				// var_dump($empresa);
				// die("excel empresa");

				// Construcción de condiciones WHERE
				$where_fecha = '';
				$where_empresa = '';

				if (!empty($fecha_inicio) && !empty($fecha_fin)) {
					$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
				}

				if (!empty($empresa)) {
					$where_empresa = " AND c.empresa_suscribe_id = '$empresa'";
				}

				// Consulta SQL optimizada
				$query = "
					SELECT 
						CONCAT('Adenda N° ', IFNULL(ca.codigo, ''), '-MD', IFNULL(c.codigo_correlativo, '')) AS Código,
						ca.created_at AS 'F. Solicitud',
						IFNULL(es.nombre, 'Pendiente') AS Estado,
					CASE
						WHEN c.estado_aprobacion = 0 THEN 'En Solicitud' 
						WHEN c.estado_aprobacion = 1 THEN 'Aprobado'
						ELSE c.estado_aprobacion
					END AS 'Estado Aprobación',
					per.nombre AS Mutuante,
					per.num_docu AS 'DNI Mutuante',
					rs.nombre AS Mutuatario,
					rs.num_ruc AS 'RUC Mutuatario',
						u.usuario AS Solicitante,
					CONCAT('http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitud_mutuo_dinero&id=', c.contrato_id, '&adenda_id=', ca.id) AS 'Ver Detalle'
					FROM 
						cont_adendas ca
					INNER JOIN 
						cont_contrato c ON ca.contrato_id = c.contrato_id
					INNER JOIN 
						cont_etapa e ON c.etapa_id = e.etapa_id
					LEFT JOIN 
						cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					LEFT JOIN 
						cont_estado_solicitud es ON es.id = c.estado_solicitud
					LEFT JOIN cont_propietario cp ON c.contrato_id = cp.contrato_id
					LEFT JOIN cont_persona per ON per.id = cp.persona_id
					LEFT JOIN 
						tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
					LEFT JOIN 
						tbl_usuarios u ON ca.user_created_id = u.id
					WHERE 
						ca.procesado = 0 
						AND ca.status = 1 
						AND c.tipo_contrato_id = 15 
						AND c.status = 1 
						$where_empresa
						$where_fecha
					ORDER BY 
						ca.created_at DESC;
				";
				// var_dump($query);
				// die("excel rip");

				// Ejecutar la consulta
				$list_query = $mysqli->query($query);
				$list = [];

				// Procesar resultados
				while ($li = $list_query->fetch_assoc()) {
					$list[] = $li;
				}

				// Columnas a mostrar
				$list_cols = [
					"Código" => "Código",
					"Solicitante" => "Solicitante",
					"F. Solicitud" => "F. Solicitud",
					"Estado Aprobación" => "Estado Aprobación",
					"Mutuante" => "Mutuante",
					"DNI Mutuante" => "DNI Mutuante",
					"Mutuatario" => "Mutuatario",
					"RUC Mutuatario" => "RUC Mutuatario",
					"Ver Detalle" => "Ver Detalle"
				];
				$list_cols_show = $list_cols;
			}

			// if ($tipo_contrato_id == 2) {
			// 	$area_id = $login ? $login['area_id'] : 0;
			// 	$empresa = $empresa;
			// 	$area_solicitante = $area;
			// 	$ruc = $ruc;
			// 	$razon_social = $razon_social;
			// 	$moneda = $moneda;
			// 	if (isset($estado_sol)) {
			// 		$estado_sol = $estado_sol;
			// 	} else {
			// 		$estado_sol = '';
			// 	}
			// 	$where_empresa = "";
			// 	$where_area_solicitante = "";
			// 	$where_ruc = "";
			// 	$where_razon_social = "";
			// 	$where_moneda = "";

			// 	$where_estado_sol = '';
			// 	$where_estado_sol_v2 = '';
			// 	$where_estado_aprobacion = '';
			// 	$where_director_aprobacion = '';

			// 	$where_area = "";
			// 	$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1")->fetch_assoc();
			// 	$menu_consultar = $menu_id_consultar["id"];
			// 	if (array_key_exists($menu_consultar, $usuario_permisos) && (in_array("ver_todo_solicitud", $usuario_permisos[$menu_consultar]) || in_array("see_all", $usuario_permisos[$menu_consultar]))) {
			// 		$where_area = "";
			// 	} else {
			// 		if ($area_id == 33) {
			// 			$where_area = " AND ((c.check_gerencia_proveedor = 0 ) OR (c.aprobacion_gerencia_proveedor = 1) OR (c.check_gerencia_proveedor = 1 and p.area_id = 33 ) )";
			// 		} else {
			// 			$where_area = " AND a.id =" . $area_id;
			// 		}
			// 	}

			// 	if (!empty($empresa)) {
			// 		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
			// 	}

			// 	if (!empty($area_solicitante)) {
			// 		$where_area_solicitante = " AND a.id = '" . $area_solicitante . "' ";
			// 	}

			// 	if (!empty($ruc)) {
			// 		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
			// 	}

			// 	if (!empty($razon_social)) {
			// 		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
			// 	}

			// 	if (!empty($moneda)) {
			// 		$where_moneda = " AND (c.moneda_id = $moneda OR (SELECT ct.moneda_id FROM cont_contraprestacion ct WHERE ct.contrato_id = c.contrato_id LIMIT 1) = $moneda) ";
			// 	}
			// 	$where_fecha = '';
			// 	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
			// 		$where_fecha = " AND c.created_at between '" . $fecha_inicio . "' AND '" . $fecha_fin . "'";
			// 	}

			// 	if (!empty($estado_sol) && $estado_sol != "undefined") {
			// 		if ((int) $estado_sol === 1) {
			// 			$where_estado_sol = " AND ( c.estado_solicitud = 1 OR c.estado_solicitud IS NULL ) ";
			// 		} else {
			// 			$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
			// 		}
			// 	}

			// 	if (!empty($estado_sol_v2)) {
			// 		if ((int) $estado_sol_v2 === 1) {
			// 			$where_estado_sol_v2 = " AND ( c.cancelado_id != 1 || c.cancelado_id IS NULL )";
			// 		} elseif ((int) $estado_sol_v2 === 2) {
			// 			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
			// 		}
			// 	}

			// 	$where_fecha_aprobacion = '';
			// 	if (!empty($fecha_inicio_aprobacion) && !empty($fecha_fin_aprobacion)) {
			// 		$where_fecha = " AND c.fecha_atencion_gerencia_proveedor between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
			// 	} elseif (!empty($fecha_inicio_aprobacion)) {
			// 		$where_fecha = " AND c.fecha_atencion_gerencia_proveedor >= '$fecha_inicio_aprobacion 00:00:00'";
			// 	} elseif (!empty($fecha_fin_aprobacion)) {
			// 		$where_fecha = " AND c.fecha_atencion_gerencia_proveedor <= '$fecha_fin_aprobacion 23:59:59'";
			// 	}

			// 	if (!empty($director_aprobacion_id)) {
			// 		$where_director_aprobacion = " AND ( ( c.director_aprobacion_id = " . $director_aprobacion_id . " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor=0) ) OR c.aprobado_por = " . $director_aprobacion_id . " ) ";
			// 	}

			// 	if ($estado_aprobacion == 1) {
			// 		$where_estado_aprobacion = " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor=1)";
			// 	} elseif ($estado_aprobacion == 2) {
			// 		$where_estado_aprobacion = " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor=0)";
			// 	} elseif ($estado_aprobacion == 3) {
			// 		$where_estado_aprobacion = " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor=0)";
			// 	}

			// 	$query = "SELECT 
			// 				concat(IFNULL(co.sigla,''),IFNULL(c.codigo_correlativo,'')) AS numero, c.contrato_id,
			// 				a.nombre AS area,
			// 				concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			// 				DATE_FORMAT(c.created_at, '%d-%m-%Y') as fecha_solicitud,
			// 				c.detalle_servicio,
			// 				r.nombre AS empresa_suscribe,
			// 				c.razon_social AS proveedor,
			// 				CONCAT(c.periodo_numero, ' ', pd.nombre) AS periodo,
			// 				DATE_FORMAT(c.fecha_inicio, '%d-%m-%Y') as fecha_inicio,

			// 				m.nombre AS tipo_moneda,
			// 				cp.subtotal,
			// 				cp.igv,
			// 				cp.monto,
			// 				t.nombre AS tipo_comprobante,
			// 				cp.plazo_pago,
			// 				cp.forma_pago_detallado,
			// 				(CASE WHEN (c.cancelado_id = 1) THEN 'Cancelado' ELSE es.nombre END) AS estado_solicitud_legal,
			// 				(CASE WHEN (c.cancelado_id = 1) THEN 'Cancelado' ELSE 'En proceso' END) AS estado_solicitud,
			// 				c.fecha_atencion_gerencia_proveedor,
			// 				(CASE
			// 				WHEN (c.check_gerencia_proveedor = 1 AND c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor = 0) THEN 'Pendiente'
			// 				WHEN (c.check_gerencia_proveedor = 1 AND c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor = 1) THEN 'Aprobado'
			// 				WHEN (c.check_gerencia_proveedor = 1 AND c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor = 0) THEN 'Denegado'
			// 				ELSE '' END) AS estado_aprobacion,
			// 				(CASE
			// 				WHEN (c.check_gerencia_proveedor = 1 AND c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor = 0)
			// 				THEN CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) 
			// 				ELSE CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, ''))
			// 				END) AS aprobante,
			// 				c.director_aprobacion_id,
			// 				c.aprobado_por,
			// 				c.cancelado_id,
			// 				es.nombre as nombre_estado_solicitud,
			// 				CONCAT(c.dias_habiles, ' días hábiles') as dias_habiles

			// 			FROM cont_contrato c
			// 				INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			// 				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			// 				INNER JOIN tbl_areas a ON c.area_responsable_id = a.id
			// 				INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
			// 				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			// 				LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
			// 				LEFT JOIN cont_periodo pd ON c.periodo = pd.id
			// 				LEFT JOIN cont_contraprestacion AS cp ON cp.contrato_id = c.contrato_id
			// 				LEFT JOIN tbl_moneda m ON cp.moneda_id = m.id
			// 				LEFT JOIN cont_tipo_comprobante t ON cp.tipo_comprobante_id = t.id
			// 				LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
			// 				LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			// 				LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
			// 				LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
			// 			WHERE c.status = 1 AND c.tipo_contrato_id = 2 AND c.etapa_id != 5 
			// 			" . $where_area . "
			// 			" . $where_empresa . "
			// 			" . $where_area_solicitante . "
			// 			" . $where_ruc . "
			// 			" . $where_razon_social . "
			// 			" . $where_moneda . "
			// 			" . $where_fecha . "
			// 			" . $where_estado_sol . "
			// 			" . $where_director_aprobacion . "
			// 			" . $where_estado_aprobacion . "
			// 			" . $where_fecha_aprobacion . "
			// 			ORDER BY c.created_at DESC";

			// 	$list_query = $mysqli->query($query);
			// 	$list = array();

			// 	while ($li = $list_query->fetch_assoc()) {
			// 		if ($li["cancelado_id"] == 1) {
			// 			$li["nombre_estado_solicitud"] = 'cancelado';
			// 		} elseif ($area_id == '33') {
			// 			$li["nombre_estado_solicitud"] = $li["nombre_estado_solicitud"];
			// 		} else {
			// 			$li["nombre_estado_solicitud"] = 'En proceso';
			// 		}

			// 		if ($area_id == '33') {
			// 			$li["dias_habiles"] = $li["dias_habiles"];
			// 			$seg_proc = new SeguimientoProceso();
			// 			$data_sp['tipo_documento_id'] = 1;
			// 			$data_sp['proceso_id'] = $li['contrato_id'];
			// 			$data_sp['proceso_detalle_id'] = 0;
			// 			$li["etapa_seguimiento"] = $seg_proc->obtener_ultimo_seguimiento($data_sp);
			// 		} else {
			// 			$li["dias_habiles"] = '';
			// 		}
			// 		$list[] = $li;
			// 	}

			// 	$list_cols = array();
			// 	$list_cols["numero"] = "Código";
			// 	$list_cols["area"] = "Área Solicitante";
			// 	$list_cols["solicitante"] = "Solicitante";
			// 	$list_cols["fecha_solicitud"] = "F. Solicitud";
			// 	$list_cols["detalle_servicio"] = "Detalle Servicio";
			// 	$list_cols["empresa_suscribe"] = "Empresa que Suscribe";
			// 	$list_cols["proveedor"] = "Proveedor";
			// 	$list_cols["fecha_inicio"] = "Fecha Inicio";
			// 	$list_cols["periodo"] = "Periodo";
			// 	$list_cols["nombre_estado_solicitud"] = 'Estado solicitud';
			// 	if ($area_id == '33') {
			// 		$list_cols["etapa_seguimiento"] = 'Seguimiento';
			// 	}
			// 	// if($area_id == 33) {
			// 	// 	$list_cols["estado_solicitud_legal"]="Estado Solicitud";
			// 	// } else {
			// 	// 	$list_cols["estado_solicitud"]="Estado Solicitud";
			// 	// }
			// 	$list_cols["dias_habiles"] = "Dias de atención";

			// 	$list_cols["estado_aprobacion"] = "Estado Aprobación";
			// 	$list_cols["aprobante"] = "Aprobante";
			// 	$list_cols["fecha_atencion_gerencia_proveedor"] = "F. Aprobación";

			// 	$list_cols["tipo_moneda"] = "Moneda";
			// 	$list_cols["subtotal"] = "Subtotal";
			// 	$list_cols["igv"] = "IGV";
			// 	$list_cols["monto"] = "Monto Bruto";
			// 	$list_cols["tipo_comprobante"] = "Tipo Comprobante";
			// 	$list_cols["plazo_pago"] = "Plazo de Pago";
			// 	$list_cols["forma_pago_detallado"] = "Forma de Pago";


			// 	$list_cols_show = $list_cols;
			// }
			// if ($tipo_contrato_id == 3) {

			// 	$empresa = $empresa;
			// 	$area_solicitante = $area;
			// 	$ruc = $ruc;
			// 	$razon_social = $razon_social;
			// 	$moneda = $moneda;
			// 	$nombre_tienda = trim($nombre_tienda);
			// 	$id_departamento = $id_departamento;
			// 	$id_provincia = $id_provincia;
			// 	$id_distrito = $id_distrito;
			// 	if (isset($estado_sol) && $estado_sol != "undefined") {
			// 		$estado_sol = $estado_sol;
			// 	} else {
			// 		$estado_sol = '';
			// 	}
			// 	$where_empresa = "";
			// 	$where_area_solicitante = "";
			// 	$where_ruc = "";
			// 	$where_razon_social = "";
			// 	$where_moneda = "";
			// 	$where_ubigeo = '';
			// 	$where_nombre_tienda = '';
			// 	$query_fecha = '';
			// 	$where_estado_sol = '';
			// 	$where_estado_sol_v2 = '';
			// 	$where_estado_cancelado = "";

			// 	if (!empty($empresa)) {
			// 		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
			// 	}

			// 	if (!empty($ruc)) {
			// 		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
			// 	}

			// 	if (!empty($razon_social)) {
			// 		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
			// 	}

			// 	if (!empty($moneda)) {
			// 		$where_moneda = " AND ce.tipo_moneda_id = '" . $moneda . "'";
			// 	}

			// 	if (!empty($id_departamento)) {
			// 		$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
			// 	}

			// 	if (!empty($nombre_tienda)) {
			// 		$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
			// 	}

			// 	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
			// 		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
			// 	} elseif (!empty($fecha_inicio)) {
			// 		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
			// 	} elseif (!empty($fecha_fin)) {
			// 		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
			// 	}

			// 	if (!empty($estado_sol)) {
			// 		if ((int) $estado_sol === 99) {
			// 			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
			// 		} elseif ((int) $estado_sol === 1) {
			// 			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
			// 		} else {
			// 			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
			// 		}
			// 	}

			// 	if (!empty($estado_sol_v2)) {
			// 		if ((int) $estado_sol_v2 === 1) {
			// 			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
			// 		} elseif ((int) $estado_sol_v2 === 2) {
			// 			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
			// 		}
			// 	}

			// 	if ($login["area_id"] == 33) {
			// 		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
			// 	}
			// 	$query = "
			// 		SELECT
			// 			a.id,
			// 			a.codigo,
			// 			CONCAT(IFNULL(co.sigla,''),IFNULL(c.codigo_correlativo,'')) AS numero,
			// 			c.contrato_id,
			// 			c.nombre_tienda,
			// 			CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) AS solicitante,
			// 			p.area_id,
			// 			a.created_at,
			// 			co.sigla AS sigla_correlativo,
			// 			c.codigo_correlativo,
			// 			a.estado_solicitud_id,
			// 			es.nombre AS nombre_estado_solicitud,
			// 			a.procesado,
			// 			a.cancelado_id,
			// 			a.dias_habiles
			// 		FROM 
			// 			cont_adendas a
			// 			INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			// 			INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
			// 			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			// 			INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
			// 			INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND ce.contrato_detalle_id = i.contrato_detalle_id
			// 			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			// 			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			// 			LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
			// 		WHERE 
			// 			c.tipo_contrato_id = 1
			// 			AND a.procesado = 0
			// 			AND a.status = 1
			// 			" . $where_empresa . "
			// 			" . $where_ruc . "
			// 			" . $where_razon_social . "
			// 			" . $where_moneda . "
			// 			" . $where_ubigeo . "
			// 			" . $where_nombre_tienda . "
			// 			" . $query_fecha . "
			// 			" . $where_estado_sol . "
			// 			" . $where_estado_sol_v2 . "
			// 			" . $where_estado_cancelado . "
			// 		GROUP BY a.id
			// 		ORDER BY a.created_at DESC";

			// 	$list_query = $mysqli->query($query);
			// 	$list = array();

			// 	while ($li = $list_query->fetch_assoc()) {
			// 		if ($li["cancelado_id"] == 1) {
			// 			$li["nombre_estado_solicitud"] = 'cancelado';
			// 		} elseif ($area_id == '33') {
			// 			$li["nombre_estado_solicitud"] = $li["nombre_estado_solicitud"];
			// 		} else {
			// 			$li["nombre_estado_solicitud"] = 'En proceso';
			// 		}

			// 		if ($area_id == '33') {
			// 			$li["dias_habiles"] = $li["dias_habiles"];
			// 		} else {
			// 			$li["dias_habiles"] = '';
			// 		}
			// 		$li['numero'] = !empty($li['codigo']) ? $li['numero'] . ' N° ' . $li['codigo'] : '';
			// 		$list[] = $li;
			// 	}

			// 	$list_cols = array();
			// 	$list_cols["numero"] = "Código";
			// 	$list_cols["nombre_tienda"] = "Nombre de la tienda";
			// 	$list_cols["solicitante"] = "Solicitante";
			// 	$list_cols["fecha_solicitud"] = "F. Solicitud";
			// 	$list_cols["nombre_estado_solicitud"] = "Estado";
			// 	$list_cols["dias_habiles"] = "Días de atención";
			// 	$list_cols_show = $list_cols;
			// }
			// if ($tipo_contrato_id == 4) {
			// 	$area_id = $login ? $login['area_id'] : 0;
			// 	$empresa = $empresa;
			// 	$area_solicitante = $area;
			// 	$ruc = $ruc;
			// 	$razon_social = $razon_social;
			// 	$moneda = $moneda;
			// 	if (isset($estado_sol)) {
			// 		$estado_sol = $estado_sol;
			// 	} else {
			// 		$estado_sol = '';
			// 	}
			// 	$where_empresa = "";
			// 	$where_area_solicitante = "";
			// 	$where_ruc = "";
			// 	$where_razon_social = "";
			// 	$where_moneda = "";

			// 	$where_estado_cancelado = '';
			// 	$where_estado_sol = '';
			// 	$where_estado_sol_v2 = '';
			// 	$where_estado_aprobacion = '';
			// 	$where_director_aprobacion = '';

			// 	$where_area = "";
			// 	$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1")->fetch_assoc();
			// 	$menu_consultar = $menu_id_consultar["id"];
			// 	if (array_key_exists($menu_consultar, $usuario_permisos) && (in_array("ver_todo_solicitud", $usuario_permisos[$menu_consultar])  || in_array("see_all", $usuario_permisos[$menu_consultar]))) {
			// 		$where_area = "";
			// 	} else {
			// 		if ($area_id == 33) {
			// 			$where_area = "";
			// 		} else {
			// 			$where_area = " AND p.area_id =" . $area_id;
			// 		}
			// 	}

			// 	if (!empty($empresa)) {
			// 		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
			// 	}

			// 	if (!empty($area_solicitante)) {
			// 		$where_area_solicitante = " AND p.area_id = '" . $area_solicitante . "' ";
			// 	}

			// 	if (!empty($ruc)) {
			// 		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
			// 	}

			// 	if (!empty($razon_social)) {
			// 		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
			// 	}

			// 	$query_fecha = '';
			// 	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
			// 		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
			// 	} elseif (!empty($fecha_inicio)) {
			// 		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
			// 	} elseif (!empty($fecha_fin)) {
			// 		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
			// 	}

			// 	if (!empty($estado_sol) && $estado_sol != "undefined") {
			// 		if ((int) $estado_sol === 99) {
			// 			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
			// 		} elseif ((int) $estado_sol === 1) {
			// 			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
			// 		} else {
			// 			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
			// 		}
			// 	}

			// 	if (!empty($estado_sol_v2)) {
			// 		if ((int) $estado_sol_v2 === 1) {
			// 			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
			// 		} elseif ((int) $estado_sol_v2 === 2) {
			// 			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
			// 		}
			// 	}

			// 	if ($login["area_id"] == 33) {
			// 		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
			// 	}

			// 	$where_fecha_aprobacion = '';
			// 	if (!empty($fecha_inicio_aprobacion) && !empty($fecha_fin_aprobacion)) {
			// 		$where_fecha_aprobacion = " AND a.aprobado_el between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
			// 	} elseif (!empty($fecha_inicio_aprobacion)) {
			// 		$where_fecha_aprobacion = " AND a.aprobado_el >= '$fecha_inicio_aprobacion 00:00:00'";
			// 	} elseif (!empty($fecha_fin_aprobacion)) {
			// 		$where_fecha_aprobacion = " AND a.aprobado_el <= '$fecha_fin_aprobacion 23:59:59'";
			// 	}

			// 	if (!empty($director_aprobacion_id)) {
			// 		$where_director_aprobacion = " AND ( ( a.director_aprobacion_id = $director_aprobacion_id AND (a.requiere_aprobacion_id = 1 AND a.aprobado_estado_id IS NULL) ) OR a.aprobado_por_id = $director_aprobacion_id ) ";
			// 	}

			// 	if ($estado_aprobacion == 1) {
			// 		$where_estado_aprobacion = " AND (a.aprobado_estado_id = 1)";
			// 	} elseif ($estado_aprobacion == 2) {
			// 		$where_estado_aprobacion = " AND (a.aprobado_estado_id = 0)";
			// 	} elseif ($estado_aprobacion == 3) {
			// 		$where_estado_aprobacion = " AND (a.requiere_aprobacion_id = 1)";
			// 	}

			// 	$query = "
			// 		SELECT 
			// 			concat(IFNULL(co.sigla,''),IFNULL(c.codigo_correlativo,'')) AS numero,
			// 			a.id,
			// 			a.codigo,
			// 			c.contrato_id,
			// 			c.nombre_tienda,
			// 			CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			// 			p.area_id,
			// 			a.created_at,
			// 			ar.nombre AS area,
			// 			c.razon_social AS parte,
			// 			r.nombre AS empresa_suscribe,
			// 			c.status,
			// 			c.detalle_servicio,
			// 			CONCAT(c.periodo_numero, ' ', pd.nombre) AS periodo,
			// 			DATE_FORMAT(c.fecha_inicio, '%d-%m-%Y') as fecha_inicio,
			// 			co.sigla AS sigla_correlativo,
			// 			c.codigo_correlativo,
			// 			a.estado_solicitud_id,
			// 			(CASE WHEN (c.cancelado_id = 1) THEN 'Cancelado' ELSE es.nombre END) AS estado_solicitud_legal,
			// 			(CASE WHEN (c.cancelado_id = 1) THEN 'Cancelado' ELSE 'En proceso' END) AS estado_solicitud,
			// 			(CASE
			// 			WHEN (a.requiere_aprobacion_id = 1 AND a.aprobado_estado_id IS NULL) THEN 'Pendiente'
			// 			WHEN (a.aprobado_estado_id = 1) THEN 'Aprobado'
			// 			WHEN (a.aprobado_estado_id = 0) THEN 'Denegado'
			// 			ELSE '' END) AS estado_aprobacion,
			// 			(CASE
			// 			WHEN (a.requiere_aprobacion_id = 1 AND a.aprobado_estado_id IS NULL)
			// 			THEN CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) 
			// 			ELSE CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, ''))
			// 			END) AS aprobante,
			// 			a.procesado,
			// 			a.cancelado_id,
			// 			a.director_aprobacion_id,
			// 			a.aprobado_por_id,
			// 			a.aprobado_el,
			// 			es.nombre as nombre_estado_solicitud,
			// 			CONCAT(a.dias_habiles, ' días hábiles') as dias_habiles
			// 		FROM 
			// 			cont_adendas a
			// 			INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			// 			INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
			// 			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			// 			INNER JOIN tbl_areas ar ON p.area_id = ar.id
			// 			LEFT JOIN cont_periodo pd ON c.periodo = pd.id
			// 			INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
			// 			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			// 			LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
			// 			LEFT JOIN tbl_usuarios ud ON a.director_aprobacion_id = ud.id
			// 			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			// 			LEFT JOIN tbl_usuarios uap ON a.aprobado_por_id = uap.id
			// 			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
			// 		WHERE 
			// 			c.tipo_contrato_id = 2
			// 			AND a.procesado = 0
			// 			AND a.status = 1
			// 			" . $where_empresa . "
			// 			" . $where_area_solicitante . "
			// 			" . $where_ruc . "
			// 			" . $where_razon_social . "
			// 			" . $where_area . "
			// 			" . $query_fecha . "
			// 			" . $where_estado_sol . "
			// 			" . $where_estado_sol_v2 . "
			// 			" . $where_estado_cancelado . "
			// 			" . $where_director_aprobacion . "
			// 			" . $where_estado_aprobacion . "
			// 			" . $where_fecha_aprobacion . "
			// 		ORDER BY c.created_at DESC";

			// 	$list_query = $mysqli->query($query);
			// 	$list = array();

			// 	while ($li = $list_query->fetch_assoc()) {
			// 		if ($li["cancelado_id"] == 1) {
			// 			$li["nombre_estado_solicitud"] = 'cancelado';
			// 		} elseif ($area_id == '33') {
			// 			$li["nombre_estado_solicitud"] = $li["nombre_estado_solicitud"];
			// 		} else {
			// 			$li["nombre_estado_solicitud"] = 'En proceso';
			// 		}

			// 		if ($area_id == '33') {
			// 			$li["dias_habiles"] = $li["dias_habiles"];
			// 			$seg_proc = new SeguimientoProceso();
			// 			$data_sp['tipo_documento_id'] = 2;
			// 			$data_sp['proceso_id'] = $li['id'];
			// 			$data_sp['proceso_detalle_id'] = 0;
			// 			$li["etapa_seguimiento"] = $seg_proc->obtener_ultimo_seguimiento($data_sp);
			// 		} else {
			// 			$li["dias_habiles"] = '';
			// 		}
			// 		$list[] = $li;
			// 	}

			// 	$list_cols = array();
			// 	$list_cols["numero"] = "Código";
			// 	$list_cols["area"] = "Área Solicitante";
			// 	$list_cols["solicitante"] = "Solicitante";
			// 	$list_cols["created_at"] = "F. Solicitud";
			// 	$list_cols["detalle_servicio"] = "Detalle Servicio";
			// 	$list_cols["empresa_suscribe"] = "Empresa que Suscribe";
			// 	$list_cols["parte"] = "Proveedor";
			// 	$list_cols["fecha_inicio"] = "Fecha Inicio";
			// 	$list_cols["periodo"] = "Periodo";
			// 	$list_cols["dias_habiles"] = "Días de atención";
			// 	$list_cols["nombre_estado_solicitud"] = "Estado";
			// 	if ($area_id == '33') {
			// 		$list_cols["etapa_seguimiento"] = 'Seguimiento';
			// 	}
			// 	// if($area_id == 33) {
			// 	// 	$list_cols["estado_solicitud_legal"]="Estado Solicitud";
			// 	// } else {
			// 	// 	$list_cols["estado_solicitud"]="Estado Solicitud";
			// 	// }

			// 	$list_cols["estado_aprobacion"] = "Estado Aprobación";
			// 	$list_cols["aprobante"] = "Aprobante";
			// 	$list_cols["aprobado_el"] = "F. Aprobación";

			// 	$list_cols_show = $list_cols;
			// }
			// if ($tipo_contrato_id == 5) {
			// 	$area_id = $login ? $login['area_id'] : 0;
			// 	$empresa = $empresa;
			// 	$area_solicitante = $area;
			// 	$ruc = $ruc;
			// 	$razon_social = $razon_social;
			// 	$moneda = $moneda;
			// 	if (isset($estado_sol) && $estado_sol != "undefined") {
			// 		$estado_sol = $estado_sol;
			// 	} else {
			// 		$estado_sol = '';
			// 	}
			// 	$where_empresa = "";
			// 	$where_area_solicitante = "";
			// 	$where_ruc = "";
			// 	$where_razon_social = "";
			// 	$where_moneda = "";

			// 	$where_estado_sol = '';
			// 	$where_estado_sol_v2 = '';
			// 	$where_estado_aprobacion = '';
			// 	$where_director_aprobacion = '';

			// 	$where_area = "";
			// 	$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1")->fetch_assoc();
			// 	$menu_consultar = $menu_id_consultar["id"];
			// 	if (array_key_exists($menu_consultar, $usuario_permisos) && (in_array("ver_todo_solicitud", $usuario_permisos[$menu_consultar]) || in_array("see_all", $usuario_permisos[$menu_consultar]))) {
			// 		$where_area = "";
			// 	} else {
			// 		if ($area_id == 33) {
			// 			$where_area = " AND (c.check_gerencia_proveedor = 0 OR (c.aprobacion_gerencia_proveedor = 1)) ";
			// 		} else {
			// 			$where_area = " AND p.area_id =" . $area_id;
			// 		}
			// 	}

			// 	if (!empty($empresa)) {
			// 		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
			// 	}

			// 	if (!empty($area_solicitante)) {
			// 		$where_area_solicitante = " AND a.id = '" . $area_solicitante . "' ";
			// 	}

			// 	if (!empty($ruc)) {
			// 		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
			// 	}

			// 	if (!empty($razon_social)) {
			// 		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
			// 	}

			// 	if (!empty($moneda)) {
			// 		$where_moneda = " AND (c.moneda_id = $moneda OR (SELECT ct.moneda_id FROM cont_contraprestacion ct WHERE ct.contrato_id = c.contrato_id LIMIT 1) = $moneda) ";
			// 	}
			// 	$where_fecha = '';
			// 	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
			// 		$where_fecha = " AND c.created_at between '" . $fecha_inicio . "' AND '" . $fecha_fin . "'";
			// 	}

			// 	if (!empty($estado_sol) && $estado_sol != "undefined") {
			// 		if ((int) $estado_sol === 99) {
			// 			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
			// 		} elseif ((int) $estado_sol === 1) {
			// 			$where_estado_sol = " AND ( c.estado_solicitud = 1 OR c.estado_solicitud IS NULL ) ";
			// 		} else {
			// 			$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
			// 		}
			// 	}

			// 	if (!empty($estado_sol_v2)) {
			// 		if ((int) $estado_sol_v2 === 1) {
			// 			$where_estado_sol_v2 = " AND ( c.cancelado_id != 1 || c.cancelado_id IS NULL )";
			// 		} elseif ((int) $estado_sol_v2 === 2) {
			// 			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
			// 		}
			// 	}

			// 	$where_fecha_aprobacion = '';
			// 	if (!empty($fecha_inicio_aprobacion) && !empty($fecha_fin_aprobacion)) {
			// 		$where_fecha = " AND c.fecha_atencion_gerencia_proveedor between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
			// 	} elseif (!empty($fecha_inicio_aprobacion)) {
			// 		$where_fecha = " AND c.fecha_atencion_gerencia_proveedor >= '$fecha_inicio_aprobacion 00:00:00'";
			// 	} elseif (!empty($fecha_fin_aprobacion)) {
			// 		$where_fecha = " AND c.fecha_atencion_gerencia_proveedor <= '$fecha_fin_aprobacion 23:59:59'";
			// 	}

			// 	if (!empty($director_aprobacion_id)) {
			// 		$where_director_aprobacion = " AND ( ( c.director_aprobacion_id = " . $director_aprobacion_id . " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor=0) ) OR c.aprobado_por = " . $director_aprobacion_id . " ) ";
			// 	}

			// 	if ($estado_aprobacion == 1) {
			// 		$where_estado_aprobacion = " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor=1)";
			// 	} elseif ($estado_aprobacion == 2) {
			// 		$where_estado_aprobacion = " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor=0)";
			// 	} elseif ($estado_aprobacion == 3) {
			// 		$where_estado_aprobacion = " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor=0)";
			// 	}

			// 	$query = "SELECT 
			// 				concat(IFNULL(co.sigla,''),IFNULL(c.codigo_correlativo,'')) AS numero,
			// 				a.nombre AS area,
			// 				concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			// 				DATE_FORMAT(c.created_at, '%d-%m-%Y') as fecha_solicitud,
			// 				c.detalle_servicio,
			// 				r.nombre AS empresa_suscribe,
			// 				c.razon_social AS proveedor,

			// 				(CASE WHEN (c.cancelado_id = 1) THEN 'Cancelado' ELSE es.nombre END) AS estado_solicitud_legal,
			// 				(CASE WHEN (c.cancelado_id = 1) THEN 'Cancelado' ELSE 'En proceso' END) AS estado_solicitud,
			// 				c.fecha_atencion_gerencia_proveedor,
			// 				(CASE
			// 				WHEN (c.check_gerencia_proveedor = 1 AND c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor = 0) THEN 'Pendiente'
			// 				WHEN (c.check_gerencia_proveedor = 1 AND c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor = 1) THEN 'Aprobado'
			// 				WHEN (c.check_gerencia_proveedor = 1 AND c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor = 0) THEN 'Denegado'
			// 				ELSE '' END) AS estado_aprobacion,
			// 				(CASE
			// 				WHEN (c.check_gerencia_proveedor = 1 AND c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor = 0)
			// 				THEN CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) 
			// 				ELSE CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, ''))
			// 				END) AS aprobante,
			// 				c.director_aprobacion_id,
			// 				c.aprobado_por,
			// 				c.cancelado_id,
			// 				es.nombre AS nombre_estado_solicitud,
			// 				CONCAT(c.dias_habiles, ' días hábiles') as dias_habiles
			// 			FROM cont_contrato c
			// 				INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			// 				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			// 				INNER JOIN tbl_areas a ON p.area_id = a.id
			// 				INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
			// 				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			// 				LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
			// 				LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
			// 				LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			// 				LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
			// 				LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
			// 			WHERE c.status = 1 AND c.tipo_contrato_id = 5 AND c.etapa_id != 5 
			// 			" . $where_area . "
			// 			" . $where_empresa . "
			// 			" . $where_area_solicitante . "
			// 			" . $where_ruc . "
			// 			" . $where_razon_social . "
			// 			" . $where_moneda . "
			// 			" . $where_fecha . "
			// 			" . $where_estado_sol . "
			// 			" . $where_estado_sol_v2 . "
			// 			" . $where_director_aprobacion . "
			// 			" . $where_estado_aprobacion . "
			// 			" . $where_fecha_aprobacion . "
			// 			ORDER BY c.created_at DESC";

			// 	$list_query = $mysqli->query($query);
			// 	$list = array();

			// 	while ($li = $list_query->fetch_assoc()) {
			// 		if ($li["cancelado_id"] == 1) {
			// 			$li["nombre_estado_solicitud"] = 'cancelado';
			// 		} elseif ($area_id == '33') {
			// 			$li["nombre_estado_solicitud"] = $li["nombre_estado_solicitud"];
			// 		} else {
			// 			$li["nombre_estado_solicitud"] = 'En proceso';
			// 		}

			// 		if ($area_id == '33') {
			// 			$li["dias_habiles"] = $li["dias_habiles"];
			// 		} else {
			// 			$li["dias_habiles"] = '';
			// 		}
			// 		$list[] = $li;
			// 	}

			// 	$list_cols = array();
			// 	$list_cols["numero"] = "Código";
			// 	$list_cols["area"] = "Área Solicitante";
			// 	$list_cols["solicitante"] = "Solicitante";
			// 	$list_cols["fecha_solicitud"] = "F. Solicitud";
			// 	$list_cols["detalle_servicio"] = "Detalle Servicio";
			// 	$list_cols["empresa_suscribe"] = "Empresa que Suscribe";
			// 	$list_cols["proveedor"] = "Proveedor";
			// 	$list_cols['dias_habiles'] = "Días de atención";
			// 	$list_cols['nombre_estado_solicitud'] = "Estado Solicitud";
			// 	// if($area_id == 33) {
			// 	// 	$list_cols["estado_solicitud_legal"]="Estado Solicitud";
			// 	// } else {
			// 	// 	$list_cols["estado_solicitud"]="Estado Solicitud";
			// 	// }

			// 	$list_cols["estado_aprobacion"] = "Estado Aprobación";
			// 	$list_cols["aprobante"] = "Aprobante";
			// 	$list_cols["fecha_atencion_gerencia_proveedor"] = "F. Aprobación";

			// 	$list_cols_show = $list_cols;
			// }
			// if ($tipo_contrato_id == 6) {

			// 	if (isset($estado_sol) && $estado_sol != "undefined") {
			// 		$estado_sol = $estado_sol;
			// 	} else {
			// 		$estado_sol = '';
			// 	}
			// 	$where_empresa = '';
			// 	$where_estado_sol = '';
			// 	$where_area = "";
			// 	$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1")->fetch_assoc();
			// 	$menu_consultar = $menu_id_consultar["id"];
			// 	if (array_key_exists($menu_consultar, $usuario_permisos) && in_array("ver_todo_solicitud", $usuario_permisos[$menu_consultar])) {
			// 		$where_area = "";
			// 	}
			// 	if (!empty($empresa)) {
			// 		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
			// 	} else {
			// 		if ($area_id == 33) {
			// 			$where_area = " AND (c.check_gerencia_proveedor = 0 OR (c.aprobacion_gerencia_proveedor = 1)) ";
			// 		} else {
			// 			$where_area = " AND p.area_id =" . $area_id;
			// 		}
			// 	}

			// 	$query_fecha = '';
			// 	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
			// 		$query_fecha = " AND c.created_at between '" . $fecha_inicio . "' AND date_add('" . $fecha_fin . "', interval 1 day)";
			// 	}

			// 	if (!empty($estado_sol)) {
			// 		$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
			// 	}

			// 	$query = "SELECT 
			// 				concat( IFNULL(co.sigla,''), IFNULL(c.codigo_correlativo,'')) AS numero,
			// 				c.nombre_agente,
			// 				concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			// 				DATE_FORMAT(c.created_at, '%d-%m-%Y') AS fecha_solicitud,
			// 				r.nombre AS empresa_suscribe,
			// 				es.nombre AS nombre_estado_solicitud,
			// 				c.cancelado_id,
			// 				CONCAT(c.dias_habiles, ' días hábiles') as dias_habiles
			// 			FROM cont_contrato c
			// 				INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			// 				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			// 				INNER JOIN tbl_areas a ON p.area_id = a.id
			// 				INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
			// 				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			// 				LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
			// 			WHERE c.status = 1 AND c.tipo_contrato_id = 6 AND c.etapa_id != 5 
			// 			" . $where_empresa . "
			// 			" . $where_area . "
			// 			" . $query_fecha . "
			// 			" . $where_estado_sol . "
			// 			ORDER BY c.created_at DESC";

			// 	$list_query = $mysqli->query($query);
			// 	$list = array();

			// 	while ($li = $list_query->fetch_assoc()) {
			// 		if ($li["cancelado_id"] == 1) {
			// 			$li["nombre_estado_solicitud"] = 'cancelado';
			// 		} elseif ($area_id == '33') {
			// 			$li["nombre_estado_solicitud"] = $li["nombre_estado_solicitud"];
			// 		} else {
			// 			$li["nombre_estado_solicitud"] = 'En proceso';
			// 		}

			// 		if ($area_id == '33') {
			// 			$li["dias_habiles"] = $li["dias_habiles"];
			// 		} else {
			// 			$li["dias_habiles"] = '';
			// 		}
			// 		$list[] = $li;
			// 	}

			// 	$list_cols = array();
			// 	$list_cols["numero"] = "Código";
			// 	$list_cols["nombre_agente"] = "Agente";
			// 	$list_cols["solicitante"] = "Solicitante";
			// 	$list_cols["fecha_solicitud"] = "F. Solicitud";
			// 	$list_cols["empresa_suscribe"] = "Empresa que suscribe el contrato";
			// 	$list_cols["nombre_estado_solicitud"] = "Estado";
			// 	$list_cols["dias_habiles"] = "Días de atención";
			// 	$list_cols_show = $list_cols;
			// }
			// if ($tipo_contrato_id == 7) {


			// 	$area_id = $login ? $login['area_id'] : 0;

			// 	if (isset($estado_sol) && $estado_sol != "undefined") {
			// 		$estado_sol = $estado_sol;
			// 	} else {
			// 		$estado_sol = '';
			// 	}

			// 	$where_area = "";
			// 	$where_estado_sol = '';

			// 	if (!empty($empresa)) {
			// 		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
			// 	}

			// 	$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1")->fetch_assoc();
			// 	$menu_consultar = $menu_id_consultar["id"];
			// 	if (array_key_exists($menu_consultar, $usuario_permisos) && in_array("ver_todo_solicitud", $usuario_permisos[$menu_consultar])) {
			// 		$where_area = "";
			// 	} else {
			// 		if ($area_id == 33) {
			// 			$where_area = " AND (c.check_gerencia_interno = 0 OR (c.aprobacion_gerencia_interno = 1)) ";
			// 		} else {
			// 			$where_area = " AND per.area_id =" . $area_id;
			// 		}
			// 	}

			// 	if (!empty($estado_sol)) {
			// 		$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
			// 	}

			// 	$query_fecha = '';
			// 	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
			// 		$query_fecha = " AND c.created_at between '" . $fecha_inicio . "' AND '" . $fecha_fin . "'";
			// 	}

			// 	$query = "SELECT
			// 				concat( IFNULL(co.sigla,''), IFNULL(c.codigo_correlativo,'')) AS numero,
			// 				ar.nombre AS area,
			// 				concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS solicitante,
			// 				DATE_FORMAT(c.created_at, '%d-%m-%Y') as fecha_solicitud,
			// 				c.detalle_servicio,
			// 				rs1.nombre AS empresa_at1,
			// 				rs2.nombre AS empresa_at2,
			// 				c.cancelado_id,
			// 				es.nombre as nombre_estado_solicitud,
			// 				CONCAT(c.dias_habiles, ' días hábiles') as dias_habiles
			// 			FROM 
			// 				cont_contrato c
			// 				INNER JOIN cont_periodo p ON c.periodo = p.id
			// 				INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			// 				INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
			// 				INNER JOIN tbl_areas ar ON per.area_id = ar.id
			// 				INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
			// 				INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
			// 				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			// 				LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
			// 			WHERE c.status = 1 AND c.tipo_contrato_id = 7  AND c.etapa_id != 5 
			// 			" . $where_empresa . "
			// 			" . $where_area . "
			// 			" . $query_fecha . "
			// 			" . $where_estado_sol . "
			// 			ORDER BY c.created_at DESC";

			// 	$list_query = $mysqli->query($query);
			// 	$list = array();

			// 	while ($li = $list_query->fetch_assoc()) {
			// 		if ($li["cancelado_id"] == 1) {
			// 			$li["nombre_estado_solicitud"] = 'cancelado';
			// 		} elseif ($area_id == '33') {
			// 			$li["nombre_estado_solicitud"] = $li["nombre_estado_solicitud"];
			// 		} else {
			// 			$li["nombre_estado_solicitud"] = 'En proceso';
			// 		}

			// 		if ($area_id == '33') {
			// 			$li["dias_habiles"] = $li["dias_habiles"];
			// 		} else {
			// 			$li["dias_habiles"] = '';
			// 		}
			// 		$list[] = $li;
			// 	}

			// 	$list_cols = array();
			// 	$list_cols["numero"] = "Código";
			// 	$list_cols["area"] = "Área Solicitante";
			// 	$list_cols["solicitante"] = "Solicitante";
			// 	$list_cols["fecha_solicitud"] = "F. Solicitud";
			// 	$list_cols["detalle_servicio"] = "Detalle Servicio";
			// 	$list_cols["empresa_at1"] = "Empresa Grupo AT 1";
			// 	$list_cols["empresa_at2"] = "Empresa Grupo AT 2";
			// 	$list_cols["nombre_estado_solicitud"] = "Estado";

			// 	$list_cols["dias_habiles"] = "Días de atención";
			// 	$list_cols_show = $list_cols;
			// }
			// if ($tipo_contrato_id == 8) {

			// 	$empresa = $empresa;
			// 	if (isset($estado_sol) && $estado_sol != "undefined") {
			// 		$estado_sol = $estado_sol;
			// 	} else {
			// 		$estado_sol = '';
			// 	}
			// 	$area_solicitante = $area;

			// 	$where_empresa = "";
			// 	$where_area_solicitante = "";
			// 	$where_estado_sol = '';
			// 	$where_estado_sol_v2 = '';
			// 	$where_estado_cancelado = "";

			// 	$query_fecha = '';
			// 	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
			// 		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
			// 	} elseif (!empty($fecha_inicio)) {
			// 		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
			// 	} elseif (!empty($fecha_fin)) {
			// 		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
			// 	}

			// 	if (!empty($empresa)) {
			// 		$where_empresa = " AND ( c.empresa_suscribe_id = '" . $empresa . "'  || c.empresa_grupo_at_2 = '" . $empresa . "' ) ";
			// 	}

			// 	if (!empty($area_solicitante)) {
			// 		$where_area_solicitante = " AND p.area_id = '" . $area_solicitante . "' ";
			// 	}

			// 	if (!empty($estado_sol)) {
			// 		if ((int) $estado_sol === 99) {
			// 			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
			// 		} elseif ((int) $estado_sol === 1) {
			// 			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
			// 		} else {
			// 			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
			// 		}
			// 	}

			// 	if (!empty($estado_sol_v2)) {
			// 		if ((int) $estado_sol_v2 === 1) {
			// 			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
			// 		} elseif ((int) $estado_sol_v2 === 2) {
			// 			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
			// 		}
			// 	}

			// 	if ($login["area_id"] == 33) {
			// 		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
			// 	}

			// 	$query = "
			// 		SELECT
			// 			a.id,
			// 			a.codigo,
			// 			c.contrato_id,
			// 			c.nombre_tienda,
			// 			CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			// 			p.area_id,
			// 			a.created_at,
			// 			ar.nombre AS area,
			// 			c.razon_social AS parte,
			// 			rs1.nombre AS empresa_at_1,
			// 			rs2.nombre AS empresa_at_2,
			// 			c.status,
			// 			c.detalle_servicio,
			// 			co.sigla AS sigla_correlativo,
			// 			c.codigo_correlativo,
			// 			a.estado_solicitud_id,
			// 			es.nombre AS nombre_estado_solicitud,
			// 			a.procesado,
			// 			a.cancelado_id,
			// 			a.dias_habiles
			// 		FROM cont_adendas a
			// 		INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			// 		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
			// 		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			// 		INNER JOIN tbl_areas ar ON p.area_id = ar.id
			// 		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id AND rs1.status = 1
			// 		INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id AND rs2.status = 1
			// 		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			// 		LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
			// 		WHERE c.tipo_contrato_id = 7
			// 		AND a.procesado = 0
			// 		AND a.status = 1
			// 		" . $where_empresa . "
			// 		" . $where_area_solicitante . "
			// 		" . $query_fecha . "
			// 		" . $where_estado_sol . "
			// 		" . $where_estado_sol_v2 . "
			// 		" . $where_estado_cancelado . "
			// 		ORDER BY c.created_at DESC";

			// 	$list_query = $mysqli->query($query);
			// 	$list = array();

			// 	while ($li = $list_query->fetch_assoc()) {
			// 		if ($li["cancelado_id"] == 1) {
			// 			$li["nombre_estado_solicitud"] = 'cancelado';
			// 		} elseif ($area_id == '33') {
			// 			$li["nombre_estado_solicitud"] = $li["nombre_estado_solicitud"];
			// 		} else {
			// 			$li["nombre_estado_solicitud"] = 'En proceso';
			// 		}

			// 		if ($area_id == '33') {
			// 			$li["dias_habiles"] = $li["dias_habiles"];
			// 		} else {
			// 			$li["dias_habiles"] = '';
			// 		}

			// 		$list[] = $li;
			// 	}
			// 	$list_cols = array();
			// 	$list_cols["numero"] = "Código";
			// 	$list_cols["area"] = "Área Solicitante";
			// 	$list_cols["solicitante"] = "Solicitante";
			// 	$list_cols["detalle_servicio"] = "Detalle servicio";
			// 	$list_cols["empresa_at_1"] = "Empresa Grupo AT 1";
			// 	$list_cols["empresa_at_2"] = "Empresa Grupo AT 2";
			// 	$list_cols["created_at"] = "F. Solicitud";
			// 	$list_cols["nombre_estado_solicitud"] = "Estado";
			// 	$list_cols["dias_habiles"] = "Días de atención";
			// 	$list_cols_show = $list_cols;
			// }
			// if ($tipo_contrato_id == 9) {
			// 	$area_id = $login ? $login['area_id'] : 0;
			// 	$empresa = $empresa;
			// 	$area_solicitante = $area;
			// 	$ruc = $ruc;
			// 	$razon_social = $razon_social;
			// 	$moneda = $moneda;
			// 	if (isset($estado_sol)) {
			// 		$estado_sol = $estado_sol;
			// 	} else {
			// 		$estado_sol = '';
			// 	}
			// 	$where_empresa = "";
			// 	$where_area_solicitante = "";
			// 	$where_ruc = "";
			// 	$where_razon_social = "";
			// 	$where_moneda = "";

			// 	$where_estado_cancelado = '';
			// 	$where_estado_sol = '';
			// 	$where_estado_sol_v2 = '';
			// 	$where_estado_aprobacion = '';
			// 	$where_director_aprobacion = '';

			// 	$where_area = "";
			// 	$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1")->fetch_assoc();
			// 	$menu_consultar = $menu_id_consultar["id"];
			// 	if (array_key_exists($menu_consultar, $usuario_permisos) && (in_array("ver_todo_solicitud", $usuario_permisos[$menu_consultar])  || in_array("see_all", $usuario_permisos[$menu_consultar]))) {
			// 		$where_area = "";
			// 	} else {
			// 		if ($area_id == 33) {
			// 			$where_area = "";
			// 		} else {
			// 			$where_area = " AND p.area_id =" . $area_id;
			// 		}
			// 	}

			// 	if (!empty($empresa)) {
			// 		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
			// 	}

			// 	if (!empty($area_solicitante)) {
			// 		$where_area_solicitante = " AND p.area_id = '" . $area_solicitante . "' ";
			// 	}

			// 	if (!empty($ruc)) {
			// 		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
			// 	}

			// 	if (!empty($razon_social)) {
			// 		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
			// 	}

			// 	$query_fecha = '';
			// 	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
			// 		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
			// 	} elseif (!empty($fecha_inicio)) {
			// 		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
			// 	} elseif (!empty($fecha_fin)) {
			// 		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
			// 	}

			// 	if (!empty($estado_sol) && $estado_sol != "undefined") {
			// 		if ((int) $estado_sol === 99) {
			// 			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
			// 		} elseif ((int) $estado_sol === 1) {
			// 			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
			// 		} else {
			// 			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
			// 		}
			// 	}

			// 	if (!empty($estado_sol_v2)) {
			// 		if ((int) $estado_sol_v2 === 1) {
			// 			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
			// 		} elseif ((int) $estado_sol_v2 === 2) {
			// 			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
			// 		}
			// 	}

			// 	if ($login["area_id"] == 33) {
			// 		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
			// 	}

			// 	$where_fecha_aprobacion = '';
			// 	if (!empty($fecha_inicio_aprobacion) && !empty($fecha_fin_aprobacion)) {
			// 		$where_fecha_aprobacion = " AND a.aprobado_el between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
			// 	} elseif (!empty($fecha_inicio_aprobacion)) {
			// 		$where_fecha_aprobacion = " AND a.aprobado_el >= '$fecha_inicio_aprobacion 00:00:00'";
			// 	} elseif (!empty($fecha_fin_aprobacion)) {
			// 		$where_fecha_aprobacion = " AND a.aprobado_el <= '$fecha_fin_aprobacion 23:59:59'";
			// 	}

			// 	if (!empty($director_aprobacion_id)) {
			// 		$where_director_aprobacion = " AND ( ( a.director_aprobacion_id = $director_aprobacion_id AND (a.requiere_aprobacion_id = 1 AND a.aprobado_estado_id IS NULL) ) OR a.aprobado_por_id = $director_aprobacion_id ) ";
			// 	}

			// 	if ($estado_aprobacion == 1) {
			// 		$where_estado_aprobacion = " AND (a.aprobado_estado_id = 1)";
			// 	} elseif ($estado_aprobacion == 2) {
			// 		$where_estado_aprobacion = " AND (a.aprobado_estado_id = 0)";
			// 	} elseif ($estado_aprobacion == 3) {
			// 		$where_estado_aprobacion = " AND (a.requiere_aprobacion_id = 1)";
			// 	}

			// 	$query = "
			// 		SELECT 
			// 			concat(IFNULL(co.sigla,''),IFNULL(c.codigo_correlativo,'')) AS numero,
			// 			a.id,
			// 			a.codigo,
			// 			c.contrato_id,
			// 			c.nombre_tienda,
			// 			CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			// 			p.area_id,
			// 			a.created_at,
			// 			ar.nombre AS area,
			// 			c.razon_social AS parte,
			// 			r.nombre AS empresa_suscribe,
			// 			c.status,
			// 			c.detalle_servicio,
			// 			co.sigla AS sigla_correlativo,
			// 			c.codigo_correlativo,
			// 			a.estado_solicitud_id,
			// 			(CASE WHEN (c.cancelado_id = 1) THEN 'Cancelado' ELSE es.nombre END) AS estado_solicitud_legal,
			// 			(CASE WHEN (c.cancelado_id = 1) THEN 'Cancelado' ELSE 'En proceso' END) AS estado_solicitud,
			// 			(CASE
			// 			WHEN (a.requiere_aprobacion_id = 1 AND a.aprobado_estado_id IS NULL) THEN 'Pendiente'
			// 			WHEN (a.aprobado_estado_id = 1) THEN 'Aprobado'
			// 			WHEN (a.aprobado_estado_id = 0) THEN 'Denegado'
			// 			ELSE '' END) AS estado_aprobacion,
			// 			(CASE
			// 			WHEN (a.requiere_aprobacion_id = 1 AND a.aprobado_estado_id IS NULL)
			// 			THEN CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) 
			// 			ELSE CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, ''))
			// 			END) AS aprobante,
			// 			a.procesado,
			// 			a.cancelado_id,
			// 			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			// 			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			// 			a.director_aprobacion_id,
			// 			a.aprobado_por_id,
			// 			a.aprobado_el,
			// 			es.nombre as nombre_estado_solicitud
			// 		FROM 
			// 			cont_adendas a
			// 			INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			// 			INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
			// 			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			// 			INNER JOIN tbl_areas ar ON p.area_id = ar.id
			// 			INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
			// 			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			// 			LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
			// 			LEFT JOIN tbl_usuarios ud ON a.director_aprobacion_id = ud.id
			// 			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			// 			LEFT JOIN tbl_usuarios uap ON a.aprobado_por_id = uap.id
			// 			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
			// 		WHERE c.tipo_contrato_id = 5
			// 		AND a.procesado = 0
			// 		AND a.status = 1
			// 		" . $where_empresa . "
			// 		" . $where_area_solicitante . "
			// 		" . $where_ruc . "
			// 		" . $where_razon_social . "
			// 		" . $query_fecha . "
			// 		" . $where_estado_sol . "
			// 		" . $where_estado_sol_v2 . "
			// 		" . $where_estado_cancelado . "
			// 		" . $where_director_aprobacion . "
			// 		" . $where_estado_aprobacion . "
			// 		" . $where_fecha_aprobacion . "
			// 		ORDER BY c.created_at DESC
			// 		";

			// 	$list_query = $mysqli->query($query);
			// 	$list = array();

			// 	while ($li = $list_query->fetch_assoc()) {
			// 		if ($li["cancelado_id"] == 1) {
			// 			$li["nombre_estado_solicitud"] = 'cancelado';
			// 		} elseif ($area_id == '33') {
			// 			$li["nombre_estado_solicitud"] = $li["nombre_estado_solicitud"];
			// 		} else {
			// 			$li["nombre_estado_solicitud"] = 'En proceso';
			// 		}
			// 		$list[] = $li;
			// 	}

			// 	$list_cols = array();
			// 	$list_cols["numero"] = "Código";
			// 	$list_cols["area"] = "Área Solicitante";
			// 	$list_cols["solicitante"] = "Solicitante";
			// 	$list_cols["created_at"] = "F. Solicitud";
			// 	$list_cols["empresa_suscribe"] = "Empresa que Suscribe";
			// 	$list_cols["parte"] = "Proveedor";
			// 	$list_cols["nombre_estado_solicitud"] = 'Estado Solicitud';
			// 	// if($area_id == 33) {
			// 	// 	$list_cols["estado_solicitud_legal"]="Estado Solicitud";
			// 	// } else {
			// 	// 	$list_cols["estado_solicitud"]="Estado Solicitud";
			// 	// }

			// 	$list_cols["estado_aprobacion"] = "Estado Aprobación";
			// 	$list_cols["aprobante"] = "Aprobante";
			// 	$list_cols["aprobado_el"] = "F. Aprobación";

			// 	$list_cols_show = $list_cols;
			// }
			// if ($tipo_contrato_id == 10) {
			// 	// $menu_consultar = $_POST['menu_consultar'];
			// 	$empresa = $empresa;
			// 	$where_empresa = "";
			// 	$where_estado_sol_v2 = '';
			// 	$where_estado_cancelado = "";
			// 	if (isset($estado_sol) && $estado_sol != "undefined") {
			// 		$estado_sol = $estado_sol;
			// 	} else {
			// 		$estado_sol = '';
			// 	}
			// 	if (!empty($empresa)) {
			// 		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
			// 	}

			// 	$query_fecha = '';
			// 	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
			// 		$query_fecha = " AND c.created_at between '" . $fecha_inicio . "' AND date_add('" . $fecha_fin . "', interval 1 day)";
			// 	}


			// 	if (!empty($estado_sol)) {
			// 		if ((int) $estado_sol === 99) {
			// 			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
			// 		} elseif ((int) $estado_sol === 1) {
			// 			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
			// 		} else {
			// 			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
			// 		}
			// 	}

			// 	if (!empty($estado_sol_v2)) {
			// 		if ((int) $estado_sol_v2 === 1) {
			// 			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
			// 		} elseif ((int) $estado_sol_v2 === 2) {
			// 			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
			// 		}
			// 	}

			// 	if ($login["area_id"] == 33) {
			// 		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
			// 	}
			// 	$query = "
			// 		SELECT 
			// 			a.id,
			// 			CONCAT(IFNULL(co.sigla,''),IFNULL(c.codigo_correlativo,'')) AS numero,
			// 			c.contrato_id,
			// 			c.nombre_tienda,
			// 			CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS solicitante, 
			// 			p.area_id,
			// 			a.created_at,
			// 			ar.nombre AS area,
			// 			c.razon_social AS parte,
			// 			r.nombre AS empresa_suscribe,
			// 			c.status,
			// 			c.detalle_servicio,
			// 			co.sigla AS sigla_correlativo,
			// 			c.codigo_correlativo,
			// 			a.estado_solicitud_id,
			// 			es.nombre AS nombre_estado_solicitud,
			// 			a.procesado,
			// 			a.cancelado_id,
			// 			a.dias_habiles
			// 		FROM cont_adendas a
			// 		INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			// 		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
			// 		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			// 		INNER JOIN tbl_areas ar ON p.area_id = ar.id
			// 		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
			// 		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			// 		LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
			// 		WHERE c.tipo_contrato_id = 6
			// 		AND a.procesado = 0
			// 		AND a.status = 1
			// 		" . $where_empresa . "
			// 		" . $query_fecha . "
			// 		" . $where_estado_sol_v2 . "
			// 		" . $where_estado_cancelado . "
			// 		ORDER BY c.created_at DESC";

			// 	$list_query = $mysqli->query($query);
			// 	$list = array();

			// 	while ($li = $list_query->fetch_assoc()) {
			// 		if ($li["cancelado_id"] == 1) {
			// 			$li["nombre_estado_solicitud"] = 'cancelado';
			// 		} elseif ($area_id == '33') {
			// 			$li["nombre_estado_solicitud"] = $li["nombre_estado_solicitud"];
			// 		} else {
			// 			$li["nombre_estado_solicitud"] = 'En proceso';
			// 		}

			// 		if ($area_id == '33') {
			// 			$li["dias_habiles"] = $li["dias_habiles"];
			// 		} else {
			// 			$li["dias_habiles"] = '';
			// 		}
			// 		$list[] = $li;
			// 	}

			// 	$list_cols = array();
			// 	$list_cols["numero"] = "Código";
			// 	$list_cols["area"] = "Area solicitante";
			// 	$list_cols["solicitante"] = "Solicitante";
			// 	$list_cols["detalle_servicio"] = "Detalle servicio";
			// 	$list_cols["empresa_suscribe"] = "F. Empresa que suscribe el contrato";
			// 	$list_cols["parte"] = "Proveedor";
			// 	$list_cols["created_at"] = "F. Solicitud";
			// 	$list_cols["nombre_estado_solicitud"] = "Estado";
			// 	$list_cols["dias_habiles"] = "Días de atención";
			// 	$list_cols_show = $list_cols;
			// }
			// if ($tipo_contrato_id == 11) {
			// 	// $menu_consultar = $_POST['menu_consultar'];
			// 	$query_fecha = '';
			// 	$query_estado = '';
			// 	$query_tipo_contrato = '';
			// 	$query_area = '';
			// 	$where_estado_sol_v2 = '';
			// 	$where_estado_cancelado = "";

			// 	if (isset($estado_sol) && $estado_sol != "undefined") {
			// 		$estado_sol = $estado_sol;
			// 	} else {
			// 		$estado_sol = '';
			// 	}


			// 	$query_fecha = '';
			// 	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
			// 		$query_fecha = " AND r.created_at between '" . $fecha_inicio . "' AND '" . $fecha_fin . "'";
			// 	}
			// 	if (!empty($rc_tipo_contrato_id)) {
			// 		$query_tipo_contrato = " AND r.tipo_contrato_id = '" . $rc_tipo_contrato_id . "'";
			// 	}
			// 	if (!empty($estado_sol)) {
			// 		if ((int) $estado_sol === 99) {
			// 			$where_estado_sol_v2 = " AND r.cancelado_id = 1 ";
			// 		} elseif ((int) $estado_sol === 1) {
			// 			$query_estado = " AND ( r.estado_solicitud_legal = 1 OR r.estado_solicitud_legal IS NULL )";
			// 		} else {
			// 			$query_estado = " AND r.estado_solicitud_legal = '" . $estado_sol . "' ";
			// 		}
			// 	}

			// 	if (!empty($estado_sol_v2)) {
			// 		if ((int) $estado_sol_v2 === 1) {
			// 			$where_estado_sol_v2 = " AND ( r.cancelado_id != 1 || r.cancelado_id IS NULL )";
			// 		} elseif ((int) $estado_sol_v2 === 2) {
			// 			$where_estado_sol_v2 = " AND r.cancelado_id = 1 ";
			// 		}
			// 	}

			// 	if ($login["area_id"] == 33) {
			// 		$where_estado_cancelado = " AND ( r.cancelado_id != 1 OR r.cancelado_id IS NULL )";
			// 	}

			// 	$consult_query = "
			// 			SELECT 
			// 				r.id,
			// 				c.tipo_contrato_id,
			// 				r.contrato_id,
			// 				r.motivo,
			// 				r.fecha_solicitud,
			// 				DATE_FORMAT(r.fecha_resolucion,'%d-%m-%Y') as fecha_resolucion,
			// 				CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS usuario_solicitud,
			// 				tpa.area_id,
			// 				r.status,
			// 				DATE_FORMAT(r.created_at,'%d-%m-%Y %H:%i:%s') as created_at,
			// 				co.sigla,
			// 				c.codigo_correlativo,
			// 				tc.nombre AS nombre_tipo_contrato,
			// 				r.estado_solicitud_id,
			// 				r.estado_solicitud_legal,
			// 				es.nombre AS nombre_estado_solicitud_legal,
			// 				r.fecha_cambio_estado_solicitud,
			// 				r.dias_habiles,
			// 				(CASE
			// 					WHEN r.estado_solicitud_id = 1 THEN 'En Proceso'
			// 					WHEN r.estado_solicitud_id = 2 THEN 'Procesado'
			// 					ELSE ''
			// 				END) as estado_solicitud, 
			// 				c.nombre_tienda,
			// 				c.razon_social AS parte,
			// 				c.nombre_agente,
			// 				r.cancelado_id,

			// 				CONCAT(IFNULL(tpag.nombre, ''),' ',IFNULL(tpag.apellido_paterno, ''),	' ',	IFNULL(tpag.apellido_materno, '')) AS aprobante,
			// 				(CASE
			// 					WHEN r.estado_aprobacion_gerencia = 0 THEN 'Pendiente'
			// 					WHEN r.estado_aprobacion_gerencia = 1 THEN 'Aprobado'
			// 					WHEN r.estado_aprobacion_gerencia = 2 THEN 'Rechazado'
			// 					ELSE ''
			// 				END) as estado_aprobacion, 
			// 				r.fecha_aprobacion_gerencia

			// 			FROM cont_resolucion_contrato AS r
			// 			INNER JOIN cont_contrato c ON c.contrato_id = r.contrato_id
			// 			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			// 			INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
			// 			LEFT JOIN cont_estado_solicitud es ON es.id = r.estado_solicitud_legal
			// 			INNER JOIN tbl_usuarios tu ON r.user_created_id = tu.id
			// 			INNER JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id

			// 			LEFT JOIN tbl_usuarios tua ON r.aprobacion_gerencia_id = tua.id
			// 			LEFT JOIN tbl_personal_apt tpag ON tua.personal_id = tpag.id

			// 			WHERE r.status = 1 AND r.estado_solicitud_id = 1
			// 			" . $query_fecha . "
			// 			" . $query_tipo_contrato . "
			// 			" . $query_estado . "
			// 			" . $where_estado_sol_v2 . "
			// 			" . $where_estado_cancelado . "
			// 			" . $query_area . "
			// 			ORDER BY r.created_at DESC
			// 			";
			// 	$list_query = $mysqli->query($consult_query);
			// 	$list = array();

			// 	while ($li = $list_query->fetch_assoc()) {


			// 		$li['correlativo'] = $li["sigla"] . $li["codigo_correlativo"];
			// 		$nombre = '';
			// 		switch ($li["tipo_contrato_id"]) {
			// 			case '1':
			// 				$nombre = $li['nombre_tienda'];
			// 				break;
			// 			case '2':
			// 				$nombre = $li['parte'];
			// 				break;
			// 			case '5':
			// 				$nombre = $li['parte'];
			// 				break;
			// 			case '6':
			// 				$nombre = $li['nombre_agente'];
			// 				break;
			// 			default:
			// 				$nombre = '';
			// 				break;
			// 		}
			// 		$li['nombre'] = $nombre;
			// 		$li['fecha_atencion'] = (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : '';
			// 		$li['dias_atencion'] = (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : '';
			// 		if ($area_id == '33') {
			// 			$li["dias_habiles"] = $li["dias_habiles"];
			// 			$seg_proc = new SeguimientoProceso();
			// 			$data_sp['tipo_documento_id'] = 3;
			// 			$data_sp['proceso_id'] = $li['id'];
			// 			$data_sp['proceso_detalle_id'] = 0;
			// 			$li["etapa_seguimiento"] = $seg_proc->obtener_ultimo_seguimiento($data_sp);
			// 		}
			// 		$list[] = $li;
			// 	}

			// 	$list_cols = array();
			// 	$list_cols["nombre_tipo_contrato"] = "Tipo Contrato";
			// 	$list_cols["correlativo"] = "Código";
			// 	$list_cols["nombre"] = "Nombre";
			// 	$list_cols["usuario_solicitud"] = "Solicitante";
			// 	$list_cols["motivo"] = "Motivo";
			// 	$list_cols["fecha_resolucion"] = "F. Resolución";
			// 	$list_cols["created_at"] = "F. Solicitud";
			// 	$list_cols["estado_aprobacion"] = "Estado Aprobante";
			// 	$list_cols["aprobante"] = "Aprobante";
			// 	$list_cols["fecha_aprobacion_gerencia"] = "F. Aprobación";
			// 	$list_cols["nombre_estado_solicitud_legal"] = "Estado";

			// 	if ($login["area_id"] == 33) {
			// 		$list_cols["fecha_atencion"] = "Fecha Atención";
			// 		$list_cols["dias_atencion"] = "Dias de Atención";
			// 		$list_cols["etapa_seguimiento"] = 'Seguimiento';
			// 	}
			// 	$list_cols_show = $list_cols;
			// }
		}
		//YONATHAN MAMANI C.: FIN REPORTE DE SOLICITUD DE CONTRATO

		elseif ($export == "reporte_nif16_terminacion_renovacion") {

			$where_tipo = "";
			$where_empresa = "";
			$where_centro_costo = "";
			$where_nombre_tienda = "";
			$where_locales = "";


			if (!empty($tipo) && $tipo != "0") {
				$where_tipo = " AND tipo = '" . $tipo . "'";
			}
			if (!empty($empresa) && $empresa != "0") {
				$where_empresa = " AND empresa_suscribe_id IN (" . $empresa . ")";
			}
			if (!empty($centro_costo) && $centro_costo != "0") {
				$where_centro_costo = "  AND cc_id LIKE '%" . $centro_costo . "%'";
			}
			if (!empty($nomb_tienda) && $nomb_tienda != "0") {
				$where_nombre_tienda = "  AND nombre_tienda LIKE '%" . $nomb_tienda . "%'";
			}
			if ($login["usuario_locales"]) {
				$where_locales = " AND local_id IN (" . implode(",", $login["usuario_locales"]) . ")";
			}


			$query_nif16 = "
				SELECT  contrato_id, nombre_tienda, tipo, fecha_gerencia, fecha_vencimiento, empresa_suscribe_id, cc_id, local_id, fecha_orden
				FROM (

					SELECT c.contrato_id, c.nombre_tienda, CONCAT('Terminación') AS tipo, DATE_FORMAT(rc.fecha_resolucion, '%d-%m-%Y') AS fecha_gerencia,  
					DATE_FORMAT(rc.fecha_resolucion, '%d-%m-%Y') AS fecha_vencimiento,c.empresa_suscribe_id ,c.cc_id, l.id as local_id,
					rc.fecha_resolucion AS fecha_orden
					FROM cont_resolucion_contrato AS rc 
					INNER JOIN cont_contrato AS c ON c.contrato_id = rc.contrato_id
					INNER JOIN tbl_locales as l ON l.contrato_id = c.contrato_id
					WHERE rc.tipo_contrato_id = 1 
					AND rc.estado_solicitud_id = 2
					GROUP BY rc.id
					
					UNION ALL
					
					SELECT c.contrato_id, c.nombre_tienda, 
					CONCAT('Renovación') AS tipo,
					DATE_FORMAT(DATE_ADD(CONVERT(ad.valor_original,DATE), INTERVAL 1 DAY),'%d-%m-%Y') AS fecha_gerencia,
					DATE_FORMAT(ad.valor_date,'%d-%m-%Y') AS fecha_vencimiento,c.empresa_suscribe_id ,c.cc_id, l.id as local_id,
					DATE_ADD(CONVERT(ad.valor_original,DATE), INTERVAL 1 DAY) AS fecha_orden
					FROM cont_adendas_detalle ad
					INNER JOIN cont_adendas AS a ON a.id = ad.adenda_id
					INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
					INNER JOIN tbl_locales as l ON l.contrato_id = c.contrato_id
					WHERE  a.status = 1 AND  a.procesado = 1
					AND c.tipo_contrato_id = 1
					AND ad.nombre_tabla = 'cont_condicion_economica' AND ad.nombre_campo = 'fecha_fin'
					GROUP BY ad.id

				) AS reporte 
				WHERE 1 = 1
				" . $where_tipo . "
				" . $where_empresa . "
				" . $where_centro_costo . "
				" . $where_nombre_tienda . "
				" . $where_locales . "
				ORDER BY nombre_tienda ASC, fecha_orden ASC
				";

			$list_query = $mysqli->query($query_nif16);
			$list_proc_registros = array();
			while ($sp = $list_query->fetch_assoc()) {
				array_push($list_proc_registros, array(
					'contrato_id' => $sp['nombre_tienda'],
					'tipo' => $sp['tipo'],
					'incluido_contrato' => 'SI',
					'ejecucion_arrendatario' => 'SI',
					'gerencia_ejecutara' => 'SI',
					'fecha_gerencia' => $sp['fecha_gerencia'],
					'nueva_fecha' => $sp['fecha_vencimiento'],
					'importe_penalizacion' => '',
					'periodo_pago' => '',
				));
			}
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols["contrato_id"] = "ID Contrato";
			$list_cols["tipo"] = "¿Terminación o Renovación?";
			$list_cols["incluido_contrato"] = "¿Se ha incluido esta opción en el contrato?";
			$list_cols["ejecucion_arrendatario"] = "¿La ejecución de la opción depende únicamente del arrendatario?";
			$list_cols["gerencia_ejecutara"] = "¿La gerencia ejecutará la opción?";
			$list_cols["fecha_gerencia"] = "Fecha en que la Gerencia decide ejecutar la opción";
			$list_cols["nueva_fecha"] = "Nueva fecha de vencimiento del contrato";
			$list_cols["importe_penalizacion"] = "Importe de la penalización por ejecutar la opción";
			$list_cols["periodo_pago"] = "Periodo de pago de penalización";
			$list_cols_show = $list_cols;
		} elseif ($export == "reporte_nif16_bdt") {

			$where_empresa = "";
			$where_centro_costo = "";
			$where_nombre_tienda = "";
			$where_ubigeo = "";
			$where_direccion = "";
			$where_fech_suscripcion = "";
			$where_fecha_inicio = "";
			$where_fecha_fin = "";
			$where_locales = "";


			if (!empty($empresa) && $empresa != "0") {
				$where_empresa = " AND c.empresa_suscribe_id IN (" . $empresa . ")";
			}
			if (!empty($centro_costo) && $centro_costo != "0") {
				$where_centro_costo = "  AND c.cc_id LIKE '%" . $centro_costo . "%'";
			}
			if (!empty($nomb_tienda) && $nomb_tienda != "0") {
				$where_nombre_tienda = "  AND c.nombre_tienda LIKE '%" . $nomb_tienda . "%'";
			}
			if (!empty($direccion) && $direccion != "0") {
				$where_direccion = "  AND i.ubicacion LIKE '%" . $direccion . "%'";
			}
			if (!empty($departamento) && $departamento != "0") {
				$where_ubigeo .= "  AND dpo.cod_depa = '" . $departamento . "'";
			}
			if (!empty($provincia) && $provincia != "0") {
				$where_ubigeo .= "  AND prv.cod_prov = '" . $provincia . "'";
			}
			if (!empty($distrito) && $distrito != "0") {
				$where_ubigeo .= "  AND dto.cod_dist = '" . $distrito . "'";
			}
			if (!empty($fec_suscrip)) {
				$where_fech_suscripcion .= "  AND ce.fecha_suscripcion = '" . $fec_suscrip . "'";
			}
			if (!empty($fec_inicio)) {
				$where_fecha_inicio .= "  AND ce.fecha_inicio = '" . $fec_inicio . "'";
			}
			if (!empty($fec_fin)) {
				$where_fecha_fin .= "  AND ce.fecha_fin = '" . $fec_fin . "'";
			}
			if ($login["usuario_locales"]) {
				$where_locales = " AND l.id IN (" . implode(",", $login["usuario_locales"]) . ")";
			}

			$query_nif16 = "
				SELECT 
				c.contrato_id,
				c.nombre_tienda,
				GROUP_CONCAT(DISTINCT per.nombre SEPARATOR ' y ') AS arrendador,
				rz.nombre AS arrendatario,
				CONCAT('Terceros') AS tipo_relacion,
				CONCAT('Inmuebles: Agencias y oficinas') AS tipo_activo,
				CONCAT('No') AS bajo_valor,
				IF(c.cc_id IS NULL,'',c.cc_id) AS centro_logistico,
				m.sigla AS moneda,
				ce.fecha_inicio AS fecha_inicio,
				ce.fecha_fin AS fecha_final,
				CONCAT('Inicio de periodo') AS tipo_pago,
				CONCAT('Mensual') AS frecuencia_pago,
				tpr.nombre AS fijo_variable,
				ce.monto_renta AS importe_renta,
				IF(ce.pago_renta_id = 2, CONCAT(ce.cuota_variable,'% del ',tv.nombre),'') AS cuota_variable,
				tai.nombre AS afecto_igv,
				tir.nombre AS incluye_ir,
				CONCAT('Arrendamiento') AS califica_nif16,
				
				IF ( (SELECT COUNT(ad.id) AS total FROM cont_adelantos AS ad WHERE ad.contrato_id =  c.contrato_id) > 0, 'SI','NO') AS existe_pagos_anticipados,
				(SELECT COUNT(ad.id) AS total FROM cont_adelantos AS ad WHERE ad.contrato_id =  c.contrato_id)  AS total_pagos_anticipados,
				IF ( (SELECT COUNT(ad.id) AS total FROM cont_adelantos AS ad WHERE ad.contrato_id =  c.contrato_id) > 0, ((SELECT COUNT(ad.id) AS total FROM cont_adelantos AS ad WHERE ad.contrato_id =  c.contrato_id) * ce.monto_renta),'') AS pago_inicial_anticipado,	
				IF ( ce.periodo_gracia_id = 1 AND TRUNCATE(( IF(ce.periodo_gracia_numero > 0, ce.periodo_gracia_numero ,0 ) / 30  ) ,0) >= 1,  'SI','NO') AS existe_periodo_gracia,
				TRUNCATE(( IF(ce.periodo_gracia_numero > 0, ce.periodo_gracia_numero ,0 ) / 30  ) ,0) AS periodo_gracia_numero,
				IF ( c.tipo_inflacion_id = 1,  'SI','NO') AS existe_inflacion,
				inf.fecha AS inf_fecha_ajuste,
				CONCAT(tp.nombre,' ',inf.numero,' ',pr.nombre) AS periocidad_ajuste,
				mi.sigla AS curva_inflacion,
				inf.porcentaje_anadido AS porcentaje_anadido,
				inf.tope_inflacion AS tope_inflacion,
				inf.minimo_inflacion AS minimo_inflacion,
				
				IF(ce.tipo_incremento_id = 1,IF(inc.tipo_continuidad_id = 3, 'SI','NO'),'NO') AS tipo_incremento,
				DATE_ADD(ce.fecha_inicio, INTERVAL 1 YEAR) AS fecha_inicio_incremento,
				(CASE
					WHEN inc.tipo_continuidad_id = 3 THEN CONCAT(tci.nombre)  
					ELSE '' 
				END) AS continuidad,
				IF(inc.tipo_continuidad_id = 3, IF(inc.tipo_valor_id = 2, CONCAT(inc.valor, ' %'), CONCAT(m.simbolo, ' ', inc.valor) ) ,'') AS incremento,
				
				IF(c.tipo_cuota_extraordinaria_id = 1, 'SI','NO') AS cuota_extraordinaria,
				mes.nombre AS mes_extraordinario, 
				cex.multiplicador AS multiplicador,
				IF(c.tipo_cuota_extraordinaria_id = 1, '12','') AS cuantos_meses_prox_pago

				FROM cont_contrato AS c
				INNER JOIN cont_inmueble i ON i.contrato_id = c.contrato_id AND i.status = 1
				INNER JOIN tbl_ubigeo AS dpo ON dpo.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND dpo.cod_prov = '00' AND dpo.cod_dist = '00'
				INNER JOIN tbl_ubigeo AS prv ON prv.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND SUBSTRING(i.ubigeo_id, 3, 2) = prv.cod_prov AND prv.cod_dist = '00'
				INNER JOIN tbl_ubigeo AS dto ON dto.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND SUBSTRING(i.ubigeo_id, 3, 2) = dto.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = dto.cod_dist

				INNER JOIN tbl_razon_social AS rz ON rz.id = c.empresa_suscribe_id 
				INNER JOIN cont_propietario AS p ON p.contrato_id = c.contrato_id
				INNER JOIN cont_persona AS per ON per.id = p.persona_id

				INNER JOIN tbl_locales AS l ON l.contrato_id = c.contrato_id
				
				INNER JOIN cont_condicion_economica AS ce ON ce.contrato_id = c.contrato_id
				INNER JOIN tbl_moneda AS m ON m.id = ce.tipo_moneda_id 
				INNER JOIN cont_tipo_pago_renta tpr ON tpr.id = ce.pago_renta_id
				LEFT JOIN cont_tipo_afectacion_igv tai ON tai.id = ce.afectacion_igv_id
				LEFT JOIN cont_tipo_venta tv ON tv.id = ce.tipo_venta_id
				LEFT JOIN cont_tipo_impuesto_a_la_renta tir ON tir.id = ce.impuesto_a_la_renta_id
				
				LEFT JOIN cont_inflaciones AS inf ON inf.contrato_id = c.contrato_id
				LEFT JOIN cont_tipo_periodicidad AS tp ON tp.id = inf.tipo_periodicidad_id
				LEFT JOIN tbl_moneda AS mi ON mi.id = inf.moneda_id
				LEFT JOIN cont_periodo as pr ON pr.id = inf.tipo_anio_mes
				
				LEFT JOIN cont_incrementos AS inc ON inc.contrato_id = c.contrato_id AND inc.estado = 1
				LEFT JOIN cont_tipo_pago_incrementos AS tpi ON inc.tipo_valor_id = tpi.id
				LEFT JOIN cont_tipo_continuidad_pago AS tci ON inc.tipo_continuidad_id = tci.id
				
				LEFT JOIN cont_cuotas_extraordinarias AS cex ON cex.contrato_id = c.contrato_id
				LEFT JOIN tbl_meses AS mes ON mes.id = cex.mes
				
				WHERE 1 = 1
				AND c.tipo_contrato_id = 1
				AND c.etapa_id = 5
				AND c.status = 1
				AND p.status = 1
				AND ce.status = 1
				AND TIMESTAMPDIFF(MONTH, ce.fecha_inicio, ce.fecha_fin) >= 12
				
				AND (inf.status = 1 OR inf.status IS NULL)
				AND (cex.status = 1 OR cex.status IS NULL)
				$where_empresa
				$where_centro_costo
				$where_nombre_tienda 
				$where_direccion
				$where_ubigeo 
				$where_fech_suscripcion
				$where_fecha_inicio
				$where_fecha_fin
				$where_locales
				GROUP BY contrato_id
				ORDER BY c.nombre_tienda ASC
				";

			$list_query = $mysqli->query($query_nif16);
			$list_proc_registros = array();
			while ($sp = $list_query->fetch_assoc()) {
				array_push($list_proc_registros, array(
					'contrato_id' => $sp['contrato_id'],
					'nombre_tienda' => $sp['nombre_tienda'],
					'arrendador' =>  $sp['arrendador'],
					'arrendatario' =>  $sp['arrendatario'],
					'tipo_relacion' =>  $sp['tipo_relacion'],
					'tipo_activo' => $sp['tipo_activo'],
					'bajo_valor' => $sp['bajo_valor'],
					'centro_logistico' => $sp['centro_logistico'],
					'moneda' => $sp['moneda'],
					'fecha_inicio' => $sp['fecha_inicio'],
					'fecha_final' => $sp['fecha_final'],
					'tipo_pago' => $sp['tipo_pago'],
					'frecuencia_pago' => $sp['frecuencia_pago'],
					'fijo_variable' => $sp['fijo_variable'],
					'importe_renta' => $sp['importe_renta'],
					'cuota_variable' => $sp['cuota_variable'],
					'afecto_igv' => $sp['afecto_igv'],
					'incluye_ir' => $sp['incluye_ir'],
					'califica_nif16' => $sp['califica_nif16'],
					'existe_pagos_anticipados' => $sp['existe_pagos_anticipados'],
					'total_pagos_anticipados' => $sp['total_pagos_anticipados'],
					'pago_inicial_anticipado' => $sp['pago_inicial_anticipado'],
					'existe_periodo_gracia' => $sp['existe_periodo_gracia'],
					'periodo_gracia_numero' => $sp['periodo_gracia_numero'],
					'existe_inflacion' => $sp['existe_inflacion'],
					'inf_fecha_ajuste' => $sp['inf_fecha_ajuste'],
					'periocidad_ajuste' => $sp['periocidad_ajuste'],
					'curva_inflacion' => $sp['curva_inflacion'],
					'porcentaje_anadido' => $sp['porcentaje_anadido'],
					'tope_inflacion' => $sp['tope_inflacion'],
					'minimo_inflacion' => $sp['minimo_inflacion'],
					'tipo_incremento' => $sp['tipo_incremento'],
					'fecha_inicio_incremento' => $sp['fecha_inicio_incremento'],
					'continuidad' => $sp['continuidad'],
					'incremento' => $sp['incremento'],
					'cuota_extraordinaria' => $sp['cuota_extraordinaria'],
					'mes_extraordinario' => $sp['mes_extraordinario'],
					'multiplicador' => $sp['multiplicador'],
					'cuantos_meses_prox_pago' => $sp['cuantos_meses_prox_pago'],
				));
			}
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols['nombre_tienda'] = 'ID Contrato';
			$list_cols['arrendador'] = 'Arrendador';
			$list_cols['arrendatario'] = 'Arrendatario';
			$list_cols['tipo_relacion'] = 'Tipo de relación comercial con el arrendador';
			$list_cols['tipo_activo'] = 'Tipo de activo arrendado';
			$list_cols['bajo_valor'] = '¿Es un arrendamiento de bajo valor? (Low value leasing)';
			$list_cols['centro_logistico'] = 'Centro Logístico (Este campo solo es informativo)';
			$list_cols['moneda'] = 'Moneda del contrato';
			$list_cols['fecha_inicio'] = 'Fecha de inicio del contrato';
			$list_cols['fecha_final'] = 'Fecha Final del Contrato';
			$list_cols['tipo_pago'] = 'Tipo de pago';
			$list_cols['frecuencia_pago'] = 'Frecuencia de pago';
			$list_cols['fijo_variable'] = '¿El pago de la renta es totalmente fijo, totalmente variable o combinado?';
			$list_cols['importe_renta'] = 'Importe  de renta/cuota fijo';
			$list_cols['cuota_variable'] = 'Importe  de renta/cuota variable (solo informativo)';
			$list_cols['afecto_igv'] = '¿La renta incluye IGV, no incluye IGV o está inafecta al IGV?';
			$list_cols['incluye_ir'] = 'La renta incluye el impuesto a la renta y/o es asumida por el arrendatario';
			$list_cols['califica_nif16'] = 'Inclusión en el alcance de la NIIF 16';
			$list_cols['existe_pagos_anticipados'] = '¿Existen pagos anticipados al inicio?';
			$list_cols['total_pagos_anticipados'] = '¿Cuántos periodos son pagados anticipadamente?';
			$list_cols['pago_inicial_anticipado'] = '¿A cuánto asciende el pago inicial anticipado?';
			$list_cols['existe_periodo_gracia'] = '¿Existen periodos de gracia?';
			$list_cols['periodo_gracia_numero'] = '¿Cuántos periodos de gracia son?';
			$list_cols['existe_inflacion'] = '¿Se ajusta la cuota de acuerdo a la inflación?';
			$list_cols['inf_fecha_ajuste'] = 'Fecha en que se realiza el primer ajuste';
			$list_cols['periocidad_ajuste'] = 'Periodicidad del Ajuste';
			$list_cols['curva_inflacion'] = 'Curva de inflacion ';
			$list_cols['porcentaje_anadido'] = 'Porcentaje añadido a la inflación';
			$list_cols['tope_inflacion'] = 'Tope de Inflación';
			$list_cols['minimo_inflacion'] = 'Minimo de Inflación';
			$list_cols['tipo_incremento'] = '¿La renta/cuota pagada se incrementa periódicamente?';
			$list_cols['continuidad'] = '¿Cada cuánto se incrementa la renta?';
			$list_cols['fecha_inicio_incremento'] = '¿A partir de qué fecha  ocurre el incremento?';
			$list_cols['incremento'] = '¿Cuál es el porcentaje de incremento de la renta/ cuota?';
			$list_cols['cuota_extraordinaria'] = '¿Existe pago extraordinario?';
			$list_cols['mes_extraordinario'] = 'Mes en el que se paga extraordinariamente';
			$list_cols['multiplicador'] = 'Multiplicador extraordinario';
			$list_cols['cuantos_meses_prox_pago'] = '¿Cuántos meses después existe otro pago?';
			$list_cols_show = $list_cols;
		} elseif ($export == "reporte_por_cambio_cuota_moneda") {
			$where_empresa = "";
			$where_centro_costo = "";
			$where_nombre_tienda = "";
			$where_ubigeo = "";
			$where_direccion = "";
			$where_locales = "";

			if (!empty($empresa) && $empresa != "0") {
				$where_empresa = " AND c.empresa_suscribe_id IN (" . $empresa . ")";
			}
			if (!empty($centro_costo) && $centro_costo != "0") {
				$where_centro_costo = "  AND c.cc_id LIKE '%" . $centro_costo . "%'";
			}
			if (!empty($nomb_tienda) && $nomb_tienda != "0") {
				$where_nombre_tienda = "  AND c.nombre_tienda LIKE '%" . $nomb_tienda . "%'";
			}
			if (!empty($direccion) && $direccion != "0") {
				$where_direccion = "  AND i.ubicacion LIKE '%" . $direccion . "%'";
			}
			if (!empty($departamento) && $departamento != "0") {
				$where_ubigeo .= "  AND dpo.cod_depa = '" . $departamento . "'";
			}
			if (!empty($provincia) && $provincia != "0") {
				$where_ubigeo .= "  AND prv.cod_prov = '" . $provincia . "'";
			}
			if (!empty($distrito) && $distrito != "0") {
				$where_ubigeo .= "  AND dto.cod_dist = '" . $distrito . "'";
			}
			if ($login["usuario_locales"]) {
				$where_locales = " AND l.id IN (" . implode(",", $login["usuario_locales"]) . ")";
			}

			$query_nif16 = "
				SELECT
					c.cc_id AS centro_de_costo,
					c.nombre_tienda AS nombre_de_tienda,
					m.sigla AS sigla_de_la_moneda,
					ccm.fecha_decision,
					ccm.fecha_cambio,
					ccm.importe,
					ccm.enmienda,
					ccm.inflacion,
					ccm.incremento_renta,
					ccm.cuota_extraordinaria
				FROM
					cont_cambio_cuota_moneda AS ccm
					INNER JOIN cont_contrato c ON c.contrato_id = ccm.contrato_id
					INNER JOIN tbl_locales as l ON l.contrato_id = c.contrato_id
					INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id
					INNER JOIN tbl_moneda m ON ce.tipo_moneda_id = m.id
					INNER JOIN cont_inmueble i ON i.contrato_id = c.contrato_id
					INNER JOIN tbl_ubigeo AS dpo ON dpo.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND dpo.cod_prov = '00' AND dpo.cod_dist = '00'
					INNER JOIN tbl_ubigeo AS prv ON prv.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND SUBSTRING(i.ubigeo_id, 3, 2) = prv.cod_prov AND prv.cod_dist = '00'
					INNER JOIN tbl_ubigeo AS dto ON dto.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND SUBSTRING(i.ubigeo_id, 3, 2) = dto.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = dto.cod_dist
				WHERE 
					ccm.status = 1 
					AND c.status = 1 
					AND ce.status = 1

					$where_empresa
					$where_centro_costo
					$where_nombre_tienda 
					$where_direccion
					$where_ubigeo 
					$where_locales

				ORDER BY c.nombre_tienda ASC
				";

			$list_query = $mysqli->query($query_nif16);
			$list_proc_registros = array();
			while ($sp = $list_query->fetch_assoc()) {
				array_push($list_proc_registros, array(
					'centro_de_costo' => $sp['centro_de_costo'],
					'nombre_de_tienda' => $sp['nombre_de_tienda'],
					'sigla_de_la_moneda' =>  $sp['sigla_de_la_moneda'],
					'fecha_decision' =>  $sp['fecha_decision'],
					'fecha_cambio' =>  $sp['fecha_cambio'],
					'importe' => $sp['importe'],
					'enmienda' => $sp['enmienda'],
					'inflacion' => $sp['inflacion'],
					'incremento_renta' => $sp['incremento_renta'],
					'cuota_extraordinaria' => $sp['cuota_extraordinaria'],
				));
			}
			$list = array();
			for ($i = 0; $i < count($list_proc_registros); $i++) {
				$list[] = $list_proc_registros[$i];
			}
			$list_cols = array();
			$list_cols['centro_de_costo'] = 'Centro de Costo';
			$list_cols['nombre_de_tienda'] = 'Nombre de tienda';
			$list_cols['sigla_de_la_moneda'] = 'Moneda';
			$list_cols['fecha_decision'] = 'Fecha de decisión';
			$list_cols['fecha_cambio'] = 'Fecha de cambio';
			$list_cols['importe'] = 'Importe de la nueva cuota';
			$list_cols['enmienda'] = 'Enmienda';
			$list_cols['inflacion'] = '¿Sigue afecto por Inflación?';
			$list_cols['incremento_renta'] = '¿Sigue afecto por Incrementos de renta?';
			$list_cols['cuota_extraordinaria'] = '¿Sigue afecto por Cuotas Extraordinaria?';
			$list_cols_show = $list_cols;
		}

		?>

		<head>
			<style>
				body {
					font-family: Arial, sans-serif;
				}

				table {
					width: 100%;
					border-collapse: collapse;
					margin-bottom: 20px;
				}

				th,
				td {
					border: 1px solid #ddd;
					padding: 8px;
					text-align: left;
				}

				th {
					background-color: #395168;
					text-align: center;
					color: white;
				}

				.btn-ver-detalle {
					background-color: #4CAF50;
					border: none;
					color: white;
					padding: 5px 20px;
					text-align: center;
					text-decoration: none;
					display: inline-block;
					font-size: 16px;
				}

				.title {
					font-size: 18px;
					font-weight: bold;
					margin-bottom: 10px;
					text-align: center;
				}
			</style>
			<?php
			if (isset($tipo_contrato_id)) {
				if ($tipo_contrato_id == 12) {
					echo '<div class="title">Solicitudes de Contratos de Arrendamiento</div>';
				}
				if ($tipo_contrato_id == 13) {
					echo '<div class="title">Solicitudes de Contratos de Locación de Servicio</div>';
				}
				if ($tipo_contrato_id == 14) {
					echo '<div class="title">Solicitudes de Contratos de Mandato</div>';
				}
				if ($tipo_contrato_id == 15) {
					echo '<div class="title">Solicitudes de Contratos de Mutuo Dinero</div>';
				}
				if ($tipo_contrato_id == 3) {
					echo '<div class="title">Solicitudes de Adenda de Contrato de Arrendamiento</div>';
				}
				if ($tipo_contrato_id == 16) {
					echo '<div class="title">Solicitudes de Adenda de Contrato de Locación de Servicio</div>';
				}
				if ($tipo_contrato_id == 17) {
					echo '<div class="title">Solicitudes de Adenda de Contrato de Mandato</div>';
				}
				if ($tipo_contrato_id == 18) {
					echo '<div class="title">Solicitudes de Adenda de Contrato de Mutuo de Dinero</div>';
				}
			} else {
				if ($export == "cont_contrato_arrendamiento") {
					echo '<div class="title">Contratos de Arrendamiento</div>';
				}
				if ($export == "cont_contrato_locacion") {
					echo '<div class="title">Contratos de Locación de Servicio</div>';
				}
				if ($export == "cont_contrato_mandato") {
					echo '<div class="title">Contratos de Mandato</div>';
				}
				if ($export == "cont_contrato_mutuodinero") {
					echo '<div class="title">Contratos de Mutuo de Dinero</div>';
				}
			}
			?>
		</head>

		<body>
			<table>
				<thead>
					<tr><?php foreach ($list_cols_show as $key => $value) { ?>
							<th><?php echo $value; ?></th><?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($list as $l_k => $l_v) {
					?><tr>
							<?php
							foreach ($list_cols_show as $key => $value) {
							?>
								<td>
									<?php if ($key === 'Ver Detalle') { ?>
										<a href="<?php echo $l_v[$key]; ?>" class="btn-ver-detalle">Ver Detalle</a>
									<?php } else { ?>
										<?php echo $l_v[$key]; ?>
									<?php } ?>
								</td> <?php
									}
										?>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>
		</body>
	</table>

<?php

}

if (isset($_POST["table_to_xls"])) {
	print_r($_POST["table_to_xls"]);
}

function Utf8_ansi($valor = '')
{

	$utf8_ansi2 = array(
		"u00c0" => "À",
		"u00c1" => "Á",
		"u00c2" => "Â",
		"u00c3" => "Ã",
		"u00c4" => "Ä",
		"u00c5" => "Å",
		"u00c6" => "Æ",
		"u00c7" => "Ç",
		"u00c8" => "È",
		"u00c9" => "É",
		"u00ca" => "Ê",
		"u00cb" => "Ë",
		"u00cc" => "Ì",
		"u00cd" => "Í",
		"u00ce" => "Î",
		"u00cf" => "Ï",
		"u00d1" => "Ñ",
		"u00d2" => "Ò",
		"u00d3" => "Ó",
		"u00d4" => "Ô",
		"u00d5" => "Õ",
		"u00d6" => "Ö",
		"u00d8" => "Ø",
		"u00d9" => "Ù",
		"u00da" => "Ú",
		"u00db" => "Û",
		"u00dc" => "Ü",
		"u00dd" => "Ý",
		"u00df" => "ß",
		"u00e0" => "à",
		"u00e1" => "á",
		"u00e2" => "â",
		"u00e3" => "ã",
		"u00e4" => "ä",
		"u00e5" => "å",
		"u00e6" => "æ",
		"u00e7" => "ç",
		"u00e8" => "è",
		"u00e9" => "é",
		"u00ea" => "ê",
		"u00eb" => "ë",
		"u00ec" => "ì",
		"u00ed" => "í",
		"u00ee" => "î",
		"u00ef" => "ï",
		"u00f0" => "ð",
		"u00f1" => "ñ",
		"u00f2" => "ò",
		"u00f3" => "ó",
		"u00f4" => "ô",
		"u00f5" => "õ",
		"u00f6" => "ö",
		"u00f8" => "ø",
		"u00f9" => "ù",
		"u00fa" => "ú",
		"u00fb" => "û",
		"u00fc" => "ü",
		"u00fd" => "ý",
		"u00ff" => "ÿ"
	);
	return strtr($valor, $utf8_ansi2);
}


function sec_contrato_detalle_solicitud_de_meses_a_anios_y_meses($meses)
{
	if ($meses < 12) {
		$anio_y_meses = $meses . ' meses';
	} else {
		$anio = intval($meses / 12);
		$meses_restantes = $meses % 12;

		if ($anio == 0) {
			$anio = '';
		} else if ($anio == 1) {
			$anio = $anio . ' año';
		} else if ($anio > 1) {
			$anio = $anio . ' años';
		}

		if ($meses_restantes == 0) {
			$meses_restantes = '';
		} else if ($meses_restantes == 1) {
			$meses_restantes = ' y ' . $meses_restantes . ' mes';
		} else if ($meses_restantes > 1) {
			$meses_restantes = ' y ' . $meses_restantes . ' meses';
		}

		return $anio . $meses_restantes;
	}
}

function sec_contrato_solicitud_num_dias_habiles_formato($num_dias_habiles)
{
	$texto = ((int) $num_dias_habiles === 1) ? 'día hábil' : 'días hábiles';
	return $num_dias_habiles . ' ' . $texto;
}


?>