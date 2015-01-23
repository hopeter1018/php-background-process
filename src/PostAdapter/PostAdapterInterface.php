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
interface PostAdapterInterface
{

    public static function post(BackgroundProcess $backgroundProcess, $customHttpHeaders = null);

}
