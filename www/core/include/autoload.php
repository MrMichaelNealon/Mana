<?php


    /*-----------------------------------------------------
     |
     |  mana/www/core/include/autoload.php
     |
     |  Standard autoload routines.
     |
     |  The __autoload() will first call _loadModel()
     |  on a given $class_name. _loadModel() will first
     |  try to find the class in the CORE_MODELS_PATH
     |  and if not found will check MODELS_PATH instead.
     |
     |  If no model is found, __autoload() will then call
     |  the _loadController() function. Same routine, it
     |  will first look for a core controller in the
     |  CORE_CONTROLLERS_PATH directory, if not found
     |  then will check CONTROLLERS_PATH, if not found
     |  then __autoload() will bail with an error.
     |
     |  First - just a few definitiions...
     |
     */
        _defifndef('CORE_MODELS_PATH', _translatePath('../core/Models'));
        _defifndef('MODELS_PATH', _translatePath('../app'));

        _defifndef('CORE_CONTROLLERS_PATH', _translatePath('../core/Controllers'));
        _defifndef('CONTROLLERS_PATH', _translatePath('../app/Controllers'));
        

    /*-----------------------------------------------------
     |
     |  _loadModel()
     |
     |  __autoload() will call this first. _loadModel
     |  returns true if it loads either a core or user
     |  defined model.
     |
     |  If the model isn't found then false is returned 
     |  and __autoload() will look for a controller,
     |  instead.
     |
     */
        function _loadModel($class_name) {
            $path = _buildPath([CORE_MODELS_PATH, $class_name . '.php']);

            if (! is_file($path)) {
                $path = _buildPath([MODELS_PATH, $class_name . '.php']);
               
                if (! is_file($path))
                    return false;
            }

            require($path);

            return true;
        }


    /*-----------------------------------------------------
     |
     |  _loadController()
     |
     |  If __autoload()'s call to _loadModel() returns
     |  false then this is called to load the $class_name
     |  as a controller.
     |
     |  If the controller is found then true is returned,
     |  otherwise false is returned and __autoload will
     |  die with an error.
     |
     */
        function _loadController($class_name) {
            $path = _buildPath([CORE_CONTROLLERS_PATH, $class_name . '.php']);

            if (! is_file($path)) {
                $path = _buildPath([CONTROLLERS_PATH, $class_name . '.php']);
                
                if (! is_file($path))
                    return false;
            }

            require($path);

            return true;
        }


    /*-----------------------------------------------------
     |
     |  __autoload()
     |
     |  Textbook stuff. As explained above will first
     |  try to load $class_name as a model, if that fails
     |  will attempt to load a controller.
     |
     |  If both fail __autoload() dumps an error and 
     |  bails.
     |
     */
        function __autoload($class_name) {
            if (_loadModel($class_name))
                return true;
                
            if (_loadController($class_name))
                return true;
            
            die("__autoload(): Class $class_name not found!");
        }

    