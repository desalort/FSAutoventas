<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript">
   function eliminar_fp(cod)
   {
      if( confirm("¿Realmente desea eliminar la forma de pago "+cod+"?") )
         window.location.href = '<?php echo $fsc->url();?>&delete='+cod;
   }
</script>

<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12">
         <div class="page-header">
            <h1>
               Formas de pago
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
            <?php if( $fsc->button_plazos ){ ?>

            <p class="help-block">
               Usa el botón &nbsp;
               <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> &nbsp;
               para definir los plazos de pago de cada forma de pago.
            </p>
            <?php }else{ ?>

            <p class="help-block">
               <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> &nbsp;
               Puedes configurar plazos de pago con el
               <a href="https://www.facturascripts.com/store/producto/plugin-facturacion_premium/" target="_blank">plugin tesorería</a>,
               que se vende junto con facturación premium.
            </p>
            <?php } ?>

            <p class="help-block">
               <span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span> &nbsp;
               Además puedes gestionar las cuentas bancarias de la empresa desde
               <a href="index.php?page=admin_empresa#cuentasb">Admin &gt; Empresa &gt; Cuentas bancarias</a>.
            </p>
         </div>
      </div>
   </div>
</div>

<div class="table-responsive">
   <table class="table table-hover">
      <thead>
         <tr>
            <th width="150" class="text-left">Código</th>
            <th class="text-left">Descripción</th>
            <th class="text-left">Generar facturas</th>
            <th class="text-left">Vencimiento</th>
            <?php if( $fsc->button_plazos ){ ?>

            <th width="80"></th>
            <?php } ?>

            <th class="text-left">
               <a href="index.php?page=admin_empresa#cuentasb" target="_blank">Cuenta bancaria</a>
            </th>
            <th class="text-center">Domiciliado</th>
            <th class="text-right" width="120">Acciones</th>
         </tr>
      </thead>
      <?php $loop_var1=$fsc->forma_pago->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <form action="<?php echo $fsc->url();?>" method="post" class="form" role="form">
         <tr<?php if( $value1->genrecibos=='Pagados' ){ ?> class="success"<?php } ?>>
            <td>
               <input type="hidden" name="codpago" value="<?php echo $value1->codpago;?>"/>
               <div class="form-control"><?php echo $value1->codpago;?></div>
            </td>
            <td><input class="form-control" type="text" name="descripcion" value="<?php echo $value1->descripcion;?>" autocomplete="off"/></td>
            <td>
               <select name="genrecibos" class="form-control">
                  <option value="Emitidos"<?php if( $value1->genrecibos=='Emitidos' ){ ?> selected=""<?php } ?>>Sin pagar</option>
                  <option value="Pagados"<?php if( $value1->genrecibos=='Pagados' ){ ?> selected=""<?php } ?>>Pagadas</option>
               </select>
            </td>
            <td>
               <select name="vencimiento" class="form-control">
               <?php $loop_var2=$fsc->vencimientos(); $counter2=-1; if($loop_var2) foreach( $loop_var2 as $key2 => $value2 ){ $counter2++; ?>

                  <?php if( $value1->vencimiento==$key2 ){ ?>

                  <option value="<?php echo $key2;?>" selected=""><?php echo $value2;?></option>
                  <?php }else{ ?>

                  <option value="<?php echo $key2;?>"><?php echo $value2;?></option>
                  <?php } ?>

               <?php } ?>

               </select>
            </td>
            <?php if( $fsc->button_plazos ){ ?>

               <?php if( $value1->genrecibos=='Emitidos' ){ ?>

               <td>
                  <a href="index.php?page=<?php echo $fsc->button_plazos;?>&cod=<?php echo $value1->codpago;?>" class="btn btn-sm btn-default" title="Plazos de pago">
                     <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                  </a>
               </td>
               <?php }else{ ?>

               <td></td>
               <?php } ?>

            <?php } ?>

            <td>
               <select name="codcuenta" class="form-control">
                  <option value="">Ninguna</option>
                  <option value="">------</option>
                  <?php $loop_var2=$fsc->cuentas_banco; $counter2=-1; if($loop_var2) foreach( $loop_var2 as $key2 => $value2 ){ $counter2++; ?>

                     <?php if( $value1->codcuenta==$value2->codcuenta ){ ?>

                     <option value="<?php echo $value2->codcuenta;?>" selected=""><?php echo $value2->descripcion;?></option>
                     <?php }else{ ?>

                     <option value="<?php echo $value2->codcuenta;?>"><?php echo $value2->descripcion;?></option>
                     <?php } ?>

                  <?php } ?>

               </select>
            </td>
            <td class="text-center">
               <div class="checkbox">
                  <label title="¿Domiciliado?">
                     <input type="checkbox" name="domiciliado" value="TRUE"<?php if( $value1->domiciliado ){ ?> checked="checked"<?php } ?>/>
                  </label>
               </div>
            </td>
            <td class="text-right">
               <div class="btn-group">
                  <?php if( $fsc->allow_delete ){ ?>

                     <?php if( $value1->codpago==$fsc->empresa->codpago ){ ?>

                     <a href="#" class="btn btn-sm btn-warning" title="Bloqueado" onclick="alert('No puedes eliminar la forma de pago predeterminada.')">
                        <span class="glyphicon glyphicon-lock"></span>
                     </a>
                     <?php }else{ ?>

                     <a href="#" class="btn btn-sm btn-danger" onclick="eliminar_fp('<?php echo $value1->codpago;?>')" title="Eliminar">
                        <span class="glyphicon glyphicon-trash"></span>
                     </a>
                     <?php } ?>

                  <?php } ?>

                  <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();" title="Guardar">
                     <span class="glyphicon glyphicon-floppy-disk"></span>
                  </button>
               </div>
            </td>
         </tr>
      </form>
      <?php } ?>

      <form action="<?php echo $fsc->url();?>" method="post" class="form" role="form">
         <tr class="info">
            <td>
               <input class="form-control" type="text" name="codpago" maxlength="10" autocomplete="off" placeholder="Nuevo código"/>
            </td>
            <td>
               <input class="form-control" type="text" name="descripcion" autocomplete="off" placeholder="Nueva descripción"/>
            </td>
            <td>
               <select name="genrecibos" class="form-control">
                  <option value="Emitidos">Sin pagar</option>
                  <option value="Pagados">Pagadas</option>
               </select>
            </td>
            <td class="text-center">
               <select name="vencimiento" class="form-control">
                  <?php $loop_var1=$fsc->vencimientos(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <option value="<?php echo $key1;?>"><?php echo $value1;?></option>
                  <?php } ?>

               </select>
            </td>
            <?php if( $fsc->button_plazos ){ ?>

            <td></td>
            <?php } ?>

            <td>
               <select name="codcuenta" class="form-control">
                  <option value="">Ninguna</option>
                  <option value="">------</option>
                  <?php $loop_var1=$fsc->cuentas_banco; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <option value="<?php echo $value1->codcuenta;?>"><?php echo $value1->descripcion;?></option>
                  <?php } ?>

               </select>
            </td>
            <td class="text-center">
               <div class="checkbox">
                  <label title="¿Domiciliado?">
                     <input type="checkbox" name="domiciliado" value="TRUE"/>
                  </label>
               </div>
            </td>
            <td class="text-right">
               <div class="btn-group">
                  <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();" title="Guardar">
                     <span class="glyphicon glyphicon-floppy-disk"></span>
                  </button>
               </div>
            </td>
         </tr>
      </form>
   </table>
</div>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>