<?php

namespace JasperPHP\database;

/**
 * TLogger abstract class.
 *
 * Provides an abstract interface for logging classes.
 */
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