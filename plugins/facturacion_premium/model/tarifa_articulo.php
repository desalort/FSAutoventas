<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2013-2015  Carlos Garcia Gomez  neorazorx@gmail.com
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

require_model('articulo.php');
require_model('tarifa.php');

/**
 * El precio concreto de un artículo en una tarifa determinada.
 */
class tarifa_articulo extends fs_model
{
   public $id;
   public $referencia;
   public $codtarifa;
   public $pvp;
   
   public function __construct($t = FALSE)
   {
      parent::__construct('articulostarifas');
      if($t)
      {
         $this->id = $this->intval( $t['id'] );
         $this->referencia = $t['referencia'];
         $this->codtarifa = $t['codtarifa'];
         $this->pvp = floatval($t['pvp']);
      }
      else
      {
         $this->id = NULL;
         $this->referencia = NULL;
         $this->codtarifa = NULL;
         $this->pvp = 0;
      }
   }
   
   protected function install()
   {
      new articulo();
      new tarifa();
      return '';
   }
   
   public function get_precios(&$articulos, $codtarifa)
   {
      $resultados = array();
      
      if( count($articulos) > 0 )
      {
         if( count($articulos) == 1 )
         {
            $sql = "SELECT * FROM ".$this->table_name." WHERE codtarifa = ".$this->var2str($codtarifa).
                    " AND referencia = ".$this->var2str($articulos[0]->referencia).';';
         }
         else
         {
            $sql = "SELECT * FROM ".$this->table_name." WHERE codtarifa = ".$this->var2str($codtarifa)." AND referencia IN (";
            $coma = '';
            foreach($articulos as $a)
            {
               $sql .= $coma.$this->var2str($a->referencia);
               $coma = ',';
            }
            $sql .= ");";
         }
         
         $data = $this->db->select($sql);
         if($data)
         {
            foreach($data as $d)
            {
               $resultados[$d['referencia']] = floatval($d['pvp']);
            }
         }
      }
      
      return $resultados;
   }
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE id = ".$this->var2str($id).";");
      if($data)
      {
         return new tarifa_articulo($data[0]);
      }
      else
         return FALSE;
   }
   
   public function get_by($ref, $codtarifa)
   {
      $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE referencia = ".
              $this->var2str($ref)." AND codtarifa = ".$this->var2str($codtarifa).";");
      if($data)
      {
         return new tarifa_articulo($data[0]);
      }
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->id) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET referencia = ".$this->var2str($this->referencia)
                 .", codtarifa = ".$this->var2str($this->codtarifa)
                 .", pvp = ".$this->var2str($this->pvp)
                 ."  WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO ".$this->table_name." (referencia,codtarifa,pvp) VALUES
                 (".$this->var2str($this->referencia).
                 ",".$this->var2str($this->codtarifa).
                 ",".$this->var2str($this->pvp).");";
         
         if( $this->db->exec($sql) )
         {
            $this->id = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function all_from_articulo($ref)
   {
      $tarlist = array();
      
      $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE referencia = ".$this->var2str($ref).";");
      if($data)
      {
         foreach($data as $d)
            $tarlist[] = new tarifa_articulo($d);
      }
      
      return $tarlist;
   }
}
