<?php
/**
 * Created by PhpStorm.
 * User: z97
 * Date: 15-5-22
 * Time: 下午8:22
 */
namespace xx_jsonrpc;
require_once("remote.php");


abstract class E_xx_jsonrpc extends \Exception{
    /*
$std_msg=  array(
-32700 => 'Parse error',
-32600 => 'Invalid Request',
-32601 => 'Method not found',
-32602 => 'Invalid params',
-32603 => 'Internal error'
);
*/
    protected $msg_str='';
    static $error_code=-2;
    static $error_msg="E_xx_jsonrpc";

    function __construct($msg_str=''){
        $this->msg_str=$msg_str;
    }
    public function ExgetMessage(){
        return "custom msg : ".$this->msg_str;
    }
    public function rpc_errorCode(){ return static::$error_code;}
    public function rpc_errorMsg(){return static::$error_msg;}
}

class E_Parse_error extends E_xx_jsonrpc{
    static $error_code=-32700;
    static $error_msg="Parse error";
}

class E_Invalid_Request extends E_xx_jsonrpc{
    static $error_code=-32600;
    static $error_msg="Invalid Request";
}

class E_Method_not_found extends E_xx_jsonrpc{
    static $error_code=-32601;
    static $error_msg="Method not found";
}

class E_Invalid_params extends E_xx_jsonrpc{
    static $error_code=-32602;
    static $error_msg="Invalid params";
}

class E_Internal_error extends E_xx_jsonrpc{
    static $error_code=-32603;
    static $error_msg="Internal error";
}

// ---------------- must stop exception------------

abstract class rpc_protocol{

    // must return json_text !!! type is  string
    abstract function std_error_maker($code,$msg,$id=null, $data=null);
    abstract function encode_result($result,$id);

    // the return is json_text !!! type is  string
    function Exception_ToError(E_xx_jsonrpc $e){
        return $this->std_error_maker($e->rpc_errorCode(),$e->rpc_errorMsg(),null,$e->ExgetMessage());
    }

    function json_encode_exp($obj,$exp_msg=""){
        $rt=json_encode($obj);
        if($rt==false){
            throw new E_Internal_error($exp_msg." | json_last_error=".json_last_error());
        }
        return $rt;
    }

// { "version": "1.1","method": "confirmFruitPurchase","id": "194521489","params": [["apple", "orange", "mangoes"],1.123] }
    function decode($json_text){

        if($json_text==null){
            throw new E_Invalid_Request("You must send request by POST | ".$json_text);
        }

        $is_batch = false;
        if( preg_match("/^[ ]*{/",$json_text)==true ){
            $is_batch=false;
        }else{
            if(preg_match("/^[ ]*[/",$json_text)==true ){
                $is_batch=true;
            }else{
                throw new E_Invalid_Request("The request must be json | ".$json_text);
            }
        }

        $json_array=json_decode($json_text,true);
        if($is_batch){
            foreach($json_array as $single){
                $this->check($single);
            }
        }else{
            $this->check($json_array);
        }
        return $json_array;
    }

    protected function check($single_request){
        if($single_request==null){
            throw new E_Parse_error("Your json is decoded to null | ".$this->json_encode_exp($single_request,"in throw"));
        }

        $rt=isset($single_request['method'])&&
            is_string($single_request['method'])&&
            preg_match("/[\w\.]+/",$single_request['method']);
        if($rt!=true){
            throw new E_Invalid_Request("rpc_protocol.decode :invalid method | ".$this->json_encode_exp($single_request,"in throw"));
        }

        if(isset($single_request['params'])){
            if( is_array($single_request['params'])==false ){
                throw new E_Invalid_Request("rpc_protocol.decode :Bad params | ".$this->json_encode_exp($single_request,"in throw"));
            }
        }else{
            $single_request['params']=array();
        }
        return true;
    }
}

class rpc_protocol_1_1 extends rpc_protocol{
// { "version": "1.1","method": "confirmFruitPurchase","id": "194521489","params": [["apple", "orange", "mangoes"],1.123] }
// return json_text !!! type is  string
    function std_error_maker($code,$msg,$id=null, $data=null)
    {
        $error_object = array(
            'id' => $id,
            'result' => null,
            'error' => array(
                'code' => $code,
                'message' => $msg,
                'data' => $data
            ));
        return json_encode($error_object);
    }

    function decode($json_text){
        $json_array=rpc_protocol::decode($json_text);

        $rt= isset($json_array['version']) && ($json_array['version']=="1.1");
        if($rt!=true){
            throw new E_Invalid_Request('v1.1 server Should set "version":"1.1", | '.$this->json_encode_exp($json_array,"in throw"));
        }
        return $json_array;
    }

    function encode_result($result,$id){
        $rt = $this->json_encode_exp($result,"encode result to json ERROR");
        $result_object=array(
            "version"=>"1.1",
            "result"=>$rt,
            "error"=>null,
            "id"=>$id
        );
        $rt_text = $this->json_encode_exp($result_object,"encode result to json ERROR , the api result encode is success,but encode to result_object failed");
        return $rt_text;
    }
}

class rpc_protocol_2_0 extends rpc_protocol{
    // {"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 3}

    // return json_text !!! type is  string
    function std_error_maker($code,$msg,$id=null, $data=null) // return
    {
        $error_object = array(
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => array(
                'code' => $code,
                'message' => $msg,
                'data' => $data
            ));
        return json_encode($error_object);
    }

    function decode($json_text){
        $json_array=rpc_protocol::decode($json_text);

        $rt = isset($json_array['jsonrpc']) && ($json_array['jsonrpc']=="2.0");
        if($rt!=true){
            throw new E_Invalid_Request('Hello ,This v2.0 server,typecal request is {"jsonrpc": "2.0", "method": "subtract", "params": {}, "id": 3} | '
                .$this->json_encode_exp($json_array,"in throw"));
        }

        return $json_array;
    }

    function encode_result($result,$id){
        $rt = $this->json_encode_exp($result,"encode result to json ERROR");
        $result_object=array(
            "jsonrpc"=>"2.0",
            "result"=>$rt,
            "id"=>$id
        );
        $rt_text = $this->json_encode_exp($result_object,"encode result to json ERROR , the api result encode is success,but encode to result_object failed");
        return $rt_text;
    }

}

class remote_api{
    private $func_list;
    //{ 'func_name'=> func , 'func_name2' => func2 }
    function __construct($func_list){
        $this->allow_func($func_list);
    }
    function allow_func($func_list){
        if($func_list){
            foreach($func_list as $name=>$real_func){
                if(is_callable($real_func)==false){
                    throw new \Exception("remote_api your $real_func is not callable!");
                }
            }
            $this->func_list=$func_list;
        }else{
            throw new \Exception("remote_api you must set $func_list");
        }
    }
    function call($name_func,$params_array){

        if(isset($this->func_list[$name_func])){
            return call_user_func_array($this->func_list[$name_func],$params_array);
        }else{
            throw new E_Method_not_found("remote_api [$name_func] is not found in function list");
        }
    }
}

class doku_remote_api{
    protected $doku_remote;
    function __construct(){
        $this->doku_remote=new \RemoteAPI();
        $this->doku_remote->setDateTransformation(array($this, 'toDate'));
        $this->doku_remote->setFileTransformation(array($this, 'toFile'));
    }
    function call($name_func,$params_array){
        try{
            return $this->doku_remote->call($name_func, $params_array);
        }catch (\RemoteAccessDeniedException $e){
            throw new E_Internal_error("dokuwiki may not set remote access |".$e->getMessage());
        }catch (\RemoteException $e){
            throw new E_Method_not_found($e->getMessage());
        }
    }
}
/*
function error_write($msg){
    $ff=fopen("mye.txt","a");
    fwrite($ff,$msg);
    fclose($ff);
}
*/