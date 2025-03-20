function sec_comercial() {
	if(sec_id==="comercial"){
		//console.log("sec:comercial");
		if (sub_sec_id=="reporte_total_pais") {
			sec_comercial_reporte_total_pais();			
		}
		if (sub_sec_id=="reporte_acumulado_mensual") {
			sec_comercial_reporte_acumulado_mensual();			
		}
		if (sub_sec_id=="reporte_total_productos") {
			sec_comercial_reporte_total_productos();			
		}
		if (sub_sec_id=="reporte_total_por_zona") {
			sec_comercial_reporte_total_por_zona();			
		}
		if (sub_sec_id=="reporte_admin_productos") {
			sec_comercial_reporte_admin_productos();			
		}

		FusionCharts.register('theme', {
		    name: 'fire',
		    theme: {
		      base: {
		        chart: {
		          paletteColors: '#FF4444, #FFBB33, #99CC00, #33B5E5, #AA66CC',
		          baseFontColor: '#36474D',
		          baseFont: 'Helvetica Neue,Arial',
		          captionFontSize: '14',
		          subcaptionFontSize: '14',
		          subcaptionFontBold: '0',
		          showBorder: '0',
		          bgColor: '#ffffff',
		          showShadow: '0',
		          canvasBgColor: '#ffffff',
		          canvasBorderAlpha: '0',
		          useplotgradientcolor: '0',
		          useRoundEdges: '0',
		          showPlotBorder: '0',
		          showAlternateHGridColor: '0',
		          showAlternateVGridColor: '0',
		          toolTipBorderThickness: '0',
		          toolTipBgColor: '#99CC00',
		          toolTipBgAlpha: '90',
		          toolTipBorderRadius: '2',
		          toolTipPadding: '5',
		          legendBgAlpha: '0',
		          legendBorderAlpha: '0',
		          legendShadow: '0',
		          legendItemFontSize: '10',
		          divlineAlpha: '100',
		          divlineColor: '#36474D',
		          divlineThickness: '1',
		          divLineIsDashed: '1',
		          divLineDashLen: '1',
		          divLineGapLen: '1',
		          showHoverEffect: '1',
		          valueFontSize: '11',
		          showXAxisLine: '1',
		          xAxisLineThickness: '1',
		          xAxisLineColor: '#36474D'
		        }
		      },
		      mscolumn2d: {
		        chart: {
		          valueFontColor: '#3B373A', //overwrite base value
		          placeValuesInside: '1',
		          rotateValues: '1'
		        }
		      }
		    }
		});
	}
}
