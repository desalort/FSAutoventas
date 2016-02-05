<?php

/**
 * @author Carlos García Gómez      neorazorx@gmail.com
 * @copyright 2014-2015, Carlos García Gómez. All Rights Reserved. 
 */

require_model('cliente.php');
require_model('pedido_cliente.php');
require_model('presupuesto_cliente.php');
require_model('serie.php');

/**
 * Description of ventas_agrupar_presup
 *
 * @author carlos
 */
class ventas_agrupar_presup extends fs_controller
{
   public $cliente;
   public $codserie;
   public $desde;
   public $hasta;
   public $resultados;
   public $serie;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Agrupar '.FS_PRESUPUESTOS, 'ventas', FALSE, FALSE);
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
            $this->resultados = $this->buscar_presupuestos();
            
            if( isset($_POST['cantidad_0']) )
            {
               $this->agrupar_presupuestos();
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
      $fsext->to = 'ventas_presupuestos';
      $fsext->type = 'button';
      $fsext->text = '<span class="glyphicon glyphicon-duplicate"></span><span class="hidden-xs">&nbsp; Agrupar</span>';
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
   
   private function buscar_presupuestos()
   {
      $plist = array();
      $sql = "SELECT * FROM presupuestoscli WHERE codcliente = ".$this->cliente->var2str($this->cliente->codcliente);
      $sql .= " AND fecha >= ".$this->cliente->var2str($this->desde);
      $sql .= " AND fecha <= ".$this->cliente->var2str($this->hasta);
      $sql .= " AND codserie = ".$this->cliente->var2str($this->codserie);
      $sql .= " AND status = ".$this->cliente->var2str(0).' ORDER BY fecha DESC;';
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
            $plist[] = new presupuesto_cliente($d);
      }
      
      return $plist;
   }
   
   private function agrupar_presupuestos()
   {
      $continuar = TRUE;
      $pedido = new pedido_cliente();
      $pedido_rellenado = FALSE;
      $num = 0;
      
      foreach($this->resultados as $pres)
      {
         foreach($pres->get_lineas() as $lin)
         {
            if( !isset($_POST['idl_'.$num]) OR !$continuar )
            {
               
            }
            else if($lin->idlinea == intval($_POST['idl_'.$num]))
            {
               if(!$pedido_rellenado)
               {
                  $pedido->codagente = $this->user->codagente;
                  $pedido->codalmacen = $pres->codalmacen;
                  $pedido->coddivisa = $pres->coddivisa;
                  $pedido->tasaconv = $pres->tasaconv;
                  $pedido->codejercicio = $pres->codejercicio;
                  $pedido->codpago = $pres->codpago;
                  $pedido->codserie = $pres->codserie;
                  $pedido->irpf = $pres->irpf;
                  
                  foreach($this->cliente->get_direcciones() as $dir)
                  {
                     if($dir->domfacturacion)
                     {
                        $pedido->apartado = $dir->apartado;
                        $pedido->cifnif = $this->cliente->cifnif;
                        $pedido->ciudad = $dir->ciudad;
                        $pedido->codcliente = $this->cliente->codcliente;
                        $pedido->coddir = $dir->id;
                        $pedido->codpais = $dir->codpais;
                        $pedido->codpostal = $dir->codpostal;
                        $pedido->direccion = $dir->direccion;
                        $pedido->nombrecliente = $this->cliente->razonsocial;
                        $pedido->provincia = $dir->provincia;
                        break;
                     }
                  }
                  
                  if( !$pedido->save() )
                  {
                     $continuar = FALSE;
                     $this->new_error_msg('Error al agrupar el pedido.');
                  }
                  
                  $pedido_rellenado = TRUE;
               }
               
               $linea = new linea_pedido_cliente();
               $linea->idlineapresupuesto = $lin->idlinea;
               $linea->idpedido = $pedido->idpedido;
               $linea->idpresupuesto = $pres->idpresupuesto;
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
                  $pedido->neto += $linea->pvptotal;
                  $pedido->totaliva += ($linea->pvptotal * $linea->iva/100);
                  $pedido->totalirpf += ($linea->pvptotal * $linea->irpf/100);
                  $pedido->totalrecargo += ($linea->pvptotal * $linea->recargo/100);
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
            if( in_array($pres->idpresupuesto, $_POST['aprobado']) )
            {
               $pres->editable = FALSE;
               $pres->idpedido = $pedido->idpedido;
               $pres->status = 1;
               $pres->save();
            }
         }
      }
      
      if($continuar)
      {
         /// redondeamos
         $pedido->neto = round($pedido->neto, FS_NF0);
         $pedido->totaliva = round($pedido->totaliva, FS_NF0);
         $pedido->totalirpf = round($pedido->totalirpf, FS_NF0);
         $pedido->totalrecargo = round($pedido->totalrecargo, FS_NF0);
         $pedido->total = $pedido->neto + $pedido->totaliva - $pedido->totalirpf + $pedido->totalrecargo;
         
         if( $pedido->save() )
         {
            $this->new_message('<a href="'.$pedido->url().'">'.ucfirst(FS_PEDIDO).'</a> generado correctamente.');
         }
         else
         {
            $this->new_error_msg('Error al generar el '.FS_PEDIDO);
            $pedido->delete();
         }
      }
      else if( !is_null($pedido->idpedido) )
      {
         $pedido->delete();
      }
   }
   
   /// Devuelve la cantidad servida de esta línea
   public function linea_servida($idlinea)
   {
      $data = $this->db->select("SELECT * FROM lineaspedidoscli WHERE idlineapresupuesto = ".$this->empresa->var2str($idlinea).";");
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
      
      $presupuesto = new presupuesto_cliente();
      foreach($presupuesto->all_ptepedir() as $pre)
      {
         $encontrado = FALSE;
         foreach($pendientes as $i => $pe)
         {
            if($pre->codcliente == $pe['codcliente'])
            {
               $encontrado = TRUE;
               $pendientes[$i]['num']++;
               break;
            }
         }
         
         if(!$encontrado)
         {
            $pendientes[] = array(
                'codcliente' => $pre->codcliente,
                'nombre' => $pre->nombrecliente,
                'codserie' => $pre->codserie,
                'num' => 1
            );
         }
      }
      
      return $pendientes;
   }
}
