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

      $product = $_POST['product'];
      $raw_material = $_POST['raw_material'];
      $quant = $_POST['quantity'];
      $user = $_SESSION['userName'];

      

      $sql =<<<EOF
      set search_path to sco;
      insert into manufacturer values ('$user','$product','$raw_material','$quant');
EOF;
      
      $ret = pg_query($db, $sql);
      if(!$ret) {
         echo pg_last_error($db);
      }
      
      pg_close($db);
      
      header('Location: Manufacture.html');
      exit;
      }
   
?>