<?php


    /*-----------------------------------------------------
     |
     |  mana/www/app/Controllers/authController.php
     |
     */


        class authController extends Controller
        {

            public function errors()
            {
                $err = Messages::__getInstance()->getMessages('error');
                if (! is_array($err) || ! count($err))
                    return false;
                foreach ($err as $e) {
                    echo "Error: <b>$e</b><br>";
                }
                return true;
            }

            protected function createUser(
                $user,
                $email,
                $password,
                $confirm
            )
            {
                $_verifyURL = _getVerifyURL($user);

                _sendVerifyEmail($_verifyURL, $user, $email);
                
                ORM::__getInstance()->insert('users', [
                    'user' => $user,
                    'email' => $email,
                    'password' => $password,
                    'url' => $_verifyURL
                ]);

                return true;
            }

            public function register()
            {
                if (self::errors())
                    die();
                    
                if (isset($_SESSION[CSRF_VALID]) && ! $_SESSION[CSRF_VALID]) {
                    $_SESSION[SESSION_ERROR] = "CSRF FAIL!!!<br>";
                    header("Location: /register");
                    exit;
                }

                $body = Route::getRequest()->body;

                $user = $body['user'];
                $email = $body['email'];
                $password = $body['password'];
                $confirm = $body['confirm'];

                User::init();

                $results = ORM::__getInstance()->where('users', [
                    'user' => $user,
                    'expr' => 'or',
                    'email' => $email
                ]);

                $user_exists = false;

                foreach ($results as $result) {
                    if ($result->user == $user || $result->email == $email)
                        $user_exists = true;
                };
            
                if (! $user_exists) {
                    $_SESSION[SESSION_NOTIFY] = 'Please check your email and verify your account';
                    Messages::__getInstance()->pushMessage('notify', 'Please check your emails and validate your account');
                        
                    if ($password != $confirm) {
                        Messages::__getInstance()->pushMessage('error', 'Password and confirm password did not match!');
                        return false;
                    }

                    unset($_SESSION[CSRF_KEY]);
                  
                    $options = [ 'cost' => 12 ];
                    $password = password_hash($password, PASSWORD_BCRYPT, $options);
                    
                    self::createUser($user, $email, $password, $confirm);

                    header('Location: /login');
                }
                else {
                    $_SESSION[SESSION_ERROR] = 'Username or email already registered!';
                    Messages::__getInstance()->pushMessage('error', 'Username or email already registered!');    
                    header('Location: /register');
                }

                exit();
            }


            public function validateUser(
                $user,
                $password,
                &$id,
                &$status
            ) {
                User::init();

                $results = ORM::__getInstance()->where('users', [
                    'user' => $user
                ]);

                $user_verified = false;
                $user_confirm = false;

                $_id = -1;
                $status = 1;

                foreach ($results as $result) {
                    if ($result->user == $user) {
                        if (password_verify($password, $result->password)) {
                            if (! empty(trim($result->url))) {
                                $_SESSION[SESSION_NOTIFY] = 'Please check your email and verify your account';
                                $user_confirm = true;
                            }
                            else
                                $user_verified = true;
                            
                            $status = $result->status;
                            
                            if ($user_verified) {
                                $_id = $result->id;
                                break;
                            }
                        }
                    }
                }

                return $user_verified;
            }


            public function login()
            {
                if (self::errors())
                    die();

                if (isset($_SESSION[CSRF_VALID]) && ! $_SESSION[CSRF_VALID])
                    return "CSRF FAIL!!!<br>";

                $body = Route::getRequest()->body;

                $user = $body['user'];
                $password = $body['password'];

                User::init();

                $results = ORM::__getInstance()->where('users', [
                    'user' => $user
                ]);

                $user_verified = false;
                $user_confirm = false;

                $_id = -1;
                $status = 1;

                foreach ($results as $result) {
                    if ($result->user == $user) {
                        if (password_verify($password, $result->password)) {
                            if (! empty(trim($result->url))) {
                                $_SESSION[SESSION_NOTIFY] = 'Please check your email and verify your account';
                                $user_confirm = true;
                            }
                            else
                                $user_verified = true;
                            
                            $status = $result->status;
                            
                            if ($user_verified) {
                                $_id = $result->id;
                                break;
                            }
                        }
                    }
                }

                if (! $user_verified) {
                    if (! $user_confirm)    
                    $_SESSION[SESSION_NOTIFY] = 'Login failure';
                    header('Location: /login');
                } else {
                    $_SESSION[SESSION_NOTIFY] = 'Login success';
                    Auth::loginUser(
                        $_id,
                        $user,
                        $status
                    );
                    header('Location: /dashboard');
                }

                unset($_SESSION[CSRF_KEY]);

                exit;
            }

            public function changePassword() {
                if (self::errors())
                    die();

                if (isset($_SESSION[CSRF_VALID]) && ! $_SESSION[CSRF_VALID])
                    return "CSRF FAIL!!!<br>";

                $body = Route::getRequest()->body;

                $user = $body['user'];
                $password = $body['password'];
                $newpassword = $body['newpassword'];
                $confirmpassword = $body['confirmpassword'];

                $id = 0;
                $status = 0;

                $user_verified = self::validateUser(
                    $user,
                    $password,
                    $id,
                    $status
                );

                if (! $user_verified) {
                    $_SESSION[SESSION_NOTIFY] = 'Error!';
                    header('Location: /changepassword');                    
                } else {
                    if (empty($newpassword) || ($newpassword != $confirmpassword)) {
                        $_SESSION[SESSION_NOTIFY] = 'Error!';
                        header('Location: /changepassword');
                    } else { 
                        $options = [ 'cost' => 12 ];
                        $_password = password_hash($newpassword, PASSWORD_BCRYPT, $options);
                        
                        ORM::__getInstance()->update('users', [
                            'password' => $_password
                        ], [
                            'user' => $user
                        ]);

                        $_SESSION[SESSION_NOTIFY] = 'Success!';
                        header('Location: /changepassword');
                    }
                }

                unset($_SESSION[CSRF_KEY]);

                exit;
            }

            public function logout() {
                Auth::logoutUser();
                header('Location: /');
                exit;
            }

            public function verifyEmail($params)
            {
                $body = $params;
                $user = $body['user'];
                $url = $body['url'];

                User::init();

                $results = ORM::__getInstance()->where('users', [
                    'user' => $user
                ]);

                $_url = "http://" . $_SERVER['HTTP_HOST'] . "/verification/$user/$url";
                $_verified = false;

                foreach ($results as $result) {
                    if ($result->user == $user && $result->url == $_url) {
                        $_verified = true;
                        break;
                    }
                }

                
                if (! $_verified) {
                    $_SESSION[SESSION_NOTIFY] = 'Email verification error';
                    header('Location: /');
                } else {
                    $_SESSION[SESSION_NOTIFY] = 'Your account has been verified, please log in';
                    ORM::__getInstance()->update('users', [
                        'verified_at' => time(),
                        'url' => ''
                    ], [
                        'user' => $user
                    ]);

                    header('Location: /login');
                }

                exit;
            }

        }

