<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of documentos_procli
 *
 * @author jcanda
 */

require_model('cliente.php');
require_model('proveedor.php');

class documentos_procli extends fs_controller {

    public $documentos;
    public $proveedor;
    public $cliente;
    public $cod;

    public function __construct() {
        parent::__construct(__CLASS__, 'Documentos', 'compras', FALSE, FALSE);
    }

    protected function private_core() {
        $this->share_extension();
        $this->documentos = array();

        if (isset($_GET['folder']) AND isset($_GET['cod'])  ) {
            
            //Primero cargamos el proveedor o cliente segun sea
            if ($_GET['folder']=='proveedor'){
                $proveedor = new proveedor();
                $this->proveedor = $proveedor->get($_GET['cod']);
                $this->cod = $_GET['cod'];
            }else{
                $cliente = new cliente();
                $this->cliente = $cliente->get($_GET['cod']);
                $this->cod = $_GET['cod'];
            }
            //Luego si no existen documentos crea directorios
            if (!file_exists('tmp/' . FS_TMP_NAME . 'documentos_procli')) {
                mkdir('tmp/' . FS_TMP_NAME . 'documentos_procli');
            }

            if (!file_exists('tmp/' . FS_TMP_NAME . 'documentos_procli/' . $_GET['folder'])) {
                mkdir('tmp/' . FS_TMP_NAME . 'documentos_procli/' . $_GET['folder']);
            }
            //PAra subir archivos
            if (isset($_POST['upload'])) {
                if (is_uploaded_file($_FILES['fdocumento']['tmp_name'])) {
                    if (!file_exists('tmp/' . FS_TMP_NAME . 'documentos_procli/' . $_GET['folder'] . '/' . $this->cod)) {
                        mkdir('tmp/' . FS_TMP_NAME . 'documentos_procli/' . $_GET['folder'] . '/' . $this->cod);
                    }

                    copy($_FILES['fdocumento']['tmp_name'], "tmp/" . FS_TMP_NAME . "documentos_procli/" . $_GET['folder'] . '/' . $this->cod . '/' . $_FILES['fdocumento']['name']);
                    $this->new_message('Documentos añadido correctamente.');
                }
            } 
            //Para eliminar archivos
            else if (isset($_GET['delete'])) {
                if (file_exists('tmp/' . FS_TMP_NAME . 'documentos_procli/' . $_GET['folder'] . '/' . $this->cod . '/' . $_GET['delete'])) {
                    if (unlink('tmp/' . FS_TMP_NAME . 'documentos_procli/' . $_GET['folder'] . '/' . $this->cod . '/' . $_GET['delete'])) {
                        $this->new_message('Archivo ' . $_GET['delete'] . ' eliminado correctamente.');
                    } else
                        $this->new_error_msg('Error al eliminar el archivo ' . $_GET['delete'] . '.');
                } else
                    $this->new_error_msg('Archivo no encontrado.');
            }
            //Si no finalmente enseñamos todos los documentos para este cliente o proveedor
            $this->documentos = $this->get_documentos();
        }
    }

    private function share_extension() {
        $extensiones = array(
            array(
                'name' => 'documentos_cli',
                'page_from' => __CLASS__,
                'page_to' => 'ventas_cliente',
                'type' => 'tab',
                'text' => '<span class="glyphicon glyphicon-file" title="Documentos"></span> &nbsp; Documentos',
                'params' => '&folder=cliente'
            ),
            array(
                'name' => 'documentos_prov',
                'page_from' => __CLASS__,
                'page_to' => 'compras_proveedor',
                'type' => 'tab',
                'text' => '<span class="glyphicon glyphicon-file" title="Documentos"></span> &nbsp; Documentos',
                'params' => '&folder=proveedor'
            ),
        );
        foreach ($extensiones as $ext) {
            $fsext = new fs_extension($ext);
            $fsext->save();
        }
    }

    public function url() {
        if (isset($_GET['folder']) AND isset($_GET['cod'])) {
            return 'index.php?page=' . __CLASS__ . '&folder=' . $_GET['folder'] . '&cod=' . $_GET['cod'];
        } else
            return parent::url();
    }

    private function get_documentos() {
        $doclist = array();
        $folder = 'tmp/' . FS_TMP_NAME . 'documentos_procli/' . $_GET['folder'] . '/' . $this->cod;

        if (file_exists($folder)) {
            foreach (scandir($folder) as $f) {
                if ($f != '.' AND $f != '..') {
                    $doclist[] = array(
                        'name' => (string) $f,
                        'fullname' => $folder . '/' . $f,
                        'filesize' => $this->human_filesize(filesize(getcwd() . '/' . $folder . '/' . $f)),
                        'date' => date("d-m-Y H:i:s.", filemtime(getcwd() . '/' . $folder . '/' . $f))
                    );
                }
            }
        }

        return $doclist;
    }

    private function human_filesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

}
