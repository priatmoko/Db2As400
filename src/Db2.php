<?php 

namespace Masterpis\Db2as400;

/**
 * Db2As400 is a collection of attributes and methods to simplify connection to DB2 As400.
 * The main purpose of this library is standarization of code in my small organization. 
 * It will provide basic operation of database connection
 * 
 * This library is built on lumen, laravel
 * 
 * Class Db2 provide instance methods to connect database Db2 AS400, driver selection, method compatible with driver
 * Using this library will make you use same name method to operate connection and query.
 * 
 * 
 * @link masterpis.com
 * @since v1.0.0
 * @author Priatmoko <priatmoko.informatics@gmail.com>
 * 
 */

class Db2{

    /**
     * The registered connection
     * @var object of connection
     */

    protected $con;

    /**
     * The registered host database, IP Address of server database Db2 As400 (RDBMS)
     * @var string
     */
    protected $host;

    /**
     * The registered username of database Db2 As400 (RDBMS)
     * @var string
     */
    protected $username;

    /**
     * The registered password of database Db2 As400 (RDBMS)
     * @var string
     */
    protected $password;

    /**
     * The registered catalog of database Db2 As400 (RDBMS). It is only exist on driver COM driver
     * @var string
     */
    protected $catalog;

    /**
     * The registed column of Assigned table Db2 As400 (RDBMS)
     * @var array 
     */
    protected $columns;

    /**
     * The retrieved records of table Db2 As400 (RDBMS)
     * @var object of records
     */
    protected $records;

    /**
     * Initialization method
     * If you have multiple database environment you can set here on parameter $env
     * $env is index of array that start from 0.
     * You can define this multiple environment by separating first element and next element with | in .env
     * Ex. 
     * DB2_HOST=192.168.1.1|192.168.1.2
     * DB2_USERNAME=user1|user2
     * DB2_PASSWORD=passwd1|passwd2
     * First element of DB2_HOST before char "|" has username on first element of DB2_USERNAME and password on first element of DB2_PASSWORD
     * Ex. 192.168.1.1 has user name user1 and password passwd1
     * If you want to use first env, you can pass $env = 0 Ex. $con = new Db2(0);
     * But if you only have one env you dont need to separate using char "|" and you dont neet to pass $env, just let it blank
     */

    /**
     * Create a new Db2 instance
     * @param int $env (index of array env configuration, 0,1..)
     * @return void
     */
    public function __construct($env=NULL)
    {
        //Filter whether hosts are multiple or single
        if (env('DB2_HOST')!=''){
            $hosts = env('DB2_HOST');
            if (strpos($hosts, '|')==true){
                $h = explode('|', $hosts);
                if ($env!=NULL && is_numeric($env) && $env<=count($h) &&  
                    is_array($h) && count($h)>0) $this->host = $h[$env];
                else $this->host= $h[0];
            }else{
                $this->host = $hosts;
            }
        }
        //Filter whether users are multiple or single
        $username = env('DB2_USERNAME');
        $this->username = $username;
        if (env('DB2_USERNAME')!=''){
            $username = env('DB2_USERNAME');
            if (strpos($username, '|')==true){
                $u = explode('|', $username);
                if ($env!=NULL && is_numeric($env) && $env<=count($u) &&  
                    is_array($u) && count($u)>0) $this->username = $u[$env];
                else $this->username= $u[0];
            }else{
                $this->username = $username;
            }
        }
        //Filter whether passwords are multiple or single
        if (env('DB2_PASSWORD')!=''){
            $password = env('DB2_PASSWORD');
            if (strpos($password, '|')==true){
                $p = explode('|', $password);
                if ($env!=NULL && is_numeric($env) && $env<=count($p) &&  
                    is_array($p) && count($p)>0) $this->password = $p[$env];
                else $this->password= $p[0];
            }else{
                $this->password = $password;
            }
        }

        //Assign catalog from env
        if (env('DB2_CATALOG')!=''){
            $catalog = env('DB2_CATALOG');
            if (strpos($catalog, '|')==true){
                $c = explode('|', $catalog);
                if ($env!=NULL && is_numeric($env) && $env<=count($c) &&  
                    is_array($c) && count($c)>0) $this->catalog = $c[$env];
                else $this->catalog= $c[0];
            }else{
                $this->catalog = $catalog;
            }
        }


        //calling method whether is com or odbc
        if (env('DB2_DRIVER')!="" && method_exists($this, strtolower(env('DB2_DRIVER')))){
            $method = strtolower(env('DB2_DRIVER'));
            $this->$method();
        }
        
    }

    /**
     * Create instance of Db2 As400 connection using COM DOT NET driver
     * @return void
     */
    protected function com()
    {
        try{
            $this->con = new \COM('ADODB.Connection');
            $dsn= "Provider=IBMDASQL.DataSource.1;Data Source=".$this->host.";";
            $dsn.="Persist Security Info=True;User ID=".$this->username.";";
            $dsn.="Password=".$this->password.";Initial Catalog=".$this->catalog.";";
            $this->con->open($dsn);
        }catch(com_exception  $e){
            $errors=['error_code'=>$e->getCode(),'error_msg'=>strip_tags($e->getMessage())];
            echo json_encode($errors, true);
            die();
        }
    }

    /**
     * Create instance of Db2 As400 connection using ODBC driver
     * @return void
     */
    protected function odbc()
    {
        try{
            $dsn="Driver={iSeries Access ODBC Driver};system=".$this->host.";";
            $con = odbc_connect($dsn, $this->username, $this->password);
            $this->con=$con;
        }catch(\Exception $e){
            $errors=['error_code'=>$e->getCode(),
                'error_msg'=>strip_tags($e->getMessage())];
            echo json_encode($errors, true);    
            die();
        }
        
    } 

    /**
     * Choose method for query execution, COM driver method or odbc method
     * @param string $sql
     * @return void
     */
    public function query($sql)
    {
        if (env('DB2_DRIVER')!="" && method_exists($this, "execute".ucfirst(strtolower(env('DB2_DRIVER'))))){
            $method = "execute".ucfirst(strtolower(env('DB2_DRIVER')));
            $this->$method($sql);
        }
    }

    /**
     * Execute query with COM driver
     * @param string $sql
     * @return void
     */
    protected function executeCom($sql)
    {
        try {
            $row = $this->con->execute($sql);
            $this->setColumns($row);
            $this->records=$row;
        }catch(\Exception $e){
            $errors=['error_code'=>$e->getCode(),
                'error_msg'=>strip_tags($e->getMessage()),
                'query'=>$sql];
            echo json_encode($errors, true);    
            die();    
        }
    }

    /**
     * Execute query with ODBC driver
     * @param string $sql
     * @return void
     */
    protected function executeOdbc($sql)
    {
        try {
            $row = odbc_exec($this->con, $sql);
            $this->records=$row;
        }catch(\Exception $e){
            $errors=['error_code'=>$e->getCode(),
                'error_msg'=>strip_tags($e->getMessage()),
                'query'=>$sql];
            echo json_encode($errors, true);    
            die();    
        }
    }

    /**
     * Set / Register column of executed query
     * @param mixed $obj
     * @return void
     */
    protected function setColumns($obj)
    {
        $number_of_column=$obj->fields->count();
        for ($i=0; $i<$number_of_column; $i++){
            $field[$i] = $obj->fields($i)->name;
        }
        if (isset($field)) $this->columns = $field;
    }

    /**
     * get column of executed query
     * @return array
     */
    public function getColumn()
    {
        return $this->columns;
    }

    /**
     * get records of executed query
     * @return mixed
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * choose get list array of executed query using COM method or ODBC method 
     * @return array
     * @return false
     */
    public function get()
    {
        if (env('DB2_DRIVER')!="" && method_exists($this, "get".ucfirst(strtolower(env('DB2_DRIVER'))))){
            $method = "get".ucfirst(strtolower(env('DB2_DRIVER')));
            return $this->$method();
        }       
        return false;
    }

    /**
     * choose get single array of executed query using COM method or ODBC method 
     * @return array
     * @return false
     */
    public function first()
    {
        if (env('DB2_DRIVER')!="" && method_exists($this, "first".ucfirst(strtolower(env('DB2_DRIVER'))))){
            $method = "first".ucfirst(strtolower(env('DB2_DRIVER')));
            return $this->$method();
        }       
        return false;
    }

    /**
     * Get list array of executed query using COM method 
     * @return array
     * @return false
     */
    protected function getCom()
    {
        if (!is_object($this->records)) $this->query($this->query);
        if (is_object($this->records)){
            $rs = $this->records;
            $col = $this->columns;
            while(!$rs->EOF){
                if (is_array($col) && count($col)>0){
                    for($i=0;$i<count($col); $i++){
                        $result[$col[$i]] = trim($rs[$col[$i]]->value."");
                    }
                    if (isset($result)){
                        $results[] = $result;
                        unset($result);
                    }
                }
                $rs->movenext();
            }
            if (isset($results)) return $results;
        }
        return false;
    }

    /**
     * Get list array of executed query using ODBC method 
     * @return array
     * @return false
     */
    protected function getOdbc()
    {
        if (is_null($this->records)) $this->query($this->query);
        if (!is_null($this->records)){
            $rs = $this->records;
            while(odbc_fetch_row($rs)){
                for ($i = 1; $i <= odbc_num_fields($rs); $i++){
                    $field = odbc_field_name($rs, $i);
                    $result[$field] = trim(odbc_result($rs, $field))."";
                }
                if (isset($result)){
                    $results[] = $result;
                    unset($result);
                }
            }
            if (isset($results)) return $results;
        }
        return false;
    }

    /**
     * Get single array of executed query using COM method 
     * @return array
     * @return false
     */
    protected function firstCom()
    {
        if (!is_object($this->records)) $this->query($this->query);
        if (is_object($this->records)){
            $rs = $this->records;
            $col = $this->columns;
            if(!$rs->EOF){
                if (is_array($col) && count($col)>0){
                    for($i=0;$i<count($col); $i++){
                        $result[$col[$i]] = $rs[$col[$i]]->value."";
                    }
                }
            }
            if (isset($result)) return $result;
        }
        return false;
    }
    
    /**
     * Get single array of executed query using ODBC method 
     * @return array
     * @return false
     */
    protected function firstOdbc()
    {
        if (is_null($this->records)) $this->query($this->query);
        if (!is_null($this->records)){
            $rs = $this->records;
            if(odbc_fetch_row($rs)){
                for ($i = 1; $i <= odbc_num_fields($rs); $i++){
                    $field = odbc_field_name($rs, $i);
                    $result[$field] = trim(odbc_result($rs, $field))."";
                }
            }
            if (isset($result)) return $result;
        }
        return false;
    }

}