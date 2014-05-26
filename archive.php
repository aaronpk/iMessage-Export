<?php
chdir(dirname(__FILE__));
include('include.php');

$query = $db->query('SELECT date+978336000 AS date, 
    message.text, is_from_me, handle.id AS contact
  FROM message
  LEFT JOIN handle ON message.handle_id = handle.ROWID
  WHERE cache_roomnames IS NULL
  ORDER BY date
  ');
while($line = $query->fetch(PDO::FETCH_ASSOC)) {
  $fn = filename_for_message($line['contact'], $line['date']);
  if(!file_exists(dirname($fn))) {
    mkdir(dirname($fn));
  }
  if(!entry_exists($line, $fn)) {
    $fp = fopen($fn, 'a');
    $log = format_line($line);
    fwrite($fp, $log."\n");
    fclose($fp);
    echo $log."\n";
  }
}

