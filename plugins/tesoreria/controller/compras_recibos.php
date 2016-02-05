<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('cuenta_banco_proveedor.php');
require_model('factura_proveedor.php');
require_model('forma_pago.php');
require_model('forma_pago_plazo.php');
require_model('proveedor.php');
require_model('recibo_factura.php');
require_model('recibo_proveedor.php');

/**
 * Description of compras_recibos
 *
 * @author carlos
 */
class compras_recibos extends fs_controller
{
   public $check_vencimiento;
   public $desde;
   public $estado;
   public $factura;
   public $hasta;
   public $mostrar;
   public $num_pendientes;
   public $num_resultados;
   public $num_vencidos;
   public $offset;
   public $order;
   public $pagada_previamente;
   public $proveedor;
   public $resultados;
   public $total_resultados;
   public $total_resultados_txt;
   public $vencimiento;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Recibos', 'compras');
   }
   
   protected function private_core()
   {
      $this->share_extenstion();
      $recibo = new recibo_proveedor();
      
      $this->check_vencimiento = FALSE;
      $this->desde = '';
      $this->estado = '';
      $this->hasta = '';
      $this->num_pendientes = 0;
      $this->num_resultados = 0;
      $this->num_vencidos = 0;
      $this->proveedor = FALSE;
      $this->total_resultados = 0;
      $this->total_resultados_txt = '';
      $this->vencimiento = Date('d-m-Y', strtotime('+1month'));
      
      if( isset($_REQUEST['buscar_proveedor']) )
      {
         $this->buscar_proveedor();
      }
      else if( isset($_POST['fecha']) )
      {
         $this->nuevo_recibo();
      }
      else if( isset($_REQUEST['id']) )
      {
         /// pestaña recibos de una factura
         $this->template = 'tab_factura_proveedor';
         
         $factura = new factura_proveedor();
         $this->factura = $factura->get($_REQUEST['id']);
         if($this->factura)
         {
            $this->resultados = $recibo->all_from_factura($_REQUEST['id']);
            
            if( isset($_GET['regenerar']) )
            {
               foreach($this->resultados as $res)
               {
                  $res->delete();
               }
               
               $this->factura->pagada = FALSE;
               $this->factura->save();
               $this->sync_factura(TRUE);
            }
            
            $this->pagada_previamente = FALSE;
            if($this->factura->pagada AND count($this->resultados) == 0)
            {
               $this->pagada_previamente = TRUE;
            }
            else
            {
               $this->sync_factura(TRUE);
            }
         }
      }
      else
      {
         $this->mostrar = 'todo';
         if( isset($_GET['mostrar']) )
         {
            $this->mostrar = $_GET['mostrar'];
         }
         
         $this->offset = 0;
         if( isset($_GET['offset']) )
         {
            $this->offset = intval($_GET['offset']);
         }
         
         if( isset($_GET['delete']) )
         {
            $recibo2 = $recibo->get($_GET['delete']);
            if($recibo2)
            {
               if( $recibo2->delete() )
               {
                  $this->new_message('Recibo eliminado correctamente.');
               }
               else
                  $this->new_message('Error al eliminar el recibo.');
            }
            else
               $this->new_message('Recibo no encontrado.');
         }
         else if( !isset($_GET['mostrar']) AND isset($_REQUEST['codproveedor']) )
         {
            $this->mostrar = 'buscar';
         }
         
         if( isset($_REQUEST['codproveedor']) )
         {
            if($_REQUEST['codproveedor'] != '')
            {
               $pro0 = new proveedor();
               $this->proveedor = $pro0->get($_REQUEST['codproveedor']);
            }
         }
         
         if( isset($_REQUEST['desde']) )
         {
            $this->desde = $_REQUEST['desde'];
            $this->estado = $_REQUEST['estado'];
            $this->hasta = $_REQUEST['hasta'];
            $this->check_vencimiento = isset($_REQUEST['vencimiento']);
         }
         
         $this->order = 'fecha DESC';
         if( isset($_GET['order']) )
         {
            if($_GET['order'] == 'fecha_desc')
            {
               $this->order = 'fecha DESC';
            }
            else if($_GET['order'] == 'fecha_asc')
            {
               $this->order = 'fecha ASC';
            }
            else if($_GET['order'] == 'fechav_desc')
            {
               $this->order = 'fechav DESC';
            }
            else if($_GET['order'] == 'fechav_asc')
            {
               $this->order = 'fechav ASC';
            }
            else if($_GET['order'] == 'codigo_desc')
            {
               $this->order = 'codigo DESC';
            }
            else if($_GET['order'] == 'codigo_asc')
            {
               $this->order = 'codigo ASC';
            }
            
            setcookie('compras_recibos_order', $this->order, time()+FS_COOKIES_EXPIRE);
         }
         else if( isset($_COOKIE['compras_recibos_order']) )
         {
            $this->order = $_COOKIE['compras_recibos_order'];
         }
         
         $this->cron_job();
         $this->buscar();
      }
   }
   
   private function buscar_proveedor()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $pro0 = new proveedor();
      $json = array();
      foreach($pro0->search($_REQUEST['buscar_proveedor']) as $pro)
      {
         $json[] = array('value' => $pro->nombre, 'data' => $pro->codproveedor);
      }
      
      header('Content-Type: application/json');
      echo json_encode( array('query' => $_REQUEST['buscar_proveedor'], 'suggestions' => $json) );
   }
   
   private function buscar()
   {
      $this->resultados = array();
      
      /// añadimos segundo nivel de ordenación
      $order2 = '';
      if($this->order == 'fecha DESC' || $this->order == 'fechav DESC')
      {
         $order2 = ', codigo DESC';
      }
      else if($this->order == 'fecha ASC' || $this->order == 'fechav ASC')
      {
         $order2 = ', codigo ASC';
      }
      
      $sql = 'FROM recibosprov';
      $where = ' WHERE ';
      
      if($this->mostrar == 'pendientes')
      {
         $sql .= $where."estado = 'Emitido' AND ". "fechav >= ".$this->empresa->var2str(date('d-m-Y'));
         $where = ' AND ';
      }
      else if($this->mostrar == 'vencidos')
      {
         $sql .= $where."estado = 'Devuelto' OR estado = 'Vencido'";
         $where = ' AND ';
      }
      else
      {
         if($this->proveedor)
         {
            $sql .= $where."codproveedor = ".$this->empresa->var2str($this->proveedor->codproveedor);
            $where = ' AND ';
         }
         
         if($this->estado)
         {
            $sql .= $where."estado = ".$this->empresa->var2str($this->estado);
            $where = ' AND ';
         }
         
         if($this->desde != '')
         {
            if($this->check_vencimiento)
            {
               $sql .= $where."fechav >= ".$this->empresa->var2str($this->desde);
            }
            else
            {
               $sql .= $where."fecha >= ".$this->empresa->var2str($this->desde);
            }
            $where = ' AND ';
         }
         
         if($this->hasta != '')
         {
            if($this->check_vencimiento)
            {
               $sql .= $where."fechav <= ".$this->empresa->var2str($this->hasta);
            }
            else
            {
               $sql .= $where."fecha <= ".$this->empresa->var2str($this->hasta);
            }
            $where = ' AND ';
         }
      }
      
      $data = $this->db->select('SELECT COUNT(idrecibo) as num '.$sql);
      if($data)
      {
         $this->num_resultados = intval($data[0]['num']);
         
         $data2 = $this->db->select_limit('SELECT * '.$sql.' ORDER BY '.$this->order.$order2, FS_ITEM_LIMIT, $this->offset);
         if($data2)
         {
            foreach($data2 as $d)
            {
               $this->resultados[] = new recibo_proveedor($d);
            }
         }
         
         $data2 = $this->db->select("SELECT SUM(importe) as total ".$sql);
         if($data2)
         {
            $this->total_resultados = floatval($data2[0]['total']);
            $this->total_resultados_txt = 'Suma total de los resultados:';
         }
      }
      
      /// ahora obtenemos el número de recibos pendientes
      $sql = "SELECT COUNT(idrecibo) as num FROM recibosprov WHERE estado = 'Emitido'"
              . " AND fechav >= ".$this->empresa->var2str(date('d-m-Y')).';';
      $data = $this->db->select($sql);
      if($data)
      {
         $this->num_pendientes = intval($data[0]['num']);
      }
      
      /// ahora obtenemos el número de recibos vencidos
      $sql = "SELECT COUNT(idrecibo) as num FROM recibosprov WHERE estado = 'Devuelto' OR estado = 'Vencido';";
      $data = $this->db->select($sql);
      if($data)
      {
         $this->num_vencidos = intval($data[0]['num']);
      }
   }
   
   public function paginas()
   {
      $codproveedor = '';
      if($this->proveedor)
      {
         $codproveedor = $this->proveedor->codproveedor;
      }
      
      $url = $this->url()."&mostrar=".$this->mostrar
              ."&codproveedor=".$codproveedor
              ."&desde=".$this->desde
              ."&hasta=".$this->hasta;
      
      if($this->check_vencimiento)
      {
         $url .= '&vencimiento=TRUE';
      }
      
      $paginas = array();
      $i = 0;
      $num = 0;
      $actual = 1;
      
      if($this->mostrar == 'pendientes')
      {
         $total = $this->num_pendientes;
      }
      else if($this->mostrar == 'vencidos')
      {
         $total = $this->num_vencidos;
      }
      else
      {
         $total = $this->num_resultados;
      }
      
      /// añadimos todas la página
      while($num < $total)
      {
         $paginas[$i] = array(
             'url' => $url."&offset=".($i*FS_ITEM_LIMIT),
             'num' => $i + 1,
             'actual' => ($num == $this->offset)
         );
         
         if($num == $this->offset)
         {
            $actual = $i;
         }
         
         $i++;
         $num += FS_ITEM_LIMIT;
      }
      
      /// ahora descartamos
      foreach($paginas as $j => $value)
      {
         $enmedio = intval($i/2);
         
         /**
          * descartamos todo excepto la primera, la última, la de enmedio,
          * la actual, las 5 anteriores y las 5 siguientes
          */
         if( ($j>1 AND $j<$actual-5 AND $j!=$enmedio) OR ($j>$actual+5 AND $j<$i-1 AND $j!=$enmedio) )
         {
            unset($paginas[$j]);
         }
      }
      
      if( count($paginas) > 1 )
      {
         return $paginas;
      }
      else
      {
         return array();
      }
   }
   
   private function share_extenstion()
   {
      /// metemos la pestaña de recibos en la página de factura de compra
      $fsext = new fs_extension();
      $fsext->name = 'recibos_factura';
      $fsext->from = __CLASS__;
      $fsext->to = 'compras_factura';
      $fsext->type = 'tab';
      $fsext->text = '<span class="glyphicon glyphicon-piggy-bank" aria-hidden="true"></span>'
              . '<span class="hidden-xs">&nbsp; Recibos</span>';
      $fsext->save();
      
      /// desactivamos el botón de pagada/sin pagar de la factura
      $fsext2 = new fs_extension();
      $fsext2->name = 'no_button_pagada';
      $fsext2->from = __CLASS__;
      $fsext2->to = 'compras_factura';
      $fsext2->type = 'config';
      $fsext2->text = 'no_button_pagada';
      $fsext2->save();
      
      /// metemos el botón recibos en la página del proveedor
      $fsext3 = new fs_extension();
      $fsext3->name = 'recibos_proveedor';
      $fsext3->from = __CLASS__;
      $fsext3->to = 'compras_proveedor';
      $fsext3->type = 'button';
      $fsext3->text = '<span class="glyphicon glyphicon-piggy-bank" aria-hidden="true"></span> &nbsp; Recibos';
      $fsext3->save();
   }
   
   private function nuevo_recibo()
   {
      $factura = new factura_proveedor();
      $this->factura = $factura->get($_POST['idfactura']);
      if($this->factura)
      {
         $recibo = new recibo_proveedor();
         $recibo->cifnif = $this->factura->cifnif;
         $recibo->coddivisa = $this->factura->coddivisa;
         $recibo->tasaconv = $this->factura->tasaconv;
         $recibo->codpago = $this->factura->codpago;
         $recibo->codproveedor = $this->factura->codproveedor;
         $recibo->codserie = $this->factura->codserie;
         $recibo->estado = 'Emitido';
         $recibo->fecha = $_POST['fecha'];
         $recibo->fechav = $_POST['fechav'];
         $recibo->idfactura = $this->factura->idfactura;
         $recibo->importe = floatval($_POST['importe']);
         $recibo->nombreproveedor = $this->factura->nombre;
         $recibo->numero = $recibo->new_numero($recibo->idfactura);
         $recibo->codigo = $this->factura->codigo.'-'.sprintf('%02s', $recibo->numero);
         
         $cbp = new cuenta_banco_proveedor();
         foreach($cbp->all_from_proveedor($recibo->codproveedor) as $cuenta)
         {
            if( is_null($recibo->codcuenta) OR $cuenta->principal )
            {
               $recibo->codcuenta = $cuenta->codcuenta;
               $recibo->iban = $cuenta->iban;
               $recibo->swift = $cuenta->swift;
            }
         }
         
         if( $recibo->save() )
         {
            $this->new_message('Recibo creado correctamente.');
            header('Location: '.$recibo->url());
         }
         else
         {
            $this->new_error_msg('Error al guardar el recibo.');
         }
      }
      else
      {
         $this->new_error_msg('Factura no encontrada.');
      }
   }
   
   private function sync_factura($actualizar = FALSE)
   {
      $recibo_factura = new recibo_factura();
      
      if($actualizar)
      {
         $this->resultados = $recibo_factura->sync_factura_prov($this->factura);
      }
      else
      {
         $recibo_factura->sync_factura_prov($this->factura);
      }
      
      /// ¿Hay errores?
      foreach($recibo_factura->errors as $err)
      {
         $this->new_error_msg($err);
      }
      
      $recibo_factura->errors = array();
   }
   
   private function cron_job()
   {
      $recibo = new recibo_proveedor();
      $recibo->cron_job();
      
      $sql = "SELECT * FROM facturasprov WHERE NOT pagada AND idfactura NOT IN (SELECT idfactura FROM recibosprov)";
      $data = $this->db->select_limit($sql, FS_ITEM_LIMIT, 0);
      if($data)
      {
         foreach($data as $d)
         {
            $this->factura = new factura_proveedor($d);
            $this->sync_factura();
         }
      }
   }
}
