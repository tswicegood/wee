<?php

namespace wee\framework;

interface Exception {}

class InvalidStateException extends \Exception implements Exception {}

class Runner
{
    public static function run(array $routes) {
        foreach ($routes as $route => $executable) {
            list($verb, $route_regexp) = explode(' ', $route, 2);

            if ($verb != $_SERVER['REQUEST_METHOD']) {
                continue;
            } elseif (preg_match("@{$route_regexp}@", $_SERVER['REQUEST_URI'])) {
                return self::process($executable);
            }
        }

        throw new InvalidStateException();
    }

    private static function process($executable) {
        $result = $executable();
        $response = new Response();
        $response->headers = isset($result['headers']) ? $result['headers'] : array();
        $response->body = $result['body'];
        return $response;
    }
}

class Response {
    public $headers = array();
    public $body = '';

    public function __toString() {
        $this->_doHeaders();
        return $this->body;
    }

    private function _doHeaders() {
        if (empty($this->headers)) {
            return;
        }

        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}\r\n");
        }
    }
}

echo Runner::run(array(

    'GET /examples/foo.php$' => function() {
        return array(
            'headers' => array(
                'X-Powered-By' => 'wee: a wee little framework',
            ),
            'body' => 'Hello world from wee',
        );
    },
    
    'GET /examples/foo.php/(\d+)/' => function() {
        return array(
            'headers' => array(
                'X-Powered-By' => 'wee: a wee little framework',
            ),
            'body' => 'This is the other one',
        );
    },
    

    'GET /examples/foo.php/redirect' => function() {
        return array(
            'headers' => array(
                'Location' => '/examples/foo.php/2/',
            ),
        );
    },
    


));
