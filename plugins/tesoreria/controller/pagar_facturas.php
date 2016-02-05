<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2014-2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('asiento.php');
require_model('cliente.php');
require_model('factura_cliente.php');
require_model('pago_recibo_cliente.php');
require_model('recibo_cliente.php');
require_model('subcuenta.php');

/**
 * Description of pagar_facturas
 *
 * @author carlos
 */
class pagar_facturas extends fs_controller
{
   public $cliente;
   public $codcliente;
   public $codserie;
   public $desde;
   public $hasta;
   public $resultados;
   public $serie;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Pagar facturas', 'ventas', FALSE, FALSE);
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
      
      $this->cliente = FALSE;
      $this->codcliente = FALSE;
      if( isset($_POST['codcliente']) )
      {
         $this->codcliente = $_POST['codcliente'];
         
         $cli0 = new cliente();
         $this->cliente = $cli0->get($this->codcliente);
      }
      
      $this->serie = new serie();
      $this->codserie = FALSE;
      if( isset($_POST['codserie']) )
      {
         $this->codserie = $_POST['codserie'];
      }
      
      if( isset($_REQUEST['buscar_cliente']) )
      {
         $this->buscar_cliente();
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
      if( isset($_POST['codcliente']) )
      {
         $this->resultados = $this->buscar_facturas();
      }
   }
   
   private function share_extensions()
   {
      $fsext = new fs_extension();
      $fsext->name = 'pagar_facturas';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_facturas';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-check" aria-hidden="true"></span>'
          . '<span class="hidden-xs">&nbsp; Pagar...</span>';
      $fsext->save();
      
      $fsext2 = new fs_extension();
      $fsext2->name = 'pagar_recibos';
      $fsext2->from = __CLASS__;
      $fsext2->to = 'ventas_recibos';
      $fsext2->type = 'button';
      $fsext2->text = '<span class="glyphicon glyphicon-check" aria-hidden="true"></span>'
          . '<span class="hidden-xs">&nbsp; Pagar...</span>';
      $fsext2->save();
   }
   
   private function buscar_cliente()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $cliente = new cliente();
      $json = array();
      foreach($cliente->search($_REQUEST['buscar_cliente']) as $cli)
      {
         $json[] = array('value' => $cli->nombre, 'data' => $cli->codcliente);
      }
      
      header('Content-Type: application/json');
      echo json_encode( array('query' => $_REQUEST['buscar_cliente'], 'suggestions' => $json) );
   }
   
   private function buscar_facturas()
   {
      $facturas = array();
      $sql = "SELECT * FROM facturascli WHERE pagada = false"
              ." AND fecha >= ".$this->serie->var2str($_POST['desde'])
              ." AND fecha <= ".$this->serie->var2str($_POST['hasta'])
              ." AND codserie = ".$this->serie->var2str($_POST['codserie'])
              ." AND codcliente = ".$this->serie->var2str($_POST['codcliente'])
              ." ORDER BY fecha ASC, codigo ASC";
      
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $facturas[] = new factura_cliente($d);
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
         $rec0 = new recibo_cliente();
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
      
      $fac0 = new factura_cliente();
      foreach($_POST['idfactura'] as $id)
      {
         $error = FALSE;
         
         $recibos = $rec0->all_from_factura($id);
         foreach($recibos as $recibo)
         {
            if($recibo->estado != 'Pagado')
            {
               $pago = new pago_recibo_cliente();
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
      $asiento->concepto = 'Cobro facturas de '.$this->cliente->nombre;
      $asiento->editable = FALSE;
      $asiento->importe = $importe;
      
      $eje0 = new ejercicio();
      $ejercicio = $eje0->get_by_fecha($this->today());
      if($ejercicio)
      {
         $asiento->codejercicio = $ejercicio->codejercicio;
      }
      
      $subcuenta_cli = $this->cliente->get_subcuenta($ejercicio->codejercicio);
      
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
      else if( !$subcuenta_cli )
      {
         $this->new_message("No se ha podido generar una subcuenta para el cliente "
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
         $partida1->idsubcuenta = $subcuenta_cli->idsubcuenta;
         $partida1->codsubcuenta = $subcuenta_cli->codsubcuenta;
         $partida1->haber = $importe;
         $partida1->coddivisa = $coddivisa;
         $partida1->tasaconv = $tasaconv;
         $partida1->codserie = $this->codserie;
         $partida1->save();
         
         $partida2 = new partida();
         $partida2->idasiento = $asiento->idasiento;
         $partida2->concepto = $asiento->concepto;
         $partida2->idsubcuenta = $subcaja->idsubcuenta;
         $partida2->codsubcuenta = $subcaja->codsubcuenta;
         $partida2->debe = $importe;
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
