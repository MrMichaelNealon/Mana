<?php


    /*-----------------------------------------------------
     |
     |  mana/www/core/Controllers/App.php
     |
     */


        class App
        {

            protected   static  $_instance;

            protected           $_messages;
            protected           $_orm;
            public              $_mana;

            protected           $_status;


            public function __construct()
            {
                $this->_messages = Messages::__getInstance();
                $this->_orm = ORM::__getInstance();
                $this->_mana = Mana::__getInstance();

                $this->_status = 200;
            }

            public static function __getInstance()
            {
                if (is_null(self::$_instance))
                    self::$_instance = new self();
                return self::$_instance;
            }

            public function init()
            {
                $_routes = _buildPath([
                    '..', 'routes', 'routes.php'
                ]);

                require($_routes);

                if (! Route::hasRouted()) {
                    if (defined('PAGE_NOT_FOUND')) {
                        if (is_file(PAGE_NOT_FOUND))
                        die(eval("http_response_code(404);?>" . file_get_contents(PAGE_NOT_FOUND)));
                    } else
                        die(eval("http_response_code(404);?><h3>404 not found</h3>"));
                } else {
                    $_status = $this->_status;
                    $_output = Route::getOutput();

                    echo eval("http_response_code($_status);?>" . $_output);
                }
            }

        }

