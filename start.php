<?php 

	function croncheck_init(){
		// extend view
		elgg_extend_view("admin/statistics/overview", "croncheck/info");
		
		// register a widget for admins
		elgg_register_widget_type("croncheck", elgg_echo("croncheck:widget:title"), elgg_echo("croncheck:widget:description"), "admin");
		
		// register plugin hooks
		elgg_register_plugin_hook_handler("cron", "all", "croncheck_monitor");
	}
	
	function croncheck_monitor($hook, $entity_type, $returnvalue, $params){
		$allowed_crons = array("reboot", "minute", "fiveminute", "fifteenmin", "halfhour", "hourly", "daily", "weekly", "monthly", "yearly");
		
		if(in_array($entity_type, $allowed_crons)){
			$time = elgg_extract("time", $params, time());
			
			elgg_set_plugin_setting("latest_" . $entity_type . "_ts", $time, "croncheck");
		}
	}
	
	// register default Elgg event
	elgg_register_event_handler("init", "system", "croncheck_init");
	