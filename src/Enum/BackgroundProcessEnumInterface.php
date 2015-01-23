<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\BackgroundProcess\Enum;

use Hopeter1018\BackgroundProcess\BackgroundProcess;

/**
 * Description of BackgroundProcessEnum
 *
 * @version $id$
 * @author peter.ho
 */
interface BackgroundProcessEnumInterface
{

    public static function fromArray($array);
    public static function isAllowed(BackgroundProcess $bp);
//    public static function doScheduler(BackgroundProcess $backgroundProcess);
//    public static function doRun(BackgroundProcess $backgroundProcess);
}
