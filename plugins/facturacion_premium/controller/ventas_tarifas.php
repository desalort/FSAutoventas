<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('familia.php');
require_model('grupo_clientes.php');
require_model('tarifa.php');

/**
 * Description of ventas_tarifas
 *
 * @author carlos
 */
class ventas_tarifas extends fs_controller
{
   public $allow_delete;
   public $familias_no;
   public $tarifa;
   public $tarifa_s;
   public $tarifas_familias;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Tarifas', 'ventas');
   }
   
   protected function private_core()
   {
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on(__CLASS__);
      
      $this->share_extensions();
      $familia = new familia();
      $this->tarifa = new tarifa();
      $this->tarifa_s = FALSE;
      
      if( isset($_GET['importar']) )
      {
         $this->importar_tarifas();
      }
      else if( isset($_POST['codtarifa']) ) /// crear/modificar tarifa
      {
         if($_POST['codtarifa'] == '')
         {
            /// Nueva tarifa de familia
            $tar0 = new tarifa();
            $tar0->codtarifa = $tar0->get_new_codigo();
         }
         else
         {
            $tar0 = $this->tarifa->get($_POST['codtarifa']);
            if(!$tar0)
            {
               $tar0 = new tarifa();
               $tar0->codtarifa = $_POST['codtarifa'];
            }
         }
         
         if( isset($_POST['madre']) )
         {
            $tar0->madre = $_POST['madre'];
            $tar0->codfamilia = $_POST['codfamilia'];
            $tar0->nombre = 'Familia ?';
            $fam = $familia->get($_POST['codfamilia']);
            if($fam)
            {
               $tar0->nombre = $fam->descripcion;
            }
         }
         else if( isset($_POST['nombre']) )
         {
            $tar0->nombre = $_POST['nombre'];
         }
         
         $tar0->margen = ($_POST['aplicar_a'] == 'coste');
         $tar0->set_x( floatval($_POST['dtopor']) );
         $tar0->set_y( floatval($_POST['inclineal']) );
         $tar0->mincoste = isset($_POST['mincoste']);
         $tar0->maxpvp = isset($_POST['maxpvp']);
         
         if( $tar0->save() )
         {
            $this->new_message("Tarifa guardada correctamente.");
         }
         else
            $this->new_error_msg("¡Imposible guardar la tarifa!");
      }
      else if( isset($_GET['delete_tarifa']) ) /// eliminar tarifa
      {
         $tar0 = $this->tarifa->get($_GET['delete_tarifa']);
         if($tar0)
         {
            if( $tar0->delete() )
            {
               $this->new_message("Tarifa borrada correctamente.");
            }
            else
               $this->new_error_msg("¡Imposible borrar la tarifa!");
         }
         else
            $this->new_error_msg("¡La tarifa no existe!");
      }
      
      if( isset($_REQUEST['cod']) )
      {
         $this->tarifa_s = $this->tarifa->get($_REQUEST['cod']);
      }
      
      if($this->tarifa_s)
      {
         $this->template = 'ventas_tarifa';
         
         $this->tarifas_familias = $this->tarifa->all_from_tarifa($this->tarifa_s->codtarifa);
         
         $this->familias_no = array();
         foreach($familia->all() as $fam)
         {
            $encontrada = FALSE;
            
            foreach($this->tarifas_familias as $tar)
            {
               if($tar->codfamilia == $fam->codfamilia)
               {
                  $encontrada = TRUE;
                  break;
               }
            }
            
            if(!$encontrada)
            {
               $this->familias_no[] = $fam;
            }
         }
      }
   }
   
   private function share_extensions()
   {
      $fsext = new fs_extension();
      $fsext->name = 'no_tab_tarifas';
      $fsext->from = __CLASS__;
      $fsext->to = 'ventas_articulos';
      $fsext->type = 'config';
      $fsext->text = 'no_tab_tarifas';
      $fsext->save();
   }
   
   private function importar_tarifas()
   {
      $importadas = 0;
      
      $data = $this->db->select("SELECT * FROM tarifas;");
      if($data)
      {
         foreach($data as $d)
         {
            $tar = $this->tarifa->get($d['codtarifa']);
            if(!$tar)
            {
               $tar = new tarifa();
               $tar->codtarifa = $d['codtarifa'];
               $tar->nombre = $d['nombre'];
               
               if($d['aplicar_a'] != 'pvp')
               {
                  $tar->margen = TRUE;
                  $tar->set_x( floatval($d['incporcentual']) );
                  $tar->set_y( floatval($d['inclineal']) );
               }
               else
               {
                  $tar->set_x( 0 - floatval($d['incporcentual']) );
                  $tar->set_y( 0 - floatval($d['inclineal']) );
               }
               
               $tar->mincoste = $tar->str2bool($d['mincoste']);
               $tar->maxpvp = $tar->str2bool($d['maxpvp']);
               
               if( $tar->save() )
               {
                  $importadas++;
               }
            }
         }
      }
      
      $this->new_message($importadas.' tarifas importadas.');
   }
   
   /**
    * Devuelve un array con los nombres de los grupos que tienen asignada
    * la tarifa $codtarifa
    * @param type $codtarifa
    * @return array
    */
   public function get_grupos_tar($codtarifa)
   {
      $lista = array();
      
      $grupo = new grupo_clientes();
      foreach($grupo->all_with_tarifa($codtarifa) as $gru)
      {
         $lista[] = $gru;
      }
      
      return $lista;
   }
}
