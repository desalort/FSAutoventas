<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2014-2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('albaran_proveedor.php');
require_model('articulo.php');
require_model('proveedor.php');
require_model('pedido_proveedor.php');
require_model('serie.php');

/**
 * Description of compras_agrupar_pedidos
 *
 * @author carlos
 */
class compras_agrupar_pedidos extends fs_controller
{
   public $proveedor;
   public $codserie;
   public $desde;
   public $hasta;
   public $resultados;
   public $serie;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Agrupar '.FS_PEDIDOS, 'compras', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->share_extension();
      $this->proveedor = FALSE;
      $this->desde = Date('01-01-Y');
      $this->hasta = Date('t-m-Y');
      $this->resultados = FALSE;
      $this->serie = new serie();
      
      if( isset($_REQUEST['buscar_proveedor']) )
      {
         $this->buscar_proveedor();
      }
      else if( isset($_REQUEST['codproveedor']) )
      {
         $prov0 = new proveedor();
         $this->proveedor = $prov0->get($_REQUEST['codproveedor']);
         
         if( isset($_REQUEST['codserie']) )
         {
            $this->codserie = $_REQUEST['codserie'];
         }
         
         if( isset($_REQUEST['desde']) )
         {
            $this->desde = $_REQUEST['desde'];
         }
         
         if( isset($_REQUEST['hasta']) )
         {
            $this->hasta = $_REQUEST['hasta'];
         }
         
         if($this->proveedor)
         {
            $this->resultados = $this->buscar_pedidos();
            
            if( isset($_POST['cantidad_0']) )
            {
               $this->agrupar_pedidos();
               $this->resultados = FALSE;
            }
         }
      }
   }
   
   private function share_extension()
   {
      $fsext = new fs_extension();
      $fsext->name = __CLASS__;
      $fsext->from = __CLASS__;
      $fsext->to = 'compras_pedidos';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-duplicate"></span><span class="hidden-xs">&nbsp; Agrupar</span>';
      $fsext->save();
   }
   
   private function buscar_proveedor()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $proveedor = new proveedor();
      $json = array();
      foreach($proveedor->search($_REQUEST['buscar_proveedor']) as $pro)
      {
         $json[] = array('value' => $pro->nombre, 'data' => $pro->codproveedor);
      }
      
      header('Content-Type: application/json');
      echo json_encode( array('query' => $_REQUEST['buscar_proveedor'], 'suggestions' => $json) );
   }
   
   private function buscar_pedidos()
   {
      $plist = array();
      $sql = "SELECT * FROM pedidosprov WHERE codproveedor = ".$this->proveedor->var2str($this->proveedor->codproveedor);
      $sql .= " AND fecha >= ".$this->proveedor->var2str($this->desde);
      $sql .= " AND fecha <= ".$this->proveedor->var2str($this->hasta);
      $sql .= " AND codserie = ".$this->proveedor->var2str($this->codserie);
      $sql .= " AND idalbaran IS NULL ORDER BY fecha DESC;";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
            $plist[] = new pedido_proveedor($d);
      }
      
      return $plist;
   }
   
   private function agrupar_pedidos()
   {
      $continuar = TRUE;
      $albaran = new albaran_proveedor();
      $albaran_rellenado = FALSE;
      $art0 = new articulo();
      $num = 0;
      
      foreach($this->resultados as $ped)
      {
         foreach($ped->get_lineas() as $lin)
         {
            if( !isset($_POST['idl_'.$num]) OR !$continuar )
            {
               
            }
            else if($lin->idlinea == intval($_POST['idl_'.$num]))
            {
               if(!$albaran_rellenado)
               {
                  $albaran->codagente = $this->user->codagente;
                  $albaran->codalmacen = $ped->codalmacen;
                  $albaran->coddivisa = $ped->coddivisa;
                  $albaran->tasaconv = $ped->tasaconv;
                  $albaran->codejercicio = $ped->codejercicio;
                  $albaran->codpago = $ped->codpago;
                  $albaran->codserie = $ped->codserie;
                  $albaran->irpf = $ped->irpf;
                  $albaran->nombre = $this->proveedor->nombre;
                  
                  if( !$albaran->save() )
                  {
                     $continuar = FALSE;
                     $this->new_error_msg('Error al agrupar el pedido.');
                  }
                  
                  $albaran_rellenado = TRUE;
               }
               
               $linea = new linea_albaran_proveedor();
               $linea->idalbaran = $albaran->idalbaran;
               $linea->idpedido = $ped->idpedido;
               $linea->idlineapedido = $lin->idlinea;
               $linea->referencia = $lin->referencia;
               $linea->descripcion = $lin->descripcion;
               $linea->cantidad = floatval($_POST['cantidad_'.$num]);
               $linea->pvpunitario = $lin->pvpunitario;
               $linea->codimpuesto = $lin->codimpuesto;
               $linea->dtopor = $lin->dtopor;
               $linea->irpf = $lin->irpf;
               $linea->iva = $lin->iva;
               $linea->recargo = $lin->recargo;
               $linea->pvpsindto = $linea->pvpunitario * $linea->cantidad;
               $linea->pvptotal = $linea->pvpunitario * $linea->cantidad * (100 - $linea->dtopor) / 100;
               if( $linea->save() )
               {
                  /// añadimos al stock
                  $articulo = $art0->get($linea->referencia);
                  if($articulo)
                  {
                     $articulo->sum_stock($albaran->codalmacen, $linea->cantidad, TRUE);
                  }
                  
                  $albaran->neto += $linea->pvptotal;
                  $albaran->totaliva += ($linea->pvptotal * $linea->iva/100);
                  $albaran->totalirpf += ($linea->pvptotal * $linea->irpf/100);
                  $albaran->totalrecargo += ($linea->pvptotal * $linea->recargo/100);
               }
               else
               {
                  $this->new_error_msg("¡Imposible guardar la linea con referencia: ".$linea->referencia);
                  $continuar = FALSE;
               }
            }
            
            $num++;
         }
         
         if( isset($_POST['aprobado']) )
         {
            if( in_array($ped->idpedido, $_POST['aprobado']) )
            {
               $ped->editable = FALSE;
               $ped->idalbaran = $albaran->idalbaran;
               $ped->save();
            }
         }
      }
      
      if($continuar)
      {
         /// redondeamos
         $albaran->neto = round($albaran->neto, FS_NF0);
         $albaran->totaliva = round($albaran->totaliva, FS_NF0);
         $albaran->totalirpf = round($albaran->totalirpf, FS_NF0);
         $albaran->totalrecargo = round($albaran->totalrecargo, FS_NF0);
         $albaran->total = $albaran->neto + $albaran->totaliva - $albaran->totalirpf + $albaran->totalrecargo;
         
         if( $albaran->save() )
         {
            $this->new_message('<a href="'.$albaran->url().'">'.ucfirst(FS_ALBARAN).'</a> generado correctamente.');
         }
         else
         {
            $this->new_error_msg('Error al generar el '.FS_ALBARAN);
            $albaran->delete();
         }
      }
      else if( !is_null($albaran->idalbaran) )
      {
         $albaran->delete();
      }
   }
   
   /// Devuelve la cantidad servida de esta línea
   public function linea_servida($idlinea)
   {
      $data = $this->db->select("SELECT * FROM lineasalbaranesprov WHERE idlineapedido = ".$this->empresa->var2str($idlinea).";");
      if($data)
      {
         return floatval($data[0]['cantidad']);
      }
      else
         return 0;
   }
   
   public function pendientes()
   {
      $pendientes = array();
      
      $pedido = new pedido_proveedor();
      foreach($pedido->all_ptealbaran() as $ped)
      {
         $encontrado = FALSE;
         foreach($pendientes as $i => $pe)
         {
            if($ped->codproveedor == $pe['codproveedor'])
            {
               $encontrado = TRUE;
               $pendientes[$i]['num']++;
               break;
            }
         }
         
         if(!$encontrado)
         {
            $pendientes[] = array(
                'codproveedor' => $ped->codproveedor,
                'nombre' => $ped->nombre,
                'codserie' => $ped->codserie,
                'num' => 1
            );
         }
      }
      
      return $pendientes;
   }
}
