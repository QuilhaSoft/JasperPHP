<?php
/*
 * classe TConnection
 * Manage conection with databases, based on configuration files
 *
 * @author   Rogerio Muniz de Castro <rogerio@quilhasoft.com.br>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- create
**/
namespace JasperPHP\ado;
use PDO;
 
final class TConnection
{
    /*
     * method __construct()
     */
    private function __construct() {}
    
    /*
     * open a conection to a database
     * @author Rogerio Muniz de Castro
     * @param sring $name a name of configuration file without extention
     * @return PDO object
     */
    public static function open($name)
    {
        if (file_exists($name))
        {
            $db = parse_ini_file($name);
        }
        elseif (file_exists("config/{$name}.ini"))
        {
            $db = parse_ini_file("config/{$name}.ini");
        }
        else
        {
            throw new Exception("Arquivo '$name' não encontrado");
        }
        $user = isset($db['user']) ? $db['user'] : NULL;
        $pass = isset($db['pass']) ? $db['pass'] : NULL;
        $name = isset($db['name']) ? $db['name'] : NULL;
        $host = isset($db['host']) ? $db['host'] : NULL;
        $type = isset($db['type']) ? $db['type'] : NULL;
        $port = isset($db['port']) ? $db['port'] : NULL;
        
        switch ($type)
        {
            case 'pgsql':
                $port = $port ? $port : '5432';
                $conn = new PDO("pgsql:dbname={$name}; user={$user}; password={$pass};
                        host=$host;port={$port}");
                break;
            case 'mysql':
                $port = $port ? $port : '3306';
                $conn = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8", $user, $pass);
                break;
            case 'sqlite':
                $conn = new PDO("sqlite:{$name}");
                break;
            case 'ibase':
                $conn = new PDO("firebird:dbname={$name}", $user, $pass);
                break;
            case 'oci8':
                $conn = new PDO("oci:dbname={$name}", $user, $pass);
                break;
            case 'mssql':
                $conn = new PDO("mssql:host={$host},1433;dbname={$name}", $user, $pass);
                break;
        }
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    }
}