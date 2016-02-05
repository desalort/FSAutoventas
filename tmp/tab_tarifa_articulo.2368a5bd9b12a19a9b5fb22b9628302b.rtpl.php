<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header2") . ( substr("header2",-1,1) != "/" ? "/" : "" ) . basename("header2") );?>


<script type="text/javascript">
   function cambiar_pvp()
   {
      var coste = parseFloat( $("#coste").val() );
      var iva = parseFloat( $("#iva").val() );
      var pvp = parseFloat( $("#pvp").val() );
      $("#pvpi").val( pvp * (100 + iva)/100 );
      
      var margen = 0;
      if(coste > 0)
      {
         margen = (pvp*100)/coste - 100;
      }
      
      $("#margen").val(margen);
   }
   function cambiar_pvpi()
   {
      var coste = parseFloat( $("#coste").val() );
      var iva = parseFloat( $("#iva").val() );
      var pvpi = parseFloat( $("#pvpi").val() );
      
      var pvp = (100 * pvpi) / (100 + iva);
      $("#pvp").val(pvp);
      
      var margen = 0;
      if(coste > 0)
      {
         margen = (pvp*100)/coste - 100;
      }
      
      $("#margen").val(margen);
   }
   function cambiar_margen()
   {
      var iva = parseFloat( $("#iva").val() );
      var coste = parseFloat( $("#coste").val() );
      var margen = parseFloat( $("#margen").val() );
      
      if( !isNaN(margen) && isFinite(margen) )
      {
         var pvp = coste*(100 + margen)/100;
         $("#pvp").val(pvp);
         $("#pvpi").val( pvp * (100 + iva)/100 );
      }
   }
   function calcular_margen()
   {
      var coste = parseFloat( $("#coste").val() );
      var pvp = parseFloat( $("#pvp").val() );
      
      var margen = 0;
      if(coste > 0)
      {
         margen = (pvp*100)/coste - 100;
      }
      
      $("#margen").val(margen);
   }
</script>

<form action="<?php echo $fsc->url();?>#precios" method="post" class="form">
   <input type="hidden" name="referencia" value="<?php echo $fsc->articulo->referencia;?>"/>
   <input type="hidden" id="iva" name="iva" value="<?php echo $fsc->articulo->get_iva();?>"/>
   <div class="container-fluid" style="margin-top: 10px;">
      <div class="row">
         <div class="col-md-4">
            <div class="form-group">
               Precio:
               <div class="input-group">
                  <span class="input-group-addon"><?php echo $fsc->simbolo_divisa();?></span>
                  <input type="text" class="form-control" id="pvp" name="pvp" value="<?php echo $fsc->articulo->pvp;?>" autocomplete="off" onkeyup="cambiar_pvp()" onclick="this.select()"/>
               </div>
               <p class="help-block">
                  El precio se guarda con <b><?php  echo FS_NF0_ART;?> decimales</b>.
                  Puedes cambiarlo desde el <a href="index.php?page=admin_home#avanzado">panel de control</a>.
               </p>
            </div>
         </div>
         <div class="col-md-4">
            <div class="form-group">
               <a href="<?php echo $fsc->impuesto->url();?>"><?php  echo FS_IVA;?></a>:
               <select class="form-control" name="codimpuesto">
               <?php $loop_var1=$fsc->impuesto->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <?php if( $value1->codimpuesto==$fsc->articulo->codimpuesto ){ ?>

                  <option value="<?php echo $value1->codimpuesto;?>" selected="selected"><?php echo $value1->descripcion;?></option>
                  <?php }else{ ?>

                  <option value="<?php echo $value1->codimpuesto;?>"><?php echo $value1->descripcion;?></option>
                  <?php } ?>

               <?php } ?>

               </select>
            </div>
         </div>
         <div class="col-md-4">
            <div class="form-group">
               Precio+<?php  echo FS_IVA;?>:
               <div class="input-group">
                  <span class="input-group-addon"><?php echo $fsc->simbolo_divisa();?></span>
                  <input type="text" class="form-control" id="pvpi" name="pvpiva" value="<?php echo $fsc->articulo->pvp_iva();?>" autocomplete="off" onkeyup="cambiar_pvpi()" onclick="this.select()"/>
               </div>
               <p class="help-block">Último cambio de precio: <?php echo $fsc->articulo->factualizado;?></p>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-md-4">
            <div class="form-group">
               Precio de Coste:
               <?php if( $fsc->articulo->secompra AND FS_COST_IS_AVERAGE ){ ?>

               <input type="text" name="coste" id="coste" class="form-control" value="<?php echo $fsc->articulo->preciocoste();?>" disabled="disabled">
               <?php }else{ ?>

               <input type="text" name="preciocoste" id="coste" class="form-control" value="<?php echo $fsc->articulo->preciocoste();?>" onclick="this.select()" autocomplete="off">
               <?php } ?>

               <p class="help-block">
                  Puede cambiar la configuración de precio de coste desde
                  la configuración del <a href="index.php?page=admin_almacenes" target="_parent">almacén</a>.
               </p>
            </div>
         </div>
         <div class="col-md-4">
            <div class="form-group">
               Margen sobre precio de coste
               <div class="input-group">
                  <span class="input-group-addon">%</span>
                  <input type="text" class="form-control" id="margen" name="margen" value="0" autocomplete="off" onkeyup="cambiar_margen()" onclick="this.select()"/>
               </div>
               <p class="help-block">Sirve para calcular el PVP, pero no se almacena (todavía).</p>
            </div>
         </div>
      </div>
   </div>
   <div>
      <ul class="nav nav-tabs" role="tablist">
         <li role="presentation" class="active">
            <a href="#tarifas" aria-controls="tarifas" role="tab" data-toggle="tab">Tarifas</a>
         </li>
         <li role="presentation">
            <a href="#proveedores" aria-controls="proveedores" role="tab" data-toggle="tab">Proveedores</a>
         </li>
      </ul>
      <div class="tab-content">
         <div role="tabpanel" class="tab-pane active" id="tarifas">
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th colspan="2" class="text-left">Tarifa</th>
                        <th class="text-left">Aplicar</th>
                        <th class="text-right" width="150">Nuevo Precio</th>
                        <th class="text-right" width="170">Nuevo Precio+<?php  echo FS_IVA;?></th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->get_tarifas(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr>
                     <td width="120">
                        <div class="form-control">
                           <a href="<?php echo $value1->tarifa_url;?>" target="_blank"><?php echo $value1->codtarifa;?></a>
                        </div>
                     </td>
                     <td><div class="form-control"><?php echo $value1->tarifa_nombre;?></div></td>
                     <td><div class="form-control"><?php echo $value1->tarifa_diff;?></div></td>
                     <td>
                        <input type="hidden" name="codtarifa_<?php echo $counter1;?>" value="<?php echo $value1->codtarifa;?>"/>
                        <input type="text" name="pvp_<?php echo $counter1;?>" class="form-control text-right" placeholder="<?php echo $fsc->show_numero($value1->pvp*(100-$value1->dtopor)/100, FS_NF0_ART);?>" autocomplete="off"/>
                     </td>
                     <td>
                        <input type="text" name="pvpi_<?php echo $counter1;?>" class="form-control text-right" placeholder="<?php echo $fsc->show_numero($value1->pvp*(100-$value1->dtopor)/100*(100+$value1->get_iva())/100, FS_NF0_ART);?>" autocomplete="off"/>
                     </td>
                  </tr>
                  <?php } ?>

                  <?php if( $fsc->get_tarifas() ){ ?>

                  <tr>
                     <td colspan="2"></td>
                     <td>
                        <a class="btn btn-xs btn-warning" href="<?php echo $fsc->url();?>&recalcular=TRUE">
                           <span class="glyphicon glyphicon-sort-by-order"></span>
                           <span class="hidden-xs">&nbsp; Recalcular tarifas</span>
                        </a>
                     </td>
                     <td colspan="2"></td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td colspan="5">No hay tarifas definidas.</td>
                  </tr>
                  <?php } ?>

               </table>
            </div>
            <div class="container-fluid">
               <div class="row">
                  <div class="col-xs-6">
                     <a class="btn btn-sm btn-success" href="index.php?page=ventas_tarifas" target="_blank">
                        <span class="glyphicon glyphicon-edit"></span>
                        <span class="hidden-xs">&nbsp; Nueva tarifa</span>
                     </a>
                  </div>
                  <div class="col-xs-6 text-right">
                     <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                        <span class="glyphicon glyphicon-floppy-disk"></span> &nbsp; Guardar
                     </button>
                  </div>
               </div>
            </div>
         </div>
         <div role="tabpanel" class="tab-pane" id="proveedores">
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th>Proveedor</th>
                        <th>Ref. Proveedor</th>
                        <th class="text-right">Precio</th>
                        <th class="text-right">Descuento</th>
                        <th class="text-right">Total+<?php  echo FS_IVA;?></th>
                        <th class="text-right">Stock</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->get_articulo_proveedores(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr>
                     <td><a href="<?php echo $value1->url_proveedor();?>"><?php echo $value1->nombre_proveedor();?></a></td>
                     <td><?php echo $value1->refproveedor;?></td>
                     <td class="text-right"><?php echo $fsc->show_precio($value1->precio);?></td>
                     <td class="text-right"><?php echo $fsc->show_numero($value1->dto);?> %</td>
                     <td class="text-right"><?php echo $fsc->show_precio($value1->total_iva());?></td>
                     <td class="text-right">
                        <?php if( $value1->nostock ){ ?>-<?php }else{ ?><?php echo $value1->stock;?><?php } ?>

                     </td>
                  </tr>
                  <?php }else{ ?>

                  <tr><td colspan="6" class="warning">Sin resultados.</td></tr>
                  <?php } ?>

               </table>
            </div>
         </div>
      </div>
   </div>
</form>

<br/>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer2") . ( substr("footer2",-1,1) != "/" ? "/" : "" ) . basename("footer2") );?>