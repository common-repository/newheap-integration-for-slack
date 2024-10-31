<?php
namespace Slackr\Application\Event\EventCategory;

use Slackr\Application\Event\AbstractEvent;
use Slackr\Application\Helper\TypeHelper;

if (!defined('ABSPATH'))
{
    exit;
}

abstract class AbstractEventCategory
{
    /** @var  AbstractEvent[] */
    private $events;

    public function __construct()
    {
        $this->events = [];
    }

    /** @var  string */
    public abstract function getDisplayName();

    public function addEvent(AbstractEvent $event)
    {
        $this->events[] = $event;

        usort($this->events, function($a, $b){
            return strcmp($a->getName(), $b->getName());
        });
    }

    public function addEvents($events)
    {
        if(is_array($events))
        {
            foreach($events as $event)
            {
                if(!$event instanceof AbstractEvent)
                {
                    continue;
                }

                $this->addEvent($event);
            }
        }
    }

    public function removeEvent($eventClassName)
    {
        foreach($this->events as $i => &$event)
        {
            if(TypeHelper::getCleanClassNameString($eventClassName) === TypeHelper::getCleanClassNameString(get_class($event)))
            {
                unset($this->events[$i]);
                break;
            }
        }
    }

    /**
     * @return AbstractEvent[]
     */
    public function getEvents()
    {
        /** @var AbstractEvent[] $events */
        $events = [];

        foreach($this->events as $className => $event)
        {
            $events[] = $event;
        }

        usort($events, function($a, $b){
            return strcmp($a->getName(), $b->getName());
        });

        return $events;
    }

    /**
     * @param $eventClassName
     * @return null|AbstractEvent
     */
    public function getEvent($eventClassName)
    {
        $event = null;
        foreach($this->events as $i => &$possibleEvent)
        {
            if(TypeHelper::getCleanClassNameString($eventClassName) === TypeHelper::getCleanClassNameString(get_class($possibleEvent)))
            {
                $event = $possibleEvent;
                break;
            }
        }

        if(!$event instanceof AbstractEvent)
        {
            $event = null;
        }

        return $event;
    }

}