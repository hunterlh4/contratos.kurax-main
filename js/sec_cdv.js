function sec_cdv() {
	if(sec_id=="cdv"){
		console.log("sec:cdv");
		sec_cdv_events();
	}
}
function sec_cdv_events(){
	console.log("sec_cdv_events");
	$(".cdv_import_btn")
		.off()
		.click(function(event) {
			console.log("cdv_import_btn:click");
			$("#cdv_import_modal")
				.on('shown.bs.modal', function (e) {
					sec_cdv_events();
					cdv_import_modal_events();

					
				})
				.on('hidden.bs.modal', function (e) {
				})
				.modal({
					"backdrop":'static',
					"keyboard":false,
					"show":true
				});
		})
		//.click()
		;
}
function cdv_import_modal_events() {
	console.log("cdv_import_modal_events");
	$("#cdv_import_modal .nav-tabs label")
		.off()
		.click(function(event) {
			var tab = $(this).find("input").val();
			console.log(tab);
			$(".tab-content div.tab-pane").removeClass('active');
			$("#cnl_"+tab).addClass('active');
	});
	$("#cdv_import_modal .timepicker").timepicker({
    	"showMeridian":false,
    	"minuteStep":1
    });
	$('#cdv_import_modal .cdv_datepicker').datepicker({
		format: 'dd-mm-yyyy',
		autoclose:true
	}).on('show', function(ev){
		console.log($(this));
    }).on('changeDate', function(ev){
		$(this).datepicker('hide');
		var newDate = new Date(ev.date);
		$("input[data-real-date="+$(this).attr("id")+"]").val($.format.date(newDate, "yyyy-MM-dd"));
    });


    var upload_btn = $(".upload-btn");
    var data = {};
    	data["tabla"]="tbl_transacciones_repositorio";
    var uploader = new ss.SimpleUpload({
		button: upload_btn,
		name: 'uploadfile',
		autoSubmit:false,
		data: data,
		debug:true,
		allowedExtensions:["csv"],
		url:$("#"+upload_btn.data("form")).attr("action"),
		onChange:function ( filename, extension, uploadBtn, fileSize, file) {
			console.log("uploader:onChange");
			console.log(file);
			var info_html = filename;
				info_html+= " ";
				info_html+= (fileSize)+"Kb";
			$("#"+upload_btn.data("form")+" label.uploader_file_name").html(info_html);
		},
		onSubmit: function(filename, extension, uploadBtn, size) {
			console.log("uploader:onSubmit");
			loading(true);
			var progress_bar = $('<div class="progress progress-lg"><div class="this-bar progress-bar progress-bar-striped active progress-bar-success"></div></div>');
			uploader.prev_pro = 0;
			$(".loading_box").addClass("loading_box_progress");
			$(".loading_box").append(progress_bar);
		},         
		onComplete: function(filename, response, uploadBtn, size) {
			console.log("uploader:onComplete");
			console.log(response);
			if (response) {
			}else{
				return false;            
			}
		},
		onProgress:function(pro){
			console.log("uploader:onProgress");
			if(pro!=uploader.prev_pro){			
				if(pro<=100){
					$(".this-bar").html(pro+"%");
					$(".this-bar").stop().css({width: pro+"%"});
					uploader.prev_pro=pro;
					console.log(pro);
				}	
			}
		},
		onError( filename, errorType, status, statusText, response, uploadBtn, fileSize ) {
			console.log("uploader:onError");
			console.log("error");
		},
		onExtError( filename, extension ){
			console.log("uploader:onExtError");
			swal("Error!", "Seleccione solo archivos CSV", "warning");
		}
	}); 
	$("#cdv_import_form")
		.off()
		.submit(function(event) {
			event.preventDefault();
			console.log("cdv_import_form:submit");
			$(".import_data").each(function(index, el) {
				if($(el).attr("type")=="radio"){
					if($(el).prop('checked')){
						data[$(el).attr("name")]=$(el).val();
					}
				}else{
					data[$(el).attr("name")]=$(el).val();
				}
			});
			console.log(data);
			if(uploader.getQueueSize()){
				uploader.submit();
			}else{
				swal("Error!", "Seleccione un archivo", "warning");
			}
			console.log(uploader);
		});
}