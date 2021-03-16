<?
namespace BasicInstagram;

class Token implements \Serializable
{
    public function serialize()
    {
        return serialize([
            "token" => $this->token,
            "dateEnd" => $this->dateEnd->format("Y-m-d h:i:s"),
        ]);
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $data["token"] = (string)$data["token"];
        $data["dateEnd"] = (string)$data["dateEnd"];

        if(empty((string)$data["token"]))
            throw new \Exception("token is empty");

        if(!empty($data["dateEnd"]))
            $this->dateEnd = \DateTime::createFromFormat("Y-m-d h:i:s", $data["dateEnd"]);

        $this->token = $data["token"];
    }

    private $token = null;
    /**
     * @var \DateTime|null
     */
    private $dateEnd = null;

    public function __construct(string $token = null, \DateTime $dateEnd = null){
        $this->token = $token;

        if(empty($dateEnd)) {
            $this->dateEnd = new \DateTime();
            $this->dateEnd->add(new \DateInterval('P50D'));
        }
        else
            $this->dateEnd = $dateEnd;
    }

    /**
     * @return null|string
     */
    public function getToken() : string
    {
        return (string)$this->token;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    public function update() {
        $res = Request::getInstance()
            ->setMethod("/refresh_access_token")
            ->addParam("grant_type", "ig_refresh_token")
            ->addParam("access_token", $this->token)
            ->exec()
        ;

        if(!empty($res["error"]))
            throw new \Exception($res["error"]["code"]." ".$res["error"]["message"]);

        $this->dateEnd = new \DateTime();
        $this->dateEnd->add(new \DateInterval('P50D'));
        $this->token = $res["access_token"];
    }
}

interface IRequest{
    public function addParam(string $param, $value) : IRequest;
    public function setMethod(string $method) : IRequest;
    public function addParams(array $params, bool $needMerge) : IRequest;
    public function exec() : array;
}

class Request implements IRequest{
    /**
     * @var array
     */
    protected $params = [];
    /**
     * @var string
     */
    protected $method = "";
    /**
     * @var string
     */
    protected $apiUrl = "https://graph.instagram.com";
    /**
     * @var string
     */
    protected $token = "";

    /**
     * @var string
     */
    public $LAST_ERROR = "";

    /**
     * @var null|IRequest
     */
    private static $instance = null;
    private function __construct(){}

    /**
     * @return IRequest|Request|null
     */
    public static function getInstance(){
        if(empty(self::$instance))
            self::$instance = new self();

        return self::$instance;
    }

    /**
     * @param string $param
     * @param $value
     * @return IRequest
     */
    public function addParam(string $param, $value) : IRequest{
        $this->params[$param] = $value;

        return $this;
    }

    /**
     * @param string $method
     * @return IRequest
     */
    public function setMethod(string $method) : IRequest{
        $this->method = $method;

        return $this;
    }

    /**
     * @param array $params
     * @param bool $needMerge
     * @return IRequest
     */
    public function addParams(array $params, bool $needMerge) : IRequest{
        if($needMerge)
            $params = array_merge($params, $this->params);

        $this->params = $params;

        return $this;
    }

    /**
     * @param string $token
     * @return IRequest
     */
    public function setToken(string $token) : IRequest{
        $this->token = $token;

        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function exec() : array {
        if(empty($this->token))
            throw new \Exception("empty token");

        $url = $this->apiUrl.$this->method."?".$this->buildParams();
        if(empty($this->params["access_token"]))
            $url .= "&access_token=".$this->token;

        $answ = $this->curlExec($url);

        try{
            $res = json_decode($answ, true);

            if(empty($res))
                throw new \Exception("Пустой ответ");
        }
        catch (\Exception $e){
            $res = [];
            $this->LAST_ERROR = "Произошла ошибка при декодировании ответа ".$e->getMessage()." ".$answ;
        }

        return $res;
    }

    /**
     * @param $url
     * @return string
     */
    protected function curlExec($url) : string{
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);


        $result = curl_exec($curl);

        curl_close($curl);

        if(empty($result))
            return "";

        return $result;
    }

    /**
     * @description сбрасывает состояние запросов
     * @return IRequest
     */
    public function reset() : IRequest {
        $this->params = [];
        $this->method = "";

        return $this;
    }

    /**
     * @description строит строку с параметрами
     * @return string
     */
    private function buildParams() : string {
        $parts = [];
        foreach($this->params as $param => $value){
            if(is_array($value))
                $value = join(",", $value);

            $parts[] = $param."=".$value;
        }

        return join("&", $parts);
    }
}

class Me{
    /**
     * @var null
     */
    protected $username = null;
    /**
     * @var null
     */
    protected $id = null;
    /**
     * @var null|Media
     */
    protected $media = null;
    /**
     * @var IRequest|null
     */
    protected $gateway = null;

    /**
     * Me constructor.
     * @param IRequest|null $gateway
     */
    public function __construct(IRequest $gateway = null){
        $this->gateway = $gateway;
        $this->media = new Media();
    }

    /**
     * @description запрашивает данные о пользователе и записывает их
     */
    private function fetch(){
        $res = Request::getInstance()
            ->addParam("fields", self::getMap())
            ->setMethod("/me")
            ->exec()
        ;

        if(empty($res['error'])){
            $this->username = $res['username'];
            $this->id = $res['id'];
        }
    }

    /**
     * @return string
     */
    public function getUsername() : string {
        if(empty($this->username))
            $this->fetch();

        return (string)$this->username;
    }

    /**
     * @return int
     */
    public function getID() : int {
        if(empty($this->id))
            $this->fetch();

        return (int)$this->id;
    }

    /**
     * @param IRequest $gateway
     * @return Me
     */
    public function setGateway(IRequest $gateway) : Me{
        if(empty($gateway))
            $gateway = Request::getInstance();

        $this->gateway = $gateway;

        $this->media->setGateway($gateway);

        return $this;
    }

    /**
     * @return Media|null
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return array
     */
    static public function getMap(){
        return [
            "id",
            "username"
        ];
    }
}

class Media{
    /**
     * @var IRequest|null
     */
    protected $gateway = null;
    /**
     * @var string|null
     */
    protected $after = null;

    /**
     * @var null|string
     */
    protected $before = null;

    /**
     * @return array
     */
    static public function getMap(){
        return [
            'caption',
            'id',
            'media_type',
            'media_url',
            'permalink',
            'thumbnail_url',
            'timestamp',
            'username'
        ];
    }

    /**
     * @return Media
     */
    public function clearPaging() : Media{
        $this->after = null;
        $this->before = null;

        return $this;
    }

    /**
     * @description устанавливает другой шлюз к api
     *
     * @param IRequest|null $gateway
     * @return Media
     */
    public function setGateway(IRequest $gateway = null) : Media{
        if(empty($gateway))
            $gateway = Request::getInstance();

        $this->gateway = $gateway;

        return $this;
    }

    /**
     * @description запрашивает данные из api с сохранением offset и следующие берет с этим учетом
     *
     * @throws \Exception
     * @param int $limit
     * @return array
     */
    public function getNext(int $limit = 9) : array{
        $res = Request::getInstance()
            ->addParam("limit", $limit)
            ->addParam("offset", $this->offset)
            ->addParam("fields", self::getMap())
            ->setMethod("/me/media")
        ;

        if(!empty($this->after))
            $res->addParam("after", $this->after);

        $res = $res->exec();

        if(!$this->is_correct($res))
            throw new \Exception("bad format");

        if(!empty($res['error']))
            throw new \Exception($res['error']['message']);

        $this->after = $res['paging']['cursors']['after'];
        $this->before = $res['paging']['cursors']['before'];

        return $res['data'];
    }

    /**
     * @param $res
     * @return bool
     */
    private function is_correct($res) : bool {
        if(!empty('error') || (!empty('paging') && !empty($res['data'])))
            return true;

        return false;
    }
}
