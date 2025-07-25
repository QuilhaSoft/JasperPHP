<?php
namespace JasperPHP\database;

/**
 * TLoggerHTML class.
 *
 * Implements an HTML logging algorithm.
 */
class TLoggerHTML extends TLogger
{
    /*
     * escreve uma mensagem no arquivo de LOG
     * @param string $message to write into a log file
     */
    public function write($message)
    {
        $time = date("Y-m-d H:i:s");
        
        $text = "<p>\n";
        $text.= "   <b>$time</b> : \n";
        $text.= "   <i>$message</i> <br>\n";
        $text.= "</p>\n";
        
        $handler = fopen($this->filename, 'a');
        fwrite($handler, $text);
        fclose($handler);
    }
}