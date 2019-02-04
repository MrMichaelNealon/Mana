<?php


    /*-----------------------------------------------------
     |
     |  mana/www/core/include/config.php
     |
     */


        _defifndef('CONFIG_PATH', _buildPath(['..', 'config']));
        _defifndef('CONFIG_EXT', '.config.php');


        function _isConfig($_config) {
            $_extlen = strlen(CONFIG_EXT);
            $_pathlen = strlen($_config);

            $_ext = substr($_config, ($_pathlen - $_extlen));

            if ($_ext == CONFIG_EXT)
                return true;
            
            return false;
        }

        function _loadconfig($_config)
        {
            if (substr($_config, 0, 1) == ".")
                return;
            if (! _isConfig($_config))
                return;
            
            $_path = _buildPath([
                CONFIG_PATH,
                $_config
            ]);

            include($_path);
        }

        function _loadConfigs()
        {
            if (! is_dir(CONFIG_PATH))
                die("_loadConfigs(): cannot find config path <b>" . CONFIG_PATH . "</b>");
            
            $_dir = opendir(CONFIG_PATH);
            
            while ($_config = readdir($_dir)) {
                _loadConfig($_config);
            }

            closedir($_dir);
        }

