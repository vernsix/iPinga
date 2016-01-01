<?php
namespace ipinga;

/*
* note: You need to make sure MySql is not running in strict mode.   Try using the following SQL statement from phpmyadmin
*  SET @@global.sql_mode= 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
 *
 */
class table
{

    public $tableName = '';

    /** @var array|mixed */
    public $field = array();

    public $fieldTypes = array(); // [ field_name=>fieldType, ... ]
    public $clearedValues = array();
    public $saved = false;
    public $lastSql = '';
    public $sqlParams = array();

    function __construct($tableName)
    {
        $ipinga = \ipinga\ipinga::getInstance();

        $this->tableName = $tableName;

        $sql = sprintf('describe %s', $this->tableName);
        try {
            foreach ($ipinga->pdo()->query($sql) as $row) {

                $fieldName = $row['Field'];

                if ($row['Type'] == 'tinyint(1)') {
                    $fieldType = 'boolean';
                    $clearedValue = false;
                } elseif (substr($row['Type'], 0, 3) == 'int') {
                    $fieldType = 'integer';
                    $clearedValue = 0;
                } elseif ($row['Type'] == 'timestamp') {
                    $fieldType = 'timestamp';
                    $clearedValue = '';
                } elseif ($row['Type'] == 'datetime') {
                    $fieldType = 'datetime';
                    $clearedValue = '';
                } elseif ($row['Type'] == 'date') {
                    $fieldType = 'date';
                    $clearedValue = '';
                } elseif ($row['Type'] == 'float') {
                    $fieldType = 'float';
                    $clearedValue = 0;
                } else {
                    $fieldType = 'varchar';
                    $clearedValue = '';
                }
                $this->fieldTypes[$fieldName] = $fieldType;
                $this->clearedValues[$fieldName] = $clearedValue;
            }
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
        }
        $this->clear();
    }

    public function clear()
    {
        foreach ($this->fieldTypes as $fieldName => $fieldType) {
            if ($fieldName == 'passwd') {
                $this->field[$fieldName] = base64_decode($this->clearedValues['passwd']);
            } else {
                $this->field[$fieldName] = $this->clearedValues[$fieldName];
            }
        }
        $this->saved = false;
    }


    public function __set($fieldName, $value)
    {
        $this->field[$fieldName] = $value;
    }

    public function __get($fieldName)
    {
        if (isset($this->field[$fieldName])) {
            return $this->field[$fieldName];
        }
        $c = debug_backtrace(false);
        throw new \Exception('<pre>Table: ' . $this->tableName . ' Unknown Field Name: ' . $fieldName . ' Trace: ' . var_export($c, true));
    }



    // *************************************************************************************************************
    // writing to the database...
    // *************************************************************************************************************

    /**
     * @return bool $success
     */
    public function save()
    {
        /** @var boolean $success */
        if ($this->field['id'] > 0) {

            if ($this->recordExists($this->field['id']) == true) {
                $success = $this->_Update();
            } else {
                $success = $this->_Insert();
            }

        } else {
            $success = $this->_Insert();
        }
        return $success;
    }


    private function recordExists($id)
    {
        $found = false;
        try {
            $sql = 'select count(*) as num_rows from ' . $this->tableName . ' where id = :id';
            $this->lastSql = $sql;
            $stmt = \ipinga\ipinga::getInstance()->pdo()->prepare($sql);
            $stmt->bindParam(':id', $id);
            $this->sqlParams = array('id' => $id);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row['num_rows'] > 0) {
                $found = true;
            }
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
        }
        return $found;
    }


    private function _Update()
    {
        $ipinga = \ipinga\ipinga::getInstance();

        $sql = 'update ' . $this->tableName . ' set ';

        $add_comma_on_next_field = false;
        foreach ($this->fieldTypes as $fieldName => $fieldType) {
            // id and timestamp take care of themselves in the database
            if (($fieldName <> 'id') && ($fieldType <> 'timestamp')) {
                if ($add_comma_on_next_field == true) {
                    $sql .= ', ';
                }
                $sql .= $fieldName . '=:' . $fieldName;
                $add_comma_on_next_field = true;
            }
        }

        $sql .= ' where id=:id';
        $this->lastSql = $sql;
        $this->sqlParams = array();    // start fresh
        $stmt = $ipinga->pdo()->prepare($sql);


        foreach ($this->fieldTypes as $fieldName => $fieldType) {
            // id and timestamp take care of themselves in the database
            if ($fieldType <> 'timestamp') {
                if ($fieldName == 'passwd') {
                    $passwd = bin2hex(\ipinga\crypto::encrypt($this->field[$fieldName]));
                    $stmt->bindParam(':' . $fieldName, $passwd);
                    $this->sqlParams[$fieldName] = $passwd;
                } else {
                    $stmt->bindParam(':' . $fieldName, $this->field[$fieldName]);
                    $this->sqlParams[$fieldName] = $this->field[$fieldName];
                }
            }
        }

        $retval = false;
        try {
            $retval = $stmt->execute();
            if ($this->field['id'] == 0) {
                $this->field['id'] = $ipinga->pdo()->lastInsertId();
            }
            $this->saved = true;
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
            $this->saved = false;
        }
        return $retval;
    }

    private function _Insert()
    {

        $ipinga = \ipinga\ipinga::getInstance();

        $sqlfields = array();
        $sqlparams = array();

        $sql = 'insert into ' . $this->tableName . ' (';
        foreach ($this->fieldTypes as $fieldName => $fieldType) {
            // timestamp takes care of itself in the database
            if ($fieldType <> 'timestamp') {
                $sqlfields[] = $fieldName;
                $sqlparams[] = ':' . $fieldName;
            }
        }
        $sql = $sql . implode(',', $sqlfields) . ') values (' . implode(',', $sqlparams) . ')';
        $this->lastSql = $sql;
        $this->sqlParams = array();

        $sth = $ipinga->pdo()->prepare($sql);
        foreach ($this->fieldTypes as $fieldName => $fieldType) {
            // id and timestamp take care of themselves in the database
            if ($fieldType <> 'timestamp') {
                if ($fieldName == 'created') {
                    $created = date('Y-m-d H:i:s');
                    $sth->bindParam(':' . $fieldName, $created);
                    $this->sqlParams[$fieldName] = $created;
                } elseif ($fieldName == 'passwd') {
                    $passwd = bin2Hex(\ipinga\crypto::encrypt($this->field[$fieldName]));
                    //            $passwd = base64_encode($this->field[$fieldName]);
                    $sth->bindParam(':' . $fieldName, $passwd);
                    $this->sqlParams[$fieldName] = $passwd;
                } else {
                    $sth->bindParam(':' . $fieldName, $this->field[$fieldName]);
                    $this->sqlParams[$fieldName] = $this->field[$fieldName];
                }
            }
        }
        $retval = false;
        try {
            $retval = $sth->execute();
            if ($this->field['id'] == 0) {
                $this->field['id'] = $ipinga->pdo()->lastInsertId();
            }
            $this->saved = true;
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
            $this->saved = false;
        }
        return $retval;
    }


    // *************************************************************************************************************
    // misc delete methods...
    // *************************************************************************************************************

    /**
     * Delete the current database record that matches this object's record
     */
    public function delete()
    {
        $this->deleteById($this->field['id']);
    }

    /**
     * Delete a record in the database using its id
     *
     * @param integer $id
     *
     * @return bool
     */
    public function deleteById($id)
    {
        $ipinga = \ipinga\ipinga::getInstance();
        $this->clear();
        try {
            $sql = 'delete from ' . $this->tableName . ' where id = :id';
            $this->lastSql = $sql;
            $stmt = $ipinga->pdo()->prepare($sql);
            $stmt->bindParam(':id', $id);
            $this->sqlParams = array('id' => $id);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
            $this->saved = false;
        }
    }



    // *************************************************************************************************************
    // misc read methods...
    // *************************************************************************************************************

    /**
     * Some other function prepares this and does all the binding.  All I am doing here is executing it in a common
     * fashion and populating all the fields() array
     *
     * @param \PDOStatement $stmt
     */
    protected function _process_loadby_execute($stmt)
    {
        try {
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            foreach ($this->fieldTypes as $fieldName => $fieldType) {
                if ($fieldName == 'passwd') {
                    $this->field[$fieldName] = \ipinga\crypto::decrypt(hex2bin($row['passwd']));
                } else {
                    $this->field[$fieldName] = $row[$fieldName];
                }
            }

            if ($this->field['id'] < 1) {
                $this->saved = false;
            } else {
                $this->saved = true;
            }

        } catch (\Exception $e) {
            echo $e->getMessage() . '<br><hr>';
            $this->saved = false;
        }
    }

    public function loadById($id)
    {
        $this->clear();
        if (!$id == 0) {

            try {
                $sql = 'select * from ' . $this->tableName . ' where id = :id';
                $this->lastSql = $sql;
                $stmt = \ipinga\ipinga::getInstance()->pdo()->prepare($sql);
                $stmt->bindParam(':id', $id);
                $this->sqlParams = array('id' => $id);
                $this->_process_loadby_execute($stmt);
            } catch (\PDOException $e) {
                echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
                $this->saved = false;
            }

        }
        return $this->saved;
    }

    public function loadBySecondaryKey($fieldName, $desiredValue)
    {
        $this->clear();
        try {
            $sql = 'select * from ' . $this->tableName . ' where ' . $fieldName . ' = :desired_value';
            $this->lastSql = $sql;
            $stmt = \ipinga\ipinga::getInstance()->pdo()->prepare($sql);
            $stmt->bindParam(':desired_value', $desiredValue);
            $this->sqlParams = array('desired_value' => $desiredValue);
            $this->_process_loadby_execute($stmt);
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
            $this->saved = false;
        }
        return $this->saved;
    }


    /**
     * @param $fields array
     *
     * @return bool
     */
    public function loadByFieldsMatching($fields)
    {
        $this->clear();

        $w = '';
        foreach($fields as $fieldName => $desiredValue) {
            if (empty($w)==false) {
                $w .= ' AND ';
            }
            $w .= $fieldName . ' = :' . $fieldName;
        }

        try {

            $sql = 'select * from ' . $this->tableName . ' where ' . $w;
            $this->lastSql = $sql;
            $stmt = \ipinga\ipinga::getInstance()->pdo()->prepare($sql);
            foreach($fields as $fieldName => $desiredValue) {
                $stmt->bindParam(':'. $fieldName, $desiredValue);
            }
            $this->_process_loadby_execute($stmt);
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
            $this->saved = false;
        }
        return $this->saved;
    }




    /**
     * WARNING!  This son-of-a-gun is ripe with the ability to screw the pooch!  PDO doesn't allow a dynamic where
     * clause. Meaning... you can only bindParam to field=value pairs.   It is 100% your responsibility to make
     * sure the where clause you pass to me is safe from SqlInjection.  Just remember "Bobby Tables"!!!!  YOU HAVE
     * BEEN WARNED.
     *
     * @param $where
     *
     * @return bool
     */
    public function loadByCustomWhere($where)
    {
        $this->clear();
        try {
            $sql = 'select * from ' . $this->tableName . ' where ' . $where;
            $this->lastSql = $sql;
            $this->sqlParams = array();
            $stmt = \ipinga\ipinga::getInstance()->pdo()->prepare($sql);
            $this->_process_loadby_execute($stmt);
        } catch (\PDOException $e) {
            echo $e->getMessage() . '<br>' . $sql . '<br><hr>';
            $this->saved = false;
        }
        return $this->saved;
    }

}


?>