<?php


    /*-----------------------------------------------------
     |
     |  mana/www/core/Models/Message.php
     |
     |  Message is a very simple class, all it really does
     |  is store an array of messages.
     |
     |  See mana/doc/Messages.txt for more detailed info.
     |
     |  M. Nealon, 2019.
     */


    /*-----------------------------------------------------
     |
     |  These flags can be set via Message setFlags()
     |  method.
     */
        _defifndef('MESSAGE_REPORT_DIE', 0b0001);


        class Messages
        {

        /*-------------------------------------------------
         |
         |  This is a singleton class - the instance is
         |  returned by the __getInstance() method.
         |
         */
            protected   static  $_instance;
            
            protected   $_messages;

        /*-------------------------------------------------
         | 
         |  $_report can be set to mark a specific message
         |  type as an error class message. What does this
         |  mean?
         |
         |  See mana/doc/Messages.txt for more detailed
         |  info or take a look at the setReportType()
         |  and popMessage() methods.
         |
         */
            protected   $_report;
            protected   $_flags;


        /*-------------------------------------------------
         |
         |  __construct()
         |
         |  Self explanitory, really. This is invoked
         |  when the __getInstance() method creates an
         |  instance of this class.
         |
         */
            public function __construct()
            {
                $this->_messages = Array();

                if (isset($_SESSION[SESSION_NOTIFY])) {
                    $this->_messages[SESSION_NOTIFY] = preg_split('/;/', $_SESSION[SESSION_NOTIFY], -1, PREG_SPLIT_NO_EMPTY);
                    unset($_SESSION[SESSION_NOTIFY]);
                }
                if (isset($_SESSION[SESSION_ERROR])) {
                    $this->_messages[SESSION_ERROR] = preg_split('/;/', $_SESSION[SESSION_ERROR], -1, PREG_SPLIT_NO_EMPTY);
                    unset($_SESSION[SESSION_ERROR]);
                }
                
                $this->_report = null;
                $this->_flags = 0;
            }


        /*-------------------------------------------------
         |
         |  __getInstance()
         |
         |  Instantiates the class if necessary, always
         |  returns the instance.
         |
         */
            public static function __getInstance()
            {
                if (is_null(self::$_instance))
                    self::$_instance = new self();

                return self::$_instance;
            }


        /*-------------------------------------------------
         |
         |  setReportType()
         |
         |  Allows you to set the $_report type, basically
         |  we set this to a particular message type if we
         |  want to automatically report that error when
         |  it is pushed.
         |
         |  So we might set the report type to "error" so
         |  that any time we:
         |
         |      Message::pushMessage('error', 'Message');
         |
         |  The message is immediately output. Optionally,
         |  setting the MESSAGE_REPORT_DIE flag will cause
         |  the message to be reported using die().
         |
         */
            public function setReportType($_type)
            {
                $this->_report = $_type;
            }


        /*-------------------------------------------------
         |
         |  setFlags()
         |
         |  Kinda obvious...
         |
         */
            public function setFlags($_flags)
            {
                $this->_flags |= $_flags;
            }


        /*-------------------------------------------------
         |
         |  unsetFlags()
         |
         |  No comment
         |
         */
            public function unsetFlags($_flags)
            {
                $this->_flags & ~$_flags;
            }


        /*-------------------------------------------------
         |
         |  pushMessage()
         |
         |  Push the specified message.
         |
         |  The message stack will be created if it does
         |  not already exist.
         |
         */
            public function pushMessage($_type, $_msg)
            {
                if (! isset($this->_messages[$_type]))
                    $this->_messages[$_type] = Array();
            
                array_push($this->_messages[$_type], $_msg);

                if (! is_null($this->_report)) {
                    if ($this->_report == $_type) {
                        if ($this->_flags & MESSAGE_REPORT_DIE)
                            die($_msg);
                        echo $_msg;
                    }
                }
            }


        /*-------------------------------------------------
         |
         |  popMessage()
         |
         |  Pop the next message of the given $_type, this
         |  both removes the popped message from the array
         |  and returns the popped message string
         |
         |  Will return false if the message $_type is not
         |  found to exist or if the array exists but is
         |  empty.
         |
         */
            public function popMessage($_type)
            {
                if (! isset($this->_messages[$_type]))
                    return false;   // Stack doesn't exist
                if (! count($this->_messages[$_type]))
                    return false;   // Stack is empty

                $_index = (count($this->_messages[$_type]) - 1);

                $_msg = $this->_messages[$_type][$_index];
                array_splice($this->_messages[$_type], $_index, 1);

                return $_msg;
            }


        /*-------------------------------------------------
         |
         |  getall()
         |
         |  Returns all of the $_messages arrays.
         |
         */
            public function getAll()
            {
                return $this->_messages;
            }


        /*-------------------------------------------------
         |
         |  getMessages()
         |
         |  Returns an array of specific message types if
         |  if exists, otherwise returns false.
         |
         */
            public function getMessages($_type)
            {
                if (! isset($this->_messages[$_type]))
                    return false;   // Stack doesn't exist
                return $this->_messages[$_type];
            }


            public function deleteMessages($_type) {
                if (isset($this->_messages[$_type]))
                    unset($this->_messages[$_type]);
            }

        }

