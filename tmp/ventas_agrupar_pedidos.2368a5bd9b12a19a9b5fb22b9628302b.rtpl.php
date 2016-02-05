<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript">
   $(document).ready(function() {
      document.f_agrupar_pre.ac_cliente.focus();
      $("#ac_cliente").autocomplete({
         serviceUrl: '<?php echo $fsc->url();?>',
         paramName: 'buscar_cliente',
         onSelect: function (suggestion) {
            if(suggestion)
            {
               if(document.f_agrupar_pre.codcliente.value != suggestion.data && suggestion.data != '')
               {
                  document.f_agrupar_pre.codcliente.value = suggestion.data;
               }
            }
         }
      });
   });
</script>

<form name="f_agrupar_pre" action="<?php echo $fsc->url();?>" method="post" class="form">
   <?php if( $fsc->cliente ){ ?>

   <input type="hidden" name="codcliente" value="<?php echo $fsc->cliente->codcliente;?>"/>
   <?php }else{ ?>

   <input type="hidden" name="codcliente"/>
   <?php } ?>

   <div class="container-fluid">
      <div class="row">
         <div class="col-sm-12">
            <div class="page-header">
               <h1>
                  <a class="btn btn-xs btn-default" href="index.php?page=ventas_pedidos">
                     <span class="glyphicon glyphicon-arrow-left"></span>
                  </a>
                  <span class="glyphicon glyphicon-duplicate"></span>
                  Agrupar <?php  echo FS_PEDIDOS;?>

                  <a class="btn btn-xs btn-default" href="<?php echo $fsc->url();?>" title="Recargar la página">
                     <span class="glyphicon glyphicon-refresh"></span>
                  </a>
                  <span class="btn-group">
                  <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                     <?php if( $value1->type=='button' ){ ?>

                     <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" class="btn btn-xs btn-default"><?php echo $value1->text;?></a>
                     <?php } ?>

                  <?php } ?>

                  </span>
               </h1>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-4">
            <div class="form-group">
               Cliente:
               <div class="input-group">
                  <?php if( $fsc->cliente ){ ?>

                  <input class="form-control" type="text" name="ac_cliente" value="<?php echo $fsc->cliente->nombre;?>" id="ac_cliente" placeholder="Buscar" autocomplete="off"/>
                  <?php }else{ ?>

                  <input class="form-control" type="text" name="ac_cliente" id="ac_cliente" placeholder="Buscar" autocomplete="off"/>
                  <?php } ?>

                  <span class="input-group-btn">
                     <button class="btn btn-default" type="button" onclick="document.f_agrupar_pre.ac_cliente.value='';document.f_agrupar_pre.ac_cliente.focus();">
                        <span class="glyphicon glyphicon-edit"></span>
                     </button>
                  </span>
               </div>
            </div>
         </div>
         <div class="col-sm-2">
            Serie:
            <select name="codserie" class="form-control">
            <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

				   <?php if( $value1->codserie==$fsc->codserie ){ ?>

				   <option value="<?php echo $value1->codserie;?>" selected=""><?php echo $value1->descripcion;?></option>
				   <?php }else{ ?>

				   <option value="<?php echo $value1->codserie;?>"><?php echo $value1->descripcion;?></option>
				   <?php } ?>

				<?php } ?>

            </select>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               Desde:
               <input type="text" name="desde" class="form-control datepicker" value="<?php echo $fsc->desde;?>" autocomplete="off"/>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               Hasta:
               <input type="text" name="hasta" class="form-control datepicker" value="<?php echo $fsc->hasta;?>" autocomplete="off"/>
            </div>
         </div>
         <div class="col-sm-2">
            <br/>
            <button type="submit" class="btn btn-sm btn-primary" onclick="this.disabled=true;this.form.submit();">
               <span class="glyphicon glyphicon-search"></span> &nbsp; Buscar
            </button>
         </div>
      </div>
   </div>
</form>

<?php if( $fsc->resultados ){ ?>

<form id="f_agrupar_res" action="<?php echo $fsc->url();?>" method="post" class="form">
   <input type="hidden" name="petition_id" value="<?php echo $fsc->random_string();?>"/>
   <input type="hidden" name="codcliente" value="<?php echo $fsc->cliente->codcliente;?>"/>
   <input type="hidden" name="codserie" value="<?php echo $fsc->codserie;?>"/>
   <input type="hidden" name="desde" value="<?php echo $fsc->desde;?>"/>
   <input type="hidden" name="hasta" value="<?php echo $fsc->hasta;?>"/>
   <div class="container-fluid">
      <div class="row">
         <div class="col-sm-12">
            <p class="help-block">
               Esta herramienta permite agrupar de forma <b>parcial</b>, así que puedes
               desmarcar las líneas que quieras y dejar los <?php  echo FS_PEDIDOS;?>

               abiertos para poder agrupar el resto después.
               La columna <b>servido</b> te indica cuantas unidades han sido ya
               aprobadas previamente.
            </p>
         </div>
      </div>
      <!--<?php $num=$this->var['num']=0;?>-->
      <?php $loop_var1=$fsc->resultados; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <div class="row">
         <div class="col-sm-12">
            <div class="panel panel-default">
               <div class="panel-heading">
                  <a href="<?php echo $value1->url();?>"><?php echo $value1->codigo;?></a> <?php echo $value1->numero2;?>

                  <?php echo $value1->fecha;?> <?php echo $value1->hora;?>

               </div>
               <?php if( $value1->observaciones!='' ){ ?>

               <div class="panel-body">
                  <p class="help-block"><?php echo $value1->observaciones;?></p>
               </div>
               <?php } ?>

               <div class="table-responsive">
                  <table class="table table-hover">
                     <thead>
                        <tr>
                           <th>Artículo</th>
                           <th class="text-right" width="100">Cantidad</th>
                           <th class="text-right" width="100">Servido</th>
                           <th class="text-right">Precio</th>
                           <th class="text-right">Dto.</th>
                           <th class="text-right">Total</th>
                           <th class="text-right">IVA</th>
                           <th class="text-right">Total+IVA</th>
                        </tr>
                     </thead>
                     <?php $loop_var2=$value1->get_lineas(); $counter2=-1; if($loop_var2) foreach( $loop_var2 as $key2 => $value2 ){ $counter2++; ?>

                     <!--<?php $servido=$this->var['servido']=$fsc->linea_servida($value2->idlinea);?>-->
                     <tr<?php if( $servido==$value2->cantidad ){ ?> class="bg-success"<?php }elseif( $servido>0 ){ ?> class="bg-warning"<?php } ?>>
                        <td>
                           <label>
                              <input type="checkbox" name="idl_<?php echo $num;?>" value="<?php echo $value2->idlinea;?>"<?php if( $servido!=$value2->cantidad ){ ?> checked="checked"<?php } ?>/>
                              &nbsp; <a href="<?php echo $value2->articulo_url();?>"><?php echo $value2->referencia;?></a> <?php echo $value2->descripcion;?>

                           </label>
                        </td>
                        <td>
                           <input type="number" name="cantidad_<?php echo $num;?>" value="<?php echo $value2->cantidad;?>" class="form-control text-right" autocomplete="off"/>
                           <!--<?php $num=$this->var['num']=$num+1;?>-->
                        </td>
                        <td><div class="form-control text-right"><?php echo $servido;?></div></td>
                        <td class="text-right"><?php echo $fsc->show_precio($value2->pvpunitario, $value1->coddivisa);?></td>
                        <td class="text-right"><?php echo $fsc->show_numero($value2->dtopor);?> %</td>
                        <td class="text-right"><?php echo $fsc->show_precio($value2->pvptotal, $value1->coddivisa);?></td>
                        <td class="text-right"><?php echo $fsc->show_numero($value2->iva);?> %</td>
                        <td class="text-right"><?php echo $fsc->show_precio($value2->total_iva(), $value1->coddivisa);?></td>
                     </tr>
                     <?php } ?>

                     <tr>
                        <td colspan="5">
                           <label>
                              <input type="checkbox" name="aprobado[]" value="<?php echo $value1->idpedido;?>" checked="checked"/>
                              &nbsp; marcar <?php  echo FS_PEDIDO;?> como aprobado
                           </label>
                        </td>
                        <td class="text-right"><b><?php echo $fsc->show_precio($value1->neto, $value1->coddivisa);?></b></td>
                        <td class="text-right"><b><?php echo $fsc->show_precio($value1->totaliva, $value1->coddivisa);?></b></td>
                        <td class="text-right"><b><?php echo $fsc->show_precio($value1->total, $value1->coddivisa);?></b></td>
                     </tr>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <?php } ?>

      <div class="row">
         <div class="col-sm-12 text-right">
            <button type="submit" class="btn btn-sm btn-primary" onclick="this.disabled=true;this.form.submit();">
               <span class="glyphicon glyphicon-file"></span> &nbsp; Generar <?php  echo FS_ALBARAN;?>

            </button>
         </div>
      </div>
   </div>
</form>
<?php }elseif( $fsc->cliente ){ ?>

<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12">
         <div class="panel panel-info">
            <div class="panel-heading">
               <h3 class="panel-title">Ayuda</h3>
            </div>
            <div class="panel-body">
               No se han encontrado resultados para esta búsqueda.
            </div>
         </div>
      </div>
   </div>
</div>
<?php }else{ ?>

<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Ayuda</h3>
            </div>
            <div class="panel-body">
               Con esta herramienta puedes buscar y agrupar varios <?php  echo FS_PEDIDOS;?>

               de un mismo cliente y generar un único <?php  echo FS_ALBARAN;?>.
               Si hay <?php  echo FS_PEDIDOS;?> pendientes aparecerán los clientes aquí debajo
               para ahorrarte clics ;-)
            </div>
         </div>
      </div>
   </div>
   <div class="row">
      <?php $loop_var1=$fsc->pendientes(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <div class="col-sm-4">
         <a href="<?php echo $fsc->url();?>&codcliente=<?php echo $value1['codcliente'];?>&codserie=<?php echo $value1['codserie'];?>" class="btn btn-sm btn-block <?php if( $value1['num']>1 ){ ?>btn-info<?php }else{ ?>btn-default<?php } ?>">
            <span class="glyphicon glyphicon-user"></span>
            &nbsp; <?php echo $value1['nombre'];?>

            <?php if( $value1['num']>1 ){ ?>&nbsp; (<?php echo $value1['num'];?>)<?php } ?>

         </a>
         <br/>
      </div>
      <?php } ?>

   </div>
</div>
<?php } ?>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>