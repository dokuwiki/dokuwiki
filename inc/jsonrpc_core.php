<?php
/**
 * Created by PhpStorm.
 * User: z97
 * Date: 15-5-22
 * Time: 上午11:28
 */

require_once("jsonrpc_helper.php");

class jsonrpc_server
{
    protected $raw_data;
    protected $protocol;
    protected $remote_api;

    function __construct($remote_api=null,$protocol_version="2.0"){
        if($protocol_version=="2.0"){
            $this->protocol=new xx_jsonrpc\rpc_protocol_2_0();
        }else{
            $this->protocol=new xx_jsonrpc\rpc_protocol_1_1();
        }
        if($remote_api==null){
            $this->remote_api=new \xx_jsonrpc\doku_remote_api();
        }else{
            $this->remote_api=$remote_api;
        }
    }
// JSON-RPC executor , can execute a request text from a string(from server,file or another function)
    function rpc($json_text){
        $rt='';
        try {
            $request_array=$this->protocol->decode($json_text); // decode request text
//            \xx_jsonrpc\error_write(json_encode($request_array)."\n");
            if((count($request_array)>1)&&(isset($request_array["method"])==false) ){ // is it batch request ? cause PHP does not distinguish between indexed and associative arrays.
                $rt=$this->_batch_call($request_array); //batch
            }else{ // not batch
                $rt = $this->_call($request_array); //so single call
            }

        }catch (\xx_jsonrpc\E_xx_jsonrpc $e){ // exception process
            $rt= $this->protocol->Exception_ToError($e);
        }catch (Exception $e){
            $code=xx_jsonrpc\E_Internal_error::$error_code;
            $msg=xx_jsonrpc\E_Internal_error::$error_msg;
            $rt =$this->protocol->std_error_maker($code,$msg,null,"unexpected exception | {$e->getMessage()} | {$e->getCode()} | {$e->getFile()} | {$e->getLine()}|");
        }

        return $rt;
    }

    public function _batch_call($batch_request){
        $rt_array=array();
        foreach($batch_request as $req){
            $rt= $this->_call($req);
            if($rt){ $rt_array[]=$rt; }
        }
        return $this->protocol->json_encode_exp($rt_array); // custom json_encode ,just tranlate the json_last_error to Exception
    }
    public function _call($json_array){
//        xx_jsonrpc\error_write(json_encode($json_array));
        $rt=$this->remote_api->call($json_array["method"], $json_array["params"]);
        if(isset($json_array['id'])){ // a normal call with id, so return the result
            return $this->protocol->encode_result($rt,$json_array['id']);
        }else{
            return ''; // id == null is notify ,client do not need the result
        }
    }

// A simple self host HTTP server
    function server(){
        header('Content-type: application/json');
        $this->raw_data = file_get_contents("php://input");
        echo $this->rpc($this->raw_data);
    }
}