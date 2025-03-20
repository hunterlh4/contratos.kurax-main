var contadorLocales = document.getElementById("contador_locales");
var de = document.getElementById("de");
var cantidadLocales = document.getElementById("cantidad_locales");
var span_locales = document.getElementById("span_locales");
var mensaje_cargando = document.getElementById("mensaje-cargando");

var item_config = {};

function sec_caja2(){
	$("#local_id").select2();
    if (contadorLocales && de && cantidadLocales && span_locales && mensaje_cargando) {
        contadorLocales.style.display = "none";
        de.style.display = "none";
        cantidadLocales.style.display = "none";
        span_locales.style.display = "none";
		mensaje_cargando.style.display = "none";
        sec_caja_events2();
    }
}

function sec_caja_auditoria_events2(data_export) {
	// console.log("sec_caja_auditoria_events");
	$(".detalle_btn")
		.off()
		.click(function (event) {
			loading(true);
			var btn_data = $(this).data();
			$.each(btn_data, function (config_index, config_val) {
				var ls_index = "sec_caja_reporte_" + config_index;
				localStorage.setItem(ls_index, config_val);
			});
			localStorage.setItem("sec_caja_auditoria_detalle", "true");
			window.open("/?sec_id=caja&sub_sec_id=reporte#ste=.table_container");
			setTimeout(function () {
				loading();
			}, 1000);
			console.log(btn_data);
		});

	$(".btn_export_caja_auditoria2")
		.off()
		.on("click", function (e) {
			loading(true);
			var tabla = document.getElementsByName('tbl_auditoria_name')[0].getElementsByTagName('tbody')[0];
			var filas = tabla.getElementsByTagName('tr');
			var datos = [];
	
			for (var i = 0; i < filas.length; i++) {
				var fila = filas[i];
				var celdas = fila.getElementsByTagName('td');
				var filaData = {
					local_nombre: celdas[0].innerText,
					fecha_operacion: celdas[1].innerText,
					sistema_resultado: celdas[2].innerText,
					cajero_resultado: celdas[3].innerText,
					diferencia_resultado: celdas[4].innerText,
					sistema_pagos_manuales: celdas[5].innerText,
					cajero_pagos_manuales: celdas[6].innerText,
					diferencia_pagos_manuales: celdas[7].innerText,
					sistema_apuestas_deportivas: celdas[8].innerText,
					cajero_apuestas_deportivas: celdas[9].innerText,
					diferencia_apuestas_deportivas: celdas[10].innerText,
					sistema_billeteros: celdas[11].innerText,
					cajero_billeteros: celdas[12].innerText,
					diferencia_billeteros: celdas[13].innerText,
					sistema_goldenrace: celdas[14].innerText,
					cajero_goldenrace: celdas[15].innerText,
					diferencia_goldenrace: celdas[16].innerText,
					sistema_carreradecaballos: celdas[17].innerText,
					cajero_carreradecaballos: celdas[18].innerText,
					diferencia_carreradecaballos: celdas[19].innerText,
					sistema_dsvirtualgaming: celdas[20].innerText,
					cajero_dsvirtualgaming: celdas[21].innerText,
					diferencia_dsvirtualgaming: celdas[22].innerText,
					sistema_bingo: celdas[23].innerText,
					cajero_bingo: celdas[24].innerText,
					diferencia_bingo: celdas[25].innerText,
					sistema_web: celdas[26].innerText,
					cajero_web: celdas[27].innerText,
					diferencia_web: celdas[28].innerText,
					sistema_web_televentas: celdas[29].innerText,
					cajero_web_televentas: celdas[30].innerText,
					diferencia_web_televentas: celdas[31].innerText,
					sistema_cash: celdas[32].innerText,
					cajero_cash: celdas[33].innerText,
					diferencia_cash: celdas[34].innerText,
					sistema_apuestas_deportivas_altenar: celdas[35].innerText,
					cajero_apuestas_deportivas_altenar: celdas[36].innerText,
					diferencia_apuestas_deportivas_altenar: celdas[37].innerText,
					sistema_kasnet: celdas[38].innerText,
					cajero_kasnet: celdas[39].innerText,
					diferencia_kasnet: celdas[40].innerText,
					sistema_disashop: celdas[41].innerText,
					cajero_disashop: celdas[42].innerText,
					diferencia_disashop: celdas[43].innerText,
					sistema_atsnacks: celdas[44].innerText,
					cajero_atsnacks: celdas[45].innerText,
					diferencia_atsnacks: celdas[46].innerText,
					sistema_devoluciones: celdas[47].innerText,
					cajero_devoluciones: celdas[48].innerText,
					diferencia_devoluciones: celdas[49].innerText,
					sistema_devoluciones_carrera_caballos: celdas[50].innerText,
					cajero_devoluciones_carrera_caballos: celdas[51].innerText,
					diferencia_devoluciones_carrera_caballos: celdas[52].innerText,
					sistema_torito: celdas[53].innerText,
					cajero_torito: celdas[54].innerText,
					diferencia_torito: celdas[55].innerText,
					sistema_nsoft: celdas[56].innerText,
					cajero_nsoft: celdas[57].innerText,
					diferencia_nsoft: celdas[58].innerText,
					sistema_kiron: celdas[59].innerText,
					cajero_kiron: celdas[60].innerText,
					diferencia_kiron: celdas[61].innerText,
					resultado_sistema: celdas[62].innerText,
					resultado_voucher: celdas[63].innerText,
					sistema_devoluciones: celdas[64].innerText,
					sistema_devoluciones_carrera_caballos: celdas[65].innerText,
					sistema_pagos_manuales: celdas[66].innerText,
					diferencia: celdas[67].innerText
				};
				datos.push(filaData);
			}
			
			$.ajax({
				url: '/export/caja_auditoriav2_reporte.php',
				type: 'post',
				data: { datos: JSON.stringify(datos), data_export: JSON.stringify(data_export) },
			})
			.done(function (dataresponse) {
				loading(false);
				var obj = JSON.parse(dataresponse);
				window.open(obj.path);
				loading();
			});
		});

	$(".btn_export_caja_auditoria2_all")
		.off()
		.on("click", function (e) {
			var tabla = document.getElementsByName('tbl_auditoria_name')[1].getElementsByTagName('tbody')[0];
			// var tabla = document.getElementById('tabla-body');
			var filas = tabla.getElementsByTagName('tr');
			var datos = [];
	
			for (var i = 0; i < filas.length; i++) {
				var fila = filas[i];
				var celdas = fila.getElementsByTagName('td');
				var filaData = {
					local_nombre: celdas[0].innerText,
					fecha_operacion: celdas[1].innerText,
					sistema_resultado: celdas[2].innerText,
					cajero_resultado: celdas[3].innerText,
					diferencia_resultado: celdas[4].innerText,
					sistema_pagos_manuales: celdas[5].innerText,
					cajero_pagos_manuales: celdas[6].innerText,
					diferencia_pagos_manuales: celdas[7].innerText,
					sistema_apuestas_deportivas: celdas[8].innerText,
					cajero_apuestas_deportivas: celdas[9].innerText,
					diferencia_apuestas_deportivas: celdas[10].innerText,
					sistema_billeteros: celdas[11].innerText,
					cajero_billeteros: celdas[12].innerText,
					diferencia_billeteros: celdas[13].innerText,
					sistema_goldenrace: celdas[14].innerText,
					cajero_goldenrace: celdas[15].innerText,
					diferencia_goldenrace: celdas[16].innerText,
					sistema_carreradecaballos: celdas[17].innerText,
					cajero_carreradecaballos: celdas[18].innerText,
					diferencia_carreradecaballos: celdas[19].innerText,
					sistema_dsvirtualgaming: celdas[20].innerText,
					cajero_dsvirtualgaming: celdas[21].innerText,
					diferencia_dsvirtualgaming: celdas[22].innerText,
					sistema_bingo: celdas[23].innerText,
					cajero_bingo: celdas[24].innerText,
					diferencia_bingo: celdas[25].innerText,
					sistema_web: celdas[26].innerText,
					cajero_web: celdas[27].innerText,
					diferencia_web: celdas[28].innerText,
					sistema_web_televentas: celdas[29].innerText,
					cajero_web_televentas: celdas[30].innerText,
					diferencia_web_televentas: celdas[31].innerText,
					sistema_cash: celdas[32].innerText,
					cajero_cash: celdas[33].innerText,
					diferencia_cash: celdas[34].innerText,
					sistema_apuestas_deportivas_altenar: celdas[35].innerText,
					cajero_apuestas_deportivas_altenar: celdas[36].innerText,
					diferencia_apuestas_deportivas_altenar: celdas[37].innerText,
					sistema_kasnet: celdas[38].innerText,
					cajero_kasnet: celdas[39].innerText,
					diferencia_kasnet: celdas[40].innerText,
					sistema_disashop: celdas[41].innerText,
					cajero_disashop: celdas[42].innerText,
					diferencia_disashop: celdas[43].innerText,
					sistema_atsnacks: celdas[44].innerText,
					cajero_atsnacks: celdas[45].innerText,
					diferencia_atsnacks: celdas[46].innerText,
					sistema_devoluciones: celdas[47].innerText,
					cajero_devoluciones: celdas[48].innerText,
					diferencia_devoluciones: celdas[49].innerText,
					sistema_devoluciones_carrera_caballos: celdas[50].innerText,
					cajero_devoluciones_carrera_caballos: celdas[51].innerText,
					diferencia_devoluciones_carrera_caballos: celdas[52].innerText,
					sistema_torito: celdas[53].innerText,
					cajero_torito: celdas[54].innerText,
					diferencia_torito: celdas[55].innerText,
					sistema_nsoft: celdas[56].innerText,
					cajero_nsoft: celdas[57].innerText,
					diferencia_nsoft: celdas[58].innerText,
					sistema_kiron: celdas[59].innerText,
					cajero_kiron: celdas[60].innerText,
					diferencia_kiron: celdas[61].innerText,
					resultado_sistema: celdas[62].innerText,
					resultado_voucher: celdas[63].innerText,
					sistema_devoluciones: celdas[64].innerText,
					sistema_devoluciones_carrera_caballos: celdas[65].innerText,
					sistema_pagos_manuales: celdas[66].innerText,
					diferencia: celdas[67].innerText
				};
				datos.push(filaData);
			}

			$.ajax({
				url: '/export/caja_auditoriav2_reporte.php',
				type: 'post',
				data: { datos: JSON.stringify(datos), data_export: JSON.stringify(data_export) },
			})
			.done(function (dataresponse) {
				var obj = JSON.parse(dataresponse);
				window.open(obj.path);
				loading();
			});
		});
}

function sec_caja_events2() {
	$(".auditoria_btn2")
		.off()
		.click(function (event) {
			contadorLocales.style.display = "none";
			de.style.display = "none";
			cantidadLocales.style.display = "none";
			span_locales.style.display = "none";
			mensaje_cargando.style.display = "none";

			var tabla1 = document.getElementsByClassName('table_container');
			for (var i = 0; i < tabla1.length; i++) {
				var elemento = tabla1[i];
				if (elemento.classList.contains('oculto')) {
					elemento.classList.remove('oculto');
				}
			}

			var tabla2 = document.getElementById('tabla-data');
			if(!tabla2.classList.contains('oculto')){
				tabla2.classList.add('oculto');
			}

			sec_caja_auditoria2();
		});

	$(".auditoria_btn2_all")
		.off()
		.click(function (event) {
			contadorLocales.style.display = "inline";
			de.style.display = "inline";
			cantidadLocales.style.display = "inline";
			span_locales.style.display = "inline";
			mensaje_cargando.style.display = "inline";

			var tbody = document.getElementById('tabla-body');
			tbody.innerHTML = '';

			var tabla3 = document.getElementsByClassName('table_container');
			for (var i = 0; i < tabla3.length; i++) {
				var elemento = tabla3[i];
				if (!elemento.classList.contains('oculto')) {
					elemento.classList.add('oculto');
				}
			}

			var tabla4 = document.getElementById('tabla-data');
			if(tabla4.classList.contains('oculto')){
				tabla4.classList.remove('oculto');
			}

			var elementos = document.querySelectorAll('.table');
			elementos.forEach(function(elemento) {
				if (elemento.classList.contains('fixed')) {
					elemento.remove();
				}
			});

			var tbody = document.getElementById('tabla-body');
			tbody.innerHTML = '';

			mostrarSiguienteLocal();
		});
}

function sec_caja_auditoria2() {
	// console.log("sec_caja_get_reporte");
	console.log('caja2');
	loading(true);

	// console.log(item_config);
	$(".item_config").each(function (index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});

	// console.log(item_config);
	var get_data = jQuery.extend({}, item_config);
	$.post('/sys/get_caja2.php', {
		"sec_caja_auditoria2": get_data
	}, function (r) {
		loading();
		try {
			$(".table_container").html(r);
			console.log(get_data)
			sec_caja_auditoria_events2(get_data);
		} catch (err) {
			// console.log(r);
		}
		// console.log(r);
	});
}

function sec_caja_auditoria2_all() {
	// console.log("sec_caja_get_reporte");
	console.log('caja2_all');
	// loading(true);

	// console.log(item_config);
	$(".item_config").each(function (index, el) {
		var config_index = $(el).attr("name");
		var config_val = $(el).val();
		var ls_index = "sec_" + sec_id + "_" + sub_sec_id + "_" + config_index;
		localStorage.setItem(ls_index, config_val);
		item_config[config_index] = config_val;
	});

	// console.log(item_config);
	var get_data = jQuery.extend({}, item_config);
	$.post('/sys/get_caja2.php', {
		"sec_caja_auditoria2": get_data
	}, function (r) {
		loading();
		try {
			$(".table_container").html(r);
			sec_caja_auditoria_events2(get_data);
		} catch (err) {
			// console.log(r);
		}
		// console.log(r);
	});
}
// var cantidad_locales = 0;
function sec_caja_reporte_get_locales_all() {
	var valores = {
        cantidad_locales: 0,
        locales: []
    };

    $.ajax({
        url: "/sys/get_caja_reporte.php",
        type: "POST",
        data: {
            accion: "sec_caja_reporte_obtener_locales_all"
        },
        success: function (datos) {
			var respuesta = JSON.parse(datos);
			valores.cantidad_locales = respuesta.result.cantidad_locales[0].cantidad_locales;
			for (var i = 0; i < respuesta.result.locales.length; i++) {
				valores.locales.push(respuesta.result.locales[`${i}`].id);
			}
        },
        error: function () {
            // Manejar el error si es necesario
        }
    });
	return valores;
}

function sumarColumnas() {
    const tabla = document.getElementById('tabla-body');
    const filas = tabla.querySelectorAll('tr');
    const numColumnas = filas[0].cells.length;
    const sumaColumnas = new Array(numColumnas).fill(0);

    for (let i = 0; i < filas.length; i++) {
        const cells = filas[i].querySelectorAll('td');
        for (let j = 2; j < numColumnas; j++) { // Excluimos la primera, segunda y última columna
            const cellValue = parseFloat(cells[j].textContent);
            sumaColumnas[j] += isNaN(cellValue) ? 0 : cellValue;
        }
    }

    const filaSuma = document.createElement('tr');
    filaSuma.innerHTML = `<td class="stuck"></td>`;
	filaSuma.innerHTML += `<td class="stuck">Total</td>`;
    for (let k = 2; k < numColumnas - 1; k++) { // Excluimos la primera, segunda y última columna
		if(k == 2 || k == 3 || k == 4){
			filaSuma.innerHTML += `<td class="stuck">${sumaColumnas[k].toFixed(2)}</td>`;
		}else{
			filaSuma.innerHTML += `<td>${sumaColumnas[k].toFixed(2)}</td>`;
		}
    }
    tabla.appendChild(filaSuma);
}

function getPeticion(fecha_inicio, fecha_fin, local_id) {
	return fetch(`/sec_cajaauditoria.php?do=AJAX_OBTENER_CAJA_AUDITORIA&fecha_inicio=${fecha_inicio}&fecha_fin=${fecha_fin}&local_id=${local_id}`)
	  .then(response => response.json())
	  .then(data => {
		// console.log(data)
		return data; // Aquí podrías procesar los datos si es necesario
	  })
	  .catch(error => {
		console.error('Error al obtener la petición:', error);
		throw error;
	  });
}

const all_locales = sec_caja_reporte_get_locales_all();
// const all_locales = {cantidad_locales : 5, locales : [362,1557,1190,974,557]};

let currentIndex = 0;
let localesCargados = 0;

function mostrarSiguienteLocal() {
	localesCargados = 0;
	var elemento_contador_locales = document.getElementById("contador_locales");
	elemento_contador_locales.textContent = localesCargados;
	var elemento_cantidad_locales = document.getElementById("cantidad_locales");
	elemento_cantidad_locales.textContent = all_locales.cantidad_locales;

	const fecha_inicio = document.getElementById("sec_caja_reporte_fecha_inicio").value;
	const fecha_f = document.getElementById("sec_caja_reporte_fecha_fin").value;
	const fecha = new Date(fecha_f);
	fecha.setDate(fecha.getDate() + 1);
	const fecha_fin = fecha.toISOString().slice(0, 10);

	const batchSize = 10; // CANTIDAD DE PETICIONES A TRAER A LA VEZ
    const maxGrupos = Math.ceil(all_locales.locales.length / batchSize);

    function cargarGrupo(index) {
        const inicio = index * batchSize;
        const fin = Math.min(inicio + batchSize, all_locales.locales.length);
        const local_ids = all_locales.locales.slice(inicio, fin);

        const promesas = local_ids.map(local_id => {
            return getPeticion(fecha_inicio, fecha_fin, local_id);
        });

        Promise.all(promesas)
            .then(resultados => {
                mostrarEnTabla(resultados);
				sec_caja_auditoria2_all();

				localesCargados += local_ids.length; // Actualiza el contador de locales
                actualizarContadorLocales();
				if(localesCargados == all_locales.cantidad_locales){
					sumarColumnas();
					mensaje_cargando.style.display = "none";
					swal({
						title: "¡Éxito!",
						text: "Se cargaron todos los locales exitosamente.",
						type: "success",
						closeOnConfirm: false
					});
				}
            })
            .catch(error => {
                console.error('Error al obtener las peticiones:', error);
            })
            .finally(() => {
                if (index < maxGrupos - 1) {
                    cargarGrupo(index + 1);
                }
            });
    }
	cargarGrupo(0);
}

function actualizarContadorLocales() {
    var elemento_contador_locales = document.getElementById("contador_locales");
    elemento_contador_locales.textContent = localesCargados; // Actualiza el contador en la interfaz
}

function mostrarEnTabla(item) {
	for(i = 0; i < item.length; i++){
		for(j = 0; j < item[i].length; j++){
			const tableRow = `<tr style="height: 25px">
				<td style="height: 25px" class="stuck">${item[i][j].local_nombre}</td>
				<td style="height: 25px" class="stuck">${item[i][j].fecha_operacion}</td>
				<td style="height: 25px" class="stuck">${Number(item[i][j].dinero_sistema).toFixed(2)}</td>
				<td style="height: 25px" class="stuck">${Number(item[i][j].dinero_cajero).toFixed(2)}</td>
				<td style="height: 25px" class="stuck ${Number(item[i][j].dinero_cajero - item[i][j].dinero_sistema).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].dinero_cajero - item[i][j].dinero_sistema).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_pagos_manuales).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_pagos_manuales).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_pagos_manuales - item[i][j].sistema_pagos_manuales).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_pagos_manuales - item[i][j].sistema_pagos_manuales).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_apuestas_deportivas).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_apuestas_deportivas).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_apuestas_deportivas - item[i][j].sistema_apuestas_deportivas).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_apuestas_deportivas - item[i][j].sistema_apuestas_deportivas).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_billeteros).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_billeteros).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_billeteros - item[i][j].sistema_billeteros).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_billeteros - item[i][j].sistema_billeteros).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_goldenrace).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_goldenrace).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_goldenrace - item[i][j].sistema_goldenrace).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_goldenrace - item[i][j].sistema_goldenrace).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_carreradecaballos).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_carreradecaballos).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_carreradecaballos - item[i][j].sistema_carreradecaballos).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_carreradecaballos - item[i][j].sistema_carreradecaballos).toFixed(2)}</td>
				<td style="height: 25px">${Number(0).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_dsvirtualgaming).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_dsvirtualgaming - 0).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_carreradecaballos - item[i][j].sistema_carreradecaballos).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_bingo).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_bingo).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_bingo - item[i][j].sistema_bingo).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_bingo - item[i][j].sistema_bingo).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_web).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_web).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_web - item[i][j].sistema_web).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_web - item[i][j].sistema_web).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_televentas).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_televentas).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].sistema_televentas - item[i][j].cajero_televentas).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].sistema_televentas - item[i][j].cajero_televentas).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_cash).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_cash).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_cash - item[i][j].sistema_cash).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_cash - item[i][j].sistema_cash).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_altenar).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_altenar).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].sistema_altenar - item[i][j].cajero_altenar).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].sistema_altenar - item[i][j].cajero_altenar).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_kasnet).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_kasnet).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_kasnet - item[i][j].sistema_kasnet).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_kasnet - item[i][j].sistema_kasnet).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_disashop).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_disashop).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_disashop - item[i][j].sistema_disashop).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_disashop - item[i][j].sistema_disashop).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_atsnacks).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_atsnacks).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_atsnacks - item[i][j].sistema_atsnacks).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_atsnacks - item[i][j].sistema_atsnacks).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_devoluciones).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_devoluciones).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_devoluciones - item[i][j].sistema_devoluciones).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_devoluciones - item[i][j].sistema_devoluciones).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_devoluciones_carrera_caballos).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_devoluciones_carrera_caballos).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_devoluciones_carrera_caballos - item[i][j].sistema_devoluciones_carrera_caballos).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_devoluciones_carrera_caballos - item[i][j].sistema_devoluciones_carrera_caballos).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_torito).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_torito).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].sistema_torito - item[i][j].cajero_torito).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].sistema_torito - item[i][j].cajero_torito).toFixed(2)}</td>
				<td style="height: 25px">${Number(0).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_nsoft).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_nsoft - 0).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_nsoft - 0).toFixed(2)}</td>
				<td style="height: 25px">${Number(0).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].cajero_kiron).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].cajero_kiron - 0).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].cajero_kiron - 0).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].dinero_sistema).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].resultado_voucher).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_devoluciones).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_devoluciones_carrera_caballos).toFixed(2)}</td>
				<td style="height: 25px">${Number(item[i][j].sistema_pagos_manuales).toFixed(2)}</td>
				<td style="height: 25px" class="${Number(item[i][j].diferencia).toFixed(2) != 0.00 ? "bg-danger text-white text-bold" : "" }">${Number(item[i][j].diferencia).toFixed(2)}</td>
				<td style="padding: 0.3rem;">
					<button
						data-local_id="${item[i][j].local_id}"
						data-fecha_inicio="${item[i][j].fecha_operacion}"
						data-fecha_fin="${item[i][j].fecha_operacion}"
						class="btn btn-secondary btn-sm detalle_btn btn-xs"><i class="glyphicon glyphicon-new-window"></i> Detalle
					</button>
				</td>
			</tr>`;
			document.getElementById('tabla-body').innerHTML += tableRow;
		}
	}
}