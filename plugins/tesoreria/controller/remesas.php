<?php

/**
 * @author Carlos García Gómez         neorazorx@gmail.com
 * @author Francesc Pineda Segarra     shawe.ewahs@gmail.com
 * @copyright 2015-2016, Carlos García Gómez. All Rights Reserved.
 * @copyright 2015-2016, Francesc Pineda Segarra. All Rights Reserved.
 */

use AbcAeffchen\SepaUtilities\SepaUtilities;
use AbcAeffchen\Sephpa\SephpaDirectDebit;

require_once 'plugins/tesoreria/sephpa/SepaUtilities.php';
require_once 'plugins/tesoreria/sephpa/SephpaDirectDebit.php';
require_once 'plugins/tesoreria/sephpa/payment-collections/SepaPaymentCollection.php';
require_once 'plugins/tesoreria/sephpa/payment-collections/SepaDirectDebit00800102.php';

require_model('cliente.php');
require_model('cuenta_banco.php');
require_model('forma_pago.php');
require_model('pago_recibo_cliente.php');
require_model('recibo_cliente.php');
require_model('remesa.php');

/**
 * Description of remesas
 *
 * @author carlos
 */
class remesas extends fs_controller
{
   public $allow_delete;
   public $cuentab;
   public $cuentab_s;
   public $forma_pago;
   public $remesa;
   public $resultados;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Remesas', 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      $this->cuentab = new cuenta_banco();
      $this->cuentab_s = FALSE;
      $this->forma_pago = new forma_pago();
      $this->resultados = array();
      
      $reme = new remesa();
      if( isset($_POST['nueva']) )
      {
         $reme->descripcion = $_POST['descripcion'];
         $reme->coddivisa = $this->empresa->coddivisa;
         $reme->codpago = $_POST['codpago'];
         $reme->fechacargo = $_POST['fechacargo'];
         
         /// buscamos la cuenta bancaria asociada a la forma de pago
         $formap = $this->forma_pago->get($_POST['codpago']);
         if($formap)
         {
            $cuentab = $this->cuentab->get($formap->codcuenta);
            if($cuentab)
            {
               $reme->codcuenta = $cuentab->codcuenta;
               $reme->iban = $cuentab->iban;
               $reme->swift = $cuentab->swift;
            }
         }
         
         if( is_null($reme->codcuenta) )
         {
            $this->new_error_msg('La <a href="'.$this->forma_pago->url().'">forma de pago</a>'
                    . ' seleccionada no tiene una cuenta bancaria asociada.');
         }
         else if( $reme->save() )
         {
            $this->new_message('Datos guardados correctamente.');
            header('Location: '.$reme->url());
         }
         else
         {
            $this->new_error_msg('Error al guardar los datos.');
         }
         
         $this->resultados = $reme->all();
      }
      else if( isset($_REQUEST['id']) )
      {
         $this->remesa = $reme->get($_REQUEST['id']);
         if($this->remesa)
         {
            $this->cuentab_s = $this->cuentab->get($this->remesa->codcuenta);
            
            if( isset($_GET['download']) )
            {
               $recli = new recibo_cliente();
               $this->resultados = $recli->all_from_remesa($this->remesa->idremesa);
               
               $this->download();
            }
            else
            {
               $this->template = 'editar_remesa';
               $this->modificar_remesa();
               
               $recli = new recibo_cliente();
               $this->resultados = $recli->all_from_remesa($this->remesa->idremesa);
               
               /// calculamos el total
               $this->remesa->total = 0;
               foreach($this->resultados as $res)
               {
                  $this->remesa->total += $res->importe;
               }
               $this->remesa->save();
            }
         }
         else
         {
            $this->new_error_msg('Remesa no encontrada.');
         }
      }
      else if( isset($_GET['delete']) )
      {
         $remesa = $reme->get($_GET['delete']);
         if($remesa)
         {
            if( $remesa->delete() )
            {
               $this->new_message('Remesa eliminada correctamente.');
            }
            else
            {
               $this->new_error_msg('Imposible eliminar la remesa.');
            }
         }
         
         $this->resultados = $reme->all();
         $this->liberar_recibos();
      }
      else
      {
         $this->resultados = $reme->all();
         $this->liberar_recibos();
      }
   }
   
   public function formas_pago_domiciliadas()
   {
      $lista = array();
      
      foreach($this->forma_pago->all() as $fp)
      {
         if($fp->domiciliado)
         {
            $lista[] = $fp;
         }
      }
      
      return $lista;
   }
   
   public function recibos_disponibles()
   {
      $lista = array();
      
      $sql = "SELECT * FROM reciboscli WHERE idremesa IS NULL AND estado != 'Pagado'"
              . " AND fechav <= ".$this->remesa->var2str($this->remesa->fechacargo)
              . " AND (codpago = ".$this->remesa->var2str($this->remesa->codpago)
              . " OR codpago IN (SELECT codpago FROM formaspago WHERE domiciliado"
              . " AND codcuenta = ".$this->remesa->var2str($this->remesa->codcuenta)."))"
              . " ORDER BY fechav ASC;";
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new recibo_cliente($d);
         }
      }
      
      return $lista;
   }
   
   private function modificar_remesa()
   {
      if( isset($_POST['descripcion']) )
      {
         $this->remesa->descripcion = $_POST['descripcion'];
         $this->remesa->fecha = $_POST['fecha'];
         $this->remesa->fechacargo = $_POST['fechacargo'];
         $this->remesa->estado = $_POST['estado'];
         
         if( $this->remesa->save() )
         {
            $this->new_message('Datos guardados correctamente.');
         }
         else
         {
            $this->new_error_msg('Error al guardar los datos.');
         }
      }
      else if( isset($_GET['pagar']) )
      {
         $this->pagar_recibos();
      }
      else if( !$this->remesa->editable() AND (isset($_POST['addrecibo']) OR isset($_GET['sacar'])) )
      {
         $this->new_error_msg('Solamente se pueden hacer cambios en remesas <b>en trámite</b>.');
      }
      else if( isset($_POST['addrecibo']) )
      {
         $nuevos = 0;
         $recli = new recibo_cliente();
         foreach($_POST['addrecibo'] as $id)
         {
            $recibo = $recli->get($id);
            if($recibo)
            {
               $recibo->idremesa = $this->remesa->idremesa;
               if( $recibo->save() )
               {
                  $nuevos++;
               }
            }
         }
         
         $this->new_message($nuevos.' recibos añadidos a la remesa.');
      }
      else if( isset($_GET['sacar']) )
      {
         $recli = new recibo_cliente();
         $recibo = $recli->get($_GET['sacar']);
         if($recibo)
         {
            $recibo->idremesa = NULL;
            if( $recibo->save() )
            {
               $this->new_message('Recibo '.$recibo->codigo.' excluido.');
            }
            else
            {
               $this->new_error_msg('Error al excluir el recibo '.$recibo->codigo);
            }
         }
      }
   }
   
   private function download()
   {
      $this->template = FALSE;
      
      // ID único de la remesa: id de remesa + fecha de generación
      $paymentInfoId = $this->empresa->cifnif.'-'.date('dmy-H:i', strtotime($this->remesa->fecha));
      // Formato de documento a utilizar
      $sepaPAIN = SephpaDirectDebit::SEPA_PAIN_008_001_02;
      // Comprobar y sanear valores, permite evitar validación de IBAN, útil para pruebas con IBANs falsos
      $checkAndSanitize = FALSE;
      /**
       * normal direct debit : LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT = 'CORE';
       * urgent direct debit : LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT_D_1 = 'COR1';
       * business direct debit : LOCAL_INSTRUMENT_BUSINESS_2_BUSINESS = 'B2B';
       */

      $localInstrument = SepaUtilities::LOCAL_INSTRUMENT_CORE_DIRECT_DEBIT;
      /**
      * first direct debit : SEQUENCE_TYPE_FIRST = 'FRST';
      * recurring direct debit : SEQUENCE_TYPE_RECURRING = 'RCUR';
      * one time direct debit : SEQUENCE_TYPE_ONCE = 'OOFF';
      * final direct debit : SEQUENCE_TYPE_FINAL = 'FNAL';
      */
      $sequenceType = SepaUtilities::SEQUENCE_TYPE_RECURRING;

      $directDebitFile = new SephpaDirectDebit($this->empresa->nombre, $paymentInfoId, $sepaPAIN, $checkAndSanitize);

      $creationDateTime = date('Y-m-d\TH:i:s', strtotime($this->remesa->fecha));

      // at least one in every SEPA file. No limit.
      $directDebitCollection = $directDebitFile->addCollection(array(
         // needed information about the payer
            'pmtInfId' => $paymentInfoId,                               // ID of the payment collection
            'lclInstrm' => $localInstrument,
            'seqTp' => $sequenceType,
            'cdtr' => substr($this->empresa->nombre, 0, 70),            // (max 70 characters)
            'iban' => $this->remesa->iban,                        // IBAN of the Creditor
            'bic' => $this->remesa->swift,                        // BIC of the Creditor
            'ci' => $this->empresa->cifnif,                             // Creditor-Identifier (CIF/NIF sólo válido para España!)
         // optional
            'ccy' => $this->remesa->coddivisa,                         // Currency. Default is 'EUR'
         //   'btchBookg' => 'true',                                      // BatchBooking, only 'true' or 'false'
         // 'ctgyPurp' => ,                                              // Do not use this if you not know how. For further information read the SEPA documentation
         // 'ultmtCdtr' => substr($this->empresa->nombre, 0, 70),       // just an information, this do not affect the payment (max 70 characters)
            'reqdColltnDt' => date('Y-m-d', strtotime($this->remesa->fechacargo)), // Date: YYYY-MM-DD
      ));
      
      // at least one in every DirectDebitCollection. No limit.
      foreach($this->resultados as $recibo)
      {
         if(TRUE)
         {
            $directDebitCollection->addPayment(array(
               // needed information about the
               'pmtId' => $recibo->codigo,                              // ID of the payment (EndToEndId)
               'instdAmt' => $recibo->importe,                          // amount
               'mndtId' => $recibo->codigo,                            // Mandate ID
               'dtOfSgntr' => date('Y-m-d', strtotime($recibo->fecha)),  // Date of signature
               'dbtr' => substr($recibo->nombrecliente, 0, 70),        // (max 70 characters)
               //TODO: IBAN, BIC
               'bic' => $recibo->swift,          // BIC of the Debtor
               'iban' => $recibo->iban,          // IBAN of the Debtor
               // optional
               // 'amdmntInd' => 'true',                                // Did the mandate change
               // 'elctrncSgntr' => 'test',                             // do not use this if there is a paper-based mandate
               //'ultmtDbtr' => substr($factura->nombrecliente, 0, 70),   // just an information, this do not affect the payment (max 70 characters)
               // 'purp' => ,                                              // Do not use this if you not know how. For further information read the SEPA documentation
         // Concepto del recibo
               'rmtInf' => $recibo->codigo,                             // unstructured information about the remittance (max 140 characters)
               // only use this if 'amdmntInd' is 'true'. at least one must be used
               // 'orgnlMndtId' => 'Original-Mandat-ID',
               // 'orgnlCdtrSchmeId_nm' => 'Creditor-Identifier Name',
               // 'orgnlCdtrSchmeId_id' => 'Creditor-Identifier ID',
               // 'orgnlDbtrAcct_iban' => 'DE87200500001234567890',        // Original Debtor Account
               // 'orgnlDbtrAgt' => 'SMNDA'                                // only 'SMNDA' allowed if used
            ));
         }
      }
      
      $this->remesa->estado = "En trámite";
      if( !$this->remesa->save() )
      {
         $this->new_error_msg("¡Imposible modificar la remesa!");
      }
      
      $directDebitFile->downloadSepaFile('Remesa_'.$this->remesa->idremesa.'_'.$this->remesa->fecha
              .'_SEPA_'.$localInstrument.''.'.xml', $creationDateTime, $this->empresa->cifnif);
   }
   
   private function liberar_recibos()
   {
      $sql = "UPDATE reciboscli SET idremesa = NULL WHERE idremesa NOT IN (SELECT idremesa FROM remesas_sepa);";
      $this->db->exec($sql);
   }
   
   private function pagar_recibos()
   {
      $pagados = 0;
      
      $eje0 = new ejercicio();
      $ejercicio = $eje0->get_by_fecha($this->remesa->fechacargo);
      
      $cuentab = $this->cuentab->get($this->remesa->codcuenta);
      if($cuentab)
      {
         $subc0 = new subcuenta();
         $subcaja = $subc0->get_by_codigo($cuentab->codsubcuenta, $ejercicio->codejercicio);
         if($subcaja)
         {
            $cli0 = new cliente();
            $recli = new recibo_cliente();
            foreach( $recli->all_from_remesa($this->remesa->idremesa) as $recibo )
            {
               $cliente = $cli0->get($recibo->codcliente);
               if($cliente)
               {
                  $subcli = $cliente->get_subcuenta($ejercicio->codejercicio);
                  if($subcli)
                  {
                     $pago = new pago_recibo_cliente();
                     $pago->idrecibo = $recibo->idrecibo;
                     $pago->idremesa = $this->remesa->idremesa;
                     $pago->fecha = $this->remesa->fechacargo;
                     $pago->idsubcuenta = $subcaja->idsubcuenta;
                     $pago->codsubcuenta = $subcaja->codsubcuenta;
                     $pago->idasiento = $this->nuevo_asiento_pago($recibo, $pago, $ejercicio, $subcli);
                     if( $pago->save() )
                     {
                        $recibo->estado = 'Pagado';
                        $recibo->fechap = $this->remesa->fechacargo;
                        if( $recibo->save() )
                        {
                           $pagados++;
                        }
                     }
                     else
                     {
                        $this->new_error_msg('Imposible guardar el pago del recibo '.$recibo->codigo);
                     }
                  }
               }
            }
            
            $this->new_message($pagados.' recibos marcados como pagados.');
         }
         else
         {
            $this->new_error_msg('La <a href="'.$cuentab->url().'">cuenta bancaria</a> no está asociada a una subcuenta.');
         }
      }
      else
      {
         $this->new_error_msg('Cuenta bancaria no encontrada.');
      }
   }
   
   /**
    * 
    * @param recibo_cliente $recibo
    * @param pago_recibo_cliente $pago
    * @param ejercicio $ejercicio
    * @param subcuenta $subcli
    * @return type
    */
   private function nuevo_asiento_pago(&$recibo, &$pago, &$ejercicio, &$subcli)
   {
      $asiento = new asiento();
      $asiento->fecha = $pago->fecha;
      $asiento->codejercicio = $ejercicio->codejercicio;
      $asiento->editable = FALSE;
      $asiento->importe = $recibo->importe;
      
      if($pago->tipo == 'Pago')
      {
         $asiento->concepto = 'Cobro recibo '.$recibo->codigo.' - '.$recibo->nombrecliente;
      }
      else
      {
         $asiento->concepto = $pago->tipo.' recibo '.$recibo->codigo.' - '.$recibo->nombrecliente;
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
         $partida1->idsubcuenta = $subcli->idsubcuenta;
         $partida1->codsubcuenta = $subcli->codsubcuenta;
         $partida1->haber = $recibo->importe;
         $partida1->coddivisa = $recibo->coddivisa;
         $partida1->tasaconv = $recibo->tasaconv;
         $partida1->codserie = $recibo->codserie;
         $partida1->save();
         
         $partida2 = new partida();
         $partida2->idasiento = $asiento->idasiento;
         $partida2->concepto = $asiento->concepto;
         $partida2->idsubcuenta = $pago->idsubcuenta;
         $partida2->codsubcuenta = $pago->codsubcuenta;
         $partida2->debe = $recibo->importe;
         $partida2->coddivisa = $recibo->coddivisa;
         $partida2->tasaconv = $recibo->tasaconv;
         $partida2->codserie = $recibo->codserie;
         $partida2->save();
      }
      else
      {
         $this->new_error_msg('Error al guardar el asiento.');
      }
      
      return $asiento->idasiento;
   }
}
