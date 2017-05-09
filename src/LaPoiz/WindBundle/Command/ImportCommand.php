<?php	

namespace LaPoiz\WindBundle\Command;

use Doctrine\ORM\EntityManager;
use LaPoiz\WindBundle\core\maree\MareeGetData;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;
use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\Entity\InfoSpot;
use LaPoiz\WindBundle\Entity\MareeRestriction;
use LaPoiz\WindBundle\Entity\Spot;
use LaPoiz\WindBundle\Entity\WindOrientation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class ImportCommand extends ContainerAwareCommand  {

 	protected function configure()
    {
        $this
            ->setName('lapoiz:import')
            ->setDescription('Import data from csv file (web/uploads/import/data.csv)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<comment>Utilise le fichier : web/uploads/import/data.csv </comment>');
        $output->writeln('<comment>Ne pas avoir de saut de ligne dans les cellules (description ...)</comment>');

        // Showing when the script is launched
        $now = new \DateTime();
        $output->writeln('<comment>Start : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');

        // Importing CSV on DB via Doctrine ORM
        $this->import($input, $output, $this->getContainer()->get('doctrine.orm.entity_manager'));

        // Showing when the script is over
        $now = new \DateTime();
        $output->writeln('<comment>End : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');
    }

    static function import(InputInterface $input, OutputInterface $output,EntityManager $em)
    {
        // Getting php array of data from CSV
        $data = ImportCommand::get($input, $output);

        if ($data != null) {
            // Turning off doctrine default logs queries for saving memory
            $em->getConnection()->getConfiguration()->setSQLLogger(null);

            $tabRegions = ImportCommand::getTabRegions($output, $em);
            $tabWebsites = ImportCommand::getTabWebsites($output, $em);

            $size = count($data);
            $batchSize = 10; // tout les 20 elements on enregistre
            $i = 1;

            // Starting progress
            $progress = new ProgressBar($output, $size);
            $progress->start();

            // Processing on each row of data
            foreach ($data as $row) {
                try {

                    ImportCommand::importRow($row, $output, $em, $tabRegions, $tabWebsites);

                    if (($i % $batchSize) === 0) {
                        $em->flush();
                        // Detaches all objects from Doctrine for memory save
                        //$em->clear();
                    }

                    // Advancing for progress display on console
                    $progress->advance(1);
                } catch (\Exception $e) {
                    $output->writeln('******');
                    $output->writeln('<error>' . $e->getMessage() . '</error>');
                    $output->writeln('******');
                }
                $i++;
            }

            // Flushing and clear data on queue
            $em->flush();
            $em->clear();

            // Ending the progress bar process
            $progress->finish();
        }
    }

    /**
     * @param $row : avec les colonnes: Nom - Region - Description - Localisation - Long - Lat - Windfinder -
     * @param OutputInterface $output
     * @param EntityManager $em
     * @param $tabRegions : tableau des régions
     * @param $tabWebsites : tableau des sites
     */
    static function importRow($row, OutputInterface $output, EntityManager $manager, $tabRegions, $tabWebsites) {

        if (!empty($row['Nom'])) {
            $output->writeln('');
            $output->writeln('<comment>Spot : ' . $row['Nom'] . ' </comment>');
            $spot = new Spot();
            $spot->setNom($row['Nom']);
            $spot->setDescription(ImportCommand::cleanDescription($row['Description']));
            $spot->setLocalisationDescription(ImportCommand::cleanDescription($row['Localisation']));
            $spot->setGpsLong($row['Long']);
            $spot->setGpsLat($row['Lat']);
            $spot->setIsValide(true);


            $webSiteWG = $tabWebsites[WebsiteGetData::windguruName];
            //$webSiteWGPro=$tabWebsites['webSiteWGPro'];
            $webSiteWF = $tabWebsites[WebsiteGetData::windFinderName];
            $webSiteMF = $tabWebsites[WebsiteGetData::meteoFranceName];
            $webSiteAS = $tabWebsites[WebsiteGetData::alloSurfName];
            $webSiteMC = $tabWebsites[WebsiteGetData::meteoConsultName];
            $webSiteMer = $tabWebsites[WebsiteGetData::merteoName];

            $region = $tabRegions[$row['Region']];

            $dataWindPrevWG = new DataWindPrev();
            $dataWindPrevWG->setUrl($row['Windguru']);
            $dataWindPrevWG->setWebsite($webSiteWG);
            $dataWindPrevWG->setCreated(new \DateTime("now"));

            $dataWindPrevWF = new DataWindPrev();
            $dataWindPrevWF->setUrl($row['Windfinder']);
            $dataWindPrevWF->setWebsite($webSiteWF);
            $dataWindPrevWF->setCreated(new \DateTime("now"));

            $dataWindPrevMF = new DataWindPrev();
            $dataWindPrevMF->setUrl($row['Meteo France']);
            $dataWindPrevMF->setWebsite($webSiteMF);
            $dataWindPrevMF->setCreated(new \DateTime("now"));

            $dataWindPrevMC = new DataWindPrev();
            $dataWindPrevMC->setUrl($row['MeteoConsult']);
            $dataWindPrevMC->setWebsite($webSiteMC);
            $dataWindPrevMC->setCreated(new \DateTime("now"));

            $dataWindPrevAS = new DataWindPrev();
            $dataWindPrevAS->setUrl($row['AlloSurf']);
            $dataWindPrevAS->setWebsite($webSiteAS);
            $dataWindPrevAS->setCreated(new \DateTime("now"));

            $dataWindPrevMer = new DataWindPrev();
            $dataWindPrevMer->setUrl($row['Merteo']);
            $dataWindPrevMer->setWebsite($webSiteMer);
            $dataWindPrevMer->setCreated(new \DateTime("now"));

            $dataWindPrevWG->setSpot($spot);
            $dataWindPrevWF->setSpot($spot);
            $dataWindPrevMF->setSpot($spot);
            $dataWindPrevMC->setSpot($spot);
            $dataWindPrevAS->setSpot($spot);
            $dataWindPrevMer->setSpot($spot);
            $spot->addDataWindPrev($dataWindPrevMF);
            $spot->addDataWindPrev($dataWindPrevWG);
            $spot->addDataWindPrev($dataWindPrevWF);
            $spot->addDataWindPrev($dataWindPrevMC);
            $spot->addDataWindPrev($dataWindPrevAS);
            $spot->addDataWindPrev($dataWindPrevMer);

            $region->addSpot($spot);
            $spot->setRegion($region);

            // Put WindOrientation
            ImportCommand::getWindOrientation($spot, $row, $manager);

            if (!empty($row['Maree'])) {
                $spot->setMareeURL($row['Maree']);

                ImportCommand::getExtremMaree($spot, $row, $manager);
                // Put marrée restriction
                ImportCommand::getMareeRestriction($spot, $row, $manager);
            }
            $spot->setTempWaterURL($row['Temp eau']);

            // Put info sur le site
            ImportCommand::getInfoURL($spot, $row, $manager);


            $manager->persist($dataWindPrevWG);
            $manager->persist($dataWindPrevWF);
            $manager->persist($dataWindPrevMF);
            $manager->persist($dataWindPrevMC);
            $manager->persist($dataWindPrevAS);
            $manager->persist($dataWindPrevMer);
            $manager->persist($spot);
            $manager->persist($region);
        }
    }

    static function getTabRegions(OutputInterface $output, EntityManager $em) {
        $result = array();
        $regionList = $em->getRepository('LaPoizWindBundle:Region')->findAll();

        foreach ($regionList as $region) {
            $result[$region->getNom()] = $region;
        }
        return $result;
    }

    static function getTabWebsites(OutputInterface $output, EntityManager $em) {
        $result = array();
        $websiteList = $em->getRepository('LaPoizWindBundle:WebSite')->findAll();

        foreach ($websiteList as $website) {
            $result[$website->getNom()] = $website;
        }
        return $result;
    }

    static function get(InputInterface $input, OutputInterface $output)
    {
        try {
            $data=ImportCommand::csv_to_array();

            return $data;
        } catch (\Exception $e) {
            $output->writeln('<error>***** Erreur *******</error>');
            $output->writeln('<error>Impossible de convertir le fichier en tableau</error>');
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            $output->writeln('<error>********************</error>');
            return null;
        }
    }

    /**
     * Convert a comma separated file into an associated array.
     * The first row should contain the array keys.
     *
     * Example:
     *
     * @param string $filename Path to the CSV file
     * @param string $delimiter The separator used in the file
     * @return array
     * @link http://gist.github.com/385876
     * @author Jay Williams <http://myd3.com/>
     * @copyright Copyright (c) 2010, Jay Williams
     * @license http://www.opensource.org/licenses/mit-license.php MIT License
     */
    static function csv_to_array($filename='', $delimiter=';')
    {
        $filename='web/uploads/import/data.csv';
        if(!file_exists($filename))
            $filename='../web/uploads/import/data.csv';
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 10000, $delimiter)) !== FALSE)
            {
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    static function cleanDescription($desc)
    {
        // Enléve les <br /> pour mettre à la ligne
        $desc = str_replace('<br />', '\u240D', $desc);
        $desc=htmlentities($desc);
        $desc = str_replace('\u240D', '<br />' , $desc);
        return $desc;
    }

    static function getWindOrientation(Spot $spot, $row, EntityManager $manager)
    {
        $orientationArray = array('n','nne','ne','ene','e', 'ese','se', 'sse', 's', 'ssw', 'sw', 'wsw', 'w', 'wnw', 'nw', 'nnw');

        foreach ($orientationArray as $orientationName)
        {
            $windOrientation = new WindOrientation();
            $windOrientation->setSpot($spot);
            $windOrientation->setOrientation($orientationName);
            // "OK", "KO", "warn"
            $windOrientation->setState(ImportCommand::getOrientationState($row[$orientationName]));

            $manager->persist($spot);
            $manager->persist($windOrientation);
        }
    }
    // -1, 0, 1, 2, ? => "OK", "KO", "warn"
    static function getOrientationState($rowValue) {
        switch ($rowValue) {
            case -1:
                return 'KO';
            case 0:
                return 'warn';
            case 1:
                return 'OK';
            case 2:
                return 'OK';
        }
        return 'KO'; // ? ou autre
    }

    // "OK", "KO", "warn"
    static function getMareeRestriction(Spot $spot, $row, EntityManager $manager)
    {
        $marreRestrictionArray = array('OK','warn','KO');

        foreach ($marreRestrictionArray as $mareeState) {
            if (!empty($row[$mareeState])) {
                $marreRestriction = new MareeRestriction();
                $marreRestriction->setSpot($spot);
                $marreRestriction->setState($mareeState); // "OK", "KO", "warn"

                // Récupére les valeurs max/min depuis le texte: "2->10,5"
                preg_match('#([0-9,.]*)->([0-9,.]*)#',$row[$mareeState],$data);
                $marreRestriction->setHauteurMax(str_replace(',','.',$data[2]));
                $marreRestriction->setHauteurMin(str_replace(',','.',$data[1]));
                $spot->addMareeRestriction($marreRestriction);

                $manager->persist($spot);
                $manager->persist($marreRestriction);
            }
        }
    }

    static function getExtremMaree(Spot $spot, $row, EntityManager $manager) {
        preg_match('#http://maree.info/([0-9]*)#',$row['Maree'],$data);
        $idSpotMareeInfo=$data[1];
        $tabDataExtrem = MareeGetData::getExtremMaree($idSpotMareeInfo);
        // exemple: http://windserver3/web/app_dev.php/admin/BO/ajax/maree/dateCoef/150

        $idDateUrl=$tabDataExtrem['max']['idDateUrl'];
        list($hauteurMin,$hauteurMax)=ImportCommand::getHminHmaxMaree($idSpotMareeInfo, $idDateUrl);
        $spot->setHauteurMBGrandeMaree($hauteurMin);
        $spot->setHauteurMHGrandeMaree($hauteurMax);
        $manager->persist($spot);

        $idDateUrl=$tabDataExtrem['min']['idDateUrl'];
        list($hauteurMin,$hauteurMax)=ImportCommand::getHminHmaxMaree($idSpotMareeInfo, $idDateUrl);
        $spot->setHauteurMBPetiteMaree($hauteurMin);
        $spot->setHauteurMHPetiteMaree($hauteurMax);
        $manager->persist($spot);

        $idDateUrl=$tabDataExtrem['norm']['idDateUrl'];
        list($hauteurMin,$hauteurMax)=ImportCommand::getHminHmaxMaree($idSpotMareeInfo, $idDateUrl);
        $spot->setHauteurMBMoyenneMaree($hauteurMin);
        $spot->setHauteurMHMoyenneMaree($hauteurMax);
        $manager->persist($spot);
    }

    static function getHminHmaxMaree($idSpotMareeInfo, $idDateUrl){
        $tabData = MareeGetData::getHauteurMaree($idSpotMareeInfo,$idDateUrl);
        // exemple: http://windserver3/web/app_dev.php/admin/BO/ajax/maree/150/forDay/20170808?_=1494277199274
        $hauteurMax=0;
        $hauteurMin=20;
        foreach ($tabData as $hauteurStr)
        {
            preg_match('#([0-9.]*)m#',$hauteurStr,$data);
            $hauteur=floatval($data[1]);
            $hauteurMax = $hauteurMax > $hauteur ? $hauteurMax : $hauteur;
            $hauteurMin = $hauteurMin < $hauteur ? $hauteurMin : $hauteur;
        }
        return array($hauteurMin,$hauteurMax);
    }

    static function getInfoURL(Spot $spot, $row, EntityManager $manager) {

            for ($i=1;$i<5;$i++) {
                if (!empty($row['URL '.$i])) {
                    $infoSpot = new InfoSpot();
                    $infoSpot->setSpot($spot);
                    $infoSpot->setUrl($row['URL '.$i]);

                    if (!empty($row['Titre '.$i])) {
                        $infoSpot->setTitre($row['Titre '.$i]);
                    }
                    if (!empty($row['Commentaire '.$i])) {
                        $infoSpot->setCommentaire($row['Commentaire '.$i]);
                    }

                    $spot->addInfoSpot($infoSpot);

                    $manager->persist($spot);
                    $manager->persist($infoSpot);
                }
        }
    }
}
