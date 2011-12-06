<?php 


	function croncheck_init()	{
		global $CONFIG;
		extend_view('admin/site','croncheck/info');
		extend_view('css','croncheck/css');
		
	}
	
	function croncheck_monitor($hook, $entity_type, $returnvalue, $params){
		$allowed_crons = array("reboot", "minute", "fiveminute", "fifteenmin", "halfhour", "hourly", "daily", "weekly", "monthly", "yearly");
		
		if(in_array($entity_type, $allowed_crons)){
			set_plugin_setting("latest_" . $entity_type . "_ts", time(), "croncheck");
		}		
	}
	
	register_elgg_event_handler('init','system','croncheck_init');

	register_plugin_hook('cron', 'all', 'croncheck_monitor');
	
?>