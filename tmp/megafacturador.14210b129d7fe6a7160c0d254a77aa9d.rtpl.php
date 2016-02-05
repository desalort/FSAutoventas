<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<?php if( $fsc->url_recarga ){ ?>

<script type="text/javascript">
   function recargar()
   {
      window.location.href = '<?php echo $fsc->url_recarga;?>';
   }
   $(document).ready(function() {
      setTimeout(recargar, 1000);
   });
</script>
<?php } ?>


<script type="text/javascript">
   function facturar()
   {
      document.f_facturador.procesar.value = 'TRUE';
      document.f_facturador.submit();
   }
</script>

<div class="container">
   <form name="f_facturador" class="form" action="<?php echo $fsc->url();?>" method="post">
      <input type="hidden" name="procesar" value="FALSE"/>
      <div class="row">
         <div class="col-sm-12">
            <div class="page-header">
               <h1>
                  <span class="glyphicon glyphicon-king" aria-hidden="true"></span>
                  MegaFacturador
                  <a href="<?php echo $fsc->url();?>" class="btn btn-xs btn-default" title="Recargar la página">
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
               <p class="help-block">
                  Elige qué es lo que quieres facturar, cómo y pulsa el botón empezar.
               </p>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-4">
            <div class="checkbox">
               <label>
                  <input type="checkbox" name="ventas" value="TRUE"<?php if( $fsc->opciones['ventas'] ){ ?> checked=""<?php } ?>/>
                  <?php  echo FS_ALBARANES;?> de venta pendientes <span class="badge"><?php echo $fsc->total_pendientes_venta();?></span>
               </label>
            </div>
            <div class="checkbox">
               <label>
                  <input type="checkbox" name="compras" value="TRUE"<?php if( $fsc->opciones['compras'] ){ ?> checked=""<?php } ?>/>
                  <?php  echo FS_ALBARANES;?> de compra pendientes <span class="badge"><?php echo $fsc->total_pendientes_compra();?></span>
               </label>
            </div>
         </div>
         <div class="col-sm-3">
            <div class="form-group">
               Serie:
               <select name="codserie" class="form-control" onchange="this.form.submit();">
                  <option value="">Todas</option>
                  <option value="">------</option>
                  <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                     <?php if( $fsc->opciones['codserie']==$value1->codserie ){ ?>

                     <option value="<?php echo $value1->codserie;?>" selected=""><?php echo $value1->descripcion;?></option>
                     <?php }else{ ?>

                     <option value="<?php echo $value1->codserie;?>"><?php echo $value1->descripcion;?></option>
                     <?php } ?>

                  <?php } ?>

               </select>
            </div>
         </div>
         <div class="col-sm-3">
            <div class="form-group">
               Facturar con fecha:
               <select name="fecha" class="form-control">
                  <option value="hoy">Hoy</option>
                  <?php if( $fsc->opciones['fecha']=='albaran' ){ ?>

                  <option value="albaran" selected="">El mismo día del <?php  echo FS_ALBARAN;?></option>
                  <?php }else{ ?>

                  <option value="albaran">El mismo día del <?php  echo FS_ALBARAN;?></option>
                  <?php } ?>

               </select>
            </div>
         </div>
         <div class="col-sm-2 text-right">
            <br/>
            <button class="btn btn-sm btn-primary" type="button" onclick="this.disabled=true;facturar()">
               <span class="glyphicon glyphicon-play"></span> &nbsp; Empezar
            </button>
         </div>
      </div>
   </form>
   <div class="row">
      <div class="col-sm-12">
         <div class="page-header">
            <h2>
               <span class="glyphicon glyphicon-info-sign"></span> Otras opciones
            </h2>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-sm-3">
         <a href="<?php echo $fsc->url();?>&genasientos=TRUE" class="btn btn-default btn-block">
            <span class="glyphicon glyphicon-paperclip"></span>
            &nbsp; Generar asientos
            <?php if( $fsc->numasientos>0 ){ ?>

            <span class="badge"><?php echo $fsc->numasientos;?></span>
            <?php } ?>

         </a>
      </div>
      <div class="col-sm-9">
         <p class="help-block">
            Genera los asientos de las facturas que no tengan. Si tienes activada
            la contabilidad integrada, los asientos contables de las facturas se
            generan automáticamente, pero si por algún motivo tienes facturas sin
            asientos o quieres generarlos ahora, simplemente pulsa el botón.
         </p>
      </div>
   </div>
</div>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>