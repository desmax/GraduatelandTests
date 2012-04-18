<?php
/**
 * Crawler class. Interacts with html.
 */
class Crawler {

    /**
     * Keeps link in safety
     * @var $_link string
     */
    protected $_link;

    /**
     * @param $link
     * @param $pdoObject
     * @throws Exception
     * @return void
     */
    public function __construct($link) {
        if (filter_var($link, FILTER_VALIDATE_URL) === FALSE) {
            throw new Exception('Not a valid URL given');
        }
        $this->_link = $link;
    }

    /**
     * Starts to crawl
     * Returns array of parsed jobs
     * @return array
     */
    public function getJobs() {
        return $this->_getJobs();
    }

    /**
     * Returns array of parsed jobs
     * @return array
     */
    protected function _getJobs() {
        $categoriesDom = $this->_getDomObject($this->_link);

        $categories = array();
        $pTags = $categoriesDom->getElementById('content')->getElementsByTagName('p');
        foreach($pTags as $pTag) {
            /** @var $pTag DOMElement*/
            $aTags = $pTag->getElementsByTagName('a');
            foreach($aTags as $aTag) {
                /** @var $aTag DOMElement*/
                if ($aTag->hasAttribute('href') && $aTag->hasAttribute('onclick')) {
                    $categories[] = array('head' => $aTag->nodeValue, 'link' => $this->_link . $aTag->getAttribute('href'));
                }
            }
        }

        $jobs = array();
        foreach($categories as $category) {
            $categoryDom = $this->_getDomObject($category['link']);
            $jobsDom = $categoryDom->getElementById('main')->getElementsByTagName('ul');
            foreach($jobsDom as $jobDom) {
                /** @var $jobDom DOMElement*/
                $aTag =  $jobDom->getElementsByTagName('a');
                foreach ($aTag as $jobData) {
                    /** @var $jobData DOMElement*/
                    $relativeLink = str_replace('../', '', $jobData->getAttribute('href'));
                    $jobs[] = array('head' => $jobData->nodeValue, 'link' => $this->_link . $relativeLink);
                }
            }
        }

        foreach($jobs as $key => $job) {
            $jobDom = $this->_getDomObject($job['link']);
            $text = $jobDom->getElementById('content');
            $jobs[$key]['text'] = $text->nodeValue;
        }

        return $jobs;
    }

    /**
     * Reads a link and returns DOM object built with that link source
     * @param $link
     * @return DOMDocument
     * @throws Exception
     */
    protected function _getDomObject($link) {
        $file_headers = @get_headers($link);
        if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
            throw new Exception("404 Error");
        }
        $source = file_get_contents($link);

        $dom = new DOMDocument();
        @$dom->loadHTML($source);

        return $dom;
    }
}