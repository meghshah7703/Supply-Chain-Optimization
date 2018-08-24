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

      $username = $_POST['username'];
      $password = $_POST['password'];
      $_SESSION['userName']=$username;

      $sql =<<<EOF
      set search_path to sco;
      select * from register where uname = '$username';
EOF;
      
      $ret = pg_query($db, $sql);
      if(pg_num_rows ($ret)==0){
         echo "<script type=\"text/javascript\">".
        "alert('Enter valid username');".
        "</script>";
        header('Location: index.html');
        exit;
      }
      else{
         $row = pg_fetch_row($ret);
         if($password==$row[2]){
          switch ($row[4]) {
            case 'supplier':
              header('Location: supplier.html');
              break;
            case 'distributor':
              header('Location: Distributor.html');
              break;
            case 'transporter':
              header('Location: transporter.html');
              break;
            case 'manufacturer':
              header('Location: Manufacture.html');
              break;
            default:
              # code...
              break;
          }
        exit;
         }
         else{
            echo "<script type=\"text/javascript\">".
        "alert('Enter valid password');".
        "</script>";
        header('Location: index.html');
        exit;
         }
      }

      if(!$ret) {
         echo pg_last_error($db);
      }
      
      pg_close($db);
      
      
      }
   
?>