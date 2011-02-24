<?php defined('SYSPATH') or die('No direct script access.');

/**
 * A simple class for calculating holidays
 *
 * Able to calculate dates for holidays based on the stored derivations. 
 * Holidays other than Easter based on lunar cycles will not be able to be
 * calculated. Work should be done to improve this with configuration abilities
 */
class Synapse_Holiday {

	public static $default_format = 'Y-m-d';

	protected static $_derivations = array
	(
		'newyears'     => 'January 1 <year>',
		'mlk'          => 'January 17 <year>',
		'presidents'   => 'February 11 <year>',
		'memorial'     => 'last Mon of May <year>',
		'independence' => 'July 4 <year>',
		'labor'        => 'first Mon of September <year>',
		'columbus'     => 'second Mon of October <year>',
		'veterans'     => 'November 11 <year>',
		'thanksgiving' => 'fourth Thu of November <year>',
		'christmas'    => 'December 25 <year>',
	);

	protected static $_aliases = array
	(
		'newyears'     => array('New Year\'s Day'),
		'mlk'          => array('Martin Luther King Day', 'Birthday of Martin Luther King, Jr.'),
		'presidents'   => array('President\'s Day', 'Washington\'s Birthday'),
		'memorial'     => array('Memorial Day'),
		'independence' => array('Independence Day', 'Fourth of July', '4th of July'),
		'labor'        => array('Labor Day'),
		'columbus'     => array('Columbus Day'),
		'veterans'     => array('Veterans Day', 'Armistice Day', 'Remembrance Day'),
		'thanksgiving' => array('Thanksgiving Day'),
		'christmas'    => array('Christmas Day'),
	);

	public static function factory($name, $year = NULL)
	{
		return new Holiday($name, $year);
	}

	public static function known_holidays()
	{
		return array_keys(self::$_derivations);
	}

	public static function is_observed_holiday(DateTime $date = NULL)
	{
		if ( ! $date)
		{
			$date = date_create();
		}

		foreach (self::known_holidays() as $holiday)
		{
			if ($date->format('Y-m-d') == self::factory($holiday, $date->format('Y'))->observed('Y-m-d'))
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	public static function is_holiday(DateTime $date = NULL)
	{
		if ( ! $date)
		{
			$date = date_create();
		}

		foreach (self::known_holidays() as $holiday)
		{
			if ($date->format('Y-m-d') == self::factory($holiday, $date->format('Y'))->format('Y-m-d'))
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	protected $_year;
	protected $_key;

	public function __construct($name, $year = NULL)
	{
		$this->_key  = $this->_find_key($name);
		$this->_year = (string) ($year ?: date('Y'));
	}

	public function name()
	{
		return (isset(self::$_aliases[$this->_key][0])) ? self::$_aliases[$this->_key][0] : $this->_key;
	}

	public function observed($format = NULL)
	{
		$date = $this->datetime();
		
		if ($date->format('l') == 'Saturday')
		{
			$date = date_create($date->format('F j Y').' -1 day');
		}
		elseif ($date->format('l') == 'Sunday')
		{
			$date = date_create($date->format('F j Y').' +1 day');
		}

		$format = $format ? $format : self::$default_format;

		return $date->format($format);
	}

	public function date($format = NULL)
	{
		$date = $this->datetime();

		$format = $format ?: self::$default_format;

		return $date->format($format);
	}

	public function datetime()
	{
		$replacements = array
		(
			'<year>' => $this->_year,
		);

		$calculation = strtr(self::$_derivations[$this->_key], $replacements);

		return date_create($calculation);
	}

	protected function _find_key($name)
	{
		if (array_key_exists($name, self::$_aliases))
		{
			return $name;
		}
		else
		{
			foreach (self::$_aliases as $key => $aliases)
			{
				if (in_array($name, $aliases))
					return $key;
			}
		}

		throw new InvalidArgumentException('"'.$name.'" was not a valid holiday name.');
	}
} // End Holiday
