<?php

namespace Spsr;

class Core {
    /**
     * @var string
     */
    public $queryPath;

    /**
     * @var string
     */
    public $user;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $company;

    /**
     * @var int
     */
    public $ICN;

    /**
     * @var string
     */
    public $SID;

    public function __construct(array $config) {
        if ($config['proto'] === 'https') {
            $this->queryPath = $config['env'] === 'production' ? 'https://api.spsr.ru' : 'https://api.spsr.ru/test';
        } else {
            $this->queryPath = $config['env'] === 'production' ? 'http://api.spsr.ru/waExec/WAExec' : 'http://api.spsr.ru:8020/waExec/WAExec';
        }

        if ($config['env'] === 'production') {
            $this->user = $config['user'];
            $this->password = $config['password'];
            $this->company = $config['company'];
            $this->ICN = $config['ICN'];
        } else {
            $this->user = 'test';
            $this->password = 'test';
            $this->company = 'Test company';
            $this->ICN = 7600010711;
        }

        $this->openSession();
    }

    public function __destruct() {
        $this->closeSession();
    }


    private function openSession() {
        $query = <<<XML
    <root   xmlns="http://spsr.ru/webapi/usermanagment/login/1.0">
        <p:Params Name="WALogin" Ver="1.0" xmlns:p="http://spsr.ru/webapi/WA/1.0" />
        <Login  Login="{$this->user}" Pass="{$this->password}" UserAgent="{$this->company}" />
    </root>
XML;
        $response = $this->makeXmlCall($query);
        $success = (string)$response->Result['RS'] == 0 ? true : false;

        if ($success === true)
            $this->SID = (string)$response->Login['SID'];
        else
            throw new \RuntimeException('Wrong Authorization');
    }

    private function closeSession() {
        $query = <<<XML
    <root   xmlns="http://spsr.ru/webapi/usermanagment/login/1.0">
        <p:Params Name="WALogin" Ver="1.0" xmlns:p="http://spsr.ru/webapi/WA/1.0" />
        <Logout Login="{$this->user}" SID="{$this->SID}" />
    </root>
XML;
        $this->makeXmlCall($query);
    }

    /**
     * @param $body
     * @return \SimpleXMLElement
     */
    private function makeXmlCall($body) {
        if (!$body)
            throw new \InvalidArgumentException('No body for api call');

        $header = array('Content-Type: application/xml');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->queryPath);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $respond = curl_exec($curl);
        curl_close($curl);

        $result = simplexml_load_string($respond);
        return $result;
    }

    /**
     * @param $path
     * @return \SimpleXMLElement
     */
    private function makeGetCall($path) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $respond = curl_exec($curl);
        curl_close($curl);

        return simplexml_load_string($respond);
    }

}