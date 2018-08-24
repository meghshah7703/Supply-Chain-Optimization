<?php
   $host        = "host = 127.0.0.1";
   $port        = "port = 5432";
   $dbname      = "dbname = postgres";
   $credentials = "user = postgres password=****";

   $db = pg_connect( "$host $port $dbname $credentials"  );
   if(!$db) {
      echo "Error : Unable to open database\n";
   }


   if(isset($_POST['submit'])){

      $username = $_POST['username'];
      $email = $_POST['email'];
      $password = $_POST['password'];
      $city = $_POST['ciyt'];
      $utype= $_POST['userType'];

      $sql =<<<EOF
      set search_path to sco;
      insert into register (uname,uemail,password,city,utype) values ('$username','$email','$password','$city','$utype');
EOF;
      
      $ret = pg_query($db, $sql);
      if(!$ret) {
         echo pg_last_error($db);
      }
      
      pg_close($db);
      
      echo "<script type=\"text/javascript\">".
        "alert('Registered successfully');".
        "</script>";
      header('Location: index.html');
      exit;
      }
   
?>