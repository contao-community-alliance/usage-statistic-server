<?php

namespace ContaoCommunityAlliance\UsageStatistic\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation AS Serializer;

/**
 * WeeklyDataNameSummary
 *
 * @Serializer\ExclusionPolicy("all")
 *
 * @ORM\Table(name="summary_data_name_weekly")
 * @ORM\Entity(repositoryClass="ContaoCommunityAlliance\UsageStatistic\ServerBundle\Repository\WeeklyDataNameSummaryRepository")
 */
class WeeklyDataNameSummary
{

	/**
	 * @Serializer\Expose
	 *
	 * @ORM\Id
	 * @ORM\Column(name="year", type="integer")
	 * @ORM\GeneratedValue(strategy="NONE")
	 *
	 * @var int
	 */
	private $year;

	/**
	 * @Serializer\Expose
	 *
	 * @ORM\Id
	 * @ORM\Column(name="week", type="integer")
	 * @ORM\GeneratedValue(strategy="NONE")
	 *
	 * @var int
	 */
	private $week;

	/**
	 * @Serializer\Expose
	 *
	 * @ORM\Id
	 * @ORM\Column(name="name", type="string")
	 * @ORM\GeneratedValue(strategy="NONE")
	 *
	 * @var string
	 */
	private $name;

	/**
	 * @Serializer\Expose
	 *
	 * @ORM\Column(name="summary", type="integer")
	 *
	 * @var int
	 */
	private $summary;

	/**
	 * @return int
	 */
	public function getYear()
	{
		return $this->year;
	}

	/**
	 * @param int $year
	 *
	 * @return static
	 */
	public function setYear($year)
	{
		$this->year = (int) $year;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getWeek()
	{
		return $this->week;
	}

	/**
	 * @param int $week
	 *
	 * @return static
	 */
	public function setWeek($week)
	{
		$this->week = (int) $week;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 *
	 * @return static
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSummary()
	{
		return $this->summary;
	}

	/**
	 * @param int $summary
	 *
	 * @return static
	 */
	public function setSummary($summary)
	{
		$this->summary = (int) $summary;
		return $this;
	}
}
