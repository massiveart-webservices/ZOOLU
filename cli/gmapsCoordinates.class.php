<?php

class gmapsCoordinates
{
    /**
     * @var Core
     */
    protected $core;
    protected $gmapsUrl = "http://maps.google.com/maps/geo?output=xml&oe=utf-8&key=%KEY%&q=%QUERY%";
    //hold some values for google maps
    protected $street = '';
    protected $number = '';
    protected $city = '';
    protected $zip = '';
    protected $country = '';
    protected $gmapsKey = '';
    protected $longitude;
    protected $latitude;
    protected $gmapsQuery = '';
    protected $zeroLat = 0;
    protected $zeroLong = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    public function setZeroLat($latx)
    {
        $this->zeroLat = $latx;
    }

    public function setZeroLong($longY)
    {
        $this->zeroLong = $longY;
    }

    public function setStreet($_street)
    {
        $this->street = $_street;
    }

    public function setNumber($_number)
    {
        $this->number = $_number;
    }

    public function setZIP($_zip)
    {
        $this->zip = $_zip;
    }

    public function setCity($_city)
    {
        $this->city = $_city;
    }

    public function setCountry($_country)
    {
        $this->country = $_country;
    }

    public function setGMapsKey($key)
    {
        $this->gmapsKey = $key;
        $this->gmapsUrl = str_replace('%KEY%', $this->gmapsKey, $this->gmapsUrl);
    }

    public function getKeyFromCore()
    {
        $this->gmapsKey = $this->core->sysConfig->gmaps->key;
        $this->gmapsUrl = str_replace('%KEY%', $this->gmapsKey, $this->gmapsUrl);
    }

    protected function strbet($inputStr, $delimeterLeft, $delimeterRight, $debug = false)
    {
        $posLeft = strpos($inputStr, $delimeterLeft);
        if ($posLeft === false) {
            if ($debug) {
                echo "Warning: left delimiter '{$delimeterLeft}' not found";
            }
            return false;
        }
        $posLeft += strlen($delimeterLeft);
        $posRight = strpos($inputStr, $delimeterRight, $posLeft);
        if ($posRight === false) {
            if ($debug) {
                echo "Warning: right delimiter '{$delimeterRight}' not found";
            }
            return false;
        }
        return substr($inputStr, $posLeft, $posRight - $posLeft);
    }

    protected function doGMapQuery()
    {
        $coord = '';
        $gmapsUrl = str_replace('%QUERY%', urlencode($this->gmapsQuery), $this->gmapsUrl);
        //echo "\n" . "URL:" . $gmapsUrl ;
        $xml = simplexml_load_file($gmapsUrl);
        //quickfix für Frühlingsstraße+6850+Dornbirn
        //9.7466230,47.4147330,0
        //echo "\nName:" . $xml->Response->name . "!";
        if ($xml->Response->name == '6850+Dornbirn+Frühlingsstraße') {
            echo "FIX FOR SCHERTLER ALGE !!!! \n";
            return "9.7466230,47.4147330,0";
        }
        if (!empty($xml->Response)) {
            $point = $xml->Response->Placemark->Point;
            //echo "    coord:" . $point->coordinates . "\n";
            if (!empty($point)) return $point->coordinates;
        }
        return $coord;
    }

    protected function setMostEnhancedQuery()
    {
        $this->clearQuery();
        $this->addSearchQuery($this->zip);
        $this->addSearchQuery($this->city);
        $this->addSearchQuery($this->country);
        $this->addSearchQuery($this->street);
        $this->addSearchQuery($this->number, false);

    }

    protected function setEnhancedQuery()
    {
        $this->clearQuery();
        $this->addSearchQuery($this->zip);
        $this->addSearchQuery($this->city);
        $this->addSearchQuery($this->country);
        $this->addSearchQuery($this->street);

    }

    protected function setBasicQuery()
    {
        $this->clearQuery();
        $this->addSearchQuery($this->zip);
        $this->addSearchQuery($this->city);
        $this->addSearchQuery($this->country);
    }

    protected function addSearchQuery($val2Add, $withPlus = true)
    {
        if ($val2Add != '') {
            if ($this->gmapsQuery != '') {
                if ($withPlus === true) {
                    $this->gmapsQuery .= '+';
                } else {
                    $this->gmapsQuery .= ' ';
                }
            }
            $this->gmapsQuery .= $val2Add;
        }
    }

    protected function clearQuery()
    {
        $this->gmapsQuery = '';
    }

    public function calculateCoordinates()
    {

        $this->longitude = 0;
        $this->latitude = 0;
        $coord = '';
        $retryCounter = 0;

        //first create most enhanced query for gmaps
        while ($coord == '' && $retryCounter < 3) {
            $this->setMostEnhancedQuery();
            $coord = $this->doGMapQuery();
            if ($coord == '') {

                //try another query
                $this->setEnhancedQuery();
                $coord = $this->doGMapQuery();

                if ($coord == '') {
                    //try another query
                    $this->setBasicQuery();
                    $coord = $this->doGMapQuery();
                }
            }
            $retryCounter++;
        }
        if ($coord == '') {
            $this->longitude = $this->zeroLat;
            $this->latitude = $this->zeroLong;
        } else {
            $arrCoord = explode(',', $coord);
            $this->longitude = (isset($arrCoord[0])) ? $arrCoord[0] : 0;
            $this->latitude = (isset($arrCoord[1])) ? $arrCoord[1] : 0;
        }
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

}

?>