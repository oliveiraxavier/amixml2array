<?php

/**
 * Simple classe de conexao e comandos(actions) ami utilizando php
 * Para habilitar , em manager.conf
 * enabled = yes 
 * webenabled = yes
 * Observar a config permit 
 * Ex liberar acesso para o ip 192.168.1.10
 * permit = 192.168.1.10/255.255.255.0
 * usuario de exemplo em manager.conf
 * [teste_asterisk]
 * secret = asterisk
 * read = call,dialplan
 * write = system,all
 * 
 * https://wiki.asterisk.org/wiki/display/AST/Allow+Manager+Access+via+HTTP
 */
class AmiXml2Array
{
    
    public $curl,$pathTmpFile,$loginUrl,$globalUrl,$options;
    
    /**
    * @access protected
    * Configuracoes das opcoes do curl
    */
    public function __construct()
    {
        
        $this->curl         = $this->curlStart();
        $tempFile           = tmpfile();
        $this->pathTmpFile  = stream_get_meta_data($tempFile)['uri'];
        $this->loginUrl     = '192.168.1.10:8088/mxml?action=login&username=teste_asterisk&secret=asterisk';
        $this->globalUrl    = '192.168.1.10:8088/mxml?';
        $this->options      =  [            
                                CURLOPT_COOKIESESSION   => TRUE,
                                CURLOPT_RETURNTRANSFER  => TRUE,
                                CURLOPT_TIMEOUT         => 30,
                                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST   => "GET",
                                CURLOPT_COOKIEFILE      => $this->pathTmpFile
                            ];

    }
    
    public  function curlStart(){
        return curl_init();
    }

    /**    
     * Faz login no manager utilizando  curl 
    */
    public function loginAmiHttp()
    {
        
        $this->options[CURLOPT_URL]   = $this->loginUrl;
        
        $curl           = $this->curl;
        curl_setopt_array($curl, $this->options);
        $pathTmpFile    = $this->pathTmpFile;
        $data           = curl_exec($curl);

        $xml            = simplexml_load_string($data);
        $nodes          = [];
        foreach ($xml as $node) {
            $nodes[] = current($node->generic->attributes());
        }
        return compact('curl','pathTmpFile');        
    }    

    /**
     * Faz logoff do manager 
    */ 
    public function logoffAmiHttp()
    {
        $curl                               = $this->curl;
        $this->options[CURLOPT_URL]         = $this->globalUrl . 'action=logoff';
        $this->options[CURLOPT_COOKIEFILE]  = '';
        curl_setopt_array($this->curl, $this->options);
        $data = curl_exec($this->curl);
        return $data;
    }
     
    /**
     * Retorna um array com informacoes referentes a action consultada
     * @param string $action - Ver em  https://wiki.asterisk.org/wiki/display/AST/Asterisk+14+AMI+Actions
    */
    public function getSimpleAction($action,$object = false){

        $curlData       = $this->loginAmiHttp();
        if(!$curlData){
            return false;
        }

        $curl           = $curlData['curl'];
        $tmpFile        = $curlData['pathTmpFile'];
        $url            = $this->globalUrl . "action={$action}";        
        $this->options[CURLOPT_URL]   = $url;
        curl_setopt_array($curl, $this->options);
        $data   = curl_exec($curl);

        $this->logoffAmiHttp();
        $xml    = simplexml_load_string($data);
        $nodes = [];
        foreach ($xml as $node) {
            $nodes[] = current($node->generic->attributes());
        }
        
        if($object){
            return  json_encode($nodes);
        }
        return $nodes;
    }

    /**
     * Retorna um array com informacoes sobre a fila
     * @param string $queuName - O nome da fila
    */
    public function getQueueStatusByName($queueName,$object = false){

        $curlData       = $this->loginAmiHttp();
        if(!$curlData){
            return false;
        }

        $curl           = $curlData['curl'];
        $tmpFile        = $curlData['pathTmpFile'];
        $url            = $this->globalUrl . "action=QueueStatus&queue={$queueName}";        
        
        $this->options[CURLOPT_URL]   = $url;
        curl_setopt_array($curl, $this->options);
        $data   = curl_exec($curl);

        $this->logoffAmiHttp();
        $xml    = simplexml_load_string($data);
        $nodes = [];
        foreach ($xml as $node) {
            $nodes[] = current($node->generic->attributes());
        }

        if($object){
            return  json_encode($nodes);
        }
        return $nodes;
    }

    /**
     * Retorna um array com informacoes sobre a fila
     * @param string $sipPeeer - O peer a ser consultado(ex:1004)
    */
    public function getSipStatusBySipPeer($sipPeeer,$object = false){

        $curlData       = $this->loginAmiHttp();
        if(!$curlData){
            return false;
        }

        $curl           = $curlData['curl'];
        $tmpFile        = $curlData['pathTmpFile'];
        $url            = $this->globalUrl . "action=SIPshowpeer&peer={$sipPeeer}";        
        
        $this->options[CURLOPT_URL]   = $url;
        curl_setopt_array($curl, $this->options);
        $data   = curl_exec($curl);

        $this->logoffAmiHttp();
        $xml    = simplexml_load_string($data);
        $nodes = [];
        foreach ($xml as $node) {
            $nodes[] = current($node->generic->attributes());
        }
        
        if($object){
            return  json_encode($nodes);
        }
        return $nodes;
    }
}

/**
* More actions
* https://wiki.asterisk.org/wiki/display/AST/Asterisk+14+AMI+Actions
* Examples
*/

$ami = new AmiXml2Array();

//get status from all sip peers
//json
print_r($ami->getSimpleAction('SIPpeers',true));
//array
print_r($ami->getSimpleAction('SIPpeers'));

//queue status from all queues
//json
 print_r($ami->getSimpleAction('QueueStatus',true));
//array
print_r($ami->getSimpleAction('QueueStatus'));

// // //queue status from callcenter_1_fila_asdfasd_10
//json
print_r($ami->getQueueStatusByName('callcenter_1_fila_asdfasd_10',true));
//array
print_r($ami->getQueueStatusByName('callcenter_1_fila_asdfasd_10'));

//get status from peer 11004
//json
print_r($ami->getSipStatusBySipPeer('11004',true));
//array
print_r($ami->getSipStatusBySipPeer('11004'));
