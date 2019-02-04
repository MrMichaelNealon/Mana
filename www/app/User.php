<?php


    /*-----------------------------------------------------
     |
     |  mana/www/app/User.php
     |
     */


        class User extends Model
        {

            public function init() {
                parent::$table = "users";

                parent::$schema = [
                    'id' => [ 'number', 'auto', 'primary' ],
                    'user' => [ 'char', 64, 'required' ],
                    'email' => [ 'char', 255, 'required' ],
                    'password' => [ 'char', 255, 'required' ],
                    'status' => [ 'number', 'required' ],
                    'fails' => [ 'number', 'required' ],
                    'created_at' => [ 'timestamp', 'required', 'CURRENT_TIMESTAMP' ],
                    'verified_at' => [ 'timestamp', 'required', 'NULL' ],
                    'url' => [ 'char', 120 ]
                ];

                self::up();
            }

        }

