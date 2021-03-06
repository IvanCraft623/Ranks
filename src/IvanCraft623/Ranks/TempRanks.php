<?php

#Ranks by IvanCraft623 (Twitter: @IvanCraft623)

/*
    8888888                            .d8888b.                   .d888 888     .d8888b.   .d8888b.   .d8888b.  
      888                             d88P  Y88b                 d88P"  888    d88P  Y88b d88P  Y88b d88P  Y88b 
      888                             888    888                 888    888    888               888      .d88P 
      888  888  888  8888b.  88888b.  888        888d888 8888b.  888888 888888 888d888b.       .d88P     8888"  
      888  888  888     "88b 888 "88b 888        888P"      "88b 888    888    888P "Y88b  .od888P"       "Y8b. 
      888  Y88  88P .d888888 888  888 888    888 888    .d888888 888    888    888    888 d88P"      888    888 
      888   Y8bd8P  888  888 888  888 Y88b  d88P 888    888  888 888    Y88b.  Y88b  d88P 888"       Y88b  d88P 
    8888888  Y88P   "Y888888 888  888  "Y8888P"  888    "Y888888 888     "Y888  "Y8888P"  888888888   "Y8888P"  
*/

#For Server (IP: endergames.ddns.net  Port:25331):

/*
    ███████╗███╗   ██╗██████╗ ███████╗██████╗  ██████╗  █████╗ ███╗   ███╗███████╗███████╗
    ██╔════╝████╗  ██║██╔══██╗██╔════╝██╔══██╗██╔════╝ ██╔══██╗████╗ ████║██╔════╝██╔════╝
    █████╗  ██╔██╗ ██║██║  ██║█████╗  ██████╔╝██║  ███╗███████║██╔████╔██║█████╗  ███████╗
    ██╔══╝  ██║╚██╗██║██║  ██║██╔══╝  ██╔══██╗██║   ██║██╔══██║██║╚██╔╝██║██╔══╝  ╚════██║
    ███████╗██║ ╚████║██████╔╝███████╗██║  ██║╚██████╔╝██║  ██║██║ ╚═╝ ██║███████╗███████║
    ╚══════╝╚═╝  ╚═══╝╚═════╝ ╚══════╝╚═╝  ╚═╝ ╚═════╝ ╚═╝  ╚═╝╚═╝     ╚═╝╚══════╝╚══════╝
*/

namespace IvanCraft623\Ranks;

use IvanCraft623\Ranks\Ranks;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\level\Level;
use pocketmine\utils\{Config, TextFormat as TE};

class TempRanks extends Task {
	
	/** @var Ranks */
	protected $plugin;

	/**
	 * TempRanks Constructor
	 * @param Ranks $plugin
	 */
	public function __construct(Ranks $plugin){
		$this->plugin = $plugin;
	}

	/**
	 * @param Int $currentTick
	 */

	public function onRun(Int $currentTick){
		$rankInfo = $this->plugin->db->query("SELECT * FROM rankPlayers;");
		$i = -1;
		while ($resultArr = $rankInfo->fetchArray(SQLITE3_ASSOC)) {
			$j = $i + 1;
			$rankPlayer = $resultArr['player'];
			$this->CheckExpireRank($rankPlayer);
			$i = $i + 1;
		}
	}

	public function CheckExpireRank ($rankPlayer) {
		$PPerms = $this->plugin->getServer()->getPluginManager()->getPlugin("PurePerms");
		$player = $PPerms->getPlayer($rankPlayer);
		$rankInfo = $this->plugin->db->query("SELECT * FROM rankPlayers WHERE player = '$rankPlayer';");
		$array = $rankInfo->fetchArray(SQLITE3_ASSOC);
		$now = time();
		$rankTime = $array['rankTime'];
		$nowRank = $array['nowRank'];
		$lastRank = $array['lastRank'];
		if($rankTime < $now) {
			if ($this->plugin->getConfig()->get("mode") === "setdefaultrank") {
				$newRank = $PPerms->getDefaultGroup();
			} elseif ($this->plugin->getConfig()->get("mode") === "setlastrank") {
				$newRank = $PPerms->getGroup($lastRank);
			} else { //This error will show as flow
				$this->plugin->getLogger()->critical("{$this->plugin->getConfig()->get("mode")} is not a valid value in config.yml, correct it, it has not been possible to remove the time range to {$rankPlayer}");
				if ($player instanceof Player) {
					$player->sendMessage("§cAn unexpected error has occurred in the Ranks plugin configuration, contact an Admin to correct the error...");
				}
				return;
			}
			if ($player instanceof Player) {
				$player->sendMessage("§eYour §b{$nowRank} §erank has expired!");
			}
			//$this->plugin->getLogger()->info("§aPlayer §b{$rankPlayer}§a Rank: §b{$nowRank} §ahas expired!");
			$PPerms->setGroup($player, $newRank);
			$this->plugin->db->query("DELETE FROM rankPlayers WHERE player = '$rankPlayer';");
		}
	}
}
