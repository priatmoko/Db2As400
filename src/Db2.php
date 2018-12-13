<?php 

namespace Masterpis\Db2as400;

class Db2{

    /**
     * Db2 params
     * $con         : parameter for recording object initialization of connection
     * $host        : IP Address of server Db2 As400
     * $username    : user of database Db2 As400
     * $password    : user password of database Db2 As400
     * $catalog     : catalog name of Db2 As400 (you can find this catalog on i series navigator ex. S651658f)
     * $columns     : parameter for recording object array list of column of query select
     *  $records    : parameter for recording row result of query 
     */

    private $con;
    private $host;
    private $username;
    private $password;
    private $catalog;
    private $columns;
    private $records;

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
    public function __construct($env=NULL)
    {
        //Filter whether hosts are multiple or single
        if (env('DB2_HOST')!=''){
            $hosts = env('DB2_HOST');
            if (strpos($hosts, '|')==true){
                $h = explode('|', $hosts);
                if ($env!=NULL && is_numeric($env) && $env<=count($h) &&  
                    is_array($h) && count($h)>0) $this->host = $h[int($env)-1];
                else $this->host= $hosts;
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
                    is_array($u) && count($u)>0) $this->username = $u[int($env)-1];
                else $this->username= $username;
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
                    is_array($p) && count($p)>0) $this->password = $p[int($env)-1];
                else $this->password= $password;
            }else{
                $this->password = $password;
            }
        }

        //Assign catalog from env
        if (env('DB2_CATALOG')!=''){
            $this->catalog=env('DB2_CATALOG');
        }    
        //calling method whether is com or odbc
        if (env('DB2_DRIVER')!="" && method_exists($this, strtolower(env('DB2_DRIVER')))){
            $method = strtolower(env('DB2_DRIVER'));
            $this->$method();
        }
        
    }

    /**
     * Db2 As400 connection using con dot net driver
     */
    private function com()
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
     * Db2 As400 connection using odbc driver
     */
    private function odbc()
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

    public function execute($sql)
    {
        if (env('DB2_DRIVER')!="" && method_exists($this, strtolower("execute_".env('DB2_DRIVER')))){
            $method = strtolower("execute_".env('DB2_DRIVER'));
            $this->$method($sql);
        }
    }

    private function execute_com($sql){

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

    private function execute_odbc($sql){
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

    private function setColumns($obj){
        $number_of_column=$obj->fields->count();
        for ($i=0; $i<$number_of_column; $i++){
            $field[$i] = $obj->fields($i)->name;
        }
        if (isset($field)) $this->columns = $field;
    }
    public function getColumn(){
        return $this->columns;
    }
    public function getRecords(){
        return $this->records;
    }
    public function getList(){
        if (env('DB2_DRIVER')!="" && method_exists($this, strtolower("getList_".env('DB2_DRIVER')))){
            $method = strtolower("getList_".env('DB2_DRIVER'));
            return $this->$method();
        }       
        return false;
    }
    public function getRow(){
        if (env('DB2_DRIVER')!="" && method_exists($this, strtolower("getRow_".env('DB2_DRIVER')))){
            $method = strtolower("getRow_".env('DB2_DRIVER'));
            return $this->$method();
        }       
        return false;
    }
    private function getlist_com(){
        
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

    private function getlist_odbc(){
        
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

    private function getrow_com(){
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
    
    private function getrow_odbc(){
        
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