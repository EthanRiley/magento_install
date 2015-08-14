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


/**
 * downloads the downloader for magento
 */


class Mage_Install_Command_Configuration_Model {

    private $enckey;

    private $admindata = array();

    private $config = array();

    private $dbconfig = array();

    //increases readablity
    private function getInstaller(){

        return Mage::getSingleton('install/installer');
    }

    /**
     * @param bool $save_in_db if true saves session data in Database otherwise will be saved in filesystem
     */
    function __construct($save_in_db = false){

        Mage::init();
        if(!$save_in_db){
            $config['session_save'] = "db";
        }
        else{
            $config['session_save'] = "files";
        }

    }

    function __destruct(){

        if($this->getInstaller()->IsApplicationInstalled()) {
            Mage::getSingleton('install/session')->clear();
            $this->getInstaller()->finish();
        }
    }


    /**
     * @param string $locale @format X_Y where X is language and Y country in caps. 2 letters max each e.g. en_GB
     * @param string $timezone @format X/Y where X is contenent Y is city e.g. Europe/London
     * @param string $currency @format 3 caps identifier e.g. GBP = great british pound
     * @return bool true if function completed.
     */
    public function setlocale($locale, $timezone, $currency){

        $localedata = array();
        $localedata['locale'] = $locale;
        $localedata['timezone'] = $timezone;
        $localedata['currency'] = $currency;

        Mage::getSingleton('install/session')->setLocale($locale);
        Mage::getSingleton('install/session')->setTimezone($timezone);
        Mage::getSingleton('install/session')->setCurrency($currency);

        Mage::getSingleton('install/session')->setLocaleData($localedata);
        return true;
    }

    /**
     * @param string $dbtype name of the database type e.g. mysql4
     * @param string $host name of databases host e.g. localhost:80
     * @param string $name name of the database e.g. magento
     * @return bool true if function completed.
     */
    public function database_setupconfig($dbtype, $host, $name){

        $this->config['db_model'] = $dbtype;
        $this->dbconfig[$this->config['db_model']]['db_host'] = $host;
        $this->dbconfig[$this->config['db_model']]['db_name'] = $name;

        return true;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $prefix
     * @param bool $enable_charts
     * @return bool true if function completed.
     */
    public function database_config($username, $password = null, $prefix = null, $enable_charts = true){

        $this->config['enable_charts'] = $enable_charts;

        $this->dbconfig[$this->config['db_model']]['db_user'] = $username;
        $this->dbconfig[$this->config['db_model']]['db_pass'] = $password;
        $this->dbconfig[$this->config['db_model']]['db_prefix'] = $prefix;

        if($this->config['db_model'] == 'mysql4'){
            $dbtype = $this->config['db_model'];

            $Database = new mysqli($this->dbconfig[$dbtype]['db_host'], $this->dbconfig[$dbtype]['db_user'],
                                    $this->dbconfig[$dbtype]['db_pass']);

            $Database->query("CREATE DATABASE IF NOT EXISTS " . $this->dbconfig[$dbtype]['db_name'] );
            $Database->close();
        }

        return true;
    }

    /**
     * @param string $unsecure_base_url
     * @param bool $use_rewrites if true uses webserver rewites to get improved search engine optimization
     * note: if set true make sure that mod_rewrite is enabled in your apache configuration
     * @return bool true if function completed.
     */
    public function set_unsecure_urlconfigs($unsecure_base_url, $use_rewrites = false){

        $this->config['use_rewites'] = $use_rewrites;
        $this->config['unsecure_base_url'] = Mage::helper('core/url')->encodePunycode($unsecure_base_url);
        return true;
    }

    /**
     * @param $secure_base_url
     * @param bool $use_secure_admin
     * @return bool true if function completed
     * If not called no secure url will be created.
     */
    public function set_secure_urlconfigs($secure_base_url, $use_secure_admin = true){

        $this->config['use_secure'] = true;
        $this->config['use_secure_admin'] = $use_secure_admin;
        $this->config['secure_base_url'] = Mage::helper('core/url')->encodePunycode($secure_base_url);
        return true;
    }

    /**
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @return bool true if function completed.
     */
    public function admin_personaldetails($firstname, $lastname, $email){

        $this->admindata['firstname'] = $firstname;
        $this->admindata['lastname'] = $lastname;
        $this->admindata['email'] = $email;
        return true;
    }

    /**
     * @param string $adminurlpath relative path(appended to un/secure_base_url)
     * @param string $username
     * @param string $password
     * @param string $encryptionkey leave to auto generate one.
     * note: password confirmation argument skipped but still confirmed in function
     * @return bool true if function completed.
     */
    public function admin_create_user($adminurlpath, $username, $password, $encryptionkey = null){

        $this->config['admin_frontname']  = $adminurlpath;

        $this->admindata['username'] = $username;
        $this->admindata['new_password'] = $password;
        $this->admindata['password_confirmation'] = $password;
        $this->enckey = $encryptionkey;

        return true;
    }

    /**
     * @param bool $skip_base_url_validation
     * @param bool $skip_url_validation only check if not possible to vaildate automatically e.g. HTTP authentication is required
     * @return array of errors, if empty then install was succsessful.
     */
    public function install($skip_base_url_validation = false, $skip_url_validation = false){

        $errors = array();
        if(empty($this->config['secure_base_url'])){
            $this->config['use_secure'] = false;
        }
        if(!empty($this->config) && !empty($this->dbconfig[$this->config['db_model']])){
            $configdata = array_merge($this->config, $this->dbconfig[$this->config['db_model']]);

            Mage::getSingleton('install/session')->setConfigData($configdata)
                ->setSkipUrlValidation($skip_url_validation)
                ->setSkipUrlValidation($skip_base_url_validation);
            try{
                $this->getInstaller()->installConfig($configdata);
                $this->getInstaller()->installDb();

                Mage::getSingleton('install/session')->setAdminData($this->admindata);
                $this->getInstaller()->createAdministrator($this->admindata);
                $this->getInstaller()->installEnryptionKey($this->enckey);

                Mage_AdminNotification_Model_Survey::saveSurveyViewed(true);
            }
            catch(Exception $e){

                array_push($errors, $e);
                return $errors;
            }

        }

        return $errors;
    }

}
