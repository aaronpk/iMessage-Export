<?php
date_default_timezone_set('UTC');

$db = new PDO('sqlite:' . $_SERVER['HOME'] . '/Library/Messages/chat.db');

$keys = array_keys(load_contacts());
$me = array_shift($keys);

function load_contacts() {
  static $data;
  if(!isset($data)) {
    $data = array();
    preg_match_all('/([^ ]+) (.+)/', file_get_contents('contacts.txt'), $matches);
    foreach($matches[1] as $i=>$key) {
      $data[trim($key)] = trim($matches[2][$i]);
    }
  }
  return $data;
}

function contact($id) {
  $data = load_contacts();

  if(preg_match('/.+@.+\..+/', $id)) {
    $href = 'mailto:' . $id;
  } else {
    $href = 'sms:' . $id;
  }

  if(array_key_exists($id, $data)) {
    return '<a href="' . $href . '" class="p-author h-card">' . $data[$id] . '</a>';
  } else {
    return '<a href="' . $href . '" class="p-author h-card">' . $id . '</a>';
  }
}

function contact_name($id) {
  $data = load_contacts();
  if(array_key_exists($id, $data)) {
    return $data[$id];
  } else {
    return $id;
  }
}

function query_messages_since(&$db, $timestamp) {
  return $db->query('SELECT message.ROWID, date+978307200 AS date, 
    message.text, is_from_me, handle.id AS contact
  FROM message
  LEFT JOIN handle ON message.handle_id = handle.ROWID
  WHERE cache_roomnames IS NULL
    AND date+978307200 > ' . $timestamp . '
  ORDER BY date
  ');
}

function filename_for_message($contact, $ts) {
  $folder = contact_name($contact);
  return 'messages/' . $folder . '/' . date('Y-m', $ts) . '.html';
}

function attachment_folder($contact, $ts, $relative=false) {
  $folder = contact_name($contact);
  return ($relative ? '' : 'messages/' . $folder . '/') . date('Y-m', $ts) . '/';
}

function format_line($line, $attachments) {
  global $me;

  if($line['is_from_me'])
    $contact = $me;
  else
    $contact = $line['contact'];

  $attachments_html = '';

  if(count($attachments)) {
    foreach($attachments as $at) {
      $imgsrc = attachment_folder($line['contact'], $line['date'], true) . $at['transfer_name'];
      $attachments_html .= '<img src="' . $imgsrc . '" class="u-photo">';
    }
  }

  return '<div class="h-entry">'
    . '<time class="dt-published" datetime="' . date('c', $line['date']) . '">' . date('Y-m-d H:i:s', $line['date']) . '</time> '
    . contact($contact)
    . ' <span class="e-content p-name">' . htmlentities(trim($line['text'])) . '</span>'
    . $attachments_html
    . '</div>';
}

function entry_exists($line, $attachments, $fn) {
  if(!file_exists($fn)) return false;
  $file = file_get_contents($fn);
  return strpos($file, format_line($line, $attachments)) !== false;
}

function html_template() {
  ob_start();
?>
<!DOCTYPE html>
<meta charset="utf-8">
<style type="text/css">
body {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  font-size: 14px;
  padding: 10px;
}
.h-entry {
  padding: 8px;
}
.h-entry:nth-of-type(2n+1) {
  background-color: #eee;
}
img {
  max-width: 600px;
  max-height: 600px;
  display: block;
}
</style>
<?php
  return ob_get_clean();
}
