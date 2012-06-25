<?php if (version_compare(PHP_VERSION, '5.2.0', '<=')): ?>
<div class="vbx-applet">
    <h1>This applet is not supported in php version <?= PHP_VERSION ?></h1>
</div>
<?php else:
//check for user provided holiday dates and parse 
//TODO: would be nice to have an AppletUI for a datepicker
$holiday = array();
foreach(array('holiday_start', 'holiday_end') as $date){
    $holiday[$date] = AppletInstance::getValue($date);
    if($holiday[$date]){
        try{
            $holiday[$date] = date('g:ia M j, Y', strtotime($holiday[$date]));
        } catch (Exception $e) {
            error_log('could not parse holiday date: ' . $$holiday[$date]);
        }
    }
}

$days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday',
							'Saturday', 'Sunday');
$now = new DateTime('now');
?>
<div class="vbx-applet">
	<h2>Set your open hours.</h2>
	<p>Use the table below to set the hours which you are open. Time is based on
	the server's current time. We use the timezone specified in your
	<em>index.php</em> in the OpenVBX install directory.</p>
	<p><em>Your server's current time: <?php echo $now->format('r'); ?></em></p>

	<div class="vbx-full-pane">
<?php foreach ($days as $index => $day): ?>
		<div class="timing-timerange-wrap">
			<label><?php print $day; ?></label>
<?php
			$state = AppletInstance::getValue("range_{$index}_from", '') ? 'remove' : 'add';
			$default = $index < 5 ? '09:00AM' : '';
			echo AppletUI::timeRange(
				"range_$index",
				AppletInstance::getValue("range_{$index}_from", $default),
				AppletInstance::getValue("range_{$index}_to", '05:00PM'),
				$day
			);
?>
			<a href="#" class="timing-<?php echo $state; ?>"><?php echo $state; ?></a>
			<br class="clear"/>
		</div>
<?php endforeach; ?>
	</div>

	<h3>Open applet.</h3>
	<div class="vbx-full-pane">
		<p>When someone calls or SMS while open, use the applet below.</p>
		<?php echo AppletUI::DropZone('open', 'Open'); ?>
	</div>
	<h3>Closed applet.</h3>
	<div class="vbx-full-pane">
		<p>When someone calls or SMS while closed, use the applet below.</p>
		<?php echo AppletUI::DropZone('closed', 'Closed'); ?>
	</div>
    <h3>Holiday applet.</h3>
    <div class="vbx-full-pane">
        <p>
            Date: <input type='text' name='holiday_start' value='<?php echo $holiday['holiday_start']?>' /> to <input type='text' name='holiday_end' value='<?php echo $holiday['holiday_end']?>' /> 
        </p>
        <p>On this date, override with the applet below.</p>
        <?php echo AppletUI::DropZone('holiday', 'Holiday'); ?>
    </div>
</div>
<?php endif; ?>
