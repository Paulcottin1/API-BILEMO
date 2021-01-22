<?php


namespace App\Controller;


use App\Entity\Mobile;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
     * @return Mobile
     */
    public function mobile(Mobile $mobile)
    {
        if(!$mobile->getUsers()->contains($this->getUser())) {
            throw new AccessDeniedHttpException('This mobile does not belong to you');
        }

        return $mobile;
    }

    /**
     * Return the clients list linked to user connected
     * @Rest\Get(path="/api/mobiles")
     * @Rest\View(
     *     StatusCode = 200,
     *     serializerGroups={"mobile"}
     * )
     * @return \App\Entity\Mobile[]
     */
    public function clientsList()
    {
        return $this->getUser()->getMobiles();
    }
}
