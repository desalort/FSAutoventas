<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('articulo.php');
require_model('articulo_proveedor.php');
require_model('impuesto.php');
require_model('tarifa.php');
require_model('tarifa_articulo.php');

/**
 * Description of tarifa_articulo
 *
 * @author carlos
 */
class tab_tarifa_articulo extends fs_controller
{
   public $articulo;
   public $impuesto;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Tarifas artículo', 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->share_extension();
      $this->impuesto = new impuesto();
      $this->articulo = FALSE;
      
      if( isset($_POST['pvpiva']) )
      {
         $articulo = new articulo();
         $this->articulo = $articulo->get($_POST['referencia']);
         if($this->articulo)
         {
            $continuar = TRUE;
            $this->articulo->set_impuesto( $_POST['codimpuesto'] );
            $this->articulo->set_pvp_iva( $_POST['pvpiva'] );
            
            if( isset($_POST['preciocoste']) )
            {
               $this->articulo->costemedio = $this->articulo->preciocoste = floatval($_POST['preciocoste']);
            }
            
            if( !$this->articulo->save() )
            {
               $this->new_error_msg("¡Imposible modificar el artículo!");
               $continuar = FALSE;
            }
            
            $tarifa_articulo = new tarifa_articulo();
            for($i = 0; $i < 100; $i++)
            {
               if( isset($_POST['codtarifa_'.$i]) )
               {
                  if($_POST['pvpi_'.$i] != '') /// pvp+iva
                  {
                     $ta = $tarifa_articulo->get_by($this->articulo->referencia, $_POST['codtarifa_'.$i]);
                     if(!$ta)
                     {
                        $ta = new tarifa_articulo();
                        $ta->codtarifa = $_POST['codtarifa_'.$i];
                        $ta->referencia = $this->articulo->referencia;
                     }
                     
                     $ta->calculado = FALSE;
                     $ta->pvp = $this->pvpiva2pvp( floatval($_POST['pvpi_'.$i]) );
                     
                     if( !$ta->save() )
                     {
                        $this->new_error_msg("¡Imposible modificar la tarifa!");
                        $continuar = FALSE;
                     }
                  }
                  else if($_POST['pvp_'.$i] != '') /// pvp
                  {
                     $ta = $tarifa_articulo->get_by($this->articulo->referencia, $_POST['codtarifa_'.$i]);
                     if(!$ta)
                     {
                        $ta = new tarifa_articulo();
                        $ta->codtarifa = $_POST['codtarifa_'.$i];
                        $ta->referencia = $this->articulo->referencia;
                     }
                     
                     $ta->calculado = FALSE;
                     $ta->pvp = floatval($_POST['pvp_'.$i]);
                     
                     if( !$ta->save() )
                     {
                        $this->new_error_msg("¡Imposible modificar la tarifa!");
                        $continuar = FALSE;
                     }
                  }
               }
               else
                  break;
            }
            
            if($continuar)
            {
               $this->new_message("Precios modificadas correctamente.");
            }
         }
      }
      else if( isset($_GET['ref']) )
      {
         $art = new articulo();
         $this->articulo = $art->get($_GET['ref']);
      }
      
      if($this->articulo)
      {
         if( isset($_GET['recalcular']) )
         {
            /// eliminamos los precios fijos almacenados
            $tarifa_articulo = new tarifa_articulo();
            foreach( $tarifa_articulo->all_from_articulo($this->articulo->referencia) as $ta )
            {
               $ta->delete();
            }
         }
      }
      else
         $this->new_error_msg('Artículo no encontrado.');
   }
   
   public function url()
   {
      if($this->articulo)
      {
         return 'index.php?page='.__CLASS__.'&ref='.$this->articulo->referencia;
      }
      else
         return parent::url();
   }
   
   private function share_extension()
   {
      $fsext = new fs_extension();
      $fsext->name = 'tab_precios';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_articulo';
      $fsext->type = 'tab';
      $fsext->text = '<span class="glyphicon glyphicon-usd" aria-hidden="true"></span><span class="hidden-xs">&nbsp; Precios</span>';
      $fsext->save();
      
      $fsext2 = new fs_extension();
      $fsext2->name = 'no_tab_precios';
      $fsext2->from = __CLASS__;
      $fsext2->to = 'ventas_articulo';
      $fsext2->type = 'config';
      $fsext2->text = 'no_tab_precios';
      $fsext2->save();
   }
   
   public function get_tarifas()
   {
      $tarlist = array();
      $tarifa = new tarifa();
      
      foreach($tarifa->all() as $tar)
      {
         $articulo = $this->articulo->get($this->articulo->referencia);
         if($articulo)
         {
            $articulo->dtopor = 0;
            $aux = array($articulo);
            $tar->set_precios($aux);
            $tarlist[] = $aux[0];
         }
      }
      
      return $tarlist;
   }
   
   private function pvpiva2pvp($p)
   {
      return round((100*$p)/(100+$this->articulo->get_iva()), 3);
   }
   
   public function get_articulo_proveedores()
   {
      $artprov = new articulo_proveedor();
      $alist = $artprov->all_from_ref($this->articulo->referencia);
      
      /// revismos el impuesto y la descripción
      foreach($alist as $i => $value)
      {
         $guardar = FALSE;
         if( is_null($value->codimpuesto) )
         {
            $alist[$i]->codimpuesto = $this->articulo->codimpuesto;
            $guardar = TRUE;
         }
         
         if( is_null($value->descripcion) )
         {
            $alist[$i]->descripcion = $this->articulo->descripcion;
            $guardar = TRUE;
         }
         
         if($guardar)
         {
            $alist[$i]->save();
         }
      }
      
      return $alist;
   }
}
