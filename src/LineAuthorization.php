<?php


namespace LittleChou\LineLogin;


class LineAuthorization{

    private $configManager;

    public function __construct(ConfigManager $configManager){
        $this->configManager = $configManager;
    }

    public function createAuthUrl(){
        $config = $this->configManager->getConfigs();

        $parameter = [
            'response_type' => 'code',
            'client_id' => $config->client_id,
            'scope' => $config->client_scope,
            'state' => uniqid(15),
            'redirect_uri' => $config->redirect_uri
        ];

        $host = "https://access.line.me/oauth2/v2.1/authorize" ;

        $url = $host . "?" . http_build_query($parameter);

        return $url;
    }


}