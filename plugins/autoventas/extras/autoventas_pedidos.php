<?php

require_model('almacen.php');
require_model('articulo.php');
require_model('articulo_combinacion.php');
require_model('asiento_factura.php');
require_model('cliente.php');
require_model('divisa.php');
require_model('fabricante.php');
require_model('familia.php');
require_model('forma_pago.php');
require_model('grupo_clientes.php');
require_model('impuesto.php');
require_model('pais.php');
require_model('pedido_cliente.php');
require_model('presupuesto_cliente.php');
require_model('serie.php');
require_model('tarifa.php');

class autoventas_pedidos extends fs_controller{
   public $agente;
   public $almacen;
   public $articulo;
   public $cliente;
   public $cliente_s;
   public $direccion;
   public $divisa;
   public $fabricante;
   public $familia;
   public $forma_pago;
   public $grupo;
   public $impuesto;
   public $nuevocli_setup;
   public $pais;
   public $results;
   public $serie;
   public $tipo;
   
      public function __construct()
   {

   }
   public function trata_pedidos ($listaPedidos = NULL)
   {
      $respuesta = array();
      $p=0;
      $a=0;
          for ($i=0;$i<count($listaPedidos);$i++) {
              if ($listaPedidos[$i]->tipo=="pedido") {
                $respuesta[$i] = $this->trata_pedido($listaPedidos[$i]);
                $p +=1;
                
              } else {
                  if ($listaPedidos[$i]->tipo=="albaran") { 
                        $respuesta[$i] = $this->trata_albaran($listaPedidos[$i]);
                        $a +=1;                  
                  }
              }
          }
      
      return "GENERADOS : $p PEDIDOS y $a ALBARANES";
   }
   
   protected function trata_pedido($pedidoRaw)
   {
      $this->cliente = new cliente();
      $this->cliente_s = FALSE;
      $this->direccion = FALSE;
      $this->fabricante = new fabricante();
      $this->familia = new familia();
      $this->impuesto = new impuesto();
      $this->results = array();
      $this->grupo = new grupo_clientes();
      $this->pais = new pais();
      
      /// cargamos la configuración
      $respuesta = "Intentando pedido...";
      
      $p = json_decode($pedidoRaw->pedido, TRUE);
      
      if( isset($p['cliente']) ) {
         $this->cliente_s = $this->cliente->get($p['cliente']);
         
         if($this->cliente_s)
         {
            foreach($this->cliente_s->get_direcciones() as $dir)
            {
               if($dir->domfacturacion)
               {
                  $this->direccion = $dir;
                  break;
               }
            }
         }
         
         if( isset($p['codagente']) )
         {
            $agente = new agente();
            $this->agente = $agente->get($p['codagente']);
         }
         
         
         $this->almacen = new almacen();
         $this->serie = new serie();
         $this->forma_pago = new forma_pago();
         $this->divisa = new divisa();
         $p["numero2"] = $pedidoRaw->numero2;
         $respuesta = $this->nuevo_pedido_cliente($p);
      }    
      return $respuesta;
   }
   
 
      protected function trata_albaran($pedidoRaw)
   {
      $this->cliente = new cliente();
      $this->cliente_s = FALSE;
      $this->direccion = FALSE;
      $this->fabricante = new fabricante();
      $this->familia = new familia();
      $this->impuesto = new impuesto();
      $this->results = array();
      $this->grupo = new grupo_clientes();
      $this->pais = new pais();
      
      /// cargamos la configuración
      $respuesta = "Intentando albaran...";
      
      $p = json_decode($pedidoRaw->pedido, TRUE);
      
      if( isset($p['cliente']) ) {
         $this->cliente_s = $this->cliente->get($p['cliente']);
         
         if($this->cliente_s)
         {
            foreach($this->cliente_s->get_direcciones() as $dir)
            {
               if($dir->domfacturacion)
               {
                  $this->direccion = $dir;
                  break;
               }
            }
         }
         
         if( isset($p['codagente']) )
         {
            $agente = new agente();
            $this->agente = $agente->get($p['codagente']);
         }
         
         
         $this->almacen = new almacen();
         $this->serie = new serie();
         $this->forma_pago = new forma_pago();
         $this->divisa = new divisa();
         $p["numero2"] = $pedidoRaw->numero2;
         $respuesta = $this->nuevo_albaran_cliente($p);
         echo "NUMERO2 " . $p["numero2"];
      }    
      return $respuesta;
   }
   
   /**
    * Devuelve los tipos de documentos a guardar,
    * así para añadir tipos no hay que tocar la vista.
    * @return type
    */
   public function tipos_a_guardar()
   {
      $tipos = array();
      
      if( $this->user->have_access_to('ventas_presupuesto') AND class_exists('presupuesto_cliente') )
      {
         $tipos[] = array('tipo' => 'presupuesto', 'nombre' => ucfirst(FS_PRESUPUESTO).' para cliente');
      }
      
      if( $this->user->have_access_to('ventas_pedido') AND class_exists('pedido_cliente') )
      {
         $tipos[] = array('tipo' => 'pedido', 'nombre' => ucfirst(FS_PEDIDO).' de cliente');
      }
      
      if( $this->user->have_access_to('ventas_albaran') )
      {
         $tipos[] = array('tipo' => 'albaran', 'nombre' => ucfirst(FS_ALBARAN).' de cliente');
      }
      
      if( $this->user->have_access_to('ventas_factura') )
      {
         $tipos[] = array('tipo' => 'factura', 'nombre' => 'Factura de cliente');
      }
      
      return $tipos;
   }
   
   public function url()
   {
      return 'index.php?page='.__CLASS__.'&tipo='.$this->tipo;
   }
   
   private function buscar_cliente()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $json = array();
      foreach($this->cliente->search($_REQUEST['buscar_cliente']) as $cli)
      {
         $json[] = array('value' => $cli->nombre, 'data' => $cli->codcliente);
      }
      
      header('Content-Type: application/json');
      echo json_encode( array('query' => $_REQUEST['buscar_cliente'], 'suggestions' => $json) );
   }
   
   private function datos_cliente()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      header('Content-Type: application/json');
      echo json_encode( $this->cliente->get($_REQUEST['datoscliente']) );
   }
   
   private function new_articulo()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $art0 = new articulo();
      $art0->referencia = $_REQUEST['referencia'];
      if( $art0->exists() )
      {
         $this->results[] = $art0->get($_REQUEST['referencia']);
      }
      else
      {
         $art0->descripcion = $_REQUEST['descripcion'];
         $art0->set_impuesto($_REQUEST['codimpuesto']);
         $art0->set_pvp( floatval($_REQUEST['pvp']) );
         
         if($_REQUEST['codfamilia'] != '')
         {
            $art0->codfamilia = $_REQUEST['codfamilia'];
         }
         
         if($_REQUEST['codfabricante'] != '')
         {
            $art0->codfabricante = $_REQUEST['codfabricante'];
         }
         
         if( $art0->save() )
         {
            $this->results[] = $art0;
         }
      }
      
      header('Content-Type: application/json');
      echo json_encode($this->results);
   }
   
   private function new_search()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;
      
      $fsvar = new fs_var();
      $multi_almacen = $fsvar->simple_get('multi_almacen');
      $stock = new stock();
      
      $articulo = new articulo();
      $codfamilia = '';
      if( isset($_REQUEST['codfamilia']) )
      {
         $codfamilia = $_REQUEST['codfamilia'];
      }
      $codfabricante = '';
      if( isset($_REQUEST['codfabricante']) )
      {
         $codfabricante = $_REQUEST['codfabricante'];
      }
      $con_stock = isset($_REQUEST['con_stock']);
      $this->results = $articulo->search($this->query, 0, $codfamilia, $con_stock, $codfabricante);
      
      /// añadimos la busqueda, el descuento, la cantidad, etc...
      foreach($this->results as $i => $value)
      {
         $this->results[$i]->query = $this->query;
         $this->results[$i]->dtopor = 0;
         $this->results[$i]->cantidad = 1;
         
         $this->results[$i]->stockalm = $this->results[$i]->stockfis;
         if( $multi_almacen AND isset($_REQUEST['codalmacen']) )
         {
            $this->results[$i]->stockalm = $stock->total_from_articulo($this->results[$i]->referencia, $_REQUEST['codalmacen']);
         }
      }
      
      /// ejecutamos las funciones de las extensiones
      foreach($this->extensions as $ext)
      {
         if($ext->type == 'function' AND $ext->params == 'new_search')
         {
            $name = $ext->text;
            $name($this->db, $this->results);
         }
      }
      
      /// buscamos el grupo de clientes y la tarifa
      if( isset($_REQUEST['codcliente']) )
      {
         $cliente = $this->cliente->get($_REQUEST['codcliente']);
         if($cliente)
         {
            if($cliente->codgrupo)
            {
               $grupo0 = new grupo_clientes();
               $tarifa0 = new tarifa();
               
               $grupo = $grupo0->get($cliente->codgrupo);
               if($grupo)
               {
                  $tarifa = $tarifa0->get($grupo->codtarifa);
                  if($tarifa)
                  {
                     $tarifa->set_precios($this->results);
                  }
               }
            }
         }
      }
      
      header('Content-Type: application/json');
      echo json_encode($this->results);
   }
   
   private function get_precios_articulo()
   {
      /// cambiamos la plantilla HTML
      $this->template = 'ajax/nueva_venta_precios';
      
      $articulo = new articulo();
      $this->articulo = $articulo->get($_REQUEST['referencia4precios']);
   }
   
   private function get_combinaciones_articulo()
   {
      /// cambiamos la plantilla HTML
      $this->template = 'ajax/nueva_venta_combinaciones';
      
      $this->results = array();
      $comb1 = new articulo_combinacion();
      foreach($comb1->all_from_ref($_REQUEST['referencia4combi']) as $com)
      {
         if( isset($this->results[$com->codigo]) )
         {
            $this->results[$com->codigo]['desc'] .= ', '.$com->nombreatributo.' - '.$com->valor;
            $this->results[$com->codigo]['txt'] .= ', '.$com->nombreatributo.' - '.$com->valor;
         }
         else
         {
            $this->results[$com->codigo] = array(
                'ref' => $_REQUEST['referencia4combi'],
                'desc' => base64_decode($_REQUEST['desc'])."\n".$com->nombreatributo.' - '.$com->valor,
                'pvp' => floatval($_REQUEST['pvp']) + $com->impactoprecio,
                'dto' => floatval($_REQUEST['dto']),
                'codimpuesto' => $_REQUEST['codimpuesto'],
                'cantidad' => floatval($_REQUEST['cantidad']),
                'txt' => $com->nombreatributo.' - '.$com->valor
            );
         }
      }
   }
   
   public function get_tarifas_articulo($ref)
   {
      $tarlist = array();
      $articulo = new articulo();
      $tarifa = new tarifa();
      
      foreach($tarifa->all() as $tar)
      {
         $art = $articulo->get($ref);
         if($art)
         {
            $art->dtopor = 0;
            $aux = array($art);
            $tar->set_precios($aux);
            $tarlist[] = $aux[0];
         }
      }
      
      return $tarlist;
   }

   private function nuevo_pedido_cliente($p)
   {
       
       $respuesta = "";
      $continuar = TRUE;
      
      $cliente = $this->cliente->get($p['cliente']);
      if(!$cliente)
      {
         $respuesta .='Cliente no encontrado.';
         $continuar = FALSE;
      }
      
      $almacen = $this->almacen->get($p['almacen']);
      if($almacen)
      {
          //print_r($almacen);
          
        // $this->save_codalmacen($p['almacen']);
      }
      else
      {
         echo ('Almacén no encontrado.');
         $continuar = FALSE;
      }
      
      $eje0 = new ejercicio();
      $ejercicio = $eje0->get_by_fecha($p['fecha']);
      if(!$ejercicio)
      {
         echo ('Ejercicio no encontrado.');
         $continuar = FALSE;
      }
      
      $serie = $this->serie->get($p['serie']);
      if(!$serie)
      {
         echo ('Serie no encontrada.');
         $continuar = FALSE;
      }
      
      $forma_pago = $this->forma_pago->get($p['forma_pago']);
      if($forma_pago)
      {
         //$this->save_codpago($p['forma_pago']);
      }
      else
      {
         echo('Forma de pago no encontrada.');
         $continuar = FALSE;
      }
      
      $divisa = $this->divisa->get($p['divisa']);
      if(!$divisa)
      {
         echo ('Divisa no encontrada.');
         $continuar = FALSE;
      }
      
      $pedido = new pedido_cliente();

      if($continuar)
      {
         $pedido->fecha = $p['fecha'];
         $pedido->codalmacen = $almacen->codalmacen;
         $pedido->codejercicio = $ejercicio->codejercicio;
         $pedido->codserie = $serie->codserie;
         $pedido->codpago = $forma_pago->codpago;
         $pedido->coddivisa = $divisa->coddivisa;
         $pedido->tasaconv = $divisa->tasaconv;
         $pedido->numero2 = $p['numero2'];
         $pedido->codagente = $this->agente->codagente;
         $pedido->observaciones = $p['observaciones'];         
         $pedido->irpf = $serie->irpf;
         $pedido->porcomision = $this->agente->porcomision;
         
         $pedido->codcliente = $cliente->codcliente;
         $pedido->cifnif = $cliente->cifnif;
         $pedido->nombrecliente = $cliente->nombre;
         
         $direccion=array();
         foreach($cliente->get_direcciones() as $dir)
            {
               if($dir->domfacturacion)
               {
                  $direccion = $dir;
                  break;
               }
            }
            
         $pedido->ciudad = $direccion->ciudad;
         $pedido->codpais = $direccion->codpais;
         $pedido->codpostal = $direccion->codpostal;
         $pedido->direccion = $direccion->direccion;
         $pedido->provincia = $direccion->provincia;
         
         if( $pedido->save() )
         {
            $art0 = new articulo();
            $n = floatval($p['numlineas']);
            for($i = 0; $i <= $n; $i++)
            {
               if( isset($p['referencia_'.$i]) )
               {
                  $linea = new linea_pedido_cliente();
                  $linea->idpedido = $pedido->idpedido;
                  $linea->descripcion = $p['desc_'.$i];
                  
                  if( !$serie->siniva AND $cliente->regimeniva != 'Exento' )
                  {
                     $imp0 = $this->impuesto->get_by_iva($p['iva_'.$i]);
                     if($imp0)
                     {
                        $linea->codimpuesto = $imp0->codimpuesto;
                        $linea->iva = floatval($p['iva_'.$i]);
                        $linea->recargo = floatval($p['recargo_'.$i]);
                     }
                     else
                     {
                        $linea->iva = floatval($p['iva_'.$i]);
                        $linea->recargo = floatval($p['recargo_'.$i]);
                     }
                  }
                  
                  $linea->irpf = floatval($p['irpf_'.$i]);
                  $linea->pvpunitario = floatval($p['pvp_'.$i]);
                  $linea->cantidad = floatval($p['cantidad_'.$i]);
                  $linea->dtopor = floatval($p['dto_'.$i]);
                  $linea->pvpsindto = ($linea->pvpunitario * $linea->cantidad);
                  $linea->pvptotal = floatval($p['neto_'.$i]);
                  
                  $articulo = $art0->get($p['referencia_'.$i]);
                  if($articulo)
                  {
                     $linea->referencia = $articulo->referencia;
                  }
                  if( $linea->save() )
                  {
                     $pedido->neto += $linea->pvptotal;
                     $pedido->totaliva += ($linea->pvptotal * $linea->iva/100);
                     $pedido->totalirpf += ($linea->pvptotal * $linea->irpf/100);
                     $pedido->totalrecargo += ($linea->pvptotal * $linea->recargo/100);
                  }
                  else
                  {
                     $respuesta .= "¡Imposible guardar la linea con referencia: ".$linea->referencia;
                     $continuar = FALSE;
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
                   $respuesta .= "Pedido correcto";
               }
               else
                   $respuesta .= "¡Imposible actualizar el <a href='".$pedido->url()."'>".FS_PEDIDO."</a>!";
            }
            else if( $pedido->delete() )
            {
               $respuesta .= ucfirst(FS_PEDIDO)." eliminado correctamente.";
            }
            else
               $respuesta .= "¡Imposible eliminar el <a href='".$pedido->url()."'>".FS_PEDIDO."</a>!";
         }
         else
            $respuesta .= "¡Imposible guardar el ".FS_PEDIDO."!";
      }
   
      return $respuesta;
            }
   
private function nuevo_albaran_cliente($p)
   {
   // print_r($p);
      $continuar = TRUE;
      $respuesta ="";
      $cliente = $this->cliente->get($p['cliente']);
      if(!$cliente)
      {
         $respuesta .='Cliente no encontrado.';
         $continuar = FALSE;
      }
      
      $almacen = $this->almacen->get($p['almacen']);
      if($almacen)
      {
 
      }
      else
      {
         $respuesta .= 'Almacén no encontrado.';
         $continuar = FALSE;
      }
      
      $eje0 = new ejercicio();
      $ejercicio = $eje0->get_by_fecha($p['fecha'], FALSE);
      if(!$ejercicio)
      {
         $respuesta .='Ejercicio no encontrado.';
         $continuar = FALSE;
      }
      
      $serie = $this->serie->get($p['serie']);
      if(!$serie)
      {
         $respuesta .='Serie no encontrada.';
         $continuar = FALSE;
      }
      
      $forma_pago = $this->forma_pago->get($p['forma_pago']);
      if($forma_pago)
      {
      }
      else
      {
         $respuesta .= 'Forma de pago no encontrada.';
         $continuar = FALSE;
      }
      
      $divisa = $this->divisa->get($p['divisa']);
      if(!$divisa)
      {
         $respuesta .= 'Divisa no encontrada.';
         $continuar = FALSE;
      }
      
      $albaran = new albaran_cliente();
      
      if($continuar)
      {
         $albaran->fecha = $p['fecha'];
         $albaran->hora = $p['hora'];
         $albaran->codalmacen = $almacen->codalmacen;
         $albaran->codejercicio = $ejercicio->codejercicio;
         $albaran->codserie = $serie->codserie;
         $albaran->codpago = $forma_pago->codpago;
         $albaran->coddivisa = $divisa->coddivisa;
         $albaran->tasaconv = $divisa->tasaconv;

         
         $albaran->codagente = $this->agente->codagente;
         $albaran->numero2 = $p['numero2'];
         $albaran->observaciones = $p['observaciones'];
         $albaran->porcomision = $this->agente->porcomision;
         
         $albaran->codcliente = $cliente->codcliente;
         $albaran->cifnif = $cliente->cifnif;
         $albaran->nombrecliente = $cliente->nombre;
        $direccion=array();
         foreach($cliente->get_direcciones() as $dir)
            {
               if($dir->domfacturacion)
               {
                  $direccion = $dir;
                  break;
               }
            }
         $albaran->ciudad = $direccion->ciudad;
         $albaran->codpais = $direccion->codpais;
         $albaran->codpostal = $direccion->codpostal;
         $albaran->direccion = $direccion->direccion;
         $albaran->provincia = $direccion->provincia;
         
         if( $albaran->save() )
         {
            $art0 = new articulo();
            $n = floatval($p['numlineas']);
            for($i = 0; $i <= $n; $i++)
            {
               if( isset($p['referencia_'.$i]) )
               {
                  $linea = new linea_albaran_cliente();
                  $linea->idalbaran = $albaran->idalbaran;
                  $linea->descripcion = $p['desc_'.$i];
                  
                  if( !$serie->siniva AND $cliente->regimeniva != 'Exento' )
                  {
                     $imp0 = $this->impuesto->get_by_iva($p['iva_'.$i]);
                     if($imp0)
                     {
                        $linea->codimpuesto = $imp0->codimpuesto;
                        $linea->iva = floatval($p['iva_'.$i]);
                        $linea->recargo = floatval($p['recargo_'.$i]);
                     }
                     else
                     {
                        $linea->iva = floatval($p['iva_'.$i]);
                        $linea->recargo = floatval($p['recargo_'.$i]);
                     }
                  }
                  
                  $linea->irpf = floatval($p['irpf_'.$i]);
                  $linea->pvpunitario = floatval($p['pvp_'.$i]);
                  $linea->cantidad = floatval($p['cantidad_'.$i]);
                  $linea->dtopor = floatval($p['dto_'.$i]);
                  $linea->pvpsindto = ($linea->pvpunitario * $linea->cantidad);
                  $linea->pvptotal = floatval($p['neto_'.$i]);
                  
                  $articulo = $art0->get($p['referencia_'.$i]);
                  if($articulo)
                  {
                     $linea->referencia = $articulo->referencia;
                  }
                  
                  if( $linea->save() )
                  {
                     if( $articulo AND isset($p['stock']) )
                     {
                        /// descontamos del stock
                        $articulo->sum_stock($albaran->codalmacen, 0 - $linea->cantidad);
                     }
                     
                     $albaran->neto += $linea->pvptotal;
                     $albaran->totaliva += ($linea->pvptotal * $linea->iva/100);
                     $albaran->totalirpf += ($linea->pvptotal * $linea->irpf/100);
                     $albaran->totalrecargo += ($linea->pvptotal * $linea->recargo/100);
                     
                     if($linea->irpf > $albaran->irpf)
                     {
                        $albaran->irpf = $linea->irpf;
                     }
                  }
                  else
                  {
                     $respuesta .= "¡Imposible guardar la linea con referencia: ".$linea->referencia;
                     $continuar = FALSE;
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
                   $respuesta .="Albarán correcto";
               }
               else
                  $respuesta .="¡Imposible actualizar el <a href='".$albaran->url()."'>".FS_ALBARAN."</a>!";
            }
            else if( $albaran->delete() )
            {
               $respuesta .= FS_ALBARAN." eliminado correctamente.";
            }
            else
               $respuesta .= "¡Imposible eliminar el <a href='".$albaran->url()."'>".FS_ALBARAN."</a>!";
         }
         else
            $respuesta .= "¡Imposible guardar el ".FS_ALBARAN."!";
      }
      return $respuesta;
   }
   
   
}

/*
 * 
petition_id=zwalyAZo7cnh4kqfJWTmXLgDrCjExO
numlineas=2
cliente=000001
redir
serie=A
fecha=18-01-2016
hora=16:10:03
idlinea_0=-1
referencia_0=ARMDO
desc_0=Armario+Doble
cantidad_0=1
pvp_0=210
dto_0=0
neto_0=210
iva_0=21
recargo_0=0
irpf_0=0
total_0=254.10
idlinea_1=-1
referencia_1=MCAFE
desc_1=Mesa+de+Cafe
cantidad_1=1
pvp_1=110
dto_1=0
neto_1=110
iva_1=21
recargo_1=0
irpf_1=0
total_1=133.10
atotal=387.2
nombrecliente=Cliente1
cifnif=12345676789A
codagente=1
almacen=ALG
divisa=EUR
tasaconv
codpais=ESP
provincia=Baleares
ciudad=Ciutadella
codpostal=07760
direccion=Direccio+client+1
observaciones=Observacions
tipo=pedido
numero2
forma_pago=ENTRE
stock=TRUE
 */
?>