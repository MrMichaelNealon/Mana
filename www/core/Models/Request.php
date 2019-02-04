<?php


    /*-----------------------------------------------------
     |
     |  mana/www/core/Models/Request.php
     |
     */


        class Request
        {

            public    $url;
            public    $request;
            public    $route;
            public    $params;
            public    $body;

            public function __construct(
                $url,
                $request,
                $route,
                $params,
                $body
            ) {
                $this->url = $url;
                $this->request = $request;
                $this->route = $route;
                $this->params = $params;
                $this->body = $body;
            }

        }

