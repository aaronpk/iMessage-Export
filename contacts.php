<?php
chdir(dirname(__FILE__));
include('include.php');

if(file_exists('contacts.txt')) {
  echo "contacts.txt file already exists! Won't overwrite.\n";
  die();
}

$fp = fopen('contacts.txt', 'a');

$query = $db->query('SELECT * FROM handle');
while($q = $query->fetch(PDO::FETCH_ASSOC)) {
  fwrite($fp, $q['id']." \n");
}

fclose($fp);
