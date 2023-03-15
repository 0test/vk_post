<?php
namespace BW;
class Vkontakte
{
    const VERSION = '5.131';
    private $appId;
    private $secret;
    private $scope = array();
    private $redirect_uri;
    private $responceType = 'code';
    private $accessToken;
    public function __construct(array $config){
        if (isset($config['access_token'])) {
            $this->setAccessToken(json_encode(['access_token' => $config['access_token']]));
        }
        if (isset($config['app_id'])) {
            $this->setAppId($config['app_id']);
        }
        if (isset($config['secret'])) {
            $this->setSecret($config['secret']);
        }
        if (isset($config['scopes'])) {
            $this->setScope($config['scopes']);
        }
        if (isset($config['redirect_uri'])) {
            $this->setRedirectUri($config['redirect_uri']);
        }
        if (isset($config['response_type'])) {
            $this->setResponceType($config['response_type']);
        }
    }
    public function getUserId(){
        return $this->accessToken->user_id;
    }
    public function setAppId($appId){
        $this->appId = $appId;
        return $this;
    }
    public function getAppId(){
        return $this->appId;
    }
    public function setSecret($secret){
        $this->secret = $secret;
        return $this;
    }
    public function getSecret(){
        return $this->secret;
    }
    public function setScope(array $scope){
        $this->scope = $scope;
        return $this;
    }
    public function getScope(){
        return $this->scope;
    }
    public function setRedirectUri($redirect_uri){
        $this->redirect_uri = $redirect_uri;
        return $this;
    }
    public function getRedirectUri(){
        return $this->redirect_uri;
    }
    public function setResponceType($responceType){
        $this->responceType = $responceType;
        return $this;
    }
    public function getResponceType(){
        return $this->responceType;
    }
    public function getLoginUrl(){
        return 'https://oauth.vk.com/authorize'
        . '?client_id=' . urlencode($this->getAppId())
        . '&scope=' . urlencode(implode(',', $this->getScope()))
        . '&redirect_uri=' . urlencode($this->getRedirectUri())
        . '&response_type=' . urlencode($this->getResponceType())
        . '&v=' . urlencode(self::VERSION);
    }
    public function isAccessTokenExpired(){
        return time() > $this->accessToken->created + $this->accessToken->expires_in;
    }
    public function authenticate($code = NULL){
        $code = $code ? $code : $_GET['code'];
        $url = 'https://oauth.vk.com/access_token'
            . '?client_id=' . urlencode($this->getAppId())
            . '&client_secret=' . urlencode($this->getSecret())
            . '&code=' . urlencode($code)
            . '&redirect_uri=' . urlencode($this->getRedirectUri());
        $token = $this->curl($url);
        $data = json_decode($token);
        $data->created = time();
        $token = json_encode($data);
        $this->setAccessToken($token);
        return $this;
    }
    public function setAccessToken($token){
        $this->accessToken = json_decode($token);
        return $this;
    }
    public function getAccessToken(){
        return json_encode($this->accessToken);
    }
    public function api($method, array $query = array()){
        $parameters = array();
        foreach ($query as $param => $value) {
            $q = $param . '=';
            if (is_array($value)) {
                $q .= urlencode(implode(',', $value));
            } else {
                $q .= urlencode($value);
            }
            $parameters[] = $q;
        }
        $q = implode('&', $parameters);
        if (count($query) > 0) {
            $q .= '&'; // Add "&" sign for access_token if query exists
        }
        $url = 'https://api.vk.com/method/' . $method . '?' . $q . 'access_token=' . $this->accessToken->access_token ."&v=" . self::VERSION;
        $result = json_decode($this->curl($url));
        if (isset($result->response)) {
            return $result->response;
        }
        return $result;
    }
    protected function curl($url){
        // create curl resource
        $ch = curl_init();
        // set url
        curl_setopt($ch, CURLOPT_URL, $url);
        // return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // disable SSL verifying
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // $output contains the output string
        $result = curl_exec($ch);
        if (!$result) {
            $errno = curl_errno($ch);
            $error = curl_error($ch);
        }
        curl_close($ch);
        if (isset($errno) && isset($error)) {
            throw new \Exception($error, $errno);
        }
        return $result;
    }
    public function postToPublic($publicID, $text, $fullServerPathToImage, $tags = array()){
        $params = [];
		$ids = [];
		$i=1;
		$get_server = $this->api('photos.getWallUploadServer', [
			'group_id' => $publicID,
		]);
		$uploadURL = $get_server->upload_url;
		
		foreach($fullServerPathToImage as $one_photo){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $uploadURL);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);			
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			if (class_exists('\CURLFile')) {
			$params = array('photo' => new \CURLFile($one_photo));
			} else {
			$params = array('photo' => '@' . $one_photo);
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			
			$result=curl_exec($ch);
			if($result === false){
				$result = curl_error($ch);
				var_dump($result);
				//	Если этот вардамп исполняется, скорей всего виноват хостер, который зарубил allow_url_fopen
			}
			curl_close($ch);
			$json = json_decode($result);
				$response = $this->api('photos.saveWallPhoto', [
						'group_id' => $publicID,
						'photo' => $json->photo,
						'server' => $json->server,
						'hash' => $json->hash,
				]);
				foreach($response as $photo_str){
					$ids[] = "photo" . $photo_str->owner_id . "_" . $photo_str->id;
				}
			$i++; 
		}
		$ids=implode(',',$ids);
		$text = html_entity_decode($text);	//	Сомнительная польза. Мне надо.
		$response = $this->api('wall.post',
            [
                'owner_id' => -$publicID,
                'from_group' => 1,
                'message' => "$text",
                'attachments' => $ids,
            ]);
        return isset($response->post_id);
	}
}
