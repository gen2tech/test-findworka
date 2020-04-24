<?php

namespace App\Controllers;

use Inf\Router\RouterRequest;
use App\Sqlite;
use App\Helper;

class Controller{    
	private $response; // Array storing response
	private $statusCode; // int status code
    private $contentType; // String status code
    public $db;
    public $helper;
	
    const DEFAULT_RESPONSE_FORMAT = 'json'; // Default response format
    private static $contentTypes = array(
        'xml' => 'application/xml',
        'json' => 'application/json; charset=UTF-8',
        'text' => 'text/plain'
    );
    private static $formats = array('xml', 'json', 'text');
    private static $codes = array(  
        100 => 'Continue',  
        101 => 'Switching Protocols',  
        200 => 'OK',  
        201 => 'Created',  
        202 => 'Accepted',  
        203 => 'Non-Authoritative Information',  
        204 => 'No Content',  
        205 => 'Reset Content',  
        206 => 'Partial Content',  
        300 => 'Multiple Choices',  
        301 => 'Moved Permanently',  
        302 => 'Found',  
        303 => 'See Other',  
        304 => 'Not Modified',  
        305 => 'Use Proxy',  
        306 => '(Unused)',  
        307 => 'Temporary Redirect',  
        400 => 'Bad Request',  
        401 => 'Unauthorized',  
        402 => 'Payment Required',  
        403 => 'Forbidden',  
        404 => 'Not Found',  
        405 => 'Method Not Allowed',  
        406 => 'Not Acceptable',  
        407 => 'Proxy Authentication Required',  
        408 => 'Request Timeout',  
        409 => 'Conflict',  
        410 => 'Gone',  
        411 => 'Length Required',  
        412 => 'Precondition Failed',  
        413 => 'Request Entity Too Large',  
        414 => 'Request-URI Too Long',  
        415 => 'Unsupported Media Type',  
        416 => 'Requested Range Not Satisfiable',  
        417 => 'Expectation Failed',  
        500 => 'Internal Server Error',  
        501 => 'Not Implemented',  
        502 => 'Bad Gateway',  
        503 => 'Service Unavailable',  
        504 => 'Gateway Timeout',  
        505 => 'HTTP Version Not Supported'  
    );


    public function __construct(){
		$this->helper = new Helper();
        $this->db = $this->helper->getDB();
        if(!$this->db->table_exists('comments')){
            $createTable = "CREATE TABLE IF NOT EXISTS comments (
                                id INTEGER PRIMARY KEY AUTOINCREMENT,
                                book_id INTEGER NOT NULL,
                                commenter text NOT NULL,
                                comment text NOT NULL,
                                ip_address text NOT NULL,
                                date text NOT NULL
                            );";
            $this->db->execute($createTable);
        }
    }


    /**
	 * Function returns HTTP response message based on HTTP response status code
	 */
	private function getStatusMessage($status) {
        return (isset(self::$codes[$status])) ? self::$codes[$status] : self::$codes[500];
    }

    /**
	 * Function returns response format from allowed list
	 * else the default response format
	 */
	private function getResponseFormat($format) {
		return (in_array($format, self::$formats)) ? $format : self::DEFAULT_RESPONSE_FORMAT;
    }
    /**
	 * Function returns response content type.
	 */
	private function getResponseContentType($type = null) {
		return self::$contentTypes[$type];
	}
    
    

	private function xmlHelper($data, $version = '1.0', $encoding = 'UTF-8') {
		$xml = new XMLWriter;
		$xml->openMemory();
		$xml->startDocument($version, $encoding);

		if(!function_exists('write')) {
			function write(XMLWriter $xml, $data, $old_key = null) {
				foreach($data as $key => $value){
					if(is_array($value)){
						if(!is_int($key)) {
							$xml->startElement($key);
						}
						write($xml, $value, $key);
						if(!is_int($key)) {
							$xml->endElement();
						}
						continue;
					}
					// Special handling for integer keys in array
					$key = (is_int($key)) ? $old_key.$key : $key;
					$xml->writeElement($key, $value);
				}
			}
		}
		write($xml, $data);
		return $xml->outputMemory(true);
    }
    


    /**
	 * Function implementing xml response helper.
	 * Converts response array to xml response.
	 */
	private function xmlResponse($data) {
		return $this->xmlHelper($data);
	}

	/**
	 * Function implementating json response helper.
	 * Converts response array to json.
	 */
	private function jsonResponse($data) {
		return json_encode($data, JSON_PRETTY_PRINT);
	}

	/**
	 * Function implementing querystring response helper
	 * Converts response array to querystring.
	 */
	private function textResponse($data) {
		return http_build_query($data);
    }

    protected function process($data,$statusCode=200,$contentType='json'){
        
        $method = $contentType . 'Response';
        $this->statusCode = $statusCode;
        $this->contentType = $this->getResponseFormat($contentType);
        $this->response = $this->$method($data);
        return $this;
    }
    

    protected function send() {

		$status = (isset($this->statusCode)) ? $this->statusCode : 200;
		$contentType = (isset($this->contentType)) ? $this->contentType : 'text';
		$contentType = $this->getResponseContentType($contentType);
		$body = (empty($this->response)) ? '' : $this->response;

		$headers = 'HTTP/1.1 ' . $status . ' ' . $this->getStatusMessage($status);
		header($headers);
        header('Content-Type: ' . $contentType);
        
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: access");
        header("Access-Control-Allow-Methods: GET,POST, OPTION");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        
		echo $body;
	}
}