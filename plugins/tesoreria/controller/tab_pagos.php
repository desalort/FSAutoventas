<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2014-2015  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_model('albaran_cliente.php');
require_model('pago.php');
require_model('pedido_cliente.php');

/**
 * Description of informe_pago
 *
 * @author carlos
 */
class tab_pagos extends fs_controller
{
   public $allow_delete;
   public $bloquear;
   public $coddivisa;
   public $pagado;
   public $pago;
   public $pagos;
   public $pendiente;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'pago', 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      $this->coddivisa = $this->empresa->coddivisa;
      $this->pagado = FALSE;
      $this->pago = new pago();
      $this->pagos = array();
      $this->pendiente = 0;
      
      $this->pago->cron_job();
      
      if( isset($_GET['delete']) ) /// eliminar pago
      {
         $pago = $this->pago->get($_GET['delete']);
         if($pago)
         {
            if( $pago->delete() )
            {
               $this->new_message('Pago eliminado correctamente.');
            }
            else
               $this->new_error_msg('Error al eliminar el pago.');
         }
         else
            $this->new_error_msg('Pago no encontrado.');
      }
      else if( isset($_POST['idpago']) ) /// modificar pago
      {
         $pago = $this->pago->get($_POST['idpago']);
         if($pago)
         {
            $pago->fecha = $_POST['fecha'];
            $pago->importe = floatval($_POST['importe']);
            $pago->nota = $_POST['nota'];
            
            if( $pago->save() )
            {
               $this->new_message('Pago modificado correctamente.');
            }
            else
               $this->new_error_msg('Error al modificar el pago.');
         }
         else
            $this->new_error_msg('Pago no encontrado.');
      }
      else if( isset($_POST['importe']) ) /// nuevo pago
      {
         if( isset($_REQUEST['albaran']) )
         {
            $this->pago->fase = ucfirst(FS_ALBARAN);
            $this->pago->idalbaran = $_REQUEST['id'];
         }
         else if( isset($_REQUEST['pedido']) )
         {
            $this->pago->fase = ucfirst(FS_PEDIDO);
            $this->pago->idpedido = $_REQUEST['id'];
         }
         
         $this->pago->fecha = $_POST['fecha'];
         $this->pago->importe = floatval($_POST['importe']);
         $this->pago->nota = $_POST['nota'];
         
         if( $this->pago->save() )
         {
            $this->new_message('Pago guardado correctamente.');
         }
         else
            $this->new_error_msg('Error al guardar el pago.');
      }
      
      $this->bloquear = TRUE;
      if( isset($_REQUEST['albaran']) )
      {
         $alb0 = new albaran_cliente();
         $albaran = $alb0->get($_REQUEST['id']);
         if($albaran)
         {
            /// buscamos pagos de la fase pedido
            /// un albarán puede ser una agrupación de muchos pedidos
            $idpedido = NULL;
            foreach($albaran->get_lineas() as $linea)
            {
               /// el idpedido lo tienes en las lineas del albarán
               if($linea->idpedido != $idpedido)
               {
                  $idpedido = $linea->idpedido;
                  $this->db->exec("UPDATE pagos SET idalbaran = ".$alb0->var2str($_REQUEST['id'])." WHERE idpedido = ".$alb0->var2str($idpedido).";");
               }
            }
            
            /// fase de albarán
            $this->pagos = $this->pago->all_from_albaran($_REQUEST['id']);
            
            $this->pendiente = $albaran->total;
            foreach($this->pagos as $i => $value)
            {
               $this->pendiente -= $value->importe;
               $this->pagos[$i]->pendiente = $this->pendiente;
            }
            
            if( abs($this->pendiente) < 0.1 )
            {
               $this->pagado = TRUE;
            }
            
            if($albaran->ptefactura)
            {
               $this->bloquear = FALSE;
            }
         }
      }
      else if( isset($_REQUEST['pedido']) )
      {
         /// fose de pedido
         $this->pagos = $this->pago->all_from_pedido($_REQUEST['id']);
         
         $ped0 = new pedido_cliente();
         $pedido = $ped0->get($_REQUEST['id']);
         if($pedido)
         {
            $this->pendiente = $pedido->total;
            foreach($this->pagos as $i => $value)
            {
               $this->pendiente -= $value->importe;
               $this->pagos[$i]->pendiente = $this->pendiente;
            }
            
            if( abs($this->pendiente) < 0.1 )
            {
               $this->pagado = TRUE;
            }
            
            if($pedido->status == 0)
            {
               $this->bloquear = FALSE;
            }
         }
      }
      
      $this->share_extensions();
   }
   
   public function url()
   {
      if( isset($_REQUEST['albaran']) )
      {
         return 'index.php?page='.__CLASS__.'&albaran=TRUE&id='.$_REQUEST['id'];
      }
      else if( isset($_REQUEST['pedido']) )
      {
         return 'index.php?page='.__CLASS__.'&pedido=TRUE&id='.$_REQUEST['id'];
      }
      else
         return parent::url();
   }
   
   private function share_extensions()
   {
      $extensiones = array(
          array(
              'name' => 'pago_albaran',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_albaran',
              'type' => 'tab',
              'text' => '<span class="glyphicon glyphicon-piggy-bank" aria-hidden="true"></span><span class="hidden-xs">&nbsp; Pagos</span>',
              'params' => '&albaran=TRUE'
          ),
          array(
              'name' => 'pago_pedido',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_pedido',
              'type' => 'tab',
              'text' => '<span class="glyphicon glyphicon-piggy-bank" aria-hidden="true"></span><span class="hidden-xs">&nbsp; Pagos</span>',
              'params' => '&pedido=TRUE'
          )
      );
      foreach($extensiones as $ext)
      {
         $fsext = new fs_extension($ext);
         if( !$fsext->save() )
         {
            $this->new_error_msg('Imposible guardar los datos de la extensión '.$ext['name'].'.');
         }
      }
   }
}
