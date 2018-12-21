# Db2As400
Packages for managing connection to Db2 As400 using driver COM dot net and ODBC. It is built on lumen laravel.
It is a collection of attributes and methods to simplify connection to DB2 As400. The main purpose of this library is standarization of code in my small organization. It will provide basic operation of database connection.

# How to use it on your project.
Running this script on your lumen / laravel project directory
```composer require masterpis/db2as400```

Database configuration on .env on laravel or lumen project

If you use ODBC Driver, you can use configuration below :
```
DB2_DRIVER=ODBC
DB2_CATALOG=
DB2_HOST=192.168.x.xx
DB2_USERNAME=user_odbc
DB2_PASSWORD=pass_odbc
```
if you use COM Driver, you can use configuration below:
```
DB2_DRIVER=COM
DB2_CATALOG=S651658X
DB2_HOST=192.168.x.xx
DB2_USERNAME=user_com
DB2_PASSWORD=pass_com
```

if you have multiple env ODBC
```
DB2_DRIVER=ODBC
DB2_CATALOG=
DB2_HOST=192.168.x.xx|192.168.xx.xxx
DB2_USERNAME=user1|user2
DB2_PASSWORD=pass1|pass2
```
if you have multiple env COM
```
DB2_DRIVER=COM
DB2_CATALOG=S651658X|S651658Y
DB2_HOST=192.168.x.xx|192.168.xx.xxx
DB2_USERNAME=user1|user2
DB2_PASSWORD=pass1|pass2
```

Open your Model, remove default eloquent and load this packages by this code
```use Masterpis\Db2as400\Model;```

Change extends from default eloquent to this packages
```
<?php 
namespace App;
use Masterpis\Db2as400\Model;

class Employee extends Model{
    protected $table ="oplenhp.employee";
    protected $fields= ["id", "name", "phone"];
    
    public function __construct(){
        parent::__construct();
    }                        
}
?>
```

Use your model on controller
```
<?php 

namespace App\Http\Controllers;

use App\Employee;
use Laravel\Lumen\Routing\Controller as BaseController;

class TestController extends BaseController{

    public function get()
    {
        $mmusid = new Employee;
        $mmusid->where(['id'=>'1']);
        $a=$mmusid->first();
        dd($a)
    }

}
```
Methods reference:
```
Clause configuration. Call method before method where();
/**
 * The query clause like.
 * @param array
 * @return void 
 */
$mmusid->like($like) 

/**
* The query clause order
* @param array
* @return void
*/
$mmusid->orderBy($order)

/**
 * The query clause limit
 * @param int
 * @return void
 */
$mmusid->limit($limit)

/**
 * The server RDBMS, for multiple env only
 * @param int
 * @return void
 */
$mmusid->setServer($server)

/**
* The user in charge. Registered user running commands
* @param @string
* @return void
*/
$mmusid->setUser($user)

```
Main method
```
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
$mmusid->where($where=NULL, $console=NULL)

/**
 * Get list array of executed query 
 * @return array
 * @return false
 */
$mmusid->get()

/**
 * Get single array of executed query 
 * @return array
 * @return false
 */
$mmusid->first()

/**
 * @param array filter
 * @return int 
 */
// generate select count(*) from xxx
$mmusid->count($filter=NULL)

    ```
    
    
> Compability
You have to install driver COM or ODBC to make it run.
If you have iSeries packages like iSeries Navigator, you can install it, the driver automatically installed
