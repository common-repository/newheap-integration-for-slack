<?php
namespace Slackr\Application\Event\EventGroup;

use Slackr\Application\Event\AbstractEvent;
use Slackr\Application\Event\EventCategory\AbstractEventCategory;
use Slackr\Application\Helper\TypeHelper;

if (!defined('ABSPATH'))
{
    exit;
}

abstract class AbstractEventGroup
{
    /** @var  AbstractEventCategory[] */
    private $eventsCategories;

    public function __construct()
    {
        $this->eventsCategories = [];
    }

    /** @var  string */
    public abstract function getDisplayName();

    public function addEventCategory(AbstractEventCategory $eventCategory)
    {
        $this->eventsCategories[] = $eventCategory;

        usort($this->eventsCategories, function($a, $b){
            return strcmp($a->getDisplayName(), $b->getDisplayName());
        });
    }

    public function addEventCategories($eventCategories)
    {
        if(is_array($eventCategories))
        {
            foreach($eventCategories as $eventCategory)
            {
                if(!$eventCategory instanceof AbstractEventCategory)
                {
                    continue;
                }

                $this->addEventCategory($eventCategory);
            }
        }
    }

    public function removeEventCategory($eventCategoryClassName)
    {
        foreach($this->eventsCategories as $i => &$eventCategory)
        {
            if(TypeHelper::getCleanClassNameString($eventCategoryClassName) === TypeHelper::getCleanClassNameString(get_class($eventCategory)))
            {
                unset($this->eventsCategories[$i]);
                break;
            }
        }
    }

    /**
     * @return AbstractEventCategory[]
     */
    public function getEventCategories()
    {
        /** @var AbstractEventCategory[] $events */
        $eventCategories = [];

        foreach($this->eventsCategories as $i => $eventCategory)
        {
            $eventCategories[] = $eventCategory;
        }

        usort($eventCategories, function($a, $b){
            return strcmp($a->getDisplayName(), $b->getDisplayName());
        });

        return $eventCategories;
    }

    /**
     * @param $eventCategoryClassName
     * @return null|AbstractEventCategory
     */
    public function getEventCategory($eventCategoryClassName)
    {
        $eventCategory = null;

        foreach($this->eventsCategories as $i => &$possibleEventCategory)
        {
            if(TypeHelper::getCleanClassNameString($eventCategoryClassName) === TypeHelper::getCleanClassNameString(get_class($possibleEventCategory)))
            {
                $eventCategory = $possibleEventCategory;
                break;
            }
        }

        if(!$eventCategory instanceof AbstractEventCategory)
        {
            $eventCategory = null;
        }

        return $eventCategory;
    }

    /**
     * @return AbstractEvent[]
     */
    public function getEvents()
    {
        /** @var AbstractEvent[] $events */
        $events = [];

        foreach($this->eventsCategories as $i => $eventCategory)
        {
            $events = array_merge($events, $eventCategory->getEvents());
        }

        usort($events, function($a, $b){
            return strcmp($a->getName(), $b->getName());
        });

        return $events;
    }

}