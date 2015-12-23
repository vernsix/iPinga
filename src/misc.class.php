<?php
namespace ipinga;

class misc
{

    /**
     * @param int    $length
     * @param string $prefix
     * @param string $characterPool
     *
     * @return string
     */
    public static function randomString($length = 64, $prefix = '', $characterPool = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789')
    {
        $randomString = $prefix;

        $charsToAdd = ($length - strlen($randomString));
        for ($i = 0; $i < $charsToAdd; $i++) {
            $randomString .= $characterPool[mt_rand(0, strlen($characterPool) - 1)];
        }

        return $randomString;
    }

}