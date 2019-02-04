<?php


    /*-----------------------------------------------------
     |
     |  mana/ww/core/include/mail.php
     |
     */


        _defifndef('VERIFY_URL_CHARS', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        _defifndef('VERIFY_URL_LEN', 32);


        function _getVerifyURL($user)
        {
            $_rndstr = "";

            while (strlen($_rndstr) < VERIFY_URL_LEN) {
                $_rnd = rand(0, strlen(VERIFY_URL_CHARS));
                $_rndstr .= substr(VERIFY_URL_CHARS, $_rnd, 1);
            }

            return "http://" . $_SERVER['HTTP_HOST'] . "/verification/$user/$_rndstr";
        }


        function _PHPMailerSend(
            $to,
            $from,
            $from_name,
            $subject,
            $body,
            $user
        ) {
            $mail = new PHPMailer();

            $mail->CharSet = "UTF-8";
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->Host = SMTP_HOST;
            $mail->Port = SMTP_PORT;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PSWD;
            $mail->SMTPSecure = SMTP_TYPE;
            $mail->setFrom($from, $from_name);
            $mail->Subject = $subject;
            $mail->AltBody = $body;
            $mail->Body = $body;
            $mail->isHTML();
            $mail->addAddress($to, $user);
            
            return $mail->send();
        }


        function _sendVerifyEmail($verify, $user, $email)
        {
            $to = $email;
            $from = SMTP_SENDER;
            $from_name = SMTP_SENDER_NAME;

            $subject = "$user -- please verify your " . APP_TITLE . " account!";

            $body = '
                Hi, ' . $user . '!
                <br><br>
                Please verify your account by clicking the link
                below:
                <br><br>
                    <form action="' . $verify . '" method="POST">
                        <input type="submit" value="Click here to verify">
                    </form>
                <br><br>
                Didn&#39;t create an account at ' . APP_TITLE . '?
                <br><br>
                You can safely ignore this email or let us know
                by emailing us at:
                <br><br>
                    <a href="mailto: ' . SMTP_SENDER . '">
                        ' . SMTP_SENDER . '
                    </a>
                <br>
            ';

            return _phpMailerSend($to, $from, $from_name, $subject, $body, $user);
        }

