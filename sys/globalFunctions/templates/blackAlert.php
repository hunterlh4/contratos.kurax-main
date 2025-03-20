<?php


/*
Genera una Alerta Negra en formato tabla para email
INPUT: informacion del body para el correo
OUPUT: html
*/
function generateTableAlertBlack($info)
{
    $body = "";
    $body .= '<table border="1" width="700px" cellpadding="5" cellspacing="0" style="font-family: arial">';
    $body .= '<thead>';
    $body .= '<tr>';
    $body .= '<th colspan="2" style=" color:#ffffff; background-color: #000000; vertical-align: middle; font-size: 16px">';
    $body .= 'ALERTA NEGRA AGENTE AT';
    $body .= '</th>';
    $body .= '</tr>';
    $body .= '</thead>';
    $body .= '<tbody>';
    foreach ($info as $i) {
        $body .= '<tr>';
        $body .= '<td valign="top"><b>'.$i[0].'</b></td>';
        $body .= '<td valign="top"> ' . $i[1] . '</td>';
        $body .= '</tr>';
    }
    $body .= '</tbody>';
    $body .= '</table>';
    return $body;
}

?>