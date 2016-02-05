<?php
/*
 * This file is part of FacturaScripts
 * Copyright (C) 2014  Francesc Pineda Segarra  shawe.ewahs@gmail.com
 * Copyright (C) 2015  Carlos García Gómez      neorazorx@gmail.com
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

class dashboard extends fs_controller
{
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Dashboard', 'ventas');
   }

   protected function private_core()
   {
      /// Guardamos las extensiones
      $extensiones = array(
          array(
              'name' => 'docs.min.css',
              'page_from' => __CLASS__,
              'page_to' => __CLASS__,
              'type' => 'head',
              'text' => '<link href="plugins/dashboard/view/css/docs.min.css" rel="stylesheet" type="text/css" />',
              'params' => ''
          ),
          array(
              'name' => 'carousel.css',
              'page_from' => __CLASS__,
              'page_to' => __CLASS__,
              'type' => 'head',
              'text' => '<link href="plugins/dashboard/view/css/carousel.css" rel="stylesheet" type="text/css" />',
              'params' => ''
          )
      );
      foreach($extensiones as $ext)
      {
         $fsext0 = new fs_extension($ext);
         if( !$fsext0->save() )
         {
            $this->new_error_msg('Imposible guardar los datos de la extensión '.$ext['name'].'.');
         }
      }

      // Cambiar este valor si no se va a utilizar nunca el plugin "Presupuestos y pedidos" en ventas
      $this->show_presupuestos_y_pedidos_ventas = TRUE;

      // Cambiar este valor si no se va a utilizar nunca el plugin "Presupuestos y pedidos" en compras
      $this->show_presupuestos_y_pedidos_compras = TRUE;

      if( !in_array('presupuestos_y_pedidos', $GLOBALS['plugins']) )
      {
         $this->show_presupuestos_y_pedidos_ventas = FALSE;
         $this->show_presupuestos_y_pedidos_compras = FALSE;
      }
   }

   /* Devuelve el número total de presupuestos de venta realizados */
   public function ventas_num_presupuestos()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM presupuestoscli");
   }

   /* Devuelve el número total de presupuestos de venta aprobados */
   public function ventas_num_presupuestos_aprobados()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM presupuestoscli WHERE status=1");
   }

   /* Devuelve el número total de presupuestos de venta sin aprobar */
   public function ventas_num_presupuestos_pendientes()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM presupuestoscli WHERE status=0");
   }

   /* Devuelve el número total de presupuestos de venta rechazados */
   public function ventas_num_presupuestos_rechazados()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM presupuestoscli WHERE status=2");
   }

   /* Devuelve el número total de pedidos de venta realizados */
   public function ventas_num_pedidos()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM pedidoscli");
   }

   /* Devuelve el número total de pedidos de venta aprobados */
   public function ventas_num_pedidos_aprobados()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM pedidoscli WHERE status=1");
   }

   /* Devuelve el número total de pedidos de venta sin aprobar */
   public function ventas_num_pedidos_pendientes()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM pedidoscli WHERE status=0");
   }

   /* Devuelve el número total de pedidos de venta rechazados */
   public function ventas_num_pedidos_rechazados()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM pedidoscli WHERE status=2");
   }

   /* Devuelve el número total de albaranes de venta realizados */
   public function ventas_num_albaranes()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM albaranescli");
   }

   /* Devuelve el número total de albaranes de venta hechos */
   public function ventas_num_albaranes_aprobados()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM albaranescli WHERE idfactura IS NOT NULL");
   }

   /* Devuelve el número total de albaranes de venta sin aprobar */
   public function ventas_num_albaranes_pendientes()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM albaranescli WHERE idfactura IS NULL");
   }

   /* Devuelve el número total de facturas de venta realizadas */
   public function ventas_num_facturas()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM facturascli");
   }

   /* Devuelve el número total de facturas de venta cobradas */
   public function ventas_num_facturas_cobradas()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM facturascli WHERE pagada=TRUE");
   }

   /* Devuelve el número total de facturas de venta sin cobrar */
   public function ventas_num_facturas_sin_cobrar()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM facturascli WHERE pagada=FALSE");
   }

   /* Devuelve el número total de pedidos de compra realizados */
   public function compras_num_pedidos()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM pedidosprov");
   }

   /* Devuelve el número total de pedidos de compra aprobados */
   public function compras_num_pedidos_aprobados()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM pedidosprov WHERE idalbaran IS NOT NULL");
   }

   /* Devuelve el número total de pedidos de compra sin aprobar */
   public function compras_num_pedidos_pendientes()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM pedidosprov WHERE idalbaran IS NULL");
   }

   /* Devuelve el número total de albaranes de compra realizados */
   public function compras_num_albaranes()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM albaranesprov");
   }

   /* Devuelve el número total de albaranes de compra hechos */
   public function compras_num_albaranes_aprobados()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM albaranesprov WHERE idfactura IS NOT NULL");
   }

   /* Devuelve el número total de albaranes de compra sin aprobar */
   public function compras_num_albaranes_pendientes()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM albaranesprov WHERE idfactura IS NULL");
   }

   /* Devuelve el número total de facturas de compra realizadas */
   public function compras_num_facturas()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM facturasprov");
   }

   /* Devuelve el número total de facturas de compra cobradas */
   public function compras_num_facturas_cobradas()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM facturasprov WHERE pagada=TRUE");
   }

   /* Devuelve el número total de facturas de compra sin pagar */
   public function compras_num_facturas_sin_pagar()
   {
      return $this->db->select("SELECT COUNT(codigo) AS total FROM facturasprov WHERE pagada=FALSE");
   }

   public function num_articulos()
   {
      $data = $this->db->select("SELECT COUNT( DISTINCT(referencia)) AS total
            FROM articulos");
      return $data;
   }

   public function num_clientes()
   {
      $data = $this->db->select("SELECT COUNT( DISTINCT(codcliente)) AS total
            FROM clientes");
      return $data;
   }

   /* Devuelve el importe total de facturas de ventas */
   public function importe_total_facturas_ventas()
   {
      $this->desde = Date('1-m-Y');
      $this->hasta = Date('d-m-Y', mktime(0, 0, 0, date("m")+1, date("1")-1, date("Y")));

      $data = $this->db->select("SELECT SUM(total) AS total
            FROM facturascli
            WHERE fecha >= ".$this->empresa->var2str($this->desde)." AND fecha <= ".$this->empresa->var2str($this->hasta));
      return $data;
   }

   /* Devuelve el importe total de facturas de compras */
   public function importe_total_facturas_compras()
   {
      $this->desde = Date('1-m-Y');
      $this->hasta = Date('d-m-Y', mktime(0, 0, 0, date("m")+1, date("1")-1, date("Y")));

      $data = $this->db->select("SELECT SUM(total) AS total
            FROM facturasprov
            WHERE fecha >= ".$this->empresa->var2str($this->desde)." AND fecha <= ".$this->empresa->var2str($this->hasta));
      return $data;
   }

   /* Devuelve el importe total de albaranes pendientes */
   public function importe_total_albaranes_ventas_pendientes()
   {
      $this->desde = Date('1-m-Y');
      $this->hasta = Date('d-m-Y', mktime(0, 0, 0, date("m")+1, date("1")-1, date("Y")));

      $data = $this->db->select("SELECT SUM(total) AS total
            FROM albaranescli
            WHERE fecha >= ".$this->empresa->var2str($this->desde)." AND fecha <= ".$this->empresa->var2str($this->hasta));
      return $data;
   }

   /* Devuelve el total de IVA pagado */
   public function importe_total_iva_pagado()
   {
      $this->desde = Date('1-m-Y');
      $this->hasta = Date('d-m-Y', mktime(0, 0, 0, date("m")+1, date("1")-1, date("Y")));

      $data = $this->db->select("SELECT SUM(totaliva) AS total
            FROM facturasprov
            WHERE fecha >= ".$this->empresa->var2str($this->desde)." AND fecha <= ".$this->empresa->var2str($this->hasta));
      return $data;
   }

   /* Devuelve el total de IVA a pagar */
   public function importe_total_iva_a_pagar()
   {
      $this->desde = Date('1-m-Y');
      $this->hasta = Date('d-m-Y', mktime(0, 0, 0, date("m")+1, date("1")-1, date("Y")));

      $data = $this->db->select("SELECT SUM(totaliva) AS total
            FROM facturascli
            WHERE fecha >= ".$this->empresa->var2str($this->desde)." AND fecha <= ".$this->empresa->var2str($this->hasta));
      return $data;
   }

   /* Devuelve el número total de servicios en distintos estados */
   public function num_total_estados_servicios()
   {
      $data = $this->db->select("SELECT s.idestado,e.descripcion,e.color,COUNT(s.idestado) AS total "
              . "               FROM servicioscli s, estados_servicios e WHERE e.id=s.idestado GROUP BY s.idestado,e.descripcion,e.color;");
      return $data;
   }
}
