<?php
chdir(dirname(__FILE__));
include('include.php');

$query = $db->query('SELECT message.ROWID, date+978307200 AS date, 
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
}

