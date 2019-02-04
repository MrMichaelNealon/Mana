<?php


    /*-----------------------------------------------------
     |
     |  mana/www/core/Controllers/Route.php
     |
     */


        class Route
        {

        /*-------------------------------------------------
         |
         |  Request parameters.
         |
         */
            protected   static  $_url = '/';
            protected   static  $_request = 'GET';
            protected   static  $_route = '';
            protected   static  $_params = Array();
            protected   static  $_body = Array();

        /*-------------------------------------------------
         |
         |  The controller, when called - will return the
         |  view data which is stored here.
         |
         |  It can later be returned by the getOutput()
         |  method.
         |
         */
            protected   static  $_output = false;


        /*-------------------------------------------------
         |
         |  getURLParams()
         |
         |  Sets and parses the request URL
         |
         */
            protected function getURLParams() {
                $url = "/";

                if (isset($_GET['url']))
                    $url = $_GET['url'];                
                if ($url == "/")
                    return Array('/');
                
                self::$_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

                return preg_split('/\//', $url, -1, PREG_SPLIT_NO_EMPTY);
            }

        /*-------------------------------------------------
         |
         |  variableURLParam()
         |
         |  If the given $_param string is a {variable}
         |  url parameter, the { and } are stripped and
         |  the parameter id returned.
         |
         |  If $_param is not a {variable} parameters then
         |  false is returned.
         |
         */
            protected function variableURLParam(&$_param) {
                if (
                    substr($_param, 0, 1) == '{' &&
                    substr($_param, (strlen($_param) - 1), 1) == '}'
                ) {
                    $_param = substr($_param, 1, (strlen($_param) - 2));
                    return true;
                }

                return false;
            }

        /*-------------------------------------------------
         |
         |  variableRoute()
         |
         |  Compares the given route againse the request
         |  URL. If there's a match then $_action will be
         |  called.
         |
         |  $_action can be either a callable, anonymous
         |  function or it can be a 'controller:method'
         |  string.
         |
         |  Any {variable} URL parameters that are found
         |  will be keyed to the $_params array - example,
         |  if we have a route like:
         |
         |      '/one/{two}/{three}
         |
         |  Then there are 2 variable parameters, so if
         |  the URL points to /one/ten/eleven then $_params
         |  will be:
         |
         |      $_params['two'] = "ten"
         |      $_params['three'] = "eleven"
         |
         */
            protected function validateRoute($_route, &$_params)
            {
                $_url_params = self::getURLParams();
                $_route_params = Array('/');

                if ($_route != '/')
                    $_route_params = preg_split('/\//', $_route, -1, PREG_SPLIT_NO_EMPTY);

                if (count($_route_params) != count($_url_params))
                    return false;

                foreach ($_route_params as $_index=>$_param) {
                    if (self::variableURLParam($_param)) {
                        $_params[$_param] = $_url_params[$_index];
                    }
                    else {
                        if ($_param != $_url_params[$_index])
                            return false;
                    }
                }

                return true;
            }

        /*-------------------------------------------------
         |
         |  execController()
         |
         |  Execute a 'controller:method', $_params will
         |  be passed to the method.
         |
         */
            protected function execController($_action, $_params)
            {
                $_action = preg_split('/:/', $_action, -1, PREG_SPLIT_NO_EMPTY);

                if (
                    count($_action) < 0 ||
                    (empty(trim($_action[0])) || empty(trim($_action[1])))
                )
                    die("Route::execController(): marformed action <b>$_action</b><br>");

                $_controller = $_action[0];
                $_method = $_action[1];

                self::$_output = $_controller::$_method($_params);
            }

        /*-------------------------------------------------
         |
         |  serveRoute()
         |
         |  When a matching route is found by get() or
         |  post(), the serveRoute() method is called.
         |
         |  This sets the self::$_route which identifies
         |  that a route has been matched and serve.
         |
         |  If all routes run and no match is found then
         |  this will be unset and the hasRouted() method
         |  will return false (404)
         |
         */
            protected function serveRoute($_route, $_action, $_params)
            {
                self::$_request = $_SERVER['REQUEST_METHOD'];
                self::$_route = $_route;
                self::$_params = $_params;

                if (is_callable($_action))
                    self::$_output = call_user_func_array($_action, array($_params));
                else
                    return self::execController($_action, $_params);
            }

        /*-------------------------------------------------
         |
         |  get()
         |
         */
            public function get($_route, $_action)
            {
                if ($_SERVER['REQUEST_METHOD'] !== 'GET')
                    return;

                $_params = Array();

                if (self::validateRoute($_route, $_params))
                    return self::serveRoute($_route, $_action, $_params);
            }

        /*-------------------------------------------------
         |
         |  post()
         |
         */
            public function post($_route, $_action)
            {
                if ($_SERVER['REQUEST_METHOD'] !== 'POST')
                    return;

                $_params = Array();

                if (isset($_POST))
                    self::$_body = $_POST;

            /*---------------------------------------------
             |
             |  There might be a CSRF token...
             |
             */
                if (isset($_SESSION[CSRF_KEY])) {
                    $_SESSION[CSRF_VALID] = true;
                    
                    if (! isset(self::$_body[CSRF_KEY])) {
                    /*  Any form should submit a field named
                     |  CSRF_KEY containing the matching token.
                     */
                        $_SESSION[CSRF_VALID] = false;
                        Messages::__getInstance()->pushMessage('error', 'CSRF Token required');
                    }

                    if ($_SESSION[CSRF_KEY] != self::$_body[CSRF_KEY]) {
                        $_SESSION[CSRF_VALID] = false;
                        Messages::__getInstance()->pushMessage('error', 'CSRF Token mismatch');
                    }

                    unset($_SESSION[CSRF_KEY]);
                }

                if (self::validateRoute($_route, $_params)) {
                    return self::serveRoute($_route, $_action, $_params);
                }

            }

        /*-------------------------------------------------
         |
         |  hasRouted()
         |
         |  Returns true if a route has been matched and
         |  served, otherwise returns false.
         |
         */
            public function hasRouted() {
                if (self::$_output !== false)
                    return true;

                return false;
            }

        /*-------------------------------------------------
         |
         |  getOutput()
         |
         |  If hasRouted() returns true then this should
         |  contain the view data returned by the
         |  controller.
         |
         */
            public function getOutput() {
                return self::$_output;
            }

        /*-------------------------------------------------
         |
         |  getRequestInfo()
         |
         */
            public function getRequest() {
                return new Request(
                    self::$_url,
                    self::$_request,
                    self::$_route,
                    self::$_params,
                    self::$_body
                );
            }

        }

