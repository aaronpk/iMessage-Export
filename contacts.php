<?php
chdir(dirname(__FILE__));
include('include.php');

$data = file_get_contents('contacts.txt');
$contacts = array();
if(preg_match_all('/([^ \n]+) ([^\n]+)/', $data, $matches)) {
  $contacts = $matches[1];
}

$query = $db->query('SELECT * FROM handle');
while($q = $query->fetch(PDO::FETCH_ASSOC)) {
  if(!in_array($q['id'], $contacts)) {
    echo $q['id'] . "\n";
  }
}

