<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Mobile;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $password = $this->encoder->encodePassword($user, 'admin');
        $user
            ->setName('admin')
            ->setEmail('admin@admin.com')
            ->setPassword($password);

        $manager->persist($user);

        $mobile = new Mobile();
        $mobile
            ->setName('Iphone 12')
            ->setDescription('The Apple iPhone 12 features a 6.1-inch (15.3-cm) display with Super Retina XDR OLED technology at a resolution of 2532×1170 pixels and a pixel density of about 460 ppi.')
            ->setPrice(1000)
            ->addUser($user);

        $manager->persist($mobile);

        $mobile = new Mobile();
        $mobile
            ->setName('Galaxy S21')
            ->setDescription('Samsung Galaxy S21 is powered by a 2.2GHz octa-core Samsung Exynos 2100 processor that features 3 cores clocked at 2.8GHz, 4 cores clocked at 2.2GHz and 1 cores clocked at 2.9GHz. It comes with 8GB of RAM.')
            ->setPrice(1000)
            ->addUser($user);

        $manager->persist($mobile);

        $mobile = new Mobile();
        $mobile
            ->setName('Huawai P40')
            ->setDescription('Huawei P40 Pro smartphone runs on Android v10 (Q) operating system. The phone is powered by Octa core (2.86 GHz, Dual core, Cortex A76 + 2.36 GHz, Dual core, Cortex A76 + 1.95 GHz, Quad core, Cortex A55) processor.')
            ->setPrice(800)
            ->addUser($user);

        $manager->persist($mobile);

        $client = new Client();
        $client
            ->setFirstname('Jean')
            ->setLastname('Martin')
            ->setEmail('jean.martin@gmail.com')
            ->setUser($user);

        $manager->persist($client);

        $client = new Client();
        $client
            ->setFirstname('Eric')
            ->setLastname('Roger')
            ->setEmail('eric.roger@gmail.com')
            ->setUser($user);

        $manager->persist($client);

        $manager->flush();
    }
}
