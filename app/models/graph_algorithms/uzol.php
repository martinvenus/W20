<?php  
class uzol {   
   public $nazov; // id unikatne, musi byt vzdy take, aky je index v poli
   public $sus = array();
   public $status; // volna promenna, na dalsi data. Např. pole vlastnosti uzlu, nejaka doplnkova hodnota atd... 
   
   function __construct($nazov) {
      $this->nazov = $nazov;      
   }
   
   function add_sus($sus){
      if (is_array($sus)){
         $this->sus = $sus;
      }
      else {
         $this->sus[] = $sus;
      }
   }
}


?>