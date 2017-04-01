<?php
namespace app\api\controller\supplier;
@session_start();
use app\api\YZ;

class Af_supplier extends YZ
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $result = $this->callPlugin('supplier/af_supplier');
        $this->returnSuccess($result);
    }
    public function hasApplied(){
        $result = $this->callPlugin('supplier/af_supplier');
        $this->returnSuccess(array('is_supplier'=>(string)$result['json']['is_supplier']));

    }
}