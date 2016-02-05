<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2014-2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('albaran_cliente.php');
require_model('articulo.php');
require_model('cliente.php');
require_model('pedido_cliente.php');
require_model('serie.php');

/**
 * Description of ventas_agrupar_presup
 *
 * @author carlos
 */
class ventas_agrupar_pedidos extends fs_controller
{
   public $cliente;
   public $codserie;
   public $desde;
   public $hasta;
   public $resultados;
   public $serie;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Agrupar '.FS_PEDIDOS, 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->share_extension();
      $this->cliente = FALSE;
      $this->desde = Date('01-01-Y');
      $this->hasta = Date('t-m-Y');
      $this->resultados = FALSE;
      $this->serie = new serie();
      
      if( isset($_REQUEST['buscar_cliente']) )
      {
         $this->buscar_cliente();
      }
      else if( isset($_REQUEST['codcliente']) )
      {
         $cli0 = new cliente();
         $this->cliente = $cli0->get($_REQUEST['codcliente']);
         
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
         
         if($this->cliente)
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
      $fsext->to = 'ventas_pedidos';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-duplicate"></span>'
              . '<span class="hidden-xs">&nbsp; Agrupar</span>';
      $fsext->save();
   }
   
   private function buscar_cliente()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $cliente = new cliente();
      $json = array();
      foreach($cliente->search($_REQUEST['buscar_cliente']) as $cli)
      {
         $json[] = array('value' => $cli->nombre, 'data' => $cli->codcliente);
      }
      
      header('Content-Type: application/json');
      echo json_encode( array('query' => $_REQUEST['buscar_cliente'], 'suggestions' => $json) );
   }
   
   private function buscar_pedidos()
   {
      $plist = array();
      $sql = "SELECT * FROM pedidoscli WHERE codcliente = ".$this->cliente->var2str($this->cliente->codcliente);
      $sql .= " AND fecha >= ".$this->cliente->var2str($this->desde);
      $sql .= " AND fecha <= ".$this->cliente->var2str($this->hasta);
      $sql .= " AND codserie = ".$this->cliente->var2str($this->codserie);
      $sql .= " AND status = ".$this->cliente->var2str(0).' ORDER BY fecha DESC;';
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
            $plist[] = new pedido_cliente($d);
      }
      
      return $plist;
   }
   
   private function agrupar_pedidos()
   {
      $continuar = TRUE;
      $albaran = new albaran_cliente();
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
                  $albaran->cifnif = $this->cliente->cifnif;
                  $albaran->codcliente = $this->cliente->codcliente;
                  $albaran->nombrecliente = $this->cliente->razonsocial;
                  $albaran->apartado = '';
                  $albaran->ciudad = '';
                  $albaran->codpais = $this->empresa->codpais;
                  $albaran->codpostal = '';
                  $albaran->direccion = '';
                  $albaran->provincia = '';
                  
                  foreach($this->cliente->get_direcciones() as $dir)
                  {
                     if($dir->domfacturacion)
                     {
                        $albaran->apartado = $dir->apartado;
                        $albaran->ciudad = $dir->ciudad;
                        $albaran->coddir = $dir->id;
                        $albaran->codpais = $dir->codpais;
                        $albaran->codpostal = $dir->codpostal;
                        $albaran->direccion = $dir->direccion;
                        $albaran->provincia = $dir->provincia;
                        break;
                     }
                  }
                  
                  if( !$albaran->save() )
                  {
                     $continuar = FALSE;
                     $this->new_error_msg('Error al agrupar el pedido.');
                  }
                  
                  $albaran_rellenado = TRUE;
               }
               
               $linea = new linea_albaran_cliente();
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
                  /// desconamos el stock
                  $articulo = $art0->get($linea->referencia);
                  if($articulo)
                  {
                     $articulo->sum_stock($albaran->codalmacen, 0 - $linea->cantidad);
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
               $ped->status = 1;
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
      $data = $this->db->select("SELECT * FROM lineasalbaranescli WHERE idlineapedido = ".$this->empresa->var2str($idlinea).";");
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
      $pedido = new pedido_cliente();
      
      $offset = 0;
      $pedidos = $pedido->all_ptealbaran($offset);
      while($pedidos)
      {
         foreach($pedidos as $ped)
         {
            $encontrado = FALSE;
            foreach($pendientes as $i => $pe)
            {
               if($ped->codcliente == $pe['codcliente'])
               {
                  $encontrado = TRUE;
                  $pendientes[$i]['num']++;
                  break;
               }
            }
            
            if(!$encontrado)
            {
               $pendientes[] = array(
                   'codcliente' => $ped->codcliente,
                   'nombre' => $ped->nombrecliente,
                   'codserie' => $ped->codserie,
                   'num' => 1
               );
            }
            
            $offset++;
         }
         
         $pedidos = $pedido->all_ptealbaran($offset);
      }
      
      return $pendientes;
   }
}
