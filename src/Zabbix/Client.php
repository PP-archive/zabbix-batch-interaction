<?php

namespace Zabbix;

class Client {

    protected $_apiUrl = null;
    protected $_user = null;
    protected $_password = null;
    protected $_auth = null;
    
    public function __construct($apiUrl, $user, $password) {
        $this->_apiUrl = $apiUrl;
        $this->_user = $user;
        $this->_password = $password;
    }
    
    protected function _request($request) {
        $request = json_encode($request);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER,['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        
        $result = curl_exec($ch);
        
        if($result !== false) {
            return json_decode($result, true);
        }
        else {
            throw new Exception(curl_error($ch));
        }
    }
    
    public function auth() {
        $request = [
            'jsonrpc' => '2.0',
            'method' => 'user.login',
            'params' => [
                'user' => $this->_user,
                'password' => $this->_password
            ],
            'id' => 0
        ];
        
        $result = $this->_request($request);
        
        $this->_auth = $result['result'];
    }
        
    
    public function getHosts($hostIds = []) {
        $request = [
            'jsonrpc' => '2.0',
            'method' => 'host.get',
            'params' => [
                'output' => 'extend',

            ],
            'auth' => $this->_auth,
            'id' => 0
        ];
        
        if(is_array($hostIds) && !empty($hostIds)) {
            $request['params']['hostids'] = $hostIds;
        }
        
        $result = $this->_request($request);
        
        return $result['result'];
    }
    
    public function getApplicationsByHostId($hostIds) {
        $request = [
            'jsonrpc' => '2.0',
            'method' => 'application.get',
            'params' => [
                'hostids' => $hostIds,
                'output' => 'extend'
            ],
            'auth' => $this->_auth,
            'id' => 0
        ];
        
        $result = $this->_request($request);
        var_dump($result); exit;
        
        return $result;
    }
    
    public function getItemsByHost() {
        
    }
    
    public function createItems() {
        
    }

}
