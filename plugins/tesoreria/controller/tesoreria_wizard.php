<?php

/*
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

/**
 * Description of tesoreria_wizard
 *
 * @author carlos
 */
class tesoreria_wizard extends fs_controller
{
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Asistente', 'admin', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->fix_db();
      $this->check_menu();
   }
   
   private function fix_db()
   {
      /// eliminamos mierda
      if( $this->db->table_exists('reciboscli') )
      {
         $this->db->exec("DELETE FROM reciboscli WHERE idfactura NOT IN (SELECT idfactura FROM facturascli);");
      }
      
      if( $this->db->table_exists('recibosprov') )
      {
         $this->db->exec("DELETE FROM recibosprov WHERE idfactura NOT IN (SELECT idfactura FROM facturasprov);");
      }
   }
   
   private function check_menu()
   {
      if( !$this->page->get('ventas_recibos') )
      {
         if( file_exists(__DIR__) )
         {
            /// activamos las páginas del plugin
            foreach( scandir(__DIR__) as $f)
            {
               if( is_string($f) AND strlen($f) > 0 AND !is_dir($f) AND $f != __CLASS__.'.php' )
               {
                  $page_name = substr($f, 0, -4);
                  
                  require_once __DIR__.'/'.$f;
                  $new_fsc = new $page_name();
                  
                  if( !$new_fsc->page->save() )
                  {
                     $this->new_error_msg("Imposible guardar la página ".$page_name);
                  }
                  
                  unset($new_fsc);
               }
            }
         }
         else
         {
            $this->new_error_msg('No se encuentra el directorio '.__DIR__);
         }
         
         $this->load_menu(TRUE);
      }
   }
}
