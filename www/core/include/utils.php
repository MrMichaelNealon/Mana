<?php


    /*-----------------------------------------------------
     |
     |  mana/www/core/include/utils.php
     |
     */

     
        function _defifndef($key, $value) {
            if (! defined($key))
                define($key, $value);
        }


        function _translatePath($_path) {
            return str_replace('/', PATH_SEP, $_path);
        }

        
        function _buildPath($_array) {
            $_path;

            foreach ($_array as $_index=>$_value) {
                $_value = _translatePath($_value);

                if (! $_index)
                    $_path = rtrim($_value, PATH_SEP);
                else if (($_index + 1) >= count($_array))
                    $_path .= PATH_SEP . ltrim($_value, PATH_SEP);
                else
                    $_path .= PATH_SEP . trim($_value, PATH_SEP);
            }

            return $_path;
        }
