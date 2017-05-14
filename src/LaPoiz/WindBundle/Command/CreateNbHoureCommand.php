<?php	

namespace LaPoiz\WindBundle\Command;

use Doctrine\ORM\EntityManager;
use LaPoiz\WindBundle\core\nbHoure\NbHoureMeteo;
use LaPoiz\WindBundle\core\nbHoure\NbHoureNav;
use LaPoiz\WindBundle\core\note\NbHoureWind;
use LaPoiz\WindBundle\core\note\ManageNote;


use LaPoiz\WindBundle\core\tempWater\TempWaterGetData;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;


class CreateNbHoureCommand extends ContainerAwareCommand  {

    const HEURE_MATIN = 8;
    const HEURE_SOIR = 20;

 	protected function configure()
    {
        $this
            ->setName('lapoiz:createNbHoure')
            ->setDescription('Calculate numbre of navigation houre for each spot, with data on DB')
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

        $this->calcul($input, $importanteOutput, $infoOutput, $this->getContainer()->get('doctrine.orm.entity_manager'));
    }


    static function calcul(InputInterface $input, OutputInterface $importanteOutput, OutputInterface $infoOutput,EntityManager $em)
    {
        // récupere tous les spots
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();


    	foreach ($listSpot as $spot) {
            $infoOutput->writeln('<info>Note du Spot '.$spot->getNom().' - </info>');

            // On efface les vielles notes (avant aujourd'hui)
            ManageNote::deleteOldData($spot, $em);

            list($tabDataNbHoureNav,$tabDataMeteo)=NbHoureNav::createTabNbHoureNav($spot, $em);
            $tabNbHoureNav=NbHoureNav::calculateNbHourNav($tabDataNbHoureNav);

            $infoOutput->writeln('<info>           *** Nb Heure nav ***</info>');
            // Save nbHoure on spot
            foreach ($tabNbHoureNav as $keyDate=>$tabWebSite) {
                $infoOutput->writeln('<info>' . $keyDate . ': ');
                $nbHoureNavCalc=0;
                $nbSiteCalc=0;
                foreach ($tabWebSite as $keyWebSite=>$nbHoureNav) {
                    $infoOutput->writeln('<info>    '.$keyWebSite.' : '.$nbHoureNav.'</info> ');
                    $noteDates=ManageNote::getNotesDate($spot, \DateTime::createFromFormat('Y-m-d',$keyDate), $em);
                    $nbHoureNavObj=ManageNote::getNbHoureNav($noteDates, $keyWebSite, $em);
                    $nbHoureNavObj->setNbHoure($nbHoureNav);
                    $em->persist($nbHoureNavObj);
                    $em->persist($noteDates);
                    $nbSiteCalc++;
                    if ($nbHoureNav>0) {
                        $nbHoureNavCalc += $nbHoureNav;
                    }
                }
                if ($nbSiteCalc>0) {
                    $infoOutput->writeln('<info>    ' . $keyDate . ' : ' . $nbHoureNavCalc . ' / ' . $nbSiteCalc . ' = ' . ($nbHoureNavCalc / $nbSiteCalc) . '</info> ');
                    $noteDates->setNbHoureNavCalc($nbHoureNavCalc / $nbSiteCalc);
                    $em->persist($noteDates);
                }
            }

            $infoOutput->writeln('<info>           *** Meteo ***</info>');
            // Save meteo
            $tabMeteo=NbHoureMeteo::calculateMeteoNav($tabDataMeteo);
            foreach ($tabMeteo as $keyDate=>$tabMeteoDay) {
                $infoOutput->writeln('<info>   Calcule Meteo of '.$keyDate.'</info> ');
                $noteDates=ManageNote::getNotesDate($spot, \DateTime::createFromFormat('Y-m-d',$keyDate), $em);
                $noteDates->setTempMax($tabMeteoDay["tempMax"]);
                $noteDates->setTempMin($tabMeteoDay["tempMin"]);
                $noteDates->setMeteoBest($tabMeteoDay["meteoBest"]);
                $noteDates->setMeteoWorst($tabMeteoDay["meteoWorst"]);

                $em->persist($noteDates);
            }

            $infoOutput->writeln('<info>           *** T C de l eau ***</info>');
            //********** Température de l'eau **********
            try {
                $tabTempWater = null;
                $tabTempWater = TempWaterGetData::getTempWaterFromSpot($spot, $importanteOutput, $infoOutput );
            } catch (\Exception $e) {
                $importanteOutput->writeln('<warn>'.$e->getMessage().'</warn>');
                $importanteOutput->writeln('<warn> * End Warn * </warn>');
            }

            if ($tabTempWater != null) {
                $currentDay = new \DateTime("now");
                foreach ($tabTempWater as $numJourFromToday => $tempWater) {
                    $noteDates = ManageNote::getNotesDate($spot, clone $currentDay, $em);
                    $noteDates->setTempWater($tempWater);
                    $infoOutput->writeln('<info>* '.$currentDay->format('d-m-Y').':'.$tempWater.'</info>');
                    $em->persist($noteDates);
                    $currentDay = date_add($currentDay, new \DateInterval('P1D')); // Jour suivant
                }
            }
            $infoOutput->writeln('<info>******************************</info>');
    	}
        $em->flush();
    }
}