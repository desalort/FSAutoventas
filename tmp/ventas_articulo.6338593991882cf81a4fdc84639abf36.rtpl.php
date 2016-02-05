<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<?php if( $fsc->articulo ){ ?>

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
   function delete_combinacion(cod)
   {
      if( confirm("¿Realmente desea eliminar la combinacion "+cod+"?") )
      {
         window.location.href = '<?php echo $fsc->url();?>&delete_combi='+cod+'#atributos';
      }
   }
   $(document).ready(function() {
      calcular_margen();
      
      if(window.location.hash.substring(1) == 'precios')
      {
         $('#tab_articulo a[href="#precios"]').tab('show');
      }
      else if(window.location.hash.substring(1) == 'stock')
      {
         $('#tab_articulo a[href="#stock"]').tab('show');
      }
      else if(window.location.hash.substring(1) == 'atributos')
      {
         $('#tab_articulo a[href="#atributos"]').tab('show');
      }
      
      $("#b_eliminar_articulo").click(function(event) {
         event.preventDefault();
         <?php if( $fsc->articulo->publico ){ ?>

         alert('Este artículo es público. Si estas seguro de que quieres eliminarlo, desmarcalo como público, guarda y pulsa eliminar.');
         <?php }else{ ?>

         if( confirm("¿Estas seguro de que deseas eliminar este articulo?") )
         {
            window.location.href = "index.php?page=ventas_articulos&delete=<?php echo urlencode($fsc->articulo->referencia); ?>";
         }
         <?php } ?>

      });
      $("#b_imagen").click(function(event) {
         event.preventDefault();
         $("#modal_articulo_imagen").modal('show');
      });
      $('#b_regularizaciones').click(function(event) {
         event.preventDefault();
         $("#b_movimientos").removeClass('active');
         $("#b_regularizaciones").addClass('active');
         $("#table_movimientos").hide();
         $("#table_regularizaciones").show();
      });
      $('#b_movimientos').click(function(event) {
         event.preventDefault();
         $("#b_movimientos").addClass('active');
         $("#b_regularizaciones").removeClass('active');
         $("#table_movimientos").show();
         $("#table_regularizaciones").hide();
      });
   });
</script>

<div class="container-fluid" style="margin-top: 10px; margin-bottom: 10px;">
   <div class="row">
      <div class="col-xs-8">
         <a href="<?php echo $fsc->url();?>" class="btn btn-sm btn-default hidden-xs" title="Recargar la página">
            <span class="glyphicon glyphicon-refresh"></span>
         </a>
         <a href="index.php?page=ventas_articulos" class="btn btn-sm btn-default">
            <span class="glyphicon glyphicon-arrow-left"></span>
            <span class="hidden-xs">&nbsp; Todos los artículos</span>
         </a>
         <a href="#" id="b_imagen" class="btn btn-sm btn-default">
            <span class="glyphicon glyphicon-picture"></span>
            <span class="hidden-xs">&nbsp; Imagen</span>
         </a>
         <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <?php if( $value1->type=='button' ){ ?>

            <a href="index.php?page=<?php echo $value1->from;?>&ref=<?php echo urlencode($fsc->articulo->referencia); ?><?php echo $value1->params;?>" class="btn btn-sm btn-default">
               <?php echo $value1->text;?>

            </a>
            <?php } ?>

         <?php } ?>

      </div>
      <div class="col-xs-4 text-right">
         <a class="btn btn-sm btn-success" href="index.php?page=ventas_articulos#nuevo" title="Nuevo artículo">
            <span class="glyphicon glyphicon-plus"></span>
         </a>
         <?php if( $fsc->allow_delete ){ ?>

         <a href="#" id="b_eliminar_articulo" class="btn btn-sm btn-danger">
            <span class="glyphicon glyphicon-trash"></span>
            <span class="hidden-xs">&nbsp; Eliminar</span>
         </a>
         <?php } ?>

      </div>
   </div>
</div>

<div id="tab_articulo" role="tabpanel">
   <ul class="nav nav-tabs" role="tablist">
      <li role="presentation" class="active">
         <a href="#home" aria-controls="home" role="tab" data-toggle="tab">
            <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
            <span class="hidden-xs">&nbsp; <?php echo $fsc->articulo->referencia;?></span>
         </a>
      </li>
      <?php if( $fsc->mostrar_tab_atributos ){ ?>

      <li role="presentation">
         <a href="#atributos" aria-controls="atributos" role="tab" data-toggle="tab">
            <span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>
            <span class="hidden-xs">&nbsp; Atributos</span>
         </a>
      </li>
      <?php } ?>

      <?php if( $fsc->mostrar_tab_precios ){ ?>

      <li role="presentation">
         <a href="#precios" aria-controls="precios" role="tab" data-toggle="tab">
            <span class="glyphicon glyphicon-usd" aria-hidden="true"></span>
            <span class="hidden-xs">&nbsp; Precios</span>
         </a>
      </li>
      <?php } ?>

      <?php if( $fsc->mostrar_tab_stock ){ ?>

      <li role="presentation">
         <a href="#stock" aria-controls="stock" role="tab" data-toggle="tab">
            <span class="glyphicon glyphicon-tasks" aria-hidden="true"></span>
            <span class="hidden-xs">&nbsp; Stock</span>
         </a>
      </li>
      <?php } ?>

      <?php if( $fsc->equivalentes ){ ?>

      <li role="presentation">
         <a href="#equivalentes" aria-controls="equivalentes" role="tab" data-toggle="tab">
            <span class="glyphicon glyphicon-random" aria-hidden="true"></span>
            <span class="hidden-xs">&nbsp; Equivalentes</span>
         </a>
      </li>
      <?php } ?>

      <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         <?php if( $value1->type=='tab' ){ ?>

         <li role="presentation">
            <a href="#ext_<?php echo $value1->name;?>" aria-controls="ext_<?php echo $value1->name;?>" role="tab" data-toggle="tab"><?php echo $value1->text;?></a>
         </li>
         <?php } ?>

      <?php } ?>

      <li class="dropdown">
         <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
            <span class="hidden-xs">&nbsp; Buscar en...</span>
            <span class="caret"></span>
         </a>
         <ul class="dropdown-menu" role="menu">
         <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <?php if( $value1->type=='tab_button' ){ ?>

            <li>
               <a href="index.php?page=<?php echo $value1->from;?>&ref=<?php echo urlencode($fsc->articulo->referencia); ?><?php echo $value1->params;?>">
                  <?php echo $value1->text;?>

               </a>
            </li>
            <?php } ?>

         <?php } ?>

         </ul>
      </li>
   </ul>
   <div class="tab-content">
      <div role="tabpanel" class="tab-pane active" id="home">
         <form action="<?php echo $fsc->url();?>" method="post" class="post">
            <input type="hidden" name="referencia" value="<?php echo $fsc->articulo->referencia;?>"/>
            <div class="container-fluid">
               <div class="row" style="padding-top: 10px;">
                  <div class="col-sm-3">
                     <div class="form-group">
                        Referencia:
                        <input class="form-control" type="text" name="nreferencia" value="<?php echo $fsc->articulo->referencia;?>" maxlength="18" autocomplete="off"/>
                     </div>
                     <div class="form-group">
                        Tipo:
                        <select name="tipo" class="form-control" onchange="this.form.submit()">
                           <option value="">Producto simple</option>
                           <?php if( $fsc->hay_atributos ){ ?>

                           <option value="atributos"<?php if( $fsc->articulo->tipo=='atributos' ){ ?> selected=""<?php } ?>>Producto con atributos</option>
                           <?php } ?>

                           <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                              <?php if( $value1->type=='tipo_art' ){ ?>

                              <option value="<?php echo $value1->params;?>"<?php if( $fsc->articulo->tipo==$value1->params ){ ?> selected=""<?php } ?>><?php echo $value1->text;?></option>
                              <?php } ?>

                           <?php } ?>

                        </select>
                     </div>
                  </div>
                  <div class="col-sm-9">
                     <div class="form-group">
                        Descripción:
                        <textarea name="descripcion" class="form-control" rows="4"><?php echo $fsc->articulo->descripcion;?></textarea>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-3">
                     <div class="form-group">
                        <a href="<?php echo $fsc->familia->url();?>">Familia</a>:
                        <select class="form-control" name="codfamilia">
                           <option value="">Ninguna</option>
                           <option value="">-------</option>
                           <?php $loop_var1=$fsc->familia->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                              <?php if( $value1->codfamilia===$fsc->articulo->codfamilia ){ ?>

                              <option value="<?php echo $value1->codfamilia;?>" selected=""><?php echo $value1->nivel;?><?php echo $value1->descripcion;?></option>
                              <?php }else{ ?>

                              <option value="<?php echo $value1->codfamilia;?>"><?php echo $value1->nivel;?><?php echo $value1->descripcion;?></option>
                              <?php } ?>

                           <?php } ?>

                        </select>
                     </div>
                  </div>
                   <div class="col-sm-2">
                     <div class="form-group">
                        <a href="<?php echo $fsc->fabricante->url();?>">Fabricante</a>:
                        <select class="form-control" name="codfabricante">
                           <option value="">Ninguno</option>
                           <option value="">-------</option>
                           <?php $loop_var1=$fsc->fabricante->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                              <?php if( $value1->codfabricante===$fsc->articulo->codfabricante ){ ?>

                              <option value="<?php echo $value1->codfabricante;?>" selected=""><?php echo $value1->nombre;?></option>
                              <?php }else{ ?>

                              <option value="<?php echo $value1->codfabricante;?>"><?php echo $value1->nombre;?></option>
                              <?php } ?>

                           <?php } ?>

                        </select>
                     </div>
                  </div>
                  <div class="col-sm-3">
                     <div class="form-group">
                        Código de barras:
                        <div class="input-group">
                           <span class="input-group-addon">
                              <span class="glyphicon glyphicon-barcode" aria-hidden="true"></span>
                           </span>
                           <input class="form-control" type="text" name="codbarras" value="<?php echo $fsc->articulo->codbarras;?>" autocomplete="off"/>
                        </div>
                     </div>
                  </div>
                  <div class="col-sm-4">
                     <div class="form-group">
                        Código de equivalencia:
                        <input class="form-control" type="text" name="equivalencia" value="<?php echo $fsc->articulo->equivalencia;?>" autocomplete="off"/>
                        <p class="help-block">Dos o más artículos son equivalentes si tienen el mismo código de equivalencia.</p>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-3">
                     <div class="form-group">
                        Stock:
                        <input class="form-control" type="text" name="stockfis" value="<?php echo $fsc->articulo->stockfis;?>" disabled="disabled"/>
                        <label>
                           <input type="checkbox" name="nostock" value="TRUE"<?php if( $fsc->articulo->nostock ){ ?> checked="checked"<?php } ?>/>
                           No controlar stock
                        </label>
                     </div>
                  </div>
                  <div class="col-sm-2">
                     <div class="form-group">
                        Stock mínimo:
                        <input class="form-control" type="number" name="stockmin" value="<?php echo $fsc->articulo->stockmin;?>" autocomplete="off"/>
                     </div>
                  </div>
                  <div class="col-sm-2">
                     <div class="form-group">
                        Stock máximo:
                        <input class="form-control" type="number" name="stockmax" value="<?php echo $fsc->articulo->stockmax;?>" autocomplete="off"/>
                     </div>
                  </div>
                  <div class="col-sm-2">
                     <div class="checkbox">
                        <label>
                           <input type="checkbox" name="secompra" value="TRUE"<?php if( $fsc->articulo->secompra ){ ?> checked="checked"<?php } ?>/>
                           Se compra
                        </label>
                     </div>
                     <div class="checkbox">
                        <label>
                           <input type="checkbox" name="sevende" value="TRUE"<?php if( $fsc->articulo->sevende ){ ?> checked="checked"<?php } ?>/>
                           Se vende
                        </label>
                     </div>
                     <div class="checkbox">
                        <label>
                           <input type="checkbox" name="controlstock" value="TRUE"<?php if( $fsc->articulo->controlstock ){ ?> checked="checked"<?php } ?>/>
                           Permitir ventas sin stock
                        </label>
                     </div>
                  </div>
                  <div class="col-sm-3">
                     <div class="checkbox">
                        <label>
                           <input type="checkbox" name="bloqueado" value="TRUE"<?php if( $fsc->articulo->bloqueado ){ ?> checked="checked"<?php } ?>/>
                           <span class="glyphicon glyphicon-lock" aria-hidden="true"></span> Bloqueado / Obsoleto
                        </label>
                     </div>
                     <?php if( $fsc->mostrar_boton_publicar ){ ?>

                     <div class="checkbox">
                        <label title="Sincronizar con tienda online (si está disponible)">
                           <input type="checkbox" name="publico" value="TRUE"<?php if( $fsc->articulo->publico ){ ?> checked="checked"<?php } ?>/>
                           <span class="glyphicon glyphicon-globe" aria-hidden="true"></span> Público
                        </label>
                     </div>
                     <?php }elseif( $fsc->articulo->publico ){ ?>

                     <input type="hidden" name="publico" value="TRUE"/>
                     <?php } ?>

                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-10">
                     <div class="form-group">
                        Observaciones:
                        <textarea class="form-control" name="observaciones" rows="3"><?php echo $fsc->articulo->observaciones;?></textarea>
                     </div>
                  </div>
                  <div class="col-sm-2 text-right">
                     <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                        <span class="glyphicon glyphicon-floppy-disk"></span> &nbsp; Guardar
                     </button>
                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-12">
                     <p class="help-block">
                        <span class="glyphicon glyphicon-question-sign"></span> &nbsp;
                        Puedes aplicar cambios masivos a los artículos usando el
                        <a href="https://www.facturascripts.com/store/producto/plugin-articulos_megamod/" target="_blank">plugin articulos_megamod</a>.
                     </p>
                  </div>
               </div>
            </div>
         </form>
      </div>
      <?php if( $fsc->mostrar_tab_atributos ){ ?>

      <div role="tabpanel" class="tab-pane" id="atributos">
         <div class="table-responsive">
            <table class="table table-hover">
               <thead>
                  <tr>
                     <th>Código</th>
                     <th>Combinación</th>
                     <th class="text-right">Impacto en el precio</th>
                     <th></th>
                  </tr>
               </thead>
               <?php $loop_var1=$fsc->combinaciones(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <form action="<?php echo $fsc->url();?>#atributos" method="post" class="form">
                  <input type="hidden" name="editar_combi" value="<?php echo $value1->codigo;?>"/>
                  <tr>
                     <td><div class="form-control"><?php echo $value1->codigo;?></div></td>
                     <td><div class="form-control"><?php echo $value1->txt;?></div></td>
                     <td>
                        <input type="text" name="impactoprecio" value="<?php echo $value1->impactoprecio;?>" class="form-control text-right" autocomplete="off" required=""/>
                     </td>
                     <td class="text-right">
                        <div class="btn-group">
                           <?php if( $fsc->allow_delete ){ ?>

                           <a href="#" class="btn btn-sm btn-danger" onclick="delete_combinacion('<?php echo $value1->codigo;?>')">
                              <span class="glyphicon glyphicon-trash"></span>
                           </a>
                           <?php } ?>

                           <button class="btn btn-sm btn-primary" type="submit" title="Guardar">
                              <span class="glyphicon glyphicon-floppy-disk"></span>
                           </button>
                        </div>
                     </td>
                  </tr>
               </form>
               <?php }else{ ?>

               <tr class="warning">
                  <td colspan="4">Sin resultados.</td>
               </tr>
               <?php } ?>

            </table>
         </div>
         <form action="<?php echo $fsc->url();?>#atributos" method="post" class="form">
            <input type="hidden" name="nueva_combi" value="TRUE"/>
            <div class="container-fluid">
               <div class="row">
                  <div class="col-sm-12">
                     <div class="panel panel-info">
                        <div class="panel-heading">
                           <h3 class="panel-title">Nueva combinación</h3>
                        </div>
                        <div class="panel-body">
                           <div class="container-fluid">
                              <div class="row">
                                 <?php $loop_var1=$fsc->atributos(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                                 <div class="col-sm-3">
                                    <div class="form-group">
                                       <a href="<?php echo $value1->url();?>"><?php echo $value1->nombre;?></a>
                                       <select name="idvalor_<?php echo $counter1;?>" class="form-control">
                                          <option value="">Ninguno</option>
                                          <option value="">------</option>
                                          <?php $loop_var2=$value1->valores(); $counter2=-1; if($loop_var2) foreach( $loop_var2 as $key2 => $value2 ){ $counter2++; ?>

                                          <option value="<?php echo $value2->id;?>"><?php echo $value2->valor;?></option>
                                          <?php } ?>

                                       </select>
                                    </div>
                                 </div>
                                 <?php } ?>

                              </div>
                              <div class="row">
                                 <div class="col-sm-5">
                                    Impacto en el precio:
                                    <div class="input-group">
                                       <span class="input-group-addon"><?php echo $fsc->simbolo_divisa();?></span>
                                       <input type="text" class="form-control" name="impactoprecio" value="0" autocomplete="off" required=""/>
                                    </div>
                                 </div>
                                 <div class="col-sm-7 text-right">
                                    <br/>
                                    <button class="btn btn-sm btn-primary" type="submit">
                                       <span class="glyphicon glyphicon-floppy-disk"></span> &nbsp; Guardar
                                    </button>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </form>
      </div>
      <?php } ?>

      <?php if( $fsc->mostrar_tab_precios ){ ?>

      <div role="tabpanel" class="tab-pane" id="precios">
         <form action="<?php echo $fsc->url();?>#precios" method="post" class="form">
            <input type="hidden" name="referencia" value="<?php echo $fsc->articulo->referencia;?>"/>
            <input type="hidden" id="iva" name="iva" value="<?php echo $fsc->articulo->get_iva();?>"/>
            <div class="container-fluid" style="margin-top: 10px;">
               <div class="row">
                  <div class="col-sm-4">
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
                  <div class="col-sm-4">
                     <div class="form-group">
                        <a href="<?php echo $fsc->impuesto->url();?>"><?php  echo FS_IVA;?></a>:
                        <select class="form-control" name="codimpuesto" onchange="this.form.submit()">
                        <?php $loop_var1=$fsc->impuesto->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <?php if( $value1->codimpuesto==$fsc->articulo->codimpuesto ){ ?>

                           <option value="<?php echo $value1->codimpuesto;?>" selected=""><?php echo $value1->descripcion;?></option>
                           <?php }else{ ?>

                           <option value="<?php echo $value1->codimpuesto;?>"><?php echo $value1->descripcion;?></option>
                           <?php } ?>

                        <?php } ?>

                        </select>
                     </div>
                  </div>
                  <div class="col-sm-4">
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
                  <div class="col-sm-4">
                     <div class="form-group">
                        Precio de Coste:
                        <?php if( $fsc->articulo->secompra AND FS_COST_IS_AVERAGE ){ ?>

                        <input type="text" name="coste" id="coste" class="form-control" value="<?php echo $fsc->articulo->preciocoste();?>" disabled="disabled">
                        <?php }else{ ?>

                        <input type="text" name="preciocoste" id="coste" class="form-control" value="<?php echo $fsc->articulo->preciocoste();?>" onclick="this.select()" autocomplete="off">
                        <?php } ?>

                        <p class="help-block">
                           Puede cambiar la configuración de precio de coste desde
                           la configuración del <a href="index.php?page=admin_almacenes">almacén</a>.
                        </p>
                     </div>
                  </div>
                  <div class="col-sm-4">
                     <div class="form-group">
                        Margen sobre precio de coste
                        <div class="input-group">
                           <span class="input-group-addon">%</span>
                           <input type="text" class="form-control" id="margen" name="margen" value="0" autocomplete="off" onkeyup="cambiar_margen()" onclick="this.select()"/>
                        </div>
                        <p class="help-block">Sirve para calcular el Precio, pero no se almacena (todavía).</p>
                     </div>
                  </div>
                  <div class="col-sm-4">
                     <div class="hidden-xs"><br/></div>
                     <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                        <span class="glyphicon glyphicon-floppy-disk"></span> &nbsp; Guardar
                     </button>
                     <div class="visible-xs"><br/></div>
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
                                 <th class="text-right">Nuevo Precio</th>
                                 <th class="text-right">Nuevo Precio+<?php  echo FS_IVA;?></th>
                              </tr>
                           </thead>
                           <?php $loop_var1=$fsc->get_tarifas(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <tr>
                              <td width="120"><div class="form-control"><a href="<?php echo $value1->tarifa_url;?>"><?php echo $value1->codtarifa;?></a></div></td>
                              <td><div class="form-control"><?php echo $value1->tarifa_nombre;?></div></td>
                              <td><div class="form-control"><?php echo $value1->tarifa_diff;?></div></td>
                              <td class="text-right">
                                 <div class="form-control"><?php echo $fsc->show_precio($value1->pvp*(100 - $value1->dtopor)/100);?></div>
                              </td>
                              <td class="text-right">
                                 <div class="form-control"><?php echo $fsc->show_precio($value1->pvp*(100 - $value1->dtopor)/100*(100 + $value1->get_iva())/100);?></div>
                              </td>
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
                           <div class="col-xs-12">
                              <a class="btn btn-sm btn-success" href="index.php?page=ventas_articulos#tarifas">
                                 <span class="glyphicon glyphicon-edit"></span> &nbsp; Nueva tarifa
                              </a>
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

                           <tr><td colspan="6" class="bg-warning">Sin resultados.</td></tr>
                           <?php } ?>

                        </table>
                     </div>
                     <div class="container-fluid">
                        <div class="row">
                           <div class="col-sm-12">
                              <p class="help-block">
                                 Estos son los proveedores a los que has comprado este producto,
                                 sus referencias, su último precio y descuento, y su stock,
                                 si lo ofrecen.
                              </p>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </form>
      </div>
      <?php } ?>

      <?php if( $fsc->mostrar_tab_stock ){ ?>

      <div role="tabpanel" class="tab-pane" id="stock">
         <div class="table-responsive">
            <table class="table table-hover">
               <thead>
                  <tr>
                     <th class="text-left">Almacén</th>
                     <th class="text-left">Ubicación</th>
                     <th class="text-right">Cantidad actual</th>
                     <th class="text-right">Nueva cantidad</th>
                     <th class="text-left">Motivo</th>
                     <th class="text-right">Acción</th>
                  </tr>
               </thead>
               <?php $loop_var1=$fsc->stocks; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <tr>
                  <form action="<?php echo $fsc->url();?>#stock" method="post" class="form">
                     <input type="hidden" name="idstock" value="<?php echo $value1->idstock;?>"/>
                     <input type="hidden" name="almacen" value="<?php echo $value1->codalmacen;?>"/>
                     <input type="hidden" name="referencia" value="<?php echo $fsc->articulo->referencia;?>"/>
                     <input type="hidden" name="cantidadini" value="<?php echo $value1->cantidad;?>"/>
                     <td><div class="form-control"><?php echo $value1->codalmacen;?></div></td>
                     <td>
                        <input type="text" class="form-control" name="ubicacion" value="<?php echo $value1->ubicacion;?>" placeholder="dentro del almacén..." autocomplete="off"/>
                     </td>
                     <td><div class="form-control text-right"><?php echo $value1->cantidad;?></div></td>
                     <td><input type="number" step="any" class="form-control text-right" name="cantidad" value="<?php echo $value1->cantidad;?>" autocomplete="off"/></td>
                     <td><input type="text" class="form-control" name="motivo" placeholder="Escribe el motivo del cambio"/></td>
                     <td class="text-right">
                        <button class="btn btn-sm btn-primary" type="submit" title="Guardar" onclick="this.disabled=true;this.form.submit();">
                           <span class="glyphicon glyphicon-floppy-disk"></span>
                        </button>
                     </td>
                  </form>
               </tr>
               <?php } ?>

               <?php if( $fsc->nuevos_almacenes ){ ?>

               <tr class="info">
                  <form action="<?php echo $fsc->url();?>#stock" method="post" class="form">
                     <input type="hidden" name="referencia" value="<?php echo $fsc->articulo->referencia;?>"/>
                     <input type="hidden" name="cantidadini" value="0"/>
                     <td>
                        <select class="form-control" name="almacen">
                           <?php $loop_var1=$fsc->nuevos_almacenes; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <option value="<?php echo $value1->codalmacen;?>"><?php echo $value1->nombre;?></option>
                           <?php } ?>

                        </select>
                     </td>
                     <td>
                        <input type="text" class="form-control" name="ubicacion" placeholder="dentro del almacén..." autocomplete="off"/>
                     </td>
                     <td><div class="form-control text-right">0</div></td>
                     <td><input class="form-control text-right" type="number" step="any" name="cantidad" value="0" autocomplete="off"/></td>
                     <td><input type="text" class="form-control" name="motivo" placeholder="Escribe el motivo del cambio"/></td>
                     <td class="text-right">
                        <button class="btn btn-sm btn-primary" type="submit" title="Guardar" onclick="this.disabled=true;this.form.submit();">
                           <span class="glyphicon glyphicon-floppy-disk"></span>
                        </button>
                     </td>
                  </form>
               </tr>
               <?php } ?>

            </table>
         </div>
         <div class="container-fluid">
            <div class="row">
               <div class="col-sm-6">
                  <div class="btn-group">
                     <button type="button" id="b_movimientos" class="btn btn-sm btn-default active">
                        <span class="glyphicon glyphicon-transfer"></span>
                        &nbsp; Movimientos
                     </button>
                     <button type="button" id="b_regularizaciones" class="btn btn-sm btn-default">Regularizaciones</button>
                  </div>
               </div>
               <div class="col-sm-6 text-right">
                  <a href="#" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modal_recal_stock">
                     <span class="glyphicon glyphicon-wrench"></span>
                     &nbsp; Recalcular stock
                  </a>
               </div>
            </div>
         </div>
         <div class="table-responsive" style="margin-top: 10px;">
            <table id="table_movimientos" class="table table-hover">
               <thead>
                  <tr>
                     <th>Orígen</th>
                     <th class="text-right">Movimiento</th>
                     <th class="text-right">Cantidad final</th>
                     <th class="text-right">
                        Fecha
                        <span class="glyphicon glyphicon-sort-by-attributes" aria-hidden="true"></span>
                     </th>
                     <th class="text-right">Hora</th>
                  </tr>
               </thead>
               <?php $loop_var1=$fsc->get_movimientos(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <tr class='clickableRow<?php if( $value1['movimiento']=='-' ){ ?> warning<?php }elseif( $value1['movimiento']>0 ){ ?> success<?php }else{ ?> danger<?php } ?>' href='<?php echo $value1['url'];?>'>
                  <td>
                     <?php echo $value1['codalmacen'];?> - <a href="<?php echo $value1['url'];?>"><?php echo $value1['origen'];?></a>
                  </td>
                  <td class="text-right">
                     <?php if( $value1['movimiento']>0 ){ ?>+<?php } ?><?php echo $value1['movimiento'];?>

                  </td>
                  <td class="text-right"><?php echo $value1['final'];?></td>
                  <td class="text-right"><?php echo $value1['fecha'];?></td>
                  <td class="text-right"><?php echo $value1['hora'];?></td>
               </tr>
               <?php }else{ ?>

               <tr>
                  <td colspan="5" class="warning">Sin resultados.</td>
               </tr>
               <?php } ?>

               <tr>
                  <td colspan="2"></td>
                  <td class="text-right"><b><?php echo $fsc->articulo->stockfis;?></b></td>
                  <td colspan="2"></td>
               </tr>
            </table>
            <table id="table_regularizaciones" class="table table-hover" style="display: none;">
               <thead>
                  <tr>
                     <th class="text-left">Usuario</th>
                     <th class="text-left">Motivo</th>
                     <th class="text-right">Cantidad inicial</th>
                     <th class="text-right">Cantidad final</th>
                     <th class="text-right">Fecha</th>
                     <th class="text-right">Hora</th>
                     <th></th>
                  </tr>
               </thead>
               <?php $loop_var1=$fsc->regularizaciones; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <tr<?php if( $value1->cantidadfin<$value1->cantidadini ){ ?> class="danger"<?php } ?>>
                  <td><?php echo $value1->nick;?></td>
                  <td><?php echo $value1->motivo;?></td>
                  <td class="text-right"><?php echo $value1->cantidadini;?></td>
                  <td class="text-right"><?php echo $value1->cantidadfin;?></td>
                  <td class="text-right"><?php echo $value1->fecha;?></td>
                  <td class="text-right"><?php echo $value1->hora;?></td>
                  <td>
                     <?php if( $fsc->allow_delete ){ ?>

                     <a href="<?php echo $fsc->url();?>&deletereg=<?php echo $value1->id;?>#stock" title="Eliminar la regularización">
                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                     </a>
                     <?php } ?>

                  </td>
               </tr>
               <?php }else{ ?>

               <tr class="warning">
                  <td colspan="7">Sin resultados.</td>
               </tr>
               <?php } ?>

            </table>
         </div>
         <div class="modal fade" id="modal_recal_stock" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
               <div class="modal-content">
                  <div class="modal-header">
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                     </button>
                     <h4 class="modal-title">Recalcular stock</h4>
                  </div>
                  <div class="modal-body">
                     <p class='help-block'>
                        Se recalculará el stock del artículo a partir de las
                        regularizaciones (de stock) y los albaranes y facturas de compra
                        y venta.
                     </p>
                     <p class='help-block'>
                        <b>Advertencia</b>: si el artículo no tiene ningún movimiento,
                        ni regularización, el stock resultante <b>será 0</b>.
                     </p>
                  </div>
                  <div class="modal-footer">
                     <a href="<?php echo $fsc->url();?>&recalcular_stock=TRUE#stock" class="btn btn-sm btn-warning">
                        <span class="glyphicon glyphicon-wrench"></span>
                        &nbsp; Recalcular stock
                     </a>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php } ?>

      <?php if( $fsc->equivalentes ){ ?>

      <div role="tabpanel" class="tab-pane" id="equivalentes">
         <div class="table-responsive">
            <table class="table table-hover">
               <thead>
                  <tr>
                     <th class="text-left">Artículo</th>
                     <th class="text-right">Precio</th>
                     <th class="text-right">Precio+<?php  echo FS_IVA;?></th>
                     <th class="text-right">Stock</th>
                  </tr>
               </thead>
               <?php $loop_var1=$fsc->equivalentes; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <tr class="clickableRow" href="<?php echo $value1->url();?>">
                  <td>
                     <a href="<?php echo $value1->url();?>"><?php echo $value1->referencia;?></a>
                     <?php echo $value1->descripcion;?>

                  </td>
                  <td class="text-right"><?php echo $fsc->show_precio($value1->pvp);?></td>
                  <td class="text-right"><?php echo $fsc->show_precio($value1->pvp_iva());?></td>
                  <td class="text-right"><?php echo $value1->stockfis;?></td>
               </tr>
               <?php }else{ ?>

               <tr class="warning">
                  <td colspan="3">Sin resultados.</td>
               </tr>
               <?php } ?>

            </table>
         </div>
      </div>
      <?php } ?>

      <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         <?php if( $value1->type=='tab' ){ ?>

         <div role="tabpanel" class="tab-pane" id="ext_<?php echo $value1->name;?>">
            <iframe src="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>&ref=<?php echo urlencode($fsc->articulo->referencia); ?>" width="100%" height="2000" frameborder="0">
            </iframe>
         </div>
         <?php } ?>

      <?php } ?>

   </div>
</div>

<form action="<?php echo $fsc->url();?>" enctype="multipart/form-data" method="post" class="form">
   <input type="hidden" name="referencia" value="<?php echo $fsc->articulo->referencia;?>"/>
   <input type="hidden" name="imagen" value="TRUE"/>
   <div class="modal fade" id="modal_articulo_imagen">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
               <h4 class="modal-title">
                  <span class="glyphicon glyphicon-picture"></span> &nbsp; Imagen
               </h4>
            </div>
            <div class="modal-body">
               <?php if( $fsc->articulo->imagen_url() ){ ?>

               <div class="thumbnail">
                  <img src="<?php echo $fsc->articulo->imagen_url();?>" alt="<?php echo $fsc->articulo->referencia;?>"/>
                  <div class="caption">
                     <p>Esta imagen está guardada en <?php echo $fsc->articulo->imagen_url();?></p>
                  </div>
               </div>
               <?php }else{ ?>

               <div class="form-group">
                  <input type="file" name="fimagen" accept="image/jpeg, image/png"/>
               </div>
               <?php } ?>

            </div>
            <div class="modal-footer">
               <?php if( $fsc->articulo->imagen_url() ){ ?>

               <a class="btn btn-sm btn-danger" href="<?php echo $fsc->url();?>&delete_img=TRUE">
                  <span class="glyphicon glyphicon-trash"></span> &nbsp; Eliminar
               </a>
               <?php }else{ ?>

               <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                  <span class="glyphicon glyphicon-floppy-disk"></span> &nbsp; Guardar
               </button>
               <?php } ?>

            </div>
         </div>
      </div>
   </div>
</form>
<?php }else{ ?>

<div class="thumbnail">
   <img src="view/img/fuuu_face.png" alt="fuuuuu"/>
</div>
<?php } ?>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>