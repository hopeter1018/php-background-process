<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\BackgroundProcess\PostAdapter;

use Hopeter1018\BackgroundProcess\BackgroundProcess;

/**
 * Base class of PostAdapter
 *
 * @version $id$
 * @author peter.ho
 */
abstract class PostAdapter implements PostAdapterInterface
{

    CONST TIMEOUT_MICROSECONDS = 1;
    CONST USERAGENT = "ZMS background instance";

    /**
     * 
     * @param BackgroundProcess $backgroundProcess
     * @param array|string[] $customHttpHeaders
     * @return array|string[]
     */
    public static function getCustomHttpHeaders(BackgroundProcess $backgroundProcess, $customHttpHeaders = null)
    {
        $result = array(
            'ZMS_SCHEDULER: ' . base64_encode($backgroundProcess->toJson()),
        );
        if ($customHttpHeaders !== null and is_array($customHttpHeaders)) {
            foreach ($customHttpHeaders as $header)
            {
                $result[] = $header;
            }
        }
        return $result;
    }

    /**
     * 
     * @param BackgroundProcess $backgroundProcess
     */
    public static function postScheduler(BackgroundProcess $backgroundProcess)
    {
        if (function_exists('curl_init')) {
            Curl::post($backgroundProcess);
        } else {
            FSockOpen::post($backgroundProcess);
        }
    }

    /**
     * 
     * @param BackgroundProcess $backgroundProcess
     */
    public static function postRun(BackgroundProcess $backgroundProcess)
    {
        $customHttpHeader = array('ZMS_SCHEDULER_RUN: 1');
        if (function_exists('curl_init')) {
            Curl::post($backgroundProcess, $customHttpHeader);
        } else {
            FSockOpen::post($backgroundProcess, $customHttpHeader);
        }
    }

}
