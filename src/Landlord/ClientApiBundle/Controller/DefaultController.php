<?php

namespace Landlord\ClientApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('LandlordClientApiBundle:Default:index.html.twig', array('name' => $name));
    }
}
