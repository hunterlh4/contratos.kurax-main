function sec_mepa_mantenimiento()
{
    var fragmentoURL = window.location.hash;
    
    if(fragmentoURL)
    {
        $(".tab-mepa-mantenimiento > div").hide();

        // Elimina el símbolo '#tab=' del fragmento, esto eliminará el quinto carácter '#tab='
        var tab = fragmentoURL.substring(5);
        $(".tab-mepa-mantenimiento > ." + tab).show();

        if(tab == 'mant_correlativo')
        {
            sec_mepa_mantenimiento_correlativo();
        }
        else if(tab == 'mant_zona')
        {
            sec_mepa_mantenimiento_zona();
        }
        else if(tab == 'mant_correo')
        {
            sec_mepa_mantenimiento_correo();
        }
    }
    else
    {
        sec_mepa_mantenimiento_correlativo();

        $(".tab-mepa-mantenimiento > div:not(:first-child)").hide();

        $(".tab-mepa-mantenimiento > div:first-child").show();
    }

    $("a.tab_btn").click(function (event) {
        
        event.preventDefault();

        var tab = $(this).data("tab");

        $(".tab-mepa-mantenimiento > div").hide();

        $(".tab-mepa-mantenimiento > ." + tab).show();
    });

    $('#tab-mepa-mantenimiento a[href="#mant_correlativo"]').click(function (e) {
        e.preventDefault();
        sec_mepa_mantenimiento_correlativo();
    });

    $('#tab-mepa-mantenimiento a[href="#mant_zona"]').click(function (e) {
        e.preventDefault();
        sec_mepa_mantenimiento_zona();
    });

    $('#tab-mepa-mantenimiento a[href="#mant_cuenta_contable"]').click(function (e) {
        e.preventDefault();
        sec_mepa_mantenimiento_cuenta_contable();
    });

    $('#tab-mepa-mantenimiento a[href="#mant_correo"]').click(function (e) {
        e.preventDefault();
        sec_mepa_mantenimiento_correo();
    });
}