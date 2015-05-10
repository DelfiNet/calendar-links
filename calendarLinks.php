<?php

/**
 * Class CalendarLinks
 *
 * A helper class to convert an event, with description to a registerable calendar event
 *
 * @version 1.0.0
 * @author Keith Perhac <keith@delfi-net.com>
 * @link https://github.com/delfinet/calendar-links/ Latest version on GitHub.
 * @link http://twitter.com/harisenbon79 Keith on twitter.
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 */
class CalendarLinks {

	public $title = null;
	public $description = null;
	public $startDate = null;
	public $endDate = null;
	public $duration = null;
	public $location = null;

	/**
	 * Create the Calendar Event
	 *
	 * @param $title
	 * @param $startDate
	 * @param null $description
	 * @param int $duration
	 * @param null $location
	 */
	public function __construct($title, $startDate, $description = null, $duration = 60, $location = null){

		if($description){
			$markdown = new HTML_To_Markdown();
			$markdown->set_option('strip_tags', true);
			$noImageText = preg_replace(array("/<img[^>]+\>/i", "/<hr[^>]+\>/i"), "", $description);
			$markdown->convert($noImageText);
			$this->description = $markdown;
		}

        if(is_numeric($startDate)){
            $startTimestamp = new DateTime();
            $startTimestamp->setTimestamp($startDate);
        } else {
            $startTimestamp = new DateTime($startDate);
        }

		$startTimestamp->setTimezone(new DateTimeZone('UTC'));

		$this->startDate = $startTimestamp->format('Ymd\THis\Z');
		$this->duration = $duration;
		$this->endDate = $startTimestamp->modify('+' . $duration .' minutes')->format('Ymd\THis\Z');
		$this->location = $location;
		$this->title = strip_tags($title);
	}

	/**
	 * Return an iCal event Link
	 * @return string
	 */
	public function iCal(){
		return $this->ics();
	}

	/**
	 * Return a Google Calendar event Link
	 * @return string
	 */
	public function google(){

		return 'https://www.google.com/calendar/render'.
		'?action=TEMPLATE'.
		'&text=' . rawurlencode($this->title).
		'&dates=' . $this->startDate .
		'/' . $this->endDate .
		'&details=' . rawurlencode($this->description).
		'&location=' . rawurlencode($this->location).
		'&sprop=&sprop=name:';
	}

	/**
	 * Return a Yahoo Calendar event Link
	 * @return string
	 */
	public function yahoo(){

		// Convert duration to Yahoo Duration format (hh:mm)
		$yahooHourDuration = str_pad(floor($this->duration / 60), 2, '0', STR_PAD_LEFT);
		$yahooMinDuration = str_pad(floor($this->duration % 60), 2, '0', STR_PAD_LEFT);
		$yahooDuration = $yahooHourDuration . ':' . $yahooMinDuration;

		return 'http://calendar.yahoo.com/?v=60&view=d&type=20'.
		'&title=' . rawurlencode($this->title).
		'&st=' . $this->startDate .
		'&dur=' . $yahooDuration .
		'&desc=' . rawurlencode($this->description).
		'&in_loc=' . rawurlencode($this->location);
	}

	/**
	 * Return an ICS event Link
	 * @return string
	 */
	public function ics(){

		return 'data:text/calendar;charset=utf8,'.
		'BEGIN:VCALENDAR'.
		'VERSION:2.0'.
		'BEGIN:VEVENT'.
		'URL:' . rawurlencode($this->location).
		'DTSTART:' . $this->startDate .
		'DTEND:' . $this->endDate .
		'SUMMARY:' . rawurlencode($this->title).
		'DESCRIPTION:' . rawurlencode($this->description).
		'LOCATION:' . rawurlencode($this->location).
		'END:VEVENT'.
		'END:VCALENDAR';
	}

	/**
	 * Return a Microsoft Outlook event Link
	 * @return string
	 */
	public function outlook(){
		return $this->ics();
	}

}