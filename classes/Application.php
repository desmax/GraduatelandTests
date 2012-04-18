<?php

/**
 * Application class. Used to initialize all and do crawling
 */
class Application {

    /**
     * Path to config file.
     * @var $_configPath string
     */
    protected $_configPath = 'config.local.ini';

    /**
     * Here stored config array
     * @var $_config array
     */
    protected $_config = array();

    /**
     * Holder for PDO object
     * @var $_pdoObject PDO
     */
    protected $_pdoObject;

    /**
     * Protected function, used to initialize config, check if everything is ok and can we actually start to crawl
     * @throws Exception
     * @return void
     */
    protected function _initialize() {
        if(!file_exists('classes/Crawler.php')) {
            throw new Exception('There is no Crawler.php file');
        }

        require_once('Crawler.php');

        if(!file_exists('classes/JobModel.php')) {
            throw new Exception('There is no JobModel.php file');
        }

        require_once('JobModel.php');

        if(!file_exists($this->_configPath)) {
            throw new Exception('There is no config file');
        }

        $this->_config = parse_ini_file($this->_configPath);

        $necessaryParams = array('link', 'engine', 'database', 'host', 'user', 'password');
        foreach($necessaryParams as $param) { //check if all necessary param are presented
            if(!isset($this->_config[$param])) {
                throw new Exception("There is no {$param} param in config file");
            }
        }

        $connectionString = "{$this->_config['engine']}:host={$this->_config['host']};dbname={$this->_config['database']}";
        $this->_pdoObject = new PDO($connectionString, $this->_config['user'], $this->_config['password']);
        $this->_pdoObject->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Sets path to config file
     * @param $configPath string
     * @return void
     */
    public function setConfigFile($configPath) {
        $this->_configPath = $configPath;
    }

    /**
     * That very function that used to run all of    the stuff
     * @throws Exception
     * @return void
     */
    public function run() {
        try {
            $this->_initialize();

            $crawler = new Crawler($this->_config['link']);
            $jobs = $crawler->getJobs();

            foreach($jobs as $job) {
                $jobModel = new JobModel($this->_pdoObject);

                if(!$jobModel->jobExists($job['link'])) {
                    $jobModel->country_id  = 0;
                    $jobModel->region_id   = 0;
                    $jobModel->city_id     = 0;
                    $jobModel->url         = $job['link'];
                    $jobModel->head        = $job['head'];
                    $jobModel->text        = $job['text'];
                    $jobModel->experienced = 0;

                    $jobModel->save();
                }
            }

            echo 'Happy End';
        } catch(Exception $e) {
            echo 'Exception: ' . $e->getMessage();
        }
    }
}