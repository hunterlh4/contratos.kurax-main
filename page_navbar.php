<nav class="navbar navbar-fixed-top top-menu">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a href="./" class="navbar-brand navbar-brand-cover">
				<div class="navbar-brand-big">
					<img src="images/logo_kurax.png" alt="Gestion AT" class="logo_open">
				</div>
				<div class="navbar-brand-small">
					<img src="images/kuraxCol.png" alt="Gestion AT" class="logoCol">
				</div>
			</a>
		</div>
		<div class="navbar-top">
			<ul class="nav navbar-nav">
				<li>
					<a href="#" class="sidebar-collapse">
						<i class="icon icon-inline fa fa-toggle-left muted"></i>
					</a>
				</li>
			</ul>
		</div>
		<div id="navbar" class="navbar-collapse collapse">

			<ul class="nav navbar-nav navbar-right">
				<li class="mantenimiento_box hidden">
					<a class="sidebar-toggle bg-danger">
						<i class="glyphicon glyphicon-warning-sign"></i> <strong>Mantenimiento programado del servidor: Jueves 08 de Marzo desde las 15:00Hrs hasta las 18:00Hrs.</strong> <i class="glyphicon glyphicon-warning-sign"></i>
					</a>
				</li>
				<li>
					<a title="DB: <?php echo $con_host; ?>" href="#" class="sidebar-toggle" data-sidebar=".sidebar-users">
						La sesión vence el <?php echo date("Y-m-d H:i:s", $cookie_expire); ?>
						<i class="glyphicon glyphicon-info-sign"></i>
					</a>
				</li>
				<?php if ($login["area_id"] == 21 && in_array($login["cargo_id"], [4, 5])): ?>
					<li>
						<a href="#" class="sidebar-toggle" data-sidebar=".sidebar-users">
							<?= $login["local_name"] ?? "" ?>
						</a>
					</li>
				<?php endif; ?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">
						<div class="profile-avatar circle">
							<img src="/images/default_avatar.png">
						</div>
						<span
							class="user-name"
							data-user-names="<?php echo $login["nombre"]; ?> <?php echo $login["apellido_paterno"]; ?>"
							data-user-mail="<?php echo $login["correo"]; ?>">
							<?php echo $login["nombre"]; ?> <?php echo $login["apellido_paterno"]; ?> [<?php echo $login["id"] ?>]</span>
					</a>
					<ul class="dropdown-menu dropdown-menu-right">
						<li class="user_change_pass_btn"><a><i class="glyphicon glyphicon-lock"></i> Cambiar contraseña</a></li>
						<?php
						//if ($login["area_id"] == 21 && in_array($login["cargo_id"],[4,5])) { //editado por Torito
						if ($login["usuario_locales"]) {
							if (count($login["usuario_locales"]) > 1) {
						?>
								<li id="btn_cashier_select_location"><a><i class="glyphicon glyphicon-retweet"></i> Escoger local</a></li>
						<?php
							}
						}
						?>
						<li><a href="./?action=logout"><i class="glyphicon glyphicon-log-out"></i> Salir</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</nav>