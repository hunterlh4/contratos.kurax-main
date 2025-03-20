<div class="sidebar">
	<div class="height100p custom-scroll">
		<div id="contSearch">
			<!--span><i class="fa fa-search" aria-hidden="true"></i></span-->
			<input type="text" id="searchText" name="searchText" value="" placeholder="Busqueda..." autocomplete="off">
			<div id="contendMsj">
				<span id="searchMsj"><a><i class="fa fa-window-close-o" aria-hidden="true"></i></a></span>
			</div>
		</div>
		<ul class="nav-sidebar">
			<?php
			$sidebar_id_list = array("id" => 0);
			$sidebar_relacion_id_list = array();
			$sidebar_relacion_id_list["relacion_id"] = false;
			if (isset($_GET["sec_id"]) && isset($_GET["sub_sec_id"])) {
				$sidebar_id_command = "SELECT id FROM tbl_menu_sistemas WHERE sec_id = '" . $_GET["sec_id"] . "' AND sub_sec_id= '" . $_GET["sub_sec_id"] . "'";
				// print $sidebar_id_command;
				$sidebar_id_query = $mysqli->query($sidebar_id_command);

				while ($id = $sidebar_id_query->fetch_assoc()) {
					$sidebar_id_list["id"] = $id;
				}

				$sidebar_relacion_id_command = "SELECT relacion_id FROM tbl_menu_sistemas WHERE sec_id = '" . $_GET["sec_id"] . "' AND sub_sec_id= '" . $_GET["sub_sec_id"] . "'";
				// print $sidebar_relacion_id_command;
				$sidebar_relacion_id_query = $mysqli->query($sidebar_relacion_id_command);
				// $sidebar_relacion_id_list=array();
				while ($relacion_id = $sidebar_relacion_id_query->fetch_assoc()) {
					$sidebar_relacion_id_list = $relacion_id;
				}
			}

			$sidebar_command = "SELECT id, grupo_id,titulo,descripcion,relacion_id,relacion_grupo_id,nombre_formulario,sistema_id,tipo,sec_id,sub_sec_id,icon,estado,ord
									FROM tbl_menu_sistemas
									WHERE estado = '1'
									ORDER BY ord ASC";
			$sidebar_query = $mysqli->query($sidebar_command);
			$sidebar_db_list = array();
			while ($li = $sidebar_query->fetch_assoc()) {
				$sidebar_db_list[] = $li;
			}

			$sidebar_list = array();
			foreach ($sidebar_db_list as $dbdb_k => $sbdb_val) {
				if ($sbdb_val["relacion_id"] == 0) {
					$sbk = 0;
					foreach ($sidebar_db_list as $dbdb_ks => $sbdb_vals) {
						if ($sbdb_val["id"] == $sbdb_vals["relacion_id"]) {
							if ($sbdb_vals["grupo_id"] != 4) {

								$sbdb_val["submenu"][$sbk] = $sbdb_vals;
								$sbk++;
							}
						}
					}
					$sidebar_list[] = $sbdb_val;
				}
			}
			foreach ($sidebar_list as $sb_key => $sb_val) {
				if ($sb_val["grupo_id"] == 1) {
					if (array_key_exists($sb_val["id"], $usuario_permisos)) {
						if (in_array("view", $usuario_permisos[$sb_val["id"]])) {
			?>
							<li
								class="<?php
										if (array_key_exists("submenu", $sb_val)) {
										?> sub <?php
													if ($sidebar_relacion_id_list["relacion_id"] == $sb_val["id"]) { ?>active open<?php }
																												} else {
																													if ($sub_sec_id) {
																														if ($sec_id == $sb_val["sec_id"] && $sub_sec_id == $sb_val["sub_sec_id"]) { ?> active<?php }
																														} else {
																															if ($sec_id == $sb_val["sec_id"]) { ?> active<?php }
																														}
																													}
																							?> "
								id="<?php echo $sb_val["id"]; ?>"
								title="<?php echo $sb_val["descripcion"]; ?>">
								<a <?php
									if (array_key_exists("submenu", $sb_val)) {
									?>href="#" class="sub-toggle" <?php
																		} else {
																			if ($sb_val["sec_id"] == "external_link") {
																				switch ($sb_val["sub_sec_id"]) {
																					case "jira-servicedesk":
																						echo ' href="https://apuestatotal.atlassian.net/servicedesk/customer/portals"';
																						break;
																					case "at-snacks":
																						echo ' href="http://atsnacks.apuestatotal.com/?gsession=' . $login["sesion_cookie"] . '"';
																						break;
																					default:
																						break;
																				}
																				echo ' target="_blank"';
																				// echo $sb_val["sub_sec_id"];
																			} else if ($sb_val["sec_id"] == "aula_virtual") {
																				echo 'href="';
																				echo 'https://aula-at.com/login/index.php';
																				echo '" ';
																				echo 'target=';
																				echo '_blank';
																				echo '"';
																			} else {
																				echo 'href="./?';
																				echo ($sb_val["sec_id"] ? "&amp;sec_id=" . $sb_val["sec_id"] : false);
																				echo ($sb_val["sub_sec_id"] ? "&amp;sub_sec_id=" . $sb_val["sub_sec_id"] : false);
																				echo '"';
																			}
																		}
																			?>>
									<?php if ($sb_val["icon"] == "fa-help") { ?>
										<span style="width: 32px;height: 32px; margin-right: 6px;">
											<img src="images/help_2.png" style="width: 36px;margin-left: -2px;">
										</span>
									<?php } else if ($sb_val["icon"] == "fa-torito") { ?>
										<img src=images/torito.png style="width: 32px;height: 28px;">
									<?php } else if ($sb_val["icon"] == "fa-aula-virtual-at") {
									?>
										<img src=images/aula_virtual_at.png style="width: 32px;height: 28px;">
									<?php
									} else { ?>
										<i class="icon icon-inline <?php echo $sb_val["icon"]; ?>"></i>
									<?php } ?>
									<span class="title"><?php echo $sb_val["titulo"]; ?></span>
								</a>
								<?php
								if (array_key_exists("submenu", $sb_val)) {
								?>
									<ul class="sub-menu wd-230" data-menu-title="<?php echo $sb_val["titulo"]; ?>">
										<?php
										foreach ($sb_val["submenu"] as $sm_key => $sm_val) {

											$hasPermission = false;

											// Verificación adicional para el caso de "comprobante" y "reportes"
											if ($sm_val["sec_id"] == "comprobante" && $sm_val["sub_sec_id"] == "reporte") {
												if (
													(array_key_exists($sm_val["id"], $usuario_permisos) && in_array("view", $usuario_permisos[$sm_val["id"]])) ||
													(array_key_exists(399, $usuario_permisos) && in_array("btn_comp_registrar", $usuario_permisos[399]))
												) {
													$hasPermission = true;
												}
											} else if (array_key_exists($sm_val["id"], $usuario_permisos) && in_array("view", $usuario_permisos[$sm_val["id"]])) {
												$hasPermission = true;
											}

											// Si tiene permiso, renderizar el submenú
											if ($hasPermission) {
										?>
												<li
													class="<?php
															if (
																$sub_sec_id == $sm_val["sub_sec_id"]
																&& ($sidebar_id_list["id"] == $sm_val["id"])
															) { ?> active<?php } ?> "
													data-toggle="tooltip"
													data-placement="right"
													relacion-id="<?php echo $sm_val["relacion_id"]; ?>"
													title="<?php echo $sm_val["descripcion"]; ?>">
													<a href="./?sec_id=<?php echo $sm_val["sec_id"]; ?>&amp;sub_sec_id=<?php echo $sm_val["sub_sec_id"]; ?>">
														<i class="icon icon-inline <?php echo $sm_val["icon"]; ?>"></i>
														<span class="title"><?php echo $sm_val["titulo"]; ?></span>
													</a>
												</li>
										<?php
											}
										}
										?>
									</ul>
								<?php
								}
								?>
							</li>
				<?php
						}
					}
				}
			}

			if ($admin) {
				?>
				<hr>
				<li class="<?php if (!$admin) { ?>hidden <?php } ?> sub <?php if ($sec_id == "adm_mantenimientos") { ?> active <?php } ?> open" id="menu_adm_mantenimientos">
					<a href="#" class="sub-toggle">
						<i class="icon icon-inline icon-inline fa fa-list-alt"></i> <span class="title"> Mantenimientos</span>
					</a>
					<?php
					$list_m = array();
					$list_query = $mysqli->query("SELECT id, nombre, tabla, opciones, orden, estado
											FROM adm_mantenimientos
											WHERE estado = '1'
											ORDER BY orden ASC");
					while ($li = $list_query->fetch_assoc()) {
						$list_m[] = $li;
					}
					?>
					<ul class="sub-menu" data-menu-title="Mantenimientos">
						<?php
						foreach ($list_m as $key => $value) {
						?>
							<li
								class="<?php if ($sub_sec_id == $value["id"]) { ?> active<?php } ?>"
								data-toggle="tooltip"
								data-placement="right"
								title="Administracion de <?php echo $value["nombre"]; ?>">
								<a href="./?sec_id=adm_mantenimientos&amp;sub_sec_id=<?php echo $value["id"]; ?>">
									<i class="icon icon-inline fa fa-address-card-o"></i>
									<span class="title"><?php echo $value["id"]; ?> - <?php echo $value["nombre"]; ?></span>
								</a>
							</li>
						<?php
						}
						?>
					</ul>
				</li>
				<li class="<?php if (!$admin) { ?>hidden <?php } ?> sub <?php if ($sec_id == "adm") { ?> active <?php } ?>" id="menu_adm">
					<a href="#" class="sub-toggle">
						<i class="icon icon-inline icon-inline fa fa-wrench"></i> <span class="title"> Administración</span>
					</a>
					<ul class="sub-menu" data-menu-title="Mantenimientos">
						<li class="<?php if ($sub_sec_id == "mantenimientos") { ?> active<?php } ?>">
							<a href="./?sec_id=adm&amp;sub_sec_id=mantenimientos">
								<i class="icon icon-inline fa fa-address-card-o"></i>
								<span class="title">Mantenimientos</span>
							</a>
						</li>
					</ul>
				</li>
			<?php
			} ?>
		</ul>
		<div class=""><br><br><br></div>
	</div>
</div>