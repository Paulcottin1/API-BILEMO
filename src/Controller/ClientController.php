<?php


namespace App\Controller;


use App\Entity\Client;
use App\Repository\ClientRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClientController extends AbstractController
{
    /**
     * Add Client
     * @Rest\Post(path="/api/client")
     * @Rest\View(
     *     StatusCode = 201,
     *     serializerGroups={"client"}
     * )
     * @ParamConverter("client", converter="fos_rest.request_body")
     * @param Client $client
     * @return Client
     */
    public function add(Client $client)
    {
        $client->setUser($this->getUser());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($client);
        $entityManager->flush();

        return $client;
    }

    /**
     * Return the clients list
     * @Rest\Get(path="/api/clients")
     * @Rest\View(
     *     StatusCode = 200,
     *     serializerGroups={"client"}
     * )
     * @param ClientRepository $clientRepository
     * @return Client[]
     */
    public function clientsList(ClientRepository $clientRepository)
    {
        return $clientRepository->findBy([
            'user' => $this->getUser()
        ]);
    }

    /**
     * Return client detail
     * @Rest\Get(
     *     path="/api/clients/{id}",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(
     *     StatusCode = 200,
     *     serializerGroups={"client"}
     * )
     * @Security("client.getUser().getId() === user.getId()")
     * @param Client $client
     * @return Client
     */
    public function client(Client $client)
    {
        return $client;
    }

    /**
     * @Rest\Put(
     *     path="/api/clients/{id}",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(
     *     StatusCode = 201,
     *     serializerGroups={"client"}
     * )
     * @Security("client.getUser().getId() === user.getId()")
     * @param Client $client
     * @param Request $request
     * @param SerializerInterface $serializerInterface
     * @param ValidatorInterface $validator
     * @param ClientRepository $clientRepository
     * @return Client|array|object|JsonResponse
     */
    public function updateClient(Client $client, Request $request, SerializerInterface $serializerInterface, ValidatorInterface $validator, ClientRepository $clientRepository)
    {
        $json = $request->getContent();
        $clientUpdate = $serializerInterface->deserialize($json, Client::class, 'json');
        $errors = $validator->validate($clientUpdate, null, ['client']);

        if (count($errors) > 0) {
            return new JsonResponse(['message' => 'Data not valid'], Response::HTTP_NOT_MODIFIED);
        } else {
            $client
                ->setFirstname($clientUpdate->getFirstname())
                ->setLastname($clientUpdate->getLastname())
                ->setEmail($clientUpdate->getEmail());
            $this->getDoctrine()->getManager()->flush();

            return $client;
        }
    }

    /**
     * @Rest\Delete(
     *     path="/api/clients/{id}",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 204)
     * @Security("client.getUser().getId() === user.getId()")
     * @param Client $client
     */
    public function deleteClient(Client $client)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($client);
        $entityManager->flush();
    }
}
