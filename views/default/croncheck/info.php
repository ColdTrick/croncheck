<?php 

	if (!function_exists('_croncheck_describe_callable')) {
		function _croncheck_describe_callable($callable) {
			if (is_string($callable)) {
				return $callable;
			}
			if (is_array($callable) && array_keys($callable) === array(0, 1) && is_string($callable[1])) {
				if (is_string($callable[0])) {
					return "{$callable[0]}::{$callable[1]}";
				}
				return "(" . get_class($callable[0]) . ")->{$callable[1]}";
			}
			if ($callable instanceof Closure) {
				$ref = new ReflectionFunction($callable);
				$file = $ref->getFileName();
				$line = $ref->getStartLine();
				$path_base = elgg_get_root_path();
				if ($path_base && 0 === strpos($file, $path_base)) {
					$file = substr($file, strlen($path_base));
				}
				return "(Closure {$file}:{$line})";
			}
			if (is_object($callable)) {
				return "(" . get_class($callable) . ")->__invoke";
			}
			return "(?)";
		}
	}

	if (function_exists('_elgg_services')) {
		// 1.9 This is private API and may change!
		try {
			$dic = _elgg_services();
			$all_hooks = _elgg_services()->hooks->getAllHandlers();
			$cronhooks = $all_hooks['cron'];
		} catch (Exception $e) {
			$cronhooks = array();
		}
	} else {
		global $CONFIG;
		$cronhooks = $CONFIG->hooks["cron"];
	}
	
	$intervals = array("reboot", "minute", "fiveminute", "fifteenmin", "halfhour", "hourly", "daily", "weekly", "monthly", "yearly");
	
	// Info part
	$info_title = elgg_echo("croncheck:info");
	
	$info_table = "<table class='elgg-table'>";
	
	$info_table .= "<tr>";
	$info_table .= "<th>" . elgg_echo("croncheck:interval") . "</th>";
	$info_table .= "<th>" . elgg_echo("croncheck:timestamp") . "</th>";
	$info_table .= "<th>" . elgg_echo("croncheck:friendly_time") . "</th>";
	$info_table .= "</tr>";
	
	foreach($intervals as $interval){
		$interval_ts = elgg_get_plugin_setting("latest_" . $interval . "_ts", "croncheck");
		
		$info_table .= "<tr>";
		// which interval
		$info_table .= "<td>'" . $interval . "'</td>";
		// when did it last run (UNIX ts) & friendly time
		if(!empty($interval_ts)){
			$info_table .= "<td>" . $interval_ts . "</td>";
			$info_table .= "<td>" . elgg_view_friendly_time($interval_ts) . " @ " . date("r", $interval_ts) . "</td>";
		} else {
			$info_table .= "<td>&nbsp;</td>";
			$info_table .= "<td>" . elgg_echo("croncheck:no_run") . "</td>";
		}
		
		$info_table .= "</tr>";
	}
	
	$info_table .= "</table>";
	
	echo elgg_view_module($module, $info_title, $info_table);
	
	// show which functions are registerd to every interval
	if(!empty($vars["toggle"])){
		$module = "info";
		$functions_title = "<br />" . elgg_view("output/url", array("text" => elgg_echo("croncheck:registered"), "href" => "#croncheck_functions", "rel" => "toggle"));
		
		$functions_table = "<table id='croncheck_functions' class='elgg-table hidden'>";
	} else {
		$module = "inline";
		$functions_title = elgg_echo("croncheck:registered");
		
		$functions_table = "<table class='elgg-table'>";
	}
	
	$functions_table .= "<tr>";
	$functions_table .= "<th colspan='2'>" . elgg_echo("croncheck:interval") . "</th>";
	$functions_table .= "</tr>";
	
	foreach($intervals as $interval){
		$functions_table .= "<tr>";
		$functions_table .= "<td>'" . $interval . "'</td>";
		
		if(array_key_exists($interval, $cronhooks) && count($cronhooks[$interval]) >= 1){
			$functions_table .= "<td>";
			foreach($cronhooks[$interval] as $function){
				$functions_table .= _croncheck_describe_callable($function) . "<br />";
			}
			$functions_table .= "</td>";
		} else {
			$functions_table .= "<td>" . elgg_echo("croncheck:none_registered") . "</td>";
		}
		
		$functions_table .= "</tr>";
	}
	
	$functions_table .= "</table>";
	
	echo elgg_view_module($module, $functions_title, $functions_table);
	