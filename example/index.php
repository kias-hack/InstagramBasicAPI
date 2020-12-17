<?

include "../src/bootstrap.php";

$token_str = "any_token_string";
$date_create_token = \DateTime();

$token = Token($token_str, $date_create_token)

$settings = new \InstagramAPI\Settings($token);

$settings
	//разрешаем обновление
    ->setCanUpdateToken(true)
    //устанавливает функцию которая вызовется после обновления токена (чтобы сохранить)
    ->setCallbackUpdateToken(function(string $token, \DateTime $date){
    	//сериализуем чтобы сохранить в БД или файл
        $token_serial = serialize(new \InstagramAPI\Token\Token($token, $date));
        //...
    })
    //определяем дату вкогда нужно обновить токен (к дате создания прибавляем 30 дней)
    ->setDateToUpdateToken((clone $this->token->getDateCreate())->modify("+30 days"))
;

//инициализируем клиента
$instaClient = new \InstagramAPI\Client($settings);

$instaClient->getMedia(); //медиа пользователя
$instaClient->getUserData() //данные пользователя

?>