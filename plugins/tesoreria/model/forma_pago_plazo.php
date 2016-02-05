<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

/**
 * Description of forma_pago_plazo
 * Esta clase sirve para estableces plazos de pago de facturas, y dividir los
 * recibos de las facturas en tantos como plazos haya asociados a su forma de
 * pago.
 *
 * @author carlos
 */
class forma_pago_plazo extends fs_model
{
   /**
    * Clave primaria.
    * @var type 
    */
   public $id;
   
   /**
    * Código de la forma de pago asociada. Obligatorio.
    * @var type 
    */
   public $codpago;
   
   /**
    * % del importe de la factura aplazado.
    * @var type 
    */
   public $aplazado;
   
   /**
    * Nº de días desde la fecha de la factura.
    * @var type 
    */
   public $dias;
   
   
   public function __construct($p = FALSE)
   {
      parent::__construct('plazos', 'plugins/tesoreria/');
      if($p)
      {
         $this->id = $this->intval($p['id']);
         $this->codpago = $p['codpago'];
         $this->aplazado = floatval($p['aplazado']);
         $this->dias = intval($p['dias']);
      }
      else
      {
         $this->id = NULL;
         $this->codpago = NULL;
         $this->aplazado = 0;
         $this->dias = 0;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM plazos WHERE id = ".$this->var2str($id).";");
      if($data)
      {
         return new forma_pago_plazo($data[0]);
      }
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->id) )
      {
         return FALSE;
      }
      else
      {
         return $this->db->select("SELECT * FROM plazos WHERE id = ".$this->var2str($this->id).";");
      }
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE plazos SET codpago = ".$this->var2str($this->codpago).
                 ", aplazado = ".$this->var2str($this->aplazado).
                 ", dias = ".$this->var2str($this->dias).
                 " WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO plazos (codpago,aplazado,dias) VALUES (".
                 $this->var2str($this->codpago).",".
                 $this->var2str($this->aplazado).",".
                 $this->var2str($this->dias).");";
         
         if( $this->db->exec($sql) )
         {
            $this->id = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM plazos WHERE id = ".$this->var2str($this->id).";");
   }
   
   /**
    * Devuelve los plazos de una forma de pago.
    * @param type $codpago
    */
   public function all_from($codpago)
   {
      $plist = array();
      
      $data = $this->db->select("SELECT * FROM plazos WHERE codpago = ".$this->var2str($codpago)." ORDER BY dias ASC;");
      if($data)
      {
         foreach($data as $d)
            $plist[] = new forma_pago_plazo($d);
      }
      
      return $plist;
   }
}
