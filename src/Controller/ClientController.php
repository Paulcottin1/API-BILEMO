<?php


namespace App\Controller;


use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\Common\Annotations\AnnotationReader;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClientController extends AbstractController
{
    /**
     * Add Client
     * @Rest\Post(path="/api/client")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("client", converter="fos_rest.request_body")
     * @param Client $client
     * @return Client
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function add(Client $client)
    {
        $client->setUser($this->getUser());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($client);
        $entityManager->flush();

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);
        $data = $serializer->normalize($client, null, ['groups' => 'client']);

        return $data;
    }

    /**
     * Return the clients list
     * @Rest\Get(path="/api/clients")
     * @Rest\View(StatusCode = 200)
     * @param ClientRepository $clientRepository
     * @return Client[]
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function clientsList(ClientRepository $clientRepository)
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);

        $clients = $clientRepository->findBy([
            'user' => $this->getUser()
        ]);
        $arrayClients = [];

        foreach ($clients as $client)
        {
            $arrayClients[] = $serializer->normalize($client, null, ['groups' => 'client']);
        }

        return $arrayClients;
    }

    /**
     * Return client detail
     * @Rest\Get(
     *     path="/api/clients/{id}",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 200)
     * @Security("client.getUser().getId() === user.getId()")
     * @param Client $client
     * @return Client[]
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function client(Client $client)
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer]);
        $data = $serializer->normalize($client, null, ['groups' => 'client']);

        return $data;
    }

    /**
     * @Rest\Put(
     *     path="/api/clients/{id}",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = 201)
     * @Security("client.getUser().getId() === user.getId()")
     * @param Client $client
     * @param Request $request
     * @param SerializerInterface $serializerInterface
     * @param ValidatorInterface $validator
     * @param ClientRepository $clientRepository
     * @return Client|array|object|JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function updateClient(Client $client, Request $request, SerializerInterface $serializerInterface, ValidatorInterface $validator, ClientRepository $clientRepository)
    {
            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $normalizer = new ObjectNormalizer($classMetadataFactory);
            $serializer = new Serializer([$normalizer]);
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
                $data = $serializer->normalize($client, null, ['groups' => 'client']);

                return $data;
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
