<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2014-2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('asiento.php');
require_model('asiento_factura.php');
require_model('proveedor.php');

/**
 * Description of pagar_facturas
 *
 * @author carlos
 */
class pagar_facturas_prov extends fs_controller
{
   public $codproveedor;
   public $codserie;
   public $desde;
   public $hasta;
   public $proveedor;
   public $resultados;
   public $serie;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Pagar facturas', 'compras', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->desde = Date('01-m-Y');
      if( isset($_POST['desde']) )
      {
         $this->desde = $_POST['desde'];
      }
      
      $this->hasta = Date('d-m-Y');
      if( isset($_POST['hasta']) )
      {
         $this->hasta = $_POST['hasta'];
      }
      
      $this->codproveedor = FALSE;
      if( !isset($_POST['todos']) AND isset($_POST['codproveedor']) )
      {
         $this->codproveedor = $_POST['codproveedor'];
         
         $pro0 = new proveedor();
         $this->proveedor = $pro0->get($this->codproveedor);
      }
      
      $this->serie = new serie();
      $this->codserie = FALSE;
      if( isset($_POST['codserie']) )
      {
         $this->codserie = $_POST['codserie'];
      }
      
      if( isset($_REQUEST['buscar_proveedor']) )
      {
         $this->buscar_proveedor();
      }
      else if( in_array('tesoreria', $GLOBALS['plugins']) )
      {
         $this->new_error_msg('Si usas el <b>plugin Tesorería</b> no puedes usar este'
                 . ' asistente para pagar todas las facturas.');
      }
      else if( isset($_POST['idfactura']) )
      {
         /// ¿Marcamos ya las facturas?
         $num = 0;
         
         $asi0 = new asiento();
         $asifac = new asiento_factura();
         $fact0 = new factura_proveedor();
         foreach($_POST['idfactura'] as $id)
         {
            $factura = $fact0->get($id);
            if($factura)
            {
               $asiento = $asi0->get($factura->idasiento);
               if($asiento)
               {
                  $factura->idasientop = $asifac->generar_asiento_pago($asiento, $factura->codpago);
                  if($factura->idasientop)
                  {
                     $factura->pagada = TRUE;
                     if( $factura->save() )
                     {
                        $num++;
                     }
                  }
               }
               else
               {
                  $factura->pagada = TRUE;
                  if( $factura->save() )
                  {
                     $num++;
                  }
               }
            }
         }
         
         foreach($asifac->errors as $err)
         {
            $this->new_error_msg($err);
         }
         
         $this->new_message($num.' facturas marcadas como pagadas, estas son las siguientes.');
      }
      else
      {
         $this->share_extensions();
      }
      
      $this->resultados = FALSE;
      if( isset($_POST['desde']) )
      {
         $this->resultados = $this->buscar_facturas();
      }
   }
   
   private function share_extensions()
   {
      $extension = array(
          'name' => 'pagar_facturas',
          'page_from' => __CLASS__,
          'page_to' => 'compras_facturas',
          'type' => 'button',
          'text' => '<span class="glyphicon glyphicon-check" aria-hidden="true"></span>'
          . '<span class="hidden-xs">&nbsp; Pagar...</span>',
          'params' => ''
      );
      $fsext = new fs_extension($extension);
      $fsext->save();
   }
   
   private function buscar_proveedor()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $proveedor = new proveedor();
      $json = array();
      foreach($proveedor->search($_REQUEST['buscar_proveedor']) as $pro)
      {
         $json[] = array('value' => $pro->nombre, 'data' => $pro->codproveedor);
      }
      
      header('Content-Type: application/json');
      echo json_encode( array('query' => $_REQUEST['buscar_proveedor'], 'suggestions' => $json) );
   }
   
   private function buscar_facturas()
   {
      $facturas = array();
      $sql = "SELECT * FROM facturasprov WHERE pagada = false AND fecha >= ".$this->serie->var2str($_POST['desde']).
              " AND fecha <= ".$this->serie->var2str($_POST['hasta']).
              " AND codserie = ".$this->serie->var2str($_POST['codserie']);
      if( !isset($_POST['todos']) )
      {
         $sql .= " AND codproveedor = ".$this->serie->var2str($_POST['codproveedor']);
      }
      
      $sql .= " ORDER BY fecha ASC, codigo ASC";
      
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
            $facturas[] = new factura_proveedor($d);
      }
      
      return $facturas;
   }
}
