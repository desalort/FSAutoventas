<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('forma_pago.php');
require_model('forma_pago_plazo.php');

/**
 * Description of contabilidad_plazos_pago
 *
 * @author carlos
 */
class contabilidad_plazos_pago extends fs_controller
{
   public $allow_delete;
   public $forma_pago;
   public $pendiente;
   public $plazos;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Plazos de pago', 'contabilidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->share_extensions();
      
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      $this->forma_pago = FALSE;
      if( isset($_REQUEST['cod']) )
      {
         $fp = new forma_pago();
         $this->forma_pago = $fp->get($_REQUEST['cod']);
      }
      
      if($this->forma_pago)
      {
         $fpplazo = new forma_pago_plazo;
         
         if( isset($_POST['id']) )
         {
            /// modificar plazo
            $plazo = $fpplazo->get($_POST['id']);
            if($plazo)
            {
               $plazo->dias = intval($_POST['dias']);
               $plazo->aplazado = floatval($_POST['aplazado']);
               if( $plazo->save() )
               {
                  $this->new_message('Datos modificados correctamente.');
               }
               else
                  $this->new_error_msg('Error al modificar el plazo.');
            }
            else
               $this->new_error_msg('Plazo no encontrado.');
         }
         else if( isset($_GET['nueva']) )
         {
            /// nuevo plazo por defecto
            $plazo = new forma_pago_plazo();
            $plazo->codpago = $this->forma_pago->codpago;
            $plazo->dias = $this->vencimiento2dias($this->forma_pago->vencimiento);
            $plazo->aplazado = 100;
            if( $plazo->save() )
            {
               $this->new_message('Datos guardados correctamente.');
            }
            else
               $this->new_error_msg('Error al modificar el plazo.');
         }
         else if( isset($_POST['dias']) )
         {
            /// nuevo plazo
            $plazo = new forma_pago_plazo();
            $plazo->codpago = $this->forma_pago->codpago;
            $plazo->dias = intval($_POST['dias']);
            $plazo->aplazado = floatval($_POST['aplazado']);
            if( $plazo->save() )
            {
               $this->new_message('Datos guardados correctamente.');
            }
            else
               $this->new_error_msg('Error al modificar el plazo.');
         }
         else if( isset($_GET['delete']) )
         {
            /// eliminar plazo
            $plazo = $fpplazo->get($_GET['delete']);
            if($plazo)
            {
               if( $plazo->delete() )
               {
                  $this->new_message('Plazo eliminado correctamente.');
               }
               else
                  $this->new_error_msg('Error al eliminar el plazo.');
            }
            else
               $this->new_error_msg('Plazo no encontrado.');
         }
         
         $this->plazos = $fpplazo->all_from($this->forma_pago->codpago);
         
         $this->pendiente = 100;
         if($this->plazos)
         {
            foreach($this->plazos as $pl)
            {
               $this->pendiente -= $pl->aplazado;
            }
            
            $this->pendiente = round($this->pendiente, 2);
            if($this->pendiente > 0)
            {
               $this->new_message('Falta un '.$this->pendiente.'% por asignar.');
            }
            else if($this->pendiente < 0)
            {
               $this->new_message('Sobra un '.abs($this->pendiente).'% asignado.');
            }
            else
            {
               /// nos guardamos el último plazo para ponerlo como vencimiento de la forma de pago
               foreach($this->plazos as $pl)
               {
                  if($pl->dias > 0)
                  {
                     $months = $pl->dias / 30;
                     if( is_int($months) )
                     {
                        $this->forma_pago->vencimiento = '+'.$months.'month';
                     }
                     else
                     {
                        $this->forma_pago->vencimiento = '+'.$pl->dias.'day';
                     }
                  }
               }
               
               $this->forma_pago->save();
            }
         }
      }
      else
         $this->new_error_msg('Forma de pago no encontrada.');
   }
   
   private function share_extensions()
   {
      $fsext = new fs_extension();
      $fsext->name = 'btn_formas_pago';
      $fsext->from = __CLASS__;
      $fsext->to = 'contabilidad_formas_pago';
      $fsext->type = 'config';
      $fsext->text = 'button_plazos';
      $fsext->save();
   }
   
   public function url()
   {
      if($this->forma_pago)
      {
         return 'index.php?page='.__CLASS__.'&cod='.$this->forma_pago->codpago;
      }
      else
         return parent::url();
   }
   
   private function vencimiento2dias($cod)
   {
      $vencimientos = array(
          '+1day' => 1,
          '+1week' => 7,
          '+2week' => 14,
          '+3week' => 21,
          '+1month' => 30,
          '+2month' => 60,
          '+3month' => 90,
          '+4month' => 120,
          '+5month' => 150,
          '+6month' => 180,
          '+7month' => 210,
          '+8month' => 240,
          '+9month' => 270,
          '+10month' => 300,
          '+11month' => 330,
          '+12month' => 365,
      );
      
      if( isset($vencimientos[$cod]) )
      {
         return $vencimientos[$cod];
      }
      else
      {
         return 30;
      }
   }
}
