<?php
/**
 * Created by PhpStorm.
 * User: pavel
 * Date: 29.11.2017
 * Time: 17:31
 */

class db_pdo
{
    protected $pdo;
    protected $exname;
    public $connect;

    protected $db_defaults = array(
        'host' => 'localhost',
        'db' => 'test',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8',
        'exception' => 'Exception'
    );
    protected $opt_defaults = array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    );

    /**
     * DbPdo constructor.
     * @param array $db_config
     * @param array $options
     */
    function __construct($db_config=array(), $options = array())
    {
        $db_options = is_array($db_config) ? array_merge($this->db_defaults, $db_config) : $this->db_defaults;

        $dsn = "mysql:host={$db_options['host']};dbname={$db_options['db']};charset={$db_options['charset']}";
        $user = $db_options['user'];
        $pass = $db_options['pass'];
        $this->exname = $db_options['exception'];

        $opt = is_array($options) ? array_merge($this->opt_defaults, $options) : $this->opt_defaults;
        @$this->pdo = new PDO($dsn, $user, $pass, $opt);

        unset($options); unset($db_config);
    }



    public function run($sql, $args = NULL) {
        if (!$args){
            return $this->pdo->query($sql);
        }
        //echo "<pre><br>$sql<br>"; print_r($args); echo "</pre>";
        $res = $this->pdo->prepare($sql);
        //if (!is_array($args)) {var_dump($args);}
        $res->execute($args);
        return $res;
    }

    /**
     * Conventional function to get last insert id.
     *
     * @return int whatever mysqli_insert_id returns
     */
    public function insertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Conventional function to rollback last transaction.
     *
     * @return bool whatever rollBack is completed
     */
    public function rollBack() {
        /*todo: Проверить!*/
        return $this->pdo->rollBack();
    }

    protected function createSET($data)
    {
        if (!is_array($data)) {
            $this->error("SET (?u) placeholder expects array, ".gettype($data)." given");
            return;
        }
        if (!$data) {
            $this->error("Empty array for SET (?u) placeholder");
            return;
        }
        $query = $comma = '';
        foreach ($data as $key => $value) {
            $query .= $comma.$this->escapeIdent($key).'='.$this->escapeString($value);
            $comma  = ",";
        }
        return $query;
    }

}