<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<div class="container">
   <div class="row">
      <div class="col-sm-12">
         <div class="page-header">
            <h1>
               <span class="glyphicon glyphicon-usd"></span>
               Tarifas
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
            <div class="help-block">
               Define descuentos, márgenes o precios específicos de venta para <b>grupos de clientes</b>.
            </div>
         </div>
         <div class="table-responsive">
            <table class="table table-hover">
               <thead>
                  <tr>
                     <th class="text-left">Código + Nombre</th>
                     <th class="text-left">Aplicar</th>
                     <th class="text-right">Grupos de clientes</th>
                  </tr>
               </thead>
               <?php $loop_var1=$fsc->tarifa->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <tr class="clickableRow" href="<?php echo $value1->url();?>">
                  <td><a href="<?php echo $value1->url();?>"><?php echo $value1->codtarifa;?></a> <?php echo $value1->nombre;?></td>
                  <td><?php echo $value1->diff();?></td>
                  <td class="text-right">
                     <?php $loop_var2=$fsc->get_grupos_tar($value1->codtarifa); $counter2=-1; if($loop_var2) foreach( $loop_var2 as $key2 => $value2 ){ $counter2++; ?>

                     <?php echo $value2->nombre;?>,
                     <?php }else{ ?>

                     -
                     <?php } ?>

                  </td>
               </tr>
               <?php }else{ ?>

               <tr class="warning">
                  <td colspan="5">No hay ninguna tarifa definida.</td>
               </tr>
               <?php } ?>

            </table>
         </div>
         <br/>
         <form name="f_nueva_tarifa" action="<?php echo $fsc->url();?>" method="post" class="form">
            <div class="panel panel-info">
               <div class="panel-heading">
                  <h3 class="panel-title">Nueva tarifa</h3>
               </div>
               <div class="panel-body">
                  <div class="container-fluid">
                     <div class="row">
                        <div class="col-sm-2">
                           <div class="form-group">
                              Código
                              <input class="form-control" type="text" name="codtarifa" value="<?php echo $fsc->tarifa->get_new_codigo();?>" maxlength="6" autocomplete="off"/>
                           </div>
                        </div>
                        <div class="col-sm-3">
                           <div class="form-group">
                              Nombre
                              <input class="form-control" type="text" name="nombre" maxlength="50" placeholder="Nueva Tarifa" autocomplete="off"/>
                           </div>
                        </div>
                        <div class="col-sm-3">
                           <div class="form-group">
                              Aplicar
                              <select name="aplicar_a" class="form-control">
                                 <option value="pvp">Precio de venta - X% - Y</option>
                                 <option value="coste">Precio de coste + X% + Y</option>
                              </select>
                           </div>
                        </div>
                        <div class="col-sm-2">
                           <div class="form-group">
                              X%
                              <input class="form-control" type="number" step="any" name="dtopor" value="0" autocomplete="off"/>
                           </div>
                        </div>
                        <div class="col-sm-1">
                           <div class="form-group">
                              Y
                              <input class="form-control" type="number" step="any" name="inclineal" value="0" autocomplete="off"/>
                           </div>
                        </div>
                        <div class="col-sm-1">
                           <br/>
                           <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();" title="Guardar">
                              <span class="glyphicon glyphicon-floppy-disk"></span>
                           </button>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-sm-5">
                           <div class="checkbox-inline">
                              <label>
                                 <input type="checkbox" name="mincoste" value="TRUE" checked=""/>
                                 mínimo = precio de coste
                              </label>
                           </div>
                           <div class="checkbox-inline">
                              <label>
                                 <input type="checkbox" name="maxpvp" value="TRUE" checked=""/>
                                 máximo = precio de venta
                              </label>
                           </div>
                        </div>
                        <div class="col-sm-7">
                           <p class="help-block">
                              ¿Buscas <b>Precio de venta + X% + Y</b>? ¿O <b>Precio de coste - X% - Y</b>?
                              Puedes usar números negativos, simplemente pon un <b>-</b> delante.
                              <mark>1 - -1 = 1 + 1</mark>
                           </p>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </form>
         <div class="panel panel-warning">
            <div class="panel-heading">
               <h3 class="panel-title">Importar tarifas antiguas</h3>
            </div>
            <div class="panel-body">
               <p>Si lo deseas puedes importar las tarifas antiguas, las de facturacion_base.</p>
               <a class="btn btn-xs btn-default" href="<?php echo $fsc->url();?>&importar=TRUE">
                  <span class="glyphicon glyphicon-import"></span>
                  <span class="hidden-xs">&nbsp; Importar</span>
               </a>
            </div>
         </div>
      </div>
   </div>
</div>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>