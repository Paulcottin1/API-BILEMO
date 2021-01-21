<?php


namespace App\Controller;


use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MobileController extends AbstractController
{
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
