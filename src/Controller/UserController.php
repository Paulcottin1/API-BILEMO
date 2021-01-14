<?php


namespace App\Controller;


use App\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
     * @Rest\View(StatusCode = 200)
     * @Security("userConnected.getId() == user.getId()")
     * @param User $userConnected
     * @return User
     */
    public function user(User $userConnected)
    {
        return $userConnected;
    }
}
