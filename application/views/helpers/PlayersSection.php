<?php

class Application_View_Helper_PlayersSection
{
	
	public function setView($view)
	{
		$this->_view = $view;
	}
	
	/** 
	 * create players section for team, group, or game profile pages
	 * @params ($players => array of player object,
	 *			$nextGame => if there is a next game, then next game object
	 */
	public function playerssection($players, $nextGame = false, $limited = true)
	{
		$output = $lowerOutput = '';
		$counter = 0;
		$firstNotConfirmed = $firstMaybeConfirmed = false;
		$totalPlayers = count($players);
		
		if ($this->_view->team) {
			// Players for team page
			$type = 'teams';
			$typeID = $this->_view->team->teamID;
			$typeModel = $this->_view->team;
		} elseif ($this->_view->game){
			// Players for game page
			$type = 'games';
			$typeID = $this->_view->game->gameID;
			$typeModel = $this->_view->game;
		}
		
		if ($players) {
			// Players in game
			foreach ($players as $player) {
				
				if ($counter >= 14 && $limited) {
					// Only show 14 players
					$output .= "<a href='/" . $type . "/" . $typeID . "/players' class='medium margin-top clear-right smaler-text'>" . ($totalPlayers - $counter) . " more players</a>";
					break;
				}
				/*$output .= "<a href='/users/" . $player->userID . "' class='left team-player-container'>";
				$output .= 	$player->getBoxProfilePic('medium');
				*/
				
				$success = false;
				
				if ($nextGame) {
					/* only $success is in use, $src is for green check and red X instead of colored box */
					if ($nextGame->userConfirmed($player->userID)) {
						// User is going to next game
						//$src = "/images/team/confirm/small.png";
						$background = 'green-back';
						$success = true;
					} elseif ($nextGame->userNotConfirmed($player->userID)) {
						// User is confirmed as not going
						//$src = "/images/team/deny/small.png";
						$background = 'dark-red-back';
						$success = true;
					} elseif ($nextGame->userMaybeConfirmed($player->userID)) {
						$background = 'light-background';
						$success = true;
					}
				}
				
				$size = 'medium';
				$float = 'left';
				$tooltip = $playerHTML = '';
				if ($type == 'games') {
					/* only $success is in use, $src is for green check and red X instead of colored box */
					
					if ($typeModel->userConfirmed($player->userID)) {
						// User is going to next game
						//$src = "/images/team/confirm/small.png";
						$background = 'green-back';
						$success = true;
					} elseif ($typeModel->userNotConfirmed($player->userID)) {
						// User is confirmed as not going
						//$src = "/images/team/deny/small.png";
						$background = 'dark-red-back';
						
						$size = 'small';
						$success = true;
						$tooltip = "tooltip = '$player->shortName<br><span class=\"red heavy smaller-text\">out</span>'";
						if (!$firstNotConfirmed) {
							$firstNotConfirmed = true;
							$float = 'clear';
						}
					} elseif ($typeModel->userMaybeConfirmed($player->userID)) {
						$background = 'light-background';
						$success = true;
						
						$size = 'small';
						$success = true;
						$tooltip = "tooltip = '$player->shortName<br><span class=\"medium heavy smaller-text\">maybe</span>'";
						if (!$firstMaybeConfirmed) {
							$firstMaybeConfirmed = true;
							$float = 'clear';
						}
					}
				}
					
				$playerHTML .= "<a href='/users/" . $player->userID . "' class='" . $float . " team-player-container" . ($size == 'small' ? '-small' : '') . "' " . $tooltip . ">";
				$playerHTML .= 	$player->getBoxProfilePic($size, 'users', ($size == 'small' ? 'animate-opacity' : ''));
				
				if ($success) {
					// User is either confirmed or not
					//$output .= "<img src='" . $src . "' class='clear team-confirm-img' />";
					$playerHTML .= "<div class='" . $background . " clear team-confirm-img'></div>";
				}
				
				$playerHTML .= 	"<div class='hover-dark profile-player-overlay-container" . ($size == 'small' ? '-small' : '') . "'>";
				if ($size != 'small') {
					$playerHTML .=		"<div class='profile-player-overlay'>";
					$playerHTML .=			"<p class='white width-100 center left margin-top'>" . $player->shortName . "</p>";
					//$output .=			"<p class='white width-100 center left smaller-text'>age " . $player->age . "</p>";
					$playerHTML .=			"<p class='white width-100 center left largest-text heavy margin-top'>" . $player->getSport($typeModel->sport)->overall . "</p>";
					$playerHTML .=		"</div>";
				}
				$playerHTML .=	"</div>";
				$playerHTML .= "</a>";
				
				if ($size == 'small') {
					$lowerOutput .= $playerHTML;
				} else {
					$output .= $playerHTML;
				}
				
				if ($typeModel instanceof Application_Model_Game &&
					$size == 'small') {
						// Do not include "out" players in count of players
						continue;
					}
				$counter++;
			}
		}

		if ($typeModel instanceof Application_Model_Game) {
			/*if ($counter < $typeModel->totalPlayers) {
				// There are "plus-ones"
				
				$guests = $typeModel->totalPlayers - $counter;
				
				for($i = 0; $i < $guests; $i++) {
					if ($counter >= 14 && $limited) {
						$output .= "<a href='/" . $type . "/" . $typeID . "/players' class='medium margin-top clear-right smaller-text'>" . ($typeModel->totalPlayers - $counter) . " more players</a>";
						break;
					}
					$player  = new Application_Model_User();					
					$output .= "<div class='left team-player-container'>";
					$output .= 	$player->getBoxProfilePic('medium');
					$output .= 	"<div class='hover-dark profile-player-overlay-container'>";
					$output .=		"<div class='profile-player-overlay'>";
					$output .=			"<p class='light width-100 center left'>Guest</p>";
					$output .=			"<p class='light width-100 center left smaller-text'>not a member</p>";
					$output .=		"</div>";
					$output .=	"</div>";
					$output .= "</div>";
					$counter++;
				}
			}
			*/

		}
		
		$remaining = ($typeModel->rosterLimit >= 7 ? 7 - $counter : $typeModel->rosterLimit - $counter);
		if ($remaining > 0 && $typeModel->public == '1') {
			// Populate remaining with empty open spots
			$text = 'Join';
			$class = 'profile-join-player-container pointer';
			if ($this->_view->userOnTeam || $this->_view->userInGame) {
				// No need to show join spots for users on team already
				$text = 'Open';
				$class = 'profile-open-player-container default';
			}
			
			for ($i = 0; $i < $remaining; $i++) {
				$output .= "<div class='left " . $class . " light animate-opacity'>" . $text . "</div>";
			}
		}
		
		$output .= $lowerOutput;
					
		return $output;
	}
	

}
