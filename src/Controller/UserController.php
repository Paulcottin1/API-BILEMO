<?php


namespace App\Controller;


use App\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
}
