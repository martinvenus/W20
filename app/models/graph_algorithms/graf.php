<?php
class graf {
   public $uzly = array(); // typ uzol
   
   // vytvori graf s urcitym poctem prvku $pocPrvkov
   function __construct($pocPrvkov = 0){
      if ($pocPrvkov > 0){
         for ($i = 0; $i < $pocPrvkov; $i++){
            $this->uzly[$i] = new uzol($i);         
         }
      }               
   }
   
   // prida uzol na koniec grafu
   function addUzol($nazov){
      $this->uzly[$nazov] = new uzol($nazov);      
   }
   
   //prida hranu, resp. kazdemu uzlu souseda   
   function addHrana($uzolId1, $uzolId2){                         
      $this->uzly[$uzolId1]->add_sus($this->uzly[$uzolId2]);   
      $this->uzly[$uzolId2]->add_sus($this->uzly[$uzolId1]);      
   }
   
   // ziska pole hran
   function getHrany($toString = false){   // $toString urci ci sa maju hrany aj vypisat   
      $overene = array(); // pole hran, ktere se jiz overili
      foreach($this->uzly as $uzol){
         $uzol->nazov; // identifikace uzlu, ve kterem se prave nachazime
         foreach($uzol->sus as $sused){
            $over = true;
            foreach ($overene as $key => $overenaHrana){
               if ($overenaHrana->uzol1 == $sused->nazov && 
                   $overenaHrana->uzol2 == $uzol->nazov){
                  $over = false;
                  //unset($overene[$key]);
                  break;
               }               
            }
            if ($over){
               // ---- vypocet pre danu hranu            
               // ----------------------------
               $overene[] = new hrana($uzol->nazov, $sused->nazov); // hranu pridame medzi overene            
            }
         }      
      }          
      if ($toString){
         foreach($overene as $hrana){
            echo $hrana->uzol1 . " " . $hrana->uzol2 . "<br />";
         }
      }
      return $overene;      
   }
   
   // vypis grafu
   function toString(){
      foreach($this->uzly as $uzol){
         echo $uzol->nazov . ": ";
         foreach ($uzol->sus as $sused){
            echo "[" . $sused->nazov . "] &nbsp;";
         }    
         echo "<br /> \n";
      }
   }   
}


?>