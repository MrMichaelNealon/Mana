<?php


    /*-----------------------------------------------------
     |
     |  mana/www/config/smtp.config.php
     |
     |  Sendmail - edit this to point to your SMTP
     |  server, user credentials, etc.
     |
     */

     
        _defifndef('SMTP_HOST', 'smtp.gmail.com');
        _defifndef('SMTP_PORT', '587');
        _defifndef('SMTP_USER', '@');
        _defifndef('SMTP_PSWD', '!');
        _defifndef('SMTP_TYPE', 'tls');


    //  You can change this to anything you like
    //
        _defifndef('SMTP_SENDER', '');

    //  APP_TITLE is set in:
    //
    //      mana/www/config/app.config.php
    //
    //  This is basically the small signature pasted at the
    //  end of any outgoing mail.
    //
        _defifndef('SMTP_SENDER_NAME', "The " . APP_TITLE . " team");

