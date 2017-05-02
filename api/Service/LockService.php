<?php namespace api\Service;
/* * *********************************************************
* 锁的封装
* @Author  linhecheng<linhechengbush@live.com>
* @Date: 2016/4/26 11:05
* *********************************************************** */
use Cml\Lock;
use Cml\Service;

class LockService extends Service
{
    /**
     * 锁住某个值
     *
     * @param string $key
     *
     * @return bool
     */
    public static function lockWait($key)
    {
        $i = 0;
        while (!Lock::getLocker()->lock($key)) {
            if (++$i >= 3) {
                return false;
            }
            usleep(2000);
        }

        return true;
    }

    /**
     * 解锁某个值
     *
     * @param int $key
     */
    public static function unLockWait($key)
    {
        Lock::getLocker()->unlock($key);
    }
}