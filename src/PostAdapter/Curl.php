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
 * Description of Curl
 *
 * @version $id$
 * @author peter.ho
 */
class Curl extends PostAdapter
{

    private static function getCurlUrl()
    {
        return sprintf(
            "http%s://%s%s?%s",
            $_SERVER['HTTPS'] == 'on' ? 's' : '',
            $_SERVER['HTTP_HOST'],
            $_SERVER['PHP_SELF'],
            $_SERVER['QUERY_STRING']
        );
    }
    /**
     * Get the options of CURL
     * 
     * @param BackgroundProcess $backgroundProcess
     * @param array|string[] $customHttpHeaders
     * @return array
     */
    private static function getCurlParameter(BackgroundProcess $backgroundProcess, $customHttpHeaders = null)
    {
        return array (
            CURLOPT_URL => static::getCurlUrl(),
            CURLOPT_HTTPHEADER => PostAdapter::getCustomHttpHeaders($backgroundProcess, $customHttpHeaders),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => PostAdapter::TIMEOUT_MICROSECONDS * 1000,
            CURLOPT_USERAGENT => PostAdapter::USERAGENT,
        );
    }

    /**
     * Post with CUrl
     * 
     * @param BackgroundProcess $backgroundProcess
     * @param array|string[] $customHttpHeaders
     * @throws Exception
     */
    public static function post(BackgroundProcess $backgroundProcess, $customHttpHeaders = null)
    {
        $ch = curl_init();
        curl_setopt_array($ch, self::getCurlParameter($backgroundProcess, $customHttpHeaders));
        if (curl_exec($ch)) {
            var_dump(curl_errno($ch));
            var_dump(curl_error($ch));
            var_dump(self::getCurlParameter($backgroundProcess, $customHttpHeaders));
            throw new Exception("Can't exec curl");
        }
    }

}
