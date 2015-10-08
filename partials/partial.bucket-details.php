<?php
$str_bucket_name = $obj_response_bucket_details->data->name;

$str_container_class = 'rsp-container-fail';
$str_bucket_status = 'System Issue: ' . ($arr_bucket_status['total'] - $arr_bucket_status['passed']) . ' Failure(s) Detected';
if ( $arr_bucket_status['passed'] == $arr_bucket_status['total'] ) {
	$str_container_class = 'rsp-container-pass';
	$str_bucket_status = 'System Status: Fully Online';
}
?>
<h1 style="font-family: 'Open Sans', sans-serif;"><?php echo $str_bucket_name; ?></h1>

<div class="rsp-container <?php echo $str_container_class; ?>">
    <div class="rsp-inner-container">
    	<div>
    		<div class="rsp-bucket-status">
				<?php echo $str_bucket_status; ?>
				
				<div style="font-size: 12px;">
					Last refreshed <span id="bucket-last-refreshed" data-livestamp=""></span>
				</div>
		    </div>
    	</div>
	</div>
</div>

<br><br>