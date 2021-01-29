<?php


namespace App\Controller;


use App\Entity\Client;
use App\Repository\ClientRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

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
     * @SWG\Response(
     *     response=201,
     *     description="CREATED",
     *     @Model(type=Client::class, groups={"client"})
     * )
     * @SWG\Response(
     *     response=400,
     *     description="BAD REQUEST"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     default="Bearer TOKEN",
     *     description="Authorization"
     * )
     * @SWG\Parameter(
     *   name="Client",
     *   description="Fields to provide to create an client",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *     type="object",
     *     title="Client field",
     *     @SWG\Property(property="firstname", type="string"),
     *      @SWG\Property(property="lastname", type="string"),
     *     @SWG\Property(property="email", type="string")
     *   )
     * )
     * @SWG\Tag(name="Client")
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
     * Get the list of the user's clients
     * @Rest\Get(path="/api/clients")
     * @Rest\View(
     *     StatusCode = 200,
     *     serializerGroups={"client"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @Model(type=Client::class, groups={"client"})
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="string",
     *     description="The field used to pagination"
     * )
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     default="Bearer TOKEN",
     *     description="Authorization"
     * )
     * @SWG\Tag(name="Client")
     * @param ClientRepository $clientRepository
     * @param TagAwareCacheInterface $cache
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Client[]
     * @throws InvalidArgumentException
     */
    public function clientsList(ClientRepository $clientRepository, TagAwareCacheInterface $cache, PaginatorInterface $paginator, Request $request)
    {
        $page = $request->query->getInt('page', 1);

        return $cache->get('clients'. $this->getUser()->getId() . $page,
            function (ItemInterface $item) use ($clientRepository, $paginator, $page) {
                $item->expiresAfter(3600);
                $item->tag('client');
                $data = $clientRepository->findBy([
                    'user' => $this->getUser()
                ]);

                return $paginator->paginate($data, $page, 4);
            });
    }

    /**
     * Get client detail
     * @Rest\Get(
     *     path="/api/clients/{id}",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(
     *     StatusCode = 200,
     *     serializerGroups={"client"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @Model(type=Client::class, groups={"client"})
     * )
     * @SWG\Response(
     *     response=403,
     *     description="ACCESS DENIED"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="NOT FOUND"
     * )
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     default="Bearer TOKEN",
     *     description="Authorization"
     * )
     * @SWG\Parameter(
     *   name="id",
     *   description="Id of the client to get",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Tag(name="Client")
     * @Security("client.getUser().getId() === user.getId()")
     * @param Client $client
     * @param TagAwareCacheInterface $cache
     * @return Client
     * @throws InvalidArgumentException
     */
    public function client(Client $client, TagAwareCacheInterface $cache)
    {
        return $cache->get('client'. $client->getId(),
            function (ItemInterface $item) use ($client) {
                $item->expiresAfter(3600);
                $item->tag('client');

                return $client;
            });
    }

    /**
     * Update client
     * @Rest\Put(
     *     path="/api/clients/{id}",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(
     *     StatusCode = 201,
     *     serializerGroups={"client"}
     * )
     * @SWG\Response(
     *     response=201,
     *     description="UPDATED",
     *     @Model(type=Client::class, groups={"client"})
     * )
     * @SWG\Response(
     *     response=400,
     *     description="BAD REQUEST"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="ACCES DENIED"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="NOT FOUND"
     * )
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     default="Bearer TOKEN",
     *     description="Authorization"
     * )
     * @SWG\Parameter(
     *   name="Client",
     *   description="Fields to provide to create an client",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *     type="object",
     *     title="User field",
     *     @SWG\Property(property="firstname", type="string"),
     *      @SWG\Property(property="lastname", type="string"),
     *     @SWG\Property(property="email", type="string")
     *   )
     * )
     * @SWG\Parameter(
     *   name="id",
     *   description="Id of the client to update",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Tag(name="Client")
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
     * Delete client
     * @Rest\Delete(
     *     path="/api/clients/{id}",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 204)
     * @SWG\Response(
     *     response=204,
     *     description="DELETED",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="UNAUTHORIZED - JWT Token not found | Expired JWT Token | Invalid JWT Token"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="ACCES DENIED"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="NOT FOUND"
     * )
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     default="Bearer TOKEN",
     *     description="Authorization"
     * )
     * @SWG\Parameter(
     *   name="id",
     *   description="Id of the client to update",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Tag(name="Client")
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
