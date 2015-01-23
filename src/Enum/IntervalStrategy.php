<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Hopeter1018\BackgroundProcess\Enum;

use Exception;
use Hopeter1018\BackgroundProcess\BackgroundProcess;

/**
 * Description of IntervalStrategy
 * <ul>
 * <li>ONCE</li>
 * <li>REPEATED_WAIT_NEXT</li>
 * <li>REPEATED_CONTINUED</li>
 * </ul>
 * @version $id$
 * @author peter.ho
 */
final class IntervalStrategy extends BackgroundProcessEnum
{

    /**
     * Run only once
     */
    CONST ONCE = 1;

    /**
     * Run per interval, wait for next interval if a process exceeds
     */
    CONST REPEATED_WAIT_NEXT = 2;

    /**
     * Run per interval, run next if a process exceeds
     */
    CONST REPEATED_CONTINUED = 3;

    /**
     *
     * @var int 
     */
    protected $value = self::ONCE;

    /**
     *
     * @var int 
     */
    private $repeatMax = 0;

    /**
     * 
     * @param int $value
     * @param int $repeatMax
     */
    public function __construct($value, $repeatMax = 0)
    {
        parent::__construct($value);
        $this->repeatMax = $repeatMax;
    }

// <editor-fold defaultstate="collapsed" desc="Getters">

    /**
     * 
     * @return int
     */
    public function getRepeatMax()
    {
        return $this->repeatMax;
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Enum Shortcut">

    /**
     * Return once
     * @link self::ONCE
     * @return \self
     */
    public static function once()
    {
        return new self(self::ONCE);
    }

    /**
     * Return repeated wait next
     * @link self::REPEATED_WAIT_NEXT
     * @param int $repeatMax
     * @return \self
     */
    public static function repeatedWaitNext($repeatMax = 3)
    {
        return new self(self::REPEATED_WAIT_NEXT, $repeatMax);
    }

    /**
     * Return repeated continued
     * @link self::REPEATED_CONTINUED
     * @param int $repeatMax
     * @return \self
     */
    public static function repeatedContinued($repeatMax = 3)
    {
        return new self(self::REPEATED_CONTINUED, $repeatMax);
    }

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Events">

    public function toArray()
    {
        return array($this->value, $this->repeatMax);
    }

    public static function fromArray($array)
    {
        list($value, $repeatMax) = $array;
        $new = new static($value, $repeatMax);
        return $new;
    }

    public static function isAllowed(BackgroundProcess $bp)
    {
        $isAllowed = false;
        if ($bp->getIntervalStrategy()->isValue(self::ONCE)) {
            $isAllowed = ($bp->getRunTotal() === 0);
        } elseif ($bp->getIntervalStrategy()->getRepeatMax() > 0) {
            $isAllowed = $bp->getIntervalStrategy()->getRepeatMax() > $bp->getRunTotal() + 1;
        } else {
            $isAllowed = true;
        }
        return $isAllowed;
    }

// </editor-fold>

}
