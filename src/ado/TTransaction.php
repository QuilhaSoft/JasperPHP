<?php
/*
 * classe TTransaction
 * esta classe provê os métodos necessários manipular transações
 *
 * @author   Rogerio Muniz de Castro <rogerio@singularsistemas.net>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- criação
**/

namespace JasperPHP\ado;

final class TTransaction
{
    private static $conn;   // conexão ativa
    private static $logger; // objeto de LOG
    private static $fake; //conexão somente leitura?
    
    /*
     * método __construct()
     * Está declarado como private para impedir que se crie instâncias de TTransaction
     */
    private function __construct() {}
    
    /*
     * método open()
     * Abre uma transação e uma conexão ao BD
     * @param $database = nome do banco de dados
     * @param $dbinfo = conexão via array
     */
    public static function open($database, $dbinfo = NULL)
    {
        // abre uma conexão e armazena na propriedade estática $conn
        if (empty(self::$conn))
        {
            if ($dbinfo)
            {
                self::$conn = TConnection::openArray($dbinfo);                
            }else{
                $dbinfo = TConnection::getDatabaseInfo($database);
                self::$conn = TConnection::open($database);                
            }
            
            //verifica se a conexão é fake [evita locks tables]
            self::$fake = isset($dbinfo['fake']) ? $dbinfo['fake'] : FALSE;
            if(!self::$fake){
                self::$conn->beginTransaction();
            }
            
            // desliga o log de SQL
            self::$logger = NULL;
        }
    }    

    
    /*
     * método get()
     * retorna a conexão ativa da transação
     */
    public static function get()
    {
        // retorna a conexão ativa
        return self::$conn;
    }
    
    /*
     * método rollback()
     * desfaz todas operações realizadas na transação
     */
    public static function rollback()
    {
        if (self::$conn)
        {
            // desfaz as operações realizadas durante a transação
            self::$conn->rollback();
            self::$conn = NULL;
        }
    }
    
    /*
     * método close()
     * Aplica todas operações realizadas e fecha a transação
     */
    public static function close()
    {
        if (self::$conn)
        {
            // aplica as operações realizadas
            // durante a transação
            if(!self::$fake){
            self::$conn->commit();
            }
            self::$conn = NULL;
        }
    }
    
    /*
     * método setLogger()
     * define qual estratégia (algoritmo de LOG será usado)
     */
    public static function setLogger(TLogger $logger)
    {
        self::$logger = $logger;
    }
    
    /*
     * método log()
     * armazena uma mensagem no arquivo de LOG
     * baseada na estratégia ($logger) atual
     */
    public static function log($message)
    {
        // verifica existe um logger
        if (self::$logger)
        {
            self::$logger->write($message);
        }
    }
}
