<?php

class Sms extends CApplicationComponent
{
    const HOST  = 'http://sms.ru/';
    const SEND = 'sms/send?';
    const STATUS = 'sms/status?';
    const COST = 'sms/cost?';
    const BALANCE = 'my/balance?';
    const LIMIT = 'my/limit?';
    const SENDERS = 'my/senders?';
    const GET_TOKEN = 'auth/get_token';
    const CHECK = 'auth/check?';

    public $login;
    public $password;
    public $token;
    public $id;
    public $sha512;
    
    /**
     * Init
     * 
     * @throws CException
     */
    public function init()
    {
        if (!function_exists ('curl_init'))
        {
            throw new CException ('Для работы расширения требуется cURL');
        }

        parent::init();
    }
    
    /**
     * Send message
     * 
     * @param string $to 
     * @param string $text 
     * @param string $from 
     * @param integer $time 
     * @param boolean $test 
     * @param type $partner_id 
     * @return array
     */
    public function send($to, $text, $from = null, $time = null, $test = false, $partner_id = null)
    {
        $url = self::HOST . self::SEND;
        $this->id = null;

        $params = $this->get_default_params();
        $params['to'] = $to;
        $params['text'] = $text;

        if ($from)
            $params['from'] = $from;

        if ($time && $time < (time() + 7 * 60 * 60 * 24))
            $params['time'] = $time;

        if ($test)
            $params['test'] = 1;

        if ($partner_id)
            $params['partner_id'] = $partner_id;

        $result = $this->request($url, $params);
        $result = explode("\n", $result);

        return array(
            'code' => $result[0],
            'id' => $result[1],
            'balance' => str_replace( 'balance=', '', $result[2] )
        );
    }
    
    /**
     * Check message status
     * 
     * @param type $id
     * @return type
     */
    public function status($id)
    {
        $url = self::HOST.self::STATUS;

        $params = $this->get_default_params();
        $params['id'] = $id;
        $result = $this->request($url, $params);

        return $result;
    }
    
    /**
     * Check user balance
     * @return array
     */
    public function balance()
    {
        $url = self::HOST . self::BALANCE;

        $params = $this->get_default_params();
        $result = $this->request($url, $params);
        $result = explode("\n", $result);

        return array(
            'code' => $result[0],
            'balance' => $result[1]
        );
    }
    
    /**
     * Check day limit
     * 
     * @return array
     */
    public function limit()
    {
        $url = self::HOST . self::LIMIT;

        $params = $this->get_default_params();
        $result = $this->request($url, $params);
        $result = explode("\n", $result);

        return array(
            'code' => $result[0],
            'total' => $result[1],
            'current' => $result[2]
        );
    }
    
    /**
     * Get message cost
     * 
     * @param type $to
     * @param type $text
     * @return type
     */
    public function cost($to, $text) 
    {
        $url = self::HOST.self::COST;
        $this->id = null;

        $params = $this->get_default_params();
        $params['to'] = $to;
        $params['text'] = $text;

        $result = $this->request($url, $params);
        $result = explode("\n", $result);

        return array(
            'code' => $result[0],
            'price' => $result[1],
            'number' => $result[2]
        );
    }
    
    /**
     * Get my senders list
     * 
     * @return array
     */
    public function senders() 
    {
        $url = self::HOST . self::SENDERS;
        $params = $this->get_default_params();
        $result = $this->request( $url, $params );
        $result = explode("\n", rtrim($result));

        $response = array(
            'code' => $result[0],
            'senders' => $result
        );
        unset($response['senders'][0]);
        $response['senders'] = array_values($response['senders']);

        return $response;
    }
    
    /**
     * Check user auth
     * 
     * @return type
     */
    public function check() 
    {
        $url = self::HOST . self::CHECK;
        $params = $this->get_default_params();
        $result = $this->request($url, $params);

        return $result;
    }
    
    protected function get_default_params() 
    {
        $this->get_auth_token();
        $this->get_sha512();

        return array(
            'login' => $this->login,
            'token' => $this->token,
            'sha512' => $this->sha512
        );
    }
    
    protected function get_auth_token() 
    {
        $url = self::HOST . self::GET_TOKEN;
        $this->token = $this->request($url);

        return $this->token;

    }
    
    protected function get_sha512() 
    {
        $this->sha512 = hash('sha512', $this->password.$this->token);
    }
    
    protected function request($url, $params = array()) 
    {
        $ch = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => $params
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}