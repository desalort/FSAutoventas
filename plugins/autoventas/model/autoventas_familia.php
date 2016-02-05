<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of catalogo_familia
 *
 * @author user
 */
class autoventas_familia extends fs_model {
    
    
    
    public $nombre;
    public $descripcion;
    public $imagen;
    public $codigo;
    public $visible;
    
    
    public function __construct($a = FALSE)
   {
    parent::__construct('autoventas_familia','plugins/autoventas/');
    
      if($a)
      {
         $this->nombre = $this->var2str($a['nombre']);
         $this->descripcion = $this->var2str($a['descripcion']);
         $this->codigo= $this->var2str($a['codigo']);
         $this->imagen = $this->var2str($a['imagen']);
         $this->visible = $this->var2str($a['visible']);
      }
      else
      {
         $this->nombre = NULL;
         $this->descripcion = NULL;
         $this->codigo = NULL;
         $this->imagen= NULL;
         $this->visible = FALSE;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function exists()
   {
       if ($this->codigo!=NULL) {
           $a = $this->db->select("SELECT * FROM autoventas_familia WHERE codigo='" . $this->codigo . "';");
           if (count($a)>0) {
               return TRUE;
           } else {
               return FALSE;
           }
       } else {
           return FALSE;
       }
   }
   
   public function save()
   {
       if ($this->codigo!=NULL) {
           if ($this->exists()) {
               $sql = "UPDATE autoventas_familia SET visible='". $this->visible . "', imagen='" . $this->imagen . "', nombre='" . $this->nombre . "', descripcion = '" . $this->descripcion . "' WHERE codigo='" . $this->codigo . "';";
           } else {              
               $sql = "INSERT INTO autoventas_familia SET visible='". $this->visible . "', imagen='" . $this->imagen . "', nombre='" . $this->nombre . "', descripcion = '" . $this->descripcion . "', codigo='" . $this->codigo . "';";
           }
           return $this->db->exec($sql);
       }
   }
   
   public function delete()
   {
       if ($this->codigo!=NULL) {
           $sql ="";
           if ($this->exists()) {
               $sql = "DELETE FROM autoventas_familia WHERE codigo='" . $this->codigo . "';";
           }
           return $this->db->exec($sql);
       }
       
   }
   
   public function all()
   {
   }
   
   public function load_data ($codigo=NULL)
    {
       if  ($codigo!=NULL) {
           $this->codigo = $codigo;
       }
       if ($this->codigo!=NULL) {
           $a = $this->db->select("SELECT * FROM autoventas_familia WHERE codigo='" . $this->codigo . "';");
           if (count($a)>0) {
               return $a[0];
           } else {
               return FALSE;
           }
       } else {
           return FALSE;
       }
   }
   
   
   
    
}
