<?php


    /*-----------------------------------------------------
     |
     |  mana/www/core/include/csrf.php
     |
     */

        
        function _getCSRFToken() {
            if (! isset($_SESSION[CSRF_KEY])) {
                if (function_exists('mcrypt_create_iv')) {
                    $_SESSION[CSRF_KEY] = bin2hex(mcrypt_create_iv(CSRF_LEN, MCRYPT_DEV_URANDOM));
                } else {
                    $_SESSION[CSRF_KEY] = bin2hex(openssl_random_pseudo_bytes(32));
                }
            }

            return $_SESSION[CSRF_KEY];
        }

