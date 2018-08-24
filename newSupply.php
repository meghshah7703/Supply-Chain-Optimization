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


   if(isset($_POST['submit'])){

      $raw = $_POST['raw_material'];
      $cost = $_POST['cost'];
      $avail = $_POST['Availability'];
      $user = $_SESSION['userName'];

      

      $sql =<<<EOF
      set search_path to sco;
      insert into supplier values ('$user','$raw','$cost','$avail');
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