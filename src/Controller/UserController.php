<?php


namespace App\Controller;


use App\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
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
     * @SWG\Response(
     *     response=201,
     *     description="CREATED",
     *     @Model(type=User::class, groups={"user"})
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
     *   name="User",
     *   description="Fields to provide to create an user",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *     type="object",
     *     title="User field",
     *     @SWG\Property(property="name", type="string"),
     *     @SWG\Property(property="email", type="string"),
     *    )
     * )
     * @SWG\Tag(name="User")
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
     * Get current user
     * @Rest\Get(path="/api/user/{id}")
     * @Rest\View(
     *     StatusCode = 200,
     *     serializerGroups={"user"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @Model(type=User::class, groups={"user"})
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
     *   description="Id of the user to get",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Tag(name="User")
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

    /**
     * login
     * @Rest\Post(path="/api/login_check")
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @Model(type=User::class, groups={"user"})
     * )
     * @SWG\Response(
     *     response=400,
     *     description="BAD REQUEST"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="BAD CREDENTIALS"
     * )
     * @SWG\Parameter(
     *   name="Login",
     *   description="Fields to provide to sign in and get a token",
     *   in="body",
     *   required=true,
     *   type="string",
     *   @SWG\Schema(
     *     type="object",
     *     title="User login field",
     *     @SWG\Property(property="username", type="email"),
     *     @SWG\Property(property="password", type="string"),
     *    )
     * )
     * @SWG\Tag(name="Authentication")
     */
    public function login()
    {

    }
}
