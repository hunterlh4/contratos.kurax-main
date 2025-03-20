<div class="fullscreen">
	<div class="vertical-middle">
		<div class="content container">
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-4 col-sm-offset-3 col-md-offset-4">
					<div class="panel">
						<div class="panel-body">
							<div class="image mb text-center login_form_logo_holder">
								<a href="./">
									<img src="images/logo_kurax.png" alt="CasperoBoard">
								</a>
							</div>
							<?php
							// print_r($logout);
							if($logout){
								if($logout["logout_ip"]=="pass_restored"){
									?>
									<div class="btn-warning col-xs-12">
										La contraseña del usuario <strong><?php echo $logout["usuario"];?></strong> ha sido restaurada, usa tu nueva contraseña o comunicate con tu jefe directo.
									</div>
									<?php
								}elseif($logout["logout_ip"]=="user_changed"){
									?>
									<div class="btn-warning col-xs-12">
										El usuario ha cambiado, por favor vuelve a iniciar sesión.
									</div>
									<?php
								}else{
									?>
									<div class="btn-danger col-xs-12">
										El usuario <strong><?php echo $logout["usuario"];?></strong> ha iniciado sesión desde el IP: <strong><?php echo $logout["logout_ip"];?></strong> a las: <strong><?php echo $logout["logout_datetime"];?></strong>  
									</div>
									<?php									
								}
							}
							if($login_return=="inactivo"){
								?>
									<div class="btn-danger col-xs-12">
									El usuario <strong><?php echo $login_usuario;?></strong> se encuentra <strong>inactivo.</strong>  
								</div>
									<?php
							}
							if (isset($_SESSION["tmp_code"])&&$_SESSION["tmp_code"]=="no_code") {
								?>
								<form method="post" action="./" id="login_form">
								    <input type="hidden" name="username" required="required" value="<?php echo($_SESSION["tmp_user"])?>" autocomplete="off">
									<input type="hidden" name="password" required="required" value="<?php echo($_SESSION["tmp_pass"]) ?>" autocomplete="off">
									<input type="hidden" name="action" value="login">
									
									<input type="text"  class="form-control input-sm" placeholder="codigo" name="code" required="required" value="" autocomplete="off">
									<br>
									<div class="form-group d-flex" style="display: flex;flex-direction: row;justify-content: space-between;">
										<button type="button" onclick="location.reload();" class="btn btn-warning float-left">
												<i class="glyphicon glyphicon-remove"></i> Cancelar
										</button>
										<button type="submit" name="login_submit_btn" value="1" class="btn btn-primary float-rigth">
											<i class="glyphicon glyphicon-log-in"></i> Verificar
										</button>
									</div>
								</form>
								<?php								
								unset($_SESSION["tmp_user"]);
								unset($_SESSION["tmp_pass"]);
								unset($_SESSION["tmp_code"]);
							}	else{
								?>
							<form method="post" action="./" id="login_form">
								<?php if(array_key_exists("login_HTTP_REFERER", $session_array)){ ?>
									<input type="hidden" name="login_HTTP_REFERER" value="<?php echo $session_array["login_HTTP_REFERER"];?>">
								<?php } ?>
								<input type="hidden" name="action" value="login">
								<div class="form-group <?php if($login_return=="nouser"){ ?> has-error<?php } ?>">
									<label for="username">Usuario</label>
									<input type="text" oninput="validarTextoUsuarioLogin(this)" maxlength="25" id="username" class="form-control input-sm" placeholder="Usuario" name="username" required="required" autofocus="autofocus" value="<?php if(array_key_exists("username", $session_array)){ echo $session_array["username"]; }?>" autocomplete="off">
									<?php if($login_return=="nouser"){ ?>
										<span id="helpBlock2" class="help-block alert-warning">Usuario incorrecto</span>
									<?php } ?>
								</div>
								<div class="form-group <?php if($login_return=="nopass"){ ?> has-error<?php } ?>">
									<label for="password">Contraseña</label>
									<input type="password" id="password" class="form-control input-sm" placeholder="Contraseña" name="password" required="required" value="" autocomplete="off">
									<input type="hidden" id="code" name="code">
									<?php if($login_return=="nopass"){ ?>
										<span id="helpBlock2" class="help-block alert-warning">Contraseña Incorrecta</span>
									<?php } ?>
									<?php if($login_return == "restrict"){ ?>
										<span id="helpBlock2" class="help-block alert-warning text-danger">Ip restringida <?php echo $ip ?></span>
									<?php } ?>
									<?php if($login_return == "nouservalid"){ ?>
										<span id="helpBlock2" class="help-block alert-warning text-danger">Usuario ingresado no es válido</span>
									<?php } ?>
								</div>
								<div class="form-group pull-right">
									<button type="submit" name="login_submit_btn" value="1" class="btn btn-primary"><i class="glyphicon glyphicon-log-in"></i> Iniciar Sesión</button>
								</div>
							</form>
								<?php 
							}						
							?>
							
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
