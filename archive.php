<?php
chdir(dirname(__FILE__));
include('include.php');

$last_fn = dirname(__FILE__).'/last.txt';
if(file_exists($last_fn)) {
  $last = file_get_contents($last_fn);
} else {
  $last = 0;
}

$query = query_messages_since($db, $last);
$last_timestamp = 0;
while($line = $query->fetch(PDO::FETCH_ASSOC)) {
  $fn = filename_for_message($line['contact'], $line['date']);
  echo $fn."\n";
  if(!file_exists(dirname($fn))) {
    mkdir(dirname($fn));
  }
  if(!file_exists($fn)) {
    file_put_contents($fn, html_template());
  }

  $attachment_query = $db->query('SELECT attachment.*
    FROM attachment 
    JOIN message_attachment_join ON message_attachment_join.attachment_id=attachment.ROWID
    WHERE message_attachment_join.message_id = ' . $line['ROWID']);
  $attachments = array();
  while($attachment = $attachment_query->fetch(PDO::FETCH_ASSOC)) {
    $attachments[] = $attachment;
  }

  if(!entry_exists($line, $attachments, $fn)) {
    $fp = fopen($fn, 'a');
    $log = format_line($line, $attachments);
    fwrite($fp, $log."\n");
    fclose($fp);
    echo date('c', $line['date']) . "\t" . $line['contact'] . "\t" . $line['text'] . "\n";
    foreach($attachments as $at) {
      $imgsrc = attachment_folder($line['contact'], $line['date']) . $at['transfer_name'];
      if(!file_exists(dirname($imgsrc))) 
        mkdir(dirname($imgsrc));
      copy(str_replace('~/',$_SERVER['HOME'].'/',$at['filename']), $imgsrc);
    }
  }

  if($line['date'] > $last_timestamp) {
    $last_timestamp = $line['date'];
  }
}

if($last_timestamp > 0)
  file_put_contents($last_fn, $last_timestamp);

