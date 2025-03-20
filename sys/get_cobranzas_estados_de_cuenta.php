<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
// include("global_config.php");
include("db_connect.php");
// include("set_clientes.php");
include("sys_login.php");
require_once '/var/www/html/sys/helpers.php';
require_once '/var/www/html/sys/fpdf/fpdf.php';
include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

function send_email_ag($request){
	
	if ( !isset($request["subject"])) {
		return "Definir Subject";
	}
	if( !isset($request["body"]) ){
		return "Definir Body";
	}
	if ( !isset($request["cc"]) && !isset($request["bcc"]) ) {
		return "Definir CC o BCC";
	}

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();                                            // Send using SMTP
		$mail->Host       = isset($request["Host"]) ? $request["Host"] : "smtp.gmail.com";
		$mail->SMTPAuth   = isset($request["SMTPAuth"]) ? $request["SMTPAuth"] : true;
		$mail->Username   = isset($request["Username"]) ? $request["Username"] : env('MAIL_LIQUIDACIONES_USER');
		$mail->Password   = isset($request["Password"]) ? $request["Password"] : env('MAIL_LIQUIDACIONES_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
		$mail->Port       = isset($request["Port"]) ? $request["Port"] : 465;
		$mail->CharSet    = isset($request["CharSet"]) ? $request["CharSet"] : 'utf-8';
		$mail->SMTPKeepAlive 	= true;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_LIQUIDACIONES_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_LIQUIDACIONES_NAME');

		$mail->Priority   = isset($request["Priority"]) ? $request["Priority"] : 2;
		if(isset($request["Priority"]) && isset($request["Priority"]) == 1){
			$mail->AddCustomHeader("X-MSMail-Priority: Urgent");
			$mail->AddCustomHeader("Importance: High");
		}

		if(isset($request["cc"])){
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"])){
			foreach ($request["bcc"] as $bcc) {
				$mail->addBCC($bcc);
			}
		}

		if(isset($request["attach"])){
			if(is_array($request["attach"])){
				for ($i=0; $i < count($request["attach"]) ; $i++) {
					$mail->addAttachment($request["attach"][$i]);
				}
			}else{
				$mail->addAttachment($request["attach"]);
			}
		}
		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];

		$mail->send();
		return true;
		// echo 'Message has been sent';
	} catch (Exception $e) {
		return "Error: {$mail->ErrorInfo}";
	}
}
function generar_pdf($datos_agente,$l , $estado_cuenta){

	$resultados_participaciones=[];
	$resultados_participaciones["clientepbet"] = ["monto"=>"0.00"];
	$resultados_participaciones["clientesbtnegocios"] = ["monto"=>"0.00"];
	$resultados_participaciones["clientegr"] = ["monto"=>"0.00"];
	$resultados_participaciones["bingo"] = ["monto"=>"0.00"];
	$resultados_participaciones["web"] = ["monto"=>"0.00"];
	$resultados_participaciones["totalagente"] = ["monto"=>"0.00"];
	$resultados_participaciones["oatpbet"] = ["monto"=>"0.00"];
	$resultados_participaciones["oatsbtnegocios"] =	["monto"=>"0.00"];
	$resultados_participaciones["oatgr"] = ["monto"=>"0.00"];
	$resultados_participaciones["oatbingo"] = ["monto"=>"0.00"];
	$resultados_participaciones["oatweb"] = ["monto"=>"0.00"];
	$resultados_participaciones["pago_de_bingos"] = ["monto"=>"0.00"];
	$resultados_participaciones["hipica"] = ["monto"=>"0.00"];
	$resultados_participaciones["ttoat"] = ["monto"=>"0.00"];

	$datos_sistema=[];
	$datos_sistema["apuestasdeportivas"]=[
		"apostado"=>"0.00"
		,"ganado"=>"0.00"
		,"resultado"=>"0.00"];

	if(isset($datos_agente[16])){
		$datos_sistema["apuestasdeportivas"]=[
			"apostado"=> $datos_agente[16]["total_apostado"]  ,
			"ganado"=>   $datos_agente[16]["total_pagado"]    ,
			"resultado"=>$datos_agente[16]["total_produccion"]
		];
		
		$resultados_participaciones["clientepbet"]=["monto"=>$datos_agente[16]["total_cliente"]];
		$resultados_participaciones["oatpbet"]=["monto"=>$datos_agente[16]["total_freegames"]];

		$resultados_participaciones["oatweb"]=["monto"=> $datos_agente[16]["total_caja_web"]];

	}

	$datos_sistema["web"]=[
		 "apostado"=>"0.00"
		,"ganado"=>"0.00"
		,"resultado"=>"0.00"
	];

	if(isset($datos_agente[15])){
		$datos_sistema["web"]=[
			 "apostado"=>   $datos_agente[15]["total_apostado"]
			,"ganado"=>   	$datos_agente[15]["total_pagado"]
			,"resultado"=>	$datos_agente[15]["total_produccion"]
		];

		$resultados_participaciones["web"]=["monto"=>	$datos_agente[15]["total_cliente"]];
		//$resultados_participaciones["oatweb"]=["monto"=> $datos_agente[15]["total_freegames"]];
	}

	$datos_sistema["goldenrace"]=[
		"apostado"=>"0.00"
		,"ganado"=>"0.00"
		,"resultado"=>"0.00"];

	if(isset($datos_agente[21])){
		$datos_sistema["goldenrace"]=[
			"apostado"=>	$datos_agente[21]["total_apostado"]
			,"ganado"=>		$datos_agente[21]["total_pagado"]
			,"resultado"=>	$datos_agente[21]["total_produccion"]
		];

		$resultados_participaciones["clientegr"]=["monto"=>	$datos_agente[21]["total_cliente"]];
		$resultados_participaciones["oatgr"]=["monto"=> 		$datos_agente[21]["total_freegames"]];
	}

	$datos_sistema["billeteroterminal"]=["apostado"=>"0.00"
	,"ganado"=>"0.00"
	,"resultado"=>"0.00"];

	if(isset($datos_agente[17])){
		$datos_sistema["billeteroterminal"]=["apostado"=> $datos_agente[17]["total_depositado"] - $datos_agente[17]["total_anulado_retirado"]
		,"ganado"=>	$datos_agente[17]["total_pagado"]
		,"resultado"=>$datos_agente[17]["total_produccion"]
		];

		$resultados_participaciones["clientesbtnegocios"]=["monto"=>	$datos_agente[17]["total_cliente"]];
		$resultados_participaciones["oatsbtnegocios"]=["monto"=> 	$datos_agente[17]["total_freegames"]];
	}
	$datos_sistema["cashinout"]=["ganado"=>"0.00"
		,"resultado"=>"0.00"			
		,"apostado"=>"0.00"];

	// $datos_sistema["lapolla"]=["ganado"=>"0.00"
	// 	,"resultado"=>"0.00"
	// 	,"apostado"=>"0.00"];

	$datos_sistema["bingo"]=["ganado"=>"0.00"
		,"resultado"=>"0.00"			
		,"apostado"=>"0.00"];

	if(isset($datos_agente[30])){
		$datos_sistema["bingo"]=[
			"apostado"=> $datos_agente[30]["total_apostado"]
			,"ganado"=>	$datos_agente[30]["total_pagado"]
			,"resultado"=>$datos_agente[30]["total_apostado"] /*$datos_agente[30]["resultado_negocio"]*/
		];
		//$resultados_participaciones["pago_de_bingos"] = ["monto"=>$datos_agente[30]["total_pagado"]];
		$resultados_participaciones["pago_de_bingos"] = ["monto"=>$datos_agente[30]["pagados_en_su_punto_propios"]];

		$resultados_participaciones["bingo"] = ["monto"=>	$datos_agente[30]["total_cliente"]];
		$resultados_participaciones["oatbingo"]=["monto"=> 	$datos_agente[30]["total_freegames"]];
	}

	$datos_sistema["hipica"]=["ganado"=>"0.00"
		,"resultado"=>"0.00"
		,"apostado"=>"0.00"];

	if(isset($datos_agente[34])){
		$datos_sistema["hipica"]=[
			"apostado"=> $datos_agente[34]["total_apostado"]
			,"ganado"=>	$datos_agente[34]["total_pagado"]
			,"resultado"=>$datos_agente[34]["total_produccion"] /*$datos_agente[34]["resultado_negocio"]*/
		];
		$resultados_participaciones["pago_de_hipicas"] = ["monto"=>$datos_agente[34]["total_freegames"]];

		$resultados_participaciones["hipica"] = ["monto"=>	$datos_agente[34]["total_cliente"]];
	}


	$datos_sistema["total"]=[
		 "ganado"=>"0.00"
		,"resultado"=>"0.00"
		,"apostado"=>"0.00"
	];
	if(isset($datos_agente["total"])){
		$total_resultado =  $datos_sistema["apuestasdeportivas"]["resultado"]+
							$datos_sistema["web"]["resultado"]+
							$datos_sistema["goldenrace"]["resultado"]+
							$datos_sistema["billeteroterminal"]["resultado"]+
							$datos_sistema["cashinout"]["resultado"]+
							//$datos_sistema["lapolla"]["resultado"]+
							$datos_sistema["bingo"]["resultado"] + 
							$datos_sistema["hipica"]["resultado"]
							;

		$total_apostado =  $datos_sistema["apuestasdeportivas"]["apostado"]+
							$datos_sistema["web"]["apostado"]+
							$datos_sistema["goldenrace"]["apostado"]+
							$datos_sistema["billeteroterminal"]["apostado"]+
							$datos_sistema["cashinout"]["apostado"]+
							$datos_sistema["bingo"]["apostado"] + 
							$datos_sistema["hipica"]["apostado"]
							;

		$datos_sistema["total"]=[
			"apostado"=> $total_apostado
			,"ganado"=>	($datos_agente["total"]["total_pagado"] - $datos_sistema["bingo"]["ganado"] )
			,"resultado"=>$total_resultado /*$datos_agente["total"]["resultado_negocio"]*/
		];
		$resultados_participaciones["totalagente"]=["monto"=> 	$datos_agente["total"]["total_cliente"]];
		
		$part_web = isset($datos_agente[16])?$datos_agente[16]["total_caja_web"] : 0;
		$resultados_participaciones["ttoat"]=["monto"=> ($datos_agente["total"]["total_freegames"]+$part_web) -  $resultados_participaciones["pago_de_bingos"]["monto"]];
		//$resultados_participaciones["ttoat"]=["monto"=> 	$datos_agente["total"]["total_freegames"]];
	}
	///PDF							
	$pdf = new FPDF();
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',16);
	$pdf -> SetX(77); 
	$pdf->Cell(40,20,'Reporte Agentes');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',10);

	$pdf -> SetX(14);
	$pdf->Cell(17,10,'Local :');
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(60,10,$l["id"]." ".$l["nombre"]);
	//$pdf->Ln();

	$x_tabla_anual = 115;
	$pdf -> SetX($x_tabla_anual);
	//$pdf->SetTextColor(52, 73, 94);
	$pdf->SetFont('Arial','B',9);
	$pdf->SetTextColor(255,255,255);
	$pdf->setFillColor(185, 27, 27);
	$pdf->Cell(25,7,"Deuda" ,1,0, 'C',TRUE);
	$pdf->Cell(25,7,"Pagado" ,1,0,'C',TRUE);
	$pdf->Cell(25,7,"Saldo" ,1,0, 'C',TRUE);
	$pdf->SetTextColor(0,0,0);
	$pdf->setFillColor(0,0,0);
	$pdf->SetFont('Arial','',9);
	$pdf->Ln();

	$pdf -> SetX(14);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(17,10,'Periodo :');
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(60,10,$_POST["periodo"]);

	$x_tabla_anual = 115;
	$pdf->SetX($x_tabla_anual);
	$pdf->Cell(25, 7, $estado_cuenta["debe"] ,1,0,'R');
	$pdf->Cell(25, 7, $estado_cuenta["haber"],1,0,'R');
	if($estado_cuenta["local_deuda"] > 0){
		$pdf->SetTextColor(255,0,0);//red
	}
	else{
		$pdf->SetTextColor(28,183,135);//green
	}
	$pdf->Cell(25, 7, $estado_cuenta["local_deuda"],1,0,'R');
	$pdf->SetTextColor(0,0,0);
	$pdf->Ln();
	//$pdf->Ln();

	$w_col_tipo1=88;
	$w_col_apostado=30;
	$w_col_ganado=30;
	$w_col_resultado=30;
	$h_tr=8;
	$h_tr1=8;
	$x_tabla1=14;
	$pdf->SetFont('Arial','B',12);
	$pdf -> SetX($x_tabla1); 
	$pdf->Cell($w_col_tipo1,12,"DATOS DEL SISTEMA",0);
	$pdf->Ln();

	$pdf->SetFont('Arial','B',9);
	$pdf -> SetX($x_tabla1); 
	$pdf->SetTextColor(52, 73, 94);
	$pdf->Cell($w_col_tipo1,$h_tr,"Tipo",1);
	$pdf->Cell($w_col_apostado,$h_tr,"TT Apostado",1);
	$pdf->Cell($w_col_ganado,$h_tr,"TT Pagado",1);
	$pdf->Cell($w_col_resultado,$h_tr,"TT Resultado",1);
	$pdf->Ln();

	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont('Arial','',9);
	$pdf -> SetX($x_tabla1); 
	$pdf->Cell($w_col_tipo1,$h_tr1,utf8_decode("Apuestas Deportivas(Apuestas deportivas realizadas en caja)"),1);

	/*$pdf->SetFont('Arial','I',8);
	$pdf->Cell($w_col_tipo1,($h_tr1/2),utf8_decode("(Apuestas deportivas realizadas en caja)"),0);
	$pdf->SetFont('');*/
	$pdf->Cell($w_col_apostado,$h_tr1, number_format($datos_sistema["apuestasdeportivas"]["apostado"], 2, ".", ",") ,1,0,'R');
	$pdf->Cell($w_col_ganado,$h_tr1,number_format($datos_sistema["apuestasdeportivas"]["ganado"], 2, ".", ","),1,0,'R');
	$pdf->Cell($w_col_resultado,$h_tr1,number_format($datos_sistema["apuestasdeportivas"]["resultado"], 2, ".", ","),1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla1); 
	$pdf->Cell($w_col_tipo1,$h_tr1,utf8_decode("Web (Resultado de depósitos y retiros Web)"),1);
	$pdf->Cell($w_col_apostado,$h_tr1, number_format($datos_sistema["web"]["apostado"], 2, ".", ",")  ,1,0,'R');
	$pdf->Cell($w_col_ganado,$h_tr1,   number_format($datos_sistema["web"]["ganado"], 2, ".", ",") 	  ,1,0,'R');
	$pdf->Cell($w_col_resultado,$h_tr1,number_format($datos_sistema["web"]["resultado"], 2, ".", ",") ,1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla1); 
	$pdf->Cell($w_col_tipo1,$h_tr1,utf8_decode("Golden Race (Juegos Virtuales)"),1);
	$pdf->Cell($w_col_apostado,$h_tr1, number_format($datos_sistema["goldenrace"]["apostado"], 2, ".", ",")  , 1,0,'R');
	$pdf->Cell($w_col_ganado,$h_tr1,   number_format($datos_sistema["goldenrace"]["ganado"], 2, ".", ",") 	  ,1,0,'R');
	$pdf->Cell($w_col_resultado,$h_tr1,number_format($datos_sistema["goldenrace"]["resultado"], 2, ".", ",") , 1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla1); 
	$pdf->Cell($w_col_tipo1,$h_tr,utf8_decode("Billetero Terminal"),1);
	$pdf->Cell($w_col_apostado,$h_tr,	number_format($datos_sistema["billeteroterminal"]["apostado"], 2, ".", ","), 1,0,'R');
	$pdf->Cell($w_col_ganado,$h_tr,		number_format($datos_sistema["billeteroterminal"]["ganado"], 2, ".", ","),	 1,0,'R');
	$pdf->Cell($w_col_resultado,$h_tr,	number_format($datos_sistema["billeteroterminal"]["resultado"], 2, ".", ","),1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla1); 
	$pdf->Cell($w_col_tipo1,$h_tr,utf8_decode("Cash In/Out"),1);
	$pdf->Cell($w_col_apostado,$h_tr,	number_format($datos_sistema["cashinout"]["apostado"], 2, ".", ","), 1,0,'R');
	$pdf->Cell($w_col_ganado,$h_tr,		number_format($datos_sistema["cashinout"]["ganado"], 2, ".", ","),   1,0,'R');
	$pdf->Cell($w_col_resultado,$h_tr,	number_format($datos_sistema["cashinout"]["resultado"], 2, ".", ","),1,0,'R');
	$pdf->Ln();

	// $pdf -> SetX($x_tabla1); 
	// $pdf->Cell($w_col_tipo1,$h_tr,utf8_decode("La Polla"),1);
	// $pdf->Cell($w_col_apostado,$h_tr,	number_format($datos_sistema["lapolla"]["apostado"], 2, ".", ","), 1,0,'R');
	// $pdf->Cell($w_col_ganado,$h_tr,		number_format($datos_sistema["lapolla"]["ganado"], 2, ".", ","),   1,0,'R');
	// $pdf->Cell($w_col_resultado,$h_tr,	number_format($datos_sistema["lapolla"]["resultado"], 2, ".", ","),1,0,'R');
	// $pdf->Ln();

	$pdf -> SetX($x_tabla1); 
	$pdf->Cell($w_col_tipo1,$h_tr,utf8_decode("Bingo (Sistema de Bingo)"),1);
	$pdf->Cell($w_col_apostado,$h_tr,	number_format($datos_sistema["bingo"]["apostado"], 2, ".", ",") , 1,0,'R');
	//$pdf->Cell($w_col_ganado,$h_tr,	number_format($datos_sistema["bingo"]["pagado"], 2, ".", ",") ,	  1,0,'R');
	$pdf->Cell($w_col_ganado,$h_tr,	"" /*$datos_sistema["bingo"]["ganado"]*/ ,	  1,0,'R');
	$pdf->Cell($w_col_resultado,$h_tr,	number_format($datos_sistema["bingo"]["resultado"], 2, ".", ",") ,1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla1); 
	$pdf->Cell($w_col_tipo1,$h_tr,utf8_decode("Hipica (Sistema de Hipica)"),1);
	$pdf->Cell($w_col_apostado,$h_tr,	number_format($datos_sistema["hipica"]["apostado"], 2, ".", ",") , 1,0,'R');
	//$pdf->Cell($w_col_ganado,$h_tr,	number_format($datos_sistema["hipica"]["pagado"], 2, ".", ",") ,	  1,0,'R');
	$pdf->Cell($w_col_ganado,$h_tr,	$datos_sistema["hipica"]["ganado"] , 1 , 0 , 'R' );
	$pdf->Cell($w_col_resultado,$h_tr,	number_format($datos_sistema["hipica"]["resultado"], 2, ".", ",") ,1,0,'R');
	$pdf->Ln();



	$pdf->SetFont('Arial','B',9);
	$pdf->SetTextColor(52, 73, 94);
	$pdf -> SetX($x_tabla1); 
	$pdf->Cell($w_col_tipo1,$h_tr,utf8_decode("Total"),1);
	$pdf->Cell($w_col_apostado,$h_tr,	number_format($datos_sistema["total"]["apostado"], 2, ".", ","), 1,0,'R');
	$pdf->Cell($w_col_ganado,$h_tr,		number_format($datos_sistema["total"]["ganado"], 2, ".", ","),   1,0,'R');
	$pdf->Cell($w_col_resultado,$h_tr,	number_format($datos_sistema["total"]["resultado"], 2, ".", ","),1,0,'R');
	$pdf->Ln();
	$pdf->SetTextColor(0,0,0);

	//$pdf->Ln();
	
	$pdf->SetFont('Arial','B',12);
	$pdf -> SetX($x_tabla1); 
	$pdf->Cell($w_col_tipo1,12,"RESULTADOS DE PARTICIPACIONES",0);
	$pdf->Ln();

	$x_tabla2=14;
	$w_col_tipo=80;
	$w_col_monto=40;
	$pdf->SetFont('Arial','B',9);
	$pdf->SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,"Tipo",1);
	$pdf->Cell($w_col_monto,$h_tr,"Monto",1);
	$pdf->Ln();

	$pdf->SetFont('Arial','',9);

	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Participación cliente PBET"),1);
	$pdf->Cell($w_col_monto,$h_tr, number_format($resultados_participaciones["clientepbet"]["monto"], 2, ".", ",") ,1,0,'R');
	$pdf->Ln();
	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Participación cliente SBT-Negocios"),1);
	$pdf->Cell($w_col_monto,$h_tr, number_format($resultados_participaciones["clientesbtnegocios"]["monto"],2, ".", ",") ,1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Participación Cliente GR"),1);
	$pdf->Cell($w_col_monto,$h_tr, number_format($resultados_participaciones["clientegr"]["monto"],2, ".", ",") ,1,0,'R');
	$pdf->Ln();
	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Participación Bingo"),1);
	$pdf->Cell($w_col_monto,$h_tr, number_format($resultados_participaciones["bingo"]["monto"],2, ".", ",") ,1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Participación Web"),1);
	$pdf->Cell($w_col_monto,$h_tr,number_format($resultados_participaciones["web"]["monto"],2, ".", ","),1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Hipica"),1);
	$pdf->Cell($w_col_monto,$h_tr,number_format($resultados_participaciones["hipica"]["monto"],2, ".", ","),1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Participación Total Agente"),1);
	$pdf->setFillColor(0, 151, 250);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell($w_col_monto,$h_tr,number_format($resultados_participaciones["totalagente"]["monto"],2, ".", ",") ,1,0,'R',TRUE);
	$pdf->SetFont('');
	$pdf->Ln();

	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Participación OAT PBET"),1);
	$pdf->Cell($w_col_monto,$h_tr,number_format($resultados_participaciones["oatpbet"]["monto"],2, ".", ","),1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Participación OAT SBT-Negocios"),1);
	$pdf->Cell($w_col_monto,$h_tr, number_format($resultados_participaciones["oatsbtnegocios"]["monto"],2, ".", ",") ,1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Participación OAT GR"),1);
	$pdf->Cell($w_col_monto,$h_tr, number_format($resultados_participaciones["oatgr"]["monto"],2, ".", ",") ,1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Comisión Bingo"),1);
	$pdf->Cell($w_col_monto,$h_tr, number_format($resultados_participaciones["oatbingo"]["monto"],2, ".", ",") ,1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Participación OAT Web"),1);
	$pdf->Cell($w_col_monto,$h_tr, number_format($resultados_participaciones["oatweb"]["monto"],2, ".", ",") ,1,0,'R');
	$pdf->Ln();

	$pdf -> SetX($x_tabla2);
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Pago de Bingos"),1);
	$pdf->Cell($w_col_monto,$h_tr, number_format($resultados_participaciones["pago_de_bingos"]["monto"],2, ".", ",") ,1,0,'R');
	$pdf->Ln();

	$pago_de_hipicas = 0;
	if(isset($resultados_participaciones["pago_de_hipicas"]) )
	{
		$pago_de_hipicas = $resultados_participaciones["pago_de_hipicas"]["monto"];
	}
	$pdf -> SetX($x_tabla2);
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Hipica"),1);
	$pdf->Cell($w_col_monto,$h_tr, number_format($pago_de_hipicas,2, ".", ",") ,1,0,'R');
	$pdf->Ln();

	
	$pdf -> SetX($x_tabla2); 
	$pdf->Cell($w_col_tipo,$h_tr,utf8_decode("Participación TT OAT (Monto a Depositar)"),1);
	$pdf->SetTextColor(255,255,255);
	$pdf->setFillColor(185, 27, 27);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell($w_col_monto,$h_tr, number_format($resultados_participaciones["ttoat"]["monto"],2, ".", ",") ,1,0,'R',TRUE);

	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','',9);
	$pdf -> SetX($x_tabla1);
	$pdf->setFillColor(185, 27, 27);
	$pdf->SetTextColor(255,255,255);
	$pdf->Cell(65,5,utf8_decode("Leyenda Saldo"),1,0,'C',TRUE);
	$pdf->SetTextColor(0,0,0);

	$pdf->Ln();
	$pdf -> SetX($x_tabla1);
	$pdf->SetTextColor(255,0,0);//red
	$pdf->Cell(10,5,utf8_decode("2000"),1,0,'R',FALSE);
	$pdf->Cell(55,5,utf8_decode("Depósito a  Operaciones At S.A.C."),1);
	$pdf->Ln();
	$pdf -> SetX($x_tabla1);
	$pdf->SetTextColor(28,183,135);//green
	$pdf->Cell(10,5,utf8_decode("-1000"),1,0,'R',FALSE);
	$pdf->SetTextColor(0,0,0);
	$pdf->Cell(55,5,utf8_decode("Saldo a favor del Agente AT"),1);

	return $pdf;
}


function get_datos($valores){//  api/where_liquidaciones
	global $mysqli;
	if($valores["where"]=="liquidaciones"){
		$data = [];

		$cabecera_where = " WHERE id IS NOT NULL";
		$locales_command_where=" WHERE l.id IS NOT NULL";

		$fecha_inicio = date("Y-m-d H:i:s",strtotime("-1 week"));
		$fecha_fin = date("Y-m-d H:i:s");
		$is_liq_final = true;
		$red_id = false;
		$zona_id = false;
		if(array_key_exists("filtro", $valores)){
			$filtro=$valores["filtro"];
			if(array_key_exists("red_id", $filtro)){
				if(is_array($filtro["red_id"])){			
					$red_id = $filtro["red_id"];
					sort($red_id);
				}
			}
			if(array_key_exists("zona_id", $filtro)){
				if(is_array($filtro["zona_id"])){			
					$zona_id = $filtro["zona_id"];
					sort($zona_id);
				}
			}
			if(array_key_exists("fecha_inicio", $filtro)){
				$fecha_inicio = $filtro["fecha_inicio"];
			}
			if(array_key_exists("fecha_fin", $filtro)){
				// $fecha_fin = $filtro["fecha_fin"];
				$fecha_fin = date("Y-m-d",strtotime($filtro["fecha_fin"]." +1 day"));
			}
			
			// if(array_key_exists("locales", $filtro)){
			// 	if($filtro["locales"]){
			// 		if(in_array("all", $filtro["locales"])){

			// 		}else{
			// 			$cabecera_where .= " AND local_id IN ('".implode("','", $filtro["locales"])."')";
			// 			$locales_command_where .= " AND l.id IN ('".implode("','", $filtro["locales"])."')";
			// 		}
			// 	}
			// }
			if(array_key_exists("locales", $filtro)){
				if($filtro["locales"]){
					if(in_array("all", $filtro["locales"])){
						if($login["usuario_locales"]){
							// $filtro_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
							$cabecera_where .= " AND local_id IN ('".implode("','", $login["usuario_locales"])."')";
							$locales_command_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
						}
					}else{
						// $filtro_where .= " AND l.id IN ('".implode("','", $filtro["locales"])."')";
						$cabecera_where .= " AND local_id IN ('".implode("','", $filtro["locales"])."')";
						$locales_command_where .= " AND l.id IN ('".implode("','", $filtro["locales"])."')";
					}
				}else{
					if($login["usuario_locales"]){
						$cabecera_where .= " AND local_id IN ('".implode("','", $login["usuario_locales"])."')";
						$locales_command_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
					}
				}
			}else{
				if($login["usuario_locales"]){
					$cabecera_where .= " AND local_id IN ('".implode("','", $login["usuario_locales"])."')";
					$locales_command_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
				}			
			}

			if(array_key_exists("canales_de_venta", $filtro)){
				if($filtro["canales_de_venta"]){
					$cabecera_where .= " AND canal_de_venta_id IN ('".implode("','", $filtro["canales_de_venta"])."')";
				}
			}
			if(array_key_exists("proceso_unique_id", $filtro)){
				if($filtro["proceso_unique_id"]){
					$cabecera_where .= " AND proceso_unique_id = '".$filtro["proceso_unique_id"]."' ";
					$is_liq_final=false;						
				}
			}
		}

		if($red_id){
			//print_r($red_id);
			$locales_command_where.=" AND (";
			foreach ($red_id as $red_id_key => $red_id_value) {
				if($red_id_key>0){
					$locales_command_where.=" OR ";
				}
				if($red_id_value == 0){

					$locales_command_where.= "l.red_id IS NULL OR l.red_id = '0'";
					//print_r($locales_command_where);
				}else{
					$locales_command_where.= "l.red_id = '".$red_id_value."'";
				}
			}
			$locales_command_where.=" )";
		}

		if($is_liq_final){
			$cabecera_where .= " AND estado = '1'";
		}
		$pagina = 0;
		if(array_key_exists("pagina", $valores)){
		}
		$numero = -1;
		if(array_key_exists("numero", $valores)){
		}
		$limit_offset = ($pagina * $numero);
		$data["paginacion"]=[];
		$data["paginacion"]["pagina_actual"]=intval($pagina);
		$data["paginacion"]["numero_por_pagina"]=intval($numero);
		$locales = []; //LOCALES
		$locales_command_where.= " AND l.estado = '1' ";

		if($numero>0){
			$locales_command_limit = " LIMIT ".$limit_offset.",".$numero;
		}else{
			$locales_command_limit = "";
		}

	    $locales_command_where .= " AND l.reportes_mostrar = '1'";

	    // MOOOOOSSSTTRRRAAAARRRR_RRREEEEEPPPOOORRRTTTEEEEEEEEE
	    $locales_command = "SELECT l.id,l.nombre 
	    FROM tbl_locales l
	    $locales_command_where 
	    ORDER BY l.nombre ASC ";
	    //exit();
	    $return["locales_command_where"]=$locales_command_where;
	    $locales_query = $mysqli->query($locales_command.$locales_command_limit);
	    if($mysqli->error){
	        $return["ERROR_MYSQL"]=$mysqli->error;
	        print_r($mysqli->error);
	        echo $locales_command;
	    }
	    while($lcl=$locales_query->fetch_assoc()){
	        $locales[$lcl["id"]]=$lcl;
	    }
	    $data["paginacion"]["total_items"]=$mysqli->query($locales_command)->num_rows;
	    if($numero>0){
	        $data["paginacion"]["paginas"]=ceil($data["paginacion"]["total_items"] / $data["paginacion"]["numero_por_pagina"]);
	        $data["paginacion"]["desde"]=$limit_offset+1;
	        $data["paginacion"]["hasta"]=$limit_offset+$numero;
	    }else{
	        $data["paginacion"]["paginas"]=1;
	        $data["paginacion"]["desde"]=1;
	        $data["paginacion"]["hasta"]=$data["paginacion"]["total_items"];
	    }

	    if($red_id ){
	        $local_id_arr = array_keys($locales);
	        if ($local_id_arr) $cabecera_where.=" AND local_id IN (".implode(",",$local_id_arr).")";
	    }

	    if($zona_id) $cabecera_where .= "AND zona_id IN(".implode(",", $zona_id).")";
	        // $cabecera_where .= 	($filtro["locales"] && !in_array("all", $filtro["locales"])) ?
	        // 					filter_local_zona($filtro["locales"], $zona_id, $fecha_inicio, $fecha_fin, true) :
	        // 					filter_local_zona(array_keys($locales), $zona_id, $fecha_inicio, $fecha_fin, false);
	    $cabecera_where .= " AND fecha >= '".$fecha_inicio."'";
	    $cabecera_where .= " AND fecha < '".$fecha_fin."'";

		$transacciones_cabecera = []; // CABECERAS
		$transacciones_cabecera_command = "SELECT id
		,fecha
		,local_id
		,canal_de_venta_id
		,num_tickets
		,total_apostado
		,total_ganado
		,total_ingresado
		,total_pagado
		,total_produccion
		,IFNULL(resultado_negocio, 0) resultado_negocio
		,moneda_id
		,total_depositado
		,total_anulado_retirado
		,total_depositado_web
		,total_retirado_web
		,total_caja_web
		,CONCAT(porcentaje_cliente,'%') AS porcentaje_cliente
		,total_cliente
		,CONCAT(porcentaje_freegames,'%') AS porcentaje_freegames
		,total_freegames
		,pagado_en_otra_tienda
		,pagado_de_otra_tienda
		,total_pagos_fisicos
		,retirado_de_otras_tiendas
		,caja_fisico
		,cashdesk_balance
		,(total_pagado - pagado_en_otra_tienda) AS pagados_en_su_punto_propios
		FROM tbl_transacciones_cabecera ".$cabecera_where;
		//echo $cabecera_where;exit();
		//echo $transacciones_cabecera_command; echo "\n";exit();
		$transacciones_cabecera_query = $mysqli->query($transacciones_cabecera_command);
		if (!$transacciones_cabecera_query) {
	        echo "QUERY ERROR:" . "\n" . $transacciones_cabecera_command;
	        echo "\n QUERY ERROR 2:" . "\n" . $transacciones_cabecera_command;
	        exit();
	    }
	    $return["transacciones_cabecera_command"] = $transacciones_cabecera_command;

		// $test_sum = 0;
		while ($tc=$transacciones_cabecera_query->fetch_assoc()) {
			if(array_key_exists($tc["local_id"], $locales)){
				$tc["test_in"]=0;
				$tc["test_out"]=0;
					if($tc["canal_de_venta_id"]==16){ //PBET
						$tc["test_in"] = $tc["total_apostado"] + $tc["total_depositado_web"] + $tc["pagado_en_otra_tienda"];
						$tc["test_out"] = $tc["total_pagado"] + $tc["total_retirado_web"] + $tc["pagado_de_otra_tienda"];
					}
					if($tc["canal_de_venta_id"]==17){ //SBT-Negocios
						$tc["test_in"] = $tc["pagado_en_otra_tienda"];
						$tc["test_out"] = $tc["total_pagado"];
					}
					if($tc["canal_de_venta_id"]==18){ //JV Global Bet
						// $tc["test_balance"] = $tc["total_produccion"];
					}
					if($tc["canal_de_venta_id"]==19){ //Tablet BC
						// $tc["test_balance"] = $tc["total_produccion"];
						$tc["test_out"] = $tc["total_pagado"];
					}
					if($tc["canal_de_venta_id"]==20){ //SBT-BC
						// $tc["test_balance"] = $tc["total_produccion"];
					}
					if($tc["canal_de_venta_id"]==21){ //JV Golden Race
						// $tc["test_balance"] = $tc["total_produccion"];
					}
					$tc["test_balance"]=($tc["test_in"] - $tc["test_out"]);
					$tc["test_diff"]=($tc["test_balance"] - $tc["cashdesk_balance"]);
					$transacciones_cabecera[]=$tc;
					// $test_sum = $test_sum + $tc["total_apostado"];				
				}
			}
		//echo "test_sum: ".$test_sum; echo "\n\n";
		$cdv_arr = []; // CANALES DE VENTA
		$cdv_command = "SELECT id, nombre, codigo FROM tbl_canales_venta WHERE estado = '1' ORDER BY codigo ASC";
		$cdv_query = $mysqli->query($cdv_command);
		while($cdv=$cdv_query->fetch_assoc()){
			$cdv_arr[$cdv["id"]]=$cdv;
		}
		$cdv_arr["total"]=["id"=>"total","nombre"=>"Total","codigo"=>"Total"];

		//	CONTEO DIAS
		$datetime1 = new DateTime($fecha_inicio);
		$datetime2 = new DateTime($fecha_fin);
		$difference = $datetime1->diff($datetime2);
		//	FIN CONTEO DIAS

		$totales = [];
		$totales["general"]=[];
		foreach ($transacciones_cabecera as $tc_k => $tc_v) {
			$locales[$tc_v["local_id"]]["liquidaciones"]["diario"][$tc_v["fecha"]][]=$tc_v;
		}

		foreach ($locales as $local_id => $local_data) {
			if(array_key_exists("liquidaciones", $local_data)){
				$total_rango_fecha=[];
				foreach ($local_data["liquidaciones"]["diario"] as $dia => $liq_data) {
					foreach ($liq_data as $liq_data_key => $liq_data_data) {
						if(!array_key_exists($liq_data_data["canal_de_venta_id"], $total_rango_fecha)){
							$total_rango_fecha[$liq_data_data["canal_de_venta_id"]]=[];
						}
						foreach ($liq_data_data as $key => $value) {
							if(array_key_exists($key, $total_rango_fecha[$liq_data_data["canal_de_venta_id"]])){
								if(in_array($key, ["num_tickets"
									,"total_apostado"
									,"total_ganado"
									,"total_ingresado"
									,"total_pagado"
									,"total_produccion"
									,"resultado_negocio"
									,"total_depositado"
									,"total_anulado_retirado"
									,"total_depositado_web"
									,"total_retirado_web"
									,"total_caja_web"
									,"total_cliente"
									,"total_freegames"
									,"pagado_en_otra_tienda"
									,"pagado_de_otra_tienda"
									,"total_pagos_fisicos"
									,"retirado_de_otras_tiendas"
									,"caja_fisico"
									,"cashdesk_balance"
									,"test_balance"
									,"test_diff"
									,"pagados_en_su_punto_propios"])){
									$total_rango_fecha[$liq_data_data["canal_de_venta_id"]][$key]+=$value;
							}
						}else{
							$total_rango_fecha[$liq_data_data["canal_de_venta_id"]][$key]=$value;
						}
					}
				}
			}
			$locales[$local_id]["liquidaciones"]["diario"]=false;
			$locales[$local_id]["liquidaciones"]["total_rango_fecha"]=$total_rango_fecha;
			
		}

		$stop = 0;
	}
	foreach ($locales as $local_id => $local_data) {
		if($local_id!="all"){
			$liq = $local_data;

			$liq["local_id"]=$local_id;
			$liq["local_nombre"]=$local_data["nombre"];
			$liq["dias_procesados"]=$difference->days;

			if(array_key_exists("liquidaciones", $local_data)){
				$liq["liquidaciones"]["total_rango_fecha"]["total"]=[];
				foreach ($local_data["liquidaciones"]["total_rango_fecha"] as $cdv_id => $liq_data) {
					foreach ($liq_data as $liq_data_key => $liq_data_value) {
						if(array_key_exists($liq_data_key, $liq["liquidaciones"]["total_rango_fecha"]["total"])){
							if(in_array($liq_data_key, ["num_tickets"
								,"total_apostado"
								,"total_ganado"
								,"total_ingresado"
								,"total_pagado"
								,"total_produccion"
								,"resultado_negocio"
								,"total_depositado"
								,"total_anulado_retirado"
								,"total_depositado_web"
								,"total_retirado_web"
								,"total_caja_web"
								,"total_cliente"
								,"total_freegames"
								,"pagado_en_otra_tienda"
								,"pagado_de_otra_tienda"
								,"total_pagos_fisicos"
								,"retirado_de_otras_tiendas"
								,"caja_fisico"
								,"cashdesk_balance"
								,"test_in"
								,"test_out"
								,"test_balance"
								,"test_diff"
								,"pagados_en_su_punto_propios"])){
								$liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]+=$liq_data_value;
						}
								// }
					}else{
						$liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]=$liq_data_value;
					}
				}
			}
		}else{
			$liq["liquidaciones"]["total_rango_fecha"]=[];
		}
		$data["locales"][]=$liq;
	}
	}
	if(isset($data["locales"])){
		foreach ($data["locales"] as $data_id => $data_val){
			if(array_key_exists("liquidaciones", $data_val)){
				if(array_key_exists("total_rango_fecha", $data_val["liquidaciones"])){
					if(array_key_exists("total", $data_val["liquidaciones"]["total_rango_fecha"])){
						foreach ($data_val["liquidaciones"]["total_rango_fecha"]["total"] as $t_key => $t_val) {
								//echo $t_key; echo "\n";
							if(in_array($t_key, ["num_tickets"
								,"total_apostado"
								,"total_ganado"
								,"total_ingresado"
								,"total_pagado"
								,"total_produccion"
								,"resultado_negocio"
								,"total_depositado"
								,"total_anulado_retirado"
								,"total_depositado_web"
								,"total_retirado_web"
								,"total_caja_web"
								,"total_cliente"
								,"total_freegames"
								,"pagado_en_otra_tienda"
								,"pagado_de_otra_tienda"
								,"total_pagos_fisicos"
								,"retirado_de_otras_tiendas"
								,"caja_fisico"
								,"cashdesk_balance"])){
								if(array_key_exists($t_key, $totales["general"])){
									$totales["general"][$t_key]+=$t_val;
								}else{
									$totales["general"][$t_key]=$t_val;
								}
							}
						}
					}
				}
			}
		}
	}

	$data["totales"]=$totales;
	$return["data"]=$data;
	return $return;
}
}
if(isset($_POST["opt"])){
	function numeralize($ar){
		$new_arr = array();
		foreach ($ar as $key => $value) {			
			if(is_array($value)){
				$new_arr[$key] = numeralize($value);
			}else{
				if(is_numeric($value)){
					$new_arr[$key]=number_format($value,2);
				}else{
					$new_arr[$key]=$value;
				}
			}
		}
		return $new_arr;	
	}
	if($_POST["opt"]=="cobranzas_load_locales_list"){
		$command = "
			SELECT
				d.local_id,
				d.tipo,
				dt.nombre AS tipo_nombre,
				d.descripcion AS deuda_descripcion,
				SUM(d.monto) AS monto,
				l.nombre AS local_nombre,
				l.email AS local_correo,
				(
					SELECT 
						COUNT(ms.id) 
					FROM tbl_liquidacion_mail_sent ms 
					WHERE ms.periodo_year = d.periodo_year 
					AND ms.periodo_mes = d.periodo_mes 
					AND ms.periodo_rango = d.periodo_rango 
					AND ms.local_id = d.local_id
				) AS prev_sent
			FROM tbl_deudas d
			LEFT JOIN tbl_locales l ON (l.id = d.local_id)
			LEFT JOIN tbl_deudas_tipos dt ON (dt.codigo  = d.tipo)
			WHERE d.periodo_year = '".$_POST["data"]["year"]."'
			AND d.estado = '1'
			AND d.periodo_mes = '".$_POST["data"]["mes"]."'
			AND d.periodo_rango = '".$_POST["data"]["rango"]."'
			AND d.estado = '1'
			-- AND d.local_id IN('203','88')
			GROUP BY
				d.tipo_id,
				d.local_id
			ORDER BY l.nombre, dt.prioridad ASC
			";
		$query = $mysqli->query($command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		// print_r($_POST);
		// extract($_POST);
		$locales_arr = array();
		while($l = $query->fetch_assoc()) {
			$locales_arr[$l["local_id"]]["local_nombre"]=$l["local_nombre"];
			$locales_arr[$l["local_id"]]["local_correo"]=$l["local_correo"];
			// $locales_arr[$l["local_id"]]["deudas"][$l["tipo"]]=$l["monto"];
			$locales_arr[$l["local_id"]]["deudas"][$l["tipo"]]=$l;
			$locales_arr[$l["local_id"]]["prev_sent"]=$l["prev_sent"];
		}
		foreach ($locales_arr as $local_id => $local_data) {
		// for ($local_id=0; $local_id < 0; $local_id++) {
			?>
			<h3 class="local_h3_<?php echo $local_id;?>">[<?php echo $local_id;?>] <?php echo $local_data["local_nombre"];?></h3>
			<div class="local_holder local_holder_<?php echo $local_id;?>" data-local_id="<?php echo $local_id;?>">
				<div class="col-xs-9"> <!-- DEUDAS -->
					<div class="panel panel-warning">
						<div class="panel-heading">
							<div class="panel-title">Deudas</div>
						</div>
						<div class="panel-body">
							<table class="table table-bordered">
								<thead>
									<tr>
										<!-- <th></th> -->
										<th>Tipo</th>
										<th>Total a Pagar</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$sum_deuda = 0;
									// $sum_pagado = 0;
									// $sum_diff = 0;
									foreach ($local_data["deudas"] as $deuda_key => $deuda) {
										$sum_deuda+=$deuda["monto"];
										// $sum_pagado+=$deuda["pagado"];
										// $sum_diff+=$deuda["diff"];
										?>
										<tr>
											<td><?php echo $deuda["tipo_nombre"];?> <?php if($deuda["deuda_descripcion"]){ ?>(<?php echo $deuda["deuda_descripcion"];?>)<?php } ?></td>
											<td class="text-right"><?php echo number_format($deuda["monto"],2);?></td>
										</tr>
										<?php
									}
									?>
								</tbody>
									<tr>
										
										<th>Total</th>
										<th class="text-right"><?php echo number_format($sum_deuda,2);?></th>
									</tr>
							</table>
							<button 
								class="btn btn-block btn-warning local_add_deuda_btn" 
								data-local_id="<?php echo $local_id;?>"
								>+ Deuda Manual</button>
						</div>
					</div>											
				</div>
				<div class="col-xs-3"> <!-- OPTS -->
					<!-- <button class="btn btn-block btn-success"><span class="glyphicon glyphicon-floppy-saved"></span> Guardar</button> -->
					<?php
					if($local_data["prev_sent"]){
						?>
						<button 
							class="btn btn-block btn-primary preview_btn"
							data-periodo_year="<?php echo $_POST["data"]["year"];?>"
							data-periodo_mes="<?php echo $_POST["data"]["mes"];?>"
							data-periodo_rango="<?php echo $_POST["data"]["rango"];?>"
							data-local_id="<?php echo $local_id;?>"
							><span class="glyphicon glyphicon-eye-open"></span> Previsualizar</button> 
						<button 
							class="btn btn-block btn-warning send_btn"
							data-periodo_year="<?php echo $_POST["data"]["year"];?>"
							data-periodo_mes="<?php echo $_POST["data"]["mes"];?>"
							data-periodo_rango="<?php echo $_POST["data"]["rango"];?>"
							data-local_id="<?php echo $local_id;?>"
							title="<?php echo $local_data["local_correo"];?>"><span class="glyphicon glyphicon-send"></span> RE-Enviar</button>	
						<?php
					}else{
						?>
						<button 
							class="btn btn-block btn-primary preview_btn"
							data-periodo_year="<?php echo $_POST["data"]["year"];?>"
							data-periodo_mes="<?php echo $_POST["data"]["mes"];?>"
							data-periodo_rango="<?php echo $_POST["data"]["rango"];?>"
							data-local_id="<?php echo $local_id;?>"
							><span class="glyphicon glyphicon-eye-open"></span> Previsualizar</button> 
						<button 
							class="btn btn-block btn-success send_btn"
							data-periodo_year="<?php echo $_POST["data"]["year"];?>"
							data-periodo_mes="<?php echo $_POST["data"]["mes"];?>"
							data-periodo_rango="<?php echo $_POST["data"]["rango"];?>"
							data-local_id="<?php echo $local_id;?>"
							title="<?php echo $local_data["local_correo"];?>"><span class="glyphicon glyphicon-send"></span> Enviar</button>	
						<?php
					}
					?>
									
				</div>
			</div><?php
		}
	}
	if($_POST["opt"]=="cobranzas_ver_eecc"){

		$periodo_liquidacion_search = "";
		$periodo_search = "";
		
		if (isset($_POST["data"]['tipo']) && $_POST["data"]['tipo']>0) {
			$id_periodo_inicio = htmlspecialchars($_POST["data"]['id_periodo_inicio']);
			$id_periodo_fin = htmlspecialchars($_POST["data"]['id_periodo_fin']);

			$periodo_liquidacion_search = "AND pl.id >= $id_periodo_inicio AND pl.id <= $id_periodo_fin";
			$periodo_search = "AND p.periodo_liquidacion_id >= $id_periodo_inicio AND p.periodo_liquidacion_id <= $id_periodo_fin";
		}


		$local_command = "SELECT l.id AS local_id, l.nombre FROM tbl_locales l WHERE l.id = '".$_POST["data"]["local_id"]."'";
		$local_query = $mysqli->query($local_command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		$local_data = $local_query->fetch_assoc();

		$deudas_arr = array();
		$deudas_command = "
			SELECT
				d.periodo_liquidacion_id,
				d.periodo_year,
				d.periodo_mes,
				d.periodo_rango,
				d.periodo_inicio,d.periodo_fin,
				pl.fecha_inicio,
				pl.fecha_fin,
				d.tipo_id,
				dt.nombre AS tipo_nombre,
				SUM(d.monto) AS monto,
				CAST(
						(
							SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono 
							FROM tbl_pagos p 
							WHERE p.local_id = l.id
							AND p.periodo_liquidacion_id = d.periodo_liquidacion_id
							AND p.deuda_tipo_id = d.tipo_id
							AND p.estado = 1
						)
						AS DECIMAL(20,2)
					) AS abono,
				CAST(
						(
							SUM(d.monto)
							-
							(
								SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono 
								FROM tbl_pagos p 
								WHERE p.local_id = l.id
								AND p.periodo_liquidacion_id = d.periodo_liquidacion_id
								AND p.deuda_tipo_id = d.tipo_id
								AND p.estado = 1
							)
						) AS DECIMAL(20,2)
					) AS deuda
			FROM tbl_deudas d
			LEFT JOIN tbl_locales l ON (l.id = d.local_id)
			LEFT JOIN tbl_deudas_tipos dt ON(dt.id = d.tipo_id)
			LEFT JOIN tbl_periodo_liquidacion pl on pl.id = d.periodo_liquidacion_id
			WHERE
				d.local_id = '".$local_data["local_id"]."'
				AND d.estado = '1'
				$periodo_liquidacion_search
			GROUP BY
				l.id ASC,
				d.tipo_id ASC ,
 				d.periodo_liquidacion_id DESC
			ORDER BY d.periodo_liquidacion_id DESC, d.tipo_id ASC
			";
			//echo $deudas_command;die();
		$deudas_query = $mysqli->query($deudas_command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		while($dd = $deudas_query->fetch_assoc()){
			$deudas_arr[$dd["fecha_inicio"]."  -  ".$dd["fecha_fin"]][$dd["tipo_id"]]=$dd;
		}
		$deudas_totales=array();
		$deudas_totales["total"]=array();
		$deudas_totales["total"]["monto"]=0;
		$deudas_totales["total"]["abono"]=0;
		$deudas_totales["total"]["deuda"]=0;
		foreach ($deudas_arr as $period_key => $deudas) {
			$deudas_totales[$period_key]=array();
			$deudas_totales[$period_key]["monto"]=0;
			$deudas_totales[$period_key]["abono"]=0;
			$deudas_totales[$period_key]["deuda"]=0;
			foreach ($deudas as $deuda_tipo_id => $deuda) {
				$deudas_totales[$period_key]["monto"]+=$deuda["monto"];
				$deudas_totales[$period_key]["abono"]+=$deuda["abono"];
				$deudas_totales[$period_key]["deuda"]+=$deuda["deuda"];

				$deudas_totales["total"]["monto"]+=$deuda["monto"];
				$deudas_totales["total"]["abono"]+=$deuda["abono"];
				$deudas_totales["total"]["deuda"]+=$deuda["deuda"];
			}
		}
		$deudas_totales = numeralize($deudas_totales);
		?>
		<div class="col-xs-12">Local: [<?php echo $local_data["local_id"];?>]<strong><?php echo $local_data["nombre"];?></strong></div>
		<table class="table table-bordered table-condensed table-hover">
			<thead>
				<tr>
					<th style="width:25%">Periodo</th>
					<th style="width:10%">Tipo</th>
					<th style="width:20%">Monto</th>
					<th style="width:20%">Abonado</th>
					<th style="width:15%">Deuda</th>
					<th style="width:10%">Opt</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$saldo_a_favor = 0;
				foreach ($deudas_arr as $period_key => $deudas) {
					?>
					<?php
						$command = "SELECT SUM(p.abono) AS abono, pt.nombre AS nombre FROM tbl_pagos p
									LEFT JOIN tbl_pagos_tipos pt on pt.id = p.pago_tipo_id
									WHERE p.periodo_liquidacion_id = {$deudas[key($deudas)]['periodo_liquidacion_id']}
									AND p.local_id = {$local_data['local_id']}
									AND p.deuda_tipo_id is NULL
									AND p.estado = 1";
						$periodo_saldo_a_favor = $mysqli->query($command)->fetch_assoc();
						$rowspan_periodo = 2;
						if($periodo_saldo_a_favor["abono"]!=null){
							$rowspan_periodo = 3;
						}
						$year_key="";
						$month_key="";

						/* *************************************************************
						 INICIO: SALDO A FAVOR SIN ASIGNAR
						 ***************************************************************
						*/
						$saldo_favor_fila = 0;
						$saldo_command = "
						SELECT
							p.pago_detalle_id,
							p.abono
						FROM tbl_pagos p
							INNER JOIN tbl_pagos_detalle pd ON (pd.id = p.pago_detalle_id)
						WHERE
							p.local_id = {$local_data['local_id']}
							AND p.periodo_liquidacion_id = {$deudas[key($deudas)]['periodo_liquidacion_id']}
							AND p.estado = 0
							AND p.pago_tipo_id = 5
							AND pd.estado = 1
						";
						$saldo_query = $mysqli->query($saldo_command);
						if($mysqli->error){
							print_r($mysqli->error);
							exit();
						}
						while($saldo_row = $saldo_query->fetch_assoc()){
							$pd_id = $saldo_row['pago_detalle_id'];

							$saldo_busqueda_command = "
							SELECT id
							FROM tbl_pagos
							WHERE
								local_id = {$local_data['local_id']}
								AND periodo_liquidacion_id >= {$deudas[key($deudas)]['periodo_liquidacion_id']}
								AND pago_detalle_id = $pd_id
								AND pago_tipo_id = 5
								AND estado = 1
							";
							$saldo_busqueda_query = $mysqli->query($saldo_busqueda_command);
							if($mysqli->error){
								print_r($mysqli->error);
								exit();
							}
							$saldos_num_rows = $saldo_busqueda_query->num_rows;
							if ((int) $saldos_num_rows == 0) {
								$saldo_anterior_command = "
								SELECT p.id
								FROM tbl_pagos p
									INNER JOIN tbl_pagos_detalle pd ON (pd.id = p.pago_detalle_id)
								WHERE
									p.local_id = {$local_data['local_id']}
									AND p.periodo_liquidacion_id < {$deudas[key($deudas)]['periodo_liquidacion_id']}
									AND p.pago_detalle_id = $pd_id
									AND p.estado = 0
									AND p.pago_tipo_id = 5
									AND pd.estado = 1
								LIMIT 1
								";
								$saldo_anterior_query = $mysqli->query($saldo_anterior_command);
								if($mysqli->error){
									print_r($mysqli->error);
									exit();
								}
								$saldo_anterior_num_rows = $saldo_anterior_query->num_rows;
								if ((int) $saldo_anterior_num_rows == 0) {
									$saldo_valid_command = "
									SELECT id
									FROM tbl_pagos
									WHERE
										local_id = {$local_data['local_id']}
										AND periodo_liquidacion_id > {$deudas[key($deudas)]['periodo_liquidacion_id']}
										and pago_detalle_id = $pd_id
									";
									$saldo_valid_query = $mysqli->query($saldo_valid_command);
									if($mysqli->error){
										print_r($mysqli->error);
										exit();
									}
									$saldo_valid_num_rows = $saldo_valid_query->num_rows;
									if ((int) $saldo_valid_num_rows !== 0) {
										$rowspan_periodo++;
										$saldo_favor_fila = number_format($saldo_row['abono'],2);
									}
								}
							}
						}

						$pd_command = "
						SELECT pago_detalle_id
						FROM tbl_pagos
						WHERE
							estado = 1
							AND periodo_liquidacion_id = {$deudas[key($deudas)]['periodo_liquidacion_id']}
							AND local_id = {$local_data['local_id']}
						";
						$pd_query = $mysqli->query($pd_command);
						if($mysqli->error){
							print_r($mysqli->error);
							exit();
						}
						while($pd_1 = $pd_query->fetch_assoc()){
							$pd_id = (isset($pd_1['pago_detalle_id'])) ? $pd_1['pago_detalle_id'] : 'null';
							$sf_command = "
							SELECT abono
							FROM tbl_pagos
							WHERE
								pago_detalle_id = $pd_id
								AND local_id = {$local_data['local_id']}
								AND estado = 1
								AND pago_tipo_id = 5
								AND deuda_tipo_id is NULL
								AND periodo_liquidacion_id > {$deudas[key($deudas)]['periodo_liquidacion_id']}
							";
							$sf_query = $mysqli->query($sf_command);
							if($mysqli->error){
								print_r($mysqli->error);
								exit();
							}
							while($sf_1 = $sf_query->fetch_assoc()){
								$saldo_valid2_command = "
								SELECT id
								FROM tbl_pagos
								WHERE
									pago_detalle_id = $pd_id
									AND local_id = {$local_data['local_id']}
									AND estado = 1
									AND pago_tipo_id = 1
									-- AND deuda_tipo_id is NULL
									AND periodo_liquidacion_id < {$deudas[key($deudas)]['periodo_liquidacion_id']}
									LIMIT 1
								";
								$saldo_valid2_query = $mysqli->query($saldo_valid2_command);
								if($mysqli->error){
									print_r($mysqli->error);
									exit();
								}
								$saldo_valid2_num_rows = $saldo_valid2_query->num_rows;
								if ((int) $saldo_valid2_num_rows === 0) {
									$rowspan_periodo++;
									$saldo_favor_fila = number_format($sf_1['abono'],2);
								}
							}
						}
						/* *************************************************************
						 FIN: SALDO A FAVOR SIN ASIGNAR
						 ***************************************************************
						*/

						?>
						<tr class="periodo_tr" data-periodo = "<?php echo $period_key;?>">
							<td class="rowspan_me text-center"
								rowspan="<?php echo count($deudas)+$rowspan_periodo;?>"><?php echo $period_key;?></td>
						</tr>
						<?php
						foreach ($deudas as $deuda_tipo_id => $deuda) {
							?>
							<tr data-periodo = "<?php echo $period_key;?>" class="tr_deuda tr_ tr_<?php echo $year_key;?> tr_<?php echo $year_key;?>_collapse_me tr_<?php echo $year_key;?>_<?php echo $month_key;?> tr_<?php echo $year_key;?>_<?php echo $month_key;?>_collapse_me tr_<?php echo $year_key;?>_<?php echo $month_key;?>_<?php echo $period_key;?> tr_<?php echo $year_key;?>_<?php echo $month_key;?>_<?php echo $period_key;?>_collapse_me">
								<td><?php echo $deuda["tipo_nombre"];?></td>
								<td valign="top">
									<button
										class="btn btn-xs btn-default cobranzas_eecc_view_detalle_btn"
										data-what="deuda"
										data-periodo_liquidacion_id="<?php echo $deuda['periodo_liquidacion_id'];?>"
										data-periodo_year="<?php echo $year_key;?>"
										data-periodo_mes="<?php echo $month_key;?>"
										data-periodo_rango="<?php echo $period_key;?>"
										data-local_id="<?php echo $local_data["local_id"];?>"
										data-deuda_tipo_id="<?php echo $deuda_tipo_id;?>"
										>
											<span class="glyphicon glyphicon-eye-open"></span>
											<label>Ver Más</label>
									</button>
									<span class="pull-right view_detalle_span"><?php echo number_format($deuda["monto"],2);?></span>
									<table class="table table-bordered table-condensed hidden view_detalle_table">
										<thead>
											<tr>
												<th>Total</th>
												<th class="text-right"><?php echo number_format($deuda["monto"],2);?></th>
											</tr>
										</thead>
										<tbody>
										</tbody>
									</table>
								</td>
								<td valign="top">
									<button
										class="btn btn-xs btn-default cobranzas_eecc_view_detalle_btn"
										data-what="pagos"
										data-periodo_liquidacion_id="<?php echo $deuda['periodo_liquidacion_id'];?>"
										data-periodo_year="<?php echo $year_key;?>"
										data-periodo_mes="<?php echo $month_key;?>"
										data-periodo_rango="<?php echo $period_key;?>"
										data-local_id="<?php echo $local_data["local_id"];?>"
										data-deuda_tipo_id="<?php echo $deuda_tipo_id;?>"
										><span class="glyphicon glyphicon-eye-open"></span> <label>Ver Más</label></button>
									<span class="pull-right view_detalle_span"><?php echo number_format($deuda["abono"],2);?></span>
									<table class="table table-bordered table-condensed hidden view_detalle_table top">
										<thead>
											<tr>
												<th>Total</th>
												<th class="text-right"><?php echo number_format($deuda["abono"],2);?></th>
											</tr>
										</thead>
										<tbody>
										</tbody>
									</table>
								</td>
								<td class="text-right"><?php echo number_format($deuda["deuda"],2);?></td>
								<td>
									<button
										class="btn btn-xs btn-primary eecc_add_pago_btn hidden"
										data-periodo_year="<?php echo $year_key;?>"
										data-periodo_mes="<?php echo $month_key;?>"
										data-periodo_rango="<?php echo $period_key;?>"
										data-local_id="<?php echo $local_data["local_id"];?>"
										data-deuda_tipo_id="<?php echo $deuda_tipo_id;?>"
										><span class="glyphicon glyphicon-piggy-bank"></span></button>
								</td>
							</tr>
							<?php
						}
						?>
						<tr data-periodo = "<?php echo $period_key;?>" class="tr_deuda tr_ tr_<?php echo $year_key;?> tr_<?php echo $year_key;?>_collapse_me tr_<?php echo $year_key;?>_<?php echo $month_key;?> tr_<?php echo $year_key;?>_<?php echo $month_key;?>_collapse_me tr_<?php echo $year_key;?>_<?php echo $month_key;?>_<?php echo $period_key;?> cobranzas_bold">
							<td>Total</td>
							<td class="text-right"><?php echo $deudas_totales[$period_key]["monto"];?></td>
							<td class="text-right"><?php echo $deudas_totales[$period_key]["abono"];?></td>
							<td class="text-right"><?php echo $deudas_totales[$period_key]["deuda"];?></td>
							<td><button class="btn btn-xs btn-default expand_collapse_btn hidden" data-collapse="<?php echo $year_key;?>_<?php echo $month_key;?>_<?php echo $period_key;?>">-</button>
								<?php if(array_key_exists(65,$usuario_permisos) && in_array("eliminar_pago", $usuario_permisos[65])) { ?>
								<button
								class="btn btn-xs btn-primary eecc_listar_pagos_periodo_btn w-50 "
								data-periodo_liquidacion_id="<?php echo $deuda['periodo_liquidacion_id'];?>"
								data-periodo_rango="<?php echo $period_key;?>"
								data-local_id="<?php echo $local_data["local_id"];?>"
								><span class="glyphicon glyphicon-search"></span>&nbsp;Pagos</button>
								<?php }?>
							</td>
						</tr>
						<?php
						if($periodo_saldo_a_favor["abono"] != null){
							$saldo_a_favor += $periodo_saldo_a_favor["abono"];
						?>
						<tr>
							<td><strong><?php echo $periodo_saldo_a_favor["nombre"];?></strong></td>
							<td class="text-right"></td>
							<td class="text-right"><strong><?php echo $periodo_saldo_a_favor["abono"];?></strong></td>
							<td class="text-right"></td>
							<td></td>
						</tr>
						<?php }
						if ((int)$saldo_favor_fila !== 0) {
							?>
							<tr data-periodo = "<?php echo $period_key;?>" class="tr_deuda">
								<td><strong>Saldo a Favor sin asignar</strong></td>
								<td></td>
								<td class="text-right"><strong><?php echo $saldo_favor_fila?></strong></td>
								<td></td>
								<td></td>
							</tr>
							<?php
						}
					?>
					<?php
				}
				$total= filter_var($deudas_totales["total"]["abono"], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);

				$abono_total = 0;
				$abono_restante_command = "
				SELECT sum(pd.monto) as abono_total
				FROM tbl_pagos_detalle pd
				WHERE
					pd.id in (
								SELECT distinct p.pago_detalle_id
								FROM tbl_pagos p
								WHERE
									p.local_id = '".$local_data["local_id"]."'
									AND p.estado = 1
									$periodo_search
							)
					-- and pd.estado = 1
				";
				$abono_restante_query = $mysqli->query($abono_restante_command);
				if($mysqli->error){
					print_r($mysqli->error);
					exit();
				}
				while($ar = $abono_restante_query->fetch_assoc()){
					$abono_total = $ar['abono_total'];
				}
				if ($abono_total > ($total+($saldo_a_favor?:0))) {
					$abono_restante = $abono_total - ($total+($saldo_a_favor?:0));
					?>
					<tr>
						<td colspan="2"><strong>Saldo a Favor sin asignar Total</strong></td>
						<td></td>
						<td class="text-right"><strong><?php echo number_format($abono_restante,2)?></strong></td>
						<td></td>
						<td></td>
					</tr>
					<?php
				}

				$deuda_total= filter_var($deudas_totales["total"]["deuda"], FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
				?>
				<tr class="tr_ tr_<?php echo $year_key;?> cobranzas_bold">
					<td colspan="2">Total</td>
					<td class="text-right"><?php echo $deudas_totales["total"]["monto"];?></td>
					<td class="text-right"><?php echo number_format($total+($saldo_a_favor?:0)+$abono_restante,2);?></td>
					<td class="text-right"><?php echo number_format(($deuda_total - (($saldo_a_favor?:0) + $abono_restante)),2);?></td>
					<td><button class="btn btn-xs btn-default expand_collapse_btn hidden" data-collapse="<?php echo $year_key;?>">-</button></td>
				</tr>
					<?php
				?>
			</tbody>
		</table>
		<?php
	}
	if($_POST["opt"]=="cobranzas_eecc_view_detalle"){
		$data = $_POST["data"];
		// $what = $_POST["data"]["what"];
		$detalle_command = false;
		if($data["what"]=="deuda"){
			if($data["deuda_tipo_id"] == '5'){
				$detalle_command = "
					SELECT 
						d.monto AS monto,
						(SELECT cdv.codigo FROM tbl_canales_venta cdv WHERE cdv.id = d.canal_de_venta_id) AS cdv_codigo,
						d.descripcion
					FROM tbl_deudas d
					WHERE 
					 d.periodo_liquidacion_id = '".$data["periodo_liquidacion_id"]."'
					AND d.local_id = '".$data["local_id"]."'
					AND d.tipo_id = '".$data["deuda_tipo_id"]."'
					AND d.estado = '1'
					";
			}else{
				$detalle_command = "
					SELECT 
						sum(d.monto) AS monto,
						(SELECT cdv.codigo FROM tbl_canales_venta cdv WHERE cdv.id = d.canal_de_venta_id) AS cdv_codigo,
						d.descripcion
					FROM tbl_deudas d
					WHERE 
					 d.periodo_liquidacion_id = '".$data["periodo_liquidacion_id"]."'
					AND d.local_id = '".$data["local_id"]."'
					AND d.tipo_id = '".$data["deuda_tipo_id"]."'
					AND d.estado = '1'
					group by cdv_codigo
					";
			}
			//	echo $detalle_command;die();
			$detalle_query = $mysqli->query($detalle_command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
			while ($d = $detalle_query->fetch_assoc()) {
				?>
				<tr>
					<th><?php echo $d["cdv_codigo"];?> <?php echo $d["descripcion"];?></th>
					<td class="text-right"><?php echo number_format($d["monto"],2);?></td>
				</tr>
				<?php
			}
		}elseif( $data["what"] == "pagos" ){
			$detalle_command = "
				SELECT 
					p.id,
					p.pago_tipo_id,
					SUM(p.abono) AS abono,
					(SELECT pt.nombre FROM tbl_pagos_tipos pt WHERE pt.id = p.pago_tipo_id) AS pago_tipo
				FROM tbl_pagos p
				WHERE p.periodo_liquidacion_id= '".$data["periodo_liquidacion_id"]."'
				AND p.local_id = '".$data["local_id"]."'
				AND p.deuda_tipo_id = '".$data["deuda_tipo_id"]."'
				AND p.estado = 1
				GROUP BY p.id
				ORDER BY p.fecha_ingreso DESC
				";
			$detalle_query = $mysqli->query($detalle_command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
			while ($p = $detalle_query->fetch_assoc()) {
				?>
				<tr data-pago_id=<?php echo $p["id"];?> data-pago_tipo_id=<?php echo $p["pago_tipo_id"]?> >
					<th class="pagos_detalle_td" style="cursor:pointer"><span class="glyphicon glyphicon-eye-open"></span><label>&nbsp;<?php echo $p["pago_tipo"];?></label></th>
					<td class="text-right pagos_detalle_td" style="cursor:pointer"><?php echo number_format($p["abono"],2);?></td>
				</tr>
				<?php
			}
		}
	}
	if ($_POST["opt"]=="detalle_cobranzas_eecc_view") {
		$local_command = "SELECT l.id AS local_id, l.nombre FROM tbl_locales l WHERE l.id = '".$_POST["data"]["local_id"]."'";
		$local_query = $mysqli->query($local_command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		$local_data = $local_query->fetch_assoc();
	}
	if( $_POST["opt"] == "pago_detalle_view" ){
		$data = $_POST["data"];
		$detalle_command = "
			SELECT 
			 p.pago_tipo_id
			,p.fecha_ingreso
			,pd.nro_operacion
			,pd.monto AS monto2
			,p.abono AS monto
			,pd.descripcion
			,pd.voucher
			,b.nombre AS 'banco_nombre'
			,pt.nombre AS 'pago_tipo_nombre'
			FROM tbl_pagos p
			LEFT JOIN tbl_pagos_detalle pd ON pd.id = p.pago_detalle_id
			LEFT JOIN tbl_bancos b ON b.id = pd.banco_id
			LEFT JOIN tbl_pagos_tipos pt ON pt.id = p.pago_tipo_id
			WHERE p.id = ".$data["pago_id"];
		$detalle_query = $mysqli->query($detalle_command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		while ($d = $detalle_query->fetch_assoc()) {
			?>
				<div class="row">
					<label class="col-xs-3 control-label">Abono</label>
					<div class="col-xs-9">
						<div class="input-group">
						  <?php echo $d["monto"];?>
		                </div>
					</div>
				</div>

				<div class="row">
					<label class="col-xs-3 control-label" for="">Fecha Ingreso</label>
					<div class="col-xs-9">
						<div class="input-group">
						  <?php echo $d["fecha_ingreso"];?>
		                </div>
					</div>
				</div>
				
				<?php if( $d["pago_tipo_id"] == 1 ){ ?>
					<br>
					<div class="row">
						<label class="col-xs-3 control-label">Tipo</label>
						<div class="col-xs-9">
							<div class="input-group">
							<?php echo $d["pago_tipo_nombre"]; ?>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-xs-3 control-label">Banco</label>
						<div class="col-xs-9">
							<div class="input-group">
							  <?php echo $d["banco_nombre"];?>
			                </div>
						</div>
					</div>
					<div class="row">
						<label class="col-xs-3 control-label">Nro de Operación</label>
						<div class="col-xs-9">
							<div class="input-group">
							<?php echo $d["nro_operacion"] ? :"---"; ?>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-xs-3 control-label">Monto</label>
						<div class="col-xs-9">
							<div class="input-group"><?php echo $d["monto2"];?></div>
						</div>
					</div>
					<div class="row">
						<label class="col-xs-3 control-label" for="">Voucher</label>
						<div class="col-xs-9">
							<div class="input-group">
								<img id="pagodetalle<?php echo $data["pago_id"];?>" width="500" height="" src="/files_bucket/pagos_voucher/<?php echo $d["voucher"];?>">
			                </div>
						</div>
					</div>
					<br>
					<div class="row">
						<label class="col-xs-3 control-label" for="">Descripción</label>
						<div class="col-xs-9">
							<div class="input-group">
							  <?php echo $d["descripcion"];?>
			                </div>
						</div>
					</div>

				<?php }
				else if( $d["pago_tipo_id"] == 5 ){//saldo a favor
					?>
				<?php
				}
				else{
					?>
					<br>
					<div class="row">
						<label class="col-xs-3 control-label">Tipo</label>
						<div class="col-xs-9">
							<div class="input-group">
							<?php echo $d["pago_tipo_nombre"]; ?>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-xs-3 control-label">Nro de Operación</label>
						<div class="col-xs-9">
							<div class="input-group">
							<?php echo $d["nro_operacion"] ? :"---" ; ?>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-xs-3 control-label" for="">Monto</label>
						<div class="col-xs-9">
							<div class="input-group"><?php echo $d["monto2"];?></div>
						</div>
					</div>
					<div class="row">
						<label class="col-xs-3 control-label" for="">Voucher</label>
						<div class="col-xs-9">
							<div class="input-group">
								<img id="pagodetalle<?php echo $data["pago_id"];?>" width="500" height="" src="/files_bucket/pagos_voucher/<?php echo $d["voucher"];?>">
			                </div>
						</div>
					</div>	
					<br>
					<div class="row">
						<label class="col-xs-3 control-label" for="">Descripción</label>
						<div class="col-xs-9">
							<div class="input-group">
							  <?php echo $d["descripcion"];?>
			                </div>
						</div>
					</div>
				<?php }?>
			<?php
		}
	}
	if($_POST["opt"]=="cobranzas_ver_eecc_TABLES"){
		$local_command = "SELECT l.id AS local_id, l.nombre FROM tbl_locales l WHERE l.id = '".$_POST["data"]["local_id"]."'";
		$local_query = $mysqli->query($local_command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		$local_data = $local_query->fetch_assoc();

		$deudas_arr = array();
		$deudas_command = "
			SELECT
				d.periodo_year,
				d.periodo_mes,
				d.periodo_rango,
				d.tipo_id,
				dt.nombre AS tipo_nombre,
				SUM(d.monto) AS monto,
				CAST(
						(
							SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono 
							FROM tbl_pagos p 
							WHERE p.local_id = l.id
							AND p.periodo_year = d.periodo_year
							AND p.periodo_mes = d.periodo_mes
							AND p.periodo_rango = d.periodo_rango
							AND p.deuda_tipo_id = d.tipo_id
						)
						AS DECIMAL(20,2)
					) AS abono,
				CAST(
						(
							SUM(d.monto)
							-
							(
								SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono 
								FROM tbl_pagos p 
								WHERE p.local_id = l.id
								AND p.periodo_year = d.periodo_year
								AND p.periodo_mes = d.periodo_mes
								AND p.periodo_rango = d.periodo_rango
								AND p.deuda_tipo_id = d.tipo_id
							)
						) AS DECIMAL(20,2)
					) AS deuda
			FROM tbl_deudas d
			LEFT JOIN tbl_locales l ON (l.id = d.local_id)
			LEFT JOIN tbl_deudas_tipos dt ON(dt.id = d.tipo_id)
			WHERE d.local_id = '".$local_data["local_id"]."'
			AND d.estado = '1'
			-- AND d.periodo_inicio >= '2017-11-01'
			-- AND d.periodo_fin < '2017-11-21'
			GROUP BY
				l.id ASC,
				d.periodo_year DESC,
				d.periodo_mes DESC,
				d.periodo_rango ASC,
				d.tipo_id
			";
		$deudas_query = $mysqli->query($deudas_command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		while($dd = $deudas_query->fetch_assoc()){
			$deudas_arr[$dd["periodo_year"]][$dd["periodo_mes"]][$dd["periodo_rango"]][$dd["tipo_id"]]=$dd;
		}
		?>
		<div class="col-xs-12">Local: [<?php echo $local_data["local_id"];?>]<strong><?php echo $local_data["nombre"];?></strong></div>
		<table class="table table-bordered table-condensed">
			<thead>
				<tr>
					<th>Año</th>
					<th>Mes</th>
					<th>Periodo</th>
					<th>Tipo</th>
					<th>Monto</th>
					<th>Abonado</th>
					<th>Deuda</th>
					<th>Opt</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($deudas_arr as $year_key => $months) {
					$total[$year_key]=array();
					$total[$year_key]["monto"]=0;
					$total[$year_key]["abono"]=0;
					$total[$year_key]["deuda"]=0;

					?>
					<tr>
						<td rowspan="<?php
						$year_rowspan = 2;
						foreach ($months as $month_key => $periods) {
							foreach ($periods as $period_key => $deudas) {
								foreach ($deudas as $deuda_key => $deuda) {
									$year_rowspan++;
								}
								$year_rowspan++;
								$year_rowspan++;
							}
							$year_rowspan++;
							$year_rowspan++;
						}
						echo $year_rowspan;
						?>"><?php echo $year_key;?></td>
					</tr>
					<?php
					foreach ($months as $month_key => $periods) {
						$total[$month_key]["monto"]=0;
						$total[$month_key]["abono"]=0;
						$total[$month_key]["deuda"]=0;
						?>
						<tr>
							<td rowspan="<?php
							$month_rowspan = 2;
							// foreach ($months as $month_key => $periods) {
								foreach ($periods as $period_key => $deudas) {
									foreach ($deudas as $deuda_key => $deuda) {
										$month_rowspan++;
									}
									$month_rowspan++;
									$month_rowspan++;
								}
							echo $month_rowspan;
								// echo 6;
							?>"><?php echo $month_key;?></td>
						</tr>
						<?php
						foreach ($periods as $period_key => $deudas) {
							$total[$period_key]["monto"]=0;
							$total[$period_key]["abono"]=0;
							$total[$period_key]["deuda"]=0;
							?>
							<tr>
								<td rowspan="<?php echo count($deudas)+2;?>"><?php echo $period_key;?></td>
							</tr>
							<?php
							foreach ($deudas as $deuda_tipo_id => $deuda) {
								?>
								<tr>
									<td><?php echo $deuda["tipo_nombre"];?></td>
									<td valign="top">
										<button 
											class="btn btn-xs btn-default cobranzas_eecc_view_detalle_btn"
											data-what="deuda"
											data-periodo_year="<?php echo $year_key;?>"
											data-periodo_mes="<?php echo $month_key;?>"
											data-periodo_rango="<?php echo $period_key;?>"
											data-local_id="<?php echo $local_data["local_id"];?>"
											data-deuda_tipo_id="<?php echo $deuda_tipo_id;?>"
											>
												<span class="glyphicon glyphicon-eye-open"></span>
												<label>Ver Más</label>
										</button>
										<!-- <button type="button" class="btn btn-default btn-min-width" data-toggle="tooltip" data-placement="left" title="" data-original-title="Tooltip on left">Left</button> -->
										<span class="pull-right view_detalle_span"><?php echo number_format($deuda["monto"],2);?></span>
										<table class="table table-bordered table-condensed hidden view_detalle_table">
											<thead>
												<tr>
													<th>Total</th>
													<th class="text-right"><?php echo number_format($deuda["monto"],2);?></th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
									</td>
									<td valign="top">
										<button 
											class="btn btn-xs btn-default cobranzas_eecc_view_detalle_btn"
											data-what="pagos"
											data-periodo_year="<?php echo $year_key;?>"
											data-periodo_mes="<?php echo $month_key;?>"
											data-periodo_rango="<?php echo $period_key;?>"
											data-local_id="<?php echo $local_data["local_id"];?>"
											data-deuda_tipo_id="<?php echo $deuda_tipo_id;?>"
											><span class="glyphicon glyphicon-eye-open"></span> <label>Ver Más</label></button>
										<span class="pull-right view_detalle_span"><?php echo number_format($deuda["abono"],2);?></span>
										<table class="table table-bordered table-condensed hidden view_detalle_table top">
											<thead>
												<tr>
													<th>Total</th>
													<th class="text-right"><?php echo number_format($deuda["abono"],2);?></th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>					
									</td>
									<td class="text-right"><?php echo number_format($deuda["deuda"],2);?></td>
									<td>
										<button 
											class="btn btn-xs btn-primary eecc_add_pago_btn"
											data-periodo_year="<?php echo $year_key;?>"
											data-periodo_mes="<?php echo $month_key;?>"
											data-periodo_rango="<?php echo $period_key;?>"
											data-local_id="<?php echo $local_data["local_id"];?>"
											data-deuda_tipo_id="<?php echo $deuda_tipo_id;?>"
											><span class="glyphicon glyphicon-piggy-bank"></span></button>
									</td>
								</tr>
								<?php
								$total[$period_key]["monto"]+=$deuda["monto"];
								$total[$period_key]["abono"]+=$deuda["abono"];
								$total[$period_key]["deuda"]+=$deuda["deuda"];

								$total[$month_key]["monto"]+=$deuda["monto"];
								$total[$month_key]["abono"]+=$deuda["abono"];
								$total[$month_key]["deuda"]+=$deuda["deuda"];

								$total[$year_key]["monto"]+=$deuda["monto"];
								$total[$year_key]["abono"]+=$deuda["abono"];
								$total[$year_key]["deuda"]+=$deuda["deuda"];
							}
							?>
							<tr class="cobranzas_bold">
								<td>Total</td>
								<td class="text-right"><?php echo number_format($total[$period_key]["monto"],2);?></td>
								<td class="text-right"><?php echo number_format($total[$period_key]["abono"],2);?></td>
								<td class="text-right"><?php echo number_format($total[$period_key]["deuda"],2);?></td>
								<td><button class="btn btn-xs btn-default">+</button></td>
							</tr>
							<?php
						}
						?>
						<tr class="cobranzas_bold">
							<td colspan="2">Total</td>
							<td class="text-right"><?php echo number_format($total[$month_key]["monto"],2);?></td>
							<td class="text-right"><?php echo number_format($total[$month_key]["abono"],2);?></td>
							<td class="text-right"><?php echo number_format($total[$month_key]["deuda"],2);?></td>
							<td><button class="btn btn-xs btn-default">+</button></td>
						</tr>
						<?php
					}
					?>
					<tr class="cobranzas_bold">
						<td colspan="3">Total</td>
						<td class="text-right"><?php echo number_format($total[$year_key]["monto"],2);?></td>
						<td class="text-right"><?php echo number_format($total[$year_key]["abono"],2);?></td>
						<td class="text-right"><?php echo number_format($total[$year_key]["deuda"],2);?></td>
						<td><button class="btn btn-xs btn-default">+</button></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
	}
	if($_POST["opt"]=="cobranzas_ver_eecc_TABLES_OTRO"){
		$local_command = "SELECT l.id AS local_id, l.nombre FROM tbl_locales l WHERE l.id = '".$_POST["data"]["local_id"]."'";
		$local_query = $mysqli->query($local_command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		$local_data = $local_query->fetch_assoc();

		$deudas_arr = array();
		$deudas_command = "
			SELECT
				d.periodo_year,
				d.periodo_mes,
				d.periodo_rango,
				d.tipo_id,
				dt.nombre AS tipo_nombre,
				SUM(d.monto) AS monto,
				CAST(
						(
							SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono 
							FROM tbl_pagos p 
							WHERE p.local_id = l.id
							AND p.periodo_year = d.periodo_year
							AND p.periodo_mes = d.periodo_mes
							AND p.periodo_rango = d.periodo_rango
							AND p.deuda_tipo_id = d.tipo_id
						)
						AS DECIMAL(20,2)
					) AS abono,
				CAST(
						(
							SUM(d.monto)
							-
							(
								SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono 
								FROM tbl_pagos p 
								WHERE p.local_id = l.id
								AND p.periodo_year = d.periodo_year
								AND p.periodo_mes = d.periodo_mes
								AND p.periodo_rango = d.periodo_rango
								AND p.deuda_tipo_id = d.tipo_id
							)
						) AS DECIMAL(20,2)
					) AS deuda
			FROM tbl_deudas d
			LEFT JOIN tbl_locales l ON (l.id = d.local_id)
			LEFT JOIN tbl_deudas_tipos dt ON(dt.id = d.tipo_id)
			WHERE d.local_id = '".$local_data["local_id"]."'
			AND d.estado = '1'
			-- AND d.periodo_inicio >= '2017-11-01'
			-- AND d.periodo_fin < '2017-11-21'
			GROUP BY
				l.id ASC,
				d.periodo_year DESC,
				d.periodo_mes DESC,
				d.periodo_rango ASC,
				d.tipo_id
			";
		$deudas_query = $mysqli->query($deudas_command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		while($dd = $deudas_query->fetch_assoc()){
			$deudas_arr[$dd["periodo_year"]][$dd["periodo_mes"]][$dd["periodo_rango"]][$dd["tipo_id"]]=$dd;
		}
		$deudas_totales=array();
			foreach ($deudas_arr as $year_key => $months) {
				$deudas_totales[$year_key]=array();
				$deudas_totales[$year_key]["monto"]=0;
				$deudas_totales[$year_key]["abono"]=0;
				$deudas_totales[$year_key]["deuda"]=0;
				foreach ($months as $month_key => $periods) {
					$deudas_totales[$month_key]["monto"]=0;
					$deudas_totales[$month_key]["abono"]=0;
					$deudas_totales[$month_key]["deuda"]=0;
					foreach ($periods as $period_key => $deudas) {
						$deudas_totales[$period_key]["monto"]=0;
						$deudas_totales[$period_key]["abono"]=0;
						$deudas_totales[$period_key]["deuda"]=0;
						foreach ($deudas as $deuda_tipo_id => $deuda) {
							$deudas_totales[$period_key]["monto"]+=$deuda["monto"];
							$deudas_totales[$period_key]["abono"]+=$deuda["abono"];
							$deudas_totales[$period_key]["deuda"]+=$deuda["deuda"];

							$deudas_totales[$month_key]["monto"]+=$deuda["monto"];
							$deudas_totales[$month_key]["abono"]+=$deuda["abono"];
							$deudas_totales[$month_key]["deuda"]+=$deuda["deuda"];

							$deudas_totales[$year_key]["monto"]+=$deuda["monto"];
							$deudas_totales[$year_key]["abono"]+=$deuda["abono"];
							$deudas_totales[$year_key]["deuda"]+=$deuda["deuda"];
						}
					}
				}
			}
		$deudas_totales = numeralize($deudas_totales);
		?>
		<div class="col-xs-12">Local: [<?php echo $local_data["local_id"];?>]<strong><?php echo $local_data["nombre"];?></strong></div>
		<div class="eecc_holder hidden">
			<div class="cabecera">
				<div class="w-100px"><span>Año</span></div>
				<div class="w-100px"><span>Mes</span></div>
				<div class="w-100px"><span>Periodo</span></div>
				<div class="w-100px"><span>Tipo</span></div>
				<div class="w-100px"><span>Monto</span></div>
				<div class="w-100px"><span>Abonado</span></div>
				<div class="w-100px"><span>Deuda</span></div>
				<div class="w-100px"><span>Opt</span></div>
			</div>
			<?php
			foreach ($deudas_arr as $year_key => $months) {
				?>
				<div class="year_holder">
					<div class="w-100px"><span><?php echo $year_key;?></span></div>
					<div class="w-600px">
						<div class="w-300px"><span>Total</span></div>
						<div class="w-100px"><span><?php echo number_format($deudas_totales[$year_key]["monto"],2);?></span></div>
						<div class="w-100px"><span><?php echo number_format($deudas_totales[$year_key]["abono"],2);?></span></div>
						<div class="w-100px"><span><?php echo number_format($deudas_totales[$year_key]["deuda"],2);?></span></div>						
					</div>
					<div class="w-100px"><button class="btn btn-xs btn-default">+</button></div>
				</div>
				<?php
			}
			?>
		</div>

		<table class="table table-bordered table-condensed">
			<thead>
				<tr>
					<th>Año</th>
					<th>Mes</th>
					<th>Periodo</th>
					<th>Tipo</th>
					<th>Monto</th>
					<th>Abonado</th>
					<th>Deuda</th>
					<th>Opt</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($deudas_arr as $year_key => $months) {
					?>
					<tr>
						<td rowspan="<?php
						$year_rowspan = 2;
						foreach ($months as $month_key => $periods) {
							foreach ($periods as $period_key => $deudas) {
								foreach ($deudas as $deuda_key => $deuda) {
									$year_rowspan++;
								}
								$year_rowspan++;
								$year_rowspan++;
							}
							$year_rowspan++;
							$year_rowspan++;
						}
						echo $year_rowspan;
						?>"><?php echo $year_key;?></td>
					</tr>
					<?php
					foreach ($months as $month_key => $periods) {
						?>
						<tr>
							<td rowspan="<?php
							$month_rowspan = 2;
								foreach ($periods as $period_key => $deudas) {
									foreach ($deudas as $deuda_key => $deuda) {
										$month_rowspan++;
									}
									$month_rowspan++;
									$month_rowspan++;
								}
							echo $month_rowspan;
							?>"><?php echo $month_key;?></td>
						</tr>
						<?php
						foreach ($periods as $period_key => $deudas) {
							?>
							<tr>
								<td rowspan="<?php echo count($deudas)+2;?>"><?php echo $period_key;?></td>
							</tr>
							<?php
							foreach ($deudas as $deuda_tipo_id => $deuda) {
								?>
								<tr>
									<td><?php echo $deuda["tipo_nombre"];?></td>
									<td valign="top">
										<button 
											class="btn btn-xs btn-default cobranzas_eecc_view_detalle_btn"
											data-what="deuda"
											data-periodo_year="<?php echo $year_key;?>"
											data-periodo_mes="<?php echo $month_key;?>"
											data-periodo_rango="<?php echo $period_key;?>"
											data-local_id="<?php echo $local_data["local_id"];?>"
											data-deuda_tipo_id="<?php echo $deuda_tipo_id;?>"
											>
												<span class="glyphicon glyphicon-eye-open"></span>
												<label>Ver Más</label>
										</button>
										<span class="pull-right view_detalle_span"><?php echo number_format($deuda["monto"],2);?></span>
										<table class="table table-bordered table-condensed hidden view_detalle_table">
											<thead>
												<tr>
													<th>Total</th>
													<th class="text-right"><?php echo number_format($deuda["monto"],2);?></th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
									</td>
									<td valign="top">
										<button 
											class="btn btn-xs btn-default cobranzas_eecc_view_detalle_btn"
											data-what="pagos"
											data-periodo_year="<?php echo $year_key;?>"
											data-periodo_mes="<?php echo $month_key;?>"
											data-periodo_rango="<?php echo $period_key;?>"
											data-local_id="<?php echo $local_data["local_id"];?>"
											data-deuda_tipo_id="<?php echo $deuda_tipo_id;?>"
											><span class="glyphicon glyphicon-eye-open"></span> <label>Ver Más</label></button>
										<span class="pull-right view_detalle_span"><?php echo number_format($deuda["abono"],2);?></span>
										<table class="table table-bordered table-condensed hidden view_detalle_table top">
											<thead>
												<tr>
													<th>Total</th>
													<th class="text-right"><?php echo number_format($deuda["abono"],2);?></th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>					
									</td>
									<td class="text-right"><?php echo number_format($deuda["deuda"],2);?></td>
									<td>
										<button 
											class="btn btn-xs btn-primary eecc_add_pago_btn"
											data-periodo_year="<?php echo $year_key;?>"
											data-periodo_mes="<?php echo $month_key;?>"
											data-periodo_rango="<?php echo $period_key;?>"
											data-local_id="<?php echo $local_data["local_id"];?>"
											data-deuda_tipo_id="<?php echo $deuda_tipo_id;?>"
											><span class="glyphicon glyphicon-piggy-bank"></span></button>
									</td>
								</tr>
								<?php
							}
							?>
							<tr class="cobranzas_bold">
								<td>Total</td>
								<td class="text-right"><?php echo $deudas_totales[$period_key]["monto"];?></td>
								<td class="text-right"><?php echo $deudas_totales[$period_key]["abono"];?></td>
								<td class="text-right"><?php echo $deudas_totales[$period_key]["deuda"];?></td>
								<td><button class="btn btn-xs btn-default">+</button></td>
							</tr>
							<?php
						}
						?>
						<tr class="cobranzas_bold">
							<td colspan="2">Total</td>
							<td class="text-right"><?php echo $deudas_totales[$month_key]["monto"];?></td>
							<td class="text-right"><?php echo $deudas_totales[$month_key]["abono"];?></td>
							<td class="text-right"><?php echo $deudas_totales[$month_key]["deuda"];?></td>
							<td><button class="btn btn-xs btn-default">+</button></td>
						</tr>
						<?php
					}
					?>
					<tr class="cobranzas_bold">
						<td colspan="3">Total</td>
						<td class="text-right"><?php echo $deudas_totales[$year_key]["monto"];?></td>
						<td class="text-right"><?php echo $deudas_totales[$year_key]["abono"];?></td>
						<td class="text-right"><?php echo $deudas_totales[$year_key]["deuda"];?></td>
						<td><button class="btn btn-xs btn-default">+</button></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
	}
	if($_POST["opt"]=="cobranzas_load_local_periodos"){
		$local_id = $_POST["data"]["local_id"];
		$sql_command = "SELECT id,fecha_inicio,fecha_fin FROM tbl_periodo_liquidacion WHERE estado IN (1,2) ORDER BY fecha_inicio DESC";
		$sql_query = $mysqli->query($sql_command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		if($sql_query->num_rows==0){
			return 0;
		}
		while($sql = $sql_query->fetch_assoc()){
			// implode(glue, pieces)
			// print_r($sql);
			$fecha_ini = $sql["fecha_inicio"];//2021-10-01
			$fecha_fin = $sql["fecha_fin"];//2021-10-01
			$sql["periodo_liquidacion_id"] = $sql["id"];
			$sql["periodo_year"] = date("Y",strtotime($fecha_ini));//2021-10-01
			$sql["periodo_mes"] = date("m",strtotime($fecha_fin));//2021-10-01
			$sql["periodo_rango"] = date("d",strtotime($fecha_ini))."-".date("d",strtotime($fecha_fin));
			$sql["periodo_rango_int"] = date("d",strtotime($fecha_ini)).date("d",strtotime($fecha_fin));

			?>
			<div 
				class="list-group-item itm_periodo"
				data-periodo_liquidacion_id="<?php echo $sql['periodo_liquidacion_id'];?>"
				data-local_id="<?php echo $local_id;?>"
				data-periodo="<?php echo $sql["fecha_inicio"].' - '.$sql["fecha_fin"];?>"
				data-periodo_year="<?php echo $sql["periodo_year"];?>"
				data-periodo_mes="<?php echo $sql["periodo_mes"];?>"
				data-periodo_rango="<?php echo $sql["periodo_rango"];?>"
				data-btn_id="btn_unique_id_<?php echo $local_id;?>_<?php echo $sql["periodo_year"];?>_<?php echo $sql["periodo_mes"];?>_<?php echo $sql["periodo_rango"];?>"
				>
				<div class="nombre">
					<?php echo $sql["fecha_inicio"];?> - <?php echo $sql["fecha_fin"];?>
				</div>
				<button class="btn btn-xs pull-right move_btn">
					<span class="glyphicon glyphicon-chevron-right"></span>
				</button>
			</div>
			<?php
		}
	}
	if($_POST["opt"]=="cobranzas_load_eecc"){
		$data = $_POST["data"];
		$deudas_arr = array();
			$deudas_command = "
				SELECT
					d.tipo_id,
					dt.nombre AS tipo_nombre,
					SUM(d.monto) AS deuda,
					CAST(
							(
								SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono 
								FROM tbl_pagos p 
								WHERE p.local_id = d.local_id
								AND p.periodo_liquidacion_id = d.periodo_liquidacion_id
								AND p.deuda_tipo_id = d.tipo_id
								AND p.estado = 1
							)
							AS DECIMAL(20,2)
						) AS pagos,
					CAST(
							(
								SUM(d.monto)
								-
								(
									SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono 
									FROM tbl_pagos p 
									WHERE p.local_id = d.local_id
									AND p.periodo_liquidacion_id = d.periodo_liquidacion_id
									AND p.deuda_tipo_id = d.tipo_id
									AND p.estado = 1
								)
							) AS DECIMAL(20,2)
						) AS saldo
				FROM tbl_deudas d
				-- LEFT JOIN tbl_locales l ON (l.id = d.local_id)
				LEFT JOIN tbl_deudas_tipos dt ON(dt.id = d.tipo_id)
				WHERE d.local_id = '".$data["local_id"]."'
				AND d.periodo_liquidacion_id = '".$data["periodo_liquidacion_id"]."'
				AND d.estado = '1'
				GROUP BY
					d.tipo_id
				";
			$deudas_query = $mysqli->query($deudas_command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
			$totales = array();
				$totales["deuda"]=0;
				$totales["pagos"]=0;
				$totales["saldo"]=0;
			while($dd = $deudas_query->fetch_assoc()){
				$deudas_arr[$dd["tipo_id"]]=$dd;
				foreach ($totales as $key => $value) {
					if(array_key_exists($key, $dd)){
						$totales[$key]+=$dd[$key];
					}
				}
			}
			$totales["tipo_nombre"]="Total";
			// print_r($deudas_arr);
			$local = $mysqli->query("SELECT nombre FROM tbl_locales WHERE id = '".$data["local_id"]."'")->fetch_assoc();
		?>
		<?php 
		if(array_key_exists(65,$usuario_permisos) && in_array("add_deuda", $usuario_permisos[65])) {?>
		<button 
			class="btn btn-warning add_deuda_modal_btn"
			data-local_id="<?php echo $data["local_id"];?>"
			data-local_nombre="<?php echo $local["nombre"];?>"
			data-periodo_liquidacion_id="<?php echo $data["periodo_liquidacion_id"];?>"
			data-periodo="<?php echo $data["periodo"];?>"
			data-periodo_year="<?php echo $data["periodo_year"];?>"
			data-periodo_mes="<?php echo $data["periodo_mes"];?>"
			data-periodo_rango="<?php echo $data["periodo_rango"];?>"
			>Agregar Deuda</button>
		<?php }?>

		<?php if(array_key_exists(65,$usuario_permisos) && in_array("add_pago", $usuario_permisos[65])) { ?>
		<button 
			class="btn btn-primary add_pago_modal_btn"
			data-periodo_liquidacion_id="<?php echo $data["periodo_liquidacion_id"];?>"
			data-periodo="<?php echo $data["periodo"];?>"
			data-local_id="<?php echo $data["local_id"];?>"
			data-local_nombre="<?php echo $local["nombre"];?>"
			data-periodo_year="<?php echo $data["periodo_year"];?>"
			data-periodo_mes="<?php echo $data["periodo_mes"];?>"
			data-periodo_rango="<?php echo $data["periodo_rango"];?>"
			>Agregar Pago</button>
		<?php }?>
		<table class="table table-bordered table-condensed">
			<thead>
				<tr>
					<th>Tipo</th>
					<th>Deuda</th>
					<th>Pagos</th>
					<th>Saldo</th>
					<!-- <th>Opt</th> -->
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($deudas_arr as $key => $value) {
					?>					
					<tr>
						<td><?php echo $value["tipo_nombre"];?></td>
						<td style="text-align:right"><?php echo $value["deuda"];?></td>
						<td style="text-align:right"><?php echo $value["pagos"];?></td>
						<td style="text-align:right"><?php echo $value["saldo"];?></td>
						<!-- <td>opt</td> -->
					</tr>
					<?php
				}
				?>
			</tbody>
			<tfoot>
				<tr>
					<th><?php echo $totales["tipo_nombre"];?></th>
					<th style="text-align:right"><?php echo number_format($totales["deuda"], 2, ".", ",");?></th>
					<th style="text-align:right"><?php echo number_format($totales["pagos"], 2, ".", ",");?></th>
					<th style="text-align:right"><?php echo number_format($totales["saldo"], 2, ".", ",");?></th>
					<!-- <th>Opt</th> -->
				</tr>
				<?php $command = "SELECT SUM(p.abono) AS abono,
									pt.nombre AS nombre
									FROM tbl_pagos p
									LEFT JOIN tbl_pagos_tipos pt on pt.id = p.pago_tipo_id
									WHERE p.periodo_liquidacion_id = {$data['periodo_liquidacion_id']}
									AND p.local_id = {$data['local_id']}
									AND p.pago_tipo_id = 5
									AND p.deuda_tipo_id is NULL
									AND p.estado = 1";
					$periodo_saldo_a_favor = $mysqli->query($command)->fetch_assoc();
				?>
				<?php if($periodo_saldo_a_favor["abono"] != null){ ?>
				<tr>
					<th><?php echo $periodo_saldo_a_favor["nombre"];?></th>
					<th style="text-align:right"></th>
					<th style="text-align:right"><?php echo number_format($periodo_saldo_a_favor["abono"], 2, ".", ",");?></th>
					<th style="text-align:right"></th>
					<!-- <th>Opt</th> -->
				</tr>
				<?php }?>

			</tfoot>
		</table>
		<?php
		// print_r($data);
	}
	if($_POST["opt"]=="cobranzas_load_pago_source"){
		$data = $_POST["data"];
		// print_r($data);
		if( $data["pago_tipo_id"] == 1 ){
			$comando = "SELECT  id,nombre FROM tbl_bancos where estado=1";
			$bancos_query = $mysqli->query($comando);
		?>
			<div class="form-group">
				<label for="add_pago_input_abono">Banco</label>
				<select name="banco_id" class="form-control add_col" data-col="banco_id">
					<?php
						while($b=$bancos_query->fetch_assoc()){?>
						<option value="<?php echo $b["id"]?>"><?php echo $b["nombre"]?></option>
					<?php } ?>
				</select>
			</div>
			<div class="form-group">
				<label for="add_pago_input_abono">Nro Operación</label>
				<input type="text" data-col="nro_operacion" class="form-control add_col" maxlength="25">
			</div>
			
			<div class="form-group">
				<label for="add_pago_input_abono">Abono</label>
				<input type="text" data-col="abono" value="" class="form-control repartir_monto add_col" placeholder="Ej: 10.00" onkeypress="return validar_input_float(event, this);">
			</div>
			<div class="form-group">
				<label for="add_pago_input_voucher">Voucher</label>
				<input type="file" id="voucher" name="voucher" data-col="voucher" style="width:85%;display:inline" class="form-control add_col" accept=".jpg , .jpeg, .png">
				<button class="btn btn-rounded btn-primary btn-sm" style="margin-bottom:6px" id="vista_previa_img"><i class="fa fa-search"></i></button>
			</div>
			<div class="form-group">
				<label for="fecha_voucher">Fecha</label>
				<input type="date" data-col="fecha_voucher" class="form-control add_col" maxlength="25">
			</div>
			<div class="form-group">
				<label for="add_pago_input_descripcion">Descripción</label>
				<textarea data-col="descripcion" class="form-control add_col" rows="3" id="add_pago_input_descripcion" placeholder="Obligatorio."></textarea>
			</div>
		<?php 
		}else{
			?>
			<div class="form-group">
				<label for="add_pago_input_abono">Nro Operación</label>
				<input type="text" data-col="nro_operacion" class="form-control add_col" maxlength="25">
			</div>
			<div class="form-group">
				<label for="add_pago_input_voucher">Voucher</label>
				<input type="file" style="width:85%;display:inline" id="voucher" name="voucher" data-col="voucher" class="form-control add_col" accept=".jpg, .jpeg, .png">
				<button class="btn btn-rounded btn-primary btn-sm" style="margin-bottom:6px" id="vista_previa_img"><i class="fa fa-search"></i></button>
			</div>
			<div class="form-group">
				<label for="fecha_voucher">Fecha</label>
				<input type="date" data-col="fecha_voucher" class="form-control add_col" maxlength="25">
			</div>
			<div class="form-group">
				<label for="add_pago_input_abono">Abono</label>
				<input type="text" data-col="abono" value="" class="form-control repartir_monto add_col" placeholder="Ej: -10.00" onkeypress="return validar_input_float(event, this);">
			</div>
			<div class="form-group">
				<label for="add_pago_input_descripcion">Descripción</label>
				<textarea data-col="descripcion" class="form-control add_col" rows="3" id="add_pago_input_descripcion" placeholder="Obligatorio."></textarea>
			</div>
			<?php
		}
	}
	if($_POST["opt"]=="cobranzas_add_pago_load_deuda"){
		$data = $_POST["data"];
		$command = "
			SELECT
				d.local_id,
				d.tipo_id,
				dt.nombre AS tipo_nombre,
				CAST(
						(
							SUM(d.monto) - 
						(
							IF
								(
									(
										SELECT
											SUM(p.abono) AS abono
										FROM tbl_pagos p
										WHERE p.periodo_liquidacion_id= d.periodo_liquidacion_id
										AND p.local_id = d.local_id
										AND p.deuda_tipo_id = d.tipo_id
										AND p.estado = 1
									) != 0
									,(
										SELECT
											SUM(p.abono) AS abono
										FROM tbl_pagos p
										WHERE p.periodo_liquidacion_id= d.periodo_liquidacion_id
										AND p.local_id = d.local_id
										AND p.deuda_tipo_id = d.tipo_id
										AND p.estado = 1
									)
									,0
								)
							)
						)  AS DECIMAL(20,2)
					) AS monto,
				l.nombre AS local_nombre
			FROM tbl_deudas d
			LEFT JOIN tbl_locales l ON (l.id = d.local_id)
			LEFT JOIN tbl_deudas_tipos dt ON (dt.id  = d.tipo_id)
			WHERE 
			 d.periodo_liquidacion_id = '".$data["periodo_liquidacion_id"]."'
			AND d.local_id = '".$data["local_id"]."'
			AND d.estado = '1'
			GROUP BY
				d.tipo_id,
				d.local_id
			ORDER BY l.nombre, dt.prioridad ASC
			";
		$query = $mysqli->query($command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		$local_arr = array();
		while($l = $query->fetch_assoc()) {
			$local_arr["local_nombre"]=$l["local_nombre"];
			$local_arr["deudas"][$l["tipo_id"]]=$l;
		}
		$local_id = $_POST["data"]["local_id"];

		$dt_arr = array();
		$dt_command = "
			SELECT
				id,
				nombre,
				codigo
			FROM tbl_deudas_tipos
			WHERE estado = '1'
			ORDER BY prioridad ASC
						";
		$dt_query = $mysqli->query($dt_command);
		if($mysqli->error){
			echo $mysqli->error;
			echo $dt_command;
			exit();
		}
		while ($dt=$dt_query->fetch_assoc()) {
			$dt_arr[$dt["id"]]=$dt;
		}
		?>
		<!-- <button class="btn btn-block btn-warning limpiar_btn""><span class="glyphicon glyphicon-repeat"></span> Limpiar</button> -->
		<table class="table table-bordered table-condensed" id="deudas_holder_table">
			<thead>
				<tr>
					<th id="dt_codigo_total">Tipo</th>
					<th id="dt_codigo_abono">Deuda</th>
					<th id="dt_codigo_saldo">Abonar</th>
					<th id="dt_codigo_opt">Saldo</th>
				</tr>
			</thead>
			<tbody id="deudas_holder_tbody">
				<?php
				$total_deuda = 0;
				foreach ($dt_arr as $dt_codigo => $dt) {
					if(count($local_arr)==0){

					}else{

						if(array_key_exists($dt_codigo, $local_arr["deudas"])){
							$deuda = $local_arr["deudas"][$dt_codigo];
							if($deuda["monto"]!=0){
								$total_deuda+=$deuda["monto"];
								?>
								<tr class="deuda_repartir" data-deuda_tipo_id="<?php echo $dt_codigo;?>">
									<td class="col-xs-6 deuda_nombre"><?php echo $dt["nombre"];?></td>
									<td style="text-align:right"class="col-xs-2 deuda_monto" data-val="<?php echo $deuda["monto"]; ?>"><?php echo $deuda["monto"]; ?></td>
									<td style="text-align:right"class="col-xs-2 deuda_abonar">
										<input type="text" class="form-control" style="text-align:right" onkeypress="return validar_input_float(event, this);">
									</td>
									<td style="text-align:right" class="col-xs-2 deuda_saldo"><?php echo $deuda["monto"]; ?></td>
								</tr>
								<?php
							}
						}
					}

				}
				?>
			</tbody>
			<tfoot>
				<tr>
					<th>Total</th>
					<th class="total_deuda" style="text-align:right"><?php echo number_format($total_deuda,2);?></th>
					<th class="total_abonar" style="text-align:right">0.00</th>
					<th class="total_saldo" style="text-align:right"><?php echo number_format($total_deuda,2);?></th>
				</tr>
				<tr>
					<th>Excedente</th>
					<th class="" style="text-align:right"> </th>
					<th class="total_excedente" style="text-align:right">0.00</th>
					<th class="" style="text-align:right"> </th>
				</tr>
			</tfoot>
			<?php if(1==2){ ?>
				<thead>
					<tr>
						<th id="dt_codigo_local">Local</th>
						<?php
						foreach ($dt_arr as $dt_codigo => $dt_nombre) {
							?>
							<th id="dt_codigo_<?php echo $dt_codigo;?>"><?php echo $dt_nombre["nombre"];?></th>
							<?php
						}
						?>
						<th id="dt_codigo_total">Total</th>
						<th id="dt_codigo_abono">Abono</th>
						<th id="dt_codigo_saldo">Saldo</th>
						<th id="dt_codigo_opt">OPT</th>
					</tr>
				</thead>
				<tbody id="deudas_holder_tbody">
					<tr 
						class="deuda_local" 
						id="deuda_local_<?php echo $local_id;?>" 
						data-local_id="<?php echo $local_id;?>">
						<td><?php echo $local_id; ?> - <?php echo $local_arr["local_nombre"];?></td>
						<?php
						$sum_deuda = 0;
						foreach ($dt_arr as $dt_codigo => $dt) {
							if(array_key_exists($dt_codigo, $local_arr["deudas"])){
								$deuda = $local_arr["deudas"][$dt_codigo];
								$sum_deuda+=$deuda["monto"];
								?><td>
								<?php echo $deuda["monto"]; ?>
									<div 
										class="form-group deuda_repartir" 
										data-deuda_tipo='<?php echo $dt_codigo;?>' 
										data-deuda_tipo_id='<?php echo $dt["id"];?>'>
										<input type="hidden" class="form-control monto" value="<?php echo $deuda["monto"]; ?>">
										<input type="text" class="form-control amort" value="">
									</div>
								</td><?php
							}else{
								?><td>0.00</td><?php
							}
						}
						?>
						<td class="td_deuda_total"><?php echo number_format($sum_deuda,2);?></td>
						<td class="td_deuda_abono">0.00</td>
						<td class="td_deuda_saldo">0.00</td>
						<td>
							<button class="btn btn-xs btn-danger remove_local_btn" data-local_id="<?php echo $local_id;?>"><span class="glyphicon glyphicon-remove"></span></button>
						</td>
					</tr>
				</tbody>
			<?php } ?>
		</table>		
		<?php
	}
	if( $_POST["opt"] == "lista_locales_estado_de_cuenta_server" ){
		$ID_LOGIN = $login["id"];
		$draw = $_POST['draw'];
		$row = $_POST['start'];
		$rowperpage = $_POST['length']; // Rows display per page
		$columnIndex = $_POST['order'][0]['column']; // Column index
		$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
		$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
		$searchValue = $_POST['search']['value']; // Search value
		$searchValue = $mysqli->real_escape_string($searchValue);

		$fecha_condicion = "";

		$fecha_condition = "
        	cab.fecha >= (select fecha_inicio from tbl_periodo_liquidacion order by fecha_inicio asc limit 1)
			AND cab.fecha <= (select fecha_fin from tbl_periodo_liquidacion order by fecha_fin desc limit 1)
		";

	    ## Search
		$searchQuery = " ";
		if($searchValue != ''){
		    $searchQuery = "
		    AND
		    (l.operativo like '%".$searchValue."%' OR
		        l.id like '%".$searchValue."%' OR
		        l.nombre like'%".$searchValue."%'
		    ) ";
		}

		$sel = $mysqli->query("SELECT count(*) AS allcount FROM tbl_locales l
			WHERE l.id NOT IN (1)
			AND l.reportes_mostrar = '1'
			AND l.operativo in (1,2)
			AND l.red_id = 5
		");
		$records = $sel->fetch_assoc();
		$totalRecords = $records['allcount'];

		$select = "
		SELECT
			l.operativo,
			l.id,
			l.nombre as local_nombre,
			CAST(
				IF(SUM(ec.deuda), SUM(ec.deuda),0) AS DECIMAL(20,2)
			) AS debe,
			CAST(
				IF(SUM(ec.pago), SUM(ec.pago),0) AS DECIMAL(20,2)
			) AS haber,
			((SELECT
				sum(total_freegames + total_caja_web)
				FROM tbl_transacciones_cabecera  
                WHERE id IS NOT NULL 
                AND local_id = l.id 
                AND estado = '1' 
				AND canal_de_venta_id != '30'
                AND fecha >= (select fecha_inicio from tbl_periodo_liquidacion order by fecha_inicio asc limit 1)
                AND fecha <= (select fecha_fin from tbl_periodo_liquidacion order by fecha_fin desc limit 1)
				group by local_id) - (CAST(
				IF(SUM(ec.deuda), SUM(ec.deuda),0) AS DECIMAL(20,2)
			))
            ) as diferencia 
		FROM tbl_estados_cuenta ec
			INNER JOIN tbl_locales l ON (l.id = ec.id_local)
		WHERE
			l.id NOT IN (1)
			AND l.reportes_mostrar = '1'
			AND l.operativo in (1,2)
			AND l.red_id = 5
		";

		$periodo_inicio_search = '';
		$periodo_fin_search = '';
		// INICIO: SEC_COBRANZAS_DETALLE
		if (isset($_POST['tipo']) && $_POST['tipo']>0) {
			if (isset($_POST['local_id']) && $_POST['local_id'] != '') {
				$local_id_search = htmlspecialchars(implode(',', $_POST['local_id']));
				if ($local_id_search > 0) {
					$searchQuery.= " AND l.id IN ($local_id_search)";
				}
			}
			if (isset($_POST['periodo_inicio'])) {
				$periodo_inicio_search = htmlspecialchars($_POST['periodo_inicio']);
			}
			if (isset($_POST['periodo_fin'])) {
				$periodo_fin_search = htmlspecialchars($_POST['periodo_fin']);
			}

			$fecha_condition = "
				cab.fecha >= (select fecha_inicio from tbl_periodo_liquidacion where id = '$periodo_inicio_search')
				AND cab.fecha <= (select fecha_fin from tbl_periodo_liquidacion where id = '$periodo_fin_search')
			";

			$select = "
			SELECT
				l.operativo,
				l.id,
				l.nombre as local_nombre,
				(
					IF(SUM(de.monto),SUM(de.monto),0)
				) AS debe,
				(
					SELECT IFNULL(sum(pd.monto), 0)
					FROM tbl_pagos_detalle pd
					WHERE
						pd.id in (
									SELECT distinct p.pago_detalle_id
									FROM tbl_pagos p
									WHERE
										p.local_id = l.id
										AND p.estado = 1
										AND p.periodo_liquidacion_id >= '$periodo_inicio_search'
										AND p.periodo_liquidacion_id <= '$periodo_fin_search'
								)
						-- and pd.estado = 1
				) AS haber,
				((SELECT
					sum(total_freegames + total_caja_web)
					FROM tbl_transacciones_cabecera  
					WHERE id IS NOT NULL 
					AND local_id = l.id 
					AND estado = '1' 
					AND canal_de_venta_id != '30'
					AND fecha >= (select fecha_inicio from tbl_periodo_liquidacion where id = '$periodo_inicio_search')
					AND fecha <= (select fecha_fin from tbl_periodo_liquidacion where id = '$periodo_fin_search')
					group by local_id
				) - 
				(
					IF(SUM(de.monto),SUM(de.monto),0)
				)) as diferencia
			FROM tbl_locales l
			left join tbl_deudas de on (de.local_id = l.id AND de.estado = 1)
            WHERE
				de.periodo_liquidacion_id >= '$periodo_inicio_search'
				AND de.periodo_liquidacion_id <= '$periodo_fin_search'
				AND l.red_id = 5
				AND l.id NOT IN (1)
				AND l.reportes_mostrar = '1'
				AND l.operativo in (1,2)
			";
		}
		// FIN: SEC_COBRANZAS_DETALLE

		//col 0 OPERATIVO
		if($_POST["columns"][0]["search"]["value"] != "" && $_POST["columns"][0]["search"]["value"] != "null"){
			$searchQuery.=	" AND l.operativo in (".$_POST["columns"][0]["search"]["value"].")";
		}

		$empQuery =   $select . $searchQuery . " GROUP BY l.id";
		$sel = $mysqli->query("SELECT count(*) AS allcount FROM (". $empQuery .") AS subquery");
		$records = $sel->fetch_assoc();
		$totalRecordwithFilter = $records['allcount'];


		$limit =" limit ".$row.",".$rowperpage;
		if($rowperpage == -1){
			$limit = "";
		}
		$empQuery = $select . $searchQuery . " GROUP BY l.id"
		 . " order by ".$columnName." ".$columnSortOrder.$limit;
		$empRecords = $mysqli->query($empQuery);
		$data = array();

		// CANALES DE VENTA BINGO
		$deuda_command = "
			SELECT
				cab.canal_de_venta_id AS canal_de_venta_id,
				cab.fecha,
				l.id AS local_id,
				CAST(YEAR(cab.fecha) AS SIGNED) AS periodo_year,
				DATE_FORMAT(cab.fecha, '%m') AS periodo_mes,

				SUM(cab.total_freegames) AS part_fg,
				SUM(cab.total_pagado) AS total_pagado,
				CAST(SUM(cab.pagado_en_otra_tienda) - SUM(cab.pagado_de_otra_tienda) AS DECIMAL(10,2)) AS dif_tk,
				(SUM(cab.total_freegames)) AS web_total,
				(sum(total_pagado) - sum(pagado_en_otra_tienda)) AS pagados_en_su_punto_propios
				FROM
					tbl_transacciones_cabecera  cab
				LEFT JOIN tbl_locales l ON (l.id  = cab.local_id)
				WHERE
				 {$fecha_condition}
				AND cab.estado = 1
				AND cab.servicio_id in (1,3,9,13,15)
				AND l.reportes_mostrar = '1'
				AND l.red_id = 5
				and canal_de_venta_id = '30'
				$searchQuery
				GROUP BY 
					local_id ASC,
					canal_de_venta_id ASC";
		$deuda_query = $mysqli->query($deuda_command);
		if($mysqli_error = $mysqli->error){
			$return["error"]=true;
			$return["error_msg"]= "Error Servidor";
			$return["error_detalle"]= $mysqli_error;
			print_r(json_encode($return));
			exit();
		}

		$temp_arr = array();
		while($d=$deuda_query->fetch_assoc()){
			$temp_arr[$d["local_id"]] = ($d["part_fg"] - $d["pagados_en_su_punto_propios"]);
		}

		$i = 0;
		while ($row = $empRecords->fetch_assoc()) {
			$data[$i] = $row;
			$local_id = $row['id'];
			$data[$i]['local_deuda'] = $row['debe']-$row['haber'];
			$data[$i]['local_deuda'] = number_format($data[$i]['local_deuda'], 2, '.', '');
			$data[$i]['diferencia'] = number_format(($row['diferencia'] + (isset($temp_arr[$local_id]) ? $temp_arr[$local_id] : 0)), 2, '.', '');
			$data[$i]['diferencia_estado'] = ($data[$i]['diferencia'] != '0.00') ? 1 : 0;
			$i++;
		}

		$response = array(
		  "draw" => $draw,
		  "iTotalRecords" => $totalRecords,
		  "iTotalDisplayRecords" => $totalRecordwithFilter,
		  "aaData" => $data
		);

		echo json_encode($response);
		return;
	}
	if($_POST["opt"]=="lista_locales_estado_de_cuenta"){
		$locales_arr = array();
		$locales_query = "
			SELECT
				l.operativo,
				l.id,
				l.nombre as local_nombre,
				CAST(
					IF(SUM(ec.deuda), SUM(ec.deuda),0) AS DECIMAL(20,2)
				) AS debe,
				CAST(
					IF(SUM(ec.pago), SUM(ec.pago),0) AS DECIMAL(20,2)
				) AS haber,
				CAST(
					(IF(SUM(ec.deuda), SUM(ec.deuda),0)) - (IF(SUM(ec.pago), SUM(ec.pago),0)) AS DECIMAL(20,2)
				) AS local_deuda
			FROM tbl_estados_cuenta ec
				INNER JOIN tbl_locales l ON (l.id = ec.id_local)
			WHERE
				l.id NOT IN (1)
				AND l.reportes_mostrar = '1'
				AND l.operativo in (1,2)
				AND l.red_id = 5
			GROUP BY l.id
		";
		$locales_query = $mysqli->query($locales_query);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		while ($l = $locales_query->fetch_assoc()) {
			$locales_arr[]=$l;
		}
		$return["lista"]=$locales_arr;
		print_r(json_encode($return));
	}

	if($_POST["opt"]=="lista_locales_enviar"){
		$locales_arr = array();
		$locales_query = "
			SELECT
				l.operativo,
				l.id,
				l.nombre AS 'local_nombre'
			FROM tbl_locales l
			WHERE 
			l.id NOT IN (1)
			AND l.reportes_mostrar = '1'
			AND l.operativo in (1)
			AND l.red_id = 5
			AND l.estado = 1
			GROUP BY l.id
			ORDER BY local_nombre ASC
			";
		$locales_query = $mysqli->query($locales_query);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		while ($l = $locales_query->fetch_assoc()) {
			$locales_arr[]=$l;
		}
		$return["lista"]=$locales_arr;
		print_r(json_encode($return));

	}
	if($_POST["opt"] == "vista_previa_estado_cuenta")
	{
		$periodo_query = "
			SELECT id,fecha_inicio,fecha_fin
			FROM tbl_periodo_liquidacion 
			WHERE id=".$_POST["periodo_id"];
		$periodo = $mysqli->query($periodo_query)->fetch_assoc();

		////estado de cuenta
		$locales_estado_cuenta = [];
		$estado_cuenta_query = "
			SELECT
				l.operativo,
				l.id,
				l.nombre as local_nombre,
				(
					SELECT IF(SUM(d.monto),SUM(d.monto),0) AS monto_deuda
					FROM tbl_deudas d
					WHERE
						d.local_id = l.id
						AND d.estado = 1
						AND d.periodo_liquidacion_id <= '{$_POST["periodo_id"]}'
				)AS debe
				,(
					SELECT IFNULL(sum(pd.monto), 0)
					FROM tbl_pagos_detalle pd
					WHERE
						pd.id in (
									SELECT distinct p.pago_detalle_id
									FROM tbl_pagos p
									WHERE
										p.local_id = l.id
										AND p.estado = 1
										AND p.periodo_liquidacion_id <= '{$_POST["periodo_id"]}'
								)
						-- and pd.estado = 1
				) AS haber
			FROM tbl_locales l
			WHERE
				l.id NOT IN (1)
				AND l.reportes_mostrar = '1'
				AND l.operativo in (1,2)
				AND l.red_id = 5
				AND l.id IN (".join(",", $_POST["locales"]).")
		";
		$estado_cuenta = $mysqli->query($estado_cuenta_query);
		while ($fila = $estado_cuenta -> fetch_assoc() ) {
			$fila['local_deuda'] = $fila['debe'] - $fila['haber'];
			$locales_estado_cuenta[$fila["id"]] = $fila;
		}

		//////////////////
		$locales_query = "
			SELECT
				l.nombre,
				l.id,
				l.email as correo_local
			FROM tbl_locales l
			INNER JOIN tbl_usuarios_locales ul ON ul.local_id = l.id AND ul.estado = 1
			LEFT JOIN tbl_usuarios u ON u.id = ul.usuario_id AND u.estado = 1
			LEFT JOIN tbl_personal_apt psop ON (
				psop.id = u.personal_id
				AND psop.area_id = 21
				AND psop.cargo_id = 4
				AND psop.estado = 1
			)
			WHERE 
			l.id IN (".join(",", $_POST["locales"]).")
			GROUP BY l.id
		";
		$locales_query = $mysqli->query($locales_query);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}

		$variables=[];
	    $variables["filtro"]["fecha_inicio"]= $periodo["fecha_inicio"];
		$variables["filtro"]["fecha_fin"]= 	 $periodo["fecha_fin"];
		$variables["filtro"]["locales"]= 	 $_POST["locales"];
		$variables["where"]= "liquidaciones";

		$datos=get_datos($variables);
		$return["pdfs"]=[];
		$return["errores"]=[];
		

		while ($l = $locales_query->fetch_assoc()) {
			//require("/var/www/html/api/where_liquidaciones.php");
			//echo "<pre>";print_r($return);echo "</pre><br>";
			//if(isset($datos["data"]["locales"])){
				//$datos_agente=$datos["data"]["locales"][0]["liquidaciones"]["total_rango_fecha"];
			$datos_agente=[];
			if(isset($datos["data"]["locales"])){
				foreach ($datos["data"]["locales"] as $key => $value) {
					if($value["id"]==$l["id"]){
						$datos_agente= $value["liquidaciones"]["total_rango_fecha"];
						break;
					}				
				}
			}
			if(count($datos_agente) > 0){
				$pdf = generar_pdf($datos_agente,$l , $locales_estado_cuenta[ $l["id"] ] );
				$path='/var/www/html/export/estado_cuenta/';
				$periodo_temp = str_replace("-", "_", $_POST["periodo"]); 
				$periodo_temp = str_replace(" ", "", $periodo_temp); 
				$nombre_archivo = "vista_previa_agente_".$l["id"]."_".$periodo_temp."_".date('YmdHis').".pdf";

				$attach_temp = $pdf->Output($path.$nombre_archivo, 'F');
	    		$attach = $path.$nombre_archivo;

				$return["pdfs"][] = [
					"local" => $l["id"]." - ".$l["nombre"]
					,"archivo" => '/export/estado_cuenta/'.$nombre_archivo
				];
			//echo "<pre>";print_r($return);	echo "<pre>";		die();

			}
			else{
				$return["agente_sin_datos"][]=["local"=>$l["id"]." - ".$l["nombre"]];
				$return["errores"][]=$l["id"]." - ".$l["nombre"]." - Agente sin datos";
			}
		}
		print_r(json_encode($return));die();

		//echo "<pre>";print_r($return);		echo "</pre>";die();

	}

	if($_POST["opt"]=="enviar_estado_cuenta"){
		include '/var/www/html/sys/globalFunctions/generalInfo/parameterGeneral.php';

		$periodo_query = "
				SELECT id,fecha_inicio,fecha_fin
				FROM tbl_periodo_liquidacion 
				WHERE id=".$_POST["periodo_id"];
		$periodo = $mysqli->query($periodo_query)->fetch_assoc();


		////estado de cuenta
		$locales_estado_cuenta = [];
		$estado_cuenta_query = "
			SELECT
				l.id,
				l.nombre as local_nombre,
				CAST(
					IF(SUM(ec.deuda), SUM(ec.deuda),0) AS DECIMAL(20,2)
				) AS debe,
				CAST(
					IF(SUM(ec.pago), SUM(ec.pago),0) AS DECIMAL(20,2)
				) AS haber,
				CAST(
					(
					IF(SUM(ec.deuda), SUM(ec.deuda),0)
					-
					IF(SUM(ec.pago), SUM(ec.pago),0)
					) AS DECIMAL(20,2)
				) AS local_deuda
			FROM tbl_estados_cuenta ec
				INNER JOIN tbl_locales l ON (l.id = ec.id_local)
			WHERE 
			l.id NOT IN (1)
			AND l.id IN (".join(",", $_POST["locales"]).")
			AND l.reportes_mostrar = '1'
			AND l.operativo in (1,2)
			AND l.red_id = 5
			GROUP BY l.id
			ORDER BY l.nombre ASC";
		$estado_cuenta = $mysqli->query($estado_cuenta_query);
		while ($fila = $estado_cuenta -> fetch_assoc() ) {
			$locales_estado_cuenta[$fila["id"]] = $fila;
		}
		//////////////////
		$locales_query = "  SELECT
								l.nombre,
								l.id,
								l.email as correo_local
							FROM tbl_locales l
							WHERE 
							l.id IN (" . join(",", $_POST["locales"]) . ")
							GROUP BY l.id
						";
		//echo $locales_query;
		$locales_query = $mysqli->query($locales_query);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		$variables=[];
	    $variables["filtro"]["fecha_inicio"]= $periodo["fecha_inicio"];
		$variables["filtro"]["fecha_fin"]= 	 $periodo["fecha_fin"];
		$variables["filtro"]["locales"]= 	 $_POST["locales"];
		$variables["where"]= "liquidaciones";

		$datos=get_datos($variables);
		//echo "<pre>";print_r($datos);echo "</pre><br>";
		$correos_enviados=0;
		$return["pdfs"]=[];
		$return["errores"]=[];
		while ($l = $locales_query->fetch_assoc()) {
			//require("/var/www/html/api/where_liquidaciones.php");
			//echo "<pre>";print_r($return);echo "</pre><br>";
			//if(isset($datos["data"]["locales"])){
				//$datos_agente=$datos["data"]["locales"][0]["liquidaciones"]["total_rango_fecha"];
			$datos_agente=[];
			if(isset($datos["data"]["locales"])){
				foreach ($datos["data"]["locales"] as $key => $value) {
					if($value["id"]==$l["id"]){
						$datos_agente= $value["liquidaciones"]["total_rango_fecha"];
						break;
					}				
				}
			}
			if(count($datos_agente)>0){
				//echo "<pre>";print_r($datos_agente);echo "</pre><br>";
				$pdf = generar_pdf($datos_agente,$l , $locales_estado_cuenta[ $l["id"] ] );
				$path='/var/www/html/export/estado_cuenta/';
				$periodo_temp=str_replace("-", "_", $_POST["periodo"]); 
				$periodo_temp=str_replace(" ", "", $periodo_temp); 
				$nombre_archivo="agente_".$l["id"]."_".$periodo_temp."_".date('YmdHis').".pdf";

				$attach_temp= $pdf->Output($path.$nombre_archivo, 'F');

	    		$attach= $path.$nombre_archivo;
				$cuenta_bancaria_fregames= getParameterGeneral('cobranzas_freegames_cuenta_bancaria');
				$nombre_fregames= "FREE GAMES SAC";

				$body="Adjuntamos el Cuadre de caja semanal correspondiente al periodo del ".str_replace("-","/",$periodo["fecha_inicio"])." AL ".str_replace("-","/",$periodo["fecha_fin"]).".<br>
					Agradeceremos realizar el abono correspondiente en nuestra cuenta bancaria según detallamos a continuación:<br>
					BANCO BBVA N° ".$cuenta_bancaria_fregames."<br>
					NOMBRE:  ".$nombre_fregames."<br>";

				// $correo = ($_POST["chk_test"] == 1) ? "ricardo.lanchipa@testtest.kurax.dev" : $l["correo_local"];
				$correo = $l["correo_local"];
				
				$cc = [
					$correo
				];				

				$correos_query = "  SELECT 
								l.nombre AS nombre, 
								l.id as id, 
								psop.correo as correo_supervisor
							from
								tbl_usuarios_locales ul
								inner join tbl_locales l ON ul.local_id = l.id AND ul.estado = 1
								inner JOIN tbl_usuarios u ON u.id = ul.usuario_id AND u.estado = 1
								inner JOIN tbl_personal_apt psop ON psop.id = u.personal_id
							WHERE
								l.id IN (" . join(",", $_POST["locales"]) . ")
								and psop.`cargo_id` = 4
							GROUP BY correo_supervisor
						";

				$correos_query = $mysqli->query($correos_query);
				if($mysqli->error){
					print_r($mysqli->error);
					exit();
				}
				while ($correo = $correos_query->fetch_assoc()) {
					if($correo['correo_supervisor'] != null){
						$cc[] = $correo['correo_supervisor'];
					}
				}

				$bcc =[
						];
				//$bcc = [];
				$mail = [
					"subject" => "Cuadre de caja semanal ".$l["nombre"],
					"body"    =>$body,
					"cc"      => $cc,
					"bcc"     => $bcc,
				];
				$mail["attach"]=$attach;

				$envio=send_email_ag($mail);
				//$envio=true;
				if($envio===true){
					$correos_enviados++;
				}
				else{
					$return["errores"][]=$l["id"]." - ".$l["nombre"]." ".($correo?:"Sin Correo")." - ".$envio;
				}

				$return["pdfs"][]=["local"=>$l["id"]." - ".$l["nombre"],"correo"=>$correo,"archivo"=>$attach,"envio"=>$envio];
			}
			else{
				$return["agente_sin_datos"][]=["local"=>$l["id"]." - ".$l["nombre"]];
				$return["errores"][]=$l["id"]." - ".$l["nombre"]." ". ( isset($l["supervisor_correo"]) ? $l["supervisor_correo"] : "Sin Correo" ) ." - Agente sin datos";
				//$datos_agente =[]
			}
			$temp=$correos_enviados==1?" Correo enviado":" Correos enviados";
			$return["mensaje"]=$correos_enviados.$temp;
		}
		$return["locales"]=$_POST["locales"];
		print_r(json_encode($return));
	}
	if($_POST["opt"]=="lista_pagos_periodo"){
		extract($_POST);//$periodo_liquidacion_id, $local_id
		$lista_arr = array();
		$pagos_query = "
			SELECT
			pd.id ,
			p.id AS pago_id,
			p.pago_tipo_id,
			pt.nombre AS pago_tipo_nombre,
			pd.monto AS repartir,
			pd.descripcion AS 'descripcion',
			pd.nro_operacion,
			(SELECT sum(p2.abono) FROM tbl_pagos p2 WHERE p2.estado = 1
				AND p2.periodo_liquidacion_id = p.periodo_liquidacion_id
				AND p2.pago_tipo_id = 5 AND p2.deuda_tipo_id IS NOT  null
				AND p2.id = p.id
			 ) AS abono_saldo,
			( SELECT sum(p2.abono) FROM tbl_pagos p2 WHERE p2.estado = 1 AND p2.deuda_tipo_id IS NOT null
				AND p2.periodo_liquidacion_id = p.periodo_liquidacion_id
				AND p2.pago_detalle_id = p.pago_detalle_id
              )  AS abono ,
			(pd.monto - ( SELECT sum(p2.abono) FROM tbl_pagos p2 WHERE p2.estado = 1 AND p2.deuda_tipo_id IS NOT null
				AND p2.periodo_liquidacion_id = p.periodo_liquidacion_id
				AND p2.pago_detalle_id = p.pago_detalle_id
              ) ) AS saldo_favor,
			count(p.id) AS pagos_cantidad,
			p.fecha_ingreso
			FROM  tbl_pagos p
			LEFT JOIN tbl_pagos_detalle pd ON pd.id= p.pago_detalle_id
			LEFT JOIN tbl_pagos_tipos pt ON pt.id = p.pago_tipo_id
			WHERE p.estado = 1
			AND p.periodo_liquidacion_id = $periodo_liquidacion_id
			AND p.local_id = $local_id
	        AND p.pago_tipo_id != 5
			GROUP BY pd.id
			";
			//echo "<pre>";print_r($pagos_query);echo "<pre>";die();
		$pagos_query = $mysqli->query($pagos_query);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		while ($l = $pagos_query->fetch_assoc()) {
			$lista_arr[]=$l;
		}

		$pagos_saldos_query = "
            SELECT
            '' as id
            ,p.id AS pago_id
            ,p.pago_tipo_id
            ,pt.nombre AS pago_tipo_nombre
            ,p.abono AS repartir
            ,p.abono AS abono_saldo
            ,p.abono AS abono
            ,'' AS nro_operacion
            ,p.fecha_ingreso
            FROM tbl_pagos p
			LEFT JOIN tbl_pagos_tipos pt ON pt.id = p.pago_tipo_id
            WHERE p.pago_tipo_id = 5
            AND p.periodo_liquidacion_id = $periodo_liquidacion_id
            AND p.local_id = $local_id
            AND p.estado = 1
            AND p.deuda_tipo_id is not null
			";
			//echo "<pre>";print_r($pagos_query);echo "<pre>";die();
		$pagos_saldos_query = $mysqli->query($pagos_saldos_query);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		while ($l = $pagos_saldos_query->fetch_assoc()) {
			$lista_arr[]=$l;
		}

		$return["lista"]=$lista_arr;
		print_r(json_encode($return));
	}
	if($_POST["opt"] == "eliminar_pago_periodo"){
		extract($_POST);
		$command = "";
		if($pago_tipo_id == 5) {
			$command = "UPDATE tbl_pagos
					SET estado = 0
					WHERE id = $pago_id";
		}
		else{
			$command = "UPDATE tbl_pagos
						SET estado = 0
						WHERE pago_detalle_id = $pago_detalle_id";
		}
		$mysqli->query($command);
		if($mysqli_error = $mysqli->error){
			print_r($mysqli_error);
			echo "\n";
			echo $command;
			exit();
		}
		$command = "
			UPDATE tbl_pagos_detalle
			SET estado = 0
			WHERE id = $pago_detalle_id";
		$mysqli->query($command);
		if($mysqli_error = $mysqli->error){
			print_r($mysqli_error);
			echo "\n";
			echo $command;
			exit();
		}
		
		// Actualizando tabla de estados de cuenta
		$sql_search_monto = "
			SELECT p.local_id, pd.monto
			FROM
				tbl_pagos_detalle pd
				INNER JOIN tbl_pagos p ON (p.pago_detalle_id = pd.id)
			WHERE pd.id = $pago_detalle_id
			LIMIT 1
		";
		$sql_search = $mysqli->query($sql_search_monto);
		while ($row = $sql_search->fetch_assoc()) {
			$sql_update_eecc = "
				UPDATE tbl_estados_cuenta
				SET pago = pago - {$row['monto']}, update_fecha_pago = NOW()
				WHERE id_local = {$row['local_id']}
			";
			$mysqli->query($sql_update_eecc);
		}

		$return["mensaje"] = "Pago Eliminado";
		print_r(json_encode($return));
	}

	if($_POST["opt"]=="ver_pago_detalle"){
		extract($_POST);
		if($pago_tipo_id != 5 && $pago_detalle_id !="null"){
			$comando = "
			SELECT pd.monto,pd.descripcion,pd.voucher,b.nombre
			,pd.nro_operacion 
			FROM tbl_pagos_detalle pd
			LEFT JOIN tbl_bancos b ON b.id= pd.banco_id
			WHERE pd.id={$pago_detalle_id} ";
			$pago_detalle = $mysqli->query($comando)->fetch_assoc();

			if($pago_tipo_id == 1){
				$comando = "SELECT  id,nombre FROM tbl_bancos WHERE estado = 1";
				$bancos_query = $mysqli->query($comando);
			?>
			<div class="col-xs-6">
				<div class="form-group">
					<label class="col-xs-6" for="add_pago_input_abono">Banco: </label>
					<div class="col-xs-6"> <?php echo $pago_detalle["nombre"]?></div>
				</div>
				<div class="form-group">
					<label class="col-xs-6">Nro Operación: </label>
					<div class="col-xs-6"> <?php echo $pago_detalle["nro_operacion"]?:"--";?></div>
				</div>
				<div class="form-group">
					<label class="col-xs-6" for="add_pago_input_abono">Abono: </label>
					<div class="col-xs-6"> <?php echo $pago_detalle["monto"];?></div>
				</div>

				<div class="form-group">
					<label class="col-xs-6" for="add_pago_input_voucher">Voucher</label>
					<div class="col-xs-6"><button class="btn btn-rounded btn-primary btn-xs" style="margin-bottom:6px" id="vista_previa_pago_detalle_img"><i class="fa fa-eye"></i></button></div>
					<input type="hidden" value= "<?php echo $pago_detalle["voucher"]?>" name="voucher_img">
					
				</div>
				<div class="form-group">
					<label class="col-xs-6" for="add_pago_input_descripcion">Descripción: </label>
					<div class="col-xs-6"> <?php echo $pago_detalle["descripcion"];?></div>
				</div>
			</div>
			<?php
			}
			else
			{
			?>
			<div class="col-xs-6">
				<div class="form-group">
					<label class="col-xs-6">Nro Operación: </label>
					<div class="col-xs-6"><?php echo $pago_detalle["nro_operacion"]?:"---";?></div>
				</div>
				<div class="form-group">
					<label class="col-xs-6" for="add_pago_input_abono">Abono: </label>
					<div class="col-xs-6"><?php echo $pago_detalle["monto"];?></div>
				</div>
				<div class="form-group">
					<label class="col-xs-6" for="add_pago_input_voucher">Voucher</label>
					<div class="col-xs-6"><button class="btn btn-rounded btn-primary btn-xs" style="margin-bottom:6px" id="vista_previa_pago_detalle_img"><i class="fa fa-eye"></i></button></div>
					<input type="hidden" value= "<?php echo $pago_detalle["voucher"]?>" name="voucher_img">
					
				</div>
				
				<div class="form-group">
					<label class="col-xs-6" for="add_pago_input_descripcion">Descripción: </label>
					<div class="col-xs-6"><?php echo $pago_detalle["descripcion"];?></div>
				</div>
			</div>
				<?php
			}
		}

		$deudas_arr = array();
			$deudas_command = "
				SELECT
					d.tipo_id,
					dt.nombre AS tipo_nombre,
					SUM(d.monto) AS deuda,
					CAST(
							(
								SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono
								FROM tbl_pagos p
								WHERE p.local_id = d.local_id
								AND p.periodo_liquidacion_id = d.periodo_liquidacion_id
								AND p.deuda_tipo_id = d.tipo_id
								AND p.estado = 1
								AND p.pago_detalle_id ={$pago_detalle_id}
							)
							AS DECIMAL(20,2)
						) AS pagos,
					CAST(
							(
								SUM(d.monto)
								-
								(
									SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono
									FROM tbl_pagos p
									WHERE p.local_id = d.local_id
									AND p.periodo_liquidacion_id = d.periodo_liquidacion_id
									AND p.deuda_tipo_id = d.tipo_id
									AND p.estado = 1
									AND p.pago_detalle_id ={$pago_detalle_id}

								)
							) AS DECIMAL(20,2)
						) AS saldo
				FROM tbl_deudas d
				LEFT JOIN tbl_deudas_tipos dt ON(dt.id = d.tipo_id)
				WHERE d.local_id = '".$local_id."'
				AND d.periodo_liquidacion_id = '".$periodo_liquidacion_id."'
				AND d.estado = '1'
				GROUP BY
					d.tipo_id
				";
			if($pago_tipo_id == 5){
				$deudas_command = "
				SELECT
					d.tipo_id,
					dt.nombre AS tipo_nombre,
					SUM(d.monto) AS deuda,
					CAST(
							(
								SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono
								FROM tbl_pagos p
								WHERE p.local_id = d.local_id
								AND p.periodo_liquidacion_id = d.periodo_liquidacion_id
								AND p.deuda_tipo_id = d.tipo_id
								AND p.estado = 1
								AND p.id ={$pago_id}
							)
							AS DECIMAL(20,2)
						) AS pagos,
					CAST(
							(
								SUM(d.monto)
								-
								(
									SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono
									FROM tbl_pagos p
									WHERE p.local_id = d.local_id
									AND p.periodo_liquidacion_id = d.periodo_liquidacion_id
									AND p.deuda_tipo_id = d.tipo_id
									AND p.estado = 1
									AND p.id ={$pago_id}

								)
							) AS DECIMAL(20,2)
						) AS saldo
				FROM tbl_deudas d
				LEFT JOIN tbl_deudas_tipos dt ON(dt.id = d.tipo_id)
				WHERE d.local_id = '".$local_id."'
				AND d.periodo_liquidacion_id = '".$periodo_liquidacion_id."'
				AND d.estado = '1'
				GROUP BY
					d.tipo_id
				";
			}

			$deudas_query = $mysqli->query($deudas_command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
			$totales = array();
				$totales["deuda"] = 0;
				$totales["pagos"] = 0;
				$totales["saldo"] = 0;
			while($dd = $deudas_query->fetch_assoc()){
				$deudas_arr[$dd["tipo_id"]] = $dd;
				foreach ($totales as $key => $value) {
					if(array_key_exists($key, $dd)){
						$totales[$key] += $dd[$key];
					}
				}
			}
			$totales["tipo_nombre"]="Total";
			$local = $mysqli->query("SELECT nombre FROM tbl_locales WHERE id = '".$local_id."'")->fetch_assoc();
		?>
		<div class="col-xs-6">
			<table class="table table-bordered table-condensed">
				<thead>
					<tr>
						<th>Tipo</th>
						<th>Pagos</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($deudas_arr as $key => $value) {
						?>
						<tr>
							<td><?php echo $value["tipo_nombre"];?></td>
							<td style="text-align:right"><?php echo $value["pagos"];?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th><?php echo $totales["tipo_nombre"];?></th>
						<th style="text-align:right"><?php echo number_format($totales["pagos"], 2, ".", ",");?></th>
					</tr>
					<?php 
					if($pago_detalle_id != ""){
						$command = "SELECT SUM(p.abono) AS abono,
											pt.nombre AS nombre
											FROM tbl_pagos p
											LEFT JOIN tbl_pagos_tipos pt on pt.id = p.pago_tipo_id
											WHERE p.periodo_liquidacion_id = {$periodo_liquidacion_id}
											AND p.local_id = {$local_id}
											AND p.pago_detalle_id = {$pago_detalle_id}
											AND p.pago_tipo_id = 5
											AND p.deuda_tipo_id is NULL
											/*AND p.estado = 1*/";
							$periodo_saldo_a_favor = $mysqli->query($command)->fetch_assoc();
						?>
						<?php if($periodo_saldo_a_favor["abono"] != null){ ?>
						<tr>
							<th><?php echo $periodo_saldo_a_favor["nombre"];?></th>
							<th style="text-align:right"><?php echo number_format($periodo_saldo_a_favor["abono"], 2, ".", ",");?></th>
						</tr>
					<?php }
					}
					?>

				</tfoot>
			</table>
		</div>
		<?php
	}

	if($_POST["opt"]=="descarga_tabla_locales_excel"){
		header('Content-Encoding: UTF-8');
		header("Content-Type: application/vnd.ms-excel; charset=utf-8");
		$filename = "estados_de_cuenta.xls";
		header("Content-Disposition: attachment; filename=\"$filename\"");

		$output = "
		<table>
			<thead>
				<tr style='background-color:#1F4E78; color: #FFFFFF;'>
					<th>LOCAL ID</th>
					<th>LOCAL NOMBRE</th>
					<th>DEUDA</th>
				</tr>
			</thead>
			<tbody>
		";

		// Obtener el primer día del mes actual
		$fecha_inicio = date('Y-m-01');

		// Obtener el primer día del mes siguiente
		$fecha_fin = date('Y-m-01', strtotime('+1 month'));

		$locales_arr = array();
		$locales_query = "
			SELECT
				l.operativo,
				l.id,
				l.nombre AS 'local_nombre',
				CAST(
						(SELECT IF(SUM(d.monto),SUM(d.monto),0) AS monto FROM tbl_deudas d WHERE d.local_id = l.id AND d.estado = '1' AND d.periodo_inicio >= '".$fecha_inicio."')
						AS DECIMAL(20,2)
					) AS debe
			FROM tbl_locales l
			WHERE 
			l.id NOT IN (1)
			AND l.reportes_mostrar = '1'
			AND l.operativo in (1,2)
			AND l.red_id = 5
			GROUP BY l.id
			ORDER BY local_nombre ASC
		";
		$locales_query_data = $mysqli->query($locales_query);;

		foreach ($locales_query_data as $loc) {
			$output.= "
			<tr style='background-color:#E7E6E6;'>
				<td>".$loc['id']."</td>
				<td>".$loc['local_nombre']."</td>
				<td>".$loc['debe']."</td>
			</tr>
			";
		}
		$output.= "
            </tbody>
        </table>
        ";
		$output = iconv("UTF-8", "ISO-8859-1", $output);
		echo $output;
	}

	if ($_POST["opt"]=="detalle_estado_cuenta_descarga_excel") {
		header('Content-Encoding: UTF-8');
		header("Content-Type: application/vnd.ms-excel; charset=utf-8");
		$filename = "detalle_estados_de_cuenta.xls";
		header("Content-Disposition: attachment; filename=\"$filename\"");

		$local_id = htmlspecialchars($_POST['local_id']);
		$id_periodo_inicio = htmlspecialchars($_POST['id_periodo_inicio']);
		$id_periodo_fin = htmlspecialchars($_POST['id_periodo_fin']);
		$local_nombre = "";

		$sql_nombre = "SELECT nombre FROM tbl_locales where id = '$local_id' LIMIT 1";
		$sql_data = $mysqli->query($sql_nombre);
		while ($row = $sql_data->fetch_assoc()) {
			$local_nombre = $row['nombre'];
		}


		$output = "
		<table>
			<thead>
				<tr style='background-color:#1F4E78; color: #FFFFFF;'>
					<th colspan=4>$local_nombre</th>
				</tr>
				<tr>
				</tr>
				<tr style='background-color:#1F4E78; color: #FFFFFF;'>
					<th>PERIODO</th>
					<th>MONTO</th>
					<th>ABONO</th>
					<th>DEUDA</th>
				</tr>
			</thead>
			<tbody>
		";

		$detalle_query = "
			SELECT
				d.periodo_liquidacion_id,
				pl.fecha_inicio,
				pl.fecha_fin,
				IFNULL(SUM(d.monto), 0) AS monto,
				(
					SELECT IF(SUM(p.abono),SUM(p.abono),0) AS abono 
					FROM tbl_pagos p 
					WHERE p.local_id = l.id
					AND p.periodo_liquidacion_id = d.periodo_liquidacion_id
					AND p.estado = 1
				) AS abono
			FROM tbl_deudas d
			LEFT JOIN tbl_locales l ON (l.id = d.local_id)
			LEFT JOIN tbl_periodo_liquidacion pl on pl.id = d.periodo_liquidacion_id
			WHERE
				d.local_id = '$local_id'
				AND d.estado = '1'
				AND pl.id >= $id_periodo_inicio
				AND pl.id <= $id_periodo_fin
			GROUP BY
 				d.periodo_liquidacion_id DESC
			ORDER BY d.periodo_liquidacion_id DESC
		";
		$detalle_query_data = $mysqli->query($detalle_query);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}

		$total_monto = 0.00;
		$total_abono = 0.00;
		foreach ($detalle_query_data as $detalle_row) {
			$output.= "
			<tr style='background-color:#E7E6E6;'>
				<td>".$detalle_row['fecha_inicio']." - ".$detalle_row['fecha_fin']."</td>
				<td>".number_format($detalle_row['monto'], 2, ".", ",")."</td>
				<td>".number_format($detalle_row['abono'], 2, ".", ",")."</td>
				<td>".number_format(($detalle_row['monto'] - $detalle_row['abono']), 2, ".", ",")."</td>
			</tr>
			";
			$total_monto += $detalle_row['monto'];
			$total_abono += $detalle_row['abono'];
		}
		$total_monto = floatval(str_replace(',', '.', $total_monto));
		$total_abono = floatval(str_replace(',', '.', $total_abono));


		$abono_total = 0;
		$abono_restante_command = "
		SELECT sum(pd.monto) as abono_total
		FROM tbl_pagos_detalle pd
		WHERE
			pd.id in (
						SELECT distinct p.pago_detalle_id
						FROM tbl_pagos p
						WHERE
							p.local_id = '$local_id'
							AND p.estado = 1
							AND p.periodo_liquidacion_id >= $id_periodo_inicio
							AND p.periodo_liquidacion_id <= $id_periodo_fin
					)
			-- and pd.estado = 1
		";
		$abono_restante_query = $mysqli->query($abono_restante_command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		while($ar = $abono_restante_query->fetch_assoc()){
			$abono_total = $ar['abono_total'];
		}
		if ($abono_total > $total_abono) {
			$output.= "
			<tr style='background-color:#1F4E78; color: #FFFFFF;'>
				<td>Saldo a Favor sin Asignar</td>
				<td></td>
				<td>".($abono_total - $total_abono)."</td>
				<td></td>
			</tr>
			";
		}
		$output.= "
		<tr style='background-color:#1F4E78; color: #FFFFFF;'>
			<td>Total</td>
			<td>".number_format($total_monto, 2, ".", ",")."</td>
			<td>".number_format($abono_total, 2, ".", ",")."</td>
			<td>".number_format(($total_monto - $abono_total), 2, ".", ",")."</td>
		</tr>
		";

		$output.= "
            </tbody>
        </table>
        ";
		$output = iconv("UTF-8", "ISO-8859-1", $output);
		echo $output;
	}

	if($_POST["opt"]=="total_detalle_estado_cuenta_descarga_excel"){
		header('Content-Encoding: UTF-8');
		header("Content-Type: application/vnd.ms-excel; charset=utf-8");
		$filename = "total_detalle_estados_de_cuenta.xls";
		header("Content-Disposition: attachment; filename=\"$filename\"");

		$output = "
		<table>
			<thead>
				<tr style='background-color:#1F4E78; color: #FFFFFF;'>
					<th>LOCAL ID</th>
					<th>LOCAL NOMBRE</th>
					<th>DEUDA</th>
					<th>PAGADO</th>
					<th>SALDO</th>
					<th>LIQUIDACIONES</th>
					<th>DIFERENCIA</th>
				</tr>
			</thead>
			<tbody>
		";

		$searchQuery = '';
		if (isset($_POST['local_id']) && $_POST['local_id'] != '') {
			$local_id_search = htmlspecialchars(implode(',', $_POST['local_id']));
			if ($local_id_search > 0) {
				$searchQuery.= " AND l.id IN ($local_id_search)";
			}
		}
		if (isset($_POST['periodo_inicio'])) {
			$periodo_inicio_search = htmlspecialchars($_POST['periodo_inicio']);
		}
		if (isset($_POST['periodo_fin'])) {
			$periodo_fin_search = htmlspecialchars($_POST['periodo_fin']);
		}
		if (isset($_POST['estado_select']) && $_POST['estado_select'] != '') {
			$searchQuery.=	" AND l.operativo in (".$_POST['estado_select'].")";
		}

		$locales_query = "
		SELECT
			l.operativo,
			l.id,
			l.nombre as local_nombre,
			(
				IF(SUM(de.monto),SUM(de.monto),0)
			) AS debe,
			(
				SELECT IFNULL(sum(pd.monto), 0)
				FROM tbl_pagos_detalle pd
				WHERE
					pd.id in (
								SELECT distinct p.pago_detalle_id
								FROM tbl_pagos p
								WHERE
									p.local_id = l.id
									AND p.estado = 1
									AND p.periodo_liquidacion_id >= '$periodo_inicio_search'
									AND p.periodo_liquidacion_id <= '$periodo_fin_search'
							)
					-- and pd.estado = 1
			) AS haber,
			(SELECT
				sum(total_freegames + total_caja_web)
				FROM tbl_transacciones_cabecera  
				WHERE id IS NOT NULL 
				AND local_id = l.id 
				AND estado = '1' 
				AND canal_de_venta_id != '30'
				AND fecha >= (select fecha_inicio from tbl_periodo_liquidacion where id = '$periodo_inicio_search')
				AND fecha <= (select fecha_fin from tbl_periodo_liquidacion where id = '$periodo_fin_search')
				group by local_id
			) as liquidaciones,
			((SELECT
				sum(total_freegames + total_caja_web)
				FROM tbl_transacciones_cabecera  
				WHERE id IS NOT NULL 
				AND local_id = l.id 
				AND estado = '1' 
				AND canal_de_venta_id != '30'
				AND fecha >= (select fecha_inicio from tbl_periodo_liquidacion where id = '$periodo_inicio_search')
				AND fecha <= (select fecha_fin from tbl_periodo_liquidacion where id = '$periodo_fin_search')
				group by local_id
			) - 
			(
				IF(SUM(de.monto),SUM(de.monto),0)
			)) as diferencia
		FROM tbl_locales l
		left join tbl_deudas de on (de.local_id = l.id AND de.estado = 1)
		WHERE
			de.periodo_liquidacion_id >= '$periodo_inicio_search'
			AND de.periodo_liquidacion_id <= '$periodo_fin_search'
			AND l.red_id = 5
			AND l.id NOT IN (1)
			AND l.reportes_mostrar = '1'
			AND l.operativo in (1,2)
			{$searchQuery}
		GROUP BY l.id
		";
		$locales_query_data = $mysqli->query($locales_query);;

		// CANALES DE VENTA BINGO
		$deuda_command = "
			SELECT
				cab.canal_de_venta_id AS canal_de_venta_id,
				cab.fecha,
				l.id AS local_id,
				CAST(YEAR(cab.fecha) AS SIGNED) AS periodo_year,
				DATE_FORMAT(cab.fecha, '%m') AS periodo_mes,

				SUM(cab.total_freegames) AS part_fg,
				SUM(cab.total_pagado) AS total_pagado,
				CAST(SUM(cab.pagado_en_otra_tienda) - SUM(cab.pagado_de_otra_tienda) AS DECIMAL(10,2)) AS dif_tk,
				(SUM(cab.total_freegames)) AS web_total,
				(sum(total_pagado) - sum(pagado_en_otra_tienda)) AS pagados_en_su_punto_propios
				FROM
					tbl_transacciones_cabecera  cab
				LEFT JOIN tbl_locales l ON (l.id  = cab.local_id)
				WHERE
					cab.fecha >= (select fecha_inicio from tbl_periodo_liquidacion where id = '$periodo_inicio_search')
				AND cab.fecha <= (select fecha_fin from tbl_periodo_liquidacion where id = '$periodo_fin_search')
				AND cab.estado = 1
				AND cab.servicio_id in (1,3,9,13,15)
				AND l.reportes_mostrar = '1'
				AND l.red_id = 5
				and canal_de_venta_id = '30'
				{$searchQuery}
				GROUP BY 
					local_id ASC,
					canal_de_venta_id ASC";
		$deuda_query = $mysqli->query($deuda_command);
		if($mysqli_error = $mysqli->error){
			$return["error"]=true;
			$return["error_msg"]= "Error Servidor";
			$return["error_detalle"]= $mysqli_error;
			print_r(json_encode($return));
			exit();
		}

		$temp_arr = array();
		while($d=$deuda_query->fetch_assoc()){
			$temp_arr[$d["local_id"]] = ($d["part_fg"] - $d["pagados_en_su_punto_propios"]);
		}

		foreach ($locales_query_data as $loc) {
			$local_id = $loc['id'];
			$output.= "
			<tr style='background-color:#E7E6E6;'>
				<td>".$loc['id']."</td>
				<td>".$loc['local_nombre']."</td>
				<td>".$loc['debe']."</td>
				<td>".$loc['haber']."</td>
				<td>".number_format(($loc['debe'] - $loc['haber']), 2)."</td>
				<td>".number_format(($loc['liquidaciones'] + (isset($temp_arr[$local_id]) ? $temp_arr[$local_id] : 0)), 2)."</td>
				<td>".number_format(($loc['diferencia'] + (isset($temp_arr[$local_id]) ? $temp_arr[$local_id] : 0)), 2, '.', '')."</td>
			</tr>
			";
		}
		$output.= "
            </tbody>
        </table>
        ";
		$output = iconv("UTF-8", "ISO-8859-1", $output);
		echo $output;
	}
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
//print_r(json_encode($return));

?>