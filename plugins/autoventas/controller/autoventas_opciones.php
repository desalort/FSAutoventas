<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of catalogo_general
 *
 * @author user
 */

require_model ("autoventas_opcionesdb.php");

class autoventas_opciones extends fs_controller {
    
    
    public $opciones;
    
    public function fecha_sincro (){
        return date ("d/m/Y H:i:s", $this->opciones->last_sincro);
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
    
    private function sincroniza_fotos_articulos () {
        $path1 = dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/articulos";
        $path2 = dirname($_SERVER["SCRIPT_FILENAME"]) . "/images/autoventas/articulos";
        
        $d = dir($path1);
            while (false !== ($entry = $d->read())) {
                
                $guardar=0;
                if (substr($entry,-6) =="-1.jpg") {
                    $fmod = filemtime("$path1/$entry");
                    if (file_exists("$path2/$entry")) {
                        $fmod1 = filemtime(("$path2/$entry"));
                        if ($fmod > $fmod1) {
                           $guardar=1; 
                        }
                    } else {
                        $guardar=1;
                    }
                }
                if ($guardar==1) {
                    copy("$path1/$entry", "$path2/$entry");
                    $this->regulariza_foto("$path2/$entry", TRUE, 256);
                }
            }
        $d->close();
        
    }
    private function getFileList($path) {
		$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path,
				\FilesystemIterator::CURRENT_AS_FILEINFO |
				\FilesystemIterator::SKIP_DOTS
		));

		//$pathPrefixLength = strlen($path) +1;
		$files = array();
		foreach ($iterator as $fileInfo) {
                   // print_r($fileInfo);
			$fullPath = str_replace(DIRECTORY_SEPARATOR, '/', $fileInfo->getPathname());	
			$files[$fullPath] = array('size' => $fileInfo->getSize(), 'timestamp' => $fileInfo->getMTime());
		}

		return $files;
    }

    private function recoge_articulos() {       
        
        $b = array();
        
        $a= $this->db->select("SELECT * FROM articulos WHERE sevende=1 ORDER BY referencia ASC");
        
        if (count($a)>0) {
            for ($i=0;$i<count($a);$i++) {
                $art ["referencia"] = $a[$i]["referencia"];
                $art ["codfamilia"] = $a[$i]["codfamilia"];
                $art ["pvp"] = $a[$i]["pvp"];
                $art ["descripcion"] = $a[$i]["descripcion"];
                $b[] = $art;
            }
        }
        
        return ($b);
    }

    private function recoge_tarifas() {       
        
        $b = array();
        
        $a= $this->db->select("SELECT * FROM tarifas ORDER BY nombre ASC");
        
        if (count($a)>0) {
            for ($i=0;$i<count($a);$i++) {
                $art ["nombre"] = $a[$i]["nombre"];
                $art ["incporcentual"] = $a[$i]["incporcentual"];
                $art ["inclineal"] = $a[$i]["inclineal"];
                $b[] = $art;
            }
        }
        
        return ($b);
    }
    
    private function recoge_empresa() {       
        
        $b = array();
        
        $a= $this->db->select("SELECT * FROM empresa");
        
        if (count($a)>0) {            
            $art ["nombre"] = $a[0]["nombre"];
            $art ["direccion"] = $a[0]["direccion"];
            $art ["cifnif"] = $a[0]["cifnif"];
            $art ["email"] = $a[0]["email"];
            $art ["fax"] = $a[0]["fax"];
            $art ["logo"] = $a[0]["logo"];
            $art ["telefono"] = $a[0]["telefono"];
            $art ["web"] = $a[0]["web"];
            $b[] = $art;
        }
        
        return ($b);
    }
    private function recoge_familias () {
        $b = array();
        
        $a= $this->db->select("SELECT * FROM catalogo_familia ORDER BY codigo ASC");
        
        if (count($a)>0) {
            for ($i=0;$i<count($a);$i++) {
                $c = $this->db->select("SELECT * FROM articulos WHERE codfamilia='" . $a[$i]["codigo"]);
                if (count($c)>0) {
                    $art ["codfamilia"] = $a[$i]["codigo"];
                    $art ["descripcion"] = $a[$i]["descripcion"];
                    $art ["nombre"] = $a[$i]["nombre"];
                    $b[] = $art;
                }
            }
        }
        
        $a = $this->db->select("SELECT * FROM familias ORDER BY codfamilia") ;
        for ($i=0;$i<count($a);$i++) {
            $trobat = 0;
            for ($j=0; $j<count($b);$j++) {
                if ($b[$j]["codfamilia"]== $a[$i]["codfamilia"]) {
                    $trobat=1;
                    continue;
                }
            }
            if ($trobat==0) {
                $c = $this->db->select("SELECT * FROM articulos WHERE codfamilia='" . $a[$i]["codfamilia"]);
                if (count($c)>0) {
                    $art ["codfamilia"] = $a[$i]["codfamilia"];
                    $art ["descripcion"] = $a[$i]["descripcion"];
                    $art ["nombre"] = $a[$i]["codfamilia"];
                    $b[] = $art;                
                }
            }
        }
        
        return ($b);
    }
    
    private function recoge_agentes () {
        
        $b = array();
        
        $a= $this->db->select("SELECT * FROM fs_users ORDER BY codagente ASC");
        
        if (count($a)>0) {
            for ($i=0;$i<count($a);$i++) {
                $art ["codagente"] = $a[$i]["codagente"];
                $art ["nick"] = $a[$i]["nick"];
                $art ["password"] = $a[$i]["password"];               
                $art ["admin"] = $a[$i]["admin"];
                $b[] = $art;
            }
        }
        
        return ($b);
    }
  
        private function recoge_series () {
        
        $b = array();
        
        $a= $this->db->select("SELECT * FROM series ORDER BY codserie ASC");
        
        if (count($a)>0) {
            for ($i=0;$i<count($a);$i++) {
                $art ["codserie"] = $a[$i]["codserie"];
                $art ["siniva"] = $a[$i]["siniva"];
                $b[] = $art;
            }
        }
        
        return ($b);
    }
    
    private function recoge_formaspago () {
        
        $b = array();
        
        $a= $this->db->select("SELECT * FROM formaspago ORDER BY codpago ASC");
        
        if (count($a)>0) {
            for ($i=0;$i<count($a);$i++) {
                $art ["codpago"] = $a[$i]["codpago"];
                $art ["descripcion"] = $a[$i]["descripcion"];
                $b[] = $art;
            }
        }
        
        return ($b);
    }
    
    private function recoge_clientes () {
        
        $b = array();
        
        $sql = "SELECT * FROM clientes WHERE debaja=0 ORDER BY codcliente ASC";
        
        $a= $this->db->select($sql);
        
        if (count($a)>0) {
            for ($i=0;$i<count($a);$i++) {
                $art ["codagente"] = $a[$i]["codagente"];
                $art ["codcliente"] = $a[$i]["codcliente"];
                $art ["codserie"] = $a[$i]["codserie"];
                $art ["nombre"] = $a[$i]["nombre"];
                $art ["razonsocial"] = $a[$i]["razonsocial"];
                $art ["telefono1"] = $a[$i]["telefono1"];
                $art ["telefono2"] = $a[$i]["telefono2"];
                $art ["email"] = $a[$i]["email"];
                $art["direccion"] = "";
                $sql = "SELECT direccion FROM dirclientes WHERE domenvio=1 AND codcliente='" . $a[$i]["codcliente"] . "';";
                
                $c = $this->db->select($sql);
                if (count($c)>0) {
                    $art["direccion"] = $c[0]["direccion"];
                }
                $b[] = $art; 
            }
        }
        
        return ($b);        
    }
    
    private function curl_post($url, array $post = NULL, array $options = array())
    {
        $post = json_encode($post);

        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_POSTFIELDS => $post
        );

        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        $result= curl_exec($ch);
        if( $result === FALSE)
        {
            echo "ERROR: " . curl_errno($ch) . " : " . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }     
    public function __construct()
   {
      parent::__construct(__CLASS__, 'Opciones', 'Autoventas');
   }
   
   protected function private_core()
   {
      $op = new autoventas_opcionesdb();
      $this->opciones= new autoventas_opcionesdb($op->load());      
      
      if (isset($_GET["action"])) {
          switch ($_GET["action"]) {
              case 1: // modificar
                $this->opciones->url = $this->db->escape_string($_GET["url"]);                                    
                $this->opciones->activo = (isset($_GET["activo"])?1:0);
                $this->opciones->cron = (isset($_GET["cron"])?1:0);
                $this->opciones->tiempocron = (int) $_GET["tiempocron"];
                $this->opciones->ftp_url = $this->db->escape_string($_GET["ftp_url"]);                  
                $this->opciones->ftp_user = $this->db->escape_string($_GET["ftp_user"]);                  
                $this->opciones->ftp_pass = $this->db->escape_string($_GET["ftp_pass"]);                  
                $this->opciones->ftp_dir = $this->db->escape_string($_GET["ftp_dir"]);                  
                $this->opciones->save();
                  break;
              case 2: // Sincronitzar
                  $articulos = $this->recoge_articulos();
                  $familias = $this->recoge_familias();
                  $agentes = $this->recoge_agentes();
                  $series = $this->recoge_series();
                  $tarifas = $this->recoge_tarifas();
                  $formas = $this->recoge_formaspago();
                  $clientes = $this->recoge_clientes();
                  $empresa = $this->recoge_empresa();
                  $enviar ["articulos"] = $articulos;
                  $enviar ["familias"] = $familias;
                  $enviar ["agentes"] = $agentes;
                  $enviar ["series"] = $series;
                  $enviar ["tarifas"] = $tarifas;
                  $enviar ["formas"] = $formas;
                  $enviar ["clientes"] = $clientes;
                  $enviar ["empresa"] = $empresa;
                  $url = $this->opciones->url . "/sync/interfaz.php";                  
 
                  $peticion = array(
                    'comando' => 'actualizar',
                    'datos' => $enviar
                   );
                  $result = $this->curl_post($url, $peticion);
                  echo "<b>DATOS:</b> $result";
                   
                  
                  $this->sincroniza_fotos_articulos();
                  $archivos = $this->getFileList("images/autoventas");
                  
                  $peticion = array(
                    'comando' => 'archivos',
                    'datos' => $archivos
                   );
                  $result = $this->curl_post($url, $peticion);
                  $sincroniza = json_decode($result, true);
                  
                  $enviados = $realizados = 0;
                  foreach ($sincroniza["data"] as $k=>$v) {
                     //echo "SINCRONIZANDO ...  $k<br>";
                      if (strpos($k, '..') == false) {
                          if (file_exists($k)) {
                              $enviados +=1;
                       //       echo "Enviando... $k<br>";
                              $fp = fopen($k, 'rb');
                              $datos = fread($fp,filesize($k));
                              fclose($fp);
                                $peticion = array(
                                    'comando' => 'archivo',
                                    'nombre' => $k,
                                    'timestamp' =>$v["timestamp"],
                                    'data'=> base64_encode($datos)
                                );
                                $result = $this->curl_post($url, $peticion);          
                         //       echo "<br>ENS ARRIBA : $result";
                                if ($result=="OK") {
                                    $realizados +=1;
                                }
                            }
                        }
                        
                  }
                  echo " <b>ARCHIVOS:</b> $realizados / $enviados";
                  
                  $this->opciones->last_sincro = date("U");
                  $this->opciones->sincro();
                  echo " <b>LAST SINCRO:</b>" . $this->fecha_sincro();

                  $this->template="";
              break;
              case 3: // Recoger pedidos pendientes
                  $url = $this->opciones->url . "/sync/interfaz.php";                  
                $peticion = array(
                    'comando' => 'pedidos'
                );
                $result = $this->curl_post($url, $peticion);                            
                $valores=json_decode($result);
                //print_r($valores);
                require_once ("/plugins/autoventas/extras/autoventas_pedidos.php");
                $objpedidos = new autoventas_pedidos();
                $respuesta = $objpedidos->trata_pedidos($valores);
                echo $respuesta;
                $this->template="";
              break;
              default:
                  
          }
      }
      //print_r($this->opciones);
      
   }
   
   
   
}
