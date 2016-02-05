<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2014-2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('asiento.php');
require_model('factura_proveedor.php');
require_model('pago_recibo_proveedor.php');
require_model('proveedor.php');
require_model('recibo_proveedor.php');
require_model('subcuenta.php');

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
      if( isset($_POST['codproveedor']) )
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
      else if( isset($_POST['idfactura']) )
      {
         $this->pagar_facturas();
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
      $fsext = new fs_extension();
      $fsext->name = 'pagar_facturas';
      $fsext->from = __CLASS__;
      $fsext->to = 'compras_facturas';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-check" aria-hidden="true"></span>'
          . '<span class="hidden-xs">&nbsp; Pagar...</span>';
      $fsext->save();
      
      $fsext2 = new fs_extension();
      $fsext2->name = 'pagar_recibos';
      $fsext2->from = __CLASS__;
      $fsext2->to = 'compras_recibos';
      $fsext2->type = 'button';
      $fsext2->text = '<span class="glyphicon glyphicon-check" aria-hidden="true"></span>'
          . '<span class="hidden-xs">&nbsp; Pagar...</span>';
      $fsext2->save();
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
      $sql = "SELECT * FROM facturasprov WHERE pagada = false AND fecha >= ".$this->serie->var2str($_POST['desde'])
              ." AND fecha <= ".$this->serie->var2str($_POST['hasta'])
              ." AND codserie = ".$this->serie->var2str($_POST['codserie'])
              ." AND codproveedor = ".$this->serie->var2str($_POST['codproveedor'])
              ." ORDER BY fecha ASC, codigo ASC";
      
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $facturas[] = new factura_proveedor($d);
         }
      }
      
      return $facturas;
   }
   
   private function pagar_facturas()
   {
      $num = 0;
      
      /// ¿Generamos el asiento de pago?
      $asientop = NULL;
      if($this->empresa->contintegrada)
      {
         /// ¿Cuanto es el total?
         $coddivisa = NULL;
         $importe = 0;
         $tasaconv = 1;
         $rec0 = new recibo_proveedor();
         foreach($_POST['idfactura'] as $id)
         {
            $recibos = $rec0->all_from_factura($id);
            foreach($recibos as $recibo)
            {
               if($recibo->estado != 'Pagado')
               {
                  $coddivisa = $recibo->coddivisa;
                  $importe += $recibo->importe;
                  $tasaconv = $recibo->tasaconv;
               }
            }
         }
         $asientop = $this->nuevo_asiento_pago($importe, $coddivisa, $tasaconv);
      }
      
      $fac0 = new factura_proveedor();
      foreach($_POST['idfactura'] as $id)
      {
         $error = FALSE;
         
         $recibos = $rec0->all_from_factura($id);
         foreach($recibos as $recibo)
         {
            if($recibo->estado != 'Pagado')
            {
               $pago = new pago_recibo_proveedor();
               $pago->idrecibo = $recibo->idrecibo;
               
               if($asientop)
               {
                  $pago->idasiento = $asientop->idasiento;
               }
               
               if( $pago->save() )
               {
                  $recibo->estado = 'Pagado';
                  if( !$recibo->save() )
                  {
                     $error = TRUE;
                  }
               }
            }
         }
         
         if(!$error)
         {
            /// marcamos la factura como pagada
            $factura = $fac0->get($id);
            if($factura)
            {
               $factura->pagada = TRUE;
               if( $factura->save() )
               {
                  $num++;
               }
            }
         }
      }
      
      $this->new_message($num.' facturas marcadas como pagadas, estas son las siguientes.');
   }
   
   private function nuevo_asiento_pago($importe, $coddivisa, $tasaconv)
   {
      $asiento = new asiento();
      $asiento->concepto = 'Pago facturas de '.$this->proveedor->nombre;
      $asiento->editable = FALSE;
      $asiento->importe = $importe;
      
      $eje0 = new ejercicio();
      $ejercicio = $eje0->get_by_fecha($this->today());
      if($ejercicio)
      {
         $asiento->codejercicio = $ejercicio->codejercicio;
      }
      
      $subcuenta_pro = $this->proveedor->get_subcuenta($ejercicio->codejercicio);
      
      $subc0 = new subcuenta();
      $subcaja = $subc0->get_cuentaesp('CAJA', $ejercicio->codejercicio);
      
      if(!$ejercicio)
      {
         $this->new_error_msg('Ningún ejercico encontrado.');
      }
      else if( !$ejercicio->abierto() )
      {
         $this->new_error_msg('El ejercicio '.$ejercicio->codejercicio.' está cerrado.');
      }
      else if( !$subcuenta_pro )
      {
         $this->new_message("No se ha podido generar una subcuenta para el proveedor "
                 . "<a href='".$ejercicio->url()."'>¿Has importado los datos del ejercicio?</a>");
      }
      else if( !$subcaja )
      {
         $this->new_message("No se ha encontrado la subcuenta de caja "
                 . "<a href='".$ejercicio->url()."'>¿Has importado los datos del ejercicio?</a>");
      }
      else if( $asiento->save() )
      {
         $partida1 = new partida();
         $partida1->idasiento = $asiento->idasiento;
         $partida1->concepto = $asiento->concepto;
         $partida1->idsubcuenta = $subcuenta_pro->idsubcuenta;
         $partida1->codsubcuenta = $subcuenta_pro->codsubcuenta;
         $partida1->debe = $importe;
         $partida1->coddivisa = $coddivisa;
         $partida1->tasaconv = $tasaconv;
         $partida1->codserie = $this->codserie;
         $partida1->save();
         
         $partida2 = new partida();
         $partida2->idasiento = $asiento->idasiento;
         $partida2->concepto = $asiento->concepto;
         $partida2->idsubcuenta = $subcaja->idsubcuenta;
         $partida2->codsubcuenta = $subcaja->codsubcuenta;
         $partida2->haber = $importe;
         $partida2->coddivisa = $coddivisa;
         $partida2->tasaconv = $tasaconv;
         $partida2->codserie = $this->codserie;
         $partida2->save();
         
         $this->new_message('<a href="'.$asiento->url().'">Asiento de pago</a> generado.');
      }
      else
      {
         $this->new_error_msg('Error al guardar el asiento.');
      }
      
      return $asiento;
   }
}
