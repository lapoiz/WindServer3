<?php
namespace LaPoiz\WindBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="LaPoiz\WindBundle\Repository\SpotRepository")
 * @ORM\Table(name="windServer_Spot")
 */
class Spot 
{
    /**
     * @ORM\GeneratedValue (strategy="AUTO")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string",length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=3)
     */    
    private $nom;
    
    /**
     * @ORM\Column(type="text")
     */    
    private $description;
    
    
    /**
     * @ORM\Column(type="boolean",nullable=false)
     */    
    private $isValide=false;

    
    /**
     * @ORM\Column(type="text",nullable=true)
     */    
    private $localisationDescription;


    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $infoOrientation;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    private $infoMaree;


    /**
     * @ORM\Column(type="decimal",nullable=true,scale=7)
     */    
    private $gpsLat; 
    
    /**
    * @ORM\Column(type="decimal",nullable=true,scale=7)
    */
    private $gpsLong;
    
    
    /**
     * @ORM\OneToOne(targetEntity="Balise", cascade={"persist", "remove"} )
     * @ORM\JoinColumn(name="balise_id", referencedColumnName="id")
     */    
    private $balise;
    
    /**
     * @ORM\OneToMany(targetEntity="DataWindPrev", mappedBy="spot", cascade={"remove", "persist"} , orphanRemoval=true)
     */
    private $dataWindPrev;

    /**
     * @ORM\OneToMany(targetEntity="WindOrientation", mappedBy="spot", cascade={"remove", "persist"} , orphanRemoval=true)
     */
    private $windOrientation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tempWaterURL;

    /**
     * @ORM\OneToMany(targetEntity="NotesDate", mappedBy="spot", cascade={"remove", "persist"} , orphanRemoval=true)
     */
    private $notesDate;

    /**
     * @ORM\OneToMany(targetEntity="MareeRestriction", mappedBy="spot", cascade={"remove", "persist"} , orphanRemoval=true)
     */
    private $mareeRestriction;

    /**
     * @ORM\OneToMany(targetEntity="MareeDate", mappedBy="spot", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $listMareeDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mareeURL;

    /**
     * @ORM\Column(type="decimal",nullable=true,scale=2)
     * hauteur de la marée basse - pour les grandes marées
     */
    private $hauteurMBGrandeMaree;

    /**
     * @ORM\Column(type="decimal",nullable=true,scale=2)
     * hauteur de la marée haute - pour la haute marée
     */
    private $hauteurMHGrandeMaree;

    /**
     * @ORM\Column(type="decimal",nullable=true,scale=2)
     * hauteur de la marée basse - pour les moyennes marées (coef 80)
     */
    private $hauteurMBMoyenneMaree;

    /**
     * @ORM\Column(type="decimal",nullable=true,scale=2)
     * hauteur de la marée haute - pour les moyennes marées (coef 80)
     */
    private $hauteurMHMoyenneMaree;

    /**
     * @ORM\Column(type="decimal",nullable=true,scale=2)
     * hauteur de la marée basse - pour les petites marées
     */
    private $hauteurMBPetiteMaree;

    /**
     * @ORM\Column(type="decimal",nullable=true,scale=2)
     * hauteur de la marée haute - pour les petites marées
     */
    private $hauteurMHPetiteMaree;

    /**
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="spots")
     * @ORM\JoinColumn(name="region_id", referencedColumnName="id", nullable=true)
     */
    private $region;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $baliseURL;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $webcamURL;

    /**
     * @ORM\OneToMany(targetEntity="InfoSpot", mappedBy="spot", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $infoSpot;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dataWindPrev = new \Doctrine\Common\Collections\ArrayCollection();
        $this->listMareeDate = new \Doctrine\Common\Collections\ArrayCollection();
        $this->windOrientation = new \Doctrine\Common\Collections\ArrayCollection();
        $this->mareeRestriction = new \Doctrine\Common\Collections\ArrayCollection();

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("n");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("nne");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("ne");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("ene");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("e");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("ese");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("se");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("sse");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("s");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("ssw");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("sw");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("wsw");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("w");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("wnw");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("nw");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);

        $windOrientation = new WindOrientation();
        $windOrientation->setSpot($this);
        $windOrientation->setOrientation("nnw");
        //$windOrientation->setOrientationDeg(WebsiteGetData::transformeOrientationNomLongDeg($windOrientation->getOrientation()));
        $this->windOrientation->add($windOrientation);
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Spot
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Spot
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Set localisationDescription
     *
     * @param string $localisationDescription
     * @return Spot
     */
    public function setLocalisationDescription($localisationDescription)
    {
        $this->localisationDescription = $localisationDescription;

        return $this;
    }

    /**
     * Get localisationDescription
     *
     * @return string 
     */
    public function getLocalisationDescription()
    {
        return $this->localisationDescription;
    }

    /**
     * Set gpsLat
     *
     * @param string $gpsLat
     * @return Spot
     */
    public function setGpsLat($gpsLat)
    {
        $this->gpsLat = $gpsLat;

        return $this;
    }

    /**
     * Get gpsLat
     *
     * @return string 
     */
    public function getGpsLat()
    {
        return $this->gpsLat;
    }

    /**
     * Set gpsLong
     *
     * @param string $gpsLong
     * @return Spot
     */
    public function setGpsLong($gpsLong)
    {
        $this->gpsLong = $gpsLong;

        return $this;
    }

    /**
     * Get gpsLong
     *
     * @return string 
     */
    public function getGpsLong()
    {
        return $this->gpsLong;
    }

    /**
     * Set mareeURL
     *
     * @param string $mareeURL
     * @return Spot
     */
    public function setMareeURL($mareeURL)
    {
        $this->mareeURL = $mareeURL;

        return $this;
    }

    /**
     * Get mareeURL
     *
     * @return string 
     */
    public function getMareeURL()
    {
        return $this->mareeURL;
    }

    /**
     * Set balise
     *
     * @param \LaPoiz\WindBundle\Entity\Balise $balise
     * @return Spot
     */
    public function setBalise(\LaPoiz\WindBundle\Entity\Balise $balise = null)
    {
        $this->balise = $balise;

        return $this;
    }

    /**
     * Get balise
     *
     * @return \LaPoiz\WindBundle\Entity\Balise 
     */
    public function getBalise()
    {
        return $this->balise;
    }

    /**
     * Add dataWindPrev
     *
     * @param \LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev
     * @return Spot
     */
    public function addDataWindPrev(\LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev)
    {
        $this->dataWindPrev[] = $dataWindPrev;

        return $this;
    }

    /**
     * Remove dataWindPrev
     *
     * @param \LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev
     */
    public function removeDataWindPrev(\LaPoiz\WindBundle\Entity\DataWindPrev $dataWindPrev)
    {
        $this->dataWindPrev->removeElement($dataWindPrev);
    }

    /**
     * Get dataWindPrev
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDataWindPrev()
    {
        return $this->dataWindPrev;
    }

    /**
     * Add windOrientation
     *
     * @param \LaPoiz\WindBundle\Entity\WindOrientation $windOrientation
     * @return Spot
     */
    public function addWindOrientation(\LaPoiz\WindBundle\Entity\WindOrientation $windOrientation)
    {
        $this->windOrientation[] = $windOrientation;

        return $this;
    }

    /**
     * Remove windOrientation
     *
     * @param \LaPoiz\WindBundle\Entity\WindOrientation $windOrientation
     */
    public function removeWindOrientation(\LaPoiz\WindBundle\Entity\WindOrientation $windOrientation)
    {
        $this->windOrientation->removeElement($windOrientation);
    }

    /**
     * Get windOrientation
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWindOrientation()
    {
        return $this->windOrientation;
    }

    /**
     * Add listMareeDate
     *
     * @param \LaPoiz\WindBundle\Entity\MareeDate $listMareeDate
     * @return Spot
     */
    public function addListMareeDate(\LaPoiz\WindBundle\Entity\MareeDate $listMareeDate)
    {
        $this->listMareeDate[] = $listMareeDate;

        return $this;
    }

    /**
     * Remove listMareeDate
     *
     * @param \LaPoiz\WindBundle\Entity\MareeDate $listMareeDate
     */
    public function removeListMareeDate(\LaPoiz\WindBundle\Entity\MareeDate $listMareeDate)
    {
        $this->listMareeDate->removeElement($listMareeDate);
    }

    /**
     * Get listMareeDate
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getListMareeDate()
    {
        return $this->listMareeDate;
    }

    /**
     * Add mareeRestriction
     *
     * @param \LaPoiz\WindBundle\Entity\MareeRestriction $mareeRestriction
     * @return Spot
     */
    public function addMareeRestriction(\LaPoiz\WindBundle\Entity\MareeRestriction $mareeRestriction)
    {
        $this->mareeRestriction[] = $mareeRestriction;

        return $this;
    }

    /**
     * Remove mareeRestriction
     *
     * @param \LaPoiz\WindBundle\Entity\MareeRestriction $mareeRestriction
     */
    public function removeMareeRestriction(\LaPoiz\WindBundle\Entity\MareeRestriction $mareeRestriction)
    {
        $this->mareeRestriction->removeElement($mareeRestriction);
    }

    /**
     * Get mareeRestriction
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMareeRestriction()
    {
        return $this->mareeRestriction;
    }

    /**
     * Set hauteurMBGrandeMaree
     *
     * @param string $hauteurMBGrandeMaree
     * @return Spot
     */
    public function setHauteurMBGrandeMaree($hauteurMBGrandeMaree)
    {
        $this->hauteurMBGrandeMaree = $hauteurMBGrandeMaree;

        return $this;
    }

    /**
     * Get hauteurMBGrandeMaree
     *
     * @return string 
     */
    public function getHauteurMBGrandeMaree()
    {
        return $this->hauteurMBGrandeMaree;
    }

    /**
     * Set hauteurMHGrandeMaree
     *
     * @param string $hauteurMHGrandeMaree
     * @return Spot
     */
    public function setHauteurMHGrandeMaree($hauteurMHGrandeMaree)
    {
        $this->hauteurMHGrandeMaree = $hauteurMHGrandeMaree;

        return $this;
    }

    /**
     * Get hauteurMHGrandeMaree
     *
     * @return string 
     */
    public function getHauteurMHGrandeMaree()
    {
        return $this->hauteurMHGrandeMaree;
    }

    /**
     * Set hauteurMBMoyenneMaree
     *
     * @param string $hauteurMBMoyenneMaree
     * @return Spot
     */
    public function setHauteurMBMoyenneMaree($hauteurMBMoyenneMaree)
    {
        $this->hauteurMBMoyenneMaree = $hauteurMBMoyenneMaree;

        return $this;
    }

    /**
     * Get hauteurMBMoyenneMaree
     *
     * @return string 
     */
    public function getHauteurMBMoyenneMaree()
    {
        return $this->hauteurMBMoyenneMaree;
    }

    /**
     * Set hauteurMHMoyenneMaree
     *
     * @param string $hauteurMHMoyenneMaree
     * @return Spot
     */
    public function setHauteurMHMoyenneMaree($hauteurMHMoyenneMaree)
    {
        $this->hauteurMHMoyenneMaree = $hauteurMHMoyenneMaree;

        return $this;
    }

    /**
     * Get hauteurMHMoyenneMaree
     *
     * @return string 
     */
    public function getHauteurMHMoyenneMaree()
    {
        return $this->hauteurMHMoyenneMaree;
    }

    /**
     * Set hauteurMBPetiteMaree
     *
     * @param string $hauteurMBPetiteMaree
     * @return Spot
     */
    public function setHauteurMBPetiteMaree($hauteurMBPetiteMaree)
    {
        $this->hauteurMBPetiteMaree = $hauteurMBPetiteMaree;

        return $this;
    }

    /**
     * Get hauteurMBPetiteMaree
     *
     * @return string 
     */
    public function getHauteurMBPetiteMaree()
    {
        return $this->hauteurMBPetiteMaree;
    }

    /**
     * Set hauteurMHPetiteMaree
     *
     * @param string $hauteurMHPetiteMaree
     * @return Spot
     */
    public function setHauteurMHPetiteMaree($hauteurMHPetiteMaree)
    {
        $this->hauteurMHPetiteMaree = $hauteurMHPetiteMaree;

        return $this;
    }

    /**
     * Get hauteurMHPetiteMaree
     *
     * @return string 
     */
    public function getHauteurMHPetiteMaree()
    {
        return $this->hauteurMHPetiteMaree;
    }

    /**
     * Add notesDate
     *
     * @param \LaPoiz\WindBundle\Entity\NotesDate $notesDate
     * @return Spot
     */
    public function addNotesDate(\LaPoiz\WindBundle\Entity\NotesDate $notesDate)
    {
        $this->notesDate[] = $notesDate;

        return $this;
    }

    /**
     * Remove notesDate
     *
     * @param \LaPoiz\WindBundle\Entity\NotesDate $notesDate
     */
    public function removeNotesDate(\LaPoiz\WindBundle\Entity\NotesDate $notesDate)
    {
        $this->notesDate->removeElement($notesDate);
    }

    /**
     * Get notesDate
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNotesDate()
    {
        return $this->notesDate;
    }

    /**
     * Set isValide
     *
     * @param boolean $isValide
     *
     * @return Spot
     */
    public function setIsValide($isValide)
    {
        $this->isValide = $isValide;

        return $this;
    }

    /**
     * Get isValide
     *
     * @return boolean
     */
    public function getIsValide()
    {
        return $this->isValide;
    }

    /**
     * Set tempWaterURL
     *
     * @param string $tempWaterURL
     *
     * @return Spot
     */
    public function setTempWaterURL($tempWaterURL)
    {
        $this->tempWaterURL = $tempWaterURL;

        return $this;
    }

    /**
     * Get tempWaterURL
     *
     * @return string
     */
    public function getTempWaterURL()
    {
        return $this->tempWaterURL;
    }


    /**
     * Set region
     *
     * @param \LaPoiz\WindBundle\Entity\Region $region
     *
     * @return Spot
     */
    public function setRegion(\LaPoiz\WindBundle\Entity\Region $region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return \LaPoiz\WindBundle\Entity\Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set baliseURL
     *
     * @param string $baliseURL
     *
     * @return Spot
     */
    public function setBaliseURL($baliseURL)
    {
        $this->baliseURL = $baliseURL;

        return $this;
    }

    /**
     * Get baliseURL
     *
     * @return string
     */
    public function getBaliseURL()
    {
        return $this->baliseURL;
    }

    /**
     * Set webcamURL
     *
     * @param string $webcamURL
     *
     * @return Spot
     */
    public function setWebcamURL($webcamURL)
    {
        $this->webcamURL = $webcamURL;

        return $this;
    }

    /**
     * Get webcamURL
     *
     * @return string
     */
    public function getWebcamURL()
    {
        return $this->webcamURL;
    }

    /**
     * Add infoSpot
     *
     * @param \LaPoiz\WindBundle\Entity\infoSpot $infoSpot
     *
     * @return Spot
     */
    public function addInfoSpot(\LaPoiz\WindBundle\Entity\infoSpot $infoSpot)
    {
        $this->infoSpot[] = $infoSpot;

        return $this;
    }

    /**
     * Remove infoSpot
     *
     * @param \LaPoiz\WindBundle\Entity\infoSpot $infoSpot
     */
    public function removeInfoSpot(\LaPoiz\WindBundle\Entity\infoSpot $infoSpot)
    {
        $this->infoSpot->removeElement($infoSpot);
    }

    /**
     * Get infoSpot
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInfoSpot()
    {
        return $this->infoSpot;
    }

    /**
     * Set infoOrientation
     *
     * @param string $infoOrientation
     *
     * @return Spot
     */
    public function setInfoOrientation($infoOrientation)
    {
        $this->infoOrientation = $infoOrientation;

        return $this;
    }

    /**
     * Get infoOrientation
     *
     * @return string
     */
    public function getInfoOrientation()
    {
        return $this->infoOrientation;
    }

    /**
     * Set infoMaree
     *
     * @param string $infoMaree
     *
     * @return Spot
     */
    public function setInfoMaree($infoMaree)
    {
        $this->infoMaree = $infoMaree;

        return $this;
    }

    /**
     * Get infoMaree
     *
     * @return string
     */
    public function getInfoMaree()
    {
        return $this->infoMaree;
    }
}
