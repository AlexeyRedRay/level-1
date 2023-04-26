<?php

function readHttpLikeInput() {
    $f = fopen( 'php://stdin', 'r' );
    $store = "";
    $toread = 0;
    while( $line = fgets( $f ) ) {
        $store .= preg_replace("/\r/", "", $line);
        if (preg_match('/Content-Length: (\d+)/',$line,$m))
            $toread=$m[1]*1;
        if ($line == "\r\n")
            break;
    }
    if ($toread > 0)
        $store .= fread($f, $toread);
    return $store;
}

$contents = readHttpLikeInput();

function outputHttpResponse($statuscode, $statusmessage, $headers, $body) {
    $date_now = date("r");
    echo "HTTP/1.1 $statuscode $statusmessage
Date: $date_now
Server: Apache/2.2.14 (Win32)
Content-Length: $headers
Connection: Closed
Content-Type: text/html; charset=utf-8

$body";
}

function processHttpRequest($method, $uri, $headers, $body) {
    if($method == "GET") {
        if (strpos($uri, "/sum") === 0) {
            if(strpos($uri, "?nums=")) {
                preg_match_all('/[0-9]/', $uri, $uri_numb);
                $sum = 0;
                foreach ($uri_numb[0] as $number) {
                    $sum += $number; 
                }
                outputHttpResponse("200", "OK", strlen((string)$sum), $sum);
            } else {
                outputHttpResponse("400", "Bad Request", strlen("bad request"), "bad request");
            }
        } else {
            outputHttpResponse("404", "Not Found", strlen("not found"), "not found");
        }
    } 
    if($method == "POST") {
        if (strpos($uri, "/api/checkLoginAndPassword") === 0 && $headers["Content-Type"] == "application/x-www-form-urlencoded") {
            $start =  strpos($body, "=") + 1;
            $count = strpos($body, "&") - $start;
            $login = substr($body, $start, $count);
            $password = substr($body, strrpos($body, "=") + 1);
            if(file_exists(__DIR__ . '\passwords.txt')) {
                $file_contents = file_get_contents(__DIR__ . '\passwords.txt');
                if(strlen(strpos($file_contents, $login . ":" . $password . PHP_EOL)) > 0) {
                    $body_text = "<h1 style=\"color:green\">FOUND</h1>";
                } else {
                    $body_text = "<h1 style=\"color:red\">invalid login or password</h1>";
                }
                outputHttpResponse("200", "OK", strlen($body_text), $body_text);
            } else {
                outputHttpResponse("500", "Internal Server Error", strlen("internal server error"), "internal server error");
            }
        } else {
            outputHttpResponse("404", "Not Found", strlen("not found"), "not found");
        }
    }
}


function parseTcpStringAsHttpRequest($string) {
 preg_match('/^[A-Z]{3,4}/', $string, $method);
 preg_match('/\/[a-zA-Z0-9?=,\.\/]*/', $string, $uri);
 preg_match_all('/[A-Z][-a-zA-Z0-9\.\/]*:\s[^\n]*/', $string, $headers);
 $headers_array = array();
    foreach ($headers[0] as $header_string) {
        $header_key = substr($header_string, 0, strpos($header_string, ":"));
        $header_value = substr($header_string, strpos($header_string, ":") + 2);
        $headers_array[$header_key] = $header_value;
    }

 preg_match('/[^\n]*$/', $string, $body);
 return array(
     "method" => $method[0],
     "uri" => $uri[0],
     "headers" => $headers_array,
     "body" => $body[0],
 );
}

$http = parseTcpStringAsHttpRequest($contents);
processHttpRequest($http["method"], $http["uri"], $http["headers"], $http["body"]);