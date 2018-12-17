<?php 

namespace Masterpis\Db2as400;

use Masterpis\Db2as400\Db2;

/**
 * Db2As400 is a collection of attributes and methods to simplify connection to DB2 As400.
 * The main purpose of this library is standarization of code in my small organization. 
 * It will provide basic operation of database connection
 * 
 * This library is built on lumen, laravel
 * 
 * Class Model provide simple query methods  
 * Using this library will make you use same name method to operate connection and query.
 * 
 * 
 * @link masterpis.com
 * @since v1.0.0
 * @author Priatmoko <priatmoko.informatics@gmail.com>
 * 
 */

class Model extends Db2
{

    /**
     * Filtering query result using LIKE clause
     * @var array
     */
    protected $like;

    /**
     * Sorting query result
     * @var array
     */
    protected $order;

    /**
     * Limit query result
     * @var array
     */
    protected $limit;

    /**
     * Server RDBMS
     * @var string
     */
    protected $server;

    /**
     * Fields of query result
     * @var array
     */
    protected $fields;

    /**
     * User login
     * @var string
     */
    protected $user;
    
    /**
     * generated Query
     * @var string
     */
    public $query;

    /**
     * Instance object Model for generating simple query
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->where(NULL, 'ASPARAM');
    }

    /**
     * The query clause like.
     * @param array
     * @return void 
     */
    public function like($like){

        if (is_array($this->like) && count($this->like)>0){
            $used_like=array_merge($like, $this->like);
        }else{
            $used_like = $like;
        }
        $this->like=$used_like;

    }

    /**
     * The query clause order
     * @param array
     * @return void
     */
    public function orderBy($order){
        $this->order = $order;
    }

    /**
     * The query clause limit
     * @param int
     * @return void
     */
    public function limit($limit){
        $this->limit=$limit;
    }

    /**
     * The server RDBMS, for multiple env only
     * @param int
     * @return void
     */
    public function setServer($server){
        $this->server=$server;
    }

    /**
     * The customized fields in query. 
     * If you dont set it, query will generate all fealds (*) as result
     * @param @array
     * @return void
     */
    public function setFields($fields){
        $this->fields = $fields;
    }

    /**
     * The user in charge. Registered user running commands
     * @param @array
     * @return void
     */
    public function setUsid($usid){
        $this->usid=$usid;
    }

    /**
     * The query clause where
     * @param array $where
     * Simple clause where ex. ["field"=>"keyword"]
     * Complex clause on where ex 
     * [""=>"(field1='".$keyword1."' or field2='".$keyword2."') and field2 like '%".keyword3."%'"]
     * @param string $console
     * NULL, CONSOLE will display query generated, ASPARAM will assign query to var $query
     * @return void 
     */
    public function where($where=NULL, $console=NULL)
    {
        if ($where!=NULL){
            //Looping array clause where
            foreach($where as $k=>$v) {
                //Validate index array clause. If index array is empty, We use all value as clause. 
                //It is complex clause. if index array is not empty, It is simple clause. 
                if ($k=="") $pf[] = $v; else $pf[] = $k."='".str_replace("'","''", $v)."'";
            }
        }

        //Looping like clause
        if (is_array($this->like) && count($this->like)>0){
            foreach($this->like as $lk=>$lv) {
                //Validate index array clause. If index array is empty, We use all value as clause. 
                //It is complex clause. 
                //if index array is not empty, It is simple clause.
                if ($lk=="") $pf[] = $v; else $pf[] = $lk." like '".str_replace("'","''", $lv)."'";
            }
        }
        
        //Combine all filter 
        $filter = "";
        if (isset($pf) && count($pf)>0)  $filter = " where ".implode(" and ", $pf); 
        
        //Looping array order clause
        $order = "";
        if (is_array($this->order) && count($this->order)>0){
            
            $orders = $this->order;
            foreach($orders as $ok=>$ov) $porder[] = $ok." ".$ov." ";

            if (isset($porder) && is_array($porder) && count($porder)>0)
                $order = "order by ".implode(",", $porder); else $order ="";
        
        }

        //validate param limit
        $limit = "";
        if (isset($this->limit) && $this->limit>0) 
            $limit = "fetch first ".$this->limit." rows only";
        
        //validate field selected    
        $fields = "*";
        if (is_array($this->fields) && count($this->fields)>0) 
            $fields = implode(",", $this->fields);
        
        //Combine all things here
        $sql="select ".$fields." from ".$this->table." ".$filter." ".$order." ".$limit;

        if (strtoupper($console)=='CONSOLE'){
            dd($sql);
        }else if (strtoupper($console)=='ASPARAM'){
            $this->query=$sql;
        }else{
            $this->query($sql);
        }
    }

}