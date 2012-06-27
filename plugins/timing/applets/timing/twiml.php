<?php
$response = new TwimlResponse;

$now = date_create('now');
$today = date_format($now, 'w') - 1;

//check for holiday override
$holiday = array();
foreach(array('holiday_start', 'holiday_end') as $range){
    $holiday[$range] = AppletInstance::getValue($range);
    if(empty($holiday[$range])){
        continue;
    }
    
    try {
        $holiday[$range] = new DateTime($holiday[$range]);
    } catch (Exception $e) {
        error_log('could not parse holiday date: ' . $holiday[$range]);
    }
}

//check for valid range
if($holiday['holiday_start'] instanceof DateTime AND $holiday['holiday_end'] instanceof DateTime){
    if($now >= $holiday['holiday_start'] AND $now <= $holiday['holiday_end']){
        $response->redirect(AppletInstance::getDropZoneUrl('holiday'));
        $response->respond();
        return;
    }
}

$response->redirect(AppletInstance::getDropZoneUrl(
  ($from = AppletInstance::getValue("range_{$today}_from"))
  && ($to = AppletInstance::getValue("range_{$today}_to"))
  && date_create($from) <= $now && $now < date_create($to)
  ? 'open'
  : 'closed'
));

$response->respond();
