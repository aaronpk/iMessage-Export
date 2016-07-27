<?php
chdir(dirname(__FILE__));
include('include.php');

$csv_directory = 'messages';
$csv_file = "$csv_directory/messages.csv";

if(file_exists($csv_directory) == FALSE) {

	if(mkdir($csv_directory) == FALSE) {
		exit("Could not find or create directory $csv_directory\n");
	}
	
}

// If the file exists already clean it up and add new messages
if(file_exists($csv_file)) {
  $csv = file($csv_file);
  // Remove blank lines
  $csv = array_filter($csv);
  // Get the timestamp of the last line
  $last = str_getcsv($csv[count($csv)-1]);
  $last = (int)$last[0];
  $fp = fopen($csv_file, 'a');

  if($fp == FALSE) {
	  exit("Could not open file $csv_file\n"); 
  }
  
  echo "Finding messages since ".date('Y-m-d H:i:s', $last)."\n";
} else {
  $last = 0;
  $fp = fopen($csv_file, 'a');

  if($fp == FALSE) {
	  exit("Could not open file $csv_file\n"); 
  }
  
  fputcsv($fp, array(
    'Timestamp','Date','Time','To','To Name','From','From Name','Message','Emoji','Attachments'
  ));
}


$query = query_messages_since($db, $last);
$last_timestamp = 0;
while($line = $query->fetch(PDO::FETCH_ASSOC)) {

  $attachment_query = $db->query('SELECT attachment.*
    FROM attachment 
    JOIN message_attachment_join ON message_attachment_join.attachment_id=attachment.ROWID
    WHERE message_attachment_join.message_id = ' . $line['ROWID']);
  $attachments = array();
  while($attachment = $attachment_query->fetch(PDO::FETCH_ASSOC)) {
    $attachments[] = $attachment;
  }

  if($line['is_from_me']) {
    $from = $me;
    $to = $line['contact'];
  } else {
    $from = $line['contact'];
    $to = $me;
  }
  $from_name = contact_name($from);
  $to_name = contact_name($to);

  $num_emoji = 0;
  if(preg_match_all('/(?:[0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', $line['text'], $matches)) {
    $num_emoji = count($matches[0]);
  }

  fputcsv($fp, array(
    $line['date'],
    date('Y-m-d', $line['date']),
    date('H:i:s', $line['date']),
    $from,
    $from_name,
    $to,
    $to_name,
    trim($line['text']),
    $num_emoji,
    count($attachments)
  ));

}

fclose($fp);

