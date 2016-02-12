<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of autoventa_extensiones
 *
 * @author user
 */
require_model ("autoventas_familia.php");
require_model ("familia.php");
require_model ("autoventas_opcionesdb.php");

// IMPRIMIR
require_once 'plugins/facturacion_base/extras/fs_pdf.php';
require_once 'extras/phpmailer/class.phpmailer.php';
require_once 'extras/phpmailer/class.smtp.php';
require_model('cliente.php');
require_model('cuenta_banco.php');
require_model('cuenta_banco_cliente.php');
require_model('forma_pago.php');

class autoventas_extensiones extends fs_controller {
    
    // FAMILIA
    public $codFamilia;
    public $familia;
    
    // FIRMA
    public $firmaUrl;
    public $firmaSolicitar;
    public $id;
    public $tipo;
    
    // IMPRIMIR
    public $albaran;
   public $cliente;
   public $pedido;
   public $impresion;
   public $impuesto;
    private $logo;
    
    
    
         private function generar_pdf_firma(&$pdf_doc, &$lppag , $numero2)
   {
      
        if( function_exists('imagecreatefromstring') )
        {
            $path = "images/autoventas/firmas";
            if (file_exists($path . "/$numero2.jpg")) {
                $pdf_doc->pdf->ezImage($path . "/$numero2.jpg", 0, 150, 'none');
                $lppag -= 2; 
            }
        }
        else
        {
           die('ERROR: no se encuentra la función imagecreatefromstring(). '
                   . 'Y por tanto no se puede usar el logotipo en los documentos.');
        }

   }
   
       private function generar_pdf_cabecera(&$pdf_doc, &$lppag)
   {
      /// ¿Añadimos el logo?
      if($this->logo)
      {
         if( function_exists('imagecreatefromstring') )
         {
            $pdf_doc->pdf->ezImage($this->logo, 0, 150, 'none');
            $lppag -= 2; /// si metemos el logo, caben menos líneas
         }
         else
         {
            die('ERROR: no se encuentra la función imagecreatefromstring(). '
                    . 'Y por tanto no se puede usar el logotipo en los documentos.');
         }
      }
      else
      {
         $pdf_doc->pdf->ezText("<b>".$this->empresa->nombre."</b>", 16, array('justification' => 'center'));
         $pdf_doc->pdf->ezText(FS_CIFNIF.": ".$this->empresa->cifnif, 8, array('justification' => 'center'));
         
         $direccion = $this->empresa->direccion;
         if($this->empresa->codpostal)
         {
            $direccion .= ' - ' . $this->empresa->codpostal;
         }
         
         if($this->empresa->ciudad)
         {
            $direccion .= ' - ' . $this->empresa->ciudad;
         }
         
         if($this->empresa->provincia)
         {
            $direccion .= ' (' . $this->empresa->provincia . ')';
         }
         
         if($this->empresa->telefono)
         {
            $direccion .= ' - Teléfono: ' . $this->empresa->telefono;
         }
         
         $pdf_doc->pdf->ezText($this->fix_html($direccion), 9, array('justification' => 'center'));
      }
   }
   
   private function generar_pdf_lineas(&$pdf_doc, &$lineas, &$linea_actual, &$lppag, &$documento)
   {
      if($this->impresion['print_dto'])
      {
         $this->impresion['print_dto'] = FALSE;
         
         /// leemos las líneas para ver si de verdad mostramos los descuentos
         foreach($lineas as $lin)
         {
            if($lin->dtopor != 0)
            {
               $this->impresion['print_dto'] = TRUE;
               break;
            }
         }
      }
      
      $multi_iva = FALSE;
      $multi_re = FALSE;
      $multi_irpf = FALSE;
      $iva = FALSE;
      $re = FALSE;
      $irpf = FALSE;
      /// leemos las líneas para ver si hay que mostrar los tipos de iva, re o irpf
      foreach($lineas as $lin)
      {
         if($iva === FALSE)
         {
            $iva = $lin->iva;
         }
         else if($lin->iva != $iva)
         {
            $multi_iva = TRUE;
         }
         
         if($re === FALSE)
         {
            $re = $lin->recargo;
         }
         else if($lin->recargo != $re)
         {
            $multi_re = TRUE;
         }
         
         if($irpf === FALSE)
         {
            $irpf = $lin->irpf;
         }
         else if($lin->irpf != $irpf)
         {
            $multi_irpf = TRUE;
         }
      }
      
      /*
       * Creamos la tabla con las lineas del documento
       */
      $pdf_doc->new_table();
      $table_header = array(
          'alb' => '<b>'.ucfirst(FS_ALBARAN).'</b>',
          'descripcion' => '<b>Ref. + Descripción</b>',
          'cantidad' => '<b>Cant.</b>',
          'pvp' => '<b>PVP</b>',
      );
      
      /// ¿Desactivamos la columna de albaran?
      if( get_class($documento) == 'factura_cliente' )
      {
         if($this->impresion['print_alb'])
         {
            /// aunque esté activada, si la factura no viene de un albaran, la desactivamos
            $this->impresion['print_alb'] = FALSE;
            foreach($lineas as $lin)
            {
               if($lin->idalbaran)
               {
                  $this->impresion['print_alb'] = TRUE;
                  break;
               }
            }
         }
         
         if( !$this->impresion['print_alb'] )
         {
            unset($table_header['alb']);
         }
      }
      else
      {
         unset($table_header['alb']);
      }
      
      if($this->impresion['print_dto'])
      {
         $table_header['dto'] = '<b>Dto.</b>';
      }
      
      if($multi_iva)
      {
         $table_header['iva'] = '<b>'.FS_IVA.'</b>';
      }
      
      if($multi_re)
      {
         $table_header['re'] = '<b>R.E.</b>';
      }
      
      if($multi_irpf)
      {
         $table_header['irpf'] = '<b>'.FS_IRPF.'</b>';
      }
      
      $table_header['importe'] = '<b>Importe</b>';
      $pdf_doc->add_table_header($table_header);
      
      for($i = $linea_actual; (($linea_actual < ($lppag + $i)) AND ($linea_actual < count($lineas)));)
      {
         $descripcion = $this->fix_html($lineas[$linea_actual]->descripcion);
         if( !is_null($lineas[$linea_actual]->referencia) )
         {
            $descripcion = '<b>'.$lineas[$linea_actual]->referencia.'</b> '.$descripcion;
         }
         
         $fila = array(
             'alb' => '-',
             'cantidad' => $lineas[$linea_actual]->cantidad,
             'descripcion' => $descripcion,
             'pvp' => $this->show_precio($lineas[$linea_actual]->pvpunitario, $documento->coddivisa, TRUE, FS_NF0_ART),
             'dto' => $this->show_numero($lineas[$linea_actual]->dtopor) . " %",
             'iva' => $this->show_numero($lineas[$linea_actual]->iva) . " %",
             're' => $this->show_numero($lineas[$linea_actual]->recargo) . " %",
             'irpf' => $this->show_numero($lineas[$linea_actual]->irpf) . " %",
             'importe' => $this->show_precio($lineas[$linea_actual]->pvptotal, $documento->coddivisa)
         );
         
         if( get_class($lineas[$linea_actual]) == 'linea_factura_cliente' )
         {
            $fila['alb'] = $lineas[$linea_actual]->albaran_numero();
         }
         
         $pdf_doc->add_table_row($fila);
         $linea_actual++;
      }
      
      $pdf_doc->save_table(
              array(
                  'fontSize' => 8,
                  'cols' => array(
                      'cantidad' => array('justification' => 'right'),
                      'pvp' => array('justification' => 'right'),
                      'dto' => array('justification' => 'right'),
                      'iva' => array('justification' => 'right'),
                      're' => array('justification' => 'right'),
                      'irpf' => array('justification' => 'right'),
                      'importe' => array('justification' => 'right')
                  ),
                  'width' => 520,
                  'shaded' => 0
              )
      );
   }
   
   private function generar_pdf_albaran($archivo = FALSE)
   {
      if(!$archivo)
      {
         /// desactivamos la plantilla HTML
         $this->template = FALSE;
      }
      
      $pdf_doc = new fs_pdf();
      $pdf_doc->pdf->addInfo('Title', FS_ALBARAN.' '. $this->albaran->codigo);
      $pdf_doc->pdf->addInfo('Subject', FS_ALBARAN.' de cliente ' . $this->albaran->codigo);
      $pdf_doc->pdf->addInfo('Author', $this->empresa->nombre);
      
      $lineas = $this->albaran->get_lineas();
      $lineas_iva = $this->get_lineas_iva($lineas);
      if($lineas)
      {
         $linea_actual = 0;
         $pagina = 1;
         
         /// imprimimos las páginas necesarias
         while( $linea_actual < count($lineas) )
         {
            $lppag = 35;
            
            /// salto de página
            if($linea_actual > 0)
            {
               $pdf_doc->pdf->ezNewPage();
            }
            
            $this->generar_pdf_cabecera($pdf_doc, $lppag);
            
            /*
             * Esta es la tabla con los datos del cliente:
             * Albarán:             Fecha:
             * Cliente:             CIF/NIF:
             * Dirección:           Teléfonos:
             */
            $pdf_doc->new_table();
            $pdf_doc->add_table_row(
               array(
                   'campo1' => "<b>".ucfirst(FS_ALBARAN).":</b>",
                   'dato1' => $this->albaran->codigo,
                   'campo2' => "<b>Fecha:</b>",
                   'dato2' => $this->albaran->fecha
               )
            );
            $pdf_doc->add_table_row(
               array(
                   'campo1' => "<b>Cliente:</b>",
                   'dato1' => $this->fix_html($this->albaran->nombrecliente),
                   'campo2' => "<b>".FS_CIFNIF.":</b>",
                   'dato2' => $this->albaran->cifnif
               )
            );
            $pdf_doc->add_table_row(
               array(
                   'campo1' => "<b>Dirección:</b>",
                   'dato1' => $this->fix_html($this->albaran->direccion.' CP: '.$this->albaran->codpostal.
                           ' - '.$this->albaran->ciudad.' ('.$this->albaran->provincia.')'),
                   'campo2' => "<b>Teléfonos:</b>",
                   'dato2' => $this->cliente->telefono1.'  '.$this->cliente->telefono2
               )
            );
            $pdf_doc->save_table(
               array(
                   'cols' => array(
                       'campo1' => array('justification' => 'right'),
                       'dato1' => array('justification' => 'left'),
                       'campo2' => array('justification' => 'right'),
                       'dato2' => array('justification' => 'left')
                   ),
                   'showLines' => 0,
                   'width' => 520,
                   'shaded' => 0
               )
            );
            $pdf_doc->pdf->ezText("\n", 10);
            $pdf_doc->pdf->ezText("Código albaran :" . $this->albaran->numero2 , 10);
            $pdf_doc->pdf->ezText("\n", 10);
            $this->generar_pdf_lineas($pdf_doc, $lineas, $linea_actual, $lppag, $this->albaran);
            
            if( $linea_actual == count($lineas) )
            {
               if($this->albaran->observaciones != '')
               {
                  $pdf_doc->pdf->ezText("\n".$this->fix_html($this->albaran->observaciones), 9);
               }
            }
            $pdf_doc->pdf->ezText("\n", 10);
            //$pdf_doc->set_y(70);
            $this->generar_pdf_firma($pdf_doc, $lppag, $this->albaran->numero2);
            $pdf_doc->set_y(80);
            
            /*
             * Rellenamos la última tabla de la página:
             * 
             * Página            Neto    IVA   Total
             */
            $pdf_doc->new_table();
            $titulo = array('pagina' => '<b>Página</b>', 'neto' => '<b>Neto</b>',);
            $fila = array(
                'pagina' => $pagina . '/' . ceil(count($lineas) / $lppag),
                'neto' => $this->show_precio($this->albaran->neto, $this->albaran->coddivisa),
            );
            $opciones = array(
                'cols' => array(
                    'neto' => array('justification' => 'right'),
                ),
                'showLines' => 4,
                'width' => 520
            );
            foreach($lineas_iva as $li)
            {
               $imp = $this->impuesto->get($li['codimpuesto']);
               if($imp)
               {
                  $titulo['iva'.$li['iva']] = '<b>'.$imp->descripcion.'</b>';
               }
               else
                  $titulo['iva'.$li['iva']] = '<b>'.FS_IVA.' '.$li['iva'].'%</b>';
               
               $fila['iva'.$li['iva']] = $this->show_precio($li['totaliva'], $this->albaran->coddivisa);
               
               if($li['totalrecargo'] != 0)
               {
                  $fila['iva'.$li['iva']] .= ' (RE: '.$this->show_precio($li['totalrecargo'], $this->albaran->coddivisa).')';
               }
               
               $opciones['cols']['iva'.$li['iva']] = array('justification' => 'right');
            }
            
            if($this->albaran->totalirpf != 0)
            {
               $titulo['irpf'] = '<b>'.FS_IRPF.' '.$this->albaran->irpf.'%</b>';
               $fila['irpf'] = $this->show_precio($this->albaran->totalirpf);
               $opciones['cols']['irpf'] = array('justification' => 'right');
            }
            
            $titulo['liquido'] = '<b>Total</b>';
            $fila['liquido'] = $this->show_precio($this->albaran->total, $this->albaran->coddivisa);
            $opciones['cols']['liquido'] = array('justification' => 'right');
            $pdf_doc->add_table_header($titulo);
            $pdf_doc->add_table_row($fila);
            $pdf_doc->save_table($opciones);
            
            $pdf_doc->pdf->addText(10, 10, 8, $pdf_doc->center_text($this->fix_html($this->empresa->pie_factura), 153), 0, 1.5);
            
            $pagina++;
         }
      }
      else
      {
         $pdf_doc->pdf->ezText('¡'.ucfirst(FS_ALBARAN).' sin líneas!', 20);
      }
      
      if($archivo)
      {
         if( !file_exists('tmp/'.FS_TMP_NAME.'enviar') )
         {
            mkdir('tmp/'.FS_TMP_NAME.'enviar');
         }
         
         $pdf_doc->save('tmp/'.FS_TMP_NAME.'enviar/'.$archivo);
      }
      else
         $pdf_doc->show(FS_ALBARAN.'_'.$this->albaran->codigo.'.pdf');
   }
   
   private function generar_pdf_pedido($archivo=FALSE)
   {
      if(!$archivo)
      {
         /// desactivamos la plantilla HTML
         $this->template = FALSE;
      }
      
      /// Creamos el PDF y escribimos sus metadatos
      $pdf_doc = new fs_pdf();
      $pdf_doc->pdf->addInfo('Title', ucfirst(FS_FACTURA).' '.$this->pedido->codigo);
      $pdf_doc->pdf->addInfo('Subject', ucfirst(FS_FACTURA).' '.$this->pedido->codigo);
      $pdf_doc->pdf->addInfo('Author', $this->empresa->nombre);
      
      $lineas = $this->pedido->get_lineas();
      //$lineas_iva = $this->pedido->get_lineas_iva();
      if($lineas)
      {
         $lineasfact = count($lineas);
         $linea_actual = 0;
         $pagina = 1;
         
         // Imprimimos las páginas necesarias
         while($linea_actual < $lineasfact)
         {
            $lppag = 35; /// líneas por página
            
            /// salto de página
            if($linea_actual > 0)
            {
               $pdf_doc->pdf->ezNewPage();
            }
            
               $this->generar_pdf_cabecera($pdf_doc, $lppag);
               

               $pdf_doc->new_table();
               
    
                  $pdf_doc->add_table_row(
                     array(
                        'campo1' => "<b>".ucfirst(FS_PEDIDO).":</b>",
                        'dato1' => $this->pedido->codigo,
                        'campo2' => "<b>Fecha:</b>",
                        'dato2' => $this->pedido->fecha
                     )
                  );
               
               
               $pdf_doc->add_table_row(
                  array(
                     'campo1' => "<b>Cliente:</b>",
                     'dato1' => $this->fix_html($this->pedido->nombrecliente),
                     'campo2' => "<b>".FS_CIFNIF.":</b>",
                     'dato2' => $this->pedido->cifnif
                  )
               );
               $pdf_doc->add_table_row(
                  array(
                     'campo1' => "<b>Dirección:</b>",
                     'dato1' => $this->pedido->direccion.' CP: '.$this->pedido->codpostal.' - '.$this->pedido->ciudad.
                                 ' ('.$this->pedido->provincia.')',
                     'campo2' => "<b>Teléfonos:</b>",
                     'dato2' => $this->cliente->telefono1.'  '.$this->cliente->telefono2
                  )
               );
               $pdf_doc->save_table(
                  array(
                     'cols' => array(
                        'campo1' => array('justification' => 'right'),
                        'dato1' => array('justification' => 'left'),
                        'campo2' => array('justification' => 'right'),
                        'dato2' => array('justification' => 'left')
                     ),
                     'showLines' => 0,
                     'width' => 520,
                     'shaded' => 0
                  )
               );
               $pdf_doc->pdf->ezText("\n", 10);
            
             
            $pdf_doc->pdf->ezText("Código pedido :" . $this->pedido->numero2 , 10);
            $pdf_doc->pdf->ezText("\n", 10);
            
            $this->generar_pdf_lineas($pdf_doc, $lineas, $linea_actual, $lppag, $this->pedido);
            
            if( $linea_actual == count($lineas) )
            {
               if($this->pedido->observaciones != '')
               {
                  $pdf_doc->pdf->ezText("\n".$this->fix_html($this->pedido->observaciones), 9);
               }
               

            }
            
            $pdf_doc->pdf->ezText("\n", 10);
            $this->generar_pdf_firma($pdf_doc, $lppag, $this->pedido->numero2);

            
            $pdf_doc->set_y(80);
            
            /*
             * Rellenamos la última tabla de la página:
             * 
             * Página            Neto    IVA   Total
             */
            $pdf_doc->new_table();
            $titulo = array('pagina' => '<b>Página</b>', 'neto' => '<b>Neto</b>',);
            $fila = array(
                'pagina' => $pagina . '/' . ceil(count($lineas) / $lppag),
                'neto' => $this->show_precio($this->pedido->neto, $this->pedido->coddivisa),
            );
            $opciones = array(
                'cols' => array(
                    'neto' => array('justification' => 'right'),
                ),
                'showLines' => 4,
                'width' => 520
            );
        
            
            if($this->pedido->totalirpf != 0)
            {
               $titulo['irpf'] = '<b>'.FS_IRPF.' '.$this->pedido->irpf.'%</b>';
               $fila['irpf'] = $this->show_precio($this->pedido->totalirpf);
               $opciones['cols']['irpf'] = array('justification' => 'right');
            }
            
            $titulo['liquido'] = '<b>Total</b>';
            $fila['liquido'] = $this->show_precio($this->pedido->total, $this->pedido->coddivisa);
            $opciones['cols']['liquido'] = array('justification' => 'right');
            $pdf_doc->add_table_header($titulo);
            $pdf_doc->add_table_row($fila);
            $pdf_doc->save_table($opciones);
            
            /// pié de página para la factura
            $pdf_doc->pdf->addText(10, 10, 8, $pdf_doc->center_text($this->fix_html($this->empresa->pie_factura), 153), 0, 1.5);
            
            $pagina++;
         }
      }
      else
      {
         $pdf_doc->pdf->ezText('¡'.ucfirst(FS_FACTURA).' sin líneas!', 20);
      }
      
      if($archivo)
      {
         if( !file_exists('tmp/'.FS_TMP_NAME.'enviar') )
         {
            mkdir('tmp/'.FS_TMP_NAME.'enviar');
         }
         
         $pdf_doc->save('tmp/'.FS_TMP_NAME.'enviar/'.$archivo);
      }
      else
         $pdf_doc->show(FS_FACTURA.'_'.$this->pedido->codigo.'.pdf');
   }
   
   private function enviar_email($doc, $tipo='simple')
   {
      if( $this->empresa->can_send_mail() )
      {
         if( $_POST['email'] != $this->cliente->email AND isset($_POST['guardar']) )
         {
            $this->cliente->email = $_POST['email'];
            $this->cliente->save();
         }
         
         if($doc == 'pedido')
         {
            $filename = 'pedido'.$this->pedido->codigo.'.pdf';
            $this->generar_pdf_factura($tipo, $filename);
         }
         else
         {
            $filename = 'albaran_'.$this->albaran->codigo.'.pdf';
            $this->generar_pdf_albaran($filename);
         }
         
         if( file_exists('tmp/'.FS_TMP_NAME.'enviar/'.$filename) )
         {
            $mail = new PHPMailer();
            $mail->CharSet = 'UTF-8';
            $mail->WordWrap = 50;
            $mail->isSMTP();
            $mail->SMTPAuth = TRUE;
            $mail->SMTPSecure = $this->empresa->email_config['mail_enc'];
            $mail->Host = $this->empresa->email_config['mail_host'];
            $mail->Port = intval($this->empresa->email_config['mail_port']);
            
            $mail->Username = $this->empresa->email;
            if($this->empresa->email_config['mail_user'] != '')
            {
               $mail->Username = $this->empresa->email_config['mail_user'];
            }
            
            $mail->Password = $this->empresa->email_config['mail_password'];
            $mail->From = $this->empresa->email;
            $mail->FromName = $this->user->get_agente_fullname();
            $mail->addReplyTo($_POST['de'], $mail->FromName);
            
            $mail->addAddress($_POST['email'], $this->cliente->razonsocial);
            if($_POST['email_copia'])
            {
               if( isset($_POST['cco']) )
               {
                  $mail->addBCC($_POST['email_copia'], $this->cliente->razonsocial);
               }
               else
               {
                  $mail->addCC($_POST['email_copia'], $this->cliente->razonsocial);
               }
            }
            if($this->empresa->email_config['mail_bcc'])
            {
               $mail->addBCC($this->empresa->email_config['mail_bcc']);
            }
            
            if($doc == 'factura')
            {
               $mail->Subject = $this->empresa->nombre . ': Su factura '.$this->factura->codigo;
            }
            else
            {
               $mail->Subject = $this->empresa->nombre . ': Su '.FS_ALBARAN.' '.$this->albaran->codigo;
            }
            
            $mail->AltBody = $_POST['mensaje'];
            $mail->msgHTML( nl2br($_POST['mensaje']) );
            $mail->isHTML(TRUE);
            
            $mail->addAttachment('tmp/'.FS_TMP_NAME.'enviar/'.$filename);
            if( is_uploaded_file($_FILES['adjunto']['tmp_name']) )
            {
               $mail->addAttachment($_FILES['adjunto']['tmp_name'], $_FILES['adjunto']['name']);
            }
            
            $SMTPOptions = array();
            if($this->empresa->email_config['mail_low_security'])
            {
               $SMTPOptions = array(
                   'ssl' => array(
                       'verify_peer' => false,
                       'verify_peer_name' => false,
                       'allow_self_signed' => true
                   )
               );
            }
            
            if( $mail->smtpConnect($SMTPOptions) )
            {
               if( $mail->send() )
               {
                  $this->new_message('Mensaje enviado correctamente.');
                  
                  /// nos guardamos la fecha de envío
                  if($doc == 'pedido')
                  {
                     $this->pedido->femail = $this->today();
                     $this->pedido->save();
                  }
                  else
                  {
                     $this->albaran->femail = $this->today();
                     $this->albaran->save();
                  }
               }
               else
                  $this->new_error_msg("Error al enviar el email: " . $mail->ErrorInfo);
            }
            else
               $this->new_error_msg("Error al enviar el email: " . $mail->ErrorInfo);
            
            unlink('tmp/'.FS_TMP_NAME.'enviar/'.$filename);
         }
         else
            $this->new_error_msg('Imposible generar el PDF.');
      }
   }
   
   private function fix_html($txt)
   {
      $newt = str_replace('&lt;', '<', $txt);
      $newt = str_replace('&gt;', '>', $newt);
      $newt = str_replace('&quot;', '"', $newt);
      $newt = str_replace('&#39;', "'", $newt);
      return $newt;
   }
   
   private function get_lineas_iva($lineas)
   {
      $retorno = array();
      $lineasiva = array();
      
      foreach($lineas as $lin)
      {
         if( isset($lineasiva[$lin->codimpuesto]) )
         {
            $lineasiva[$lin->codimpuesto]['neto'] += $lin->pvptotal;
            $lineasiva[$lin->codimpuesto]['totaliva'] += ($lin->pvptotal*$lin->iva)/100;
            $lineasiva[$lin->codimpuesto]['totalrecargo'] += ($lin->pvptotal*$lin->recargo)/100;
            $lineasiva[$lin->codimpuesto]['totallinea'] = $lineasiva[$lin->codimpuesto]['neto']
                    + $lineasiva[$lin->codimpuesto]['totaliva'] + $lineasiva[$lin->codimpuesto]['totalrecargo'];
         }
         else
         {
            $lineasiva[$lin->codimpuesto] = array(
                'codimpuesto' => $lin->codimpuesto,
                'iva' => $lin->iva,
                'recargo' => $lin->recargo,
                'neto' => $lin->pvptotal,
                'totaliva' => ($lin->pvptotal*$lin->iva)/100,
                'totalrecargo' => ($lin->pvptotal*$lin->recargo)/100,
                'totallinea' => 0
            );
            $lineasiva[$lin->codimpuesto]['totallinea'] = $lineasiva[$lin->codimpuesto]['neto']
                    + $lineasiva[$lin->codimpuesto]['totaliva'] + $lineasiva[$lin->codimpuesto]['totalrecargo'];
         }
      }
      
      foreach($lineasiva as $lin)
      {
         $retorno[] = $lin;
      }
      
      return $retorno;
   }
    
    
    
    
    
    
    
    
    private function regulariza_foto ($path, $aspect_ratio = true, $width = 1024, $height = 768) {

    if (file_exists($path)) {
        $img = imagecreatefromjpeg($path);
        $w = imagesx($img);
        $h = imagesy($img);
        if ($w != $width) {
            $new_width = $width;
            if ($aspect_ratio == true) {
                $new_height = floor($h * ( $width / $w ));
            } else {
                $new_height = $height;
            }
            $tmp_img = imagecreatetruecolor($new_width, $new_height);
            imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $w, $h);
            imagejpeg($tmp_img, $path);
            }
        }
    }

    public function __construct()
   {
      parent::__construct(__CLASS__, 'Ver pedidos', 'Catalogo', FALSE, FALSE);
      $familia ["codigo"]="";
      $familia ["nombre"]="";
      $familia ["descripcion"]="";
      $familia ["visible"]="";
      $familia ["imagen"]="";
   }
   
   public function imagen_url(){
       
    if (strlen($this->familia["imagen"])>0) {
      return $this->familia["imagen"] . "?" . filemtime($this->familia["imagen"]);         
    } else {
        return FALSE;
    }
    
    }
    
   private function guarda_foto_familia ($codfamilia) {
    
    if ($_FILES["fimagen"]["error"] > 0) {
        $this->new_error_msg("Error al subir el archivo");
        return FALSE;
    } else {
        if ($_FILES["fimagen"]["type"] != "image/jpeg") {
            $this->new_error_msg("Error en el tipo de archivo. Por favor, súbalo en formato JPEG");
        }
        $path = "images/autoventas/familias";
        if (!file_exists($path)) {
            if (!mkdir($path,0777,TRUE)) {
                $this->new_error_msg('Error al crear la carpeta images/autoventas/familias.');
            }
                    
        }
        move_uploaded_file($_FILES["fimagen"]["tmp_name"], $path . "/$codfamilia.jpg");
        $this->regulariza_foto($path . "/$codfamilia.jpg", true, 256);
        
        return $path . "/$codfamilia.jpg";
        
    }
}

private function borra_foto ($codfamilia) {
    
        $path = "images/autoventas/familias/$codfamilia.jpg";
        if (!file_exists($path)) {
            return FALSE;
        } else {
            return unlink($path);
        }
}

   protected function private_core()
   {
       $this->share_extension();
      
       if (isset($_REQUEST["cat"])) {
           switch ($_REQUEST["cat"]) {
               case "fam":
                    if (isset($_REQUEST["cod"])) {
                                $this->codFamilia = $this->db->escape_string($_REQUEST["cod"]);
                                
                    }
                    $catf = new autoventas_familia();
                   if (isset($_REQUEST["action"])) {
                      switch ($_REQUEST["action"]) {
                          case 1: // Actualitzem dades
                                $catf->nombre = $this->db->escape_string($_REQUEST["fam_nombre"]);
                                $catf->descripcion= $this->db->escape_string($_REQUEST["fam_descripcion"]);
                                if (isset( $_REQUEST["fam_visible"])) {
                                    $catf->visible = 1;
                                } else {
                                    $catf->visible = 0;
                                }
                                $catf->codigo = $this->codFamilia;
                                $catf->save();
                                break;
                          case 2: // Pugem foto
                              
                                $imagen = $this->guarda_foto_familia($this->codFamilia);
                                if ($imagen) {
                                    
                                    $this->familia = $catf->load_data($this->codFamilia);  
                                    $catf->codigo=$this->familia["codigo"];
                                    $catf->nombre=$this->familia["nombre"];
                                    $catf->descripcion = $this->familia["descripcion"];
                                    $catf->visible = $this->familia["visible"];
                                    $catf->imagen = $imagen;
                                    $catf->save();
                                    $this->new_message("Imagen subida correctamente : $imagen");
                                }
                                break;
                          case 3: // Borrem foto
                                if ($this->borra_foto($this->codFamilia)) {
                                    $this->new_message("Imagen borrada correctamente");
                                    $this->familia = $catf->load_data($this->codFamilia);  
                                    $catf->codigo=$this->familia["codigo"];
                                    $catf->nombre=$this->familia["nombre"];
                                    $catf->descripcion = $this->familia["descripcion"];
                                    $catf->visible = $this->familia["visible"];
                                    $catf->imagen = "";
                                    $catf->save();                                    
                                } else {
                                    $this->new_error_msg("Hubo un problema a la hora de borrar la imagen");
                                }
                              break;
                          default:
                              $this->new_error_msg("Autoventas: Código de accion desconocido");
                              
                      }
                   } else {
                       $catf->codigo=$this->codFamilia;
                       if (!$catf->exists()) {
                           // No existeix, afegim les dades que tenim per defecte.
                           $fam = new familia();
                           $f1=$fam->get($this->codFamilia);
                           $catf->codigo = $f1->codfamilia;
                           $catf->nombre = $f1->codfamilia;
                           $catf->descripcion=$f1->descripcion;
                           $catf->visible=1;
                           $catf->imagen="";
                           $catf->save();
                       }
                   }
                   
                   $this->familia = $catf->load_data($this->codFamilia);  
                   
                   $this->template="autoventas_familias";
                   
                   break;
                case "firma":
                    if (isset($_REQUEST["tipo"])) {
                        $this->tipo = $_REQUEST ["tipo"];
                        $this->id = (int) $_REQUEST["id"];
                         
                        
                        switch ($this->tipo) {
                            case "albaran":
                                if (isset($_REQUEST["action"])) {
                                    $accion = $_REQUEST["action"];
                                } else {
                                    $accion ="";
                                }
                                require_model ("albaran_cliente.php");
                                
                                $a = new albaran_cliente();
                                $b = $a->get($this->id);
                                $numero2 = $b->numero2;
                                $path = "images/autoventas/firmas";
                                                                   
                                    switch ($accion) {
                                        case "sincroniza":
                                            
                                            $op = new autoventas_opcionesdb();
                                            $opciones  = new autoventas_opcionesdb($op->load());   
                                            
                                            $url = $opciones->url . "/images/autoventas/firmas/$numero2.jpg"; 
                                            if(@get_headers($url)[0] == 'HTTP/1.1 404 Not Found')
                                            {
                                                 $this->new_error_msg('El archivo de firma no existe.'); 
                                            }
                                            else
                                            {
                                                $archivo = file_get_contents($url);
                                                if ($archivo) {
                                                    file_put_contents($path . "/" . $numero2 . ".jpg", $archivo);
                                                }
                                            }                                           
                                        break;
                                        case "borra":
                                            if (file_exists($path . "/$numero2.jpg")) {
                                                unlink($path . "/$numero2.jpg");
                                            }
                                        break;
                                        default:

                                            
                                    }
                                        if (!file_exists($path)) {
                                            if (!mkdir($path,0777,TRUE)) {
                                                $this->new_error_msg('Error al crear la carpeta images/autoventas/familias.');
                                            }
                                        }
                                        if (!file_exists($path . "/$numero2.jpg")) {
                                            $this->firmaUrl ="data:image/png;base64,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";
                                            $this->firmaSolicitar=1;
                                        } else {
                                            $this->firmaUrl = "$path/$numero2.jpg";
                                            $this->firmaSolicitar=0;
                                        }
                                
                                break;
                            case "pedido":
                                if (isset($_REQUEST["action"])) {
                                    $accion = $_REQUEST["action"];
                                } else {
                                    $accion ="";
                                }
                                require_model ("pedido_cliente.php");
                                
                                $a = new pedido_cliente();
                                $b = $a->get($this->id);
                                $numero2 = $b->numero2;
                                $path = "images/autoventas/firmas";
                                                                   
                                    switch ($accion) {
                                        case "sincroniza":
                                            
                                            $op = new autoventas_opcionesdb();
                                            $opciones  = new autoventas_opcionesdb($op->load());   
                                            
                                            $url = $opciones->url . "/images/autoventas/firmas/$numero2.jpg"; 
                                            if(@get_headers($url)[0] == 'HTTP/1.1 404 Not Found')
                                            {
                                                 $this->new_error_msg('El archivo de firma no existe.'); 
                                            }
                                            else
                                            {
                                                $archivo = file_get_contents($url);
                                                if ($archivo) {
                                                    file_put_contents($path . "/" . $numero2 . ".jpg", $archivo);
                                                }
                                            }                                           
                                        break;
                                        case "borra":
                                            if (file_exists($path . "/$numero2.jpg")) {
                                                unlink($path . "/$numero2.jpg");
                                            }
                                        break;
                                        default:

                                            
                                    }
                                        if (!file_exists($path)) {
                                            if (!mkdir($path,0777,TRUE)) {
                                                $this->new_error_msg('Error al crear la carpeta images/autoventas/familias.');
                                            }
                                        }
                                        if (!file_exists($path . "/$numero2.jpg")) {
                                            $this->firmaUrl ="data:image/png;base64,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";
                                            $this->firmaSolicitar=1;
                                        } else {
                                            $this->firmaUrl = "$path/$numero2.jpg";
                                            $this->firmaSolicitar=0;
                                        }
                                                                
                                break;
                            default:
                                $this->new_error_msg("Autoventas: Tipo de documento desconocido");
                        }
                     
                    }
                    $this->template="autoventas_firma";
                break;
                case "imprimir":
                    
                    
                    
                    
                          $this->albaran = FALSE;
      $this->cliente = FALSE;
      $this->pedido = FALSE;
      $this->impuesto = new impuesto();
      
      /// obtenemos los datos de configuración de impresión
      $this->impresion = array(
          'print_ref' => '1',
          'print_dto' => '1',
          'print_alb' => '0',
          'print_formapago' => '1'
      );
      $fsvar = new fs_var();
      $this->impresion = $fsvar->array_get($this->impresion, FALSE);
      
      $this->logo = FALSE;
      if( file_exists('tmp/'.FS_TMP_NAME.'logo.png') )
      {
         $this->logo = 'tmp/'.FS_TMP_NAME.'logo.png';
      }
      else if( file_exists('tmp/'.FS_TMP_NAME.'logo.jpg') )
      {
         $this->logo = 'tmp/'.FS_TMP_NAME.'logo.jpg';
      }
      
      if( isset($_REQUEST['albaran']) AND isset($_REQUEST['id']) )
      {
         $alb = new albaran_cliente();
         $this->albaran = $alb->get($_REQUEST['id']);
         if($this->albaran)
         {
            $cliente = new cliente();
            $this->cliente = $cliente->get($this->albaran->codcliente);
         }
         
         if( isset($_POST['email']) )
         {
            $this->enviar_email('albaran');
         }
         else
            $this->generar_pdf_albaran();
      }
      else if( isset($_REQUEST['pedido']) AND isset($_REQUEST['id']) )
      {
          require_model ("pedido_cliente.php");
          
         $fac = new pedido_cliente();
         $this->pedido = $fac->get($_REQUEST['id']);
         if($this->pedido)
         {
            $cliente = new cliente();
            $this->cliente = $cliente->get($this->pedido->codcliente);
         }
         
         if( isset($_POST['email']) )
         {
            $this->enviar_email('pedido');
         }
         else
            $this->generar_pdf_pedido();
      }
                    
                    
                    
                    
                break;
               default:
                  $this->new_error_msg('Autoventas: Error categoría desconocida');
           }
       } else {
           $this->new_error_msg('Autoventas: Error sin categoría');
           
       }
                
   }  
   
   private function share_extension()
   {
      $extensiones = array(
          array(
              'name' => 'autoventas_familias',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_familia',
              'type' => 'tab',
              'text' => '<span class="glyphicon glyphicon-book"></span><span class="hidden-xs">&nbsp; Autoventas</span>',
              'params' => '&cat=fam'
        ),
        array(
              'name' => 'autoventas_firmasalbaranes',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_albaran',
              'type' => 'tab',
              'text' => '<span class="glyphicon glyphicon-pencil" aria-hidden="true" title="Firma">&nbsp; Autoventas</span>',
              'params' => '&cat=firma&tipo=albaran'
          ),
          array(
              'name' => 'autoventas_firmaspedidos',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_pedido',
              'type' => 'tab',
              'text' => '<span class="glyphicon glyphicon-pencil" aria-hidden="true" title="Firma">&nbsp; Autoventas</span>',
              'params' => '&cat=firma&tipo=pedido'
          ),
                    array(
              'name' => 'autoventas_imprimir_albaran',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_albaran',
              'type' => 'pdf',
              'text' => ucfirst(FS_ALBARAN).' con firma',
              'params' => '&cat=imprimir&albaran=TRUE'
          ),
          array(
              'name' => 'autoventas_email_albaran',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_albaran',
              'type' => 'email',
              'text' => ucfirst(FS_ALBARAN).' con firma',
              'params' => '&cat=imprimir&albaran=TRUE'
          ),
          array(
              'name' => 'autoventas_imprimir_pedido',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_pedido',
              'type' => 'pdf',
              'text' => ucfirst(FS_PEDIDO).' con firma',
              'params' => '&cat=imprimir&pedido=TRUE'
          ),
          array(
              'name' => 'autoventas_email_pedido',
              'page_from' => __CLASS__,
              'page_to' => 'ventas_pedido',
              'type' => 'email',
              'text' => ucfirst(FS_PEDIDO).' con firma',
              'params' => '&cat=imprimir&pedido=TRUE'
          )
          );
      
      foreach($extensiones as $ext)
      {
         $fsext = new fs_extension($ext);
         $fsext->save();
      }
   }
   
}
