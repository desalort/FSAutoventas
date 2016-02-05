<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header2") . ( substr("header2",-1,1) != "/" ? "/" : "" ) . basename("header2") );?>


<?php if( $fsc->bloquear ){ ?>

<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12">
         <div class="table-responsive">
            <table class="table table-hover">
               <thead>
                  <tr>
                     <th class="text-left" width="190">Documento</th>
                     <th class="text-left" width="140">Fecha</th>
                     <th class="text-right" width="140">Importe</th>
                     <th class="text-left">Nota</th>
                  </tr>
               </thead>
               <?php $loop_var1=$fsc->pagos; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <tr class="clickableRow<?php if( $value1->pendiente==0 ){ ?> success<?php } ?>" href="<?php echo $value1->url();?>">
                  <td><?php echo $value1->fase;?></td>
                  <td><?php echo $value1->fecha;?></div></td>
                  <td class="text-right"><?php echo $fsc->show_precio($value1->importe);?></td>
                  <td><?php echo $value1->nota;?></td>
               </tr>
               <?php }else{ ?>

               <tr class="warning">
                  <td colspan="4">Ningún pago registrado.</td>
               </tr>
               <?php } ?>

            </table>
         </div>
         <div class="alert alert-warning">Los pagos de este documento se encuentran bloqueados.</div>
      </div>
   </div>
</div>
<?php }else{ ?>

<script type="text/javascript">
   function eliminar_pago(id)
   {
      if( confirm("¿Realmente desea eliminar el pago?") )
         window.location.href = '<?php echo $fsc->url();?>&delete='+id;
   }
</script>

<div class="table-responsive">
   <table class="table table-hover">
      <thead>
         <tr>
            <th class="text-left" width="190">Documento</th>
            <th class="text-center" width="130">Fecha</th>
            <th class="text-right" width="140">Importe</th>
            <th class="text-left">Nota</th>
            <th width="120"></th>
         </tr>
      </thead>
      <?php $loop_var1=$fsc->pagos; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         <form class="form" action="<?php echo $fsc->url();?>" method="post">
            <tr<?php if( $value1->pendiente==0 ){ ?> class="success"<?php } ?>>
               <td><div class="form-control"><?php echo $value1->fase;?></div></td>
               <td>
                  <input type="hidden" name="idpago" value="<?php echo $value1->id;?>"/>
                  <input type="text" name="fecha" class="form-control datepicker text-center" value="<?php echo $value1->fecha;?>" autocomplete="off"/>
               </td>
               <td>
                  <input type="text" name="importe" class="form-control text-right" value="<?php echo $value1->importe;?>" autocomplete="off"/>
               </td>
               <td>
                  <input type="text" name="nota" class="form-control" value="<?php echo $value1->nota;?>" autocomplete="off"/>
               </td>
               <td class="text-right">
                  <div class="btn-group">
                     <?php if( $fsc->allow_delete ){ ?>

                     <a class="btn btn-sm btn-danger" title="Eliminar" onclick="eliminar_pago('<?php echo $value1->id;?>')">
                        <span class="glyphicon glyphicon-trash"></span>
                     </a>
                     <?php } ?>

                     <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();" title="Guardar">
                        <span class="glyphicon glyphicon-floppy-disk"></span>
                     </button>
                  </div>
               </td>
            </tr>
         </form>
      <?php }else{ ?>

      <tr class="warning">
         <td colspan="5">Ningún pago encontrado.</td>
      </tr>
      <?php } ?>

   </table>
</div>

<?php if( !$fsc->pagado ){ ?>

<form class="form" action="<?php echo $fsc->url();?>" method="post">
   <div class="container">
      <div class="row">
         <div class="col-sm-12">
            <h3>
               <span class="glyphicon glyphicon-plus-sign"></span>
               Nuevo pago:
            </h3>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-4">
            <div class="form-group">
               <input type="text" name="fecha" class="form-control datepicker" value="<?php echo $fsc->today();?>" autocomplete="off"/>
            </div>
         </div>
         <div class="col-sm-4">
            <div class="form-group">
               <div class="input-group">
                  <input type="text" name="importe" class="form-control text-right" value="<?php echo $fsc->pendiente;?>" autocomplete="off"/>
                  <span class="input-group-addon"><?php echo $fsc->simbolo_divisa($fsc->coddivisa);?></span>
               </div>
            </div>
         </div>
         <div class="col-sm-4 text-right">
            <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
               <span class="glyphicon glyphicon-floppy-disk"></span>
               <span class="hidden-xs">&nbsp; Guardar</span>
            </button>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-12">
            <div class="form-group">
               <textarea name="nota" class="form-control">Anticipo...</textarea>
            </div>
         </div>
      </div>
   </div>
</form>
<?php } ?>


<?php } ?>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer2") . ( substr("footer2",-1,1) != "/" ? "/" : "" ) . basename("footer2") );?>