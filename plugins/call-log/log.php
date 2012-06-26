<?php
	$ci = & get_instance();
	
	$start = isset($_POST['start'])?strtotime($_POST['start']):strtotime('-7 days');
	$end = isset($_POST['end'])?strtotime($_POST['end']):time();
	
	$format = 'g:ia M j, Y';
	
	//pull records
	$calls = $ci->db->where('timestamp >=', $start)->where('timestamp <=', $end)->get('user_calls');
	
	//get api helper
	$service = new Services_Twilio($ci->twilio_sid, $ci->twilio_token);
	
	//assemble data
	$log = array();
    foreach($calls->result() as $call){
        //TODO: could persist data in database once fetched
        $call_data = $service->account->calls->get($call->call_sid);
        // should only be one second leg
        $legs = $service->account->calls->getIterator(0,1, array('ParentCallSid' => $call->call_sid));
        $legs->valid();
        $leg = $legs->current();
        $user = VBX_User::get($call->user_id);
        $log[] = array(
        	'user' => $user,
            'call' => $call_data,
            'leg' => $leg
        );
    }	
	
    if(isset($_POST['export'])){
        ob_end_clean();
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="calllog.csv"');
        $fp = fopen('php://output', 'w');
        foreach($log as $call){
            fputcsv($fp, array(
                $call['user']->email,
                $call['leg']->to,
                $call['call']->start_time,
                $call['call']->end_time
            ));
        }
        fclose($fp);
        exit;
    }
    
	?>

<div class="vbx-plugin">
    <form method="POST">
        <label>Set Date Range: </label>
        <input type='text' name='start' value='<?php echo date($format, $start);?>' /> 
        <input type='text' name='end' value='<?php echo date($format, $end);?>' />
        <input type='submit' value='Get Log'/>
        <input type='submit' value='Export' name='export' />
    </form>
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Dialed</th>
                <th>Call Start</th>
                <th>Call End</th>
            </tr>
        </thead>
    <?php foreach($log as $call):?>
        <tr>
            <td><?php echo $call['user']->email; ?></td>
            <td><?php echo $call['leg']->to?>
            <td><?php echo $call['call']->start_time?></td>
            <td><?php echo $call['call']->end_time?></td>
            
        </tr>
    <?php endforeach;?>
    </table>
    
</div>