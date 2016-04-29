<?php
get_header();

the_post();
?>
<div class="rsp_section rsp_group">
	<div class="rsp_col rsp_span_1_of_12">
		&nbsp;
	</div>

	<div id="div_rsp_content" class="rsp_col rsp_span_10_of_12">
		<p style="font-size: 32px; margin-top: 60px; text-align: center;">
			Retrieving data from Runscope, please wait...
		</p>

		<div style="text-align: center;">
			<div class="dots-loader">
				&nbsp;
			</div>
		</div>
	</div>

	<div class="rsp_col rsp_span_1_of_12">
		&nbsp;
	</div>

	<?php the_content(); ?>
</div>

<script>
jQuery(document).ready(function($) {
	function display_test_results() {
		jQuery.ajax({
		    url: '<?php echo admin_url('admin-ajax.php?action=runscope_display_test_results&post_id=' . get_the_ID()); ?>'
		})
		.done(function( data ) {
		    jQuery('#div_rsp_content').html(data);

		    var str_timestamp_now = new Date().getTime() / 1000;
		    jQuery('#bucket-last-refreshed').data('livestamp', str_timestamp_now);
		});
	}

	display_test_results();
	setInterval(display_test_results, 60000);
});
</script>
<?php
get_footer(); 
?>