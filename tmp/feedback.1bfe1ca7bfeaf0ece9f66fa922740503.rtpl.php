<?php if(!class_exists('raintpl')){exit;}?><form name="f_feedback" action="<?php  echo FS_COMMUNITY_URL;?>/index.php?page=community_feedback" method="post" target="_blank" class="form" role="form">
   <input type="hidden" name="feedback_info" value="<?php echo $fsc->system_info();?>"/>
   <input type="hidden" name="feedback_type" value="error"/>
   <div class="modal" id="modal_feedback">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
               <h4 class="modal-title">¿Necesitas ayuda?</h4>
            </div>
            <div class="modal-body">
               <div class="form-group">
                  Detalla tu duda o problema:
                  <textarea class="form-control" name="feedback_text" rows="6"></textarea>
               </div>
               <div class="form-group">
                  Tu email:
                  <?php if( $fsc->empresa AND $fsc->user->logged_on ){ ?>

                  <input type="email" class="form-control" name="feedback_email" placeholder="Tu email" value="<?php echo $fsc->empresa->email;?>"/>
                  <?php }else{ ?>

                  <input type="email" class="form-control" name="feedback_email" placeholder="Tu email"/>
                  <?php } ?>

               </div>
            </div>
            <div class="modal-footer">
               <button type="submit" class="btn btn-sm btn-primary">
                  <span class="glyphicon glyphicon-send"></span> &nbsp; Enviar
               </button>
            </div>
         </div>
      </div>
   </div>
</form>

<?php if( $fsc->empresa AND !FS_DEMO AND mt_rand(0,2)==0 ){ ?>

<div style="display: none;">
   <?php if( mt_rand(0,2)>0 AND $fsc->user->logged_on ){ ?>

   <iframe src="index.php?page=admin_home&check4updates=TRUE" height="0"></iframe>
   <?php }else{ ?>

   <iframe src="<?php  echo FS_COMMUNITY_URL;?>/index.php?page=community_stats&add=TRUE&version=<?php echo $fsc->version();?>&plugins=<?php echo join(',', $GLOBALS['plugins']); ?>" height="0">
   </iframe>
   <?php } ?>

</div>
<?php } ?>

