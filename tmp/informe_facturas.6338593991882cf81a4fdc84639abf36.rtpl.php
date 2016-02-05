<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
   google.load('visualization', '1.0', {'packages':['corechart']});
   
   function clean_cliente()
   {
      document.listado_facturas.codcliente.value = '';
      document.informe_facturas.codcliente.value = '';
      document.listado_facturas.ac_cliente.value = '';
      document.informe_facturas.ac_cliente2.value = '';
   }
   function clean_proveedor()
   {
      document.listado_proveedor.codproveedor.value = '';
      document.informe_proveedor.codproveedor.value = '';
      document.listado_proveedor.ac_proveedor.value = '';
      document.informe_proveedor.ac_proveedor2.value = '';
   }
   $(document).ready(function() {
      $("#ac_cliente").autocomplete({
         serviceUrl: '<?php echo $fsc->url();?>',
         paramName: 'buscar_cliente',
         onSelect: function (suggestion) {
            if(suggestion)
            {
               if(document.listado_facturas.codcliente.value != suggestion.data && suggestion.data != '')
               {
                  document.listado_facturas.codcliente.value = suggestion.data;
               }
            }
         }
      });
      $("#ac_cliente2").autocomplete({
         serviceUrl: '<?php echo $fsc->url();?>',
         paramName: 'buscar_cliente',
         onSelect: function (suggestion) {
            if(suggestion)
            {
               if(document.informe_facturas.codcliente.value != suggestion.data && suggestion.data != '')
               {
                  document.informe_facturas.codcliente.value = suggestion.data;
               }
            }
         }
      });
      $("#ac_proveedor").autocomplete({
         serviceUrl: '<?php echo $fsc->url();?>',
         paramName: 'buscar_proveedor',
         onSelect: function(suggestion) {
            if(suggestion)
            {
               if(document.listado_proveedor.codproveedor.value != suggestion.data && suggestion.data != '')
               {
                  document.listado_proveedor.codproveedor.value = suggestion.data;
               }
            }
         }
      });
      $("#ac_proveedor2").autocomplete({
         serviceUrl: '<?php echo $fsc->url();?>',
         paramName: 'buscar_proveedor',
         onSelect: function(suggestion) {
            if(suggestion)
            {
               if(document.informe_proveedor.codproveedor.value != suggestion.data && suggestion.data != '')
               {
                  document.informe_proveedor.codproveedor.value = suggestion.data;
               }
            }
         }
      });
   });
</script>

<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12">
         <div class="page-header">
            <h1>
               <span class="glyphicon glyphicon-stats" aria-hidden="true"></span>
               Informe de facturas
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
            <p class="help-block">
               Haz clic en <b>facturas de compra</b> o en <b>facturas de venta</b>
               para exportar, en PDF o CSV, el listado de facturas filtradas.
               O para generar informes de compras o ventas con múltiples filtros.
            </p>
         </div>
         <ul class="nav nav-pills">
            <li role="presentation"<?php if( $fsc->mostrar=='general' ){ ?> class="active"<?php } ?>>
               <a href="<?php echo $fsc->url();?>">
                  <span class="glyphicon glyphicon-stats" aria-hidden="true"></span>
                  <span class="hidden-xs">&nbsp; General</span>
               </a>
            </li>
            <li role="presentation"<?php if( $fsc->mostrar=='agrupaciones' ){ ?> class="active"<?php } ?>>
               <a href="<?php echo $fsc->url();?>&mostrar=agrupaciones">
                  <span class="glyphicon glyphicon-compressed" aria-hidden="true"></span>
                  <span class="hidden-xs">&nbsp; Agrupaciones</span>
               </a>
            </li>
            <li role="presentation"<?php if( $fsc->mostrar=='operaciones' ){ ?> class="active"<?php } ?>>
               <a href="<?php echo $fsc->url();?>&mostrar=operaciones">
                  <span class="glyphicon glyphicon-fire" aria-hidden="true"></span>
                  <span class="hidden-xs">&nbsp; Actividad</span>
               </a>
            </li>
            <li role="presentation"<?php if( $fsc->mostrar=='compras' ){ ?> class="active"<?php } ?>>
               <a href="<?php echo $fsc->url();?>&mostrar=compras">
                  <span class="glyphicon glyphicon-duplicate" aria-hidden="true"></span>
                  <span class="hidden-xs">&nbsp; Facturas de compra</span>
               </a>
            </li>
            <li role="presentation"<?php if( $fsc->mostrar=='ventas' ){ ?> class="active"<?php } ?>>
               <a href="<?php echo $fsc->url();?>&mostrar=ventas">
                  <span class="glyphicon glyphicon-duplicate" aria-hidden="true"></span>
                  <span class="hidden-xs">&nbsp; Facturas de venta</span>
               </a>
            </li>
         </ul>
      </div>
   </div>
</div>

<?php if( $fsc->mostrar=='general' ){ ?>

<script type="text/javascript">
   google.setOnLoadCallback(drawChart);
   
   function drawChart()
   {
      // Create the data table.
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'día');
      data.addColumn('number', 'ventas <?php echo $fsc->simbolo_divisa();?>');
      data.addColumn('number', 'compras <?php echo $fsc->simbolo_divisa();?>');
      data.addRows([
      <?php $loop_var1=$fsc->stats_last_days(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $value1['day'];?>', <?php echo $value1['total_cli'];?>, <?php echo $value1['total_pro'];?>],
      <?php } ?>

      ]);
      
      // Instantiate and draw our chart, passing in some options.
      var chart = new google.visualization.ColumnChart(document.getElementById('chart_facturas_day'));
      chart.draw(data);
      
      // Create the data table.
      var data2 = new google.visualization.DataTable();
      data2.addColumn('string', 'mes');
      data2.addColumn('number', 'ventas <?php echo $fsc->simbolo_divisa();?>');
      data2.addColumn('number', 'compras <?php echo $fsc->simbolo_divisa();?>');
      data2.addColumn('number', 'beneficios <?php echo $fsc->simbolo_divisa();?>');
      data2.addRows([
      <?php $loop_var1=$fsc->stats_last_months(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $value1['month'];?>', <?php echo $value1['total_cli'];?>, <?php echo $value1['total_pro'];?>, <?php echo $value1['beneficios'];?>],
      <?php } ?>

      ]);
      
      // Instantiate and draw our chart, passing in some options.
      var chart2 = new google.visualization.AreaChart(document.getElementById('chart_facturas_month'));
      chart2.draw(data2);
      
      // Create the data table.
      var data3 = new google.visualization.DataTable();
      data3.addColumn('string', 'año');
      data3.addColumn('number', 'ventas <?php echo $fsc->simbolo_divisa();?>');
      data3.addColumn('number', 'compras <?php echo $fsc->simbolo_divisa();?>');
      data3.addColumn('number', 'beneficios <?php echo $fsc->simbolo_divisa();?>');
      data3.addRows([
      <?php $loop_var1=$fsc->stats_last_years(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $value1['year'];?>', <?php echo $value1['total_cli'];?>, <?php echo $value1['total_pro'];?>, <?php echo $value1['beneficios'];?>],
      <?php } ?>

      ]);
      
      // Instantiate and draw our chart, passing in some options.
      var chart3 = new google.visualization.AreaChart(document.getElementById('chart_facturas_year'));
      chart3.draw(data3);
   }
</script>

<div class="container-fluid" style="margin-top: 10px;">
   <div class="row">
      <div class="col-sm-12">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Facturación de los últimos días</h3>
            </div>
            <div class="panel-body">
               <div id="chart_facturas_day"></div>
            </div>
            <div class="panel-footer">
               <p class="help-block">
                  Este mes hay <?php echo $fsc->stats['facturas_compra'];?> facturas de compra por un importe total
                  de <b><?php echo $fsc->show_precio($fsc->stats['facturas_compra_importe']);?></b>,
                  además de <?php echo $fsc->stats['alb_ptes_compra'];?> <?php  echo FS_ALBARANES;?> de compra pendientes
                  por un importe de <b><?php echo $fsc->show_precio($fsc->stats['alb_ptes_compra_importe']);?></b>.
                  En todo el mes hay <?php echo $fsc->stats['facturas_venta'];?> facturas de venta
                  por un importe total de <b><?php echo $fsc->show_precio($fsc->stats['facturas_venta_importe']);?></b>,
                  además de <?php echo $fsc->stats['alb_ptes_venta'];?> <?php  echo FS_ALBARANES;?> de venta pendientes
                  por un importe de <b><?php echo $fsc->show_precio($fsc->stats['alb_ptes_venta_importe']);?></b>.
                  Si lo sumamos todo, tenemos un importe de <mark><?php echo $fsc->show_precio($fsc->stats['total']);?></mark>.
               </p>
            </div>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-sm-12">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Facturación de los últimos meses</h3>
            </div>
            <div class="panel-body">
               <div id="chart_facturas_month" style="height: 400px;"></div>
            </div>
            <div class="panel-footer">
               <p class="help-block">
                  La media de ventas es de <b><?php echo $fsc->show_precio($fsc->stats['media_ventas']);?></b>/mes,
                  la media de compras es de <b><?php echo $fsc->show_precio($fsc->stats['media_compras']);?></b>/mes
                  y el benefinicio medio es <b><?php echo $fsc->show_precio($fsc->stats['media_beneficios']);?></b>/mes.
               </p>
            </div>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-sm-12">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Facturación de los últimos años</h3>
            </div>
            <div class="panel-body">
               <div id="chart_facturas_year" style="height: 400px;"></div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php }elseif( $fsc->mostrar=='agrupaciones' ){ ?>

<script type="text/javascript">
   google.setOnLoadCallback(drawChart);
   
   function drawChart()
   {
      var options = {
         is3D: true,
      };
      
      var data = google.visualization.arrayToDataTable([
         ['TXT', 'Total'],
         <?php $loop_var1=$fsc->stats_impagos(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $value1['txt'];?>', <?php echo $value1['total'];?>],
         <?php } ?>

        ]);
      
      var chart = new google.visualization.PieChart(document.getElementById('chart_compras_impagos'));
      chart.draw(data, options);
      
      var data2 = google.visualization.arrayToDataTable([
         ['TXT', 'Total'],
         <?php $loop_var1=$fsc->stats_impagos('facturascli'); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $value1['txt'];?>', <?php echo $value1['total'];?>],
         <?php } ?>

        ]);
      
      var chart2 = new google.visualization.PieChart(document.getElementById('chart_ventas_impagos'));
      chart2.draw(data2, options);
      
      var data3 = google.visualization.arrayToDataTable([
         ['TXT', 'Total'],
         <?php $loop_var1=$fsc->stats_series(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $value1['txt'];?>', <?php echo $value1['total'];?>],
         <?php } ?>

        ]);
      
      var chart3 = new google.visualization.PieChart(document.getElementById('chart_compras_series'));
      chart3.draw(data3, options);
      
      var data4 = google.visualization.arrayToDataTable([
         ['TXT', 'Total'],
         <?php $loop_var1=$fsc->stats_series('facturascli'); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $value1['txt'];?>', <?php echo $value1['total'];?>],
         <?php } ?>

        ]);
      
      var chart4 = new google.visualization.PieChart(document.getElementById('chart_ventas_series'));
      chart4.draw(data4, options);
      
      var data5 = google.visualization.arrayToDataTable([
         ['TXT', 'Total'],
         <?php $loop_var1=$fsc->stats_formas_pago(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $value1['txt'];?>', <?php echo $value1['total'];?>],
         <?php } ?>

        ]);
      
      var chart5 = new google.visualization.PieChart(document.getElementById('chart_compras_formas_pago'));
      chart5.draw(data5, options);
      
      var data6 = google.visualization.arrayToDataTable([
         ['TXT', 'Total'],
         <?php $loop_var1=$fsc->stats_formas_pago('facturascli'); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $value1['txt'];?>', <?php echo $value1['total'];?>],
         <?php } ?>

        ]);
      
      var chart6 = new google.visualization.PieChart(document.getElementById('chart_ventas_formas_pago'));
      chart6.draw(data6, options);
      
      var data7 = google.visualization.arrayToDataTable([
         ['TXT', 'Total'],
         <?php $loop_var1=$fsc->stats_almacenes(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $value1['txt'];?>', <?php echo $value1['total'];?>],
         <?php } ?>

        ]);
      
      var chart7 = new google.visualization.PieChart(document.getElementById('chart_compras_almacenes'));
      chart7.draw(data7, options);
      
      var data8 = google.visualization.arrayToDataTable([
         ['TXT', 'Total'],
         <?php $loop_var1=$fsc->stats_almacenes('facturascli'); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $value1['txt'];?>', <?php echo $value1['total'];?>],
         <?php } ?>

        ]);
      
      var chart8 = new google.visualization.PieChart(document.getElementById('chart_ventas_almacenes'));
      chart8.draw(data8, options);
   }
</script>

<div class="container-fluid" style="margin-top: 10px;">
   <div class="row">
      <div class="col-sm-6">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Impagos en compras</h3>
            </div>
            <div class="panel-body">
               <div id="chart_compras_impagos"></div>
            </div>
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th>Campo</th>
                        <th class="text-right">Total</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->stats_impagos(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr>
                     <td><?php echo $value1['txt'];?></td>
                     <td class="text-right"><?php echo $value1['total'];?></td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td colspan="2">Sin resultados.</td>
                  </tr>
                  <?php } ?>

               </table>
            </div>
         </div>
      </div>
      <div class="col-sm-6">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Impagos en ventas</h3>
            </div>
            <div class="panel-body">
               <div id="chart_ventas_impagos"></div>
            </div>
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th>Campo</th>
                        <th class="text-right">Total</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->stats_impagos('facturascli'); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr>
                     <td><?php echo $value1['txt'];?></td>
                     <td class="text-right"><?php echo $value1['total'];?></td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td colspan="2">Sin resultados.</td>
                  </tr>
                  <?php } ?>

               </table>
            </div>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-sm-6">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Compras por serie</h3>
            </div>
            <div class="panel-body">
               <div id="chart_compras_series"></div>
            </div>
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th>Campo</th>
                        <th class="text-right">Total</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->stats_series(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr>
                     <td><?php echo $value1['txt'];?></td>
                     <td class="text-right"><?php echo $value1['total'];?></td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td colspan="2">Sin resultados.</td>
                  </tr>
                  <?php } ?>

               </table>
            </div>
         </div>
      </div>
      <div class="col-sm-6">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Ventas por serie</h3>
            </div>
            <div class="panel-body">
               <div id="chart_ventas_series"></div>
            </div>
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th>Campo</th>
                        <th class="text-right">Total</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->stats_series('facturascli'); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr>
                     <td><?php echo $value1['txt'];?></td>
                     <td class="text-right"><?php echo $value1['total'];?></td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td colspan="2">Sin resultados.</td>
                  </tr>
                  <?php } ?>

               </table>
            </div>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-sm-6">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Compras por forma de pago</h3>
            </div>
            <div class="panel-body">
               <div id="chart_compras_formas_pago"></div>
            </div>
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th>Campo</th>
                        <th class="text-right">Total</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->stats_formas_pago(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr>
                     <td><?php echo $value1['txt'];?></td>
                     <td class="text-right"><?php echo $value1['total'];?></td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td colspan="2">Sin resultados.</td>
                  </tr>
                  <?php } ?>

               </table>
            </div>
         </div>
      </div>
      <div class="col-sm-6">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Ventas por forma de pago</h3>
            </div>
            <div class="panel-body">
               <div id="chart_ventas_formas_pago"></div>
            </div>
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th>Campo</th>
                        <th class="text-right">Total</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->stats_formas_pago('facturascli'); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr>
                     <td><?php echo $value1['txt'];?></td>
                     <td class="text-right"><?php echo $value1['total'];?></td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td colspan="2">Sin resultados.</td>
                  </tr>
                  <?php } ?>

               </table>
            </div>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-sm-6">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Compras por almacén</h3>
            </div>
            <div class="panel-body">
               <div id="chart_compras_almacenes"></div>
            </div>
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th>Campo</th>
                        <th class="text-right">Total</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->stats_almacenes(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr>
                     <td><?php echo $value1['txt'];?></td>
                     <td class="text-right"><?php echo $value1['total'];?></td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td colspan="2">Sin resultados.</td>
                  </tr>
                  <?php } ?>

               </table>
            </div>
         </div>
      </div>
      <div class="col-sm-6">
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Ventas por almacén</h3>
            </div>
            <div class="panel-body">
               <div id="chart_ventas_almacenes"></div>
            </div>
            <div class="table-responsive">
               <table class="table table-hover">
                  <thead>
                     <tr>
                        <th>Campo</th>
                        <th class="text-right">Total</th>
                     </tr>
                  </thead>
                  <?php $loop_var1=$fsc->stats_almacenes('facturascli'); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <tr>
                     <td><?php echo $value1['txt'];?></td>
                     <td class="text-right"><?php echo $value1['total'];?></td>
                  </tr>
                  <?php }else{ ?>

                  <tr class="warning">
                     <td colspan="2">Sin resultados.</td>
                  </tr>
                  <?php } ?>

               </table>
            </div>
         </div>
      </div>
   </div>
</div>
<?php }elseif( $fsc->mostrar=='operaciones' ){ ?>

<script type="text/javascript">
   google.setOnLoadCallback(drawChart);
   
   function drawChart()
   {
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'día');
      data.addColumn('number', 'diario');
      data.addColumn('number', 'semanal');
      data.addRows([
      <?php $loop_var1=$fsc->stats_last_operations(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $key1;?>', <?php echo $value1['diario'];?>, <?php echo $value1['semanal'];?>],
      <?php } ?>

      ]);
      
      var chart = new google.visualization.AreaChart(document.getElementById('chart_operaciones_compra'));
      chart.draw(data);
      
      var data2 = new google.visualization.DataTable();
      data2.addColumn('string', 'día');
      data2.addColumn('number', 'diario');
      data2.addColumn('number', 'semanal');
      data2.addRows([
      <?php $loop_var1=$fsc->stats_last_operations('albaranescli'); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         ['<?php echo $key1;?>', <?php echo $value1['diario'];?>, <?php echo $value1['semanal'];?>],
      <?php } ?>

      ]);
      
      var chart2 = new google.visualization.AreaChart(document.getElementById('chart_operaciones_venta'));
      chart2.draw(data2);
   }
</script>

<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12">
         <br/>
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Operaciones de compra</h3>
            </div>
            <div class="panel-body">
               <div id="chart_operaciones_compra" style="width: 95%; margin-left: auto; margin-right: auto; height: 300px;"></div>
            </div>
            <div class="panel-footer">
               <p class="help-block">
                  Nº de facturas de compra diarias y semanales. Útil para medir la actividad.
               </p>
            </div>
         </div>
         <div class="panel panel-default">
            <div class="panel-heading">
               <h3 class="panel-title">Operaciones de venta</h3>
            </div>
            <div class="panel-body">
               <div id="chart_operaciones_venta" style="width: 95%; margin-left: auto; margin-right: auto; height: 300px;"></div>
            </div>
            <div class="panel-footer">
               <p class="help-block">
                  Nº de facturas de venta diarias y semanales. Útil para medir la actividad.
               </p>
            </div>
         </div>
      </div>
   </div>
</div>
<?php }elseif( $fsc->mostrar=='compras' ){ ?>

<br/>
<div class="container-fluid">
   <div class="row">
      <div class="col-md-4 col-sm-6">
         <form name="listado_proveedor" action="<?php echo $fsc->url();?>&mostrar=compras" method="post" target="_blank" class="form">
            <input type="hidden" name="listado" value="facturasprov"/>
            <input type="hidden" name="codproveedor"/>
            <div class="panel panel-default">
               <div class="panel-heading">
                  <h3 class="panel-title">
                     <span class="glyphicon glyphicon-list" aria-hidden="true"></span>
                     &nbsp; Listado de facturas de compra
                  </h3>
               </div>
               <div class="panel-body">
                  <div class="form-group">
                     Desde:
                     <input class="form-control datepicker" type="text" name="desde" value="<?php echo $fsc->desde;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group">
                     Hasta:
                     <input class="form-control datepicker" type="text" name="hasta" value="<?php echo $fsc->hasta;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group">
                     <a href="<?php echo $fsc->serie->url();?>">Serie</a>:
                     <select class="form-control" name="codserie">
                        <option value="">Todas</option>
                        <option value="">-----</option>
                        <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codserie;?>"><?php echo $value1->descripcion;?></option>
                        <?php } ?>

                     </select>
                  </div>
                  <div class="form-group">
                     <a href="<?php echo $fsc->agente->url();?>">Empleado</a>:
                     <select name="codagente" class="form-control">
                        <option value="">Todos</option>
                        <option value="">------</option>
                        <?php $loop_var1=$fsc->agente->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codagente;?>"><?php echo $value1->get_fullname();?></option>
                        <?php } ?>

                     </select>
                  </div>
                  <div class="form-group">
                     Proveedor:
                     <div class="input-group">
                        <input class="form-control" type="text" name="ac_proveedor" id="ac_proveedor" placeholder="Buscar" autocomplete="off"/>
                        <span class="input-group-btn">
                           <button class="btn btn-default" type="button" onclick="clean_proveedor();document.listado_proveedor.ac_proveedor.focus();">
                              <span class="glyphicon glyphicon-edit"></span>
                           </button>
                        </span>
                     </div>
                     <p class="help-block">Dejar en blanco para seleccionar todos los proveedores.</p>
                  </div>
                  <div class="form-group">
                     Estado de la factura:
                     <select class="form-control" name="estado">
                        <option value="">Todas</option>
                        <option value="pagada">Pagada</option>
                        <option value="sinpagar">Sin pagar</option>
                     </select>
                  </div>
                  <div class="form-group">
                     Generar:
                     <select name="generar" class="form-control">
                        <option value="pdf">PDF</option>
                        <option value="csv">CSV</option>
                     </select>
                  </div>
               </div>
               <div class="panel-footer">
                  <button class="btn btn-sm btn-primary" type="submit">
                     <span class="glyphicon glyphicon-eye-open"></span> &nbsp; Mostrar
                  </button>
               </div>
            </div>
         </form>
      </div>
      <div class="col-md-4 col-sm-6">
         <form name="informe_proveedor" action="<?php echo $fsc->url();?>&mostrar=compras" method="post" target="_blank" class="form">
            <input type="hidden" name="informe" value="facturasprov"/>
            <input type="hidden" name="codproveedor"/>
            <div class="panel panel-default">
               <div class="panel-heading">
                  <h3 class="panel-title">
                     <span class="glyphicon glyphicon-signal" aria-hidden="true"></span>
                     &nbsp; Informe de compras
                  </h3>
               </div>
               <div class="panel-body">
                  <p class="help-block">
                     Obtén un informe de compras desglosado por proveedor, año y mes.
                  </p>
                  <div class="form-group">
                     Desde:
                     <input class="form-control datepicker" type="text" name="desde" value="<?php echo $fsc->desde;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group">
                     Hasta:
                     <input class="form-control datepicker" type="text" name="hasta" value="<?php echo $fsc->hasta;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group">
                     <a href="<?php echo $fsc->serie->url();?>">Serie</a>:
                     <select class="form-control" name="codserie">
                        <option value="">Todas</option>
                        <option value="">-----</option>
                        <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codserie;?>"><?php echo $value1->descripcion;?></option>
                        <?php } ?>

                     </select>
                  </div>
                  <div class="form-group">
                     <a href="<?php echo $fsc->agente->url();?>">Empleado</a>:
                     <select name="codagente" class="form-control">
                        <option value="">Todos</option>
                        <option value="">------</option>
                        <?php $loop_var1=$fsc->agente->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codagente;?>"><?php echo $value1->get_fullname();?></option>
                        <?php } ?>

                     </select>
                  </div>
                  <div class="form-group">
                     Proveedor:
                     <div class="input-group">
                        <input class="form-control" type="text" name="ac_proveedor2" id="ac_proveedor2" placeholder="Buscar" autocomplete="off"/>
                        <span class="input-group-btn">
                           <button class="btn btn-default" type="button" onclick="clean_proveedor();document.informe_proveedor.ac_proveedor2.focus();">
                              <span class="glyphicon glyphicon-edit"></span>
                           </button>
                        </span>
                     </div>
                     <p class="help-block">Dejar en blanco para seleccionar todos los proveedores.</p>
                  </div>
                  <div class="radio">
                     <label>
                        <input type="radio" name="unidades" value="FALSE" checked=""/>
                        Importes
                     </label>
                  </div>
                  <div class="radio">
                     <label>
                        <input type="radio" name="unidades" value="TRUE"/>
                        Unidades
                     </label>
                  </div>
                  <div class="form-group">
                     Mínimo:
                     <input type="text" name="minimo" class="form-control" autocomplete="off" placeholder="importe o  unidades (opcional)"/>
                  </div>
               </div>
               <div class="panel-footer">
                  <button class="btn btn-sm btn-primary" type="submit">
                     <span class="glyphicon glyphicon-eye-open"></span> &nbsp; Mostrar
                  </button>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>
<?php }elseif( $fsc->mostrar=='ventas' ){ ?>

<br/>
<div class="container-fluid">
   <div class="row">
      <div class="col-md-4 col-sm-6">
         <form name ="listado_facturas" action="<?php echo $fsc->url();?>&mostrar=ventas" method="post" target="_blank" class="form">
            <input type="hidden" name="listado" value="facturascli"/>
            <input type="hidden" name="codcliente"/>
            <div class="panel panel-default">
               <div class="panel-heading">
                  <h3 class="panel-title">
                     <span class="glyphicon glyphicon-list" aria-hidden="true"></span>
                     &nbsp; Listado de facturas de venta
                  </h3>
               </div>
               <div class="panel-body">
                  <div class="form-group">
                     Desde:
                     <input class="form-control datepicker" type="text" name="desde" value="<?php echo $fsc->desde;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group">
                     Hasta:
                     <input class="form-control datepicker" type="text" name="hasta" value="<?php echo $fsc->hasta;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group">
                     <a href="<?php echo $fsc->serie->url();?>">Serie</a>:
                     <select class="form-control" name="codserie">
                        <option value="">Todas</option>
                        <option value="">-----</option>
                        <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codserie;?>"><?php echo $value1->descripcion;?></option>
                        <?php } ?>

                     </select>
                  </div>
                  <div class="form-group">
                     <a href="<?php echo $fsc->agente->url();?>">Empleado</a>:
                     <select name="codagente" class="form-control">
                        <option value="">Todos</option>
                        <option value="">---</option>
                        <?php $loop_var1=$fsc->agente->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codagente;?>"><?php echo $value1->get_fullname();?></option>
                        <?php } ?>

                     </select>
                  </div>
                  <div class="form-group">
                     Cliente:
                     <div class="input-group">
                        <input class="form-control" type="text" name="ac_cliente" id="ac_cliente" placeholder="Buscar" autocomplete="off"/>
                        <span class="input-group-btn">
                           <button class="btn btn-default" type="button" onclick="clean_cliente();document.listado_facturas.ac_cliente.focus();">
                              <span class="glyphicon glyphicon-edit"></span>
                           </button>
                        </span>
                     </div>
                     <p class="help-block">Dejar en blanco para seleccionar todos los clientes.</p>
                  </div>
                  <div class="form-group">
                     Estado de la factura:
                     <select class="form-control" name="estado">
                        <option value="">Todas</option>
                        <option value="pagada">Pagada</option>
                        <option value="sinpagar">Sin pagar</option>
                     </select>
                  </div>
                  <div class="form-group">
                     Generar:
                     <select name="generar" class="form-control">
                        <option value="pdf">PDF</option>
                        <option value="csv">CSV</option>
                     </select>
                  </div>
               </div>
               <div class="panel-footer">
                  <button class="btn btn-sm btn-primary" type="submit">
                     <span class="glyphicon glyphicon-eye-open"></span> &nbsp; Mostrar
                  </button>
               </div>
            </div>
         </form>
      </div>
      <div class="col-md-4 col-sm-6">
         <form name="informe_facturas" action="<?php echo $fsc->url();?>&mostrar=ventas" method="post" target="_blank" class="form">
            <input type="hidden" name="informe" value="facturascli"/>
            <input type="hidden" name="codcliente"/>
            <div class="panel panel-default">
               <div class="panel-heading">
                  <h3 class="panel-title">
                     <span class="glyphicon glyphicon-signal" aria-hidden="true"></span>
                     &nbsp; Informe de ventas
                  </h3>
               </div>
               <div class="panel-body">
                  <p class="help-block">
                     Obtén un informe de ventas desglosado por cliente, año y mes.
                  </p>
                  <div class="form-group">
                     Desde:
                     <input class="form-control datepicker" type="text" name="desde" value="<?php echo $fsc->desde;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group">
                     Hasta:
                     <input class="form-control datepicker" type="text" name="hasta" value="<?php echo $fsc->hasta;?>" autocomplete="off"/>
                  </div>
                  <div class="form-group">
                     <a href="<?php echo $fsc->serie->url();?>">Serie</a>:
                     <select class="form-control" name="codserie">
                        <option value="">Todas</option>
                        <option value="">-----</option>
                        <?php $loop_var1=$fsc->serie->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codserie;?>"><?php echo $value1->descripcion;?></option>
                        <?php } ?>

                     </select>
                  </div>
                  <div class="form-group">
                     <a href="<?php echo $fsc->agente->url();?>">Empleado</a>:
                     <select name="codagente" class="form-control">
                        <option value="">Todos</option>
                        <option value="">------</option>
                        <?php $loop_var1=$fsc->agente->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codagente;?>"><?php echo $value1->get_fullname();?></option>
                        <?php } ?>

                     </select>
                  </div>
                  <div class="form-group">
                     <a href="<?php echo $fsc->pais->url();?>">Pais</a>:
                     <select class="form-control" name="codpais">
                        <option value="">Todos</option>
                        <option value="">-----</option>
                        <?php $loop_var1=$fsc->pais->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1->codpais;?>"><?php echo $value1->nombre;?></option>
                        <?php } ?>

                     </select>
                  </div>
                  <div class="form-group">
                     <span class="text-capitalize"><?php  echo FS_PROVINCIA;?>:</span>
                     <select name="provincia" class="form-control">
                        <option value="">Todas</option>
                        <option value="">------</option>
                        <?php $loop_var1=$fsc->provincias(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $key1;?>"><?php echo $value1;?></option>
                        <?php } ?>

                     </select>
                  </div>
                  <div class="form-group">
                     Cliente:
                     <div class="input-group">
                        <input class="form-control" type="text" name="ac_cliente2" id="ac_cliente2" placeholder="Buscar" autocomplete="off"/>
                        <span class="input-group-btn">
                           <button class="btn btn-default" type="button" onclick="clean_cliente();document.informe_facturas.ac_cliente2.focus();">
                              <span class="glyphicon glyphicon-edit"></span>
                           </button>
                        </span>
                     </div>
                     <p class="help-block">Dejar en blanco para seleccionar todos los clientes.</p>
                  </div>
                  <div class="radio">
                     <label>
                        <input type="radio" name="unidades" value="FALSE" checked=""/>
                        Importes
                     </label>
                  </div>
                  <div class="radio">
                     <label>
                        <input type="radio" name="unidades" value="TRUE"/>
                        Unidades
                     </label>
                  </div>
                  <div class="form-group">
                     Importe mínimo:
                     <input type="text" name="minimo" class="form-control" autocomplete="off" placeholder="opcional"/>
                  </div>
               </div>
               <div class="panel-footer">
                  <button class="btn btn-sm btn-primary" type="submit">
                     <span class="glyphicon glyphicon-eye-open"></span> &nbsp; Mostrar
                  </button>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>
<?php } ?>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>