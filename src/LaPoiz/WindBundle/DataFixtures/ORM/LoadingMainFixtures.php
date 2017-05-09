<?php

namespace  LaPoiz\WindBundle\DataFixtures\ORM;
 
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use LaPoiz\WindBundle\Entity\SpotParameter;
use LaPoiz\WindBundle\Entity\WebSite;
use LaPoiz\WindBundle\Entity\Spot;
use LaPoiz\WindBundle\Entity\Region;
use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadingMainFixtures extends AbstractFixture implements OrderedFixtureInterface
{

    public function getOrder()
    {
        return 1;
    }

	/**
	 * {@inheritDoc}
	 */
    public function load(ObjectManager $manager)
    {

        // ************************
        // **** Site Internet *******
        // ************************

        $webSiteWG = new WebSite();
        $webSiteWG->setNom(WebsiteGetData::windguruName);
        $webSiteWG->setUrl("www.windguru.cz");
        $webSiteWG->setLogo("Windguru.png");
        $manager->persist($webSiteWG);
        
        $webSiteWGPro = new WebSite();
        $webSiteWGPro->setNom(WebsiteGetData::windguruProName);
        $webSiteWGPro->setUrl("www.windguru.cz");
        $webSiteWGPro->setLogo("WindguruPro.png");
        $manager->persist($webSiteWGPro);
        
        $webSiteWF = new WebSite();
        $webSiteWF->setNom(WebsiteGetData::windFinderName);
        $webSiteWF->setUrl("www.windfinder.com");
        $webSiteWF->setLogo("Windfinder.png");
        $manager->persist($webSiteWF);
        
        $webSiteMF = new WebSite();
        $webSiteMF->setNom(WebsiteGetData::meteoFranceName);
        $webSiteMF->setUrl("http://france.meteofrance.com/");
        $webSiteMF->setLogo("MeteoFrance.png");
        $manager->persist($webSiteMF);
        
        $webSiteAS = new WebSite();
        $webSiteAS->setNom(WebsiteGetData::alloSurfName);
        $webSiteAS->setUrl("http://www.allosurf.net/");
        $webSiteAS->setLogo("AlloSurf.png");
        $manager->persist($webSiteAS);

        $webSiteMC = new WebSite();
        $webSiteMC->setNom(WebsiteGetData::meteoConsultName);
        $webSiteMC->setUrl("http://marine.meteoconsult.fr");
        $webSiteMC->setLogo("Meteoconsult.jpg");
        $manager->persist($webSiteMC);

        $webSiteMer = new WebSite();
        $webSiteMer->setNom(WebsiteGetData::merteoName);
        $webSiteMer->setUrl("http://www.merteo.fr");
        $webSiteMer->setLogo("Merteo.png");
        $manager->persist($webSiteMer);

        $manager->flush();
/*
        $this->addReference('webSiteWG', $webSiteWG);
        $this->addReference('webSiteWGPro', $webSiteWGPro);
        $this->addReference('webSiteWF', $webSiteWF);
        $this->addReference('webSiteMF', $webSiteMF);
        $this->addReference('webSiteAS', $webSiteAS);
        $this->addReference('webSiteMC', $webSiteMC);
        $this->addReference('webSiteMer', $webSiteMer);
*/
        /*$balise=new Balise();
        $balise->setNom('Balise FFVL de St Aubin');
        $balise->setUrl('http://balisemeteo.com/balise.php?idBalise=56');
        $balise->setDescription('Balise de la Federation Francais de Vol Libre');
        */


        // ************************
        // **** Région ************
        // ************************

        $regionPDC = new Region();
        $regionPDC->setNom("Pas de calais");
        $regionPDC->setDescription("Dans le grand nord.");
        $regionPDC->setNumDisplay(1);
        $manager->persist($regionPDC);

        $regionS = new Region();
        $regionS->setNom("Somme");
        $regionS->setDescription("De ... à ...");
        $regionS->setNumDisplay(2);
        $manager->persist($regionS);

        $regionC = new Region();
        $regionC->setNom("Calvados");
        $regionC->setDescription("De ... à ...");
        $regionC->setNumDisplay(3);
        $manager->persist($regionC);

        $regionSM = new Region();
        $regionSM->setNom("Seine-Maritime");
        $regionSM->setDescription("Du Havre jusqu'après Dieppe.");
        $regionSM->setNumDisplay(4);
        $manager->persist($regionSM);

        $regionPA = new Region();
        $regionPA->setNom("Provence-Alpes-Cote d'Azur");
        $regionPA->setDescription("Soleil et parfois vent.");
        $regionPA->setNumDisplay(10);
        $manager->persist($regionPA);

        $manager->flush();
/*
        $this->addReference('regionSM', $regionSM);
        $this->addReference('regionPA', $regionPA);
*/
        }
}