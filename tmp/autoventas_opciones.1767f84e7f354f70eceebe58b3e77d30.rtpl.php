<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>

<form action="<?php echo $fsc->url();?>" method="get" class="post">
    <input type="hidden" name="page" value="autoventas_opciones">
    <input type="hidden" name="action" value="1">
    <div class="container-fluid">
        <div class="row" style="padding-top: 10px;">
            <h3>Opciones de configuración del catálogo</h3>
        </div>
        <div class='row'>
            <fieldset>
                <legend>General</legend>
            <div class="col-sm-6">
                <div class="form-group">
                <div class="checkbox">
                <label>
                    <input type="checkbox" id="fam_visible" name="activo" value="1" <?php if( $fsc->opciones->activo ){ ?>checked<?php } ?>/>
                Activo
                </label>
                </div>
                </div>
            </div>   
                </fieldset>
            
            <fieldset>
                <legend>Sincronización</legend>
                <div class="col-sm-10"><span id="resultado_sincro">Ultima sincronización:<b> <?php echo $fsc->fecha_sincro();?></b></span></div>
                <div class="col-sm-2 text-right">      <a class="btn btn-sm btn-primary" id="boton_sincro" href="#" onclick="sincroniza();">
                        <span class="glyphicon glyphicon-refresh"></span> &nbsp; Sincronizar ahora
                     </a></div>
            </fieldset>
                <fieldset>
                <legend>Solicitar pedidos</legend>
                <div class="col-sm-10"><span id="resultado_solicitud">Ultima solicitud<b> <?php echo $fsc->fecha_sincro();?></b></span></div>
                <div class="col-sm-2 text-right">      <a class="btn btn-sm btn-primary" id="boton_solicita" href="#" onclick="solicita();">
                        <span class="glyphicon glyphicon-refresh"></span> &nbsp; Solicitar ahora
                     </a></div>
            </fieldset>
            <fieldset>
                <legend>Configuración servidor externo</legend>
                <div class="col-sm-12">
                     <div class="form-group">
                        URL Autoventas:
                        <input class="form-control" type="text" name="url" value="<?php echo $fsc->opciones->url;?>" maxlength="250" autocomplete="off"/>
                     </div>
                </div>
                <div class="col-sm-6">
                     <div class="form-group">
                        Servidor FTP:
                        <input class="form-control" type="text" name="ftp_url" value="<?php echo $fsc->opciones->ftp_url;?>" maxlength="250" autocomplete="off"/>
                     </div>
                </div>
                            <div class="col-sm-6">
                     <div class="form-group">
                        Directorio FTP:
                        <input class="form-control" type="text" name="ftp_dir" value="<?php echo $fsc->opciones->ftp_dir;?>" maxlength="250" autocomplete="off"/>
                     </div>
            </div>   
            <div class="col-sm-6">
                     <div class="form-group">
                        Usuario FTP:
                        <input class="form-control" type="text" name="ftp_user" value="<?php echo $fsc->opciones->ftp_user;?>" maxlength="250" autocomplete="off"/>
                     </div>
            </div>   
            <div class="col-sm-6">
                     <div class="form-group">
                        Password FTP:
                        <input class="form-control" type="text" name="ftp_pass" value="<?php echo $fsc->opciones->ftp_pass;?>" maxlength="250" autocomplete="off"/>
                     </div>
            </div>   

            </fieldset>
            <fieldset>
                <legend>CRON Job</legend>
            <div class="col-sm-6">
                <div class="form-group">
                <div class="checkbox">
                <label>
                <input type="checkbox" id="fam_visible" name="cron" value="1" <?php if( $fsc->opciones->cron ){ ?>checked<?php } ?>/>
                Activar sincronización automática
                </label>
                </div>
                </div>
            </div>   
            <div class="col-sm-6">
                <div class="form-group">
                    <select name='tiempocron' class="form-control">
                        <option value="5" <?php if( $fsc->opciones->tiempocron==5 ){ ?>selected="selected"<?php } ?>>5 minutos</option>
                        <option value="15" <?php if( $fsc->opciones->tiempocron==15 ){ ?>selected="selected"<?php } ?>>15 minutos</option>
                        <option value="30" <?php if( $fsc->opciones->tiempocron==30 ){ ?>selected="selected"<?php } ?>>30 minutos</option>
                        <option value="60" <?php if( $fsc->opciones->tiempocron==60 ){ ?>selected="selected"<?php } ?>>1 hora</option>
                        <option value="180" <?php if( $fsc->opciones->tiempocron==180 ){ ?>selected="selected"<?php } ?>>3 horas</option>
                        <option value="300" <?php if( $fsc->opciones->tiempocron==300 ){ ?>selected="selected"<?php } ?>>5 horas</option>
                    </select>
                </div>
            </div>   

                
                
            </fieldset>

            
                            <div class='col-sm-12 text-right'>
                          <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                        <span class="glyphicon glyphicon-floppy-disk"></span> &nbsp; Guardar
                     </button>
                </div>
        </div>
    </div>
</form>
<script>
    function sincroniza () {
        
            $.ajax({
                url: '<?php echo $fsc->url();?>&action=2',
                cache: false,
                async: true,
                crossDomain: true,
                success: function (result, textStatus, jqXHR)
                {  
                    $("#resultado_sincro").html(result);
                },
                error: function (jqXHR, errorThrown) {
                    $("#resultado_sincro").html("Error");
                },
                beforeSend: function (jqXHR, settings) {
                    $("#boton_sincro").disabled=true;
                    $("#boton_sincro").html("Sincronizando...");
                },
                complete: function (jqXHR, textStatus) {
                    $("#boton_sincro").disabled=false;
                    $("#boton_sincro").html('<span class="glyphicon glyphicon-refresh"></span> &nbsp; Sincronizar ahora');
                }
            });
    }
        function solicita () {
        
            $.ajax({
                url: '<?php echo $fsc->url();?>&action=3',
                cache: false,
                async: true,
                crossDomain: true,
                success: function (result, textStatus, jqXHR)
                {  
                    $("#resultado_solicitud").html(result);
                    console.log("realizado");
                    
                },
                error: function (jqXHR, errorThrown) {
                    $("#resultado_solicitud").html("Error");
                },
                beforeSend: function (jqXHR, settings) {
                    $("#boton_solicita").disabled=true;
                    $("#boton_solicita").html("Solicitando...");
                },
                complete: function (jqXHR, textStatus) {
                    $("#boton_solicita").disabled=false;
                    $("#boton_solicita").html('<span class="glyphicon glyphicon-refresh"></span> &nbsp; Solicitar ahora');
                }
            });
    }
</script>    
<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>