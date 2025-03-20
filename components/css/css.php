<?php
header("Content-type: text/css; charset: UTF-8");
for ($i=0; $i <= 1000 ; $i++) { 
	?>.w-<?php echo $i;?>px{ width: <?php echo $i;?>px; }
<?php
}
?>