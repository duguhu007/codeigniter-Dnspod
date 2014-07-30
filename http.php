<?php
class HTTP {

    public $ch;
    public $url;
    public $param;
    public $option;
    private $method;

    public function __construct($url, $method = 'post', $redirect = true) {

        $this->url = $url;

        $this->ch = curl_init ( $this->url );

        if ($method == 'post') {
            curl_setopt ( $this->ch, CURLOPT_POST, true );
        } else {
            curl_setopt ( $this->ch, CURLOPT_HTTPGET, true );
        }

        $this->method = $method;

        curl_setopt ( $this->ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ( $this->ch, CURLOPT_USERAGENT,UA);

        if ($redirect) {
            curl_setopt ( $this->ch, CURLOPT_FOLLOWLOCATION, true );
        }
    }

    public function setOption($option) {

        if (! empty ( $option )) {
            $this->option = $option;
        } else {
            return false;
        }

        foreach ( $this->option as $k => $v ) {
            curl_setopt ( $this->ch, $k, $v );
        }

        return true;
    }

    public function setParam($param) {
        if (! empty ( $param )) {
            $this->param = $param;
        } else {
            return false;
        }

        foreach ( $param as $k => $v ) {
            $params [] = "$k=$v";
        }

        $this->param = implode ( '&', $params );

        if ($this->method == 'post') {
            curl_setopt ( $this->ch, CURLOPT_POSTFIELDS, $this->param );
        } else {
            curl_setopt ( $this->ch, CURLOPT_URL, $this->url . '?' . $this->param );
        }
        return true;
    }

    public function exec() {
        return curl_exec ( $this->ch );
    }

    public function __destruct() {
        curl_close ( $this->ch );
    }
}

class DnspodApi {
    private $httpHandler;
    private $email;
    private $pass;
    private $format = 'json';
    private $error = array();
    public function __construct($email, $pass) {
        if (! $email || ! $pass) {
            exit ( 'no email or pass' );
        }
        $this->email = $email;
        $this->pass = $pass;
        $this->httpHandler = new HTTP ( 'http://www.dnspod.com/API/' );
    }
    /**
     * test if an error occured;
     * */
    function isError($data){
        return isError($data);
    }
    function throwError($data,$msg){
        if(is_array($data)){
        $data["status"]["message"]=
            $data["status"]["message"]."\n".$msg;
        }
        else{
            $data=array(
                "status"=>array(
                    "code"=>$data,
                    "message"=>$msg
                )
            );
        }
        return $data;
    }
    /**
     * get the domain_id for specific domain
     * */
    public function GetDomainId($domain){
        global $_G;
        if($_G["domain_id.$domain"])
            return $_G["domain_id.$domain"];

        $ret=$this->GetDomainList();
        foreach($ret["domains"]["domain"] as $idomain){
            if($idomain["name"]==$domain){
                $_G["domain_id.$domain"]=$idomain["id"];
                return $idomain["id"];
            }
        }
        return array("status"=>array("code"=>DOMAIN_ERROR));
    }
    public function setRecord($domain,$record_name,$line="default"){
        global $_G;
    }
    /**
     * get the record for single record 
     * */

    public function getRecordByName($domain,$record_name,$line="default"){
        global $_G;
        if($_G["record.$domain.$record_name.$line"])
            return $_G["record.$domain.$record_name.$line"];

        $domainId=$this->getDomainId($domain);
        if($this->isError($domainId)){
            $msg= "can't get domainId of $domain";
            error_log($msg);
            $this->throwError($domainId,$msg);
            return $domainId;
        }
        $ret=$this->getRecordlist($domainId);
        if(isError($ret)){
            return $ret;
        }
        foreach($ret["records"]["record"] as $ir){
            if($ir["name"]==$record_name && $ir["line"]==$line){
                $_G["record.$domain.$record_name.$line"]=$ir;
                return $ir;
            }
        }
        return array("status"=>array('code'=>RECORD_ERROR));
    }
    /**
     * get the record_id for single record
     * */
    public function getRecordIdByName($domain,$record_name,$line="default"){
        $ret=$this->getRecordByName($domain,$record_name,$line);
        return $ret["id"];

    }
    public function createDomain($domain){
        $this->setAction('Domain.Create');
        $params['domain'] = $domain;
        return $this->exec($params);
    }

    public function removeDomain($domainId){
        $this->setAction('Domain.Remove');
        $params['domain_id'] = $domainId;
        return $this->exec($params);
    }

    public function setDomainStatus($domainId,$enable = true){
        $this->setAction('Domain.Status');
        $params['domain_id'] = $domainId;

        $status = $enable ? 'enable' : 'disable';
        $params['status'] = $status;

        return $this->exec($params);
    }

    public function getDomainList() {
        $this->setAction('Domain.List');
        return $this->exec();
    }
    public function RecordCreate($params,$domain){
        $domainId=$this->getDomainId($domain);
        $params["domain_id"]=$domainId;
        $this->setAction('Record.Create');
        return $this->exec($params);
    }
    public function getRecordList($domainId){
        $this->setAction('Record.List');
        $params['domain_id'] = $domainId;
        return $this->exec($params);
    }

    public function modifyRecord($params,$domain,$sub_domain){

        $domainId=$this->getDomainId($domain);
        if($this->isError($domainId)){
            return $domainId;
        }
        $recordId=$this->getRecordIdByName($domain,$sub_domain,$params["record_line"]);
        if($this->isError($recordId)){
            return $recordId;
        }
        $this->setAction('Record.Modify');
        $params["domain_id"]=$domainId;
        $params["record_id"]=$recordId;
        return $this->exec($params);
    }

    public function removeRecord($domainId,$recordId){
        $this->setAction('Record.Remove');
        $params['domain_id'] = $domainId;
        $params['record_id'] = $recordId;

        return $this->exec($params);
    }

    public function setRecordStatus($domainId,$recordId,$enable = true){
        $this->setAction('Record.Status');
        $params['domain_id'] = $domainId;
        $params['record_id'] = $recordId;

        $status = $enable ? 'enable' : 'disable';
        $params['status'] = $status;

        return $this->exec($params);
    }

    private function exec($params = ''){
        $params['login_email'] = $this->email;
        $params['login_password'] = $this->pass;
        $params['format'] = $this->format;

        $this->httpHandler->setParam($params);

        $result = $this->httpHandler->exec ();
        $result = json_decode ( $result ,true);
        return $result;
    }

    private function setAction($action){
        $this->httpHandler->setOption ( array (CURLOPT_URL => $this->httpHandler->url . $action ) );
    }

    private function error($result){
        $this->error['code'] = $result["status"]["code"];
        $this->error['message'] = $result["status"]["message"];
    }

    public function getError(){
        return $this->error;
    }
}