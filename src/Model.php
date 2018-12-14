<?php 

namespace Masterpis\Db2As400;

use Masterpis\Db2As400\Db2;

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

class Model extends Db2{

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
    protected $limit;
    protected $server;
    protected $fields;
    protected $user;

    public function __construct()
    {

    }

    public function get(){

    }

}