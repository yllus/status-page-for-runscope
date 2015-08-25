<?php
// If there's no data yet for this Test, skip it.
if ( sizeof($test_result->data) == 0 ) {
	return;
}

// Decide whether the last run of the Test passed or failed.
$bool_test_passed = false;
$str_container_class = 'rsp-container-fail';
$str_result_class = 'rsp-result-fail';
$str_result_message = 'Failed';
if ( $test_result->data[0]->assertions_failed == 0 ) {
	$bool_test_passed = true;
	$str_container_class = '';
	$str_result_class = 'rsp-result-pass';
	$str_result_message = 'Passed';
}
?>
<div class="rsp-container <?php echo $str_container_class; ?>">
    <div class="rsp-inner-container">
    	<div class="rsp-test-info">
    		<div class="rsp-test-name">
				<?php echo $test_result->test_name; ?>
		    </div>

		    <div class="rsp-last-run">
		    	Last run: 
		    </div>
    	</div>
	    
	    <div class="rsp-result <?php echo $str_result_class; ?>">
	    	<?php echo $str_result_message; ?>
	    </div>

	    <div style="clear: both;"></div>
	</div>
</div>