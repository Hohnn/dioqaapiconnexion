<?php

namespace Dioqaapiconnexion\Controller;

use Dioqaapiconnexion\Entity\ProductComment;
use Dioqaapiconnexion\Forms\DemoType;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpClient\HttpClient;

class DemoController extends FrameworkBundleAdminController
{

    public const BASE_URI = "https://cerebro.ready2dev.fr";
    private $username = "dioqa";
    private $password = "87tP^JkVA2n3iKbU";
    private $token;


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
    }

    public function demoAction()
    {
        $this->auth();

        $client = HttpClient::create();
        $response = $client->request(
            'GET',
            self::BASE_URI . "/api/crd/components",
            [
                'auth_bearer' => $this->token
            ]
        );

        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            //throw error
        }
        $content = $response->getContent();
        /* $content = json_decode($content); */

        return new Response($content);
    }

    public function createAction(Request $requests): Response
    {
        $form = $this->createForm(DemoType::class);

        $form->handleRequest($requests);


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $pc = new ProductComment();
            $pc->setProductId(1);
            $pc->setCustomerName('dsqdqd');
            $pc->setTitle('dsqdqd');
            $pc->setContent('dsqdqd');
            $pc->setGrade(5);

            $em->persist($pc);
            $em->flush();
        }

        return $this->render(
            '@Modules/dioqaapiconnexion/templates/admin/create.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }
}
