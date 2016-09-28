<?php
/*
 * classe TTransaction
 * esta classe provъ os mщtodos necessсrios manipular transaчѕes
 *
 * @author   Rogerio Muniz de Castro <rogerio@singularsistemas.net>
 * @version  2015.03.10
 * @access   restrict
 * 
 * 2015.03.10 -- criaчуo
**/

namespace JasperPHP\ado;

final class TTransaction
{
    private static $conn;   // conexуo ativa
    private static $logger; // objeto de LOG
    
    /*
     * mщtodo __construct()
     * Estс declarado como private para impedir que se crie instтncias de TTransaction
     */
    private function __construct() {}
    
    /*
     * mщtodo open()
     * Abre uma transaчуo e uma conexуo ao BD
     * @param $database = nome do banco de dados
     */
    public static function open($database)
    {
        // abre uma conexуo e armazena na propriedade estсtica $conn
        if (empty(self::$conn))
        {
            self::$conn = TConnection::open($database);
            // inicia a transaчуo
            self::$conn->beginTransaction();
            // desliga o log de SQL
            self::$logger = NULL;
        }
    }
    
    /*
     * mщtodo get()
     * retorna a conexуo ativa da transaчуo
     */
    public static function get()
    {
        // retorna a conexуo ativa
        return self::$conn;
    }
    
    /*
     * mщtodo rollback()
     * desfaz todas operaчѕes realizadas na transaчуo
     */
    public static function rollback()
    {
        if (self::$conn)
        {
            // desfaz as operaчѕes realizadas durante a transaчуo
            self::$conn->rollback();
            self::$conn = NULL;
        }
    }
    
    /*
     * mщtodo close()
     * Aplica todas operaчѕes realizadas e fecha a transaчуo
     */
    public static function close()
    {
        if (self::$conn)
        {
            // aplica as operaчѕes realizadas
            // durante a transaчуo
            self::$conn->commit();
            self::$conn = NULL;
        }
    }
    
    /*
     * mщtodo setLogger()
     * define qual estratщgia (algoritmo de LOG serс usado)
     */
    public static function setLogger(TLogger $logger)
    {
        self::$logger = $logger;
    }
    
    /*
     * mщtodo log()
     * armazena uma mensagem no arquivo de LOG
     * baseada na estratщgia ($logger) atual
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
?>