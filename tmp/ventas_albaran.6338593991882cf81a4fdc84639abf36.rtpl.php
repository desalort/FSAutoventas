<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<?php if( $fsc->albaran ){ ?>

<?php if( $fsc->albaran->ptefactura ){ ?>

<script type="text/javascript" src="<?php echo $fsc->get_js_location('provincias.js');?>"></script>
<script type="text/javascript" src="<?php echo $fsc->get_js_location('nueva_venta.js');?>"></script>
<script type="text/javascript">
   numlineas = <?php echo count($fsc->albaran->get_lineas()); ?>;
   fs_nf0 = <?php  echo FS_NF0;?>;
   all_impuestos = <?php echo json_encode($fsc->impuesto->all()); ?>;
   all_series = <?php echo json_encode($fsc->serie->all()); ?>;
   cliente = <?php echo json_encode($fsc->cliente_s); ?>;
   nueva_venta_url = '<?php echo $fsc->nuevo_albaran_url;?>';
   kiwimaru_url = '<?php  echo FS_COMMUNITY_URL;?>/index.php?page=kiwimaru';
   
   $(document).ready(function() {
      $("#numlineas").val(numlineas);
      usar_serie();
      recalcular();
      $("#ac_cliente").autocomplete({
         serviceUrl: nueva_venta_url,
         paramName: 'buscar_cliente',
         onSelect: function (suggestion) {
            if(suggestion)
            {
               if(document.f_albaran.cliente.value != suggestion.data && suggestion.data != '')
               {
                  document.f_albaran.cliente.value = suggestion.data;
                  usar_cliente(suggestion.data);
               }
            }
         }
      });
   });
</script>
<?php }else{ ?>

<script type="text/javascript">
   function check_enviar_copia()
   {
      if($("#copia").is(":checked"))
      {
         $("#checked_copia").prop("disabled",false);
      }
      else
      {
         $("#checked_copia").prop("disabled",true);
      }
   }
   
   $(document).ready(function() {
      <?php if( $fsc->albaran->totalrecargo==0 ){ ?>

      $(".recargo").hide();
      <?php } ?>

      <?php if( $fsc->albaran->totalirpf==0 ){ ?>

      $(".irpf").hide();
      <?php } ?>

   });
</script>
<?php } ?>

<script type="text/javascript">
   $(document).ready(function() {
      $("#b_imprimir").click(function(event) {
         event.preventDefault();
         $("#modal_imprimir_albaran").modal('show');
      });
      $("#b_enviar").click(function(event) {
         event.preventDefault();
         $("#modal_enviar").modal('show');
         document.enviar_email.email.select();
      });
      $("#b_aprobar").click(function(event) {
         event.preventDefault();
         $("#modal_aprobar").modal('show');
      });
      $("#b_eliminar").click(function(event) {
         event.preventDefault();
         $("#modal_eliminar").modal('show');
      });
   });
</script>

<form name="f_albaran" action="<?php echo $fsc->albaran->url();?>" method="post" class="form">
   <input type="hidden" name="idalbaran" value="<?php echo $fsc->albaran->idalbaran;?>"/>
   <input type="hidden" name="cliente" value="<?php echo $fsc->albaran->codcliente;?>"/>
   <input type="hidden" id="numlineas" name="numlineas" value="0"/>
   <div class="container-fluid">
      <div class="row" style="margin-top: 10px;">
         <div class="col-xs-8">
            <a class="btn btn-sm btn-default hidden-xs" href="<?php echo $fsc->url();?>" title="Recargar la página">
               <span class="glyphicon glyphicon-refresh"></span>
            </a>
            <div class="btn-group">
               <a id="b_imprimir" class="btn btn-sm btn-default">
                  <span class="glyphicon glyphicon-print"></span>
                  <span class="hidden-xs">&nbsp; Imprimir</span>
               </a>
               <?php if( $fsc->empresa->can_send_mail() ){ ?>

               <a id="b_enviar" class="btn btn-sm btn-default" href="#">
                  <span class="glyphicon glyphicon-envelope"></span>
                  <?php if( $fsc->albaran->femail ){ ?>

                  <span class="hidden-xs"> &nbsp; Reenviar</span>
                  <?php }else{ ?>

                  <span class="hidden-xs"> &nbsp; Enviar</span>
                  <?php } ?>

               </a>
               <?php } ?>

               <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <?php if( $value1->type=='button' ){ ?>

                  <a href="index.php?page=<?php echo $value1->from;?>&id=<?php echo $fsc->albaran->idalbaran;?><?php echo $value1->params;?>" class="btn btn-sm btn-default">
                     <?php echo $value1->text;?>

                  </a>
                  <?php } ?>

               <?php } ?>

            </div>
            
            <?php if( $fsc->albaran->idfactura ){ ?>

            <a class="btn btn-sm btn-info" href="<?php echo $fsc->albaran->factura_url();?>">
               <span class="glyphicon glyphicon-eye-open"></span>
               <span class="hidden-xs">&nbsp; Ver Factura</span>
            </a>
            <?php }elseif( $fsc->albaran->ptefactura ){ ?>

            <a id="b_aprobar" class="btn btn-sm btn-default" href="#">
               <span class="glyphicon glyphicon-paperclip"></span>
               <span class="hidden-xs">&nbsp; Aprobar</span>
            </a>
            <?php }else{ ?>

            <a class="btn btn-sm btn-warning" href="#">
               <span class="glyphicon glyphicon-lock"></span>
               <span class="hidden-xs">&nbsp; Bloqueado</span>
            </a>
            <?php } ?>

         </div>
         <div class="col-xs-4 text-right">
            <a class="btn btn-sm btn-success" href="index.php?page=nueva_venta&tipo=albaran" title="Nuevo <?php  echo FS_ALBARAN;?>">
               <span class="glyphicon glyphicon-plus"></span>
            </a>
            <div class="btn-group">
               <?php if( $fsc->allow_delete ){ ?>

               <a id="b_eliminar" class="btn btn-sm btn-danger" href="#">
                  <span class="glyphicon glyphicon-trash"></span>
                  <span class="hidden-sm hidden-xs">&nbsp; Eliminar</span>
               </a>
               <?php } ?>

               <button class="btn btn-sm btn-primary" type="button" onclick="this.disabled=true;this.form.submit();">
                  <span class="glyphicon glyphicon-floppy-disk"></span>
                  <span class="hidden-xs">&nbsp; Guardar</span>
               </button>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-12">
            <br/>
            <ol class="breadcrumb" style="margin-bottom: 5px;">
               <li><a href="<?php echo $fsc->ppage->url();?>">Ventas</a></li>
               <li><a href="<?php echo $fsc->ppage->url();?>" class="text-capitalize"><?php  echo FS_ALBARANES;?></a></li>
               <li>
                  <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                     <?php if( $value1->codserie==$fsc->albaran->codserie ){ ?>

                     <a href="<?php echo $fsc->ppage->url();?>&codserie=<?php echo $value1->codserie;?>" class="text-capitalize"><?php echo $value1->descripcion;?></a>
                     <?php } ?>

                  <?php } ?>

               </li>
               <li>
                  <a href="<?php echo $fsc->albaran->cliente_url();?>"><?php echo $fsc->albaran->nombrecliente;?></a>
               </li>
               <?php if( $fsc->cliente_s ){ ?>

                  <?php if( $fsc->cliente_s->nombre!=$fsc->albaran->nombrecliente ){ ?>

                  <li>
                     <a href="#" onclick="alert('Cliente conocido como: <?php echo $fsc->cliente_s->nombre;?>')">
                        <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                     </a>
                  </li>
                  <?php } ?>

               <?php } ?>

               <li class="active"><b><?php echo $fsc->albaran->codigo;?></b></li>
            </ol>
            <p class="help-block">
               <?php if( $fsc->agente ){ ?>

               <span class="text-capitalize"><?php  echo FS_ALBARAN;?></span> creado por
               <a href="<?php echo $fsc->agente->url();?>"><?php echo $fsc->agente->get_fullname();?></a>.
               <?php }else{ ?>

               Sin datos de qué empleado ha creado este <?php  echo FS_ALBARAN;?>.
               <?php } ?>

            </p>
         </div>
      </div>
      <div class="row">
         <?php if( $fsc->albaran->ptefactura ){ ?>

         <div class="col-md-3 col-sm-12">
            <div class="form-group">
               Cliente actual:
               <div class="input-group">
                  <input class="form-control" type="text" name="ac_cliente" id="ac_cliente" value="<?php echo $fsc->albaran->nombrecliente;?>" placeholder="Buscar" autocomplete="off"/>
                  <span class="input-group-btn">
                     <button class="btn btn-default" type="button" onclick="document.f_albaran.ac_cliente.value=''; document.f_albaran.ac_cliente.focus();">
                        <span class="glyphicon glyphicon-edit"></span>
                     </button>
                  </span>
               </div>
            </div>
         </div>
         <?php } ?>

         <div class="col-md-3 col-sm-4">
            <div class="form-group">
               <span class='text-capitalize'><?php  echo FS_NUMERO2;?>:</span>
               <input class="form-control" type="text" name="numero2" value="<?php echo $fsc->albaran->numero2;?>"/>
            </div>
         </div>
         <?php if( $fsc->albaran->ptefactura ){ ?>

         <div class="col-md-2 col-sm-2">
            <div class="form-group">
               <a href="<?php echo $fsc->serie->url();?>">Serie</a>:
               <select class="form-control" name="serie" id="codserie" onchange="usar_serie();recalcular();">
               <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <?php if( $value1->codserie==$fsc->albaran->codserie ){ ?>

                  <option value="<?php echo $value1->codserie;?>" selected=""><?php echo $value1->descripcion;?></option>
                  <?php }else{ ?>

                  <option value="<?php echo $value1->codserie;?>"><?php echo $value1->descripcion;?></option>
                  <?php } ?>

               <?php } ?>

               </select>
            </div>
         </div>
         <?php } ?>

         <div class="col-md-2 col-sm-3">
            <div class="form-group">
               Fecha:
               <?php if( $fsc->albaran->ptefactura ){ ?>

               <input class="form-control datepicker" type="text" name="fecha" value="<?php echo $fsc->albaran->fecha;?>" autocomplete="off"/>
               <?php }else{ ?>

               <div class="form-control"><?php echo $fsc->albaran->fecha;?></div>
               <?php } ?>

            </div>
         </div>
         <div class="col-md-2 col-sm-3">
            <div class="form-group">
               Hora:
               <?php if( $fsc->albaran->ptefactura ){ ?>

               <input class="form-control" type="text" name="hora" value="<?php echo $fsc->albaran->hora;?>" autocomplete="off"/>
               <?php }else{ ?>

               <div class="form-control"><?php echo $fsc->albaran->hora;?></div>
               <?php } ?>

            </div>
         </div>
      </div>
   </div>
   
   <div role="tabpanel">
      <ul class="nav nav-tabs" role="tablist">
         <li role="presentation" class="active">
            <a href="#lineas_a" aria-controls="lineas_a" role="tab" data-toggle="tab">
               <span class="glyphicon glyphicon-list" aria-hidden="true"></span>
               <span class="hidden-xs">&nbsp; Líneas</span>
            </a>
         </li>
         <li role="presentation">
            <a href="#detalles" aria-controls="detalles" role="tab" data-toggle="tab">
               <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
               <span class="hidden-xs">&nbsp; Detalles</span>
            </a>
         </li>
         <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <?php if( $value1->type=='tab' ){ ?>

            <li role="presentation">
               <a href="#ext_<?php echo $value1->name;?>" aria-controls="ext_<?php echo $value1->name;?>" role="tab" data-toggle="tab"><?php echo $value1->text;?></a>
            </li>
            <?php } ?>

         <?php } ?>

      </ul>
      <div class="tab-content">
         <div role="tabpanel" class="tab-pane active" id="lineas_a">
            <div class="table-responsive">
               <?php if( $fsc->albaran->ptefactura ){ ?>

               <table class="table table-condensed">
                  <thead>
                     <tr>
                        <th class="text-left" width="180">Referencia</th>
                        <th class="text-left">Descripción</th>
                        <th class="text-right" width="80">Cantidad</th>
                        <th width="50"></th>
                        <th class="text-right" width="110">Precio</th>
                        <th class="text-right" width="90">Dto. %</th>
                        <th class="text-right" width="130">Neto</th>
                        <th class="text-right" width="115"><?php  echo FS_IVA;?></th>
                        <th class="text-right recargo" width="115">RE %</th>
                        <th class="text-right irpf" width="115"><?php  echo FS_IRPF;?> %</th>
                        <th class="text-right" width="140">Total</th>
                     </tr>
                  </thead>
                  <tbody id="lineas_albaran">
                     <?php $loop_var1=$fsc->albaran->get_lineas(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                     <tr id="linea_<?php echo $counter1;?>">
                        <td>
                           <input type="hidden" name="idlinea_<?php echo $counter1;?>" value="<?php echo $value1->idlinea;?>"/>
                           <input type="hidden" name="referencia_<?php echo $counter1;?>" value="<?php echo $value1->referencia;?>"/>
                           <div class="form-control">
                              <?php if( $value1->idlineapedido ){ ?>

                              <a target="_blank" href="index.php?page=ventas_pedido&id=<?php echo $value1->idpedido;?>" title="ver <?php  echo FS_PEDIDO;?>">
                                 <span class="glyphicon glyphicon-paperclip" aria-hidden="true"></span>
                              </a> &nbsp;
                              <?php } ?>

                              <a target="_blank" href="<?php echo $value1->articulo_url();?>"><?php echo $value1->referencia;?></a>
                           </div>
                        </td>
                        <td><textarea class="form-control" name="desc_<?php echo $counter1;?>" rows="1" onclick="this.select()"><?php echo $value1->descripcion;?></textarea></td>
                        <td>
                           <input type="number" step="any" id="cantidad_<?php echo $counter1;?>" class="form-control text-right" name="cantidad_<?php echo $counter1;?>"
                                  value="<?php echo $value1->cantidad;?>" onchange="recalcular()" onkeyup="recalcular()" autocomplete="off" value="1"/>
                        </td>
                        <td>
                           <button class="btn btn-sm btn-danger" type="button" onclick="$('#linea_<?php echo $counter1;?>').remove();recalcular();">
                              <span class="glyphicon glyphicon-trash"></span>
                           </button>
                        </td>
                        <td>
                           <input type="text" class="form-control text-right" id="pvp_<?php echo $counter1;?>" name="pvp_<?php echo $counter1;?>" value="<?php echo $value1->pvpunitario;?>"
                                  onkeyup="recalcular()" onclick="this.select()" autocomplete="off"/>
                        </td>
                        <td>
                           <input type="text" id="dto_<?php echo $counter1;?>" name="dto_<?php echo $counter1;?>" value="<?php echo $value1->dtopor;?>" class="form-control text-right"
                                  onkeyup="recalcular()" onclick="this.select()" autocomplete="off"/>
                        </td>
                        <td>
                           <input type="text" class="form-control text-right" id="neto_<?php echo $counter1;?>" name="neto_<?php echo $counter1;?>"
                                  onchange="ajustar_neto()" onclick="this.select()" autocomplete="off"/>
                        </td>
                        <td>
                           <select class="form-control" id="iva_<?php echo $counter1;?>" name="iva_<?php echo $counter1;?>" onchange="ajustar_iva('<?php echo $counter1;?>')">
                           <?php $loop_var2=$fsc->impuesto->all(); $counter2=-1; if($loop_var2) foreach( $loop_var2 as $key2 => $value2 ){ $counter2++; ?>

                              <?php if( $value1->codimpuesto==$value2->codimpuesto OR $value1->iva==$value2->iva ){ ?>

                              <option value="<?php echo $value2->iva;?>" selected=""><?php echo $value2->descripcion;?></option>
                              <?php }else{ ?>

                              <option value="<?php echo $value2->iva;?>"><?php echo $value2->descripcion;?></option>
                              <?php } ?>

                           <?php } ?>

                           </select>
                        </td>
                        <td class="recargo">
                           <input type="text" class="form-control text-right" id="recargo_<?php echo $counter1;?>" name="recargo_<?php echo $counter1;?>" value="<?php echo $value1->recargo;?>"
                                  onchange="recalcular()" onclick="this.select()" autocomplete="off"/>
                        </td>
                        <td class="irpf">
                           <input type="text" class="form-control text-right" id="irpf_<?php echo $counter1;?>" name="irpf_<?php echo $counter1;?>" value="<?php echo $value1->irpf;?>"
                                  onchange="recalcular()" onclick="this.select()" autocomplete="off"/>
                        </td>
                        <td>
                           <input type="text" class="form-control text-right" id="total_<?php echo $counter1;?>" name="total_<?php echo $counter1;?>"
                                  onchange="ajustar_total()" onclick="this.select()" autocomplete="off"/>
                        </td>
                     </tr>
                     <?php } ?>

                  </tbody>
                  <tbody>
                     <tr class="info">
                        <td><input id="i_new_line" class="form-control" type="text" placeholder="Buscar para añadir..." autocomplete="off"/></td>
                        <td colspan="3">
                           <a href="#" class="btn btn-sm btn-default" title="Añadir sin buscar" onclick="return add_linea_libre()">
                              <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                           </a>
                        </td>
                        <td colspan="2">
                           <div class="form-control text-right">
                              Totales
                              <?php if( $fsc->albaran->coddivisa!=$fsc->empresa->coddivisa ){ ?>

                              (<?php echo $fsc->albaran->coddivisa;?>)
                              <?php } ?>

                           </div>
                        </td>
                        <td><div id="aneto" class="form-control text-right" style="font-weight: bold;"><?php echo $fsc->show_numero(0);?></div></td>
                        <td><div id="aiva" class="form-control text-right" style="font-weight: bold;"><?php echo $fsc->show_numero(0);?></div></td>
                        <td class="recargo">
                           <div id="are" class="form-control text-right" style="font-weight: bold;"><?php echo $fsc->show_numero(0);?></div>
                        </td>
                        <td class="irpf">
                           <div id="airpf" class="form-control text-right" style="font-weight: bold;"><?php echo $fsc->show_numero(0);?></div>
                        </td>
                        <td>
                           <input type="text" name="atotal" id="atotal" class="form-control text-right" style="font-weight: bold;"
                                  value="0" onchange="recalcular()" autocomplete="off"/>
                        </td>
                     </tr>
                     <?php if( $fsc->user->admin ){ ?>

                     <tr>
                        <td colspan="6"></td>
                        <td class="text-right"><?php echo $fsc->show_precio($fsc->albaran->neto, $fsc->albaran->coddivisa);?></td>
                        <td class="text-right"><?php echo $fsc->show_precio($fsc->albaran->totaliva, $fsc->albaran->coddivisa);?></td>
                        <td class="recargo text-right"><?php echo $fsc->show_precio($fsc->albaran->totalrecargo, $fsc->albaran->coddivisa);?></td>
                        <td class="irpf text-right"><?php echo $fsc->show_precio($fsc->albaran->totalirpf, $fsc->albaran->coddivisa);?></td>
                        <td class="text-right"><?php echo $fsc->show_precio($fsc->albaran->total, $fsc->albaran->coddivisa);?></td>
                     </tr>
                     <?php } ?>

                  </tbody>
               </table>
               <?php }else{ ?>

               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th width="40"></th>
                        <th class="text-left">Artículo</th>
                        <th class="text-right" width="70">Cantidad</th>
                        <th class="text-right" width="80">Precio</th>
                        <th class="text-right" width="70">Dto</th>
                        <th class="text-right" width="80">Neto</th>
                        <th class="text-right" width="70"><?php  echo FS_IVA;?></th>
                        <th class="text-right recargo" width="70">RE</th>
                        <th class="text-right irpf" width="70"><?php  echo FS_IRPF;?></th>
                        <th class="text-right" width="90">Total</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->albaran->get_lineas(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr<?php if( $value1->cantidad<=0 ){ ?> class="warning"<?php } ?>>
                     <td>
                        <?php if( $value1->idlineapedido ){ ?>

                        <a target="_blank" href="index.php?page=ventas_pedido&id=<?php echo $value1->idpedido;?>" class="btn btn-xs btn-default">
                           <span class="glyphicon glyphicon-paperclip" aria-hidden="true" title="ver <?php  echo FS_PEDIDO;?>"></span>
                        </a>
                        <?php } ?>

                     </td>
                     <td>
                        <a href="<?php echo $value1->articulo_url();?>"><?php echo $value1->referencia;?></a> <?php echo $value1->descripcion();?>

                     </td>
                     <td class="text-right"><?php echo $value1->cantidad;?></td>
                     <td class="text-right"><?php echo $fsc->show_precio($value1->pvpunitario, $fsc->albaran->coddivisa);?></td>
                     <td class="text-right"><?php echo $fsc->show_numero($value1->dtopor, 2);?> %</td>
                     <td class="text-right"><?php echo $fsc->show_precio($value1->pvptotal, $fsc->albaran->coddivisa);?></td>
                     <td class="text-right"><?php echo $fsc->show_numero($value1->iva, 2);?> %</td>
                     <td class="text-right recargo"><?php echo $fsc->show_numero($value1->recargo, 2);?> %</td>
                     <td class="text-right irpf"><?php echo $fsc->show_numero($value1->irpf, 2);?> %</td>
                     <td class="text-right"><?php echo $fsc->show_precio($value1->total_iva(), $fsc->albaran->coddivisa);?></td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td colspan="10">
                        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                        &nbsp; No hay líneas.
                     </td>
                  </tr>
                  <?php } ?>

                  <tr>
                     <?php if( $fsc->albaran->coddivisa!=$fsc->empresa->coddivisa ){ ?>

                     <td colspan="5" class="text-right warning"><b><?php echo $fsc->albaran->coddivisa;?>:</b></td>
                     <?php }else{ ?>

                     <td colspan="5"></td>
                     <?php } ?>

                     <td class="text-right"><b><?php echo $fsc->show_precio($fsc->albaran->neto, $fsc->albaran->coddivisa);?></b></td>
                     <td class="text-right"><b><?php echo $fsc->show_precio($fsc->albaran->totaliva, $fsc->albaran->coddivisa);?></b></td>
                     <td class="text-right recargo"><b><?php echo $fsc->show_precio($fsc->albaran->totalrecargo, $fsc->albaran->coddivisa);?></b></td>
                     <td class="text-right irpf"><b>-<?php echo $fsc->show_precio($fsc->albaran->totalirpf, $fsc->albaran->coddivisa);?></b></td>
                     <td class="text-right"><b><?php echo $fsc->show_precio($fsc->albaran->total, $fsc->albaran->coddivisa);?></b></td>
                  </tr>
               </table>
               <?php } ?>

            </div>
            
            <div class="container-fluid">
               <div class="row">
                  <div class="col-sm-12">
                     <div class="form-group">
                        Observaciones:
                        <textarea class="form-control" name="observaciones" rows="3"><?php echo $fsc->albaran->observaciones;?></textarea>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div role="tabpanel" class="tab-pane" id="detalles">
            <div class="container-fluid" style="margin-top: 10px;">
               <div class="row">
                  <div class="col-sm-3">
                     <div class="form-group">
                        Nombre del cliente:
                        <input class="form-control" type="text" name="nombrecliente" value="<?php echo $fsc->albaran->nombrecliente;?>" autocomplete="off"/>
                     </div>
                  </div>
                  <div class="col-sm-2">
                     <div class="form-group">
                        <?php  echo FS_CIFNIF;?>:
                        <input class="form-control" type="text" name="cifnif" value="<?php echo $fsc->albaran->cifnif;?>" autocomplete="off"/>
                     </div>
                  </div>
                  <div class="col-sm-3">
                     <div class="form-group">
                        <a href="<?php echo $fsc->forma_pago->url();?>">Forma de pago</a>:
                        <select name="forma_pago" class="form-control">
                           <?php $loop_var1=$fsc->forma_pago->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <option value="<?php echo $value1->codpago;?>"<?php if( $value1->codpago==$fsc->albaran->codpago ){ ?> selected=""<?php } ?>><?php echo $value1->descripcion;?></option>
                           <?php } ?>

                        </select>
                     </div>
                  </div>
                  <div class="col-sm-2">
                     <div class="form-group">
                        <a href="<?php echo $fsc->divisa->url();?>">Divisa</a>:
                        <select name="divisa" class="form-control">
                        <?php $loop_var1=$fsc->divisa->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <?php if( $value1->coddivisa==$fsc->albaran->coddivisa ){ ?>

                           <option value="<?php echo $value1->coddivisa;?>" selected=""><?php echo $value1->descripcion;?></option>
                           <?php }else{ ?>

                           <option value="<?php echo $value1->coddivisa;?>"><?php echo $value1->descripcion;?></option>
                           <?php } ?>

                        <?php } ?>

                        </select>
                     </div>
                  </div>
                  <div class="col-sm-2">
                     <div class="form-group">
                        Tasa de conversión (1€ = X)
                        <input type="text" name="tasaconv" class="form-control" placeholder="<?php echo $fsc->albaran->tasaconv;?>" autocomplete="off"/>
                     </div>
                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-12">
                     <h3>
                        <span class="glyphicon glyphicon-road" aria-hidden="true"></span>
                        &nbsp; Dirección de facturación:
                     </h3>
                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-3">
                     <div class="form-group">
                        <a href="<?php echo $fsc->pais->url();?>">País</a>:
                        <select class="form-control" name="codpais">
                           <?php $loop_var1=$fsc->pais->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <option value="<?php echo $value1->codpais;?>"<?php if( $value1->codpais==$fsc->albaran->codpais ){ ?> selected=""<?php } ?>><?php echo $value1->nombre;?></option>
                           <?php } ?>

                        </select>
                     </div>
                  </div>
                  <div class="col-sm-3">
                     <div class="form-group">
                        <span class="text-capitalize"><?php  echo FS_PROVINCIA;?></span>:
                        <input id="ac_provincia" class="form-control" type="text" name="provincia" value="<?php echo $fsc->albaran->provincia;?>"/>
                     </div>
                  </div>
                  <div class="col-sm-3">
                     <div class="form-group">
                        Ciudad:
                        <input class="form-control" type="text" name="ciudad" value="<?php echo $fsc->albaran->ciudad;?>"/>
                     </div>
                  </div>
                  <div class="col-sm-3">
                     <div class="form-group">
                        Código Postal:
                        <input class="form-control" type="text" name="codpostal" value="<?php echo $fsc->albaran->codpostal;?>"/>
                     </div>
                  </div>
                  <div class="col-sm-6">
                     <div class="form-group">
                        Dirección:
                        <input class="form-control" type="text" name="direccion" value="<?php echo $fsc->albaran->direccion;?>" autocomplete="off"/>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <?php if( $value1->type=='tab' ){ ?>

            <div role="tabpanel" class="tab-pane" id="ext_<?php echo $value1->name;?>">
               <iframe src="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>&id=<?php echo $fsc->albaran->idalbaran;?>" width="100%" height="2000" frameborder="0"></iframe>
            </div>
            <?php } ?>

         <?php } ?>

      </div>
   </div>
</form>

<div class="modal" id="modal_articulos">
   <div class="modal-dialog" style="width: 99%; max-width: 1000px;">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Buscar artículos</h4>
         </div>
         <div class="modal-body">
            <form id="f_buscar_articulos" name="f_buscar_articulos" action="<?php echo $fsc->url();?>" method="post" class="form">
               <input type="hidden" name="codcliente" value="<?php echo $fsc->albaran->codcliente;?>"/>
               <input type="hidden" name="codalmacen" value="<?php echo $fsc->albaran->codalmacen;?>"/>
               <div class="container-fluid">
                  <div class="row">
                     <div class="col-sm-4">
                        <div class="input-group">
                           <input class="form-control" type="text" name="query" autocomplete="off"/>
                           <span class="input-group-btn">
                              <button class="btn btn-primary" type="submit">
                                 <span class="glyphicon glyphicon-search"></span>
                              </button>
                           </span>
                        </div>
                        <label>
                           <input type="checkbox" name="con_stock" value="TRUE" onchange="buscar_articulos()"/>
                           sólo con stock
                        </label>
                     </div>
                     <div class="col-sm-4">
                        <select class="form-control" name="codfamilia" onchange="buscar_articulos()">
                           <option value="">Cualquier familia</option>
                           <option value="">------</option>
                           <?php $loop_var1=$fsc->familia->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <option value="<?php echo $value1->codfamilia;?>"><?php echo $value1->nivel;?><?php echo $value1->descripcion;?></option>
                           <?php } ?>

                        </select>
                     </div>
                     <div class="col-sm-4">
                        <select class="form-control" name="codfabricante" onchange="buscar_articulos()">
                           <option value="">Cualquier fabricante</option>
                           <option value="">------</option>
                           <?php $loop_var1=$fsc->fabricante->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <option value="<?php echo $value1->codfabricante;?>"><?php echo $value1->nombre;?></option>
                           <?php } ?>

                        </select>
                     </div>
                  </div>
               </div>
            </form>
         </div>
         <ul class="nav nav-tabs" id="nav_articulos" style="display: none;">
            <li id="li_mis_articulos">
               <a href="#" id="b_mis_articulos">Mi catálogo</a>
            </li>
            <li id="li_kiwimaru">
               <a href="#" id="b_kiwimaru">
                  <span class="glyphicon glyphicon-globe"></span>
               </a>
            </li>
            <li id="li_nuevo_articulo">
               <a href="#" id="b_nuevo_articulo">
                  <span class="glyphicon glyphicon-plus"></span> &nbsp; Nuevo
               </a>
            </li>
         </ul>
         <div id="search_results"></div>
         <div id="kiwimaru_results"></div>
         <div id="nuevo_articulo" class="modal-body" style="display: none;">
            <form name="f_nuevo_articulo" action="<?php echo $fsc->url();?>" method="post" class="form">
               <div class="container-fluid">
                  <div class="row">
                     <div class="col-sm-3">
                        <div class="form-group">
                           Referencia:
                           <input class="form-control" type="text" name="referencia" maxlength="18" autocomplete="off"/>
                        </div>
                     </div>
                     <div class="col-sm-6">
                        <div class="form-group">
                           Descripción:
                           <textarea name="descripcion" rows="1" class="form-control"></textarea>
                        </div>
                     </div>
                     <div class="col-sm-3">
                        <div class="form-group">
                           <a href="<?php echo $fsc->familia->url();?>">Familia</a>:
                           <select name="codfamilia" class="form-control">
                              <option value="">Ninguna</option>
                              <option value="">------</option>
                              <?php $loop_var1=$fsc->familia->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                              <option value="<?php echo $value1->codfamilia;?>"><?php echo $value1->nivel;?><?php echo $value1->descripcion;?></option>
                              <?php } ?>

                           </select>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-sm-4">
                        <div class="form-group">
                           <a href="<?php echo $fsc->fabricante->url();?>">Fabricante</a>:
                           <select name="codfabricante" class="form-control">
                              <option value="">Ninguno</option>
                              <option value="">------</option>
                              <?php $loop_var1=$fsc->fabricante->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                              <option value="<?php echo $value1->codfabricante;?>"><?php echo $value1->nombre;?></option>
                              <?php } ?>

                           </select>
                        </div>
                     </div>
                     <div class="col-sm-4">
                        <div class="form-group">
                           <a href="<?php echo $fsc->impuesto->url();?>"><?php  echo FS_IVA;?></a>:
                           <select name="codimpuesto" class="form-control">
                              <?php $loop_var1=$fsc->impuesto->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                              <option value="<?php echo $value1->codimpuesto;?>"<?php if( $value1->is_default() ){ ?> selected=""<?php } ?>><?php echo $value1->descripcion;?></option>
                              <?php } ?>

                           </select>
                        </div>
                     </div>
                     <div class="col-sm-4">
                        <div class="form-group">
                           Precio de venta:
                           <input type="text" name="pvp" value="0" class="form-control" autocomplete="off"/>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-sm-12 text-right">
                        <button class="btn btn-sm btn-primary" type="submit" onclick="new_articulo();return false;">
                           <span class="glyphicon glyphicon-floppy-disk"></span> &nbsp; Guardar y seleccionar
                        </button>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
</div>

<div class="modal fade" id="modal_imprimir_albaran">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Imprimir <?php  echo FS_ALBARAN;?></h4>
            <p class="help-block">
               Más formatos en la <a href="https://www.facturascripts.com/store" target="_blank">tienda de plugins</a>.
            </p>
         </div>
         <div class="modal-body">
         <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

            <?php if( $value1->type=='pdf' ){ ?>

            <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>&id=<?php echo $fsc->albaran->idalbaran;?>" target="_blank" class="btn btn-block btn-default">
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

<form class="form" role="form" name="enviar_email" action="<?php echo $fsc->url();?>" method="post">
   <div class="modal" id="modal_enviar">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
               <h4 class="modal-title">Enviar <?php  echo FS_ALBARAN;?></h4>
               <?php if( $fsc->albaran->femail ){ ?>

               <p class="help-block">
                  <span class="glyphicon glyphicon-send"></span> &nbsp;
                  Este <?php  echo FS_ALBARAN;?> fue enviado el <?php echo $fsc->albaran->femail;?>.
               </p>
               <?php } ?>

            </div>
            <div class="modal-body">
               <div class="form-group">
                  Email del cliente:
                  <input class="form-control" type="text" name="email" value="<?php echo $fsc->cliente_s->email;?>" autocomplete="off"/>
               </div>
               <div class="form-group">
                  <label>
                     <input id="copia" onclick="check_enviar_copia()" type="checkbox" name="concopia" value="TRUE"/>
                     Enviar copia:
                  </label>
                  <input id="checked_copia" class="form-control" type="text" name="email_copia" autocomplete="off" disabled=""/>
               </div>
               <div class="form-group">
                  Mensaje:
                  <textarea class="form-control" name="mensaje" rows="6">Buenos días, le adjunto su <?php  echo FS_ALBARAN;?> <?php echo $fsc->albaran->codigo;?>.
<?php echo $fsc->empresa->email_firma;?></textarea>
               </div>
               <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <?php if( $value1->type=='email' ){ ?>

                  <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.action='index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>&id=<?php echo $fsc->albaran->idalbaran;?>';this.form.submit();">
                     <span class="glyphicon glyphicon-send"></span> &nbsp; <?php echo $value1->text;?>

                  </button>
                  <?php } ?>

               <?php } ?>

            </div>
         </div>
      </div>
   </div>
</form>

<form class="form" role="form" action="<?php echo $fsc->url();?>" method="post">
   <input type="hidden" name="petid" value="<?php echo $fsc->random_string();?>"/>
   <div class="modal" id="modal_aprobar">
      <div class="modal-dialog modal-sm">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
               <h4 class="modal-title">Aprobar <?php  echo FS_ALBARAN;?></h4>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  Fecha de la <?php  echo FS_FACTURA;?>:
                  <input class="form-control datepicker" type="text" name="facturar" value="<?php echo $fsc->today();?>" autocomplete="off"/>
                  <p class="help-block">
                     Se generará una <?php  echo FS_FACTURA;?>. Si deseas aprobar de golpe todos
                     los <?php  echo FS_ALBARANES;?> pendientes, puedes usar el plugin <b>megafacturador</b>.
                  </p>
               </div>
               <div class="text-right">
                  <button class="btn btn-sm btn-primary" onclick="this.disabled=true;this.form.submit();">
                     <span class="glyphicon glyphicon-paperclip"></span> &nbsp; Aprobar
                  </button>
               </div>
            </div>
         </div>
      </div>
   </div>
</form>

<form action="<?php echo $fsc->ppage->url();?>" method="post">
   <input type="hidden" name="delete" value="<?php echo $fsc->albaran->idalbaran;?>"/>
   <div class="modal fade" id="modal_eliminar">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
               <h4 class="modal-title">¿Realmente desea eliminar este <?php  echo FS_ALBARAN;?>?</h4>
            </div>
            <?php if( $fsc->albaran->idfactura ){ ?>

            <div class="modal-body bg-warning">
               Hay una <?php  echo FS_FACTURA;?> asociada que será eliminada junto con este <?php  echo FS_ALBARAN;?>.
            </div>
            <?php } ?>

            <div class="modal-footer">
               <div class="pull-left">
                  <label>
                     <input type="checkbox" name="stock" value="TRUE" checked="checked"/>
                     Actualizar el stock
                  </label>
               </div>
               <button class="btn btn-sm btn-danger" onclick="this.disabled=true;this.form.submit();">
                  <span class="glyphicon glyphicon-trash"></span> &nbsp; Eliminar
               </button>
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