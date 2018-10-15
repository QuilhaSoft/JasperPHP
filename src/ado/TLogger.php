<?php
/*
* classe TLogger
* Esta classe provê uma interface abstrata para definição de algoritmos de LOG
*
* @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
* @version  2015.03.10
* @access   restrict
* 
* 2015.03.10 -- criação
**/
namespace JasperPHP\ado;
abstract class TLogger
{
    protected $filename;  // local do arquivo de LOG

    /*
    * método __construct()
    * instancia um logger
    * @param $filename = local do arquivo de LOG
    */
    public function __construct($filename = null)
    {
        if($filename)
        {
            $this->filename = $filename;
            // reseta o conteúdo do arquivo
            file_put_contents($filename, '');
        }
    }

    // define o método write como obrigatório
    abstract function write($message);
}
?>