<?php	

namespace LaPoiz\WindBundle\Command;

use Doctrine\ORM\EntityManager;
use LaPoiz\WindBundle\Entity\DataWindPrev;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

//use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;
use LaPoiz\WindBundle\core\maree\MareeGetData;


class GetDataCommand extends ContainerAwareCommand  {

 	protected function configure()
    {
        $this
            ->setName('lapoiz:getData')
            ->setDescription('Get Data from web site')
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption('info', 'i'),
                    ))
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $importanteOutput=$output;
        $infoOutput=new NullOutput();
        if ($input->getOption('info')) {
            // Affiche le max d'info
            $infoOutput=$output;
            $infoOutput->writeln('<info>++++ Mode Verbose ++++</info>');
        }

		// get list of URL 
    	$em = $this->getContainer()->get('doctrine.orm.entity_manager');
    	$dataWindPrevList = $em->getRepository('LaPoizWindBundle:DataWindPrev')->findAll();
    	
    	foreach ($dataWindPrevList as $dataWindPrev) {
            try {
                GetDataCommand::getDataFromDataWindPrev($dataWindPrev, $importanteOutput, $infoOutput, $em);
            } catch (\Exception $e) {
                $importanteOutput->writeln('<error>'.$e->getMessage().'</error>');
            }
    	}

        // Get Marée
        $spotList = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

        $infoOutput->writeln('<info>************** GET MAREE ****************</info>');
        foreach ($spotList as $spot) {
            if ($spot->getMareeURL()!=null) {
                //$prevMaree = MareeGetData::getMaree($spot->getMareeURL());
                $prevMaree = MareeGetData::getMareeForXDays($spot->getMareeURL(),10, $importanteOutput, $infoOutput);
                MareeGetData::saveMaree($spot, $prevMaree, $em, $importanteOutput, $infoOutput);
            }
        }
    }

    public static function getDataFromDataWindPrev(DataWindPrev $dataWindPrev,OutputInterface $importanteOutput, OutputInterface $infoOutput,EntityManager $em) {
        $infoOutput->write('<info>Spot '.$dataWindPrev->getSpot()->getNom().' - </info>');

        // get each web site
        $infoOutput->writeln('<info>site '.$dataWindPrev->getWebSite()->getNom().' -> '.$dataWindPrev->getUrl().'</info>');

        // Delete old value
        GetDataCommand::deleteOldPrevisionDate($dataWindPrev, $importanteOutput, $infoOutput, $em);

        // save data
        $websiteGetData=WebsiteGetData::getWebSiteObject($dataWindPrev);// return WindguruGetData or MeteoFranceGetData... depend of $dataWindPrev

        $data=$websiteGetData->getDataFromURL($dataWindPrev); // array($result,$chrono)
        $infoOutput->write('<info>    get data: '.$data[1].'</info>');
        $analyse=$websiteGetData->analyseDataFromPage($data[0],$dataWindPrev->getUrl()); // array($result,$chrono)
        $infoOutput->write('<info>    analyse: '.$analyse[1].'</info>');
        $transformData=$websiteGetData->transformDataFromTab($analyse[0]); // array($result,$chrono)
        $infoOutput->write('<info>    transforme data: '.$transformData[1].'</info>');
        $saveData=$websiteGetData->saveFromTransformData($transformData[0],$dataWindPrev,$em); // array(array $prevDate,$chrono)
        $infoOutput->writeln('<info>    save data: '.$saveData[1].'</info>');
        $infoOutput->writeln('<info>******************************</info>');
    }

    /**
     * Efface les anciennes PrevisionDate (antérieur à aujourd'hui)
     */
    private static function deleteOldPrevisionDate(DataWindPrev $dataWindPrev,OutputInterface $importanteOutput, OutputInterface $infoOutput,EntityManager $entityManager) {
        $infoOutput->writeln('<info>****** function deleteOldPrevisionDate ****</info>');
        $today=new \DateTime('now');
        $today->setTime(0, 0, 0);

        foreach ($dataWindPrev->getlistPrevisionDate() as $previsionDate) {
            if ($previsionDate->getDatePrev() < $today) {
                // avant today -> on efface
                try {
                    $entityManager->remove($previsionDate);
                    $infoOutput->writeln('<info>delete $previsionDate->getDatePrev : '.$previsionDate->getDatePrev()->format('Y-m-d H:i:s').'</info>');
                } catch (\Exception $ex) {
                    $importanteOutput->writeln("Exception Found - " . $ex->getMessage());;
                }
            }
        }
        $entityManager->flush();
        $infoOutput->writeln('<info>******</info>');
    }
}