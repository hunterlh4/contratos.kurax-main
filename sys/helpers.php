<?php
$unwanted_array = [
	'Š' => 'S',
	'š' => 's',
	'Ž' => 'Z',
	'ž' => 'z',
	'À' => 'A',
	'Á' => 'A',
	'Â' => 'A',
	'Ã' => 'A',
	'Ä' => 'A',
	'Å' => 'A',
	'Æ' => 'A',
	'Ç' => 'C',
	'È' => 'E',
	'É' => 'E',
	'Ê' => 'E',
	'Ë' => 'E',
	'Ì' => 'I',
	'Í' => 'I',
	'Î' => 'I',
	'Ï' => 'I',
	'Ñ' => 'N',
	'Ò' => 'O',
	'Ó' => 'O',
	'Ô' => 'O',
	'Õ' => 'O',
	'Ö' => 'O',
	'Ø' => 'O',
	'Ù' => 'U',
	'Ú' => 'U',
	'Û' => 'U',
	'Ü' => 'U',
	'Ý' => 'Y',
	'Þ' => 'B',
	'ß' => 'Ss',
	'à' => 'a',
	'á' => 'a',
	'â' => 'a',
	'ã' => 'a',
	'ä' => 'a',
	'å' => 'a',
	'æ' => 'a',
	'ç' => 'c',
	'è' => 'e',
	'é' => 'e',
	'ê' => 'e',
	'ë' => 'e',
	'ì' => 'i',
	'í' => 'i',
	'î' => 'i',
	'ï' => 'i',
	'ð' => 'o',
	'ñ' => 'n',
	'ò' => 'o',
	'ó' => 'o',
	'ô' => 'o',
	'õ' => 'o',
	'ö' => 'o',
	'ø' => 'o',
	'ù' => 'u',
	'ú' => 'u',
	'û' => 'u',
	'ý' => 'y',
	'þ' => 'b',
	'ÿ' => 'y'
];
function indexnizer($n)
{
	global $unwanted_array;
	$r = $n;
	$r = strtr($r, $unwanted_array);
	// $r = preg_match('/^[a-zA-Z0-9 .]+$/', $r);
	$r = preg_replace("/[^A-Za-z0-9]/", "", $r);
	// $r = str_replace(" ", "", $r);
	$r = strtolower($r);
	return $r;
}
function get_curl($url, $headers = [], $json = true)
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = $json ? json_decode(curl_exec($ch), true) : curl_exec($ch);

	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);
	}
	curl_close($ch);

	return $result;
}

function post_curl($url, $request = [], $headers = [], $json = true)
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, ($json ? json_encode($request) : $request));

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = $json ? json_decode(curl_exec($ch), true) : curl_exec($ch);
	if (curl_errno($ch)) {
		echo 'Error:' . curl_error($ch);
	}
	curl_close($ch);

	return $result;
}

function upload_curl($url, $filepath, $credentials = false)
{
	$ch = curl_init();
	$fp = fopen($filepath, 'r');

	if ($credentials) {
		curl_setopt($ch, CURLOPT_USERPWD, $credentials["username"] . ':' . $credentials["password"]);
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_UPLOAD, 1);
	curl_setopt($ch, CURLOPT_INFILE, $fp);
	curl_setopt($ch, CURLOPT_INFILESIZE, filesize($filepath));

	if (curl_exec($ch) === false) {
		echo 'Curl error: ' . curl_error($ch);
	}

	curl_close($ch);
}

function array2string(&$array)
{
	if (is_array($array)) {
		if (count($array, COUNT_RECURSIVE) == 1) {
			$array = $array[0];
			return;
		}
		array_walk($array, 'array2string');
	}
}

function array2csv($array, $file = false)
{
	if ($file) {
		$fp = fopen($file, 'w');
		$i = 0;
		foreach ($array as $fields) {
			if ($i == 0) {
				fputcsv($fp, array_keys($fields));
			}
			fputcsv($fp, array_values($fields));
			$i++;
		}

		fclose($fp);
	}
}
function array2table($a)
{
?>
	<table border="1">
		<tr>
			<?php
			foreach ($a as $key => $value) {
				foreach ($value as $key => $value) {
			?> <th><?php echo $key; ?></th><?php
											}
											break;
										}
												?>
		</tr>
		<?php
		foreach ($a as $a_k => $a_val) {
		?>
			<tr>
				<?php
				foreach ($a_val as $a_val_key => $a_val_value) {
				?>
					<td>
						<?php echo $a_val_value; ?>
					</td>
				<?php
				}
				?>
			</tr>
		<?php
		}
		?>
	</table>
<?php
}
function unique_multidim_array($array, $key)
{
	$temp_array = array();
	$i = 0;
	$key_array = array();

	foreach ($array as $val) {
		if (!in_array($val[$key], $key_array)) {
			$key_array[$i] = $val[$key];
			$temp_array[$i] = $val;
		}
		$i++;
	}
	return $temp_array;
}

function get_numeral_sulfix($position)
{
	switch ($position) {
		case '1':
			return $position . "st";
		case '2':
			return $position . "nd";
		case '3':
			return $position . "rd";
		default:
			return $position . "th";
	}
}

function format_array_to_db($d)
{
	global $mysqli;
	$tmp = [];
	$nulls = ["null", "", false];
	foreach ($d as $k => $v) {
		if ($v === 0) {
			$tmp[$k] = $v;
		} elseif (in_array($v, $nulls)) {
			$tmp[$k] = "NULL";
		} else {
			if (is_float($v)) {
				$tmp[$k] = "'" . $v . "'";
			} elseif (is_int($v)) {
				$tmp[$k] = $v;
			} else {
				$v = str_replace(",", ".", $v);
				$tmp[$k] = "'" . trim($mysqli->real_escape_string($v)) . "'";
			}
		}
	}
	return $tmp;
}

function insert_multiple_data($content, $table, $has_timestamps)
{
	global $mysqli;

	$dupe_values = "";
	foreach ($chunks = array_chunk($content, 1000) as $chunk) {
		$values = "";
		foreach ($chunk as $row) {
			if ($has_timestamps) {
				$row["created_at"] = date('Y-m-d H:i:s');
				$row["updated_at"] = date('Y-m-d H:i:s');
			}

			$values .= '(';
			foreach ($row as $field) {
				$values .= is_null($field) ? 'null,' : '"' . trim($mysqli->real_escape_string($field)) . '",';
			}
			$values = substr($values, 0, -1);
			$values .= '),';
		}
		$values = substr($values, 0, -1);

		if ($dupe_values == "") {
			foreach (array_keys($row) as $field) {
				$dupe_values .= "{$field}=VALUES({$field}),";
			}
			$dupe_values = substr($dupe_values, 0, -1);
		}

		$query = "
			INSERT INTO {$table}(" . implode(',', array_keys($row)) . ")
			VALUES {$values}
			ON DUPLICATE KEY UPDATE
				{$dupe_values}
		";

		$mysqli->query($query);
		if ($mysqli->error) {
			echo "<pre>";
			var_dump($query, $mysqli->error);
			echo "</pre>";
		}
	}
}

function update_multiple_data($content, $table, $unique_field, $has_timestamps, $ignore_update = [])
{
	global $mysqli;

	foreach ($content as $row) {
		if ($has_timestamps) {
			$row["updated_at"] = date('Y-m-d H:i:s');
		}

		$values = "";
		foreach ($row as $key => $field) {
			if (!in_array($key, $ignore_update)) {
				$values .= $key . "=" . (is_null($field) ? 'NULL,' : '"' . trim($mysqli->real_escape_string($field)) . '",');
			}
		}
		$values = substr($values, 0, -1);

		$query = "
			UPDATE {$table}
			SET {$values}
			WHERE {$unique_field} = '" . $row[$unique_field] . "'
		";
		$mysqli->query($query);
		if ($mysqli->error) {
			echo "<pre>";
			var_dump($query, $mysqli->error);
			echo "</pre>";
		}
	}
}

function feed_database($contents, $table, $unique_field = false, $has_timestamps = false, $ignore_update = [])
{
	global $mysqli;

	if ($unique_field) {
		$contents_temp = $contents;
		$contents = [];

		$in_unique = "";
		foreach ($contents_temp as $content) {
			$in_unique .= "'" . $content[$unique_field] . "',";
		}
		$in_unique = substr($in_unique, 0, -1);

		$at_list = [];
		$query = "
			SELECT {$unique_field}
			FROM {$table}
			WHERE {$unique_field} IN ({$in_unique})
		";
		$result = $mysqli->query($query);
		if ($mysqli->error) {
			echo "<pre>";
			var_dump($query, $mysqli->error);
			echo "</pre>";
			die;
		}
		while ($r = $result->fetch_assoc()) $at_list[] = $r[$unique_field];

		foreach ($contents_temp as $content) {
			$contents[!in_array($content[$unique_field], $at_list) ? 'insert' : 'update'][] = $content;
		}

		if (isset($contents["insert"])) {
			insert_multiple_data($contents["insert"], $table, $has_timestamps);
		}
		if (isset($contents["update"])) {
			update_multiple_data($contents["update"], $table, $unique_field, $has_timestamps, $ignore_update);
		}
	} else {
		insert_multiple_data($contents, $table, $has_timestamps);
	}
}

function send_email($request)
{
	if (!isset($request["subject"])) {
		return "Definir Subject";
	}
	if (!isset($request["body"])) {
		return "Definir Body";
	}
	if (!isset($request["cc"]) && !isset($request["bcc"])) {
		return "Definir CC o BCC";
	}

	$mail = new PHPMailer(true);
	$mail->IsSMTP();
	$mail->isHTML(true);

	$mail->CharSet      	= isset($request["CharSet"]) ? $request["CharSet"] : 'utf-8';
	$mail->SMTPDebug    	= isset($request["SMTPDebug"]) ? $request["SMTPDebug"] : 1;
	$mail->SMTPAuth     	= isset($request["SMTPAuth"]) ? $request["SMTPAuth"] : true;
	$mail->Host         	= isset($request["Host"]) ? $request["Host"] : "smtp.gmail.com";
	$mail->Port         	= isset($request["Port"]) ? $request["Port"] : 465;
	$mail->SMTPSecure   	= isset($request["SMTPSecure"]) ? $request["SMTPSecure"] : "ssl";
	$mail->Username     	= isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
	$mail->Password     	= isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
	$mail->FromName     	= isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');
	$mail->SMTPKeepAlive 	= true;

	$mail->Subject  = $request["subject"];
	$mail->Body     = $request["body"];

	if (isset($request["cc"])) {
		foreach ($request["cc"] as $cc) {
			$mail->AddAddress($cc);
		}
	}

	if (isset($request["bcc"])) {
		foreach ($request["bcc"] as $bcc) {
			$mail->AddBCC($bcc);
		}
	}

	if (isset($request["attach"])) {
		if (is_array($request["attach"])) {
			for ($i = 0; $i < count($request["attach"]); $i++) {
				$mail->addAttachment($request["attach"][$i]);
			}
		} else {
			$mail->addAttachment($request["attach"]);
		}
	}

	$mail->Send();
	return true;
}
function send_email_v6($request)
{
	include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
	include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';
	if (!isset($request["subject"])) {
		return "Definir Subject";
	}
	if (!isset($request["body"])) {
		return "Definir Body";
	}
	if (!isset($request["cc"]) && !isset($request["bcc"])) {
		return "Definir CC o BCC";
	}

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();                                            // Send using SMTP
		$mail->Host       = isset($request["Host"]) ? $request["Host"] : "smtp.gmail.com";
		$mail->SMTPAuth   = isset($request["SMTPAuth"]) ? $request["SMTPAuth"] : true;
		$mail->Username   = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password   = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
		$mail->Port       = isset($request["Port"]) ? $request["Port"] : 465;
		$mail->CharSet    = isset($request["CharSet"]) ? $request["CharSet"] : 'utf-8';
		$mail->SMTPKeepAlive 	= true;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		$mail->Priority   = isset($request["Priority"]) ? $request["Priority"] : 2;
		if (isset($request["Priority"]) && isset($request["Priority"]) == 1) {
			$mail->AddCustomHeader("X-MSMail-Priority: Urgent");
			$mail->AddCustomHeader("Importance: High");
		}

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
				$mail->addBCC($bcc);
			}
		}

		if (isset($request["attach"])) {
			if (is_array($request["attach"])) {
				for ($i = 0; $i < count($request["attach"]); $i++) {
					$mail->addAttachment($request["attach"][$i]);
				}
			} else {
				$mail->addAttachment($request["attach"]);
			}
		}
		// Attachments
		// $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		// $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

		// Content
		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];

		$mail->send();
		return true;
		// echo 'Message has been sent';
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		return false;
	}
}

function send_email_alertas($request)
{
	if (!isset($request["subject"])) {
		return "Definir Subject";
	}
	if (!isset($request["body"])) {
		return "Definir Body";
	}
	if (!isset($request["cc"]) && !isset($request["bcc"])) {
		return "Definir CC o BCC";
	}
	$mail = new PHPMailer(true);
	try {
		$mail->isSMTP();                                            // Send using SMTP
		$mail->Host = isset($request["Host"]) ? $request["Host"] : "smtp.gmail.com";
		$mail->SMTPAuth = isset($request["SMTPAuth"]) ? $request["SMTPAuth"] : true;
		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = "ssl";        // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
		$mail->Port = isset($request["Port"]) ? $request["Port"] : 465;
		$mail->CharSet = isset($request["CharSet"]) ? $request["CharSet"] : 'utf-8';
		$mail->SMTPKeepAlive = true;
		$mail->From = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		$mail->Priority = isset($request["Priority"]) ? $request["Priority"] : 2;
		if (isset($request["Priority"]) && isset($request["Priority"]) == 1) {
			$mail->AddCustomHeader("X-MSMail-Priority: Urgent");
			$mail->AddCustomHeader("Importance: High");
		}
		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}
		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
				$mail->addBCC($bcc);
			}
		}
		if (isset($request["attach"])) {
			if (is_array($request["attach"])) {
				for ($i = 0; $i < count($request["attach"]); $i++) {
					$mail->addAttachment($request["attach"][$i]);
				}
			} else {
				$mail->addAttachment($request["attach"]);
			}
		}
		// Content
		$mail->isHTML(true);
		$mail->Subject = $request["subject"];
		$mail->Body = $request["body"];

		$mail->send();
		return true;
		// echo 'Message has been sent';
	} catch (Exception $e) {
		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		return false;
	}
}

function get_dni($dni)
{
	global $mysqli;

	if (preg_match("/^[0-9]{8}$/", $dni)) {
		$response = [];
		$query = "
			SELECT
				dni,
				nombres,
				apellido_paterno,
				apellido_materno
			FROM tbl_consultas_dni
			WHERE dni = '$dni'
		";
		$result = $mysqli->query($query);
		while ($r = $result->fetch_assoc()) $response = $r;

		if (empty($response)) {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, 'https://consulta.pe/api/reniec/dni');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["dni" => $dni]));
			curl_setopt($ch, CURLOPT_POST, 1);

			$headers = array();
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjQ2NTkwN2FiZjM1NGZiMDIyNzlkYjY0MWRhOGNkM2VlZGMxMWJhNjQ2MTM4ZTVhNDY0NjE4OWQ3YTg0YTFlMmY3ZjYzNDhhOTY3NzVjZDJhIn0.eyJhdWQiOiIxIiwianRpIjoiNDY1OTA3YWJmMzU0ZmIwMjI3OWRiNjQxZGE4Y2QzZWVkYzExYmE2NDYxMzhlNWE0NjQ2MTg5ZDdhODRhMWUyZjdmNjM0OGE5Njc3NWNkMmEiLCJpYXQiOjE1NjgwNzI1ODQsIm5iZiI6MTU2ODA3MjU4NCwiZXhwIjoxNTk5Njk0OTg0LCJzdWIiOiIxMDMyIiwic2NvcGVzIjpbInVzZS1yZW5pZWMiXX0.KBaznO9yAol4JQtz8OarTjGyOLLL_r1Fx-FUQIOTnV84oJtA2glvVdKqO86GSZ5BlIQ3wjW4vM87lqrpknD5mOQEO-ePb_SfI_6HD4WMr1SxMeXHRrCSLY6xySHKWnwT4jqfT-dipFkQgX4GSFVEtQuKbnt4s4IwE1FlAcna8NUdUO3AxHT1xVylwqksZA-wDyFnkZ09mEQ6Yk6SFUe9UWme9X03GyNW_ErcELQVtMWNyza54s1hkTRoq1NHpA8r3flVMpKHQODvzLB0Y54-VL7qddzos6O4GRqCaSlWZljIruNpNMMF5lNY5Npjd1aywYi9RJtTWXw4pdk2QYq7TcnuZUbuw00W7VpJ8CmsowZG8edErIlNyprT9i8txL48lsOOM2ImyoDOsVkLqMsGqtRWIusWVBCYsWzl8PVwkb99fXFfdNuilGyoBrsDE38GuSKeDyn7nXFU89JJxbKHaV-68zRvpodTT5eXzxvtngzNHvLWPATAoPiuqvQzxTnV4WqmbUrEdhEEtEH78fuM1Vy9upenajd9aSaXbM1mZ-v6ymFbzTuWuso-uzZWhlpThQsZyhp4UAxIYtt2CZlH1q5GG3NaUDtHjwuN0G4eTAcFR40cZMJwI2TnGokOZYTEYaHnVyj8b3DcI4cJkUe6jgg0slRLdXyfZJkyvr4x3a4';
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			$response = json_decode(curl_exec($ch), true);
			if (curl_errno($ch)) {
				echo 'Error:' . curl_error($ch);
			}
			curl_close($ch);

			if (isset($response["dni"]) && $response["dni"] == $dni) {
				$query = "
					INSERT INTO tbl_consultas_dni (
						dni,
						nombres,
						apellido_paterno,
						apellido_materno,
						caracter_verificacion,
						caracter_verificacion_anterior,
						created_at,
						updated_at
					) VALUES(
						'" . $response["dni"] . "',
						'" . $response["nombres"] . "',
						'" . $response["apellido_paterno"] . "',
						'" . $response["apellido_materno"] . "',
						'" . $response["caracter_verificacion"] . "',
						'" . $response["caracter_verificacion_anterior"] . "',
						'" . date('Y-m-d H:i:s') . "',
						'" . date('Y-m-d H:i:s') . "'
					)
				";
				$mysqli->query($query);

				if ($mysqli->error) {
					echo "<pre>";
					var_dump($query, $mysqli->error);
					echo "</pre>";
					die;
				}
			}
		}

		return $response;
	}
	return false;
}

/**
 * [generate_sheet description]
 * @param  string $filename 	File name that will be saved
 * @param  string $filepath 	Destination of file
 * @param  mixed $table    		data to be saved in file
 * @param  array $config   		configurable params (type, delimiter, enclosure, line_ending)
 * @return
 */
function generate_sheet($filename, $filepath, $table, $config = [])
{
	$doc = new PHPExcel();
	$doc->setActiveSheetIndex(0);
	$doc->getActiveSheet()->fromArray($table);

	try {
		$objWriter = PHPExcel_IOFactory::createWriter($doc, isset($config["type"]) ? $config["type"] : "CSV")
			->setDelimiter(isset($config["delimiter"]) ? $config["delimiter"] : ";")
			->setEnclosure(isset($config["enclosure"]) ? $config["enclosure"] : "")
			->setLineEnding(isset($config["line_ending"]) ? $config["line_ending"] : "\r\n");
		$objWriter->save($filepath);
	} catch (Exception $e) {
		echo "<pre>";
		var_dump($e);
		echo "</pre>";
		unlink($filepath);
	}
}

function cron_print_log($t, $r = false)
{
	if (!isset($GLOBALS["init_time"])) {
		$GLOBALS["init_time"] = microtime(true);
	}
	$l = "";
	if ($t) {
		$l .= print_r($t, true) . " at ";
	}
	$l .= date("Y-m-d H:i:s");
	$l .= " ";
	$l .= time_to_h(microtime(true) - $GLOBALS["init_time"]);
	$l .= " ";
	$l .= size_to_h(memory_get_usage());
	$l .= "\n";
	if ($r) {
		return $l;
	}
	echo $l;
}
function size_to_h($size)
{
	$unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
	return @number_format($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . '' . $unit[$i];
}
function time_to_h($t)
{
	$hours = (int)($t / 60 / 60);
	$minutes = (int)($t / 60) - $hours * 60;
	$seconds = (int)$t - $hours * 60 * 60 - $minutes * 60;
	$mili = (int)$t - $hours * 60 * 60 - $minutes * 60 - $seconds * 60;
	return "(" . $hours . "h" . $minutes . "m" . $seconds . "s" . $mili . "ms" . ")";
}
function log_clear($dir)
{
	array_map('unlink', glob($dir . "/*.log")); // elimina todos los logs
}
function log_init($dir, $file = false)
{
	if (!array_key_exists("session_id", $GLOBALS)) {
		$GLOBALS["log_name"] = "session";
		$GLOBALS["session_id"] = date("Ymd_His") . "_" . $GLOBALS["log_name"] . "_" . md5(rand(0, getrandmax()));
	}
	// $ext_game = str_replace(strstr(substr(strstr($_SERVER["REQUEST_URI"],"/"),1),"/"),"",substr($_SERVER["REQUEST_URI"],1));
	$GLOBALS["log_dir"] = $dir;
	if (!is_dir($GLOBALS["log_dir"])) {
		mkdir($GLOBALS["log_dir"]);
		chmod($GLOBALS["log_dir"], 0777);
	}
	if ($file) {
		$GLOBALS["log_file"] = $GLOBALS["log_dir"] . $file;
	} else {
		$GLOBALS["log_file"] = $GLOBALS["log_dir"] . $GLOBALS["session_id"] . '.log';
	}
	if (file_exists($GLOBALS["log_file"])) {
		$GLOBALS["log"] = fopen($GLOBALS["log_file"], 'a');
	} else {
		$GLOBALS["log"] = fopen($GLOBALS["log_file"], 'w');
		log_write("log_init");
	}
	chmod($GLOBALS["log_file"], 0777);
}
function log_rename($n = false, $old = false)
{
	if ($n) {
		if (!$old) {
			if (array_key_exists("log_name", $GLOBALS)) {
				$old = $GLOBALS["log_name"];
			}
		}
		$GLOBALS["log_name"] = $n;
		// $new_log_file = $GLOBALS["log_dir"].date("Ymd_His")."_".$n."_".md5(rand(0,getrandmax())).'.log';
		$new_log_file = str_replace($old, $n, $GLOBALS["log_file"]);
		log_write("log_rename:" . $GLOBALS["log_file"] . " > " . $new_log_file);
		fclose($GLOBALS["log"]);
		rename($GLOBALS["log_file"], $new_log_file);
		$GLOBALS["log_file"] = $new_log_file;
		$GLOBALS["log"] = fopen($GLOBALS["log_file"], 'a+');
	}
}
function log_end()
{
	log_write("log_end");
	fclose($GLOBALS["log"]);
}
function log_write($t = false, $file = false)
{
	if (!array_key_exists("init_time", $GLOBALS)) {
		$GLOBALS["init_time"] = microtime(true);
	}
	$l = "";
	if ($t) {
		$l .= print_r($t, true) . " at ";
	}
	$l .= date("Y-m-d H:i:s");
	$l .= " ";
	$l .= time_to_h(microtime(true) - $GLOBALS["init_time"]);
	$l .= " ";
	$l .= size_to_h(memory_get_usage());
	$l .= "\n";
	if ($file) {
		if ($file == "output") {
			return print_r($l, true);
		} else {
			fwrite($file, $l);
		}
	} else {
		if (array_key_exists("log", $GLOBALS)) {
			fwrite($GLOBALS["log"], $l);
		} else {
			print_r($l);
		}
	}
	if (array_key_exists("log_view", $GLOBALS)) {
		print_r($l);
	}
}
function zip_files($zipname = false, $files = [], $remove_originals = false)
{
	if (!$zipname) {
		return false;
	}
	$zip = new ZipArchive();
	if ($zip->open($zipname, ZipArchive::CREATE) !== TRUE) {
		exit("ERROR: {$zipname} no se puede abrir!\n");
	}
	if (!is_array($files)) {
		$files = [$files];
	}
	foreach ($files as $key => $file) {
		if (file_exists($file)) {
			//$zip->addFile($file);
			$new_filename = substr($file, strrpos($file, '/') + 1);
			$zip->addFile($file, $new_filename);
		} else {
			exit("ERROR: {$file} no existe!\n");
		}
	}
	$zip->close();
	if ($remove_originals) {
		foreach ($files as $key => $file) {
			if (!unlink($file)) {
				exit("ERROR: {$file} no se puede eliminar!\n");
			}
		}
	}
	return $zipname;
}
?>