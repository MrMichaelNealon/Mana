<?php


    /*-----------------------------------------------------
     |
     |  mana/www/core/Controllers/View.php
     |
     |  The Mana templating engine.
     |
     */


        _defifndef('PATH_SEP', DIRECTORY_SEPARATOR);

    
    /*-----------------------------------------------------
     |
     |  some generic values.
     |
     */
        _defifndef('MANA_PATH', _buildPath(['..', 'views']));
        _defifndef('MANA_PATH_SEP', '.');
        _defifndef('MANA_EXT', '.mana.php');


    /*-----------------------------------------------------
     |
     |  A directive can be inserted into a template, the
     |  directive begins with the MANA_DIR sequence
     |  followed by one of the directive ID's, example:
     |
     |      @partial some.file
     |
     */
        _defifndef('MANA_DIR', '@');

        _defifndef('MANA_PARTIAL', 'partial');
        _defifndef('MANA_SECTION', 'section');
        _defifndef('MANA_ENDSECTION', 'endsection');
        _defifndef('MANA_LAYOUT', 'layout');
        _defifndef('MANA_CSRF', 'csrf');


        class Mana
        {

        /*-------------------------------------------------
         |
         |  The singleton instance is returned by the
         |  __getInstance() method.
         |
         */
            protected   static  $_instance;

            public              $_template;
            public              $_sections;

            public              $_output;

            public              $_data;


            public function __construct()
            {
                $this->_template = Array();
                $this->_sections = Array();

                $this->_output = "";
                $this->_data = null;
            }


            public static function __getInstance()
            {
                if (is_null(self::$_instance))
                    self::$_instance = new self();
                return self::$_instance;
            }


        /*-------------------------------------------------
         |
         |  getTemplatePath()
         |
         |  Takes the template $_path as a parameter and
         |  returns the complete relative path to the
         |  template.
         |
         */
            public function getTemplatePath($_path)
            {
                return _buildPath([
                    MANA_PATH,
                    str_replace(MANA_PATH_SEP, PATH_SEP, $_path) . MANA_EXT
                ]);
            }


        /*-------------------------------------------------
         |
         |  parseTemplate()
         |
         |  Loads the specified template file from $_path
         |  and parses the template into an array whic
         |  is returned.
         |
         */
            public function parseTemplate($_path)
            {
                if (! is_file($_path))
                    die("Mana::parseTemplate(): file <b>$_path</b> not found");

                $_fp = fopen($_path, "r");
                $_lines = Array();
                
                while ($_line = fgets($_fp)) {
                    array_push($_lines, $_line);
                }

                return $_lines;
            }


        /*-------------------------------------------------
         |
         |  insertLines()
         |
         |  Inserts all of the elements in the $_template
         |  array into the $this->_template array at
         |  index $_line_no.
         |
         |  In other words is inserts one array into
         |  another at the given index.
         |
         */
            public function insertLines($_line_no, $_template) {
                $_output = Array();

                foreach ($this->_template as $_key=>$_line) {
                    if ($_key == $_line_no) {
                        foreach ($_template as $line)
                            array_push($_output, $line);
                    }
                    else
                        array_push($_output, $_line);
                }

                $this->_template = $_output;
            }


        /*-------------------------------------------------
         |
         |  insertManaLayout()
         |
         |  Really all this does is expand a template
         |  path or filename to the contents of the
         |  template - this function works for both
         |  layout and partial directives:
         |
         |      @layout some.template
         |
         |  Would essentially be replaced with the contents
         |  of some.template (assuming it exists)
         |
         */
            public function insertManaLayout(
                $_path,
                $_line_no,
                $_params
            ) {
                if (count($_params) < 2 || empty(trim($_params[1])))
                    die("Mana::getManaLayout(): in file $_path, line $_line_no -- " . MANA_SECTION . " directive requires a parameter");
                
                $_layout = _buildPath([
                    MANA_PATH,
                    str_replace(MANA_PATH_SEP, PATH_SEP, $_params[1]) . MANA_EXT
                ]);

                $_template = $this->parseTemplate($_layout);
                
                $this->insertLines($_line_no, $_template);
            }

            public function insertManaPartial(
                $_path,
                $_line_no,
                $_params
            ) {
                if (count($_params) < 2 || empty(trim($_params[1])))
                    die("Mana::getManaPartial(): in file $_path, line $_line_no -- " . MANA_PARTIAL . " directive requires a parameter");
                
                $_layout = _buildPath([
                    MANA_PATH,
                    str_replace(MANA_PATH_SEP, PATH_SEP, $_params[1]) . MANA_EXT
                ]);

                $_template = $this->parseTemplate($_layout);
                
                $this->insertLines($_line_no, $_template);
            }


        /*-------------------------------------------------
         |
         |  getManaSection()
         |
         |  Section directives are removed from the main
         |  $this->_template and collected in the
         |  $this->_sections associative array.
         |
         |  Example:
         |
         |      @section sectionName
         |          <h3>A section</h3>
         |      @endsection
         |
         |  All of he code in the above section would be
         |  stored in:
         |
         |      $this->_sections['sectionName']
         |
         */
            public function getManaSection($_path, $_line_no, $_params) {
                if (count($_params) < 2 || empty(trim($_params[1])))
                    die("Mana::getManaSection(): in file $_path, line $_line_no -- the " . MANA_SECTION . " directive requires a parameter");
                
                $_section = $_params[1];

                if (isset($this->_sections[$_section]))
                    die("Mana::getManaSection(): in file $_path, line $_line_no -- the " . MANA_SECTION . " <b>$_section</b> already exists");

                $this->_sections[$_section] = Array();
                $this->_template[$_line_no++] = '';

                while ($_line_no < count($this->_template)) {
                    $_line = trim($this->_template[$_line_no]);
                    $this->_template[$_line_no] = '';

                    if ($_line == MANA_DIR . MANA_ENDSECTION)
                        break;

                    array_push($this->_sections[$_section], $_line);
                    $_line_no++;
                }
            }


        /*-------------------------------------------------
         |
         |  expandSections()
         |
         |  When we define a section:
         |
         |      @section mySection
         |          <p>This is my <b>section</b></p>
         |      @endsection
         |
         |  We essentially create a new directive with that
         |  name, so we can simply do:
         |
         |      @mySection
         |
         |  And $this->_sections['mySection'] will be
         |  inserted into the main template at the
         |  given point ($_line_no).
         |
         */
            public function expandSection($_path, $_line_no, $_params) {
                if (empty(trim($_params[0])))
                    die("Mana::expandSection(): in file $_path, lne $_line_no -- Directive key " . MANA_KEY . " should be followed by a " . MANA_SECTION . " id");
        
                $_section = $_params[0];

                if (! isset($this->_sections[$_section]))
                    die("Mana::expandSection(): in file $_path, line $_line_no -- reference to unknown " . MANA_SECTION . " <b>$_section</b>");
            
                $this->insertLines($_line_no, $this->_sections[$_section]);

                return true;
            }


        /*-------------------------------------------------
         |
         |  getCSRFToken()
         |
         |  Expands a @csrf directive to a form input
         |  field containing a generated token. The 
         |  post() method will check and validate the
         |  token - see files:
         |
         |      mana/www/core/include/csrf.php
         |      mana/www/config/csrf.config.php
         |
         */
            public function getCSRFToken($_line_no) {
                $_token = _getCSRFToken();
                $_html = "<input class='" . CSRF_CLASS . "' name='" . CSRF_KEY . "' type='text' id='" . CSRF_KEY . "' value='" . $_token . "'>";

                $this->_template[$_line_no] = $_html;
                
                return true;
            }


        /*-------------------------------------------------
         |
         |  processTemplate()
         |
         |  Processes the main template ($this->_template)
         |  and parses all of the directives.
         |
         |  If $_expand is true, then the routine will
         |  include looking for @layout directives.
         |
         |  You should call this twice - the first time
         |  with $_expand set to false, because you don't
         |  yet want to expand and sections since they may
         |  not have been processed and stored yet.
         |
         |  This method can be called a second time with
         |  the $_expand parameter set to true and all
         |  section directives will then be expanded.
         |
         */
            public function processTemplate($_path, $_expand)
            {
                $_continuez = true;

                while ($_continuez) {
                    $_continuez = false;

                    foreach ($this->_template as $_line_no=>$_line) {
                        $_line = trim($_line);
                        $_actual = ($_line_no + 1);

                        if (substr($_line, 0, 1) == MANA_DIR) {
                            $_params = preg_split('/\\s/', substr($_line, 1), -1, PREG_SPLIT_NO_EMPTY);

                            if (count($_params) <= 0) continue;
                            
                            if ($_params[0] == MANA_LAYOUT) {
                                $this->insertManaLayout($_path, $_line_no, $_params);
                                $_continuez = true;
                                break;
                            }
                            else if ($_params[0] == MANA_PARTIAL) {
                                $this->insertManaPartial($_path, $_line_no, $_params);
                                $_continuez = true;
                                break;
                            }
                            else if ($_params[0] == MANA_SECTION) {
                                if (! $_expand) {
                                $this->getManaSection($_path, $_line_no, $_params);
                                $_continuez = true;
                                break;
                                }
                            }
                            else if ($_params[0] == MANA_CSRF) {
                                $this->getCSRFToken($_line_no);
                            //    $_continuez = true;
                            //    break;
                            }
                            else {
                                if ($_expand) {
                                    $this->expandSection($_path, $_line_no, $_params);
                                    $_continuez = true;
                                    break;
                                }
                            }    
                        }
                    }
                }
            }
        

        /*-------------------------------------------------
         |
         |  view()
         |
         |  The purpose of the view method is to load and
         |  process the routed view file and produce the
         |  output php code for execution.
         |
         |  The render() method can be used to grab the
         |  output and process it generating the output
         |  for the client response.
         |
         */
            public function view($_path, $_data = null)
            {
                $_path = $this->getTemplatePath($_path);

            //    if (isset($_data) && $_data !== null)
                    $this->_data = $_data;
            //    else
            //        $this->_data = null;

                $this->_output = "";

                $this->_template = $this->parseTemplate($_path);

                $this->processTemplate($_path, false);
                $this->processTemplate($_path, true);

                foreach ($this->_template as $_line_no=>$_line) {
                    if (empty(trim($_line)))
                        continue;
                    $this->_output .= $_line . ' ';
                }

                return $this->_output;
            }


            public function render($_path) {
                $this->view($_path);
                return eval("?>" . $this->_output);
            }

        }

