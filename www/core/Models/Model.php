<?php


    /*-----------------------------------------------------
     |
     |  mana/www/core/Models/Model.php
     |
     */


        class Model
        {

            protected   static  $orm;

            protected   static  $table;
            protected   static  $schema;


            public static function __callStatic($_method, $_args)
            {
                if (is_null(self::$orm))
                    self::$orm = ORM::__getInstance();
                
                $_method = "_" . $_method;
                $_class = __CLASS__;

                if (! is_null(self::$table)) {
                    if (! is_null(self::$schema)) {                            self::$orm->schema(self::$table, self::$schema);
                        self::$orm->schema(self::$table, self::$schema);

                        if (! self::$orm->tableExists(self::$table)) {
                            if (! self::$orm->createTable(self::$table))
                                die("Error creating table $table<br>");
                        }
                    }
                }

                if (method_exists($_class, $_method))
                    return call_user_func(array($_class, $_method), $_args);
            }


            protected static function _up()
            {
                return true;
            }

        }

