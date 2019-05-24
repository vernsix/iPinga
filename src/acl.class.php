<?php
/*
    Vern Six MVC Framework version 3.0

    Copyright (c) 2007-2018 by Vernon E. Six, Jr.
    Author's websites: http://www.ipinga.com and http://www.VernSix.com

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to use
    the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice, author's websites and this permission notice
    shall be included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
    IN THE SOFTWARE.
*/
namespace ipinga;

class acl
{

    /*
    requires a database table with the following structure...

    CREATE TABLE IF NOT EXISTS `acl` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `access_word` varchar(50) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

     */

    /**
     * @var table
     */
    public static $userTable;

    /**
     * @var string
     */
    public static $userTableName = 'user';

    /**
     * @var string
     */
    public static $usernameFieldName = 'username';

    /**
     * @var string
     */
    public static $passwordFieldName = 'passwd';

    /**
     * @var string
     */
    public static $aclTableName = 'acl';


    /**
     * @param $username
     * @param $password
     *
     * @return bool
     */
    public static function authenticate($username, $password)
    {
        // I really just store this table as a static so it can be used to communicate with the hasAccess method
        // ie: if a userId wasn't passed, then assume it's the user we just authenticated
        // Also handy to allow outside functions to get at the user table record for the currently authenticated user
        if (isset(self::$userTable) == false) {
            self::$userTable = new table(self::$userTableName);
        }

        self::$userTable->loadBySecondaryKey(self::$usernameFieldName, $username);
        return (self::$userTable->saved) ? (self::$userTable->field[self::$passwordFieldName] == $password) : false;
    }

    /**
     * @param string $accessWord
     * @param int    $userId
     *
     * @return bool
     */
    public static function hasAccess($accessWord, $userId = 0)
    {
        if (isset(self::$userTable) == false) {
            self::$userTable = new table(self::$userTableName);
        }
        if ($userId == 0) {
            $userId = self::$userTable->id;
        }
        $sql = 'select count(*) as rowcount from ' . self::$aclTableName . ' where user_id = :user_id and access_word = :access_word';
        try {
            $stmt = \ipinga\ipinga::getInstance()->pdo()->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':access_word', $accessWord);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $retval = $row['rowcount'] > 0;
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
            $retval = false;
        }
        return $retval;
    }

    public static function addAccess($accessWord, $userId = 0)
    {
        if (isset(self::$userTable) == false) {
            self::$userTable = new table(self::$userTableName);
        }
        if ($userId == 0) {
            $userId = self::$userTable->id;
        }

        if (self::hasAccess($accessWord, $userId) == false) {
            // add it...
            $aclTable = new table(self::$aclTableName);
            $aclTable->id = 0;
            $aclTable->user_id = $userId;
            $aclTable->access_word = $accessWord;
            $aclTable->save();
        }
    }

    public static function removeAccess($accessWord, $userId = 0)
    {
        if (isset(self::$userTable) == false) {
            self::$userTable = new table(self::$userTableName);
        }
        if ($userId == 0) {
            $userId = self::$userTable->id;
        }

        $sql = 'delete from ' . self::$aclTableName . ' where user_id = :user_id and access_word = :access_word';
        try {
            $stmt = \ipinga\ipinga::getInstance()->pdo()->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':access_word', $accessWord);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
        }
    }


}
