<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<?php if( $fsc->recibo ){ ?>

<script type="text/javascript">
   function eliminar_pago(id)
   {
      if( confirm("¿Realmente desea eliminar el pago/devolución?") )
      {
         window.location.href = '<?php echo $fsc->url();?>&deletep='+id;
      }
   }
   $(document).ready(function() {
      $("#b_eliminar").click(function(event) {
         event.preventDefault();
         if( confirm("¿Realmente desea eliminar el recibo?") )
         {
            window.location.href = 'index.php?page=ventas_recibos&delete=<?php echo $fsc->recibo->idrecibo;?>';
         }
      });
      document.f_recibo.importe.focus();
   });
</script>

<form name="f_recibo" action="<?php echo $fsc->url();?>" method="post" class="form">
   <div class="container">
      <div class="row">
         <div class="col-sm-12">
            <div class="page-header">
               <h1>
                  <a class="btn btn-xs btn-default" href="index.php?page=ventas_recibos">
                     <span class="glyphicon glyphicon-arrow-left"></span>
                  </a>
                  Recibo de venta
                  <small><?php echo $fsc->recibo->codigo;?></small>
                  <span class="btn-group">
                     <?php if( $fsc->recibo->estado=='Pagado' ){ ?>

                     <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown">
                        <span class="glyphicon glyphicon-ok"></span>
                        <span class="hidden-xs"> &nbsp; <?php echo $fsc->recibo->estado;?></span>
                        <span class="caret"></span>
                     </button>
                     <?php }elseif( $fsc->recibo->estado=='Emitido' ){ ?>

                     <button type="button" class="btn btn-sm btn-warning dropdown-toggle" data-toggle="dropdown">
                        <span class="glyphicon glyphicon-time"></span>
                        <span class="hidden-xs"> &nbsp; <?php echo $fsc->recibo->estado;?></span>
                        <span class="caret"></span>
                     </button>
                     <?php }else{ ?>

                     <button type="button" class="btn btn-sm btn-danger dropdown-toggle" data-toggle="dropdown">
                        <span class="glyphicon glyphicon-exclamation-sign"></span>
                        <span class="hidden-xs"> &nbsp; <?php echo $fsc->recibo->estado;?></span>
                        <span class="caret"></span>
                     </button>
                     <?php } ?>

                     <ul class="dropdown-menu" role="menu">
                        <?php if( $fsc->recibo->estado=='Pagado' ){ ?>

                        <li>
                           <a href="#" data-toggle="modal" data-target="#modal_pago">
                              <span class="glyphicon glyphicon-remove"></span> &nbsp; Devuelto
                           </a>
                        </li>
                        <?php }else{ ?>

                        <li>
                           <a href="#" data-toggle="modal" data-target="#modal_pago">
                              <span class="glyphicon glyphicon-ok"></span> &nbsp; Pagado
                           </a>
                        </li>
                        <?php } ?>

                     </ul>
                  </span>
               </h1>
               <p class="help-block">
                  Este recibo está marcado como <b><?php echo $fsc->recibo->estado;?></b>. Si quieres cambiarlo, haz
                  clic en el botón <b><?php echo $fsc->recibo->estado;?></b>, es un desplegable.
               </p>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-6">
            <a class="btn btn-sm btn-default" href="<?php echo $fsc->url();?>" title="Recargar la página">
               <span class="glyphicon glyphicon-refresh"></span>
            </a>
            <div class="btn-group">
               <a class="btn btn-sm btn-default" href="<?php echo $fsc->factura->url();?>">
                  <span class="glyphicon glyphicon-eye-open"></span> &nbsp; Ver Factura
               </a>
               <a class="btn btn-sm btn-default" href="#" data-toggle="modal" data-target="#modal_imprimir">
                  <span class="glyphicon glyphicon-print"></span>
                  <span class="hidden-xs">&nbsp; Imprimir</span>
               </a>
               <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <?php if( $value1->type=='button' ){ ?>

                  <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>&id=<?php echo $fsc->recibo->idrecibo;?>" class="btn btn-sm btn-default"><?php echo $value1->text;?></a>
                  <?php } ?>

               <?php } ?>

            </div>
         </div>
         <div class="col-sm-6 text-right">
            <div class="btn-group">
               <?php if( $fsc->allow_delete ){ ?>

               <a id="b_eliminar" class="btn btn-sm btn-danger" href="#">
                  <span class="glyphicon glyphicon-trash"></span>
                  <span class="hidden-sm hidden-xs">&nbsp; Eliminar</span>
               </a>
               <?php } ?>

               <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                  <span class="glyphicon glyphicon-floppy-disk"></span>
                  <span class="hidden-xs">&nbsp; Guardar</span>
               </button>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-12">
            <br/>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-3">
            <div class="form-group">
               Factura:
               <div class="form-control"><?php echo $fsc->factura->codigo;?></div>
            </div>
         </div>
         <div class="col-sm-4">
            <div class="form-group">
               Cliente:
               <input type="text" name="nombrecliente" value="<?php echo $fsc->recibo->nombrecliente;?>" class="form-control" readonly=""/>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               Emitido:
               <input type="text" name="emitido" value="<?php echo $fsc->recibo->fecha;?>" class="form-control datepicker" autocomplete="off"/>
            </div>
         </div>
         <div class="col-sm-3">
            <div class="form-group">
               Importe:
               <div class="input-group">
                  <span class="input-group-addon"><?php echo $fsc->simbolo_divisa();?></span>
                  <input type="text" name="importe" value="<?php echo $fsc->recibo->importe;?>" class="form-control" autocomplete="off"/>
               </div>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-2">
            <div class="form-group">
               Vencimiento:
               <input type="text" name="fechav" value="<?php echo $fsc->recibo->fechav;?>" class="form-control datepicker" autocomplete="off" onchange="this.form.submit()"/>
            </div>
         </div>
         <div class="col-sm-6">
            <div class="form-group">
               IBAN:
               <input type="text" name="iban" value="<?php echo $fsc->recibo->iban;?>" class="form-control" autocomplete="off"/>
               <?php if( $fsc->cliente ){ ?>

               <p class="help-block">
                  <a href="<?php echo $fsc->cliente->url();?>">Añade una cuenta bancaria al cliente</a>
                  y su IBAN y SWIFT/BIC se asignarán automáticamente a los recibos.
               </p>
               <?php } ?>

            </div>
         </div>
         <div class="col-sm-4">
            <div class="form-group">
               SWIFT/BIC:
               <input type="text" name="swift" value="<?php echo $fsc->recibo->swift;?>" class="form-control" autocomplete="off"/>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-12">
            <?php if( $fsc->anticipo ){ ?>

            <div class="alert alert-info">
               Esta recibo se corresponde con el pago en la etapa de
               <b><?php echo $fsc->anticipo->fase;?></b> el <b><?php echo $fsc->anticipo->fecha;?></b>:
               <?php echo $fsc->anticipo->nota;?>

            </div>
            <?php } ?>

            <?php if( $fsc->pagos ){ ?>

            <ul class="nav nav-tabs">
               <li role="presentation" class="active">
                  <a href="#">Asientos contables</a>
               </li>
            </ul>
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th width="160">Fecha</th>
                        <th>Tipo</th>
                        <th>Subcuenta</th>
                        <th class="text-right">Acciones</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->pagos; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr<?php if( $value1->tipo=='Pago' ){ ?> class="success"<?php }else{ ?> class="danger"<?php } ?>>
                     <td><div class="form-control"><?php echo $value1->fecha;?></div></td>
                     <td><div class="form-control"><?php echo $value1->tipo;?></div></td>
                     <td><div class="form-control"><a href="<?php echo $value1->subcuenta_url();?>"><?php echo $value1->codsubcuenta;?></a></div></td>
                     <td class="text-right">
                        <div class="btn-group">
                           <?php if( $value1->idasiento ){ ?>

                           <a href="<?php echo $value1->asiento_url();?>" class="btn btn-sm btn-default" title="Ver Asiento">
                              <span class="glyphicon glyphicon-eye-open"></span>
                           </a>
                           <?php } ?>

                           <a class="btn btn-sm btn-danger" title="Eliminar" onclick="eliminar_pago('<?php echo $value1->idpagodevol;?>')">
                              <span class="glyphicon glyphicon-trash"></span>
                           </a>
                        </div>
                     </td>
                  </tr>
                  <?php } ?>

               </table>
            </div>
            <?php } ?>

            <?php if( count($fsc->recibos)>1 ){ ?>

            <h3>Otros recibos de esta factura:</h3>
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th width="190">Código</th>
                        <th class="text-right" width="140">Importe</th>
                        <th class="text-center" width="140">Emitido</th>
                        <th class="text-center" width="140">Vencimiento</th>
                        <th>Estado</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->recibos; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                     <?php if( $value1->idrecibo!=$fsc->recibo->idrecibo ){ ?>

                     <tr class="clickableRow<?php if( $value1->estado=='Pagado' ){ ?> success<?php }elseif( $value1->vencido() ){ ?> danger<?php }else{ ?> warning<?php } ?>" href="<?php echo $value1->url();?>">
                        <td>
                           <div class="form-control">
                              <a href="<?php echo $value1->url();?>" target="_parent" class="cancel_clickable"><?php echo $value1->codigo;?></a>
                           </div>
                        </td>
                        <td class="text-right">
                           <div class="form-control"><?php echo $fsc->show_precio($value1->importe, $value1->coddivisa);?></div>
                        </td>
                        <td><div class="form-control text-center"><?php echo $value1->fecha;?></div></td>
                        <td><div class="form-control text-center"><?php echo $value1->fechav;?></div></td>
                        <td>
                           <div class="input-group">
                              <div class="form-control"><?php echo $value1->estado;?></div>
                              <span class="input-group-addon"><?php echo $value1->fechap;?></span>
                           </div>
                        </td>
                     </tr>
                     <?php } ?>

                  <?php } ?>

               </table>
            </div>
            <?php } ?>

         </div>
      </div>
   </div>
</form>

<form action="<?php echo $fsc->url();?>" method="post" class="form">
   <input type="hidden" name="nuevopago" value="TRUE"/>
   <div class="modal fade" id="modal_pago" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
               <h4 class="modal-title">Cambiar estado</h4>
            </div>
            <?php if( $fsc->recibo->idremesa ){ ?>

            <div class="modal-body">
               <p class="help-block">
                  Este recibo está dentro de la remesa <b><?php echo $fsc->recibo->idremesa;?></b>
                  y sus modificaciones de estado están restringidas.
               </p>
               <a href="index.php?page=remesas&id=<?php echo $fsc->recibo->idremesa;?>" class="btn btn-sm btn-info">
                  <span class="glyphicon glyphicon-eye-open"></span>
                  &nbsp; ver remesa
               </a>
            </div>
            <?php }else{ ?>

            <div class="modal-body">
               <div class="form-group">
                  <select name="tipo" class="form-control">
                     <?php if( $fsc->recibo->estado=='Pagado' ){ ?>

                     <option value="Devolución">Devuelto</option>
                     <?php }else{ ?>

                     <option value="Pago">Pagado</option>
                     <?php } ?>

                  </select>
               </div>
               <div class="form-group">
                  Fecha:
                  <input type="text" name="fecha" value="<?php echo $fsc->today();?>" class="form-control datepicker" autocomplete="off"/>
               </div>
               <?php if( $fsc->empresa->contintegrada ){ ?>

                  <?php if( $fsc->subcuentas_pago ){ ?>

                  <div class="form-group">
                     Subcuenta de caja:
                     <select name="codsubcuenta" class="form-control">
                        <?php $loop_var1=$fsc->subcuentas_pago; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option><?php echo $value1->codsubcuenta;?></option>
                        <?php } ?>

                     </select>
                     <p class="help-block">
                        FacturaScripts busca las cuentas de caja en las
                        <a href="index.php?page=cuentas_especiales">Cuentas especiales</a>.
                     </p>
                     <label>
                        <input type="checkbox" name="generarasiento" value="TRUE" checked=""/>
                        Generar asiento
                     </label>
                  </div>
                  <?php }else{ ?>

                  <div class="alert alert-info">
                     No se ha encontrado ninguna cuenta marcada como
                     <a href="index.php?page=cuentas_especiales">Cuentas de caja</a>.
                     Y por tanto no se generará el asiento de pago.
                  </div>
                  <?php } ?>

               <?php } ?>

            </div>
            <div class="modal-footer">
               <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                  <span class="glyphicon glyphicon-floppy-disk"></span>
                  <span class="hidden-xs">&nbsp; Guardar</span>
               </button>
            </div>
            <?php } ?>

         </div>
      </div>
   </div>
</form>

<div class="modal fade" id="modal_imprimir">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Imprimir <?php  echo FS_FACTURA;?></h4>
            <p class="help-block">
               Más formatos en la <a href="https://www.facturascripts.com/store" target="_blank">tienda de plugins</a>.
            </p>
         </div>
         <div class="modal-body">
         <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <?php if( $value1->type=='pdf' ){ ?>

            <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>&id=<?php echo $fsc->recibo->idrecibo;?>" target="_blank" class="btn btn-block btn-default">
               <span class="glyphicon glyphicon-print"></span> &nbsp; <?php echo $value1->text;?>

            </a>
            <?php } ?>

         <?php } ?>

         </div>
         <div class="modal-footer">
            <a href="index.php?page=admin_empresa#impresion" target="_blank">Opciones de impresión</a>
         </div>
      </div>
   </div>
</div>

<?php }else{ ?>

<div class="thumbnail">
   <img src="view/img/fuuu_face.png" alt="fuuuuu"/>
</div>
<?php } ?>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>