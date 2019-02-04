<?php


    /*-----------------------------------------------------
     |
     |  mana/www/core/Controllers/ORM.php
     |
     */


        class ORM
        {

            protected   static  $_instance;
            protected           $_messages;

            protected           $_dsn;
            protected           $_con;

            protected           $_table;
            protected           $_schema;
            protected           $_default;

            protected           $_errmode;
            protected           $_fetch_mode;


            public function __construct()
            {
                $this->_messages = Messages::__getInstance();

                $this->_dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;

                $this->_table = Array();
                $this->_schema = Array();
                $this->_default = Array();

                $this->_errmode = PDO::ERRMODE_EXCEPTION;
                $this->_fetchmode = PDO::FETCH_OBJ;
            }

            public static function __getInstance()
            {
                if (is_null(self::$_instance))
                    self::$_instance = new self();

                return self::$_instance;
            }

            public function connect()
            {
                $_errmode = $this->_errmode;

                $this->_con = new PDO($this->_dsn, DB_USER, DB_PSWD);

                $this->_con->setAttribute(PDO::ATTR_ERRMODE, $this->_errmode);
                $this->_con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $this->_fetchmode);
            }

        /*-------------------------------------------------
         |
         |  createTable()
         |
         |  Create a stabpe fr the specified $_table
         |  schema name.
         |
         */
            public function createTable($_table)
            {
                if (! isset($this->_create[$_table])) {
                    $this->_messages->pushMessage('error', "createTable(): Schema $_table not defined");
                    return false;
                }

                $this->connect();

                $query = $this->_create[$_table];
                $con = $this->_con;

                //echo "$query<br>";die();
                try {
                    $con->query($query);
                } catch (PDOException $e) {
                    $this->_messages->pushMessage('error', $e->getMessage());
                    return false;
                }
                
                return true;
            }

        /*-------------------------------------------------
         |
         |  tableExists()
         |
         |  Returns true if the table exists, otherwise
         |  returns false.
         |
         */
            public function tableExists($_table)
            {
                $this->connect();
                
                $query = "SELECT 1 FROM $_table LIMIT 1";
                $con = $this->_con;
                
                try {
                    $con->query($query);
                } catch (PDOException $e) {
                    $this->_con = null;
                    return false;
                }

                $this->_con = null;
                return true;
            }

        /*-------------------------------------------------
         |
         |  validateSchema()
         |
         |  Validates the given schema.
         |
         */
            public function validateSchema($_table, $_schema)
            {
                $_create = "CREATE TABLE " . $_table . " (";
                $_first = true;

                foreach ($_schema as $_name=>$_params) {
                    if (! $_first) $_create .= ", " . $_name . " ";
                    else $_create .= $_name . " ";

                    if ($_first) $_first = false;

                    $_size = null;
                    $_unique = '';
                    $_primary = '';
                    $_auto = '';
                    $_required = '';
                    $_default = '';
                    $_type = '';

                    foreach ($_params as $_param) {
                        if (is_numeric($_param)) $_size = $_param;
                        else if ($_param == 'primary') $_primary = "PRIMARY KEY";
                        else if ($_param == 'unique') $_unique = "UNIQUE";
                        else if ($_param == "auto") $_auto = "AUTO_INCREMENT";
                        else if ($_param == "required") $_required = "NOT NULL";
                        else if ($_param == "number") $_type = "INT";
                        else if ($_param == "char") $_type = "VARCHAR";
                        else if ($_param == "text") $_type = "TEXT";
                        else if ($_param == "mediumtext") $_type = "MEDIUMTEXT";
                        else if ($_param == "timestamp") $_type = "TIMESTAMP";
                        else if ($_param == "NULL") {
                            $_default = "NULL";
                            $this->_default[$_table][$_name] = "NULL";
                        }
                        else {
                            $_default = "DEFAULT " . $_param;
                            $this->_default[$_table][$_name] = $_param;
                        }
                    }

                    if ($_type !== 'number' && $_size !== null)
                        $_create .= $_type . '(' . $_size . ') ';
                    else
                        $_create .= $_type . ' ';

                    if ($_auto) $_create .= $_auto . ' ';
                    if ($_unique) $_create .= $_unique . ' ';
                    if ($_primary) $_create .= $_primary . ' ';
                    if ($_required) $_create .= $_required . ' ';
                    if ($_default) $_create .= $_default . ' ';

                    if ($_type == "number" && $_size !== null)
                        $this->_default[$_table][$_name] = $_size;
                }

                $_create .= ")";
                $this->_create[$_table] = $_create;

                return $_schema;
            }

        /*-------------------------------------------------
         |
         |  schema()
         |
         |  Define a new schema.
         |
         */
            public function schema($_table, $_schema)
            {
                if (in_array($_table, $this->_table))
                    return false;

                $_valid = $this->validateSchema($_table, $_schema);
                if (! $_valid)
                    return false;

                array_push($this->_table, $_table);
                $this->_schema[$_table] = $_valid;
                
                return true;
            }

        /*-------------------------------------------------
         |
         |  getSQLValues()
         |
         |  Returns the VALUES(:param, :param) type query
         |  strings - this is used by the insert() and
         |  update() methods.
         |
         */
            public function getSQLValues(
                $_table, &$_data, &$_columns, &$_values
            ) 
            {
                $_first = true;

                $_columns = "";
                $_values = "";
                
                foreach ($this->_schema[$_table] as $_key=>$_value) {
                    if ($_first) {
                        $_columns = "$_key";
                        $_values = ":$_key";
                    }
                    else {
                        $_columns .= ", $_key";
                        $_values .= ", :$_key";
                    }

                    if ($_first) $_first = false;

                    if (in_array('required', $_value)) {
                        if (in_array('primary', $_value) >= 0) {
                            if (! 
                                isset($this->_default[$_table][$_key]) &&
                                in_array($_key, $_data) > 0
                            )
                            {
                                echo "|$_key| is unset?<br>";
                                if (in_array('number', $_value)) $_data[$_key] = "0";
                                else if (in_array('char', $_value)) $_data[$_key] = "";
                                else if (in_array('text', $_value)) $_data[$_key] = "";
                                else if (in_array('mediumText', $_value)) $_data[$_key] = "";
                                else if (in_array('timestamp', $_value)) $_data[$_key] = "CURRENT_TIMESTAMP";
                                else {
                                    $this->_messages->pushMessage('error', "insert(): Required option <b>$_key</b> has no default value");
                                    return false;
                                }
                            }
                            else {
                                if (! isset($_data[$_key])) {
                                    if (isset($this->_default[$_table][$_key]))
                                        $_data[$_key] = $this->_default[$_table][$_key];
                                    else
                                        $_data[$_key] = "0";
                                }
                            }
                        }
                    }
                    
                    if (! isset($_data[$_key])){
                        if (isset($this->_default[$_table][$_key]))
                            $_data[$_key] = $this->_default[$_table][$_key];
                        else
                            $_data[$_key] = "0";
                    }
                }
            }

        /*-------------------------------------------------
         |
         |  getSQLWhere()
         |
         |  Returns a WHERE SQL clause - this is used by
         |  methods that use a WHERE clause - where(),
         |  update() and delete().
         */
            public function getSQLWhere($_table, &$_match)
            {
                $_where = "";
                $_id = "";

                $_first = true;
                $_cond = "&&";

                foreach ($_match as $_key=>$_params) {
                    if ($_key == 'expr') {
                        if ($_params == 'and')
                            $_cond = "&&";
                        else if ($_params == "or")
                            $_cond = "||";

                        unset($_match[$_key]);
                        continue;
                    }

                    if (! isset($this->_schema[$_table][$_key])) {        
                        $this->_messages->pushMessage('error', "where(): Schema $_table [" . $_key . "] not defined");
                        return false;
                    }

                    if ($_first) {
                        $_first = false;
                        $_where .= "$_key = :$_key";
                    } else {
                        $_where .= " " . $_cond . " $_key = :$_key";
                    }
                }

                return $_where;
            }

        /*-------------------------------------------------
         |
         |  getSQLSet()
         |
         |  The update() method uses this to get the SQL
         |  for SET.
         |
         */
            public function getSQLSet($_values)
            {
                $_set = "";
                $_first = true;

                foreach ($_values as $_key=>$_value) {
                    if ($_first) {
                        $_first = false;
                        $_set = "$_key = :$_key ";
                    } else
                        $_set .= ", $_key = :$_key";
                }

                return $_set;
            }

        /*-------------------------------------------------
         |
         |  execQuery()
         |
         |  Executes the given query
         |
         */
            public function execQuery($_query, $_where)
            {
                $con = $this->_con;
                $stmt = $con->prepare($_query);

                try {
                    if ($_where)
                        $stmt->execute($_where);
                    else
                        $stmt->execute();
                } catch (PDOException $e) {
                    $this->_messages->pushMessage('error', $e->getMessage());
                    return false;
                }

                $this->_con = null;
                return $stmt;
            }

        /*-------------------------------------------------
         |
         |  insert()
         |
         |  Insert a new record using the given $_table
         |  schema.
         |
         */
            public function insert($_table, $_data)
            {
                if (! isset($this->_schema[$_table])) {
                    $this->_messages->pushMessage('error', "insert(): Schema $_table not defined");
                    return false;
                }

                $_columns = "";
                $_values = "";

                $this->getSQLValues($_table, $_data, $_columns, $_values);
                $this->connect();

                $query = "INSERT INTO $_table($_columns) VALUES($_values)";
                return $this->execQuery($query, $_data);
            }

        /*-------------------------------------------------
         |
         |  all()
         |
         |  Returns all records in the specified $_table
         |
         */
            public function all($_table)
            {
                $_results = Array();

                if (! isset($this->_schema[$_table])) {
                    $this->_messages->pushMessage('error', "all(): Schema $_table not defined");
                    return false;
                }

                $this->connect();

                $query = "SELECT * FROM $_table";   
                $stmt = $this->execQuery($query, false);

                return $stmt->fetchAll();
            }

        /*-------------------------------------------------
         |
         |  where()
         |
         |  Returns all records in the $_table that match
         |  the given $_match parameters.
         |
         */
            public function where($_table, $_match)
            {
                $_results = Array();
                $_where = "";

                if (! isset($this->_schema[$_table])) {
                    $this->_messages->pushMessage('error', "where(): Schema $_table not defined");
                    return false;
                }

                if ($_match == null)
                    return $this->all($_table);
                
                $_where = $this->getSQLWhere($_table, $_match);
                
                $this->connect();

                $query = "SELECT * FROM $_table WHERE $_where";
                $stmt = $this->execQuery($query, $_match);

                return $stmt->fetchAll();
            }

        /*-------------------------------------------------
         |
         |  update()
         |
         |  Update records in the specified $_table that
         |  match the given $_match parameters with the
         |  given $_data
         |
         */
            public function update($_table, $_data, $_match)
            {
                if (! isset($this->_schema[$_table])) {
                    $this->_messages->pushMessage('error', "update(): Schema $_table not defined");
                    return false;
                }

                $_set = $this->getSQLSet($_data);
                $_where = $this->getSQLWhere($_table, $_match);

                $this->connect();

                $query = "UPDATE $_table SET $_set WHERE $_where";

                $con = $this->_con;
                $stmt = $con->prepare($query);

                try {
                    $stmt->execute(array_merge($_data, $_match));
                } catch (PDOException $e) {
                    $this->_messages->pushMessage('error', $e->getMessage());
                    return false;
                }

                $this->_con = null;

                return true;
            }

        /*-------------------------------------------------
         |
         |  delete()
         |
         |  Delete any records in $_table that match the
         |  given $_match parameters.
         |
         */
            public function delete($_table, $_match)
            {
                if (! isset($this->_schema[$_table])) {
                    $this->_messages->pushMessage('error', "delete(): Schema $_table not defined");
                    return false;
                }
                
                $_where = $this->getSQLWhere($_table, $_match);

                $this->connect();

                $query = "DELETE FROM $_table WHERE $_where";
                return $this->execQuery($query, $_match);
            }

        }

