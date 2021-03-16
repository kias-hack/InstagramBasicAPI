<?

include "../src/bootstrap.php";

$token_str = "строка с токеном";

$token = Token($token_str, $date_create_token);

//update token
// $token->update();

// Токен сериализируется сохраняя сам токен и время окончания его действия (+50 дней с начала обновления или создания)
// $data = serialize($token);
// $token = unserialize($data);

\BasicInstagram\Request::getInstance()->setToken($token->getToken());

$me = new \BasicInstagram\Me();

$me->getMedia()->getNext(12); //медиа пользователя с лимитом в 12
//lazy load
$me->getUsername(); //логин пользователя
$me->getID(); //id пользователя
?>