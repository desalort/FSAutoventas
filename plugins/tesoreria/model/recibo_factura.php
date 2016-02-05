<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('cuenta_banco_cliente.php');
require_model('cuenta_banco_proveedor.php');
require_model('factura_cliente.php');
require_model('factura_proveedor.php');
require_model('forma_pago.php');
require_model('forma_pago_plazo.php');
require_model('recibo_cliente.php');
require_model('recibo_proveedor.php');

/**
 * Description of recibo_factura
 *
 * @author carlos
 */
class recibo_factura
{
   private $cbc;
   private $cbp;
   private $factura_cliente;
   private $factura_proveedor;
   private $forma_pago;
   private $plazo_pago;
   private $recibo_cliente;
   private $recibo_proveedor;
   
   public $errors;
   
   public function __construct()
   {
      $this->cbc = new cuenta_banco_cliente();
      $this->cbp = new cuenta_banco_proveedor();
      $this->factura_cliente = new factura_cliente();
      $this->factura_proveedor = new factura_proveedor();
      $this->forma_pago = new forma_pago();
      $this->plazo_pago = new forma_pago_plazo();
      $this->recibo_cliente = new recibo_cliente();
      $this->recibo_proveedor = new recibo_proveedor();
      
      $this->errors = array();
   }
   
   private function new_error_msg($msg)
   {
      $this->errors[] = $msg;
   }
   
   /**
    * 
    * @param factura_cliente $factura
    */
   public function sync_factura_cli(&$factura)
   {
      if($factura)
      {
         $recibos = $this->recibo_cliente->all_from_factura($factura->idfactura);
         
         if($factura->pagada AND count($recibos) == 0)
         {
            /// no hacemos nada
         }
         else if( count($recibos) == 0 )
         {
            $formap = $this->forma_pago->get($factura->codpago);
            if($formap)
            {
               $plazos = $this->plazo_pago->all_from($formap->codpago);
               if($plazos)
               {
                  $pendiente = $factura->total;
                  foreach($plazos as $i => $pla)
                  {
                     $recibo = new recibo_cliente();
                     $recibo->cifnif = $factura->cifnif;
                     $recibo->coddivisa = $factura->coddivisa;
                     $recibo->tasaconv = $factura->tasaconv;
                     $recibo->codpago = $factura->codpago;
                     $recibo->codserie = $factura->codserie;
                     $recibo->codcliente = $factura->codcliente;
                     $recibo->nombrecliente = $factura->nombrecliente;
                     $recibo->estado = 'Emitido';
                     $recibo->fecha = $factura->fecha;
                     $recibo->fechav = Date('d-m-Y', strtotime($factura->fecha.' +'.$pla->dias.'days'));
                     $recibo->idfactura = $factura->idfactura;
                     
                     if( $i+1 == count($plazos) )
                     {
                        $recibo->importe = round($pendiente, FS_NF0);
                     }
                     else
                     {
                        $recibo->importe = round($factura->total*$pla->aplazado/100, FS_NF0);
                        $pendiente -= $recibo->importe;
                     }
                     
                     $recibo->numero = $recibo->new_numero($recibo->idfactura);
                     $recibo->codigo = $factura->codigo.'-'.sprintf('%02s', $recibo->numero);
                     
                     foreach($this->cbc->all_from_cliente($factura->codcliente) as $cuenta)
                     {
                        if( is_null($recibo->codcuenta) OR $cuenta->principal )
                        {
                           $recibo->codcuenta = $cuenta->codcuenta;
                           $recibo->iban = $cuenta->iban;
                           $recibo->swift = $cuenta->swift;
                        }
                     }
                     
                     if( $recibo->save() )
                     {
                        $recibos[] = $recibo;
                     }
                     else
                     {
                        $this->new_error_msg('Error al generar el recibo.');
                     }
                  }
               }
               else
               {
                  $recibo = new recibo_cliente();
                  $recibo->cifnif = $factura->cifnif;
                  $recibo->coddivisa = $factura->coddivisa;
                  $recibo->tasaconv = $factura->tasaconv;
                  $recibo->codpago = $factura->codpago;
                  $recibo->codserie = $factura->codserie;
                  $recibo->codcliente = $factura->codcliente;
                  $recibo->nombrecliente = $factura->nombrecliente;
                  $recibo->estado = 'Emitido';
                  $recibo->fecha = $factura->fecha;
                  $recibo->fechav = Date('d-m-Y', strtotime($factura->fecha.' '.$formap->vencimiento));
                  $recibo->idfactura = $factura->idfactura;
                  
                  $recibo->importe = $factura->total;
                  
                  $recibo->numero = $recibo->new_numero($recibo->idfactura);
                  $recibo->codigo = $factura->codigo.'-'.sprintf('%02s', $recibo->numero);
                  
                  foreach($this->cbc->all_from_cliente($factura->codcliente) as $cuenta)
                  {
                     if( is_null($recibo->codcuenta) OR $cuenta->principal )
                     {
                        $recibo->codcuenta = $cuenta->codcuenta;
                        $recibo->iban = $cuenta->iban;
                        $recibo->swift = $cuenta->swift;
                     }
                  }
                  
                  if( $recibo->save() )
                  {
                     $recibos[] = $recibo;
                  }
                  else
                  {
                     $this->new_error_msg('Error al generar el recibo.');
                  }
               }
            }
         }
         else
         {
            $pagado = 0;
            foreach($recibos as $res)
            {
               if($res->estado == 'Pagado')
               {
                  $pagado += $res->importe;
               }
            }
            
            $factura->pagada = ( $pagado >= $factura->total );
            $factura->save();
         }
         
         return $recibos;
      }
      else
      {
         return array();
      }
   }
   
   /**
    * 
    * @param factura_proveedor $factura
    */
   public function sync_factura_prov(&$factura)
   {
      if($factura)
      {
         $recibos = $this->recibo_proveedor->all_from_factura($factura->idfactura);
         
         if( count($recibos) == 0 AND $factura->pagada )
         {
            /// no hacemos nada
         }
         else if( count($recibos) == 0 )
         {
            $formap = $this->forma_pago->get($factura->codpago);
            if($formap)
            {
               $plazos = $this->plazo_pago->all_from($formap->codpago);
               if($plazos)
               {
                  $pendiente = $factura->total;
                  foreach($plazos as $i => $pla)
                  {
                     $recibo = new recibo_proveedor();
                     $recibo->cifnif = $factura->cifnif;
                     $recibo->coddivisa = $factura->coddivisa;
                     $recibo->tasaconv = $factura->tasaconv;
                     $recibo->codproveedor = $factura->codproveedor;
                     $recibo->nombreproveedor = $factura->nombre;
                     $recibo->estado = 'Emitido';
                     $recibo->fecha = $factura->fecha;
                     $recibo->fechav = Date('d-m-Y', strtotime($factura->fecha.' +'.$pla->dias.'days'));
                     $recibo->idfactura = $factura->idfactura;
                     $recibo->codpago = $factura->codpago;
                     $recibo->codserie = $factura->codserie;
                     
                     if( $i+1 == count($plazos) )
                     {
                        $recibo->importe = round($pendiente, FS_NF0);
                     }
                     else
                     {
                        $recibo->importe = round($factura->total*$pla->aplazado/100, FS_NF0);
                        $pendiente -= $recibo->importe;
                     }
                     
                     $recibo->numero = $recibo->new_numero($recibo->idfactura);
                     $recibo->codigo = $factura->codigo.'-'.sprintf('%02s', $recibo->numero);
                     
                     foreach($this->cbp->all_from_proveedor($recibo->codproveedor) as $cuenta)
                     {
                        if( is_null($recibo->codcuenta) OR $cuenta->principal )
                        {
                           $recibo->codcuenta = $cuenta->codcuenta;
                           $recibo->iban = $cuenta->iban;
                           $recibo->swift = $cuenta->swift;
                        }
                     }
                     
                     if( $recibo->save() )
                     {
                        $recibos[] = $recibo;
                     }
                     else
                     {
                        $this->new_error_msg('Error al generar el recibo.');
                     }
                  }
               }
               else
               {
                  $recibo = new recibo_proveedor();
                  $recibo->cifnif = $factura->cifnif;
                  $recibo->coddivisa = $factura->coddivisa;
                  $recibo->tasaconv = $factura->tasaconv;
                  $recibo->codproveedor = $factura->codproveedor;
                  $recibo->nombreproveedor = $factura->nombre;
                  $recibo->estado = 'Emitido';
                  $recibo->fecha = $factura->fecha;
                  $recibo->fechav = Date('d-m-Y', strtotime($factura->fecha.' '.$formap->vencimiento));
                  $recibo->idfactura = $factura->idfactura;
                  $recibo->codpago = $factura->codpago;
                  $recibo->codserie = $factura->codserie;
                  
                  $recibo->importe = $factura->total;
                  
                  $recibo->numero = $recibo->new_numero($recibo->idfactura);
                  $recibo->codigo = $factura->codigo.'-'.sprintf('%02s', $recibo->numero);
                  
                  foreach($this->cbp->all_from_proveedor($recibo->codproveedor) as $cuenta)
                  {
                     if( is_null($recibo->codcuenta) OR $cuenta->principal )
                     {
                        $recibo->codcuenta = $cuenta->codcuenta;
                        $recibo->iban = $cuenta->iban;
                        $recibo->swift = $cuenta->swift;
                     }
                  }
                  
                  if( $recibo->save() )
                  {
                     $recibos[] = $recibo;
                  }
                  else
                  {
                     $this->new_error_msg('Error al generar el recibo.');
                  }
               }
            }
         }
         else
         {
            $pagado = 0;
            foreach($recibos as $res)
            {
               if($res->estado == 'Pagado')
               {
                  $pagado += $res->importe;
               }
            }
            
            $factura->pagada = ( $pagado >= $factura->total );
            $factura->save();
         }
         
         return $recibos;
      }
      else
      {
         return array();
      }
   }
}
