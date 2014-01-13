<?php

namespace Sulu\Events;

/**
 * Class Event
 * @package Sulu\Events
 * @author Alexander Schranz <alexander.schranz@massiveart.com>
 */
class Event {

    private static $weekdays = array(
        0 => 64,
        1 => 1,
        2 => 2,
        3 => 4,
        4 => 8,
        5 => 16,
        6 => 32
    );

    private static $daysOrder = array(
        0 => 7,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7
    );

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var \stdClass
     */
    protected $image;

    /**
     * @var \Datetime
     */
    protected $fromDate;

    /**
     * @var string
     */
    protected $fromTime;

    /**
     * @var \Datetime
     */
    protected $toDate;

    /**
     * @var string
     */
    protected $toTime;

    /**
     * @var bool
     */
    protected $fulltime;

    /**
     * @var bool
     */
    protected $repeat;

    /**
     * @var string
     */
    protected $repeatFrequency;

    /**
     * @var int
     */
    protected $repeatInterval;

    /**
     * @var int
     */
    protected $repeatType;

    /**
     * @var bool
     */
    protected $end;

    /**
     * @var \Datetime
     */
    protected $endDate;

    /**
     * @var array
     */
    protected $generatedDates = array();

    /**
     * @var array
     */
    protected $properties = array();


    /**
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->setData($data);
    }

    /**
     * @param string $from
     * @param string $to
     * @param bool $forceGenerate
     * @return mixed
     */
    public function getDates($from, $to, $forceGenerate = false)
    {
        if (!isset($this->generatedDates[$from . '-' . $to]) || $forceGenerate) {
            $this->generatedDates[$from . '-' . $to] = $this->generateDates($from, $to);
        }
        return $this->generatedDates[$from . '-' . $to];
    }

    /**
     * @param string $from
     * @param string $to
     * @return array
     */
    protected function generateDates ($from, $to)
    {
        $dates = array();
        if ($this->isInRange($from, $to)) {
            /** @var \Datetime  $current */
            foreach ($this->getFromToPeriod($from, $to) as $current) {
                $year = intval($current->format('Y'));
                $month = intval($current->format('m'));
                $day = intval($current->format('d'));
                if ($this->isDateBeginning($current)) {
                    $dates[] = date('Ymd H:i:s', strtotime($year.'-'.$month.'-'.$day.' '.$this->fromTime));
                }
            }
        }
        return $dates;
    }

    /**
     * @param string $from
     * @param string $to
     * @return \DatePeriod
     */
    protected function getFromToPeriod ($from, $to)
    {
        $from = new \DateTime(date('Y-m-d', $from));
        $to = new \DateTime(date('Y-m-d', $to));
        $interval = new \DateInterval('P1D');

        return new \DatePeriod($from, $interval, $to);
    }

    /**
     * @param \Datetime $date
     * @return bool
     */
    protected function isDateBeginning ($date)
    {
        if ($this->repeat) {
            if ($this->isInRange($date->getTimestamp())) {
                $frequency = $this->getInterval();
                return $this->isDateInFrequencyInterval($date, $frequency);
            }
        } else {
            if ($this->isFromDate($date)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param \Datetime $date
     * @param int $interval
     * @return bool
     */
    protected function isDateInFrequencyInterval($date, $interval)
    {
        switch ($this->repeatFrequency)
        {
            case 'daily':
                return $this->isDateInFrequencyIntervalDaily ($date, $interval);
                break;
            case 'weekly':
                return $this->isDateInFrequencyIntervalWeekly($date, $interval);
                break;
            case 'monthly':
                return $this->isDateInFrequencyIntervalMonthly($date, $interval);
                break;
            case 'yearly':
                return $this->isDateInFrequencyIntervalYearly($date, $interval);
                break;
        }
        return false;
    }

    /**
     * @param \Datetime $date
     * @param $interval
     * @return bool
     */
    protected function isDateInFrequencyIntervalDaily ($date, $interval)
    {
        $intervalSeconds = $date->getTimestamp() - $this->fromDate->getTimestamp();
        if ( $intervalSeconds % $interval == 0 ) {
            return true;
        }
       return false;
    }

    /**
     * @param \Datetime $date
     * @param $interval
     * @return bool
     */
    protected function isDateInFrequencyIntervalWeekly($date, $interval)
    {
        $bitWiseDayOfWeek = Event::$weekdays[$date->format('w')];
        if ($bitWiseDayOfWeek & $this->repeatType) {
            $from = clone $this->fromDate;

            $dayDifference = intval(Event::$daysOrder[$date->format('w')]) - intval(Event::$daysOrder[$from->format('w')]);
            $symbol = '+';
            if ($dayDifference < 0) {
                $symbol = '';
            }
            $from->modify($symbol . $dayDifference . ' day');

            $intervalSeconds = $date->getTimestamp() - $from->getTimestamp();
            if ( $intervalSeconds % $interval == 0 ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param \Datetime $date
     * @param $interval
     * @return bool
     */
    protected function isDateInFrequencyIntervalMonthly($date, $interval)
    {
        $difference = $this->fromDate->diff($date);
        if (intval($difference->format('m')) % $interval == 0) {
            switch ($this->repeatType) {
                case 2:
                    if ($date->format('D') == $this->fromDate->format('D')
                        && $this->weekOfMonth($date) == $this->weekOfMonth($this->fromDate)) {
                        return true;
                    }
                    break;
                case 1:
                default:
                    if ($date->format('d') == $this->fromDate->format('d')) {
                        return true;
                    }
                    break;
            }
        }
        return false;
    }

    /**
     * @param \Datetime $date
     * @return int
     */
    protected function weekOfMonth($date)
    {
        return ceil( date( 'j', strtotime( $date->getTimestamp() ) ) / 7 );
    }

    /**
     * @param \Datetime $date
     * @param $interval
     * @return bool
     */
    protected function isDateInFrequencyIntervalYearly($date, $interval)
    {
        $difference = $this->fromDate->diff($date);
        if (intval($difference->format('Y')) % $interval == 0
            && $date->format('m') == $this->fromDate->format('m')
            && $date->format('d') == $this->fromDate->format('d')) {
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    protected function getInterval()
    {
        $interval = $this->repeatInterval;
        switch ($this->repeatFrequency) {
            case 'daily':
                $interval = $interval * 86400;
                break;
            case 'weekly':
                $interval = $interval * 604800;
                break;
        }
        return $interval;
    }

    /**
     * @param string $from
     * @param null|string $to
     * @return bool
     */
    protected function isInRange($from, $to = null)
    {
        $to = ($to !== null) ? $to : $from;
        if ((empty($this->endDate) || $from <= $this->endDate->getTimestamp()) && $to >= $this->fromDate->getTimestamp()) {
            return true;
        }
        return false;
    }

    /**
     * @param \Datetime $date
     * @return bool
     */
    protected function isFromDate($date)
    {
        if (
            $this->fromDate->format('Y') == $date->format('Y') &&
            $this->fromDate->format('m') == $date->format('m') &&
            $this->fromDate->format('d') == $date->format('d')
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param $data
     */
    public function setData ($data)
    {
        foreach ($data as $key => $value) {
            $newKey = $this->underlineToCamelCase($key);
            $setMethod = 'set' . ucfirst($newKey);
            if (method_exists($this, $setMethod)) {
                $this->$setMethod($value);
            } else {
                $this->properties[$newKey] = $value;
            }
        }
    }

    protected function underlineToCamelCase($string)
    {
        $stringParts = explode('_', $string);
        $string = '';
        $counter = 0;
        foreach ($stringParts as $stringPart) {
            $counter++;
            if ($counter != 1) {
                $stringPart = ucfirst($stringPart);
            }
            $string .= $stringPart;
        }
        return $string;
    }

    /**
     * @param $date
     * @return $this
     */
    public function setPropertyDates($date)
    {
        $interval = $this->fromDate->diff($this->toDate);

        $fromDate = new \DateTime($date);
        $toDate = $fromDate;
        $toDate = $toDate->add($interval);

        $this->setProperty('from', $fromDate->format('Y-m-d'));
        $this->setProperty('to', $toDate->format('Y-m-d'));
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setProperty($key, $value)
    {
        $this->properties[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @return null
     */
    public function unsetProperty($key)
    {
        unset($this->properties[$key]);
        return $this;
    }

    /**
     * @param $key
     * @return null
     */
    public function getProperty($key)
    {
        if (isset($this->properties[$key])) {
            return $this->properties[$key];
        }
        return null;
    }

    /**
     * @param $end
     * @return $this
     */
    public function setEnd($end)
    {
        $this->end = (bool) $end;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param $endDate
     * @return $this
     */
    public function setEndDate($endDate)
    {
        if (is_string($endDate)) {
            $this->endDate = new \DateTime($endDate);
        } else {
            $this->endDate = $endDate;
        }
        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \Datetime $fromDate
     * @return $this
     */
    public function setFromDate($fromDate)
    {
        if (is_string($fromDate)) {
            $this->fromDate = new \DateTime($fromDate);
        } else {
            $this->fromDate = $fromDate;
        }
        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getFromDate()
    {
        return $this->fromDate;
    }

    /**
     * @param $fromTime
     * @return $this
     */
    public function setFromTime($fromTime)
    {
        $this->fromTime = $fromTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromTime()
    {
        return $this->fromTime;
    }

    /**
     * @param $fulltime
     * @return $this
     */
    public function setFulltime($fulltime)
    {
        $this->fulltime = $fulltime;
        return $this;
    }

    /**
     * @return bool
     */
    public function getFulltime()
    {
        return $this->fulltime;
    }

    /**
     * @param $generatedDates
     * @return $this
     */
    public function setGeneratedDates($generatedDates)
    {
        $this->generatedDates = $generatedDates;
        return $this;
    }

    /**
     * @return array
     */
    public function getGeneratedDates()
    {
        return $this->generatedDates;
    }

    /**
     * @param array $properties
     * @return $this
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param $repeat
     * @return $this
     */
    public function setRepeat($repeat)
    {
        $this->repeat = (bool) $repeat;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRepeat()
    {
        return $this->repeat;
    }

    /**
     * @param string $repeatFrequency
     * @return $this
     */
    public function setRepeatFrequency($repeatFrequency)
    {
        $this->repeatFrequency = $repeatFrequency;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepeatFrequency()
    {
        return $this->repeatFrequency;
    }

    /**
     * @param int $repeatInterval
     * @return $this
     */
    public function setRepeatInterval($repeatInterval)
    {
        $this->repeatInterval = intval($repeatInterval);
        return $this;
    }

    /**
     * @return int
     */
    public function getRepeatInterval()
    {
        return $this->repeatInterval;
    }

    /**
     * @param int $repeatType
     * @return $this
     */
    public function setRepeatType($repeatType)
    {
        $this->repeatType = intval($repeatType);
        return $this;
    }

    /**
     * @return int
     */
    public function getRepeatType()
    {
        return $this->repeatType;
    }

    /**
     * @param \Datetime $toDate
     * @return $this
     */
    public function setToDate($toDate)
    {
        if (is_string($toDate)) {
            $this->toDate = new \DateTime($toDate);
        } else {
            $this->toDate = $toDate;
        }
        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getToDate()
    {
        return $this->toDate;
    }

    /**
     * @param \Datetime $toTime
     * @return $this
     */
    public function setToTime($toTime)
    {
        $this->toTime = $toTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getToTime()
    {
        return $this->toTime;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param \stdClass $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return \stdClass
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }


}