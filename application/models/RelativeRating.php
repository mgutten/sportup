<?php

class Application_Model_RelativeRating extends Application_Model_SportRating
{
	protected $_mapperClass = 'Application_Model_SportRatingsMapper';
	
	protected $_attribs     = array('userRelativeRatingID'  => '',
									'actingUser'	=> '',
									'winningUser'	=> '',
									'losingUser'	=> '',
									'actingUserID'	=> '',
									'winningUserID'	=> '',
									'losingUserID'	=> '',
									'sportRatingID'	=> '',
									'sportRating'	=> '',
									'valueGained' 	=> '',
									'losingUserRating' => '',
									'oldGameID'		=> '',
									'teamGameID'	=> '',
									'game'			=> '',
									'dateHappened'	=> '',
									'locked'		=> '',
									'dateUnlocked'	=> '',
									'unlockDate'	=> '',
									'noShow'		=> ''
									);
									
	protected $_primaryKey = 'userRelativeRatingID';
	protected $_dbTable = 'Application_Model_DbTable_UserSportRatings';

	public function save($loopSave = false)
	{
		
		if ($this->noShow == '1') {
			// User is being penalized for not showing
			$this->setCurrent('dateHappened');
			$this->setCurrent('dateUnlocked');
			$this->getMapper()->noShow($this);
		}
		
		$this->getMapper()->saveRelativeRating($this);
	}
	
	public function getIDType()
	{
		if ($this->isPickupGame()) {
			return 'oldGameID';
		} else {
			return 'teamGameID';
		}
	}
	
	public function getTypeID()
	{
		return $this->_attribs[$this->getIDType()];
	}
	
	public function isPickupGame()
	{
		if ($this->hasValue('oldGameID')) {
			return true;
		} else {
			return false;
		}
	}

	public function getTimeFromNow($date = false, $maxDays = 14)
	{
		return parent::getTimeFromNow($this->dateHappened);
	}

	public function setDateHappened($date)
	{
		$this->_attribs['dateHappened'] = $date;
		$this->_attribs['date'] = DateTime::createFromFormat('Y-m-d H:i:s', $date);
		
		return $this;	
	}
	
	public function setDateUnlocked($date)
	{
		if (is_null($date)) {
			$date = '2050-01-01 00:00:00';
		}
		$this->_attribs['dateUnlocked'] = $date;
		$this->_attribs['unlockDate'] = DateTime::createFromFormat('Y-m-d H:i:s', $date);
		
		return $this;	
	}
}