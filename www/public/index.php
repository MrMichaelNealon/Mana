<?php


    /*-----------------------------------------------------
     |
     |  mana/www/public/index.php
     |
     */

        session_start();


        define('PATH_SEP', DIRECTORY_SEPARATOR);
        define('PATH_ROOT', getcwd());

        chdir(PATH_ROOT);

        include(".." . PATH_SEP . "core" . PATH_SEP ."include" . PATH_SEP . "utils.php");

        include(_translatePath("../boot/bootstrap.php"));
        
