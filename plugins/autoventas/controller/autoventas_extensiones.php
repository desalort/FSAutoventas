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

class autoventas_extensiones extends fs_controller {
    
    public $codFamilia;
    public $familia;
    
    
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
                              $this->new_error_msg("Código de accion desconocido");
                              
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
               default:
                  $this->new_error_msg('Error categoría desconocida');
           }
       } else {
           $this->new_error_msg('Error sin categoría');
           
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
        ));
      foreach($extensiones as $ext)
      {
         $fsext = new fs_extension($ext);
         $fsext->save();
      }
   }
   
}
