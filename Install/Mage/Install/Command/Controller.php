<?php
/* a utility for magento: command line installer
*  uses magento functions to create a clean magento install which is vaild
The MIT License (MIT)

Copyright (c) 2015 Ethan Riley(https://gitlab.com/u/_UNKNOWN_) and verve(www.verveuk.eu)

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/




class Mage_Install_Command_Controller{

    private $XmlDefaultConfig;

    private $Xmlfile;


    public  function __isset($name){

        return empty($name);
    }


    /**
     * @param string $DefaultInstallXmlFile
     */
    function __construct($DefaultInstallXmlFile){


        $this->Xmlfile = $DefaultInstallXmlFile;

        $data = file_get_contents($this->Xmlfile);
        $this->XmlDefaultConfig = new DOMDocument();
        $this->XmlDefaultConfig->loadXML($data);
    }

    function __destruct(){

        $this->XmlDefaultConfig->save($this->Xmlfile);
    }

    /**
     * @param string $option
     * @param string $var
     * @returns bool true if option is set in xml
     */
    public function setoption($option, $var){

        $XmlVars = $this->XmlDefaultConfig->getElementsByTagName($option);
        $XmlVars->Item(0)->nodeValue = $var;
        $this->XmlDefaultConfig->saveXml($XmlVars);
        return true;
    }

    /**
     * @param string $option to get
     * @returns string the option value
     */
    protected function getoption($option){

        $XmlVars = $this->XmlDefaultConfig->getElementsByTagName($option);
        return $XmlVars->Item(0)->nodeValue;
    }

    /**
     * function for pasrsing db name as uses formatting
     */
    protected function getDBname(){

        $data = $this->getoption("db_name");
        preg_match('/(\[(.+)\](.+))/i', $data, $out);
        $data = $this->getoption($out[1]);

        if(preg_match('///', $data)){
            $out .= '_';
            $strs = explode('/', $data);
            for($i = 0;$i < count($strs); $i++){
                $out[2] .= $strs[$i];
            }
            echo $out[2];
            return $out[2];
        }
        else{
            var_dump($data.'_'.$out[2]);
            return $data.'_'.$out[2];
        }
    }

    /**
     * @returns bool true if function completes
     */
    public function Install(){

        $installer = new Maged_Controller();
        $chan = $installer->config()->__get('root_channel');
        try {
            $installer->model('connect', true)->installAll(true, $chan);
        }
        catch(Exception $e){
            return false;
        }
        return true;
    }


    /**
     * installs magento with the defalut xmlconfig
     * @return array if empty installation is sucsessful else will return all Exeception objects
     */
    public function Configure(){


        $installer = new Mage_Install_Command_Configuration_Model();
        $installer->setlocale($this->getoption("locale"), $this->getoption("timezone"), $this->getoption("currency"));
        $installer->admin_personaldetails($this->getoption("firstname"), $this->getoption("lastname"), $this->getoption("email"));

        $installer->database_setupconfig($this->getoption("db_model"), $this->getoption("db_host"), $this->getDBname());

        $installer->set_unsecure_urlconfigs($this->getoption("base_unsecure_url"), $this->getoption("use_rewrites"));
        $installer->set_secure_urlconfigs($this->getoption("base_secure_url"), $this->getoption("use_secure_admin"));
        $installer->admin_create_user($this->getoption("admin_url"), $this->getoption("username"), $this->getoption("password"));

        $installer->database_config($this->getoption("db_user"), $this->getoption("db_pass"), $this->getoption("db_prefix"),
                                                                $this->getoption("enable_charts"));

        return $installer->install($this->getoption("skip_base_url_validation"), $this->getoption("skip_url_validation"));
    }
}

