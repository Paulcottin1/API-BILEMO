<?php


namespace App\Controller;


use App\Entity\Mobile;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Cache\InvalidArgumentException;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class MobileController extends AbstractController
{
    /**
     * Get mobile detail
     * @Rest\Get(
     *     path="/api/mobiles/{id}",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(
     *     StatusCode = 200,
     *     serializerGroups={"mobile"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @Model(type=Mobile::class, groups={"mobile"})
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
     *   description="Id of the mobile to get",
     *   in="path",
     *   required=true,
     *   type="integer"
     * )
     * @SWG\Tag(name="Mobile")
     * @param Mobile $mobile
     * @param TagAwareCacheInterface $cache
     * @return Mobile
     * @throws InvalidArgumentException
     */
    public function mobile(Mobile $mobile, TagAwareCacheInterface $cache)
    {
        if(!$mobile->getUsers()->contains($this->getUser())) {
            throw new AccessDeniedHttpException('This mobile does not belong to you');
        }

        return $cache->get('mobile'. $mobile->getId(),
            function (ItemInterface $item) use ($mobile) {
                $item->expiresAfter(3600);
                $item->tag('mobile');

                return $mobile;
            });
    }

    /**
     * Get mobiles list
     * @Rest\Get(path="/api/mobiles")
     * @Rest\View(
     *     StatusCode = 200,
     *     serializerGroups={"mobile"}
     * )
     * @SWG\Response(
     *     response=200,
     *     description="OK",
     *     @Model(type=Mobile::class, groups={"mobile"})
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
     * @SWG\Tag(name="Mobile")
     * @param TagAwareCacheInterface $cache
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Mobile[]
     * @throws InvalidArgumentException
     */
    public function mobileList(TagAwareCacheInterface $cache, PaginatorInterface $paginator, Request $request)
    {
        $page = $request->query->getInt('page', 1);

        return $cache->get('mobiles'. $this->getUser()->getId() . $page,
            function (ItemInterface $item) use ( $paginator, $page) {
                $item->expiresAfter(3600);
                $item->tag('mobile');
                $data = $this->getUser()->getMobiles();

                return $paginator->paginate($data, $page, 4);
            });
    }
}
