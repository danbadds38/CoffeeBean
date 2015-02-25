<?php
/**
 * Created by PhpStorm.
 * User: DBADDELE
 * Date: 12/23/2014
 * Time: 6:46 PM
 */
namespace CoffeeBean;

class RequestHelper{
    public function __construct($args = null) {
//        $options = array('POST','GET','REQUEST','RAW');
    }
    public function error($code) {
        $error['400'] = 'There seems to be a malformed request method. This endpoint must be retrieved via a [POST] request, and in valid JSON Format';
        $error['401'] = 'The token you have passed could not be successfully authenticated, please enter a different token.';
        $error['422'] = 'The {data} argument & OR its contents is malformed, please restructure your data object into valid JSON.';

        return $error[$code];
    }

    /**
     *  IS USED FOR ERROR HANDLING ON - SOSS UPDATE ENDPOINTS
     */
    public function authHeader($code)
    {
        $body = '<style>html { height: 100%; background: url("/img/fail_whale_big.png") no-repeat   fixed ; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover; }</style>';
        $body .= '<html><body>' . $this->error($code) . '</body></html>';
        if ($code == 400) {
            header("HTTP/1.0 400 Bad Request. ");
            die($body);
        } elseif ($code == 401) {
            header("HTTP/1.0 401 Bad Authorization Token. ");
            die($body);
        } elseif ($code == 422) {
            header("HTTP/1.0 422 Valid Syntax - Bad Data. ");
            die($body);
        }
        return $code;
        /** NO NEED TO RETURN */
    }

    public function fetchRequest($flags = null) {
        if(!isset($flags)) { $flags = 'REQUEST'; }
        if($flags === 'POST' || $flags === 'GET' || $flags === 'REQUEST'){
            $request = $_REQUEST;
            if(is_array($request)){
                foreach($request as $key => $value) {
                    $response[$key] = preg_replace('/[^!.:()@&\/\s-a-zA-Z0-9_]/','', $value);
                }
            } else {
                //Throw Bad Request - 400 Error
                $this->authHeader('400');
            }
        }
        elseif($flags === 'RAW'){
            $data = json_decode(file_get_contents('php://input'), true);
            if(is_array($data)) {
                $response = $data;
            } else {
                $this->authHeader('400');
            }
        }
        elseif($flags === 'FILE'){
            $data = $_FILES;
            if(is_array($data)){
                $response = $data;
            } else {
                $this->authHeader('400');
            }
        }

        return $response;
    }

    public function query($sql,$type = 'SELECT') {
        $response = array();
        $response['query'] = $sql;
        $response['result'] = mysql_query($sql);
        $response['count'] = mysql_num_rows($response['result']);
        $response['error'] = mysql_error();

        if($response['result']){
            $response['status'] = true;
        } else {
            $response['status'] = false;
        }

        if($type === 'SELECT') {
            if($response['count'] > 1) {
                while($row = mysql_fetch_assoc($response['result'])){
                    $response['data'][] = $row;
                }
            } else {
                $response['data'] = mysql_fetch_assoc($response['result']);
            }
        }	else {
            $response['data'] = null;
        }

        return $response;
    }

    public function sendRequest($url, $method = null,$params = array()){
        // create a new cURL resource
        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //if(!isset($method) || $method == 'POST') { curl_setopt($ch, CURLOPT_POST, 1); }
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        // grab URL and pass it to the browser
        $response = curl_exec($ch);

        // close cURL resource, and free up system resources
        // curl_close($ch);
        return $response;
    }
}