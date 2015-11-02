<?php
namespace ipinga;

class acl
{

    public $userTable;
    public $userTableName;
    public $usernameFieldName;
    public $passwordFieldName;
    public $aclTableName;

    public function __construct($userTablename = 'user', $usernameFieldName = 'username', $passwordFieldName = 'passwd', $aclTableName = 'acl')
    {
        $this->userTableName = $userTablename;
        $this->userTable = new \ipinga\table($userTablename);
        $this->usernameFieldName = $usernameFieldName;
        $this->passwordFieldName = $passwordFieldName;
        $this->aclTableName = $aclTableName;
    }

    public function authenticate($username, $password)
    {
        $this->userTable->loadBySecondaryKey($this->usernameFieldName, $username);
        return ($this->userTable->saved) ? ($this->userTable->field[$this->passwordFieldName] == $password) : false;
    }

    public function hasAccess($accessWord,$userId=0)
    {
        if ($userId==0) {
            $userId = $this->userTable->id;
        }
        $sql = 'select count(*) as rowcount from ' . $this->aclTableName .' where user_id = :user_id and access_word = :access_word';
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

}



?>