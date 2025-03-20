<?php
include("db_connect.php");
include("sys_login.php");
$input = json_decode(json_encode($_GET));
$get_report_info_head = fnc_get_data_movilidad($input->cc_movilidad_id);
$get_report_info_body = fnc_get_data_movilidad_detalle($input->cc_movilidad_id);
function fnc_get_data_movilidad($cc_movilidad_id)
{
    global $mysqli;
    $error = '';
    $querySelect = "
    SELECT mccm.id,
        mccm.num_correlativo,
        mccm.fecha_del,
        mccm.fecha_al,
        IFNULL((SELECT    
            mza.centro_costo
            FROM
            mepa_caja_chica_liquidacion     AS mccl
            LEFT JOIN mepa_asignacion_caja_chica AS macc
                ON  macc.id = mccl.asignacion_id
            LEFT JOIN mepa_zona_asignacion  AS mza
                ON  mza.id = macc.zona_asignacion_id
            WHERE  mccl.id_movilidad = mccm.id LIMIT 1),'S/N') AS cc_id,
        mccm.status,
        mccm.user_created_id,
        u.usuario,
        CONCAT(pa.nombre,' ',pa.apellido_paterno,' ',pa.apellido_materno )as nombre,
        pa.dni,
        mccm.created_at,
        mccm.updated_at
    FROM   mepa_caja_chica_movilidad AS mccm
    LEFT JOIN tbl_usuarios AS u ON u.id = mccm.user_created_id
    LEFT JOIN tbl_personal_apt AS pa ON pa.id = u.personal_id
    WHERE mccm.id = {$cc_movilidad_id}
    ";
    $resultQuery = $mysqli->query($querySelect);
    $arrayReturn = new stdClass();
    while ($li = $resultQuery->fetch_assoc()) {
        $arrayReturn->id = $li['id'];
        if ($li['num_correlativo'] == '' || $li['num_correlativo'] == null) {
            $arrayReturn->num_correlativo = 'S/N';
        } else {
            $arrayReturn->num_correlativo = $li['num_correlativo'];
        }
        $arrayReturn->empresa = $li['empresa'];
        $arrayReturn->fecha_del = $li['fecha_del'];
        $arrayReturn->fecha_al = $li['fecha_al'];
        $arrayReturn->cc_id = $li['cc_id'];
        $arrayReturn->status = $li['status'];
        $arrayReturn->user_created_id = $li['user_created_id'];
        $arrayReturn->usuario = $li['usuario'];
        $arrayReturn->dni = $li['dni'];
        $arrayReturn->nombre = $li['nombre'];
        $arrayReturn->created_at = $li['created_at'];
        $arrayReturn->updated_at = $li['updated_at'];
    }
    return $arrayReturn;
}
function fnc_get_data_movilidad_detalle($cc_movilidad_id)
{
    global $mysqli;
    $error = '';
    $querySelect = "
    SELECT
        mccmd.id,
        mccmd.id_mepa_caja_chica_movilidad,
        mccmd.fecha,
        upper(mccmd.partida_destino) as partida_destino,
        upper(mccmd.motivo_traslado) as motivo_traslado,
        mccmd.monto,
        mccmd.estado,
        mccmd.created_at,
        mccmd.updated_at
    FROM
        mepa_caja_chica_movilidad_detalle AS mccmd
    WHERE 
     mccmd.estado = 1
    AND mccmd.id_mepa_caja_chica_movilidad =  '{$cc_movilidad_id}'
    ORDER BY mccmd.fecha ASC
    ";
    $resultQuery = $mysqli->query($querySelect);
    $arrayReturn = array();
    while ($li = $resultQuery->fetch_assoc()) {
        $arrayReturn[$li['fecha']][] = $li;
    }
    return $arrayReturn;
}
ob_start();
$nombreImagen = "/var/www/html/img/logo_at_report.jpg";
$imagenBase64 = "data:image/png;base64," . base64_encode(file_get_contents($nombreImagen));


// INICIO: OBTENER EL RUC DE LA ASIGNACION, DONDE PERTENECE ESTA SOLICTUD DE MOVILIDAD
// EJEMPLO: Solicitud de movilidad Pertenece a -> Solicitud de Liquidacion Pertenece a -> Solicitud de Asignacion

$razon_social_id = 0;
$razon_social_ruc = "SIN ASIGNAR";
$razon_social_nombre = "SIN ASIGNAR";

$query_datos_razon_social = "
    SELECT
        rs.id,
        rs.nombre,
        rs.ruc
    FROM mepa_caja_chica_movilidad m
    INNER JOIN mepa_caja_chica_liquidacion l
    ON m.id = l.id_movilidad
    INNER JOIN mepa_asignacion_caja_chica a
    ON l.asignacion_id = a.id
    INNER JOIN tbl_razon_social rs
    ON a.empresa_id = rs.id
    WHERE m.id = '".$input->cc_movilidad_id."'
    LIMIT 1
";

$list_query_query_datos_razon_social = $mysqli->query($query_datos_razon_social);

$row_count = $list_query_query_datos_razon_social->num_rows;

if ($row_count > 0) 
{
    $row = $list_query_query_datos_razon_social->fetch_assoc();
    $razon_social_id = $row["id"];
    $razon_social_nombre = $row["nombre"];
    $razon_social_ruc = $row["ruc"];
}


// FIN: OBTENER EL RUC DE LA ASIGNACION, DONDE PERTENECE ESTA SOLICTUD DE MOVILIDAD


?>

<table style="font-family: Arial;">
    <tbody >
        <tr>
            <td style="width: 90px;" rowspan="4"><img width="100px" src="<?PHP ECHO($imagenBase64);?>"/></td>
            <td ><strong style='font-size: 11px;color:red;'><?php echo ($razon_social_nombre); ?></strong></td>          
        </tr>
        <tr>
            
            <td ><?php echo (''); ?></td>         
        </tr>
        <tr>
            <td ><strong style='font-size: 11px;color:red;'><?php echo ($razon_social_ruc); ?></strong></td>          
        </tr>
        <tr>
            
            <td ><?php echo (''); ?></td>         
        </tr>
        
    </tbody>
</table>
<table style="border: 0px solid black;font-size:12px;">
    <tbody>
        <tr>
            <td style="text-align: center;height: 50 px;vertical-align: middle;" colspan="4"> <u> PLANILLA DE GASTOS DE MOVILIDAD Nº<?php echo ($get_report_info_head->num_correlativo); ?></u></td>
        </tr>
        <tr>
            <td style="width: 180px;">&nbsp;Fecha de Emisi&oacute;n:</td>
            <td style="border-bottom: 0.5pt solid black; width: 250px;"><?php echo ($get_report_info_head->created_at); ?></td>
            <td style="width: 142px;">&nbsp;DNI:</td>
            <td style="border-bottom: 0.5pt solid black; width: 134.031px;"><?php echo ($get_report_info_head->dni); ?></td>
        </tr>
        <tr>
            <td style="width: 180px;">&nbsp;Nombre :</td>
            <td style="border-bottom: 0.5pt solid black; width: 250px;"><?php echo ($get_report_info_head->nombre); ?></td>
            <td style="width: 142px;">&nbsp;CECO:</td>
            <td style="border-bottom: 0.5pt solid black; width: 130px;;"><?php echo ($get_report_info_head->cc_id); ?></td>
        </tr>
        <tr>
            <td style="width: 180px;">&nbsp;Departamento:</td>
            <td style="border-bottom: 0.5pt solid black; width: 250px;">--</td>
            <td style="width: 142px;">&nbsp;</td>
            <td style="width: 130px;">&nbsp;</td>
        </tr>
        <tr>
            <td style="width: 180px;">&nbsp;Periodo:</td>
            <td style="border-bottom: 0.5pt solid black; width: 250px;"><?php echo ($get_report_info_head->fecha_del . ' al ' . $get_report_info_head->fecha_al); ?></td>
            <td style="width: 142px;">&nbsp;</td>
            <td style="width: 130px;">&nbsp;</td>
        </tr>
    </tbody>
    <br>
</table>
<table width="80%" border="1" cellspacing="0" cellpadding="0" style="border: 0.5pt solid black;">
    <tr bgcolor="#91c1e9">
        <th style="width: 80px;">Fecha</th>
        <th style="width: 240px;">Partida-Destino</th>
        <th style="width: 240px;">Motivo de la movilidad como condición de trabajo</th>
        <th style="width: 80px;">Sub Total x Viaje</th>
        <th style="width: 80px;">Total por día</th>
    </tr>
    <?php
    $body = '';
    foreach ($get_report_info_body as $key => $date_info) 
    {
        $sum_per_day = 0.00;
        $date_group = '';
        $count_rows = 0;
        $body_td = "";
        
        foreach ($date_info as $info) 
        {
            $count_rows++;
            $sum_per_day += (float)$info['monto'];
            $date_group = $info['fecha'];
        }
        
        $sum_per_day = number_format($sum_per_day, 2);
        
        foreach ($date_info as $key => $info) 
        {
            $body .= '<tr>';
            if ($key == 0) 
            {
                $body .= "<td style='text-align: center;vertical-align: middle;' rowspan='{$count_rows}'><strong style='font-size: 15px;'>{$date_group}</strong></td>";
            }
            $body .= "<td style='font-size: 10px;height: 30px;'>{$info['partida_destino']} </td>";
            $body .= "<td style='font-size: 10px;'>{$info['motivo_traslado']} </td>";
            $body .= "<td  style='text-align: center;vertical-align: middle;'>S/. {$info['monto']} </td>";
            
            if ($key == 0) 
            {
                $body .= "<td  style='text-align: center;vertical-align: middle;' rowspan='{$count_rows}'>S/. {$sum_per_day}</td>";
            }
            $body .= '</tr>';
        }
    }
    echo ($body);
    ?>
</table>
<?php
// $html = ob_get_clean();
// require_once './dompdf/autoload.inc.php';
// use Dompdf\Dompdf;
// $dompdf = new Dompdf();
// $option = $dompdf->getOptions();
// $option->set(array('isRemoteEnable' => true));
// $dompdf->setOptions($option);
// $dompdf->loadHtml(($html));
// $dompdf->setPaper('letter');
// $dompdf->render();
// header("Content-type: application/pdf");
// header("Content-Disposition: inline; filename=documento.pdf");
// $dompdf->stream();

$html = ob_get_clean();
require_once './dompdf/autoload.inc.php';
use Dompdf\Dompdf;
$dompdf = new Dompdf();
$option = $dompdf->getOptions();
$option->set(array('isRemoteEnable' => true));
$dompdf->setOptions($option);
$dompdf->loadHtml(($html));
$dompdf->setPaper('letter');
$dompdf->render();
$output = $dompdf->output();
$nameFile = 'reporte_movilidad_'.strtotime("now").'.pdf';
$filePath = '/var/www/html/export/files_exported/reporte_mepa_asignacion/'.$nameFile;
file_put_contents($filePath, $output);
header("Location: "."/export/files_exported/reporte_mepa_asignacion/".$nameFile);
exit(0);
