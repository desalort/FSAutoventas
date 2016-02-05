<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<div class="container">
   <div class="row">
      <div class="col-sm-12">
         <div class="page-header">
            <h1>
               <a class="btn btn-xs btn-default" href="index.php?page=ventas_recibos">
                  <span class="glyphicon glyphicon-arrow-left"></span>
               </a>
               Remesas
               <a class="btn btn-xs btn-default" href="<?php echo $fsc->url();?>" title="Recargar la página">
                  <span class="glyphicon glyphicon-refresh"></span>
               </a>
               <a class="btn btn-xs btn-success" href="#" data-toggle="modal" data-target="#modal_nueva_remesa">
                  <span class="glyphicon glyphicon-plus"></span>
                  <span class="hidden-xs">&nbsp; Nueva</span>
               </a>
            </h1>
            <p class="help-block">
               <span class="glyphicon glyphicon-exclamation-sign"></span>
               <b>Esta sección está en desarrollo</b>. Todavía no se recomienda su uso.
            </p>
            <p class="help-block">
               Desde aquí puedes agrupar los cobros de recibos domiciliados en un banco,
               siempre y cuando los recibos tengan un <b>IBAN</b> asociado.
            </p>
         </div>
         <div class="table-responsive">
            <table class="table table-hover">
               <thead>
                  <tr>
                     <th>ID</th>
                     <th>Fecha</th>
                     <th>Descripción</th>
                     <th class="text-right">Total</th>
                     <th>Estado</th>
                     <th class="text-right">Fecha cargo</th>
                  </tr>
               </thead>
               <?php $loop_var1=$fsc->resultados; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <tr class="clickableRow<?php if( $value1->estado=='Realizada' ){ ?> success<?php }elseif( $value1->estado=='En trámite' ){ ?> warning<?php } ?>" href="<?php echo $value1->url();?>">
                  <td><a href="<?php echo $value1->url();?>"><?php echo $value1->idremesa;?></a></td>
                  <td><?php echo $value1->fecha;?></td>
                  <td><?php echo $value1->descripcion;?></td>
                  <td class="text-right"><?php echo $fsc->show_precio($value1->total, $value1->coddivisa);?></td>
                  <td><?php echo $value1->estado;?></td>
                  <td class="text-right"><?php echo $value1->fechacargo;?></td>
               </tr>
               <?php }else{ ?>

               <tr class="warning">
                  <td colspan="6">Sin resultados.</td>
               </tr>
               <?php } ?>

            </table>
         </div>
      </div>
   </div>
</div>

<form action="<?php echo $fsc->url();?>" method="post" class="form">
   <input type="hidden" name="nueva" value="TRUE"/>
   <div class="modal" id="modal_nueva_remesa" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
               <h4 class="modal-title">
                  Nueva remesa
               </h4>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  Descripción:
                  <input type="text" name="descripcion" class="form-control" required="" autocomplete="off" autofocus=""/>
               </div>
               <div class="form-group">
                  <a href="<?php echo $fsc->forma_pago->url();?>">Forma de pago</a>:
                  <select name="codpago" class="form-control">
                     <?php $loop_var1=$fsc->formas_pago_domiciliadas(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                     <option value="<?php echo $value1->codpago;?>"><?php echo $value1->descripcion;?></option>
                     <?php }else{ ?>

                     <option value="">Ninguna forma de pago está domiciliada</option>
                     <?php } ?>

                  </select>
               </div>
               <div class="form-group">
                  Fecha de cargo:
                  <input type="text" name="fechacargo" value="<?php echo $fsc->today();?>" class="form-control datepicker" autocomplete="off" required=""/>
               </div>
            </div>
            <div class="modal-footer">
               <button class="btn btn-sm btn-primary" type="submit">
                  <span class="glyphicon glyphicon-floppy-disk"></span>
                  <span class="hidden-xs">&nbsp; Guardar</span>
               </button>
            </div>
         </div>
      </div>
   </div>
</form>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>