<?php
class Application_View_Helper_MemberHomepage
{	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	/**
	 * build member homepage for controller=>index action=>index and user logged in
	 * @return $output
	 */
	public function memberHomepage() 
	{
		$user = $this->_view->user;
		$this->_view->placeholder('narrowColumn')->captureStart();
		
		$href = '/users/' . $user->userID;
        if ($user->getProfilePic('large') == '/images/users/profile/pic/large/no_profile_male.jpg') {
			// No profile pic, send to upload profile pic page
			$href .= '/upload';
		}
			
        	echo "<a href='" . $href . "'><img src='" . $user->getProfilePic('large') . "' class='narrow-column-picture dropshadow' id='narrow-column-user-picture'/></a>";
           	echo $this->_view->narrowcolumnsection()->start(array('title' => 'My Ratings'));
				echo "Rating information will go here";
			echo $this->_view->narrowcolumnsection()->end();
			echo $this->_view->narrowcolumnsection()->start(array('title' => 'My Teams'));
				echo "Team information will go here";
			echo $this->_view->narrowcolumnsection()->end();
			echo $this->_view->narrowcolumnsection()->start(array('title' => 'My Groups'));
				echo "Group information will go here";
			echo $this->_view->narrowcolumnsection()->end();
			echo $this->_view->narrowcolumnsection()->start(array('title' => 'My Schedule'));
				echo "Schedule information will go here";
			echo $this->_view->narrowcolumnsection()->end();

		$this->_view->placeholder('narrowColumn')->captureEnd();
			

		$scheduleHeader = $this->buildScheduleHeader();
		$output 		= $this->_view->partial('partials/global/sectionHeaderBold.phtml',array('title'   => 'schedule',
																								'content' => $scheduleHeader)); 
		$output 	   .= $this->buildScheduleBody(); // Schedule content here
		
		$findHeader  = $this->buildFindHeader();																		
		$output     .= $this->_view->partial('partials/global/sectionHeaderBold.phtml',array('title'   => 'find',
																							 'content' => $findHeader)); 
		$output 	.= "<div id='gmap'></div>"; // Find content here
		$output     .= "<img src='/images/global/loading.gif' class='member-find-loading'/>
						<div id='member-find-body'>"
					 . $this->buildFindBody()
					 . "</div>";
																			
		$output     .= $this->_view->partial('partials/global/sectionHeaderPlain.phtml',array('title'   => 'newsfeed')); 
		$newsfeed    = $this->buildNewsfeed();
		$output     .= $newsfeed;																			 
																		 
																							 
		return $output;
	}
	
	/**
	 * build schedule header section
	 * @return $output
	 */
	public function buildScheduleHeader()
	{
		$output = '<div id="member-schedule-days-container">';
		$schedule = $this->_view->userSchedule;
		
		
		for ($i = 0; $i < 7; $i++) {
			// Create the days in reverse order (float:right)
			$curDay  = strtotime('+' . $i . ' days');
			$day  	 = date('l', $curDay);
			$date 	 = date('d', $curDay);
			$dayOfWeek = date('w', $curDay);
			if ($day == 'Sunday' ||
				$day == 'Saturday' ||
				$day == 'Thursday') {
					// Show 2 letters
					$dayShort = substr($day,0,2);
			} else {
					// Show one letter
					$dayShort = substr($day,0,1);
			}
			
			$class = '';
			if ($day == date('l')) {
				// Today!
				$displayDay = $day;
				$class		= ' light-back';
			} else {
				// Not today, show abbreviated names
				$displayDay = $dayShort;
			}
			$subClass = ''; // Class for inner paragraphs
			if (isset($schedule[$dayOfWeek])) {
				// Day has event
				$subClass .= 'bold darkest';
			}
			
			$output .= "<div class='member-schedule-day-container pointer " . $class . "'>
							<p class='member-schedule-day-subtext medium smaller-text " . $subClass . "'>" . $date . "</p>
							<p class='member-schedule-day medium center " . $subClass . "' fullDay='" . $day . "' shortDay='" . $dayShort . "'>" . $displayDay . "</p>
						</div>";
		}
		
		$output .= "</div>";
		
		return $output;
	}
	
	/**
	 * build schedule body section
	 * @return $output
	 */
	public function buildScheduleBody()
	{
		$output   = "<div id='member-schedule-body-container'>";
		$curDay   = date('w');
		$schedule = $this->_view->userSchedule; 
		
		for ($i = $curDay; $i < ($curDay + 7);$i++) {
			if ($i > 6) {
				// 7 days in week, maintain order of days as well as count
				$b = $i - 7;
			} else {
				$b = $i;
			}
			
			$date  	 = date('l', strtotime('+' . ($i - $curDay) . ' days'));
			if ($i - $curDay == 0) {
				// Today
				$dateCombo = 'today';
			} elseif ($i - $curDay == 1) {
				// Tomorrow
				$dateCombo = 'tomorrow';
			} else {
				$dateCombo = 'this ' . $date;
			}
			
			if (isset($schedule[$b])) {
				// Event for today
				$games   = $schedule[$b];
				$output .= "<div class='member-schedule-day-body-container'>";
				$output .= "<div class='member-schedule-day-body-pagination-container'>";
				if (count($games) > 1) {
					// More than one game
					for ($c = 1; $c <= count($games); $c++) {
						$class = 'pagination-page pointer medium member-schedule-pagination';
						if ($c == 1) {
							$class .= ' light-back';
						}
						$output .= "<p class='" . $class . "'>" . $c . "</p>";
					}
				}
				$output .= "</div>";
				$output .= "<div class='member-schedule-day-body-outer-container'>
							<div class='member-schedule-day-body-inner-container'>";
				foreach ($games as $game) {
					$class = '';
					if ($game->confirmed == '1') {
						// User is confirmed, change class of in button
						$class = 'inner-shadow member-schedule-button-selected';
					}
					$output .= "<a href='/games/" . $game->gameID . "' class='member-schedule-day-body-game-container'>";
					$output .= "<div class='member-schedule-day-body-game-left-container'>";
					$output .= "<p class='bold darkest larger-text'>" . ucwords($game->sport) . ' Game';
					$output .= "<p class='clear'>" . ucwords($dateCombo) . "</p>";
					$output .= "<p class='clear'>" . date('ga',strtotime($game->date)) . "</p>";
					$output .= "<p class='clear medium'>" . $game->getPark()->parkName . "</p>";
					$output .= "</div>";
					$output .= "<div class='member-schedule-day-body-players-container darkest bold'>";
					$output .= "<p class='member-schedule-day-body-players larger-text center'>" . $game->totalPlayers . "/" . $game->rosterLimit . "</p>";
					$output .= "<p class='member-schedule-day-body-players center clear'>players</p>";
					$output .= "<p class='center clear green smaller-text member-schedule-day-body-players-confirmed'>" . $game->confirmedPlayers . " confirmed</p>";
					$output .= "</div>";
					$output .= "<div class='member-schedule-day-body-game-right-container'>";
					$output .= "<p class='darkest center'>Are you in, or are you out?</p>";
					$output .= "<p class='button larger-text member-schedule-in " . $class . "'>in</p>";
					$output .= "<p class='button larger-text member-schedule-out'>out</p>";
					$output .= "</div>";
					$output .= "</a>";
				}
				$output .= "</div></div></div>";
				
			} else {
				$output .= "<p class='member-schedule-day-none member-schedule-day-body-container medium larger-text center'>You have no games scheduled for " . $dateCombo . ".</p>";
			}
			
		}
		$output .= '</div>';
		
		
		return $output;
	}
		
		
	
	/**
	 * build find header section
	 * @return $output
	 */
	public function buildFindHeader()
	{
		$output  = "<div class='member-find-looking-container'><p class='arial bold medium' id='member-find-looking'>Looking for: </p>";
		
		$output  .= $this->_view->lookingDropdownSport;
		$output  .= $this->_view->lookingDropdownType;
		
		
		$output .= "</div>";
		return $output;
	}
	
	/**
	 * build find body section
	 * @return $output
	 */
	public function buildFindBody()
	{
		$output = "<div class='member-find-lower-outer-container'><div class='member-find-lower-outer-inner-container'>";
		$matches  = $this->_view->matches;
		
		$counter    = 0;
		$totalMatches = 1;
		$totalPages = 1;
		$totalGames = 0;
		$matchesPerPage = 4;
		$numberOfPages  = 3;
		if (empty($matches)) {
			// No matches 
			$output  = "<p class='medium larger-text member-find-none center'>No matches could be found.</p>";
			$output .= "<a href='/find' class='light center member-find-none-search'> Search more</a>";
			return $output;
		}
			
		foreach ($matches as $match) {
			if ($totalMatches > ($matchesPerPage * $numberOfPages)) {
				// Met limit of number of pages
				break;
			}
			if ($counter == 0) {
				// Counter was reset/first round, create inner container
				$output .= "<div class='member-find-lower-inner-container'>";
			} 
			if ($counter == $matchesPerPage) {
				// Number of games/teams per "page" is met, start new
				$output .= "</div><div class='member-find-lower-inner-container'>";
				$counter = 0;
				$totalPages++;
			}
			
			if (get_class($match) == 'Application_Model_Game') {
				// Match is a game
				$type     = 'Game';
				$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $match->date);
				$newDate  = $dateTime->format('m n');
				$day      = $match->getDay();
				$hour	  = $match->getHour();
				$dateDesc = date('M j', strtotime($match->date));
				$id		  = $match->gameID;
				$location = $match->getLimitedParkName(25);
				$gameIndex= $totalGames;
				$totalGames++;
			} elseif (get_class($match) == 'Application_Model_Team') {
				// Match is a team
				$type	  = 'Team';
				$day      = '';
				$hour	  = '';
				$location = $match->getLimitedName('teamName',25);
				$id		  = $match->teamID;
				$dateDesc = '';
				$marker   = '';
				$gameIndex= '';
			}
				
			$output .= "<a class='member-find-game-container member-" . strtolower($type) . "' href='/" . strtolower($type) . "s/" . $id . "' gameIndex='" . $gameIndex . "'>";
			$output .= "<p class='member-find-game-number green-back white arial bold'>" . $totalMatches . "</p>";
			$output .= "<p class='member-find-game-sport darkest bold'>" . $match->sport . "</p>";
			$output .= "<p class='member-find-game-type darkest bold'>" . $type . "</p>";
			$output .= "<div class='member-find-game-date medium' tooltip='" . $dateDesc . "'>
								<div class='member-find-game-date-day'>" . $day . "</div>&nbsp; 
								<div class='member-find-game-date-hour'>" . $hour . "</div>
							</div>";
			$output .= "<p class='member-find-game-players darkest bold'>" . $match->totalPlayers . "/" . $match->rosterLimit . "</p>";
			$output .= "<img src='" . $match->getMatchImage() . "' class='member-find-game-match' tooltip='" . $match->getMatchDescription() . "'/>";
			$output .= "<p class='member-find-game-park medium'>" . $location . "</p>";
			$output .= "<img src='/images/global/body/double_arrows.png' class='member-find-game-arrow'/>";
			
			$output .= "</a>";
						
			$counter++;
			$totalMatches++;
			
			
		}
		
		// End game section
		$output .= "</div></div></div>";
		
		// Num pages
		$output .= "<div class='pagination-pages-outer-container'><div class='pagination-pages-inner-container'>";
		for ($i = 1; $i <= $totalPages; $i++) {
			$class = 'pagination-page pointer medium member-find-pagination';
			if ($i == 1) {
				$class .= ' light-back';
			}
			$output .= "<p class='" . $class . "'>" . $i . "</p>";
		}
		
		$output .= "</div></div>";
		
		$output .= "<a href='/find' class='member-find-view-more medium'>view more</a>";
		
		return $output;
	}
	
	
	/**
	 * build main newsfeed section
	 * @return $output
	 */
	public function buildNewsfeed()
	{
		$newsfeed = $this->_view->newsfeed;
		$output   = '<div class="notifications-container">';
		foreach ($newsfeed->read as $notification) {
			$output .= $this->createNotification($notification);
		}
		$output .= "</div>";
		return $output;
	}
	
	/**
	 * create html for notification
	 * @params(notification => notification ele)
	 */
	 public function createNotification($notification)
	 {
		  $output = '';
		  $output .= "<div class='newsfeed-notification-container'>";
		  $output .= "<img src='" . $notification->getPicture('tiny') . "' class='newsfeed-notification-img' />";
		  $output .= "<div class='newsfeed-notification-text-container'>";
		  $output .= $notification->getFormattedText();
		  $output .= "</div><span class='newsfeed-notification-time light'>" . $notification->getTimeFromNow() . "</span>
					  </div>";
					  
		  return $output;
	 }
			
}