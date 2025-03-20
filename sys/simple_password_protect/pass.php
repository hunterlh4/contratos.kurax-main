<?php
function pass_me($p='',$allow_logout=false)
{
	$site = str_replace("index.php", "", $_SERVER["SCRIPT_NAME"]);
	// if(array_key_exists('QUERY_STRING', $_SERVER)){
	// 	$site.='?'.$_SERVER['QUERY_STRING'];
	// }
	$cookie_expire = time()+(60*10);
	$login = false;
	if(isset($_COOKIE["spp".md5($site)])){
		if(password_verify($p,$_COOKIE['spp'.md5($site)])){
			$login = true;
		}else{
			setcookie("spp".md5($site), "xxx", time()-1000,"/",$_SERVER["SERVER_NAME"],($_SERVER["REQUEST_SCHEME"]=="https"?true:false),true);
		}
	}
	if(isset($_GET["getpass"])){
		echo $p; exit();
	}
	if(isset($_POST["login"])){
		if($_POST['p']==$p){
			setcookie("spp".md5($site), password_hash($p,PASSWORD_DEFAULT), $cookie_expire,"/",$_SERVER["SERVER_NAME"],($_SERVER["REQUEST_SCHEME"]=="https"?true:false),true);
			if(array_key_exists('QUERY_STRING', $_SERVER)){
				$site.='?'.$_SERVER['QUERY_STRING'];
			}
			header("Location: ".$site);
		}else{
			echo "no pass";
			setcookie("spp".md5($site), "xxx", time()-1000,"/",$_SERVER["SERVER_NAME"],($_SERVER["REQUEST_SCHEME"]=="https"?true:false),true);
		}
	}
	if(isset($_POST['logout']) || isset($_GET['logout'])){
		setcookie("spp".md5($site), "xxx", time()-1000,"/",$_SERVER["SERVER_NAME"],($_SERVER["REQUEST_SCHEME"]=="https"?true:false),true);
		header("Location: ".$site);
	}
	if($login){
		if($allow_logout){
			?>
			<form method="POST" action="<?php echo $site;?>">
				<button type="submit" name="logout" value="logout">Cerrar Sesión</button>
			</form>
			<?php
		}
	}else{
		?>
		<form method="POST" action="<?php echo $site;?>">
			<input type="text" placeholder="Contraseña" name="p" required="required" autocomplete="on" autofocus="autofocus" maxlength="32" style="width: 300px;">
			<button type="submit" name="login" value="login">Ingresar</button>
		</form>
		<?php
		exit();
	}
}

?>