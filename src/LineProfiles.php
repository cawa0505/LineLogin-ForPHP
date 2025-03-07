<?php


namespace LittleChou\LineLogin;


use LittleChou\LineLogin\Exceptions\LineAccessTokenNotFoundException;

class LineProfiles{

    /**
     * @var ConfigManager
     */
    private $configManager;

    public function __construct(ConfigManager $configManager){
        $this->configManager = $configManager;
    }

    /**
     * 取得用戶端 Profile
     *
     * @see https://developers.line.biz/en/docs/social-api/getting-user-profiles/
     * @param $code
     * @return bool|mixed|string
     * @throws LineAccessTokenNotFoundException
     */
    public function get($code){
        $tokens = self::getAccessToken($code);
        $config = $this->configManager->getConfigs();

        $accessToken = $tokens[0];
        $idToken = $tokens[1];


        $post = [
            'id_token' => $idToken,
            'client_id' => $config[ $this->configManager::CLIENT_ID ],
        ];
        $ch = curl_init();
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        // curl_setopt($ch, CURLOPT_URL, "https://api.line.me/v2/profile");
        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/oauth2/v2.1/verify");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $post ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result);


        $headerData = [
            "content-type: application/x-www-form-urlencoded",
            "charset=UTF-8",
            'Authorization: Bearer '.$accessToken,
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/v2/profile");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result2 = curl_exec($ch);
        curl_close($ch);
        $result2 = json_decode($result2);

        return array_merge((array)$result, (array)$result2);
    }

    /**
     * 取得用戶端 Access Token
     *
     * @see https://developers.line.biz/en/docs/line-login/web/integrate-line-login/
     * @param $code
     * @return string
     * @throws LineAccessTokenNotFoundException
     */
    private function getAccessToken($code){
        $config = $this->configManager->getConfigs();
        $post = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $config[ $this->configManager::CLIENT_REDIRECT_URI  ],
            'client_id' => $config[ $this->configManager::CLIENT_ID ],
            'client_secret' => $config[ $this->configManager::CLIENT_SECRET ],
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.line.me/oauth2/v2.1/token");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $post ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $info = curl_exec($ch);
        curl_close($ch);
        $info = json_decode($info);

        if(empty($info->access_token)){
            throw new LineAccessTokenNotFoundException('Can Not Find User Access Token');
        }
        return [$info->access_token, $info->id_token];
    }
}
