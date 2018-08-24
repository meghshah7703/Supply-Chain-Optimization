<?php

class Seller{
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

    $user = $_SESSION['userName'];

    $sql =<<<EOF
    set search_path to sco;
    select * from order_m where uname='$user';
EOF;

    $ret = pg_query($db, $sql);
      
    while($row = pg_fetch_row ($ret)){
      $quantity=$row[2];

      $sellers=array();
      $q1 = pg_query($db, "select * from supplier where raw_material='$row[1]'");

      while($srow = pg_fetch_row($q1)){
        $s=new Seller($srow[0],$srow[3],$srow[2]);
        array_push($sellers, $s);
      }

      $q1 = pg_query($db, "select * from distributor where raw_material='$row[1]'");

      while($srow = pg_fetch_row($q1)){
         $s=new Seller($srow[0],$srow[3],$srow[2]);
         array_push($sellers, $s);
      }

      $ret1 = pg_query($db, "select city from register where uname='$user'");
      $tr = pg_fetch_row ($ret1);
      $city = $tr[0];

      $ret1 = pg_query($db, "select * from location where city='$city'");
      $tr = pg_fetch_row ($ret1); 

      $userx=$tr[1];
      $usery=$tr[2];

        //------------------------------------------------------------------------

      $q=0;
      while($q!=sizeof($sellers)){

        $name=$sellers[$q]->name;
        $cost=$sellers[$q]->cost;
        $avail=$sellers[$q]->available;
        $rem=$sellers[$q]->available-$quantity;
        
        $ret1 = pg_query($db, "select city from register where uname='$name'");
        $tr = pg_fetch_row($ret1);
        $city = $tr[0];
      
        $ret1 = pg_query($db, "select * from location where city='$city'");
        $tr = pg_fetch_row($ret1); 

        $sellerx=$tr[1];
        $sellery=$tr[2];

        $x=$userx-$sellerx;
        $x=$x*$x;

        $y=$usery-$sellery;
        $y=$y*$y;

        $dist= intval(sqrt($x+$y));

        $ret1 = pg_query($db, "select * from transporter where raw_material='$row[1]'");
        $tr = pg_fetch_row($ret1);
        $ct = $tr[1];

        $transportcost=$ct*$dist;
        $cost=$transportcost+$cost;
        $sellers[$q]->cost=$cost; 
        $q=$q+1;
      }

      usort($sellers, "cmp");


      $q=0;

      while($quantity!=0){

        if($q==sizeof($sellers))
          break;

        $name=$sellers[$q]->name;
        $cost=$sellers[$q]->cost;
        $avail=$sellers[$q]->available;
        $rem=$sellers[$q]->available-$quantity;

        if($avail==0){
          $q=$q+1;
          continue;
        }

        if($sellers[$q]->available<=$quantity){
          $fc=$cost*$avail;
          $sql1 =<<<EOF
          insert into orders (buyer,seller,raw_material,cost,quantity) values ('$user','$name','$row[1]','$fc','$avail');
EOF;
          pg_query($db, $sql1);

          $ret1 = pg_query($db, "select * from supplier where supplierName='$name'"); 
            
          $tr1 = pg_num_rows($ret1);
          if($tr1==0){
            pg_query($db, "update distributor set availability=0 where dname='$name' and raw_material='$row[1]'");
          }

          else{
            pg_query($db, "update supplier set availability=0 where supplierName='$name' and raw_material='$row[1]'");
          }

          echo $sellers[$q]->name;
          echo $sellers[$q]->available;
          $quantity=$quantity-$sellers[$q]->available;
          $q=$q+1;
        }

      else{
        $fc=$cost*$quantity*$quantity;
        pg_query($db, "insert into orders (buyer,seller,raw_material,cost,quantity) values ('$user','$name','$row[1]','$fc','$quantity')");

        $ret1 = pg_query($db, "select * from supplier where supplierName='$name'"); 
           
        $tr1 = pg_num_rows($ret1);
        if($tr1==0){
          pg_query($db, "update distributor set availability=$rem where dname='$name' and raw_material='$row[1]'");
        }

        else{
          pg_query($db, "update supplier set availability=$rem where supplierName='$name' and raw_material='$row[1]'");
        }

        echo $sellers[$q]->name;
        echo $quantity;
        $quantity=0;
      }
    }
  }

  pg_query($db, "delete from order_m where uname='$user'");
  header('Location: Manufacture.html');
   
?>