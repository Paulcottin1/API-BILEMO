<?php


namespace App\Controller;


use App\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class UserController extends AbstractController
{
    /**
     * Add user
     * @Rest\Post(path="/api/user")
     * @Rest\View(StatusCode = 201)
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @param User $user
     * @param UserPasswordEncoderInterface $encoder
     * @return User
     */
    public function createUser(User $user, UserPasswordEncoderInterface $encoder)
    {
        $password = $user->getPassword();
        $encoded = $encoder->encodePassword($user, $password);
        $user->setPassword($encoded);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * Return user connected information
     * @Rest\Get(path="/api/user/{id}")
     * @Rest\View(
     *     StatusCode = 200,
     *     serializerGroups={"user"}
     *     )
     * @Security("userConnected.getId() == user.getId()")
     * @param User $userConnected
     * @param TagAwareCacheInterface $cache
     * @return User
     * @throws InvalidArgumentException
     */
    public function user(User $userConnected, TagAwareCacheInterface $cache)
    {
        return $cache->get('user'. $userConnected->getId(),
            function (ItemInterface $item) use ($userConnected) {
                $item->expiresAfter(3600);
                $item->tag('user');

                return $userConnected;
            });
    }
}
