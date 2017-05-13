<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Command\CreateNbHoureCommand;
use LaPoiz\WindBundle\Command\ImportCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class CommandeController extends Controller

{
  /**
   * @Template()
   *
   * Commande: Import
   */
  public function importAction()
  {
      // Element pour afficher la page BO
      $em = $this->container->get('doctrine.orm.entity_manager');
      // récupere tous les spots
      $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
      $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();
      $listSpotNotValid = $em->getRepository('LaPoizWindBundle:Spot')->findAllNotValid();
      $listSpotsWithoutRegion = $em->getRepository('LaPoizWindBundle:Spot')->findAllWithoutRegion();
      $listWebsites = $em->getRepository('LaPoizWindBundle:WebSite')->findAll();
      $listContacts = $em->getRepository('LaPoizWindBundle:Contact')->findAll();

      $messages = 10;

      $input = new ArrayInput(array(
          'command' => 'swiftmailer:spool:send',
          '--message-limit' => $messages,
      ));
      // You can use NullOutput() if you don't need the output
      $output = new BufferedOutput();


      // Commande Import
      ImportCommand::import($input, $output, $em);


      // return the output, don't use if you used NullOutput()
      $message = $output->fetch();

    return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice:index.html.twig',
        array(
            'listSpot' => $listSpot,
            'listRegion' => $listRegion,
            'listSpotNotValid' => $listSpotNotValid,
            'listSpotsWithoutRegion' => $listSpotsWithoutRegion,
            'listWebsites' => $listWebsites,
            'listContacts' => $listContacts,
            'message' => $message,
        ));
  }


    /**
     * @Template()
     *
     * Commande: Calcul nb houre de nav
     */
    public function calculNbHoureAction()
    {
        // Element pour afficher la page BO
        $em = $this->container->get('doctrine.orm.entity_manager');
        // récupere tous les spots
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
        $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();
        $listSpotNotValid = $em->getRepository('LaPoizWindBundle:Spot')->findAllNotValid();
        $listSpotsWithoutRegion = $em->getRepository('LaPoizWindBundle:Spot')->findAllWithoutRegion();
        $listWebsites = $em->getRepository('LaPoizWindBundle:WebSite')->findAll();
        $listContacts = $em->getRepository('LaPoizWindBundle:Contact')->findAll();

        $messages = 10;

        $input = new ArrayInput(array(
            'command' => 'swiftmailer:spool:send',
            '--message-limit' => $messages,
        ));
        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput();

        // Commande Calcul nb houre de nav
        CreateNbHoureCommand::calcul($input, $output, $em);

        // return the output, don't use if you used NullOutput()
        $message = $output->fetch();

        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice:index.html.twig',
            array(
                'listSpot' => $listSpot,
                'listRegion' => $listRegion,
                'listSpotNotValid' => $listSpotNotValid,
                'listSpotsWithoutRegion' => $listSpotsWithoutRegion,
                'listWebsites' => $listWebsites,
                'listContacts' => $listContacts,
                'message' => $message,
            ));
    }
}