<?php


namespace App\Controller;


use App\Entity\Mobile;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class MobileController extends AbstractController
{
    /**
     * Return mobile detail
     * @Rest\Get(
     *     path="/api/mobiles/{id}",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(
     *     StatusCode = 200,
     *     serializerGroups={"mobile"}
     * )
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
     * Return the clients list linked to user connected
     * @Rest\Get(path="/api/mobiles")
     * @Rest\View(
     *     StatusCode = 200,
     *     serializerGroups={"mobile"}
     * )
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
                $item->tag('client');
                $data = $this->getUser()->getMobiles();

                return $paginator->paginate($data, $page, 4);
            });
    }
}
