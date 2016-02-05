<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<div class="container">
   <div class="row">
      <div class="col-sm-12">
         <div class="page-header">
            <h1>Tesorería</h1>
            <p class="help-block">
               Muchas gracias por adquirir el plugin tesorería. Este plugin tiene
               las siguiente características:
            </p>
         </div>
         <ul>
            <li>
               Añade gestión de recibos de facturas de compra y de venta.
               <ul>
                  <li>Ve a <b>Compras &gt; Recibos</b> para ver los recibos de las facturas de compra.</li>
                  <li>Ve a <b>Ventas &gt; Recibos</b> para ver los recibos de las facturas de venta.</li>
               </ul>
            </li>
            <li>
               Incluye formas de pago mejoradas, que permiten definir plazos de pago,
               por ejemplo, puedes definir un pago 30-60-90, generando en las facturas
               un recibo a los 30 días, otro a los 60 y otro a los 90.
               <ul>
                  <li>
                     Puedes ver o modificar las formas de pago desde
                     <b>Contabilidas &nbsp; Formas de pago</b>.
                  </li>
                  <li>
                     Usa el botón &nbsp;
                     <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> &nbsp;
                     para definir los plazos de pago de cada forma de pago.
                  </li>
               </ul>
            </li>
            <li>
               También permite gestionar los pagos o adelantos de <?php  echo FS_PEDIDOS;?> y <?php  echo FS_ALBARANES;?>.
               <ul>
                  <li>Añade la pestaña <b>Pagos</b> a los <?php  echo FS_PEDIDOS;?> y <?php  echo FS_ALBARANES;?>.</li>
               </ul>
            </li>
            <li>
               Es compatible con el módulo de tesorería de Eneboo.
            </li>
         </ul>
      </div>
   </div>
</div>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>