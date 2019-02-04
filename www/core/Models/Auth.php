<?php


    /*-----------------------------------------------------
     |
     |  mana/www/core/Models/Auth.php
     |
     */


        _defifndef('AUTH_KEY', '_mana_auth');
        _defifndef('AUTH_ID', '_mana_auth_id');
        _defifndef('AUTH_USER', '_mana_auth_user');
        _defifndef('AUTH_GUEST', 'Guest');
        _defifndef('AUTH_STATUS', '_mana_auth_status');


        class Auth
        {

            private function __init()
            {
                if (! isset($_SESSION[AUTH_KEY]))
                    $_SESSION[AUTH_KEY] = false;
                if (! isset($_SESSION[AUTH_USER]))
                    $_SESSION[AUTH_USER] = AUTH_GUEST;
                if (! isset($_SESSION[AUTH_STATUS]))
                    $_SESSION[AUTH_STATUS] = -1;
            }

            public function auth()
            {
                self::__init();
                if ($_SESSION[AUTH_KEY]) {
                    return true;
                }

                return false;
            }

            public function guest()
            {
                self::__init();

                if ($_SESSION[AUTH_USER] == AUTH_GUEST)
                    return true;

                return false;
            }

            protected function loginUser(
                $id,
                $user,
                $status
            ) {
                self::__init();

                $_SESSION[AUTH_ID] = $id;
                $_SESSION[AUTH_USER] = $user;
                $_SESSION[AUTH_STATUS] = $status;
                $_SESSION[AUTH_KEY] = true;
            }

            protected function logoutUser() 
            {
                self::__init();

                $_SESSION[AUTH_ID] = -1;
                $_SESSION[AUTH_USER] = AUTH_GUEST;
                $_SESSION[AUTH_STATUS] = -1;
                $_SESSION[AUTH_KEY] = false;
            }

            public function id($d) {
                self::__init();

                if ($id == $_SESSION[AUTH_ID])
                    return true;

                return false;
            }

            public function status($status) {
                self::__init();

                if ($status == $_SESSION[AUTH_STATUS])
                    return true;

                return false;
            }

        }

