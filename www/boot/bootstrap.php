<?php

    chdir(PATH_ROOT);
    
    /*-----------------------------------------------------
     |
     |  mana/www/boot/bootstrap.php
     |
     */

  
    /*-----------------------------------------------------
     |
     |  First, include all of the core scripts.
     |
     */
        include(_translatePath("../core/include/utils.php"));
        include(_translatePath("../core/include/config.php"));
        include(_translatePath("../core/include/autoload.php"));
        include(_translatePath("../core/include/mail.php"));
        include(_translatePath("../core/include/csrf.php"));


    /*-----------------------------------------------------
     |
     |  The _loadConfig() function will load all of the
     |  files in mana/www/config/ - see:
     |
     |      mana/www/core/incllude/config.php
     |
     |  For more.
     |
     */
        _loadConfigs();

        
    /*-----------------------------------------------------
     |
     |  A bunch of global wrapper functions are created
     |  so that we can use things like the view engine
     |  more easily.
     |
     |  First - mana View functions...
     */
        function View($_path, $_data = null) {
            if ($_data === null)
                return App::__getInstance()->_mana->view($_path);
        }


    /*-----------------------------------------------------
     |
     |  Messages
     |
     */
        function allMessages() {
            return App::__getInstance()->_messages->getAll();
        }

        function getMessages($_type) {
            return App::__getInstance()->_messages->getMessages($_type);
        }

        function pushMessage($_type, $_message) {
            return App::__getInstance()->_messages->pushMessage($_type, $_message);
        }

        function popMessage($_type) {
            return App::__getInstance()->_messages->popMessage($_type);
        }

  
        $app = App::__getInstance();
        $app->init();

