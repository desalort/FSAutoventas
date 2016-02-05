<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of catalogo_familia
 *
 * @author user
 */
class autoventas_opcionesdb extends fs_model {
    
    public $url;
    public $activo;
    public $cron;
    public $tiempocron;
    public $last_sincro;
    public $ftp_url;
    public $ftp_user;
    public $ftp_pass;
    public $ftp_dir;
    
    
    public function __construct($a = FALSE)
   {
    parent::__construct('autoventas_opciones','plugins/autoventas/');
    
    if ($a) {
        $this->url = $this->escape_string($a["url"]);
        $this->activo = (int) $a["activo"];
        $this->cron = (int) $a["cron"];
        $this->tiempocron = (int) $a["tiempocron"];        
        $this->last_sincro = (int) $a["last_sincro"];
        $this->ftp_url = $this->escape_string($a["ftp_url"]);
        $this->ftp_user = $this->escape_string($a["ftp_user"]);
        $this->ftp_pass = $this->escape_string($a["ftp_pass"]);
        $this->ftp_dir = $this->escape_string($a["ftp_dir"]);
    } else {
        $this->url=NULL;
        $this->activo = 0;
        $this->cron = 0;
        $this->tiempocron = 5;
        $this->last_sincro = 0;        
        $this->ftp_url=NULL;
        $this->ftp_user=NULL;
        $this->ftp_pass=NULL;
        $this->ftp_dir=NULL;
    }
   }
   
   protected function install()
   {
      return 'INSERT INTO autoventas_opciones (url, activo, cron, tiempocron, last_sincro, ftp_url, ftp_user, ftp_pass, ftp_dir) VALUES ("", 0, 0, 5,0, "", "", "", "");';
   }
   
   public function exists()
   {
        return TRUE;
   }
   
   public function save()
   {
    $sql = "UPDATE autoventas_opciones SET url='". $this->url . "', activo='" . $this->activo . "', cron='" . $this->cron . "', tiempocron= '" 
            . $this->tiempocron . "', ftp_url='" . $this->ftp_url . "', ftp_user='" . $this->ftp_user . "', ftp_pass='" . $this->ftp_pass . "',"
            . " ftp_dir='" . $this->ftp_dir . "';";    
    return $this->db->exec($sql);
   }
   
   public function delete()
   {
       
   }
   
   public function all()
   {
   }
   
   public function sincro() {
    $sql = "UPDATE autoventas_opciones SET last_sincro=" . date ("U") . ";";
    return $this->db->exec($sql);       
   }
   
   public function load()
    {
    $a = $this->db->select("SELECT * FROM autoventas_opciones;");
    if (count($a)>0) {   
        return $a[0];
    } else {
        return FALSE;
    }
   }
   
   
   
    
}
