<?php


    /*-----------------------------------------------------
     |
     |  mana/www/config/csrf.config.php
     |
     |  This is stuff to prevent XSS attacks - you really
     |  shouldn't need to change anything in here.
     |
     */

    
        _defifndef('CSRF_KEY', 'mana_csrf_token');
        _defifndef('CSRF_LEN', 32);
        _defifndef('CSRF_CLASS', 'csrf_input');
        _defifndef('CSRF_VALID', 'csrf_valid');

