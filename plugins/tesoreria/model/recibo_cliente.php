<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('pago_recibo_cliente.php');

/**
 * Recibos de clientes.
 */
class recibo_cliente extends fs_model
{
   /**
    * Clave primaria.
    * @var type 
    */
   public $idrecibo;
   
   /**
    * ID de la remesa en la que está incluido el recibo,
    * si lo estuviese.
    * @var type 
    */
   public $idremesa;
   
   /**
    * ID de la factura relacionada.
    * @var type 
    */
   public $idfactura;
   
   /**
    * Código de la factura + número de recibo (dos dígitos)
    * @var type 
    */
   public $codigo;
   
   /**
    * Número de recibo.
    * @var type 
    */
   public $numero;
   
   /**
    * Emitido / Pagado / Vencido
    * @var type 
    */
   public $estado;
   
   public $fecha;
   
   /**
    * fecha de vencimiento
    * @var type 
    */
   public $fechav;
   
   /**
    * fecha de pago.
    * @var type 
    */
   public $fechap;
   
   public $codcliente;
   public $nombrecliente;
   public $cifnif;
   
   /**
    * Código de la dirección del cliente, también está guardado en
    * albaranes y facturas.
    * @var type 
    */
   public $coddir;
   public $direccion;
   public $codpostal;
   public $apartado;
   public $ciudad;
   public $provincia;
   public $codpais;
   
   /**
    * Código de la cuenta bancaria del cliente
    * @var type 
    */
   public $codcuenta;
   public $iban;
   public $swift;
   
   public $importe;
   private $importeeuros;
   public $coddivisa;
   public $tasaconv;
   public $codpago;
   public $codserie;
   
   public function __construct($r = FALSE)
   {
      parent::__construct('reciboscli', 'plugins/tesoreria/');
      if($r)
      {
         $this->idrecibo = $this->intval($r['idrecibo']);
         $this->idfactura = $this->intval($r['idfactura']);
         $this->idremesa = $this->intval($r['idremesa']);
         $this->apartado = $r['apartado'];
         $this->cifnif = $r['cifnif'];
         $this->ciudad = $r['ciudad'];
         $this->codcliente = $r['codcliente'];
         $this->codcuenta = $r['codcuenta'];
         $this->coddir = $r['coddir'];
         $this->coddivisa = $r['coddivisa'];
         $this->codigo = $r['codigo'];
         $this->codpago = $r['codpago'];
         $this->codpais = $r['codpais'];
         $this->codpostal = $r['codpostal'];
         $this->codserie = $r['codserie'];
         $this->direccion = $r['direccion'];
         $this->estado = $r['estado'];
         $this->fecha = date('d-m-Y', strtotime($r['fecha']));
         $this->fechav = date('d-m-Y', strtotime($r['fechav']));
         
         $this->fechap = NULL;
         if($r['fechap'])
         {
            $this->fechap = date('d-m-Y', strtotime($r['fechap']));
         }
         else if($this->estado == 'Pagado')
         {
            $this->fechap = date('d-m-Y', strtotime($r['fechav']));
         }
         
         $this->iban = $r['iban'];
         $this->importe = floatval($r['importe']);
         $this->importeeuros = floatval($r['importeeuros']);
         $this->nombrecliente = $r['nombrecliente'];
         $this->numero = intval($r['numero']);
         $this->provincia = $r['provincia'];         
         $this->swift = $r['swift'];
         $this->tasaconv = floatval($r['tasaconv']);
      }
      else
      {
         $this->idrecibo = NULL;
         $this->idfactura = NULL;
         $this->idremesa = NULL;
         $this->apartado = NULL;
         $this->cifnif = NULL;
         $this->ciudad = NULL;
         $this->codcliente = NULL;
         $this->codcuenta = NULL;
         $this->coddir = NULL;
         $this->coddivisa = NULL;
         $this->codigo = NULL;
         $this->codpago = NULL;
         $this->codpais = NULL;
         $this->codpostal = NULL;
         $this->codserie = NULL;
         $this->direccion = NULL;
         $this->estado = 'Emitido';
         $this->fecha = date('d-m-Y');
         $this->fechav = date('d-m-Y', strtotime('+1month'));
         $this->fechap = NULL;
         $this->iban = NULL;
         $this->importe = 0;
         $this->importeeuros = 0;
         $this->nombrecliente = NULL;
         $this->numero = NULL;
         $this->provincia = NULL;
         $this->swift = NULL;
         $this->tasaconv = 1;
      }
   }

   protected function install()
   {
      return '';
   }
   
   public function url()
   {
      if( is_null($this->idrecibo) )
      {
         return 'index.php?page=ventas_recibos';
      }
      else
         return 'index.php?page=ventas_recibo&id='.$this->idrecibo;
   }
   
   public function vencido()
   {
      if($this->estado == 'Pagado')
      {
         return FALSE;
      }
      else if($this->estado == 'Devuelto')
      {
         return TRUE;
      }
      else
         return strtotime($this->fechav) < strtotime( date('d-m-Y') );
   }
   
   public function get($id)
   {
      $recibo = $this->db->select("SELECT * FROM ".$this->table_name." WHERE idrecibo = ".$this->var2str($id).";");
      if($recibo)
      {
         return new recibo_cliente($recibo[0]);
      }
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->idrecibo) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE idrecibo = ".$this->var2str($this->idrecibo).";");
   }
   
   /**
    * Devuelve un número para un recibo nuevo dado un idfactura
    * @param type $idfactura
    * @return int
    */
   public function new_numero($idfactura)
   {
      $data = $this->db->select("SELECT COUNT(*) as total FROM ".$this->table_name." WHERE idfactura = ".$this->var2str($idfactura).";");
      if($data)
      {
         return (1 + intval($data[0]['total']));
      }
      else
         return 1;
   }
   
   public function save()
   {
      if($this->estado == 'Vencido' AND strtotime($this->fechav) > strtotime(date('d-m-Y')))
      {
         $this->estado = 'Emitido';
         $this->fechap = NULL;
      }
      else if($this->estado == 'Emitido' AND strtotime($this->fechav) < strtotime(date('d-m-Y')))
      {
         $this->estado = 'Vencido';
         $this->fechap = NULL;
      }
      
      $this->importeeuros = $this->importe * $this->tasaconv;
      
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET apartado = ".$this->var2str($this->apartado).", 
            cifnif = ".$this->var2str($this->cifnif).",
            ciudad = ".$this->var2str($this->ciudad).",
            codcliente = ".$this->var2str($this->codcliente).",
            codcuenta = ".$this->var2str($this->codcuenta).",
            coddir = ".$this->var2str($this->coddir).",
            coddivisa = ".$this->var2str($this->coddivisa).",
            codpago = ".$this->var2str($this->codpago).",
            codpais = ".$this->var2str($this->codpais).",
            codpostal = ".$this->var2str($this->codpostal).",
            codserie = ".$this->var2str($this->codserie).",
            direccion = ".$this->var2str($this->direccion).",
            estado = ".$this->var2str($this->estado).",
            fecha = ".$this->var2str($this->fecha).",
            fechav = ".$this->var2str($this->fechav).",
            fechap = ".$this->var2str($this->fechap).",
            idfactura = ".$this->var2str($this->idfactura).",
            codigo = ".$this->var2str($this->codigo).",
            idremesa = ".$this->var2str($this->idremesa).",
            iban = ".$this->var2str($this->iban).",
            importe = ".$this->var2str($this->importe).",
            importeeuros = ".$this->var2str($this->importeeuros).",
            nombrecliente = ".$this->var2str($this->nombrecliente).",
            numero = ".$this->var2str($this->numero).",
            provincia = ".$this->var2str($this->provincia).",
            swift = ".$this->var2str($this->swift).",
            tasaconv = ".$this->var2str($this->tasaconv)."
            WHERE idrecibo = ".$this->var2str($this->idrecibo).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (apartado,cifnif,ciudad,codcliente,codcuenta,coddir,
            coddivisa,codigo,codpago,codpais,codpostal,codserie,direccion,estado,fecha,fechav,fechap,idfactura,
            idremesa,iban,importe,importeeuros,nombrecliente,numero,provincia,swift,tasaconv) VALUES "
                 ."(".$this->var2str($this->apartado)
                 .",".$this->var2str($this->cifnif)
                 .",".$this->var2str($this->ciudad)
                 .",".$this->var2str($this->codcliente)
                 .",".$this->var2str($this->codcuenta)
                 .",".$this->var2str($this->coddir)
                 .",".$this->var2str($this->coddivisa)
                 .",".$this->var2str($this->codigo)
                 .",".$this->var2str($this->codpago)
                 .",".$this->var2str($this->codpais)
                 .",".$this->var2str($this->codpostal)
                 .",".$this->var2str($this->codserie)
                 .",".$this->var2str($this->direccion)
                 .",".$this->var2str($this->estado)
                 .",".$this->var2str($this->fecha)
                 .",".$this->var2str($this->fechav)
                 .",".$this->var2str($this->fechap)
                 .",".$this->var2str($this->idfactura)
                 .",".$this->var2str($this->idremesa)
                 .",".$this->var2str($this->iban)
                 .",".$this->var2str($this->importe)
                 .",".$this->var2str($this->importeeuros)
                 .",".$this->var2str($this->nombrecliente)
                 .",".$this->var2str($this->numero)
                 .",".$this->var2str($this->provincia)
                 .",".$this->var2str($this->swift)
                 .",".$this->var2str($this->tasaconv).");";
         
         if( $this->db->exec($sql) )
         {
            $this->idrecibo = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }
   
   public function delete()
   {
      /// forzamos la eliminación de los pagos, así se eliminan los asientos correspondientes
      $pago = new pago_recibo_cliente();
      foreach($pago->all_from_recibo($this->idrecibo) as $p)
      {
         $p->delete();
      }
      
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE idrecibo = ".$this->var2str($this->idrecibo).";");
   }
   
   public function all($offset=0, $limit=FS_ITEM_LIMIT)
   {
      $reciboslist = array();
      
      $sql = "SELECT * FROM ".$this->table_name." ORDER BY fecha DESC";
      $data = $this->db->select_limit($sql, $limit, $offset);
      if($data)
      {
         foreach($data as $d)
            $reciboslist[] = new recibo_cliente($d);
      }
      
      return $reciboslist;
   }
   
   public function all_from_cliente($codcliente, $offset=0, $limit=FS_ITEM_LIMIT)
   {
      $reciboslist = array();
      
      $sql = "SELECT * FROM ".$this->table_name." WHERE codcliente = ".$this->var2str($codcliente)." ORDER BY fecha DESC";
      $data = $this->db->select_limit($sql, $limit, $offset);
      if($data)
      {
         foreach($data as $d)
            $reciboslist[] = new recibo_cliente($d);
      }
      
      return $reciboslist;
   }
   
   public function all_from_factura($id)
   {
      $reciboslist = array();
      
      $sql = "SELECT * FROM ".$this->table_name." WHERE idfactura = ".$this->var2str($id)." ORDER BY fechav ASC";
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
            $reciboslist[] = new recibo_cliente($d);
      }
      
      return $reciboslist;
   }
   
   public function all_from_remesa($id)
   {
      $reciboslist = array();
      
      $sql = "SELECT * FROM ".$this->table_name." WHERE idremesa = ".$this->var2str($id)." ORDER BY fecha DESC";
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
            $reciboslist[] = new recibo_cliente($d);
      }
      
      return $reciboslist;
   }
   
   public function all_desde($desde, $hasta)
   {
      $pedlist = array();
      
      $pedidos = $this->db->select("SELECT * FROM " . $this->table_name .
              " WHERE fecha >= " . $this->var2str($desde) . " AND fecha <= " . $this->var2str($hasta) ." ORDER BY codigo ASC;");
      if($pedidos)
      {
         foreach($pedidos as $p)
            $pedlist[] = new recibo_cliente($p);
      }
      
      return $pedlist;
   }
   
   public function pendientes($offset=0, $limit=FS_ITEM_LIMIT)
   {
      $reciboslist = array();
      
      $sql = "SELECT * FROM ".$this->table_name." WHERE estado = 'Emitido' AND "
              . "fechav >= ".$this->var2str(date('d-m-Y'))." ORDER BY fechav ASC";
      $data = $this->db->select_limit($sql, $limit, $offset);
      if($data)
      {
         foreach($data as $d)
            $reciboslist[] = new recibo_cliente($d);
      }
      
      return $reciboslist;
   }
   
   public function vencidos($offset=0, $limit=FS_ITEM_LIMIT)
   {
      $reciboslist = array();
      
      $sql = "SELECT * FROM ".$this->table_name." WHERE estado = 'Devuelto' OR estado = 'Vencido' ORDER BY fechav DESC";
      $data = $this->db->select_limit($sql, $limit, $offset);
      if($data)
      {
         foreach($data as $d)
            $reciboslist[] = new recibo_cliente($d);
      }
      
      return $reciboslist;
   }
   
   public function cron_job()
   {
      $this->db->exec("UPDATE ".$this->table_name." SET estado = 'Vencido' WHERE estado = 'Emitido'"
              . " AND fechav < ".$this->var2str(Date('d-m-Y')).";");
   }
}