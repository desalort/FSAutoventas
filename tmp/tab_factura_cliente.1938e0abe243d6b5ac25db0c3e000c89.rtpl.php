<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header2") . ( substr("header2",-1,1) != "/" ? "/" : "" ) . basename("header2") );?>


<?php if( $fsc->pagada_previamente ){ ?>

<br/>
<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12">
         <div class="alert alert-info">
            Esta factura fué marcada como pagada previamente, por eso no se generan
            los recibos.
         </div>
      </div>
   </div>
   <div>
      <div class="col-sm-12">
         <a href="<?php echo $fsc->url();?>&id=<?php echo $fsc->factura->idfactura;?>&regenerar=TRUE" class="btn btn-xs btn-warning">
            <span class="glyphicon glyphicon-duplicate"></span> &nbsp;
            Generar recibos igualmente
         </a>
      </div>
   </div>
</div>
<?php }else{ ?>

<div class="table-responsive">
   <table class="table table-hover">
      <thead>
         <tr>
            <th width="120"></th>
            <th width="190">Código</th>
            <th class="text-right" width="140">Importe</th>
            <th class="text-center" width="140">Emitido</th>
            <th class="text-center" width="140">Vencimiento</th>
            <th>Estado</th>
         </tr>
      </thead>
      <!--<?php $total=$this->var['total']=0;?>-->
      <?php $loop_var1=$fsc->resultados; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <tr class="clickableRow<?php if( $value1->estado=='Pagado' ){ ?> success<?php }elseif( $value1->vencido() ){ ?> danger<?php }else{ ?> warning<?php } ?>" href="<?php echo $value1->url();?>" target="_parent">
         <td class="text-center">
            <?php if( $value1->estado=='Pagado' ){ ?>

            <div class="form-control">
               <span class="glyphicon glyphicon-ok" title="El recibo está pagado"></span>
            </div>
            <?php } ?>

         </td>
         <td>
            <div class="form-control">
               <a href="<?php echo $value1->url();?>" target="_parent" class="cancel_clickable"><?php echo $value1->codigo;?></a>
            </div>
         </td>
         <td class="text-right">
            <div class="form-control"><?php echo $fsc->show_precio($value1->importe, $value1->coddivisa);?></div>
            <!--<?php $total=$this->var['total']=$total+$value1->importe;?>-->
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

      <?php if( abs($fsc->factura->total-$total)>.01 ){ ?>

      <form action="<?php echo $fsc->url();?>" method="post" class="form" target="_parent">
         <tr class="info">
            <td></td>
            <td>
               <input type="hidden" name="idfactura" value="<?php echo $fsc->factura->idfactura;?>"/>
               <div class="form-control">Nuevo</div>
            </td>
            <td>
               <input type="text" name="importe" value="<?php echo round($fsc->factura->total-$total,FS_NF0); ?>" class="form-control text-right" autocomplete="off"/>
            </td>
            <td>
               <input type="text" name="fecha" value="<?php echo $fsc->today();?>" class="form-control datepicker text-center" autocomplete="off"/>
            </td>
            <td>
               <input type="text" name="fechav" value="<?php echo $fsc->vencimiento;?>" class="form-control datepicker text-center" autocomplete="off"/>
            </td>
            <td>
               <div class="input-group">
                  <div class="form-control">Emitido</div>
                  <span class="input-group-btn">
                     <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();" title="Guardar">
                        <span class="glyphicon glyphicon-floppy-disk"></span>
                     </button>
                  </span>
               </div>
            </td>
         </tr>
      </form>
      <?php } ?>

   </table>
</div>
<?php } ?>


<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12">
         <h3>¿Quieres generar varios recibos para una factura?</h3>
         <p class="help-block">
            Puedes hacerlo de forma automática modificando las
            <a href="index.php?page=contabilidad_formas_pago" target="_parent">formas de pago</a>,
            establece los plazos que desées y se generará un recibo para cada plazo.
         </p>
         <p class="help-block">
            También puedes hacerlo de forma manual, modifica el importe del recibo
            y así podrás añadir más.
         </p>
      </div>
   </div>
</div>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer2") . ( substr("footer2",-1,1) != "/" ? "/" : "" ) . basename("footer2") );?>