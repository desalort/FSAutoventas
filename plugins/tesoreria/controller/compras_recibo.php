<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('asiento.php');
require_model('cuenta_banco.php');
require_model('cuenta_banco_proveedor.php');
require_model('ejercicio.php');
require_model('factura_proveedor.php');
require_model('forma_pago.php');
require_model('pago_recibo_proveedor.php');
require_model('partida.php');
require_model('proveedor.php');
require_model('recibo_factura.php');
require_model('recibo_proveedor.php');
require_model('subcuenta.php');

/**
 * Description of compras_recibo
 *
 * @author carlos
 */
class compras_recibo extends fs_controller
{
   public $allow_delete;
   public $ejercicio;
   public $factura;
   public $pagos;
   public $proveedor;
   public $recibo;
   public $recibos;
   public $subcuenta_pro;
   public $subcuentas_pago;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Recibo', 'compras', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      $recibo = new recibo_proveedor();
      $this->recibo = FALSE;
      if( isset($_REQUEST['id']) )
      {
         $this->recibo = $recibo->get($_REQUEST['id']);
      }
      
      if($this->recibo)
      {
         if( isset($_POST['fechav']) )
         {
            $this->recibo->importe = floatval($_POST['importe']);
            $this->recibo->fecha = $_POST['emitido'];
            $this->recibo->fechav = $_POST['fechav'];
            $this->recibo->iban = $_POST['iban'];
            $this->recibo->swift = $_POST['swift'];
            
            if( $this->recibo->save() )
            {
               $this->new_message('Datos guardados correctamente.');
            }
            else
               $this->new_error_msg('Error al guardar los datos.');
         }
         
         $this->page->title = 'Recibo '.$this->recibo->codigo;
         
         $fact = new factura_proveedor();
         $this->factura = $fact->get($this->recibo->idfactura);
         $this->check_recibo();
         
         $this->get_subcuentas();
         
         $pago = new pago_recibo_proveedor();
         if( isset($_POST['nuevopago']) )
         {
            $pago->idrecibo = $this->recibo->idrecibo;
            $pago->fecha = $_POST['fecha'];
            $pago->tipo = $_POST['tipo'];
            
            foreach($this->subcuentas_pago as $sc)
            {
               if($sc->codsubcuenta == $_POST['codsubcuenta'])
               {
                  $pago->idsubcuenta = $sc->idsubcuenta;
                  $pago->codsubcuenta = $sc->codsubcuenta;
               }
            }
            
            if($pago->tipo == 'Pago')
            {
               if( $this->empresa->contintegrada AND isset($_POST['generarasiento']) )
               {
                  $pago->idasiento = $this->nuevo_asiento_pago($pago, $this->ejercicio);
               }
               $this->recibo->estado = 'Pagado';
               $this->recibo->fechap = $_POST['fecha'];
            }
            else
            {
               if( $this->empresa->contintegrada AND isset($_POST['generarasiento']) )
               {
                  $pago->idasiento = $this->nuevo_asiento_devolucion($pago, $this->ejercicio);
               }
               $this->recibo->estado = 'Devuelto';
            }
            
            if( $pago->save() )
            {
               $this->new_message('Pago guardado correctamente.');
               $this->recibo->save();
            }
            else
               $this->new_error_msg('Error al guardar los pagos.');
         }
         else if( isset($_GET['deletep']) )
         {
            foreach($pago->all_from_recibo($this->recibo->idrecibo) as $pg)
            {
               if( $pg->idpagodevol == intval($_GET['deletep']) )
               {
                  if( $pg->delete() )
                  {
                     $this->new_message($pg->tipo.' eliminado correctamente');
                     
                     $this->recibo->estado = 'Emitido';
                     $this->recibo->save();
                  }
                  else
                     $this->new_error_msg('Error al eliminar el '.$pg->tipo);
                  
                  break;
               }
            }
         }
         
         $this->pagos = $pago->all_from_recibo($this->recibo->idrecibo);
         $this->recibos = $this->recibo->all_from_factura($this->recibo->idfactura);
         $this->sync_factura();
      }
      else
         $this->new_error_msg('Recibo no encontrado.');
   }
   
   public function url()
   {
      if( isset($this->recibo) )
      {
         return $this->recibo->url();
      }
      else
         return parent::url();
   }
   
   private function nuevo_asiento_devolucion(&$pago, $ejercicio)
   {
      $asiento = new asiento();
      $asiento->fecha = $pago->fecha;
      $asiento->codejercicio = $ejercicio->codejercicio;
      $asiento->concepto = $pago->tipo.' recibo compra '.$this->recibo->codigo.' - '.$this->recibo->nombreproveedor;
      $asiento->editable = FALSE;
      $asiento->importe = $this->recibo->importe;
      
      if($this->factura)
      {
         $asiento->tipodocumento = "Factura de proveedor";
         $asiento->documento = $this->factura->codigo;
      }
      
      if( !$ejercicio->abierto() )
      {
         $this->new_error_msg('El ejercicio '.$ejercicio->codejercicio.' está cerrado.');
      }
      else if( $asiento->save() )
      {
         $partida1 = new partida();
         $partida1->idasiento = $asiento->idasiento;
         $partida1->concepto = $asiento->concepto;
         $partida1->idsubcuenta = $this->subcuenta_pro->idsubcuenta;
         $partida1->codsubcuenta = $this->subcuenta_pro->codsubcuenta;
         $partida1->haber = $this->recibo->importe;
         $partida1->coddivisa = $this->recibo->coddivisa;
         $partida1->tasaconv = $this->recibo->tasaconv;
         $partida1->codserie = $this->recibo->codserie;
         $partida1->save();
         
         $partida2 = new partida();
         $partida2->idasiento = $asiento->idasiento;
         $partida2->concepto = $asiento->concepto;
         $partida2->idsubcuenta = $pago->idsubcuenta;
         $partida2->codsubcuenta = $pago->codsubcuenta;
         $partida2->debe = $this->recibo->importe;
         $partida2->coddivisa = $this->recibo->coddivisa;
         $partida2->tasaconv = $this->recibo->tasaconv;
         $partida2->codserie = $this->recibo->codserie;
         $partida2->save();
      }
      else
      {
         $this->new_error_msg('Error al guardar el asiento.');
      }
      
      return $asiento->idasiento;
   }
   
   private function nuevo_asiento_pago(&$pago, $ejercicio)
   {
      $asiento = new asiento();
      $asiento->fecha = $pago->fecha;
      $asiento->codejercicio = $ejercicio->codejercicio;
      $asiento->concepto = $pago->tipo.' recibo compra '.$this->recibo->codigo.' - '.$this->recibo->nombreproveedor;
      $asiento->editable = FALSE;
      $asiento->importe = $this->recibo->importe;
      
      if($this->factura)
      {
         $asiento->tipodocumento = "Factura de proveedor";
         $asiento->documento = $this->factura->codigo;
      }
      
      if( !$ejercicio->abierto() )
      {
         $this->new_error_msg('El ejercicio '.$ejercicio->codejercicio.' está cerrado.');
      }
      else if( $asiento->save() )
      {
         $partida1 = new partida();
         $partida1->idasiento = $asiento->idasiento;
         $partida1->concepto = $asiento->concepto;
         $partida1->idsubcuenta = $this->subcuenta_pro->idsubcuenta;
         $partida1->codsubcuenta = $this->subcuenta_pro->codsubcuenta;
         $partida1->debe = $this->recibo->importe;
         $partida1->coddivisa = $this->recibo->coddivisa;
         $partida1->tasaconv = $this->recibo->tasaconv;
         $partida1->codserie = $this->recibo->codserie;
         $partida1->save();
         
         $partida2 = new partida();
         $partida2->idasiento = $asiento->idasiento;
         $partida2->concepto = $asiento->concepto;
         $partida2->idsubcuenta = $pago->idsubcuenta;
         $partida2->codsubcuenta = $pago->codsubcuenta;
         $partida2->haber = $this->recibo->importe;
         $partida2->coddivisa = $this->recibo->coddivisa;
         $partida2->tasaconv = $this->recibo->tasaconv;
         $partida2->codserie = $this->recibo->codserie;
         $partida2->save();
      }
      else
      {
         $this->new_error_msg('Error al guardar el asiento.');
      }
      
      return $asiento->idasiento;
   }
   
   private function get_subcuentas()
   {
      $this->ejercicio = FALSE;
      $this->subcuenta_pro = FALSE;
      $this->subcuentas_pago = array();
      
      $subcuenta = new subcuenta();
      $pro = new proveedor();
      $this->proveedor = $pro->get($this->recibo->codproveedor);
      if($this->proveedor)
      {
         $eje0 = new ejercicio();
         if( isset($_POST['fecha']) )
         {
            $this->ejercicio = $eje0->get_by_fecha($_POST['fecha']);
         }
         else
            $this->ejercicio = $eje0->get_by_fecha($this->today());
         
         if($this->ejercicio)
         {
            $this->subcuenta_pro = $this->proveedor->get_subcuenta($this->ejercicio->codejercicio);
            
            /// añadimos la subcuenta de la cuenta bancaria
            $cb0 = new cuenta_banco();
            $fp0 = new forma_pago();
            $formap = $fp0->get($this->recibo->codpago);
            if($formap)
            {
               if($formap->codcuenta)
               {
                  $cuentab = $cb0->get($formap->codcuenta);
                  if($cuentab)
                  {
                     $subc = $subcuenta->get_by_codigo($cuentab->codsubcuenta, $this->ejercicio->codejercicio);
                     if($subc)
                     {
                        $this->subcuentas_pago[] = $subc;
                     }
                  }
               }
            }
            
            /// añadimos todas las subcuentas de caja
            $sql = "SELECT * FROM co_subcuentas WHERE idcuenta IN "
                    . "(SELECT idcuenta FROM co_cuentas WHERE codejercicio = "
                    . $this->ejercicio->var2str($this->ejercicio->codejercicio)." AND idcuentaesp = 'CAJA');";
            $data = $this->db->select($sql);
            if($data)
            {
               foreach($data as $d)
               {
                  $this->subcuentas_pago[] = new subcuenta($d);
               }
            }
         }
         else
         {
            $this->new_error_msg('Ejercicio no encontrado.');
         }
      }
   }
   
   private function check_recibo()
   {
      if($this->factura)
      {
         $this->recibo->nombreproveedor = $this->factura->nombre;
         $this->recibo->cifnif = $this->factura->cifnif;
         
         if($this->recibo->estado != 'Pagado')
         {
            $this->recibo->coddivisa = $this->factura->coddivisa;
            $this->recibo->tasaconv = $this->factura->tasaconv;
            $this->recibo->codpago = $this->factura->codpago;
            $this->recibo->codserie = $this->factura->codserie;
            
            $cbc = new cuenta_banco_proveedor();
            foreach($cbc->all_from_proveedor($this->recibo->codproveedor) as $cuenta)
            {
               if( is_null($this->recibo->codcuenta) )
               {
                  $this->recibo->codcuenta = $cuenta->codcuenta;
                  $this->recibo->iban = $cuenta->iban;
                  $this->recibo->swift = $cuenta->swift;
               }
            }
         }
         
         $this->recibo->save();
      }
   }
   
   private function sync_factura()
   {
      $recibo_factura = new recibo_factura();
      $recibo_factura->sync_factura_prov($this->factura);
      
      /// ¿Hay errores?
      foreach($recibo_factura->errors as $err)
      {
         $this->new_error_msg($err);
      }
      
      $recibo_factura->errors = array();
   }
}
