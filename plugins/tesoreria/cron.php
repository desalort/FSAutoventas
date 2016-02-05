<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2016, Carlos García Gómez. All Rights Reserved. 
 */

require_model('factura_cliente.php');
require_model('factura_proveedor.php');
require_model('recibo_factura.php');

class tesoreria_cron
{
   private $db;
   
   public function __construct(&$db)
   {
      $this->db = $db;
      $recibo_factura = new recibo_factura();
      
      /// comprobamos los recibos de proveedores
      $recibo_prov = new recibo_proveedor();
      $recibo_prov->cron_job();
      
      /// alternamos distintas consultas
      $opcion = mt_rand(0, 2);
      
      switch ($opcion)
      {
         case 0:
            $sql = "SELECT * FROM facturasprov WHERE NOT pagada AND idfactura NOT IN (SELECT idfactura FROM recibosprov)";
            break;
         
         case 1:
            $sql = "SELECT * FROM facturasprov WHERE NOT pagada ORDER BY fecha ASC";
            break;
         
         default:
            $sql = "SELECT * FROM facturasprov WHERE NOT pagada ORDER BY fecha DESC";
            break;
      }
      
      $data = $this->db->select_limit($sql, 500, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $factura = new factura_proveedor($d);
            $recibo_factura->sync_factura_prov($factura);
            echo '.';
         }
      }
      
      
      /// comprobamos los recibos de clientes
      $recibo_cli = new recibo_cliente();
      $recibo_cli->cron_job();
      
      switch ($opcion)
      {
         case 0:
            $sql = "SELECT * FROM facturascli WHERE NOT pagada AND idfactura NOT IN (SELECT idfactura FROM reciboscli)";
            break;
         
         case 1:
            $sql = "SELECT * FROM facturascli WHERE NOT pagada ORDER BY fecha ASC";
            break;
         
         default:
            $sql = "SELECT * FROM facturascli WHERE NOT pagada ORDER BY fecha DESC";
            break;
      }
      
      $data = $this->db->select_limit($sql, 500, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $factura = new factura_cliente($d);
            $recibo_factura->sync_factura_cli($factura);
            echo '.';
         }
      }
      
      /// ¿Errores?
      foreach($recibo_factura->errors as $err)
      {
         echo $err."\n";
      }
   }
}

new tesoreria_cron($db);