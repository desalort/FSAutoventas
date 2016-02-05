<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header2") . ( substr("header2",-1,1) != "/" ? "/" : "" ) . basename("header2") );?>


<div class="container-fluid">
   <div class="row" style="margin-top: 15px; margin-bottom: 15px;">
      <?php $loop_var1=$fsc->documentos; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         <div class="col-sm-4">
            <div class="btn-group">
               <a href="<?php echo $value1['fullname'];?>" target="_blank" class="btn btn-bg btn-default">
                  <span class="glyphicon glyphicon-file" aria-hidden="true"></span> &nbsp; <?php echo $value1['name'];?>

               </a>
               <a href="#" class="btn btn-bg btn-default" title="Eliminar" onclick="delete_documento('<?php echo $value1['name'];?>')">
                  <span class="glyphicon glyphicon-trash"></span>
               </a>
            </div>
            <p class="help-block">
               Tamaño: <?php echo $value1['filesize'];?> &nbsp;
               <span class="glyphicon glyphicon-calendar"></span> <?php echo $value1['date'];?>

            </p>
         </div>
      <?php } ?>

   </div>
   <div class="row">
      <div class="col-sm-4">
         <form enctype="multipart/form-data" action="<?php echo $fsc->url();?>" method="post" class="form">
            <input type="hidden" name="upload" value="TRUE"/>
            <div class="panel panel-default">
               <div class="panel-heading">
                  <h3 class="panel-title">Añadir un documento</h3>
               </div>
               <div class="panel-body">
                  <div class="form-group">
                     <input name="fdocumento" type="file"/>
                  </div>
                  <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                     <span class="glyphicon glyphicon-floppy-disk"></span> &nbsp; Guardar
                  </button>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer2") . ( substr("footer2",-1,1) != "/" ? "/" : "" ) . basename("footer2") );?>