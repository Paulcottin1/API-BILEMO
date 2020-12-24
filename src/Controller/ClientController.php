<?php


namespace App\Controller;


use App\Entity\Client;
use App\Repository\ClientRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ClientController extends AbstractController
{
    /**
     * Add Client
     * @Rest\Post(path="/client")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("client", converter="fos_rest.request_body")
     * @param Client $client
     * @return Client
     */
    public function add(Client $client)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($client);
        $entityManager->flush();

        return $client;
    }

    /**
     * Return the clients list
     * @Rest\Get(path="/clients")
     * @Rest\View(StatusCode = 200)
     * @param ClientRepository $clientRepository
     * @return Client[]
     */
    public function clientsList(ClientRepository $clientRepository)
    {
        return $clientRepository->findAll();
    }

    /**
     * Return client detail
     * @Rest\Get(
     *     path="/clients/{id}",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 200)
     * @param Client $client
     * @return Client
     */
    public function client(Client $client)
    {
        return $client;
    }
}
