<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

/**
 * Description of remesa
 *
 * @author carlos
 */
class remesa extends fs_model
{
   /**
    * Clave primaria.
    * @var type 
    */
   public $idremesa;
   public $descripcion;
   public $codpago;
   
   /**
    * Código de la cuenta bancaria de la empresa.
    * @var type 
    */
   public $codcuenta;
   public $iban;
   public $swift;
   
   public $total;
   public $coddivisa;
   public $tasaconv;
   
   public $estado;
   public $fechacargo;
   public $fecha;
   
   public function __construct($r = FALSE)
   {
      parent::__construct('remesas_sepa');
      if($r)
      {
         $this->idremesa = $this->intval($r['idremesa']);
         $this->descripcion = $r['descripcion'];
         $this->codpago = $r['codpago'];
         $this->codcuenta = $r['codcuenta'];
         $this->iban = $r['iban'];
         $this->swift = $r['swift'];
         $this->total = floatval($r['total']);
         $this->coddivisa = $r['coddivisa'];
         $this->tasaconv = floatval($r['tasaconv']);
         $this->estado = $r['estado'];
         $this->fechacargo = date('d-m-Y', strtotime($r['fechacargo']));
         $this->fecha = date('d-m-Y', strtotime($r['fecha']));
      }
      else
      {
         $this->idremesa = NULL;
         $this->descripcion = NULL;
         $this->codpago = NULL;
         $this->codcuenta = NULL;
         $this->iban = NULL;
         $this->swift = NULL;
         $this->total = 0;
         $this->coddivisa = NULL;
         $this->tasaconv = 1;
         $this->estado = 'Preparada';
         $this->fechacargo = date('d-m-Y');
         $this->fecha = date('d-m-Y');
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function url()
   {
      return 'index.php?page=remesas&id='.$this->idremesa;
   }
   
   public function editable()
   {
      return ($this->estado == 'Preparada');
   }
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM remesas_sepa WHERE idremesa = ".$this->var2str($id));
      if($data)
      {
         return new remesa($data[0]);
      }
      else
      {
         return FALSE;
      }
   }
   
   public function exists()
   {
      if( is_null($this->idremesa) )
      {
         return FALSE;
      }
      else
      {
         return $this->db->select("SELECT * FROM remesas_sepa WHERE idremesa = ".$this->var2str($this->idremesa));
      }
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE remesas_sepa SET codcuenta = ".$this->var2str($this->codcuenta)
                 .", codpago = ".$this->var2str($this->codpago)
                 .", iban = ".$this->var2str($this->iban)
                 .", swift = ".$this->var2str($this->swift)
                 .", descripcion = ".$this->var2str($this->descripcion)
                 .", total = ".$this->var2str($this->total)
                 .", coddivisa = ".$this->var2str($this->coddivisa)
                 .", tasaconv = ".$this->var2str($this->tasaconv)
                 .", estado = ".$this->var2str($this->estado)
                 .", fechacargo = ".$this->var2str($this->fechacargo)
                 .", fecha = ".$this->var2str($this->fecha)
                 ."  WHERE idremesa = ".$this->var2str($this->idremesa).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO remesas_sepa (codpago,codcuenta,iban,swift,total,coddivisa,tasaconv"
                 . ",estado,fechacargo,fecha,descripcion) VALUES "
                 . "(".$this->var2str($this->codpago)
                 . ",".$this->var2str($this->codcuenta)
                 . ",".$this->var2str($this->iban)
                 . ",".$this->var2str($this->swift)
                 . ",".$this->var2str($this->total)
                 . ",".$this->var2str($this->coddivisa)
                 . ",".$this->var2str($this->tasaconv)
                 . ",".$this->var2str($this->estado)
                 . ",".$this->var2str($this->fechacargo)
                 . ",".$this->var2str($this->fecha)
                 . ",".$this->var2str($this->descripcion).");";
         
         if( $this->db->exec($sql) )
         {
            $this->idremesa = $this->db->lastval();
            return TRUE;
         }
         else
         {
            return FALSE;
         }
      }
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM remesas_sepa WHERE idremesa = ".$this->var2str($this->idremesa));
   }
   
   public function all($offset = 0)
   {
      $lista = array();
      
      $data = $this->db->select_limit("SELECT * FROM remesas_sepa ORDER BY fecha DESC", FS_ITEM_LIMIT, $offset);
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new remesa($d);
         }
      }
      
      return $lista;
   }
}
