
var countdownTimer_sec_torito;

var idleInterval;

function sec_torito() {
    if (sec_id == 'torito') {
        //countdownTimer_sec_torito = setInterval(torito_delay, 1000);
        swal('Recuerda que estás trabajando con el usuario de la tienda  '+local+'.', '', 'warning');
        idleInterval = setInterval(torito_timerIncrement, 1000); // 1 segundo
    }
}

//************************************************************************************************
//************************************************************************************************
// SIMULACION TIEMPO REAL
//************************************************************************************************
//************************************************************************************************
function torito_delay() {
    var contenido=$('#torito_frame').contents().find('#body').html();
    console.log(contenido);
}

var idleTime = 0;
var minutos_sesion = 4;
var minutos_en_segundos = minutos_sesion * 60;

function torito_timerIncrement() {
    idleTime = idleTime + 1;

    var tiempo_restante = minutos_en_segundos - idleTime;

    var tiempo_restante_en_texto = torito_calcular_tiempo_restante_en_texto(tiempo_restante);

    $('#input_tiempo_inactivo').html(tiempo_restante_en_texto);
    // console.log(idleTime);
    if (idleTime > minutos_en_segundos) { // 1 minutos
        $('#modalInactividad').modal({backdrop: 'static', keyboard: false});
    }
}

function torito_calcular_tiempo_restante_en_texto(segundosP) {

    if (segundosP < 1) {
        return 'expiro';
    } else {
        var valor_minutos;

        const segundos = (Math.round(segundosP % 0x3C)).toString();
        const minutos  = (Math.floor(segundosP / 0x3C ) % 0x3C).toString();

        if (minutos == 0) {
            valor_minutos = '';
        } else {
            valor_minutos = `${minutos}:`;
        }
                
        return `expira en ${valor_minutos}${torito_zfill(segundos,2)}`;
    }

}

function torito_zfill(number, width) {
    var numberOutput = Math.abs(number); /* Valor absoluto del número */
    var length = number.toString().length; /* Largo del número */ 
    var zero = "0"; /* String de cero */  
    
    if (width <= length) {
        if (number < 0) {
             return ("-" + numberOutput.toString()); 
        } else {
             return numberOutput.toString(); 
        }
    } else {
        if (number < 0) {
            return ("-" + (zero.repeat(width - length)) + numberOutput.toString()); 
        } else {
            return ((zero.repeat(width - length)) + numberOutput.toString()); 
        }
    }
}