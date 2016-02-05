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
require_model('albaran_proveedor.php');
require_model('asiento.php');
require_model('asiento_factura.php');
require_model('cliente.php');
require_model('ejercicio.php');
require_model('factura_cliente.php');
require_model('factura_proveedor.php');
require_model('forma_pago.php');
require_model('partida.php');
require_model('proveedor.php');
require_model('regularizacion_iva.php');
require_model('serie.php');
require_model('subcuenta.php');

class megafacturador extends fs_controller
{
   public $numasientos;
   public $opciones;
   public $serie;
   public $url_recarga;
   
   private $cliente;
   private $ejercicio;
   private $forma_pago;
   private $proveedor;
   private $regularizacion;
   private $total;
   
   public function __construct()
   {
      parent::__construct('megafacturador', 'MegaFacturador', 'ventas', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $this->cliente = new cliente();
      $this->ejercicio = new ejercicio();
      $this->forma_pago = new forma_pago();
      $this->numasientos = 0;
      $this->proveedor = new proveedor();
      $this->regularizacion = new regularizacion_iva();
      $this->serie = new serie();
      $this->url_recarga = FALSE;
      
      $this->opciones = array(
          'ventas' => TRUE,
          'compras' => TRUE,
          'codserie' => '',
          'fecha' => 'hoy'
      );
      
      if( isset($_REQUEST['fecha']) )
      {
         $this->opciones['codserie'] = $_REQUEST['codserie'];
         $this->opciones['fecha'] = $_REQUEST['fecha'];
         
         if($_REQUEST['procesar'] == 'TRUE')
         {
            $recargar = FALSE;
            $this->total = 0;
            if( isset($_REQUEST['ventas']) )
            {
               foreach($this->pendientes_venta() as $alb)
               {
                  $this->generar_factura_cliente( array($alb) );
                  $recargar = TRUE;
               }
               $this->new_message($this->total.' '.FS_ALBARANES.' de cliente facturados.');
            }
            else
               $this->opciones['ventas'] = FALSE;
            
            $this->total = 0;
            if( isset($_REQUEST['compras']) )
            {
               foreach($this->pendientes_compra() as $alb)
               {
                  $this->generar_factura_proveedor( array($alb) );
                  $recargar = TRUE;
               }
               $this->new_message($this->total.' '.FS_ALBARANES.' de proveedor facturados.');
            }
            else
               $this->opciones['compras'] = FALSE;
            
            /// ¿Recargamos?
            if( count($this->get_errors()) > 0 )
            {
               $this->new_error_msg('Se han producido errores. Proceso detenido.');
            }
            else if($recargar)
            {
               $this->url_recarga = $this->url().'&fecha='.$this->opciones['fecha']
                       .'&codserie='.$this->opciones['codserie'].'&procesar=TRUE';
               
               if( isset($_REQUEST['ventas']) )
               {
                  $this->url_recarga .= '&ventas=TRUE';
               }
               
               if( isset($_REQUEST['compras']) )
               {
                  $this->url_recarga .= '&compras=TRUE';
               }
               
               $this->new_message('Recargando...');
            }
            else
            {
               $this->new_advice('Finalizado. <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>');
            }
         }
      }
      else if( isset($_GET['genasientos']) )
      {
         $this->generar_asientos();
      }
      
      $this->numasientos = $this->num_asientos_a_generar();
   }
   
   private function generar_factura_cliente($albaranes)
   {
      $continuar = TRUE;
      
      $factura = new factura_cliente();
      $factura->codagente = $albaranes[0]->codagente;
      $factura->codalmacen = $albaranes[0]->codalmacen;
      $factura->coddivisa = $albaranes[0]->coddivisa;
      $factura->tasaconv = $albaranes[0]->tasaconv;
      $factura->codpago = $albaranes[0]->codpago;
      $factura->codserie = $albaranes[0]->codserie;
      $factura->irpf = $albaranes[0]->irpf;
      $factura->numero2 = $albaranes[0]->numero2;
      $factura->observaciones = $albaranes[0]->observaciones;
      
      /// asignamos fecha y ejercicio usando la del albarán
      if( $_REQUEST['fecha'] == 'albaran' )
      {
         $eje0 = $this->ejercicio->get($albaranes[0]->codejercicio);
         if($eje0)
         {
            if( $eje0->abierto() )
            {
               $factura->codejercicio = $albaranes[0]->codejercicio;
               $factura->set_fecha_hora($albaranes[0]->fecha, $albaranes[0]->hora);
            }
         }
      }
      
      /**
       * Si se ha elegido fecha de hoy o no se ha podido usar la del albarán porque
       * el ejercicio estaba cerrado, asignamos ejercicio para hoy y usamos la mejor
       * fecha y hora.
       */
      if( is_null($factura->codejercicio) )
      {
         $eje0 = $this->ejercicio->get_by_fecha($factura->fecha);
         if($eje0)
         {
            $factura->codejercicio = $eje0->codejercicio;
            $factura->set_fecha_hora($factura->fecha, $factura->hora);
         }
      }
      
      /// obtenemos los datos actuales del cliente, por si ha habido cambios
      $cliente = $this->cliente->get($albaranes[0]->codcliente);
      if($cliente)
      {
         foreach($cliente->get_direcciones() as $dir)
         {
            if($dir->domfacturacion)
            {
               $factura->apartado = $dir->apartado;
               $factura->cifnif = $cliente->cifnif;
               $factura->ciudad = $dir->ciudad;
               $factura->codcliente = $cliente->codcliente;
               $factura->coddir = $dir->id;
               $factura->codpais = $dir->codpais;
               $factura->codpostal = $dir->codpostal;
               $factura->direccion = $dir->direccion;
               $factura->nombrecliente = $cliente->razonsocial;
               $factura->provincia = $dir->provincia;
               break;
            }
         }
      }
      
      /// calculamos neto e iva
      foreach($albaranes as $alb)
      {
         foreach($alb->get_lineas() as $l)
         {
            $factura->neto += $l->pvptotal;
            $factura->totaliva += $l->pvptotal * $l->iva/100;
            $factura->totalirpf += $l->pvptotal * $l->irpf/100;
            $factura->totalrecargo += $l->pvptotal * $l->recargo/100;
         }
      }
      
      /// redondeamos
      $factura->neto = round($factura->neto, FS_NF0);
      $factura->totaliva = round($factura->totaliva, FS_NF0);
      $factura->totalirpf = round($factura->totalirpf, FS_NF0);
      $factura->totalrecargo = round($factura->totalrecargo, FS_NF0);
      $factura->total = $factura->neto + $factura->totaliva - $factura->totalirpf + $factura->totalrecargo;
      
      /// comprobamos la forma de pago para saber si hay que marcar la factura como pagada
      $formapago = $this->forma_pago->get($factura->codpago);
      if($formapago)
      {
         if($formapago->genrecibos == 'Pagados')
         {
            $factura->pagada = TRUE;
         }
         $factura->vencimiento = Date('d-m-Y', strtotime($factura->fecha.' '.$formapago->vencimiento));
      }
      
      if( !$eje0 )
      {
         $this->new_error_msg("Ningún ejercicio encontrado.");
      }
      else if( !$eje0->abierto() )
      {
         $this->new_error_msg('El ejercicio '.$eje0->codejercicio.' está cerrado.');
      }
      else if( $this->regularizacion->get_fecha_inside($factura->fecha) )
      {
         /*
          * comprobamos que la fecha de la factura no esté dentro de un periodo de
          * IVA regularizado.
          */
         $this->new_error_msg('El IVA de ese periodo ya ha sido regularizado. No se pueden añadir más facturas en esa fecha.');
      }
      else if( $factura->save() )
      {
         foreach($albaranes as $alb)
         {
            foreach($alb->get_lineas() as $l)
            {
               $n = new linea_factura_cliente();
               $n->idalbaran = $alb->idalbaran;
               $n->idfactura = $factura->idfactura;
               $n->cantidad = $l->cantidad;
               $n->codimpuesto = $l->codimpuesto;
               $n->descripcion = $l->descripcion;
               $n->dtopor = $l->dtopor;
               $n->irpf = $l->irpf;
               $n->iva = $l->iva;
               $n->pvpsindto = $l->pvpsindto;
               $n->pvptotal = $l->pvptotal;
               $n->pvpunitario = $l->pvpunitario;
               $n->recargo = $l->recargo;
               $n->referencia = $l->referencia;
               
               if( !$n->save() )
               {
                  $continuar = FALSE;
                  $this->new_error_msg("¡Imposible guardar la línea el artículo ".$n->referencia."! ");
                  break;
               }
            }
         }
         
         if($continuar)
         {
            foreach($albaranes as $alb)
            {
               $alb->idfactura = $factura->idfactura;
               $alb->ptefactura = FALSE;
               
               if( !$alb->save() )
               {
                  $this->new_error_msg("¡Imposible vincular el ".FS_ALBARAN." con la nueva factura!");
                  $continuar = FALSE;
                  break;
               }
            }
            
            if( $continuar )
            {
               $this->generar_asiento_cliente($factura);
               $this->total++;
            }
            else
            {
               if( $factura->delete() )
               {
                  $this->new_error_msg("La factura se ha borrado.");
               }
               else
                  $this->new_error_msg("¡Imposible borrar la factura!");
            }
         }
         else
         {
            if( $factura->delete() )
            {
               $this->new_error_msg("La factura se ha borrado.");
            }
            else
               $this->new_error_msg("¡Imposible borrar la factura!");
         }
      }
      else
         $this->new_error_msg("¡Imposible guardar la factura!");
   }
   
   private function generar_asiento_cliente($factura)
   {
      if($this->empresa->contintegrada)
      {
         $asiento_factura = new asiento_factura();
         $asiento_factura->generar_asiento_venta($factura);
         
         foreach($asiento_factura->errors as $err)
         {
            $this->new_error_msg($err);
         }
         
         foreach($asiento_factura->messages as $msg)
         {
            $this->new_message($msg);
         }
      }
   }
   
   private function generar_factura_proveedor($albaranes)
   {
      $continuar = TRUE;
      
      $factura = new factura_proveedor();
      $factura->codalmacen = $albaranes[0]->codalmacen;
      $factura->coddivisa = $albaranes[0]->coddivisa;
      $factura->tasaconv = $albaranes[0]->tasaconv;
      $factura->codpago = $albaranes[0]->codpago;
      $factura->codserie = $albaranes[0]->codserie;
      $factura->irpf = $albaranes[0]->irpf;
      $factura->numproveedor = $albaranes[0]->numproveedor;
      $factura->observaciones = $albaranes[0]->observaciones;
      
      /// asignamos fecha y ejercicio usando la del albarán
      if( $_REQUEST['fecha'] == 'albaran' )
      {
         $eje0 = $this->ejercicio->get($albaranes[0]->codejercicio);
         if($eje0)
         {
            if( $eje0->abierto() )
            {
               $factura->codejercicio = $albaranes[0]->codejercicio;
               $factura->set_fecha_hora($albaranes[0]->fecha, $albaranes[0]->hora);
            }
         }
      }
      
      /**
       * Si se ha elegido fecha de hoy o no se ha podido usar la del albarán porque
       * el ejercicio estaba cerrado, asignamos ejercicio para hoy y usamos la mejor
       * fecha y hora.
       */
      if( is_null($factura->codejercicio) )
      {
         $eje0 = $this->ejercicio->get_by_fecha($factura->fecha);
         if($eje0)
         {
            $factura->codejercicio = $eje0->codejercicio;
            $factura->set_fecha_hora($factura->fecha, $factura->hora);
         }
      }
      
      /// obtenemos los datos actualizados del proveedor
      $proveedor = $this->proveedor->get($albaranes[0]->codproveedor);
      if($proveedor)
      {
         $factura->cifnif = $proveedor->cifnif;
         $factura->codproveedor = $proveedor->codproveedor;
         $factura->nombre = $proveedor->razonsocial;
      }
      
      /// calculamos neto e iva
      foreach($albaranes as $alb)
      {
         foreach($alb->get_lineas() as $l)
         {
            $factura->neto += $l->pvptotal;
            $factura->totaliva += $l->pvptotal * $l->iva/100;
            $factura->totalirpf += $l->pvptotal * $l->irpf/100;
            $factura->totalrecargo += $l->pvptotal * $l->recargo/100;
         }
      }
      
      /// redondeamos
      $factura->neto = round($factura->neto, FS_NF0);
      $factura->totaliva = round($factura->totaliva, FS_NF0);
      $factura->totalirpf = round($factura->totalirpf, FS_NF0);
      $factura->totalrecargo = round($factura->totalrecargo, FS_NF0);
      $factura->total = $factura->neto + $factura->totaliva - $factura->totalirpf + $factura->totalrecargo;
      
      if( !$eje0 )
      {
         $this->new_error_msg("Ningún ejercicio encontrado.");
      }
      else if( !$eje0->abierto() )
      {
         $this->new_error_msg('El ejercicio '.$eje0->codejercicio.' está cerrado.');
      }
      else if( $this->regularizacion->get_fecha_inside($factura->fecha) )
      {
         /*
          * comprobamos que la fecha de la factura no esté dentro de un periodo de
          * IVA regularizado.
          */
         $this->new_error_msg('El IVA de ese periodo ya ha sido regularizado. No se pueden añadir más facturas en esa fecha.');
      }
      else if( $factura->save() )
      {
         foreach($albaranes as $alb)
         {
            foreach($alb->get_lineas() as $l)
            {
               $n = new linea_factura_proveedor();
               $n->idalbaran = $alb->idalbaran;
               $n->idfactura = $factura->idfactura;
               $n->cantidad = $l->cantidad;
               $n->codimpuesto = $l->codimpuesto;
               $n->descripcion = $l->descripcion;
               $n->dtopor = $l->dtopor;
               $n->irpf = $l->irpf;
               $n->iva = $l->iva;
               $n->pvpsindto = $l->pvpsindto;
               $n->pvptotal = $l->pvptotal;
               $n->pvpunitario = $l->pvpunitario;
               $n->recargo = $l->recargo;
               $n->referencia = $l->referencia;
               
               if( !$n->save() )
               {
                  $continuar = FALSE;
                  $this->new_error_msg("¡Imposible guardar la línea el artículo ".$n->referencia."! ");
                  break;
               }
            }
         }
         
         if($continuar)
         {
            foreach($albaranes as $alb)
            {
               $alb->idfactura = $factura->idfactura;
               $alb->ptefactura = FALSE;
               
               if( !$alb->save() )
               {
                  $this->new_error_msg("¡Imposible vincular el ".FS_ALBARAN." con la nueva factura!");
                  $continuar = FALSE;
                  break;
               }
            }
            
            if( $continuar )
            {
               $this->generar_asiento_proveedor($factura);
               $this->total++;
            }
            else
            {
               if( $factura->delete() )
               {
                  $this->new_error_msg("La factura se ha borrado.");
               }
               else
                  $this->new_error_msg("¡Imposible borrar la factura!");
            }
         }
         else
         {
            if( $factura->delete() )
            {
               $this->new_error_msg("La factura se ha borrado.");
            }
            else
               $this->new_error_msg("¡Imposible borrar la factura!");
         }
      }
      else
         $this->new_error_msg("¡Imposible guardar la factura!");
   }
   
   private function generar_asiento_proveedor($factura)
   {
      if($this->empresa->contintegrada)
      {
         $asiento_factura = new asiento_factura();
         $asiento_factura->generar_asiento_compra($factura);
         
         foreach($asiento_factura->errors as $err)
         {
            $this->new_error_msg($err);
         }
         
         foreach($asiento_factura->messages as $msg)
         {
            $this->new_message($msg);
         }
      }
   }
   
   public function pendientes_venta()
   {
      $alblist = array();
      
      $sql = "SELECT * FROM albaranescli WHERE ptefactura = true";
      if($this->opciones['codserie'] != '')
      {
         $sql .= " AND codserie = ".$this->serie->var2str($this->opciones['codserie']);
      }
      
      $data = $this->db->select_limit($sql.' ORDER BY fecha ASC', 25, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $alblist[] = new albaran_cliente($d);
         }
      }
      
      return $alblist;
   }
   
   public function total_pendientes_venta()
   {
      $total = 0;
      
      $sql = "SELECT count(idalbaran) as total FROM albaranescli WHERE ptefactura = true";
      if($this->opciones['codserie'] != '')
      {
         $sql .= " AND codserie = ".$this->serie->var2str($this->opciones['codserie']);
      }
      
      $data = $this->db->select($sql);
      if($data)
      {
         $total = intval($data[0]['total']);
      }
      
      return $total;
   }
   
   public function pendientes_compra()
   {
      $alblist = array();
      
      $sql = "SELECT * FROM albaranesprov WHERE ptefactura = true";
      if($this->opciones['codserie'] != '')
      {
         $sql .= " AND codserie = ".$this->serie->var2str($this->opciones['codserie']);
      }
      
      $data = $this->db->select_limit($sql.' ORDER BY fecha ASC', 25, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $alblist[] = new albaran_proveedor($d);
         }
      }
      
      return $alblist;
   }
   
   public function total_pendientes_compra()
   {
      $total = 0;
      
      $sql = "SELECT count(idalbaran) as total FROM albaranesprov WHERE ptefactura = true";
      if($this->opciones['codserie'] != '')
      {
         $sql .= " AND codserie = ".$this->serie->var2str($this->opciones['codserie']);
      }
      
      $data = $this->db->select($sql);
      if($data)
      {
         $total = intval($data[0]['total']);
      }
      
      return $total;
   }
   
   private function generar_asientos()
   {
      $nuevos = 0;
      
      $sql = "SELECT * FROM facturascli WHERE idasiento IS NULL";
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $factura = new factura_cliente($d);
            if( is_null($factura->idasiento) )
            {
               $this->generar_asiento_cliente($factura);
               $nuevos++;
            }
         }
      }
      $this->new_message($nuevos.' asientos generados para facturas de venta.');
      
      $nuevos = 0;
      $sql = "SELECT * FROM facturasprov WHERE idasiento IS NULL";
      $data = $this->db->select_limit($sql, 100, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $factura = new factura_proveedor($d);
            if( is_null($factura->idasiento) )
            {
               $this->generar_asiento_proveedor($factura);
               $nuevos++;
            }
         }
      }
      $this->new_message($nuevos.' asientos generados para facturas de compra.');
   }
   
   private function num_asientos_a_generar()
   {
      $num = 0;
      
      $sql = "SELECT COUNT(idfactura) as num FROM facturascli WHERE idasiento IS NULL;";
      $data = $this->db->select($sql);
      if($data)
      {
         $num += intval($data[0]['num']);
      }
      
      $sql = "SELECT COUNT(idfactura) as num FROM facturasprov WHERE idasiento IS NULL;";
      $data = $this->db->select($sql);
      if($data)
      {
         $num += intval($data[0]['num']);
      }
      
      return $num;
   }
}
