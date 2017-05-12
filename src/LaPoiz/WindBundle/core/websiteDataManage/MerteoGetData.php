<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\Prevision;
use Symfony\Component\DomCrawler\Crawler;

class MerteoGetData extends WebsiteGetData
{
	const goodSectionId =   '#example-datatables'; // <table id="example-datatables" class="table table-striped table-bordered table-hover tablefloat responsive">
    const updateClass =     'span[class="calculprevisions"]';// <span class="calculprevisions" data-step="5" [...]>
    const weekendClass =     'weekend';        // <tr class="weekend">
    const semaineClass =     'semaine';        // <tr class="semaine">
    const dateClass =        'span[class="jourinfodate"]';   // <span class="jourinfodate">Dimanche 9</span>
    const interligneClass =  'interligne';     // <tr class="interligne">
    

	/**
	 * @param $pageHTML: crawler de Goutte
	 * @param $url: URL de la page
	 * @return array|null
	 */
	function analyseData($pageHTML,$url)	{
        /*
        $fp = fopen('merteo_HTML.html', 'w'); // save on web directory
        fwrite($fp, $pageHTML->html());
        fclose($fp);
        */
		$previsionTab = array();

		if (empty($pageHTML)) {
			return null;
		} else {
            $tableHTML=$pageHTML->filter(MerteoGetData::goodSectionId);
            if (empty($tableHTML)){
                echo '<br />Element not find is section id="'.MerteoGetData::goodSectionId.'" ... Merteo change the page ?<br />';
            } else {
                // Récupére: <span class="calculprevisions" [...]>PrÃ&copy;visions mÃ&copy;tÃ&copy;o de Almanarre calculÃ&copy;es le 08/04/2017 Ã&nbsp; 08h39</span>
                $previsionTab['lastUpdate']=array(array($pageHTML->filter(MerteoGetData::updateClass)->text()));

                $tr_elements = $tableHTML->filter('tr');
                $beginDay=false;
                $date=null;
                $previsionDateTab=null;
                $shiftWithMarree=0; // spot avec marrée => shift=0 , Spot sans marrée => shift=1 (une colonne de plus)

                foreach ($tr_elements as $tr) {
                    if ($beginDay) {
                        if ($tr->getAttribute('class')==MerteoGetData::interligneClass) {
                                // ferme la journée
                                $beginDay = false;
                                $previsionTab[$date] = $previsionDateTab;
                        } else {
                            $previsionHoureTab = array();
                            $td_elements = $tr->childNodes;

                            // 1er TD l'heure: <td>0h</td>
                            $previsionHoureTab['houre'] = $td_elements->item(0)->nodeValue;

                            // Dans un des TD du type: <td class="datacentre" [...] </td> récupérer la force du vent: <span class="vitesse nds">4nds</span>
                            $previsionHoureTab['wind'] = $td_elements->item(7+$shiftWithMarree)->nodeValue;

                            // TD suivant récupérer le titre de l'image: <div class="vent459"><img src="lib/site/img/trans.gif" title="W (279°)" width="15" height="14"></div>
                            $previsionHoureTab['orientation'] = "?";

                                // Si le TD suivant n'est pas : <td class="datacentre" colspan="3">
                            // TD suivant le vent: <td class="datacentre" style="background-color:#b0b1fc !important">3nds</td>
                            // TD suivant encore le titre de l'image
                            // TD suivant encore le vent (rafalle)
                            $previsionDateTab[] = $previsionHoureTab;
                        }
                    } elseif ($tr->getAttribute('class')==MerteoGetData::weekendClass || $tr->getAttribute('class')==MerteoGetData::semaineClass) {
                        $beginDay=true;
                        // TR du jour => récupere la date
                        $previsionDateTab = array();
                        // On récupére le jour : <td class="datadatejour" colspan="20"><span class="jourinfodate">Dimanche 9</span> [...]</td>
                        //$day=$tr->filter(MerteoGetData::dateClass)->getNode(0)->nodeValue;
                        $date=$tr->childNodes->item(0)->nodeValue;
                        $shiftWithMarree=$tr->childNodes->item(0)->getAttribute('colspan')=='20'?0:1;
                    }
                }
                $previsionTab[$date] = $previsionDateTab;
            }
            return $previsionTab;
		}
	}


    function transformData($tableauData) {
        $cleanTabData = array();

        $day = new \DateTime('NOW');
        foreach ($tableauData as $date => $lineDayData) {
            // 1 line = 1 date
            if ($date!='lastUpdate') {
                $cleanElemDay = array();

                foreach ($lineDayData as $key => $colHoure) {
                    // 1 lineHoure = 1 hour
                    $cleanElemHoure = array();

                    $cleanElemHoure['heure'] = MerteoGetData::getHoureClean($colHoure["houre"]);
                    $cleanElemHoure['wind'] = MerteoGetData::getWindClean($colHoure['wind']);
                    $cleanElemHoure['orientation'] = $colHoure['orientation'];
                    /*
                    $cleanElemHoure['maxWind'] = $lineHoure['windMax'];
                    $cleanElemHoure['temp'] = $lineHoure['temp'];
                    $cleanElemHoure['orientation'] = MerteoGetData::getOrientationClean($lineHoure['orientation']);
                    */
                    $cleanElemDay[] = $cleanElemHoure;
                }
                $datePrev = MerteoGetData::getDateClean($date);
                $cleanTabData[$datePrev] = $cleanElemDay;
            } else { // lastUpdate
                $cleanTabData['update'] = array(array(MerteoGetData::getLastUpdateClean($lineDayData[0][0])));
            }
        }

        return $cleanTabData;
    }

    // 08h
    static private function getHoureClean($htmlData) {
        preg_match('#([0-9]*)h#',$htmlData,$data);
        return $data[1];
    }

    // 11nds
    static private function getWindClean($htmlData) {
        preg_match('#([0-9]*)nds#',$htmlData,$data);
        return $data[1];
    }

    // $htmlData: Mercredi 12 Lever du soleil : 06h00 - Coucher du soleil : 19h11 - St Stanislas
    // return: 2017-avril-12
    static private function getDateClean($htmlData) {
        //$this->get('logger')->err('htmlDate:'+$htmlData);
        //$htmlData = trim($htmlData);
        //$htmlData=mb_ereg_replace("[[:blank:]]","",$htmlData);
        //$toto=preg_split('/\n/',$htmlData);
        //$htmlData=$toto[0];
        preg_match('#([0-9]{2})#',$htmlData,$data);

        if ($data[1]>=date('d'))
            return date("Y-m-").$data[1];
        elseif (date('m')<=11)
            return date("Y-").(date('m')+1).'-'.$data[1];
        else
            return (date("Y")+1).'-01-'.$data[1];
    }

    static function typeDisplay($step) {
        $result="text";
        switch ($step) {
            case 10: $result="text";break; // display error texte
            case 0: $result="text";break;
            case 1: $result="code";break;
            case 2: $result="arrayOfArrayOfArray";break;
            case 3: $result="arrayOfArrayOfArray";break;
            case 4: $result="prevDate";break;
        }
        return $result;
    }


    /**
     * @param $lastUpdateHTML du type "0: Prévisions météo de Almanarre calculées le 12/04/2017 à 14h26(Nouveau calcul en cours : Etape 1/6 - Récupération des données)"
     * @return 2017-04-12 14:26:00
     */
    private function getLastUpdateClean($lastUpdateHTML) {

        try {
            preg_match('#([0-9]+)/([0-9]+)/([0-9]+)#', $lastUpdateHTML, $dataDate);
//            $lastUpdateDT = new \DateTime($dataDate[3]+"-"+$dataDate[2]+"-"+$dataDate[1]);
            preg_match('#([0-9]+)h([0-9]+)#', $lastUpdateHTML, $dataHoure);
//            $lastUpdateDT->setTime($dataHoure[1], $dataHoure[2]);

//            return $lastUpdateDT->format('Y-m-d H:i:s');
            return $dataDate[3]+"-"+$dataDate[2]+"-"+$dataDate[1]+" "+$dataHoure[1]+":"+$dataHoure[2]+":00";
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param $resulGetData: resultat de la fonction getDataURL, ici Crawler
     * @return ce qu'on affiche
     */
    static function displayGetData($resultGetDataURL) {
        return $resultGetDataURL->text();
    }
}