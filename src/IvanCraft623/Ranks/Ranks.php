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

use pocketmine\Player;
use pocketmine\IPlayer;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\{Command, CommandSender};
use pocketmine\utils\Config;

use IvanCraft623\Ranks\Form\{CustomForm, Form, ModalForm, SimpleForm};
use IvanCraft623\Ranks\TempRanks;

class Ranks extends PluginBase implements Listener {

	private static  $instance = null;

	public  $db = [];

	public $codeName = [];

	public $targetRank = [];

	public $targetCode = [];

	public function onLoad(){
		Ranks::$instance = $this;
	}

	public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->db = new \SQLite3($this->getDataFolder() . "TempRanks.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS rankPlayers(player TEXT PRIMARY KEY, rankTime INT, lastRank TEXT, nowRank TEXT);");
        $this->getScheduler()->scheduleRepeatingTask(new TempRanks($this), 20);
        $this->getConfig()->get("Config Version");
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label,array $args) : bool {
		switch ($cmd->getName()) {
			case 'ranks':
				if(isset($args[0])) {
					switch ($args[0]) {
						case 'claim':
							if ($sender instanceof Player) {
								$this->claimCodeUI($sender);
							} else {
								$sender->sendMessage("§cYou can only use this command in the game!");
							}
						break;

						case 'manage': //TODO!
							if (!$sender->hasPermission("rank.cmd.manage")) {
								$sender->sendMessage("§cYou dont have permission to use this command!");
								return true;
							}
							$sender->sendMessage("§cThis function is under development, please wait for it");
						break;

						case 'settemprank':
							$PPerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
							if (!$sender->hasPermission("rank.cmd.settemprank")) {
								$sender->sendMessage("§cYou dont have permission to use this command!");
								return true;
							}
							if (count($args) == 4) {
								$player = $PPerms->getPlayer($args[1]);
								$playerName = $player->getName();
								$playerName2 = strtolower($playerName);
								$group = $PPerms->getGroup($args[2]);
								$now = time();
								$day = ($args[3] * 86400);
								$rankTime = $now + $day;
								$lastRank = $PPerms->getUserDataMgr($player)->getGroup($player);
								if($group === null) {
									$sender->sendMessage("§cRank {$args[2]} does NOT exist!");
									return true;
								}
								if(!is_numeric($args[3]) || $args[3] === "0") {
									$sender->sendMessage("§cTime must be a valid number!");
									return true;
								}
								//Register into database
								$rankInfo = $this->db->prepare("INSERT OR REPLACE INTO rankPlayers (player, rankTime, lastRank, nowRank) VALUES (:player, :rankTime, :lastRank, :nowRank);");
								$rankInfo->bindValue(":player", $playerName2);
								$rankInfo->bindValue(":rankTime", $rankTime);
								$rankInfo->bindValue(":lastRank", $lastRank);
								$rankInfo->bindValue(":nowRank", $group);
								$rankInfo->execute();
								//Set rank to player
								$PPerms->setGroup($player, $group);
								//Get Rank Time
								$rankInfo2 = $this->db->query("SELECT * FROM rankPlayers WHERE player = '$playerName2';");
    							$array = $rankInfo2->fetchArray(SQLITE3_ASSOC);
    							if (!empty($array)) {
    								$rankTime2 = $array['rankTime'];
    								if($rankTime2 > $now){
    									$remainingTime = $rankTime2 - $now;
    									$day = floor($remainingTime / 86400);
    									$hourSeconds = $remainingTime % 86400;
    									$hour = floor($hourSeconds / 3600);
    									$minuteSec = $hourSeconds % 3600;
    									$minute = floor($minuteSec / 60);
    									$remainingSec = $minuteSec % 60;
    									$second = ceil($remainingSec);
    								}
    							}
    							//Send Message to Sender
    							$sender->sendMessage(
										"§a---- §bYou have given a Temp rank! §a----"."\n"."\n".
										"§ePlayer:§b {$playerName}"."\n".
            							"§eRank:§b {$group}"."\n".
            							"§eTime:§b {$day} day(s), {$hour} hour(s), {$minute} minute(s), {$second} second(s)"
									);
								//Send Message to Player
								if ($player instanceof Player) {
									$player->sendMessage(
    									"§a---- §bCongratulations §a----"."\n"."\n".
    									"§eYour rank has been changed!"."\n".
    									"§aRank:§b {$group}"."\n".
    									"§aTime:§b {$day} day(s), {$hour} hour(s), {$minute} minute(s), {$second} second(s)"
    								);
								}
							} else {
								$sender->sendMessage("§eUse:§a /ranks settemprank <player> <rank> <time in days>");
							}
						break;

						case 'createcode':						
							$PPerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
							if (!$sender->hasPermission("rank.cmd.createcode")) {
								$sender->sendMessage("§cYou dont have permission to use this command!");
								return true;
							}
							if (count($args) == 1) {
								if ($sender instanceof Player) {
									$this->createCode1UI($sender);
								} else {
									$sender->sendMessage("§eUse:§a /ranks createcode <code> <rank> <max uses> <time in days>");
								}
							} else {
								if (!isset($args[4])) {
									$sender->sendMessage("§eUse:§a /ranks createcode <code> <rank> <max uses> <time in days>");
									return true;
								} else {
									if ($args[1] === "random") {
										$args[1] = $this->getRandomCode();
									}
									if ($this->checkCodeExist($args[1])) {
										$sender->sendMessage("§c{$args[1]} code alredy exist!");
										return true;
									}
									$group = $PPerms->getGroup($args[2]);
									if($group === null) {
										$sender->sendMessage("§cRank {$args[2]} does NOT exist!");
										return true;
									}
									if(!is_numeric($args[3])) {
										$sender->sendMessage("§cMax uses must be numeric");
										return true;
									}
									if(!is_numeric($args[4]) || $args[4] === "0") {
										$sender->sendMessage("§cTime to expire the rank must be a valid number");
										return true;
									}
									//Send data to function createCodeInFile
									$codeName = $args[1];
									$codeCreator = $sender->getName();
									$rank = $args[2];
									$maxUses = $args[3];
									$rankExpTime = $args[4];
									$this->createCodeInFile($codeName, $codeCreator, $rank, $maxUses, $rankExpTime);

									//Send Message to sender of data
									$sender->sendMessage(
										"§aYou have created a new TempRank Code!"."\n"."\n".
										"§eCode:§b {$codeName}"."\n".
            							"§eCreator:§b {$codeCreator}"."\n".
            							"§eRank:§b {$rank}"."\n".
            							"§eRankExpTime:§b {$rankExpTime} day(s)"."\n".
            							"§eMax Uses:§b {$maxUses}"
									);
								}
							}
						break;

						case 'deletecode':
							if (!$sender->hasPermission("rank.cmd.deletecode")) {
								$sender->sendMessage("§cYou dont have permission to use this command!");
								return true;
							}
							if (count($args) == 1) {
								if ($sender instanceof Player) {
									$this->ListCodesUI($sender);
								} else {
									$sender->sendMessage("§cYou must specify the code!");
								}
							} else {
								if ($this->checkCodeExist($args[1])) {
									$this->deleteCode($args[1]);
									$sender->sendMessage("§bYou have delete {$args[1]} code!");
								} else {
									$sender->sendMessage("§c{$args[1]} code does not exist!");
								}
							}
						break;

						case 'credits':
							$sender->sendMessage(
								"§a---- §bRanks credits §a----"."\n"."\n".
								"§eThis server is using Ranks by IvanCraft623"."\n".
								"§eTwitter: §b@IvanCraft623"."\n".
								"§eIvanCraft623 server:"."\n".
								"§bendergames.ddns.net:25331"
							);
						break;
						
						default:
							if ($sender->hasPermission("rank.cmd.settemprank")) {
								$sender->sendMessage(
									"§a---- §bRanks Commands §a----"."\n"."\n".
									"§eUse:§a /ranks settemprank §7(Set a TempRank to a player.)"."\n".
									"§eUse:§a /ranks createcode §7(Create a code to claim a rank.)"."\n".
									"§eUse:§a /ranks deletecode §7(Delete a code.)"."\n".
									"§eUse:§a /ranks manage §7(Coming soon...)"."\n"."\n".
									"§eUse:§a /ranks claim §7(Open an UI to claim a code.)"."\n".
									"§eUse:§a /ranks credits §7(view credits)"
								);
							} else {
								$sender->sendMessage(
									"§a---- §bRanks Commands §a----"."\n"."\n".
									"§eUse:§a /ranks claim §7(claim a rank)"."\n".
									"§eUse:§a /ranks credits §7(view credits)"
								);
							}
						break;
					}
				} else {
					if ($sender->hasPermission("rank.cmd.settemprank")) {
						$sender->sendMessage(
							"§a---- §bRank Commands §a----"."\n"."\n".
							"§eUse:§a /ranks settemprank [Player] §7(open an ui to set temp rank)"."\n".
							"§eUse:§a /ranks createcode (create a code to claim a rank)"."\n".
							"§eUse:§a /ranks deletecode (delete a code)"."\n".
							"§eUse:§a /ranks manage §7(open an ui to manage)"."\n"."\n".
							"§eUse:§a /ranks claim §7(claim a rank)"
						);
					} else {
						$sender->sendMessage(
							"§a---- §bSurvival Commands §a----"."\n"."\n".
							"§eUse:§a /ranks claim §7(claim a rank)"
						);
					}
				}
			break;
		}
		return true;
	}

	public function claimCodeUI($sender) {
        $form =  new CustomForm(function (Player $sender, array $data = null) {
            if ($data === null){
                return true;
            }
            if ($this->checkCodeExist($data[1])) {
            	$this->claimCode($sender, $data[1]);
            } else {
            	$sender->sendMessage("§cThis code does not exist");
            }
        });
        $form->setTitle("§9§lClaim Code!");
        $form->addLabel("§fInsert the Code...");
        $form->addInput("Code:", "Code123");
        $form->sendToPlayer($sender);
    }

    public function claimCode ($sender, $codeName) {
    	$PPerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
    	//Check if player already hava a TempRank
    	$senderName = strtolower($sender->getName());
    	$rankInfo0 = $this->db->query("SELECT * FROM rankPlayers WHERE player = '$senderName';");
    	$array0 = $rankInfo0->fetchArray(SQLITE3_ASSOC);
    	if (!empty($array0)) {
    		$sender->sendMessage("§cYou already have a TempRank");
    		return;
    	}
    	//Register player into database...
    	$codesFile = new Config($this->getDataFolder() . "codes.yml", Config::YAML);
    	$codesFileAll = $codesFile->getAll();
    	$now = time();
    	$day = ($codesFileAll["{$codeName}"]["RankExpTime"] * 86400);
    	$rankTime = $now + $day;
    	$lastRank = $PPerms->getUserDataMgr($sender)->getGroup($sender);
    	$nowRank = $codesFileAll["{$codeName}"]["Rank"];
    	$rankInfo = $this->db->prepare("INSERT OR REPLACE INTO rankPlayers (player, rankTime, lastRank, nowRank) VALUES (:player, :rankTime, :lastRank, :nowRank);");
    	$rankInfo->bindValue(":player", strtolower($sender->getName()));
    	$rankInfo->bindValue(":rankTime", $rankTime);
    	$rankInfo->bindValue(":lastRank", $lastRank);
    	$rankInfo->bindValue(":nowRank", $nowRank);
    	$rankInfo->execute();
    	//Set Rank to Player...
    	$newRank = $PPerms->getGroup($nowRank);
    	$PPerms->setGroup($sender, $newRank);
    	//Get Rank Time
    	$rankInfo2 = $this->db->query("SELECT * FROM rankPlayers WHERE player = '$senderName';");
    	$array = $rankInfo2->fetchArray(SQLITE3_ASSOC);
    	if (!empty($array)) {
    		$rankTime2 = $array['rankTime'];
    		$nowRank2 = $array['nowRank'];
    		if($rankTime2 > $now){
    			$remainingTime = $rankTime2 - $now;
    			$day = floor($remainingTime / 86400);
    			$hourSeconds = $remainingTime % 86400;
    			$hour = floor($hourSeconds / 3600);
    			$minuteSec = $hourSeconds % 3600;
    			$minute = floor($minuteSec / 60);
    			$remainingSec = $minuteSec % 60;
    			$second = ceil($remainingSec);
    		}
    	}
    	//Add a use to the code
    	$codeUses = $codesFileAll["{$codeName}"]["Uses"];
    	$addUse = $codeUses + 1;
    	$codesFile->set("{$codeName}", [
			"Creator" => "{$codesFileAll["{$codeName}"]["Creator"]}",
			"Rank" => "{$nowRank}",
			"RankExpTime" => "{$codesFileAll["{$codeName}"]["RankExpTime"]}",
			"MaxUses" => "{$codesFileAll["{$codeName}"]["MaxUses"]}",
			"Uses" => "{$addUse}"
		]);
		$codesFile->save();
    	//Send Message to Player
    	$sender->sendMessage(
    		"§a---- §bCongratulations §a----"."\n"."\n".
    		"§eYou have successfully claimed your rank!"."\n".
    		"§aRank:§b {$nowRank2}"."\n".
    		"§aTime:§b {$day} day(s), {$hour} hour(s), {$minute} minute(s), {$second} second(s)"
    	);
    	//Check if maximum uses have been reached
		if ($this->CheckIfMaxUsesReached($codeName)) {
			$this->deleteCode($codeName);
		}
    }

    public function CheckIfMaxUsesReached ($codeName) {
    	$codesFile = new Config($this->getDataFolder() . "codes.yml", Config::YAML);
    	$codesFileAll = $codesFile->getAll();
    	if ($codesFileAll["{$codeName}"]["Uses"] >= $codesFileAll["{$codeName}"]["MaxUses"]) {
			return true;
		} else {
			return false;
		}

    }

	public function ListCodesUI($sender){
		$form =  new SimpleForm(function (Player $sender, $data = null) {
			$target = $data;
			if($target === null){
				return true;
			}
			$this->targetCode[$sender->getName()] = $target;
			$this->InfoCodeUI($sender);
		});
		$form->setTitle("§9§lList of Codes");
		$form->setContent("§fSelect a Code...");
		$codesFile = new Config($this->getDataFolder() . "codes.yml", Config::YAML);
		foreach($codesFile->getAll() as $code => $value){
			$form->addButton($code, -1, "", $code);
		}
		$form->sendToPlayer($sender);
		return $form;
	}

	public function InfoCodeUI($sender){
        $form =  new SimpleForm(function (Player $sender, int $data = null) {
            $result = $data;
            if($result === null){
                return true;
            }             
            switch ($result) {
                case 0:
                    $this->deleteCode($this->targetCode[$sender->getName()]);
                    $sender->sendMessage("§bYou have delete {$this->targetCode[$sender->getName()]} code!");
            	break;

            	case 1:
                    unset($this->targetCode[$sender->getName()]);
                    $this->ListCodesUI($sender);
            	break;
            }
        });
        $codesFile = new Config($this->getDataFolder() . "codes.yml", Config::YAML);
        $codesFileAll = $codesFile->getAll();
        $target = $this->targetCode[$sender->getName()];
        $form->setTitle("§9§l{$target} Code Info");
        $form->setContent(
        	"§f{$target}:"."\n\n".
        	"§aAdded by:§b " . $codesFileAll["{$target}"]["Creator"]."\n".
        	"§aRank:§b " . $codesFileAll["{$target}"]["Rank"]."\n".
        	"§aRankExpTime:§b " . $codesFileAll["{$target}"]["RankExpTime"]."\n".
        	"§aMax Uses:§b " . $codesFileAll["{$target}"]["MaxUses"]."\n".
        	"§aUses:§b " . $codesFileAll["{$target}"]["Uses"]
        );
        $form->addButton("§l§cDelete Code",0,"textures/ui/icon_trash");
        $form->addButton("§l§9Back",0,"textures/items/compass_item");
        $form->sendToPlayer($sender);
    }

	public function createCode1UI($sender) {
        $form =  new CustomForm(function (Player $sender, $data = null) {
            if($data === null){
                return true;
            }
            if ($data[1] === "random") {
            	$data[1] = $this->getRandomCode();
            	$sender->sendMessage("§bGenerating a random Code...");
            	$sender->sendMessage("§bCode:§a {$data[1]}");
            }
            if ($this->checkCodeExist($data[1])) {
            	$sender->sendMessage("§c{$data[1]} code alredy exist!");
            	return;
            }
            $this->codeName[$sender->getName()] = $data[1];
            $this->openRanksListUI($sender);
        });
        $form->setTitle("§9§lCreate TempRank Code");
        $form->addLabel("§fEnter code...");
        $form->addInput("Code:", "Enter code...");
        $form->sendToPlayer($sender);
    }

    public function openRanksListUI($player){
		$form =  new SimpleForm(function (Player $player, $data = null) {
			$target = $data;
			if($target === null){
				return true;
			}
			$this->targetRank[$player->getName()] = $target;
			$this->createCode2UI($player);
		});
		$form->setTitle("§9§lCreate TempRank Code");
		$form->setContent("§fSelect a Rank for {$this->codeName[$player->getName()]} Code...");
		$PPranks = $this->getServer()->getPluginManager()->getPlugin("PurePerms")->getGroups();
		foreach($PPranks as $rank){
			$form->addButton($rank, -1, "", $rank);
		}
		$form->sendToPlayer($player);
		return $form;
	}

	public function createCode2UI($sender) {
        $form =  new CustomForm(function (Player $sender, array $data = null) {
            if($data === null){
                return true;
            }
            if(!is_numeric($data[2])) {
            	$sender->sendMessage("§cMax uses must be numeric");
            	return;
            } elseif(!is_numeric($data[3])) {
            	$sender->sendMessage("§cTime to expire the code must be numeric");
            	return;
            } elseif(!is_numeric($data[4]) || $data[4] === "0") {
            	$sender->sendMessage("§cTime to expire the rank must be a valid number");
            	return;
            }
            $codeName = $this->codeName[$sender->getName()];
            $codeCreator = $sender->getName();
            $rank = $this->targetRank[$sender->getName()];
            $maxUses = $data[2];
            $codeExpTime = $data[3];
            $rankExpTime = $data[4];
            $this->createCodeInFile($codeName, $codeCreator, $rank, $maxUses, $rankExpTime);
            $sender->sendMessage(
            	"§aYou have created a new TempRank Code!"."\n"."\n".
            	"§eCode:§b {$codeName}"."\n".
            	"§eCreator:§b {$codeCreator}"."\n".
            	"§eRank:§b {$rank}"."\n".
            	"§eRankExpTime:§b {$rankExpTime} day(s)"."\n".
            	"§eMax Uses:§b {$maxUses}"
            );
        });
        $form->setTitle("§9§lCreate TempRank Code");
        $form->addLabel("§bCode:§a {$this->codeName[$sender->getName()]}");
        $form->addLabel("§bRank:§a {$this->targetRank[$sender->getName()]}");
        $form->addInput("Max uses for this code:", "0 = Infinite uses");
        $form->addInput("Time to expire the code:", "in development | does not work");
        $form->addInput("Time to expire the rank:", "in days..." );
        $form->sendToPlayer($sender);
    }

    public function deleteCode ($codeName) {
    	$codesFile = new Config($this->getDataFolder() . "codes.yml", Config::YAML);
    	$codesFile->remove("{$codeName}");
    	$codesFile->save();
    }

    public function checkCodeExist ($codeName) {
    	$codesFile = new Config($this->getDataFolder() . "codes.yml", Config::YAML);
    	$codesFileAll = $codesFile->getAll();
    	if (isset($codesFileAll["$codeName"])) return true; //CODE ALREDY EXIST!
    	if (!isset($codesFileAll["$codeName"])) return false;
    }

	public function createCodeInFile ($codeName, $codeCreator, $rank, $maxUses/*, $codeExpTime*/, $rankExpTime = null) {
		$codesFile = new Config($this->getDataFolder() . "codes.yml", Config::YAML);
		$codesFile->set("{$codeName}", [
			"Creator" => "{$codeCreator}",
			"Rank" => "{$rank}",
			"RankExpTime" => "{$rankExpTime}",
			"MaxUses" => "{$maxUses}",
			"Uses" => "0"
		]);
		$codesFile->save();
	}

	public function getRandomCode() {
		switch ($this->getRandomNumber()) {
			case '1':
				return "{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomLetter()}";
			break;
			
			case '2':
				return "{$this->getRandomLetter()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomLetter()}{$this->getRandomLetter()}{$this->getRandomNumber()}";
			break;

			case '3':
				return "{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomLetter()}";
			break;

			case '4':
				return "{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomNumber()}";
			break;

			case '5':
				return "{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomLetter()}";
			break;

			case '6':
				return "{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomLetter()}{$this->getRandomLetter()}{$this->getRandomNumber()}";
			break;

			case '7':
				return "{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomLetter()}";
			break;

			case '8':
				return "{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomNumber()}";
			break;

			case '9':
				return "{$this->getRandomNumber()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomLetter()}{$this->getRandomNumber()}{$this->getRandomLetter()}";
			break;
		}
	}

	public function getRandomLetter() {
		$letters = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];
		return $letters[array_rand($letters)];
	}

	public function getRandomNumber() {
		$numbers = ["1", "2", "3", "4", "5", "6", "7", "8", "9"];
		return $numbers[array_rand($numbers)];
	}
}
