<?php

namespace LaPoiz\WindBundle\core\nbHoure;


use LaPoiz\WindBundle\Command\CreateNbHoureCommand;
use LaPoiz\WindBundle\core\maree\MareeTools;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

class NbHoureNav {

    const HEURE_MATIN = 8;
    const HEURE_SOIR = 20;

    /**
     * @param $spot :spot dont il est question
     * @param $em: Entity Manager, pour utiliser doctrine
     * @return $tabNbHoure : tableau contenant une date par ligne, puis une colonne pour chaque heure ouverte à la nav :
     *      - l'état des marées,
     *      - les prévisions
     */
    static function createTabNbHoureNav($spot, $em) {

        $tabDataNbHoureNav = array();
        $tabDataMeteo = array();
        // Pour les 7 prochains jours
        $day= new \DateTime("now");
        for ($nbPrevision=0; $nbPrevision<7; $nbPrevision++) {
            $tabHoure=array();
            for ($houre=CreateNbHoureCommand::HEURE_MATIN;$houre<CreateNbHoureCommand::HEURE_SOIR;$houre++) {
                $tabHoure[$houre]=array();
            }
            $tabDataNbHoureNav[$day->format('Y-m-d')]=$tabHoure;
            $tabDataMeteo[$day->format('Y-m-d')]=array();
            $day->modify('+1 day');
        }

        //********** Marée **********
        // récupére les futures marées
        $listeMareeFuture = $em->getRepository('LaPoizWindBundle:MareeDate')->getFuturMaree($spot);
        foreach ($listeMareeFuture as $mareeDate) {
            // Pour chaque jour, calculer pour chaque heure le temps de navigation
            MareeTools::calculNbHoureNav($spot, $tabDataNbHoureNav[$mareeDate->getDatePrev()->format('Y-m-d')], $mareeDate);
        }

        //********** Wind ***********
        // Récupere toutes les futures prévisions de vent pour le spot
        $listALlPrevisionDate = $em->getRepository('LaPoizWindBundle:PrevisionDate')->getPrevDateAllWebSiteNextDays($spot);
        $tabAllPrevisionDate=array();
        // Construit un tableau ayant des cellules contenant toutes les previsions des differents sites par jour pour le spot
        foreach ($listALlPrevisionDate as $previsionDate) {
            $tabAllPrevisionDate[$previsionDate->getDatePrev()->format('Y-m-d')][]=$previsionDate;
        }
        // $tabAllPrevisionDate with data

        foreach ($tabDataNbHoureNav as $keyDate=>$toto) {
            if (array_key_exists($keyDate,$tabAllPrevisionDate)) {
                //$noteDate = ManageNote::getNotesDate($spot,\DateTime::createFromFormat('Y-m-d',$keyDate), $em);
                NbHoureWind::calculNbHoureNav($spot, $tabDataNbHoureNav[$keyDate], $tabAllPrevisionDate[$keyDate]);
            }
        }

        //********** Meteo ***********
        // Récupere la météo (T°C, nuage ....) pour le spot qui sera utilisé pour noter l'agréabilité
        foreach ($tabDataNbHoureNav as $keyDate=>$toto) {
            if (array_key_exists($keyDate,$tabAllPrevisionDate)) {
                foreach ($tabAllPrevisionDate[$keyDate] as $previsionDate) {
                    // pour chaque website = $previsionDate (même spot, même jour)

                    if ($previsionDate->getDataWindPrev()->getWebSite()->getNom() == WebsiteGetData::meteoFranceName) {
                        foreach ($previsionDate->getListPrevision() as $prevision) {
                            $houre = intval($prevision->getTime()->format('H'));
                            if (NbHoureWind::isInGoodTime($houre)) {
                                $tabDataMeteo[$keyDate][$houre] = array();
                                $tabDataMeteo[$keyDate][$houre]["temp"]=$prevision->getTemp();
                                $tabDataMeteo[$keyDate][$houre]["meteo"]=$prevision->getMeteo();
                                $tabDataMeteo[$keyDate][$houre]["precipitation"]=$prevision->getPrecipitation();
                            }
                        }
                    }
                }
            }
        }

        return array($tabDataNbHoureNav,$tabDataMeteo);
    }

    /**
     * @param $tabNbHoureNavData: tableau provenant de createTabNbHoureNav
     *          [aaaa-mm-dd]
     *              [8] 
     *                  [maree] = 1 ou 0
     *                  [WindFinder]
     *                      orientation = "OK" ou "KO" ou "warn"
     *                      wind = 13
     *                      timeNav = 0.5
     *                  [MeteoConsult]
     *                  [AlloSurf]
     *                  ...
     *              [9] = array
     *              ...
     *              [19] = array
     * @return tableau du nombre de nav en fonction des sites de prévision
     */
    static function calculateNbHourNav($tabNbHoureNavData) {
        $isMarée=false;
        $tabNbHoureNav=array();

        foreach ($tabNbHoureNavData as $date => $jourTab) {
            $tabNbHoureNav[$date]=array(); // date aaa-mm-jj
            $tabSitePrev=array();
            foreach ($jourTab as $houre => $hourTab) {
                if (sizeof($hourTab)>1) {
                    // pas uniquement la marée
                    foreach ($hourTab as $key => $data) {
                        if ($key != "marée") {
                            // $data => tab de prevision avec orientation et wind
                            if (!array_key_exists($key,$tabSitePrev)) {
                                // inexistant dans le tableau des websites de prevision
                                // => 1er valeur du jour $date
                                $tabSitePrev[$key]=array();

                                if ($houre > NbHoureNav::HEURE_MATIN) {
                                    // Premiere heure prevision n'est pas celle du début de la journée de kite -> on met celle de la premiere heure de prev
                                    $prevHour=NbHoureNav::HEURE_MATIN;
                                    $prevWind=$data["wind"];
                                    $prevOrientation=$data["orientation"];
                                    NbHoureNav::calculateNbHoureWindBetween2Houres($tabSitePrev[$key],$prevHour,$prevOrientation,$prevWind,$houre,$data["orientation"],$data["wind"]);
                                }
                            } else {
                                // Passage normal hors la 1er heure de la journée
                                $prevHour=$tabSitePrev[$key]["prevHoure"];
                                $prevWind=$tabSitePrev[$key]["prevWind"];
                                $prevOrientation=$tabSitePrev[$key]["prevOrientation"];
                                NbHoureNav::calculateNbHoureWindBetween2Houres($tabSitePrev[$key],$prevHour,$prevOrientation,$prevWind,$houre,$data["orientation"],$data["wind"]);
                            }
                            NbHoureNav::setPreviousData($tabSitePrev[$key],$houre,$data["wind"],$data["orientation"]);
                        } else {
                            $isMarée=true;
                        }
                    }
                }
            }
            // $tabSitePrev est remplis des nb Heures de nav en fonction du vent (force et orientation) pour chaque website renseigné
            // $tabSitePrev =
            //      [WindFinder]
            //          [8] = 1
            //          [9] = 0.5
            //          [10]= 0.5
            //          [11]= 0
            //          ...
            //          [19]= 0
            //      [MeteoConsult]
            //      ...

            if ($isMarée) {
                // Il y a des contraintes de marée
                // Il faut les croiser avec les prev de vent
                foreach ($jourTab as $houre => $hourTab) {
                    //$coefMarée=array_key_exists("marée",$hourTab)?$hourTab["marée"]:1; // A virer une fois l'erreur de datePrev des maree trouvé
                    $coefMarée=$hourTab["marée"];
                    foreach ($tabSitePrev as $webSiteKey => $hourTab) {
                        if (!array_key_exists($webSiteKey,$tabNbHoureNav[$date])) {
                            $tabNbHoureNav[$date][$webSiteKey]=0;
                        }
                        if (!array_key_exists($houre,$tabSitePrev[$webSiteKey])) {
                            // si dernier prev n'est pas à NbHoureNav::HEURE_SOIR => remplir avec derniere valeur enregistré
                            $tabSitePrev[$webSiteKey][$houre]=end($tabSitePrev[$webSiteKey]);
                        }
                        $tabNbHoureNav[$date][$webSiteKey]+=$coefMarée*$tabSitePrev[$webSiteKey][$houre];
                    }
                }
            } else {
                // Pas de contrainte de marée
                for ($numHour=NbHoureNav::HEURE_MATIN;$numHour<=NbHoureNav::HEURE_SOIR;$numHour++) {
                    foreach ($tabSitePrev as $webSiteKey => $hourTab) {
                        if (!array_key_exists($webSiteKey,$tabNbHoureNav[$date])) {
                            $tabNbHoureNav[$date][$webSiteKey]=0;
                        }
                        if (!array_key_exists($houre,$tabSitePrev[$webSiteKey])) {
                            // si dernier prev n'est pas à NbHoureNav::HEURE_SOIR => remplir avec derniere valeur enregistré
                            $tabSitePrev[$webSiteKey][$houre]=end($tabSitePrev[$webSiteKey]);
                        }
                        $tabNbHoureNav[$date][$webSiteKey]+=$tabSitePrev[$webSiteKey][$houre];
                    }
                }
            }
        }
        return $tabNbHoureNav;
    }

    private static function setPreviousData(&$tab, $houre, $wind, $orientation) {
        $tab["prevHoure"]=$houre;
        $tab["prevWind"]=$wind;
        $tab["prevOrientation"]=$orientation;
    }

    /**
     * @param $precHoure : heure précédente
     * @param $precOrientation: orientation precedente
     * @param $precWind : vent precedent
     * @param $houre
     * @param $orientation
     * @param $wind
     * remplis le tableau pour toutes les heures entre prevHoure et houre avec le nbHoure de nav en fonction du vent (puissance + orientation)
     */
    private static function calculateNbHoureWindBetween2Houres(&$tab1SitePrev,$precHoure,$precOrientation,$precWind,$houre,$orientation,$wind) {
        // Projection sur une ligne droite Y = precValue + (value-precValue)/diffHoure * X
        // y : value pour x
        // x : l'heure - $precHoure

        $valuePrecOrientation = $precOrientation=="OK"?1:($precOrientation=="KO"?0:0.5);
        $valueOrientation = $orientation=="OK"?1:($orientation=="KO"?0:0.5);

        $diffHoure=$houre-$precHoure;
        $diffOrientation=$valueOrientation-$valuePrecOrientation;
        $diffWind=$wind-$precWind;
        for ($numHour=0;$numHour<=($houre-$precHoure);$numHour++) { // pour chaque heure entre precHoure et houre
            //Calcul le coef orientation (0 à 1) et lle vent à l'heure: $precHoure + $numHoure
            $coefOrientation=$valuePrecOrientation+$numHour*$diffOrientation/$diffHoure;
            $windAtH=$precWind+$numHour*$diffWind/$diffHoure;

            if ($windAtH<=WebsiteGetData::windPowerMin) {
                // pas assez de vent
                $tab1SitePrev[$precHoure+$numHour]=0;
            } elseif ($windAtH<=WebsiteGetData::windPowerMinFun) {
                // juste assez de vent mais pas fun => check l'orientation
                if ($coefOrientation<0.75) {
                    // si orientation bof + vent limite => ~0 de temps de nav durant l'heure
                    $tab1SitePrev[$precHoure+$numHour]=0;
                } else {
                    // si bonne orientation + vent limite => ~50% de temps de nav durant l'heure
                    $tab1SitePrev[$precHoure+$numHour]=0.5;
                }
            } else {
                // assez de vent pour s'éclater
                if ($coefOrientation<0.5) {
                    // mauvaise orientation du vent, mais mais ca souffle => ~50% de temps de nav durant l'heure
                    $tab1SitePrev[$precHoure+$numHour]=0.5;
                } else {
                    // Orientation pas trop mal + pas mal de vent => 100% de temps de nav durant l'heure
                    $tab1SitePrev[$precHoure+$numHour]=1;
                }
            }
        }
    }
}