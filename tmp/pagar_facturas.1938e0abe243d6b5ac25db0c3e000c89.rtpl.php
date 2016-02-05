<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript">
   function fs_marcar_todo()
   {
      $("#f_agrupar_cli input[name='idfactura[]']").prop('checked', true);
   }
   function fs_marcar_nada()
   {
      $("#f_agrupar_cli input[name='idfactura[]']").prop('checked', false);
   }
   $(document).ready(function() {
      <?php if( !$fsc->cliente ){ ?>

      document.f_pagar_facturas.ac_cliente.focus();
      <?php } ?>

      $("#ac_cliente").autocomplete({
         serviceUrl: '<?php echo $fsc->url();?>',
         paramName: 'buscar_cliente',
         onSelect: function (suggestion) {
            if(suggestion)
            {
               if(document.f_pagar_facturas.codcliente.value != suggestion.data)
               {
                  document.f_pagar_facturas.codcliente.value = suggestion.data;
               }
            }
         }
      });
   });
</script>

<form name="f_pagar_facturas" class="form" action="<?php echo $fsc->url();?>" method="post">
   <input type="hidden" name="codcliente" value="<?php echo $fsc->codcliente;?>"/>
   <div class="container">
      <div class="row">
         <div class="col-sm-12">
            <div class="page-header">
               <h1>
                  <a href="index.php?page=ventas_facturas" class="btn btn-xs btn-default">
                     <span class="glyphicon glyphicon-arrow-left"></span>
                  </a>
                  Pagar facturas de venta
                  <a href="<?php echo $fsc->url();?>" class="btn btn-xs btn-default" title="recargar la página">
                     <span class="glyphicon glyphicon-refresh"></span>
                  </a>
               </h1>
               <p class="help-block">
                  Haz una búsqueda para seleccionar las facturas que quieres marcar como pagadas.
               </p>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-2">
            <div class="form-group">
               Desde:
               <input class="form-control datepicker" type="text" name="desde" value="<?php echo $fsc->desde;?>" autocomplete="off"/>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               Hasta:
               <input class="form-control datepicker" type="text" name="hasta" value="<?php echo $fsc->hasta;?>" autocomplete="off"/>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               Serie:
               <select name="codserie" class="form-control">
                  <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <option value="<?php echo $value1->codserie;?>"<?php if( $value1->codserie==$fsc->codserie ){ ?> selected=""<?php } ?>><?php echo $value1->descripcion;?></option>
                  <?php } ?>

               </select>
            </div>
         </div>
         <div class="col-sm-4">
            <div class="form-group">
               Cliente:
               <?php if( $fsc->cliente ){ ?>

               <input id="ac_cliente" class="form-control" type="text" name="ac_cliente" placeholder="<?php echo $fsc->cliente->nombre;?>" autocomplete="off"/>
               <?php }else{ ?>

               <input id="ac_cliente" class="form-control" type="text" name="ac_cliente" placeholder="buscar..." autocomplete="off"/>
               <?php } ?>

            </div>
         </div>
         <div class="col-sm-2">
            <br/>
            <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
               <span class="glyphicon glyphicon-search"></span> &nbsp; Buscar
            </button>
         </div>
      </div>
   </div>
</form>

<?php if( $fsc->resultados ){ ?>

<form id="f_agrupar_cli" class="form" name="f_agrupar_cli" action="<?php echo $fsc->url();?>" method="post">
   <input type="hidden" name="codcliente" value="<?php echo $fsc->codcliente;?>"/>
   <input type="hidden" name="desde" value="<?php echo $fsc->desde;?>"/>
   <input type="hidden" name="hasta" value="<?php echo $fsc->hasta;?>"/>
   <input type="hidden" name="codserie" value="<?php echo $fsc->codserie;?>"/>
   <div class="container">
      <div class="row">
         <div class="col-sm-12">
            <ul class="nav nav-tabs">
               <li role="presentation" class="active"><a href="#">Resultados</a></li>
            </ul>
            <div class="table-responsive">
               <!--<?php $total=$this->var['total']=0;?>-->
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th></th>
                        <th class="text-left">Código + Número 2</th>
                        <th class="text-left">Cliente</th>
                        <th class="text-left">Observaciones</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Fecha</th>
                        <th class="text-right">Hora</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->resultados; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr class="<?php if( $value1->anulada ){ ?>danger<?php }elseif( $value1->total<=0 ){ ?>warning<?php } ?>">
                     <td class="text-center">
                        <?php if( $value1->pagada ){ ?>

                        <span class="glyphicon glyphicon-ok" title="La factura está pagada"></span>
                        <?php }else{ ?>

                        <input type="checkbox" name="idfactura[]" value="<?php echo $value1->idfactura;?>" checked="checked"/>
                        <!--<?php echo $total+=$value1->total;?>-->
                        <?php } ?>

                     </td>
                     <td>
                        <a href="<?php echo $value1->url();?>"><?php echo $value1->codigo;?></a> <?php echo $value1->numero2;?>

                        <?php if( $value1->anulada ){ ?>

                        <span class="glyphicon glyphicon-remove" title="La <?php  echo FS_FACTURA;?> está anulada"></span>
                        <?php } ?>

                        <?php if( $value1->idfacturarect ){ ?>

                        <span class="glyphicon glyphicon-flag" title="<?php  echo FS_FACTURA_RECTIFICATIVA;?> de <?php echo $value1->codigorect;?>"></span>
                        <?php } ?>

                     </td>
                     <td><?php echo $value1->nombrecliente;?></td>
                     <td><?php echo $value1->observaciones_resume();?></td>
                     <td class="text-right"><?php echo $fsc->show_precio($value1->total, $value1->coddivisa);?></td>
                     <td class="text-right"><?php echo $value1->fecha;?></td>
                     <td class="text-right"><?php echo $value1->hora;?></td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td></td>
                     <td colspan="6">Ninguna factura encontrada. Pulsa <b>Nueva</b> para crear una.</td>
                  </tr>
                  <?php } ?>

                  <tr>
                     <td colspan="4"></td>
                     <td class="text-right"><b><?php echo $fsc->show_precio($total);?></b></td>
                     <td colspan="2"></td>
                  </tr>
               </table>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-6">
            <div class="btn-group">
               <button class="btn btn-sm btn-default" type="button" onclick="fs_marcar_todo()" title="Marcar todo">
                  <span class="glyphicon glyphicon-check"></span>
               </button>
               <button class="btn btn-sm btn-default" type="button" onclick="fs_marcar_nada()" title="Desmarcar todo">
                  <span class="glyphicon glyphicon-unchecked"></span>
               </button>
            </div>
         </div>
         <div class="col-sm-6 text-right">
            <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
               <span class="glyphicon glyphicon-ok"></span> &nbsp; Marcar como pagadas
            </button>
         </div>
      </div>
   </div>
</form>
<?php }elseif( !$fsc->cliente ){ ?>

<div class="container">
   <div class="row">
      <div class="col-sm-12">
         <div class="alert alert-info">
            Selecciona un cliente para continuar.
         </div>
      </div>
   </div>
</div>
<?php }else{ ?>

<div class="container">
   <div class="row">
      <div class="col-sm-12">
         <div class="alert alert-info">
            Sin resultados. Prueba ajustando las fechas.
         </div>
      </div>
   </div>
</div>
<?php } ?>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>