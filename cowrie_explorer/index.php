<?php

function dd($what) {
	var_dump($what);
	die();
}

function eventFilter($itm) {
	$event = trim($_GET['event']);
	return $itm['eventid'] == $event;
}

$result = null;
$cnt = 0;

$data = file_get_contents("cowrie_full.json");
$data = json_decode($data, true);

if(!$data) {
	$result = "JSON: " . json_last_error_msg();
}
else {
	if(!empty(@$_GET['event'])) {
		$data = array_filter($data, "eventFilter");
	}
	if(!empty(@$_GET['filter'])) {
		$cols = explode(",", $_GET['filter']);
		$collection = [];
		foreach($data as &$row) {
			$item = [];
			foreach($cols as &$col) {
				$item[$col] = @$row[$col];
			}
			array_push($collection, $item);
		}
		$data = $collection;
	}
	
	if(!empty(@$_GET['unique'])) {
		$data = array_map("unserialize", array_unique(array_map("serialize", $data)));
	}
	
	if(@$_GET['csv']) {
		if(empty(@$_GET['filter']) && empty(@$_GET['event'])) {
			$data = [];
			$result = "input / event filtering is required for csv";
		}
		if(count($data) > 0) {
			$cols = array_keys($data[array_key_first($data)]);
			$result = implode(";", $cols) . ";\n";
			// dd($result);
			foreach($data as &$row) {
				foreach($cols as &$col) {
					$result .= @$row[$col] . ';';
				}
				$result .= "\n";
			}
		}
	}
	else $result = json_encode($data);
	
	$cnt = count($data);
}

?>
<!DOCTYPE HTML>
<html lang="en">
	<head>
		<title>Cowrie json explorer</title>
	</head>
	<body>
		<form method="GET" action="">
			<input type="text" name="event" placeholder="Event ID" value="<?=@$_GET['event']?>"/>
			<input type="text" name="filter" placeholder="Filter" value="<?=@$_GET['filter']?>"/>
			<input type="checkbox" name="unique" <?= @$_GET['unique'] ? 'checked' : '' ?>/> <span>UNIQUE</span>
			<input type="checkbox" name="csv" <?= @$_GET['csv'] ? 'checked' : '' ?>/> <span>CSV</span>
			<input type="submit" value="RUN"/>
			<span><?= $cnt ?> item(s)</span>
		</form>
		<textarea style="width:90%; height:90svh"><?= $result ?? 'no data' ?></textarea>
	</body>
</html>
