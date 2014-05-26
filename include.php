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
  if(array_key_exists($id, $data)) {
    return '"' . $data[$id] . '" <' . $id . '>';
  } else {
    return '<' . $id . '>';
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

function filename_for_message($contact, $ts) {
  $folder = contact_name($contact);
  return 'messages/' . $folder . '/' . date('Y-m', $ts) . '.txt';
}

function format_line($line) {
  global $me;
  if($line['is_from_me'])
    $contact = $me;
  else
    $contact = $line['contact'];
  return date('Y-m-d H:i:s', $line['date']) . ' ' . contact($contact) . ' ' . trim($line['text']);
}

function entry_exists($line, $fn) {
  if(!file_exists($fn)) return false;
  $file = file_get_contents($fn);
  $date = date('Y-m-d H:i:s', $line['date']);
  return strpos($file, $date.' ') !== false;
}