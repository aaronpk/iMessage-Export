<?php
chdir(dirname(__FILE__));
include('include.php');

$dbconfig = array(
  'name' => 'aaronpk',
  'host' => '127.0.0.1',
  'username' => 'root',
  'password' => ''
);

$sql = new PDO('mysql:dbname=' . $dbconfig['name'] . ';host=' . $dbconfig['host'] . ';charset=utf8mb4', $dbconfig['username'], $dbconfig['password']);
$sql->exec('SET CHARACTER SET utf8mb4');

$query = $sql->prepare('SELECT * FROM messages ORDER BY date DESC LIMIT 1');
$query->execute();
$last = $query->fetch(PDO::FETCH_OBJ);
if($last) {
  $last = $last->timestamp;
} else {
  $last = 0;
}

$insert = $sql->prepare('INSERT INTO messages 
  (`timestamp`, `date`, `time`, `from`, `from_name`, `to`, `to_name`, `message`, `num_emoji`, `num_attachments`) 
  VALUES(?,?,?,?,?,?,?,?,?,?)');

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

  $insert->bindValue(1, $line['date']);
  $insert->bindValue(2, date('Y-m-d', $line['date']));
  $insert->bindValue(3, date('H:i:s', $line['date']));
  $insert->bindValue(4, $from);
  $insert->bindValue(5, $from_name);
  $insert->bindValue(6, $to);
  $insert->bindValue(7, $to_name);
  $insert->bindValue(8, trim($line['text']));
  $insert->bindValue(9, $num_emoji);
  $insert->bindValue(10, count($attachments));
  $insert->execute();

}

