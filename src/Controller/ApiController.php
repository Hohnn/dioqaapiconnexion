<?php

namespace Dioqaapiconnexion\Controller;

use Dioqaapiconnexion\Entity\ProductComment;
use Dioqaapiconnexion\Entity\ProductCrd;
use Dioqaapiconnexion\Demo\DemoClass;
use Dioqaapiconnexion\Forms\DemoType;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpClient\HttpClient;
use Db;
use DbQuery;
use Configuration;
use Language;
use Tools;
use Search;
use Module;
use Tag;
use Image;
use Shop;
use ImageManager;
use ImageType;
use Context;
use PrestaShopException;


class ApiController
{

    const BASE_URI = "https://cerebro.ready2dev.fr";
    /* const BASE_URI = "https://cerebro.margueritegroup.fr"; */
    private static $instance;
    private $username = "dioqa";
    private $password = "87tP^JkVA2n3iKbU";
    private $token;

    private function __construct()
    {
        $this->auth();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function auth()
    {
        $client = HttpClient::create();
        $response = $client->request(
            'GET',
            self::BASE_URI . "/api/auth/authenticate",
            [
                'auth_basic' => [$this->username, $this->password]
            ]
        );

        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        if ($statusCode == 200) {
            $content = $response->getContent();
            $content = json_decode($content);
            $this->token = $content->token;
        }
        return $this->token;
    }

    public function get($route)
    {
        if (!$this->isTokenValid()) {
            $this->auth(); // Renouveler le token si nécessaire
        }

        $client = HttpClient::create();
        $response = $client->request(
            'GET',
            self::BASE_URI . $route,
            [
                'auth_bearer' => $this->token
            ]
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            throw new \ErrorException($response->getContent(false));
        }
        $content = $response->getContent();
        $content = json_decode($content);

        return $content;
    }

    public function post($route, $data)
    {
        if (!$this->isTokenValid()) {
            $this->auth(); // Renouveler le token si nécessaire
        }

        $client = HttpClient::create();
        $response = $client->request(
            'POST',
            self::BASE_URI . $route,
            [
                'auth_bearer' => $this->token,
                'json' => $data
            ]
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            throw new \ErrorException($response->getContent(false));
        }

        $content = $response->getContent();
        $content = json_decode($content);

        return $content;
    }

    private function isTokenValid()
    {
        // Vérifier si le token est défini et s'il n'a pas expiré
        return isset($this->token) && !$this->hasTokenExpired();
    }

    private function hasTokenExpired()
    {
        if (!isset($this->token)) {
            return true; // Si le token n'est pas défini, considérez-le comme expiré
        }

        $tokenParts = explode('.', $this->token);
        if (count($tokenParts) !== 3) {
            return true; // Le token JWT doit avoir trois parties
        }

        $payload = json_decode(base64_decode($tokenParts[1]), true);

        if (isset($payload['exp']) && is_numeric($payload['exp'])) {
            $expirationTimestamp = $payload['exp'];
            return $expirationTimestamp < time(); // Comparer avec l'heure actuelle
        }

        return true; // Si le champ 'exp' n'est pas présent ou n'est pas un nombre, considérez le token comme expiré
    }
}
