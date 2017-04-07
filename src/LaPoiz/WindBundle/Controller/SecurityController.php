<?php
namespace LaPoiz\WindBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SecurityController extends Controller
{
  
    /**
     * @Template()
     */
    public function loginAction(Request $request)
    {
    	//$request = $this->getRequest();
    	//$session = $request->getSession();
		$session = $request->getSession();
    	// get the login error if there is one
    	if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
    		$error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
    	} else {
    		$error = $session->get(Security::AUTHENTICATION_ERROR);
    		$session->remove(Security::AUTHENTICATION_ERROR);
    	}
    	return $this->render('LaPoizWindBundle:Security:login.html.twig', array(
    			// last username entered by the user
    			'last_username' => $session->get(Security::LAST_USERNAME),
    			'error' => $error,
    	));
    }
}