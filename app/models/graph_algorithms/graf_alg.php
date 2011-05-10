<?php
   // konstanty
   //define("SQL_HOST","porthos.wsolution.cz");
   //define("SQL_DBNAME","w20_wsolution_cz");
   // define("SQL_USERNAME","w20_wsolution_cz");
   //define("SQL_PASSWORD","HmBHMMSTn2bdYnNW");
   
   //pripojeni k DB
   //mysql_connect(SQL_HOST, SQL_USERNAME, SQL_PASSWORD) or die("Nemozem sa pripojit: " . mysql_error());
   //mysql_select_db(SQL_DBNAME) or die("Nemozem vybrat DB: ". mysql_error());
   
   //vyber snapshotu
   //$result = mysql_query("SELECT id, date, snapshot FROM snapshot");
   //require_once 'Structures/Graph.php';   
   
   require_once 'uzol.php';
   require_once 'graf.php';
   require_once 'hrana.php';
   
   // clustering coeficient pre kazdy uzol
   function clustering($graf){
      $clustering = array();
      foreach($graf->uzly as $uzol){          
         $pocMoznosti = sizeOf($uzol->sus) * (sizeOf($uzol->sus) - 1) / 2; // n*(n-1)/2    -> max. pocet moznosti  
         foreach($uzol->sus as $sused){
            $uzolSusNazvy[] = $sused->nazov;
         }
         
         $zhoda = 0;
         foreach($uzol->sus as $sused){         
            foreach($sused->sus as $susedSuseda){
               if (in_array($susedSuseda->nazov, $uzolSusNazvy, true)){
                  
                     //echo $sused->nazov . ".:::: " . $susedSuseda->nazov . " ---zzzzzzzzz<br />";
                     /*foreach($uzolSusNazvy as $s){
                        echo $s . "<br />";
                     } */                     
                  $zhoda++;
               }
            }              
         }
          
         unset($uzolSusNazvy);  
         if ($pocMoznosti > 0){ // ochrana pred delenim nulou
            $clustering[$uzol->nazov] = ($zhoda/2) / $pocMoznosti; // su zap. vsetky moznosti dva krat, preto / 2            
         }                 
         else {
            $clustering[$uzol->nazov] = 0;            
         }
         //echo "---- <br />";                                                                                   
      }                    
      return $clustering;
   }
             
   
   // neighborhood overlap a embeddedness pre kazdy uzol
   // vysledne pole dava oba algoritmy,resp. obe vystupne polia: $vysledok["neighborhood"][], $vysledok["nembeddedness"][]
   function neighborhood_embeddedness($graf){
      $hrany = $graf->getHrany();      
      //$vysledok = array(); // vysledok budu polia pre neig. aj emb.:  $vysledok["neighborhood"][], $vysledok["embeddedness"][]   pole hodnot pre kazdy uzol. Z neho treba urobit priemer do snapshotu
    
      foreach($hrany as $hrana){ //prochazime vsechny hrany
         // k dispozici mame nazvy uzlu v hrane, dostaneme cele uzly (class uzol)
         //$uzol1 = $graf->getUzol($hrana->uzol1); 
         //$uzol2 = $graf->getUzol($hrana->uzol2);
         $uzol1 = $graf->uzly[$hrana->uzol1];
         $uzol2 = $graf->uzly[$hrana->uzol2];
         
         $zhoda = array();
         //ziskame vsechny sousedy oboch uzlu
         foreach ($uzol1->sus as $susU1){
            if($hrana->uzol2 != $susU1->nazov){ // nepocitame uzel, ktery je soucasti hrany
               $zhoda[] = $susU1->nazov;
            }            
         }
         foreach ($uzol2->sus as $susU2){
            if($hrana->uzol1 != $susU2->nazov){
               $zhoda[] = $susU2->nazov;
            }            
         }
         
         $maxVelkost = sizeOf($zhoda);         
         $zhoda = array_unique($zhoda); //odstranime duplicity
         $velkost = sizeOf($zhoda); // celkove cislo, kt. sa deli (citatel)
         $embeddedness = $maxVelkost - $velkost; // zistime embeddedness

         $hid = md5($uzol1->nazov . $uzol2->nazov);
         
         $vysledok["neighborhood"][$hid] = $embeddedness / $velkost; //vysledny overlap
         $vysledok["embeddedness"][$hid] = $embeddedness; //
      }  
      return $vysledok;       
   }
   
   function density($graf){
      $pocetUzlu = sizeOf($graf->uzly);
      $maxPocetHran = $pocetUzlu * ($pocetUzlu - 1) / 2;
      $pocetHran = sizeOf($graf->getHrany());            
      return $pocetHran / $maxPocetHran;              
   }
   
   function erdos($graf, $erNodeName){  
      /* $erNode->status["open"] =  2 -> cakajuci  
                                    1 -> otvoreny 
                                    0 -> zatvoreny uzol
                                   
         $erNode->status["dist"] = */
      $erdos = array(); // vysledne erdosNum pro kazdy uzel
      $delka = 0; // vzdalenost od erdos uzla
      $neighArray = array(); //pole nazvu (ID) sousedu vsech otevrenych vrcholu
     
      // otevreme uvodni uzel
      foreach($graf->uzly as $uzol){
         if ($uzol->nazov == $erNodeName){            
            $uzol->status["open"] = 1;
            $neighArray[] = $uzol->nazov;            
         }
      }       
      $end = false;
      while (!$end){
         // prejdeme vsetky otvorene uzly, priradime dlzky a pridame este neprezrete uzly
         foreach($neighArray as $nazov){
            $uzol = $graf->uzly[$nazov]; // poradie uzla v grafe priradime kvoli prehladnosti
            if ($uzol->status["open"] == 1){
               //echo "prechadzam uzol " . $uzol->nazov . "<br />"; 
               
               foreach($uzol->sus as $sused){
                  if (!isset($sused->status["open"])){
                     $sused->status["open"] = 2; // este sa nic nedeje, len caka
                     $neighArray[] = $sused->nazov;
                     //echo "pridavam vrchol " . $sused->nazov . "<br />";           
                  }      
               }
               $uzol->status["open"] = 0;
               $uzol->status["dist"] = $delka;
               
            }
         }
         
         $zatvorene = 0;
         // vsetky cakajuce uzly dame na otvorene a skontrolujeme kolko je uz uzavrenych
         foreach($neighArray as $nazov){          
            if ($graf->uzly[$nazov]->status["open"] == 2){
               $graf->uzly[$nazov]->status["open"] = 1;
            }
            elseif ($graf->uzly[$nazov]->status["open"] == 0){
               $zatvorene++;
            }                                                                           
         }
         $delka++;
         //echo "zatvorene: " . $zatvorene . "<br />";
         
         // vsechny zavrene, hotovo
         if ($zatvorene == sizeOf($graf->uzly)) {                        
            foreach($graf->uzly as $uzol){
               $erdos[$uzol->nazov] = $uzol->status["dist"];                
               // vypis cisel pre vsetky uzly 
               //echo $uzol->nazov . ": ma erdosa: " . $uzol->status["dist"] . "<br />";
               unset($uzol->status["dist"]);              
               unset($uzol->status["open"]);
            }
            $end = true;
         }
      
      }
      return $erdos;
   
   }
     
        

?>