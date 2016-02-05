<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<?php if( $fsc->remesa ){ ?>

<script type="text/javascript">
   function fs_marcar_todo()
   {
      $("#f_add_recibos input[name='addrecibo[]']").prop('checked', true);
   }
   function fs_marcar_nada()
   {
      $("#f_add_recibos input[name='addrecibo[]']").prop('checked', false);
   }
   $(document).ready(function() {
      $("#b_eliminar").click(function(event) {
         event.preventDefault();
         if( confirm("¿Realmente desea eliminar la remesa?") )
         {
            window.location.href = '<?php echo $fsc->url();?>&delete=<?php echo $fsc->remesa->idremesa;?>';
         }
      });
   });
</script>

<div class="container">
   <div class="row">
      <div class="col-sm-12">
         <div class="page-header">
            <h1>
               <a class="btn btn-xs btn-default" href="<?php echo $fsc->url();?>">
                  <span class="glyphicon glyphicon-arrow-left"></span>
               </a>
               Remesa
               <small><?php echo $fsc->remesa->idremesa;?></small>
               <a class="btn btn-xs btn-default" href="<?php echo $fsc->remesa->url();?>" title="Recargar la página">
                  <span class="glyphicon glyphicon-refresh"></span>
               </a>
               <?php if( $fsc->remesa->estado=='Realizada' ){ ?>

               <a class="btn btn-xs btn-success" href="<?php echo $fsc->remesa->url();?>&pagar=TRUE" title="Marcar todos los recibos como pagados">
                  <span class="glyphicon glyphicon-check"></span>
                  <span class="hidden-xs hidden-sm">&nbsp; Marcar como pagados</span>
               </a>
               <?php } ?>

            </h1>
            <p class="help-block">
               Esta remesa de <b><?php echo $fsc->show_precio($fsc->remesa->total);?></b>
               <?php if( $fsc->cuentab_s ){ ?>

               se cargará a la cuenta <b><?php echo $fsc->cuentab_s->descripcion;?></b>
               con IBAN: <?php echo $fsc->remesa->iban;?>

               <?php }else{ ?>

               se cargará a la cuenta con IBAN: <?php echo $fsc->remesa->iban;?>

               y SWIFT/BIC: <?php echo $fsc->remesa->swift;?>

               <?php } ?>

            </p>
            <p class="help-block">
               <span class="glyphicon glyphicon-question-sign"></span>
               Añade los recibos que quieras y pulsa el botón decargar para obtener el
               <b>archivo xml</b> necesario para luego añadirlo en la sección remesas
               de tu banco.
               La remesa pasará a estado <b>en trámite</b>. Una vez tengas constancia
               del cobro, cambia el estado de la remesa a <b>realizada</b> y te
               aparecerá el botón <b>marcar todos los recibos como pagados</b>.
            </p>
         </div>
      </div>
   </div>
   <form action="<?php echo $fsc->remesa->url();?>" method="post" class="form">
      <div class="row">
         <div class="col-sm-4">
            <div class="form-group">
               Descripción:
               <input class="form-control" type="text" name="descripcion" value="<?php echo $fsc->remesa->descripcion;?>" autocomplete="off"/>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               Fecha:
               <div class="input-group">
                  <input class="form-control datepicker" type="text" name="fecha" value="<?php echo $fsc->remesa->fecha;?>" autocomplete="off"/>
                  <span class="input-group-addon">
                     <span class="glyphicon glyphicon-calendar"></span>
                  </span>
               </div>
            </div>
         </div>
         <div class="col-sm-3">
            <div class="form-group">
               Fecha cargo:
               <div class="input-group">
                  <input class="form-control datepicker" type="text" name="fechacargo" value="<?php echo $fsc->remesa->fechacargo;?>" autocomplete="off"/>
                  <span class="input-group-addon">
                     <span class="glyphicon glyphicon-calendar"></span>
                  </span>
               </div>
            </div>
         </div>
         <div class="col-sm-3">
            <div class="form-group<?php if( !$fsc->remesa->editable() ){ ?> has-warning<?php } ?>">
               Estado:
               <select name="estado" class="form-control">
                  <option value="Preparada"<?php if( $fsc->remesa->estado=='Preparada' ){ ?> selected=""<?php } ?>>Preparada</option>
                  <option value="En trámite"<?php if( $fsc->remesa->estado=='En trámite' ){ ?> selected=""<?php } ?>>En trámite</option>
                  <option value="Revisar"<?php if( $fsc->remesa->estado=='Revisar' ){ ?> selected=""<?php } ?>>Revisar</option>
                  <option value="Realizada"<?php if( $fsc->remesa->estado=='Realizada' ){ ?> selected=""<?php } ?>>Realizada</option>
               </select>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-6">
            <div class="btn-group">
               <a class="btn btn-sm btn-primary" href="<?php echo $fsc->remesa->url();?>&download=TRUE" title="Descargar remesa">
                  <span class="glyphicon glyphicon-download-alt"></span>
                  <span class="hidden-xs hidden-sm">&nbsp; Descargar</span>
               </a>
               <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <?php if( $value1->type=='button' ){ ?>

                  <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" class="btn btn-xs btn-default"><?php echo $value1->text;?></a>
                  <?php } ?>

               <?php } ?>

            </div>
         </div>
         <div class="col-sm-6 text-right">
            <div class="btn-group">
               <?php if( $fsc->allow_delete ){ ?>

               <a id="b_eliminar" class="btn btn-sm btn-danger" href="#" title="Eliminar remesa">
                  <span class="glyphicon glyphicon-trash"></span>
                  <span class="hidden-xs hidden-sm">&nbsp; Eliminar</span>
               </a>
               <?php } ?>

               <button class="btn btn-sm btn-primary" type="button" onclick="this.disabled=true;this.form.submit();">
                  <span class="glyphicon glyphicon-floppy-disk"></span>
                  <span class="hidden-xs hidden-sm">&nbsp; Guardar</span>
               </button>
            </div>
         </div>
      </div>
   </form>
   <div class="row">
      <div class="col-sm-12">
         <br/>
         <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
               <a href="#incluidos" aria-controls="idcluidos" role="tab" data-toggle="tab">Incluidos</a>
            </li>
            <li role="presentation">
               <a href="#anyadir" aria-controls="anyadir" role="tab" data-toggle="tab">Añadir</a>
            </li>
         </ul>
         <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="incluidos">
               <div class="table-responsive">
                  <table class="table table-hover">
                     <thead>
                        <tr>
                           <th class="text-left">Código</th>
                           <th class="text-left">Cliente</th>
                           <th class="text-left">Estado</th>
                           <th class="text-left">Fecha</th>
                           <th class="text-left">Fecha Vencimiento</th>
                           <th class="text-right">Importe</th>
                           <th></th>
                        </tr>
                     </thead>
                     <?php $loop_var1=$fsc->resultados; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                     <tr<?php if( $value1->vencido() ){ ?> class="danger"<?php } ?>>
                        <td><a href="<?php echo $value1->url();?>"><?php echo $value1->codigo;?></a></td>
                        <td><?php echo $value1->nombrecliente;?></td>
                        <td><?php echo $value1->estado;?></td>
                        <td><?php echo $value1->fecha;?></td>
                        <td><?php echo $value1->fechav;?></td>
                        <td class="text-right"><?php echo $fsc->show_precio($value1->importe, $value1->coddivisa);?></td>
                        <td class="text-right">
                           <?php if( $fsc->remesa->editable() AND $fsc->allow_delete ){ ?>

                           <a class="btn btn-xs btn-danger" href="<?php echo $fsc->remesa->url();?>&sacar=<?php echo $value1->idrecibo;?>" title="Quitar recibo">
                              <span class="glyphicon glyphicon-remove"></span>
                           </a>
                           <?php } ?>

                        </td>
                     </tr>
                     <?php }else{ ?>

                     <tr class="warning">
                        <td colspan="7">No hay ningún recibo incluido en esta remesa.</td>
                     </tr>
                     <?php } ?>

                  </table>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="anyadir">
               <?php if( $fsc->remesa->editable() ){ ?>

               <form id="f_add_recibos" action="<?php echo $fsc->remesa->url();?>" method="post" class="form">
                  <div class="table-responsive">
                     <table class="table table-hover">
                        <thead>
                           <tr>
                              <th></th>
                              <th class="text-left">Código</th>
                              <th class="text-left">Cliente</th>
                              <th class="text-left">IBAN</th>
                              <th class="text-left">Fecha</th>
                              <th class="text-left">Fecha Vencimiento</th>
                              <th class="text-right">Importe</th>
                           </tr>
                        </thead>
                        <?php $loop_var1=$fsc->recibos_disponibles(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <tr>
                           <td>
                              <?php if( $value1->iban ){ ?>

                              <input type="checkbox" name="addrecibo[]" value="<?php echo $value1->idrecibo;?>" checked=""/>
                              <?php }else{ ?>

                              <span class="glyphicon glyphicon-exclamation-sign" title="IBAN desconocido"></span>
                              <?php } ?>

                           </td>
                           <td><a href="<?php echo $value1->url();?>"><?php echo $value1->codigo;?></a></td>
                           <td><?php echo $value1->nombrecliente;?></td>
                           <td<?php if( !$value1->iban ){ ?> class="danger"<?php } ?>>
                              <?php if( $value1->iban ){ ?><?php echo $value1->iban;?><?php }else{ ?><a href="<?php echo $value1->url();?>">desconocido</a><?php } ?>

                           </td>
                           <td><?php echo $value1->fecha;?></td>
                           <td><?php echo $value1->fechav;?></td>
                           <td class="text-right"><?php echo $fsc->show_precio($value1->importe, $value1->coddivisa);?></td>
                        </tr>
                        <?php }else{ ?>

                        <tr class="warning">
                           <td colspan="7">Ningún recibo disponible.</td>
                        </tr>
                        <?php } ?>

                     </table>
                  </div>
                  <p class="help-block">
                     <span class="glyphicon glyphicon-question-sign"></span>
                     Solamente puedes añadir recibos no pagados, con fecha de vencimiento
                     anterior a la fecha de cargo de la remesa y que tengan un IBAN asociado.
                     No se puede dar orden de cobro al banco sobre un número de cuenta desconocido.
                  </p>
                  <div class="text-right">
                     <div class="pull-left">
                        <div class="btn-group">
                           <button class="btn btn-sm btn-default" type="button" onclick="fs_marcar_todo()" title="Marcar todo">
                              <span class="glyphicon glyphicon-check"></span>
                           </button>
                           <button class="btn btn-sm btn-default" type="button" onclick="fs_marcar_nada()" title="Desmarcar todo">
                              <span class="glyphicon glyphicon-unchecked"></span>
                           </button>
                        </div>
                     </div>
                     <button class="btn btn-sm btn-success" type="button" onclick="this.disabled=true;this.form.submit();">
                        <span class="glyphicon glyphicon-plus"></span>
                        <span class="hidden-xs hidden-sm">&nbsp; Añadir</span>
                     </button>
                  </div>
               </form>
               <?php }else{ ?>

               <div class='alert alert-warning'>
                  <span class="glyphicon glyphicon-exclamation-sign"></span>
                  Solamente se pueden hacer cambios en remesas <b>preparadas</b>.
               </div>
               <?php } ?>

            </div>
         </div>
      </div>
   </div>
</div>
<?php } ?>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>