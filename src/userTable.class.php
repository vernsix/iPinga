<?php
namespace ipinga;

Class userTable Extends \ipinga\table
{

    public function loadByEmail($email = '')
    {
        $ipinga = \ipinga\ipinga::getInstance();
        $this->clear();
        try {
            $sql = 'select * from ' . $this->tableName . ' where email = :email';
            $this->lastSql = $sql;
            $stmt = $ipinga->pdo()->prepare($sql);
            $stmt->bindParam(':email', $email);
            $this->_process_loadby_execute($stmt);
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
            $this->saved = false;
        }
        return $this->saved;
    }

    public function isDupeEmail($email = '')
    {
        $ipinga = \ipinga\ipinga::getInstance();
        $IsDupe = true;
        if (!empty($email)) {
            try {
                $sql = 'select count(*) as row_count from ' . $this->tableName . ' where email = :email';
                $this->lastSql = $sql;
                $stmt = $ipinga->pdo()->prepare($sql);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row['row_count'] == 0) {
                    $IsDupe = false;
                }
            } catch (\PDOException $e) {
                echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
                $this->saved = false;
            }
        }
        return $IsDupe;
    }

    public function loadByUsername($username = '')
    {
        $ipinga = \ipinga\ipinga::getInstance();
        $this->clear();
        try {
            $sql = 'select * from ' . $this->tableName . ' where username = :username';
            $this->lastSql = $sql;
            $stmt = $ipinga->pdo()->prepare($sql);
            $stmt->bindParam(':username', $username);
            $this->_process_loadby_execute($stmt);
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
            $this->saved = false;
        }
        return $this->saved;
    }

    public function isDupeUsername($username = '')
    {
        $ipinga = \ipinga\ipinga::getInstance();
        $IsDupe = true;
        if (!empty($username)) {
            try {
                $sql = 'select count(*) as row_count from ' . $this->tableName . ' where username = :username';
                $this->lastSql = $sql;
                $stmt = $ipinga->pdo()->prepare($sql);
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row['row_count'] == 0) {
                    $IsDupe = false;
                }
            } catch (\PDOException $e) {
                echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
                $this->saved = false;
            }
        }
        return $IsDupe;
    }

}
