<?php

class Farmer{
  public $name;
  public $available;
  public $cost;

  public function __construct($n,$a,$c) {
        $this->name = $n;
        $this->available=$a;
        $this->cost=$c;
    }

}

 function cmp($a, $b){
      return $a->cost-$b->cost;
    }

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

      $raw_material = $_POST['raw_material'];
      $quantity = $_POST['quantity'];
      $user = $_SESSION['userName'];
      //$user = 'jamnadas';
      $quant=$quantity;

      $sql =<<<EOF
      set search_path to sco;
      select city from register where uname='$user';
EOF;
      
      $ret = pg_query($db, $sql);
      $row = pg_fetch_row ($ret);
      $city = $row[0];

      echo 'city1 '.$city;
      $sql =<<<EOF
      select * from location where city='$city';
EOF;
      
      $ret = pg_query($db, $sql);
      $row = pg_fetch_row($ret); 

      $userx=$row[1];
      $usery=$row[2];
      echo 'x1 '.$userx.' y1 '.$usery;

      $sql =<<<EOF
      select * from supplier where raw_material='$raw_material';
EOF;
            
      $farmers=array();
      $ret = pg_query($db, $sql);
      
      if(pg_num_rows ($ret)==0){
        header('Location: distributor.html');
        exit;
      }
      else{
         while ($row=pg_fetch_row($ret)){
           $f=new Farmer($row[0],$row[3],$row[2]);
           $f->name;
           array_push($farmers,$f);
         }
      }

      $q=0;
      while($q!=sizeof($farmers)){

        $name=$farmers[$q]->name;
        $cost=$farmers[$q]->cost;
        $avail=$farmers[$q]->available;
        $rem=$farmers[$q]->available-$quantity;

        $sql =<<<EOF
        select city from register where uname='$name';
EOF;
        
        $ret = pg_query($db, $sql);
        $row = pg_fetch_row($ret);
        $city = $row[0];

        $sql =<<<EOF
        select * from location where city='$city';
EOF;
      
        $ret = pg_query($db, $sql);
        $row = pg_fetch_row($ret); 

        $farmerx=$row[1];
        $farmery=$row[2];

        $x=$userx-$farmerx;
        $x=$x*$x;

        $y=$usery-$farmery;
        $y=$y*$y;

        $dist= intval(sqrt($x+$y));

        $sql =<<<EOF
        select * from transporter where raw_material='$raw_material';
EOF;
        $ret = pg_query($db, $sql);
        $row = pg_fetch_row($ret);
        $ct = $row[1];

        $transportcost=$ct*$dist;
        $cost=$transportcost+$cost;
        $farmers[$q]->cost=$cost; 
        $q=$q+1;
      }
      
      usort($farmers, "cmp");

      $q=0;
      while($quantity!=0){

        if($q==sizeof($farmers))
          break;

        $name=$farmers[$q]->name;
        $cost=$farmers[$q]->cost;
        $avail=$farmers[$q]->available;
        $rem=$farmers[$q]->available-$quantity;
        if($avail==0){
          $q=$q+1;
          continue;
        }
        if($farmers[$q]->available<=$quantity){
          $fc=$cost*$avail;
          $sql =<<<EOF
          insert into orders (buyer,seller,raw_material,cost,quantity) values ('$user','$name','$raw_material','$fc','$avail');
          update supplier set availability=0 where supplierName='$name';
EOF;
          pg_query($db, $sql);

          echo $farmers[$q]->name;
          echo $farmers[$q]->available;
          $quantity=$quantity-$farmers[$q]->available;
          $q=$q+1;
        }

        else{
          $fc=$cost*$quantity;
          $sql =<<<EOF
          insert into orders (buyer,seller,raw_material,cost,quantity) values ('$user','$name','$raw_material','$fc','$quantity');
          update supplier set availability=$rem where supplierName='$name' ;
EOF;
          pg_query($db, $sql);

          echo $farmers[$q]->name;
          echo $quantity;
          $quantity=0;
        }
      }

      $quant = $quant-$quantity;  
      $sql =<<<EOF
      update distributor set availability = availability+$quant where dname='$user' and raw_material='$raw_material';
EOF;
      pg_query($db, $sql);

      pg_close($db);
      header('Location: Distributor.html');
      
      }
   
?>