<?php
   $host        = "host = 127.0.0.1";
   $port        = "port = 5432";
   $dbname      = "dbname = postgres";
   $credentials = "user = postgres password=****";
   session_start(); 
   $db = pg_connect( "$host $port $dbname $credentials"  );
   if(!$db) {
      echo "Error : Unable to open database\n";
   }

   $user = $_SESSION['userName'];
   if(isset($_POST['submit'])){

      $raw = $_POST['raw_material'];
      $cost = $_POST['cost'];
      $avail = $_POST['Availability'];

      $sql =<<<EOF
      set search_path to sco;
      update supplier set cost=$cost, availability = availability+$avail where suppliername='$user' and raw_material='$raw';
EOF;
      
      $ret = pg_query($db, $sql);
      if(!$ret) {
         echo pg_last_error($db);
      }
      
      pg_close($db);
      
      header('Location: Supplier.html');
      exit;
      }
   
?>