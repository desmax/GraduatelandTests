<?
class JobModel {

    /** @var $country_id int */
    public $country_id;

    /** @var $region_id int */
    public $region_id;

    /** @var $city_id int */
    public $city_id;

    /** @var $url string */
    public $url;

    /** @var $head string */
    public $head;

    /** @var $text string */
    public $text;

    /** @var $experienced int */
    public $experienced;

    /**
     * @var $_pdoObject PDO
     */
    protected $_pdoObject;

    /**
     * Class constructor
     * @param $pdoObject
     */
    public function __construct($pdoObject) {
        $this->_pdoObject = $pdoObject;
    }

    /**
     * Saves job to DB
     * @return void
     */
    public function save() {
        $sql = "INSERT INTO `jobs` (
            `id` ,
            `country_id` ,
            `region_id` ,
            `city_id` ,
            `url` ,
            `head` ,
            `text` ,
            `experienced`)
            VALUES(NULL, :country_id, :region_id, :city_id, :url, :head, :text, :experienced);";

        $query = $this->_pdoObject->prepare($sql);

        $query->bindValue(':country_id',  (int)$this->country_id,   PDO::PARAM_INT);
        $query->bindValue(':region_id',   (int)$this->region_id,    PDO::PARAM_INT);
        $query->bindValue(':city_id',     (int)$this->city_id,      PDO::PARAM_INT);
        $query->bindValue(':url',         trim($this->url),         PDO::PARAM_STR);
        $query->bindValue(':head',        trim($this->head),        PDO::PARAM_STR);
        $query->bindValue(':text',        trim($this->text),        PDO::PARAM_STR);
        $query->bindValue(':experienced', trim($this->experienced), PDO::PARAM_INT);
        $query->execute();
    }

    /**
     * Check if there is such job already
     * @param $url string
     * @return boolean
     */
    public function jobExists($url) {
        $sql = "SELECT COUNT( id ) FROM `jobs` WHERE `url` LIKE :url";
        $query = $this->_pdoObject->prepare($sql);
        $query->bindValue(':url', trim($url), PDO::PARAM_STR);
        $query->execute();
        $rowCount = $query->fetchColumn();

        if($rowCount != 0) {
            return true;
        }

        return false;
    }
}