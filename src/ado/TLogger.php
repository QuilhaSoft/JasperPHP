<?php

/*
 * classe TLogger
 * This class take a abstract interface to any log classes
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- create
 * */

namespace JasperPHP\ado;

abstract class TLogger {

    protected $filename;

    /*
     * @author Rogerio Muniz de Castro
     * @param string $filename to write message logs
     */

    public function __construct($filename = null) {
        if ($filename) {
            $this->filename = $filename;
            // reseta o conteúdo do arquivo
            file_put_contents($filename, '');
        }
    }

    /*
     * @author Rogerio Muniz de Castro
     * @param string $message
     */

    abstract function write($message);
}