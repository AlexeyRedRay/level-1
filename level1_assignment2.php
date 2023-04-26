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

function parseTcpStringAsHttpRequest($string) {
 preg_match('/^[A-Z]{3,4}/', $string, $method);
 preg_match('/\/[a-zA-Z0-9\.\/]*/',$string,$uri);
 preg_match_all('/[A-Z][-a-zA-Z0-9\.\/]*:\s[^\n]*/', $string, $headers);
 $headers_array = array();
    foreach ($headers[0] as $header_string) {
            $sub_array = array();
            $header_key = substr($header_string, 0, strpos($header_string, ":"));
            $header_value = substr($header_string, strpos($header_string, ":") + 2);
            $sub_array[0] = $header_key;
            $sub_array[1] = $header_value;
            $headers_array[] = $sub_array;
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
echo(json_encode($http, JSON_PRETTY_PRINT));