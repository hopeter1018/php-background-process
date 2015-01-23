<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\BackgroundProcess\PostAdapter;

use Exception;
use Hopeter1018\BackgroundProcess\BackgroundProcess;

/**
 * Description of FSockOpen
 *
 * @version $id$
 * @author peter.ho
 */
class FSockOpen extends PostAdapter
{

    /**
     * 
     * @param BackgroundProcess $backgroundProcess
     * @param array|string[] $customHttpHeaders
     * @throws Exception
     */
    public static function post(BackgroundProcess $backgroundProcess, $customHttpHeaders = null)
    {
        $socket = static::getFsockopen();
        if ($socket) {
            stream_set_timeout($socket, 0, static::TIMEOUT_MICROSECONDS);
            fwrite($socket, self::getFsockHeader($backgroundProcess, $customHttpHeaders));
            fgets($socket, 512);
            fclose($socket);
        } else {
            throw new Exception("Can't create socket");
        }
    }

    /**
     * Get the http header string used in fsockopen
     * 
     * @param BackgroundProcess $backgroundProcess
     * @param array|string[] $customHttpHeaders
     * @return string
     */
    private static function getFsockHeader(BackgroundProcess $backgroundProcess, $customHttpHeaders = null)
    {
        return sprintf(
            "GET %s HTTP/1.0\r\nUser-Agent: %s\r\nHOST: %s\r\n"
            . "Content-type: application/x-www-form-urlencoded\r\n"
            . "%s\r\nAccept:*/*\r\n\r\n"
            . "%s\r\n\r\n",
            $_SERVER['PHP_SELF'],
            PostAdapter::USERAGENT,
            $_SERVER['HTTP_HOST'],
            implode("\r\n", self::getCustomHttpHeaders($backgroundProcess, $customHttpHeaders)),
            $_SERVER['QUERY_STRING']
        );
    }

    private static function getFsockopen()
    {
        $errno = array();
        $errstr = '';
        return fsockopen(
            $_SERVER['HTTP_HOST'],
            $_SERVER[$_SERVER['HTTPS'] === 'on'? 'SERVER_PORT_SECURE' : 'SERVER_PORT'],
            $errno,
            $errstr,
            1
        );
    }
}
