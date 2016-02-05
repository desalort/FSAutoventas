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

require_model('tarifa_articulo.php');

/**
 * Una tarifa para los artículos.
 */
class tarifa extends fs_model
{
   /**
    * Código de la tarifa. Clave primaria.
    * @var type 
    */
   public $codtarifa;
   
   /**
    * Código de la tarifa madre.
    * @var type 
    */
   public $madre;
   
   /**
    * Código de la familia asociada.
    * @var type 
    */
   public $codfamilia;
   
   /**
    * Nombre de la tarifa, si es una tarifa madre,
    * o nombre de la familia, si es una tarifa específica para una familia.
    * @var type 
    */
   public $nombre;
   
   /**
    * ¿Precio de coste + X% + Y?
    * @var type 
    */
   public $margen;
   
   /**
    * Incremento porcentual o descuento.
    * @var type 
    */
   private $incporcentual;
   
   /**
    * Incremento lineal o descuento lineal.
    * @var type 
    */
   private $inclineal;
   
   /**
    * no vender por debajo de coste
    * @var boolean 
    */
   public $mincoste;
   
   /**
    * no vender por encima de pvp
    * @var boolean 
    */
   public $maxpvp;
   
   public function __construct($t = FALSE)
   {
      parent::__construct('tarifasav');
      if($t)
      {
         $this->codtarifa = $t['codtarifa'];
         $this->madre = $t['madre'];
         $this->codfamilia = $t['codfamilia'];
         $this->nombre = $t['nombre'];
         $this->margen = $this->str2bool($t['margen']);
         $this->incporcentual = floatval( $t['incporcentual'] );
         $this->inclineal = floatval( $t['inclineal'] );
         $this->mincoste = $this->str2bool($t['mincoste']);
         $this->maxpvp = $this->str2bool($t['maxpvp']);
      }
      else
      {
         $this->codtarifa = NULL;
         $this->madre = NULL;
         $this->codfamilia = NULL;
         $this->nombre = NULL;
         $this->margen = FALSE;
         $this->incporcentual = 0;
         $this->inclineal = 0;
         $this->mincoste = TRUE;
         $this->maxpvp = TRUE;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function url()
   {
      if( isset($this->madre) )
      {
         return 'index.php?page=ventas_tarifas&cod='.$this->madre;
      }
      else if( isset($this->codtarifa) )
      {
         return 'index.php?page=ventas_tarifas&cod='.$this->codtarifa;
      }
      else
         return 'index.php?page=ventas_tarifas';
   }
   
   public function x()
   {
      if( !$this->margen )
      {
         return (0 - $this->incporcentual);
      }
      else
      {
         return $this->incporcentual;
      }
   }
   
   public function set_x($dto)
   {
      if( !$this->margen )
      {
         $this->incporcentual = 0 - $dto;
      }
      else
      {
         $this->incporcentual = $dto;
      }
   }
   
   public function y()
   {
      if( !$this->margen )
      {
         return (0 - $this->inclineal);
      }
      else
      {
         return $this->inclineal;
      }
   }
   
   public function set_y($inc)
   {
      if( !$this->margen )
      {
         $this->inclineal = 0 - $inc;
      }
      else
      {
         $this->inclineal = $inc;
      }
   }
   
   /**
    * Devuelve un texto explicativo de lo que hace la tarifa
    * @return type
    */
   public function diff()
   {
      $texto = '';
      $x = $this->x();
      $y = $this->y();
      
      if( !$this->margen )
      {
         $texto = 'Precio de venta ';
         $x = 0 - $x;
         $y = 0 - $y;
      }
      else
      {
         $texto = 'Precio de coste ';
      }
      
      if($x != 0)
      {
         if($x > 0)
         {
            $texto .= '+';
         }
         
         $texto .= $x.'% ';
      }
      
      if($y != 0)
      {
         if($y > 0)
         {
            $texto .= ' +';
         }
         
         $texto .= $y;
      }
      
      return $texto;
   }
   
   /**
    * Rellenamos los descuentos y los datos de la tarifa de una lista de
    * artículos.
    * @param type $articulos
    */
   public function set_precios(&$articulos)
   {
      /// obtenemos todos los datos de las familias de esta tarifa
      $tarfamilias = $this->all_from_tarifa($this->codtarifa);
      
      /// obtenemos los precios fijos de los artículos, los de la tabla articulostarifas
      $tarifa_articulo = new tarifa_articulo();
      $precios_articulos = $tarifa_articulo->get_precios($articulos, $this->codtarifa);
      
      foreach($articulos as $i => $value)
      {
         $articulos[$i]->codtarifa = $this->codtarifa;
         $articulos[$i]->tarifa_nombre = $this->nombre;
         $articulos[$i]->tarifa_url = $this->url();
         $articulos[$i]->dtopor = 0;
         
         /// si encontramos el precio fijo, genial, no necesitamos más
         $encontrada = FALSE;
         if( isset($precios_articulos[$value->referencia]) )
         {
            $articulos[$i]->pvp = $precios_articulos[$value->referencia];
            $articulos[$i]->tarifa_diff = 'Precio fijo';
            $encontrada = TRUE;
         }
         
         if(!$encontrada)
         {
            $pvp = $articulos[$i]->pvp;
            
            /// si no hemos encontrado el precio fijo, hay que ver si hay alguna tarifa para la familia
            foreach($tarfamilias as $tar)
            {
               if($value->codfamilia == $tar->codfamilia)
               {
                  $articulos[$i]->tarifa_nombre = $this->nombre.' - '.$tar->nombre;
                  $articulos[$i]->tarifa_diff = $tar->diff();
                  
                  if($tar->margen)
                  {
                     $articulos[$i]->pvp = $articulos[$i]->preciocoste() * (100 + $tar->x())/100 + $tar->y();
                  }
                  else
                  {
                     if( $tar->x() >= 0 )
                     {
                        $articulos[$i]->dtopor = $tar->x();
                        $articulos[$i]->pvp = $articulos[$i]->pvp - $tar->y();
                     }
                     else
                     {
                        $articulos[$i]->pvp = $articulos[$i]->pvp * (100 - $tar->x())/100 - $tar->y();
                     }
                  }
                  
                  $encontrada = TRUE;
                  break;
               }
            }
            
            /// si no hay precio fijo, ni tarifa de familia, usamos los datos de la tarifa madre
            if(!$encontrada)
            {
               $articulos[$i]->tarifa_diff = $this->diff();
               
               if($this->margen)
               {
                  $articulos[$i]->pvp = $articulos[$i]->preciocoste() * (100 + $this->x())/100 + $this->y();
               }
               else
               {
                  if( $this->x() >= 0 )
                  {
                     $articulos[$i]->dtopor = $this->x();
                     $articulos[$i]->pvp = $articulos[$i]->pvp - $this->y();
                  }
                  else
                  {
                     $articulos[$i]->pvp = $articulos[$i]->pvp * (100 - $this->x())/100 - $this->y();
                  }
               }
            }
            
            if($this->mincoste)
            {
               if( $articulos[$i]->pvp * (100 - $articulos[$i]->dtopor) / 100 < $articulos[$i]->preciocoste() )
               {
                  $articulos[$i]->dtopor = 0;
                  $articulos[$i]->pvp = $articulos[$i]->preciocoste();
                  $articulos[$i]->tarifa_diff = 'Mínimo precio coste';
               }
            }
            
            if($this->maxpvp)
            {
               if($articulos[$i]->pvp * (100 - $articulos[$i]->dtopor) / 100 > $pvp)
               {
                  $articulos[$i]->dtopor = 0;
                  $articulos[$i]->pvp = $pvp;
                  $articulos[$i]->tarifa_diff = 'Máximo precio de venta';
               }
            }
         }
      }
   }
   
   public function get($cod)
   {
      $tarifa = $this->db->select("SELECT * FROM ".$this->table_name." WHERE codtarifa = ".$this->var2str($cod).";");
      if($tarifa)
      {
         return new tarifa($tarifa[0]);
      }
      else
         return FALSE;
   }
   
   public function get_new_codigo()
   {
      $cod = $this->db->select("SELECT MAX(".$this->db->sql_to_int('codtarifa').") as cod FROM ".$this->table_name.";");
      if($cod)
      {
         return sprintf('%06s', (1 + intval($cod[0]['cod'])));
      }
      else
         return '000001';
   }
   
   public function exists()
   {
      if( is_null($this->codtarifa) )
      {
         return FALSE;
      }
      else
      {
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE codtarifa = ".$this->var2str($this->codtarifa).";");
      }
   }
   
   public function test()
   {
      $status = FALSE;
      
      $this->codtarifa = trim($this->codtarifa);
      $this->nombre = $this->no_html($this->nombre);
      
      if( !preg_match("/^[A-Z0-9]{1,6}$/i", $this->codtarifa) )
      {
         $this->new_error_msg("Código de tarifa no válido.");
      }
      else if( strlen($this->nombre) < 1 OR strlen($this->nombre) > 50 )
      {
         $this->new_error_msg("Nombre de tarifa no válido.");
      }
      else
         $status = TRUE;
      
      return $status;
   }
   
   public function save()
   {
      if( $this->test() )
      {
         if( $this->exists() )
         {
            $sql = "UPDATE ".$this->table_name." SET nombre = ".$this->var2str($this->nombre)
                    .", madre = ".$this->var2str($this->madre)
                    .", codfamilia = ".$this->var2str($this->codfamilia)
                    .", margen = ".$this->var2str($this->margen)
                    .", incporcentual = ".$this->var2str($this->incporcentual)
                    .", inclineal = ".$this->var2str($this->inclineal)
                    .", mincoste =".$this->var2str($this->mincoste)
                    .", maxpvp =".$this->var2str($this->maxpvp)
                    ."  WHERE codtarifa = ".$this->var2str($this->codtarifa).";";
         }
         else
         {
            $sql = "INSERT INTO ".$this->table_name." (codtarifa,madre,codfamilia,nombre,margen,
               incporcentual,inclineal,mincoste,maxpvp) VALUES (".$this->var2str($this->codtarifa)
                    .",".$this->var2str($this->madre)
                    .",".$this->var2str($this->codfamilia)
                    .",".$this->var2str($this->nombre)
                    .",".$this->var2str($this->margen)
                    .",".$this->var2str($this->incporcentual)
                    .",".$this->var2str($this->inclineal)
                    .",".$this->var2str($this->mincoste)
                    .",".$this->var2str($this->maxpvp).");";
         }
         
         return $this->db->exec($sql);
      }
      else
         return FALSE;
   }
   
   public function delete()
   {
      $sql = "DELETE FROM ".$this->table_name." WHERE codtarifa = ".$this->var2str($this->codtarifa).
              " OR madre = ".$this->var2str($this->codtarifa).";";
      
      if( $this->db->exec($sql) )
      {
         /// eliminamos las tarifas de los artículos
         $this->db->exec("DELETE FROM articulostarifas WHERE codtarifa NOT IN (SELECT codtarifa FROM ".$this->table_name.");");
         
         return TRUE;
      }
      else
         return FALSE;
   }
   
   public function all()
   {
      $tarlist = array();
      
      $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE madre IS NULL ORDER BY codtarifa ASC;");
      if($data)
      {
         foreach($data as $t)
            $tarlist[] = new tarifa($t);
      }
      
      return $tarlist;
   }
   
   public function all_from_tarifa($cod)
   {
      $tarlist = array();
      
      $data = $this->db->select("SELECT * FROM ".$this->table_name." WHERE madre = ".$this->var2str($cod)." ORDER BY codfamilia ASC;");
      if($data)
      {
         foreach($data as $t)
            $tarlist[] = new tarifa($t);
      }
      
      return $tarlist;
   }
}
