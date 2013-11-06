<?php

namespace Sulu\Events;

class Manager
{

    /**
     * @var array
     */
    protected $events = array();

    /**
     * @var array
     */
    protected $generatedViews = array();

    public function __construct()
    {

    }

    /**
     * @param int $from
     * @param int $to
     * @param bool $forceGenerate
     * @return array
     */
    public function getEvents($from = 0, $to = 0, $forceGenerate = false)
    {
        if (empty($from) || empty($to)) {
            return $this->events;
        }
        return $this->generateEvents($from, $to, $forceGenerate);
    }

    /**
     * @param $from
     * @param $to
     * @param bool $forceGenerate
     * @return array
     */
    protected function generateEvents($from, $to, $forceGenerate = false)
    {
        $events = array();
        if (!isset($generatedViews[$from . '-' . $to]) || $forceGenerate) {
            /** @var Event $event */
            foreach ($this->events as $event) {
                foreach ($event->getDates($from, $to, $forceGenerate) as $date) {
                    $dateEvent = clone $event; // clone Instance of Event important!
                    $dateEvent->setPropertyDates($date);
                    $counter = 1;
                    while ($counter) { // get a none used Key for this time
                        if (!isset($events[date('Y-m-d H:i:s', strtotime($date)) . $counter])) {
                            break;
                        }
                        $counter++;
                    }
                    $events[date('Y-m-d H:i:s', strtotime($date)) . $counter] = $dateEvent;
                }
            }
            ksort($events);
            $generatedViews[$from . '-' . $to] = $events;
        }
        return $events;
    }

    /**
     * @param Event $event
     */
    public function addEvent($event)
    {
        $this->events[] = $event;
    }
}