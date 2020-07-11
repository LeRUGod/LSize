<?php

namespace LeRUGod\LSize;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class LSize extends PluginBase implements Listener {

    private $data;
    private $db;

    protected $sy = "§b§l[ §f시스템 §b]§r ";

    public function onEnable() {

        @mkdir($this->getDataFolder());
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        $this->data = new Config($this->getDataFolder()."Size.yml",Config::YAML);
        $this->db = $this->data->getAll();
        $this->addCommand(["크기","크기변경","크기변경권","내크기"]);

    }

    public function onDisable() {
        $this->onsave();
    }

    public function addCommand($array) {
        $commandMap = $this->getServer()->getCommandMap();
        foreach ($array as $command) {
            $a = new PluginCommand($command, $this);
            $a->setDescription('크기 커맨드');
            $commandMap->register($command, $a);
        }
    }

    public function onsave(){

        $this->data->setAll($this->db);
        $this->data->save();

    }

    public function setsize($size,$player){

        $player->setScale($size);
        return true;

    }

    public function onjoin(PlayerJoinEvent $event){

        $pl = $event->getPlayer();
        $name = $pl->getName();

        if (!isset($this->db[$name]["SIZE"])){
            $this->db[$name]["SIZE"] = 1;
            $pl->sendMessage($this->sy."§l§f기본크기가 1로 설정되었습니다");
            $this->onsave();
            $this->setsize($this->db[$name]["SIZE"],$pl);
        }else{
            $this->setsize($this->db[$name]["SIZE"],$pl);
            $pl->sendMessage($this->sy."§l§f구입한 크기로 변경되었습니다");
        }

    }

    public function onMove(PlayerMoveEvent $event){

        $pl = $event->getPlayer();
        $name = $pl->getName();

        if ($pl->getLevel()->getBlock($pl->add(0,-1,0))->getId() == 152){

            $this->setsize(1,$pl);
            $pl->sendPopup($this->sy."§l§f레드스톤 블록을 밟아 크기가 1로 설정되었습니다!");
            return true;

        }elseif ($pl->getLevel()->getBlock($pl->add(0,-1,0))->getId() == 22){

            $this->setsize($this->db[$name]["SIZE"],$pl);
            $pl->sendPopup($this->sy."§l§f청금석 블록을 밟아 크기가 구입한 크기로 돌아갔습니다!");
            return true;

        }

    }

    public function onDeath(PlayerDeathEvent $event){

        $player = $event->getPlayer();
        $name = $player->getName();

        $player->setScale($this->db[$name]['SIZE']);

        $player->sendMessage($this->sy."§l§f죽어서 원래 크기로 돌아갔습니다!");

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {

        if ($sender instanceof Player){

            $name = $sender->getName();
            $item = $sender->getInventory()->getitemInHand();
            if ($command->getName() == "크기"){
                if ($sender->isOp()){
                    if (isset($args[0])){
                        if ((float)$args[0]>=0.01 and (int)$args[0]<=20){

                            $sender->sendMessage($this->sy."§l§f크기가 변경되었습니다!");
                            $this->db[$name]["SIZE"] = $args[0];
                            $this->onsave();
                            $this->setsize($this->db[$name]["SIZE"],$sender);

                            return true;

                        }else{

                            $sender->sendMessage($this->sy."§l§fOP권한으로 변경할수있는 최대한계를 넘어섰습니다!");

                        }
                    }else{

                        $sender->sendMessage($this->sy."§l§f크기를 입력해주세요!");
                        return true;

                    }

                }else{

                    $sender->sendMessage($this->sy."§l§fOP만 사용가능한 명령어입니다!");
                    return true;

                }

            }elseif ($command->getName() == "크기변경"){
                if (isset($args[0])){
                    if ($args[0] == "작아지기"){
                        if ($item->getId() == 399 and $item->getName() == "§e☆ §f크기코인 §e☆§r") {

                            if ($this->db[$name]["SIZE"] >= 0.6) {

                                $this->db[$name]["SIZE"] = $this->db[$name]["SIZE"] - 0.1;
                                $this->onsave();
                                $this->setsize($this->db[$name]["SIZE"],$sender);
                                $sender->sendMessage($this->sy."§l§f크기가 작아졌습니다!");
                                $sender->getInventory()->removeItem(Item::get(399,0,1)->setCustomName("§e☆ §f크기코인 §e☆§r"));
                                return true;

                            } else {

                                $sender->sendMessage($this->sy."§l§f더 작아지면 안됩니다!");
                                return true;

                            }
                        }else{

                            $sender->sendMessage($this->sy."§l§f크기코인을 손에 들고 실행해주세요!");
                            return true;

                        }
                    }elseif ($args[0] == "커지기"){
                        if ($item->getId() == 399 and $item->getName() == "§e☆ §f크기코인 §e☆§r") {
                            if ($this->db[$name]["SIZE"] <= 1.9) {

                                $this->db[$name]["SIZE"] = $this->db[$name]["SIZE"] + 0.1;
                                $this->onsave();
                                $this->setsize($this->db[$name]["SIZE"],$sender);
                                $sender->sendMessage($this->sy."§l§f크기가 작아졌습니다!");
                                $sender->getInventory()->removeItem(Item::get(399,0,1)->setCustomName("§e☆ §f크기코인 §e☆§r"));
                                return true;

                            } else {

                                $sender->sendMessage($this->sy."§l§f더 커지면 안됩니다!");
                                return true;

                            }
                        }else{

                            $sender->sendMessage($this->sy."§l§f크기코인을 손에 들고 실행해주세요!");
                            return true;

                        }
                    }else{

                        $sender->sendMessage($this->sy."§l§f/크기변경 [커지기|작아지기]");
                        return true;

                    }
                }
            }elseif ($command->getName() == "크기변경권"){

                if ($sender->isOp()){
                    $sender->getInventory()->addItem(Item::get(399,0,1)->setCustomName("§e☆ §f크기코인 §e☆§r"));
                    $sender->sendMessage($this->sy."§l§f크기코인이 지급되었습니다!");
                    return true;
                }else{

                    $sender->sendMessage($this->sy."§l§fOP만 사용가능한 명령어입니다!");
                    return true;

                }

            }elseif ($command->getName() == "내크기"){
                $sender->sendMessage($this->sy."§l§f당신의 크기는 ".$this->db[$name]["SIZE"]." 입니다");
                return true;
            }

            return true;

        }

        return true;

    }

    public function ontouch(PlayerInteractEvent $event){

        $pl = $event->getPlayer();
        $name = $pl->getName();
        $bl = $event->getBlock()->getId();
        $item = $pl->getInventory()->getItemInHand();

        if ($bl == 57){
            if ($item->getId() == 399 and $item->getName() == "§e☆ §f크기코인 §e☆§r"){
                if ($this->db[$name]["SIZE"]>=0.6){
                    $this->db[$name]["SIZE"] = $this->db[$name]["SIZE"] - 0.1;
                    $this->onsave();
                    $this->setsize($this->db[$name]["SIZE"],$pl);
                    $pl->getInventory()->removeItem(Item::get(399,0,1)->setCustomName("§e☆ §f크기코인 §e☆§r"));
                    $pl->sendPopup($this->sy."§l§f크기가 0.1 작아졌습니다!");
                    return true;

                }else{

                    $pl->sendPopup($this->sy."§l§f더 작아지면 안됩니다!");
                    return true;

                }

            }else{

                $pl->sendPopup($this->sy."§l§f크기코인을 손에 들고 이용해주세요!");
                return true;

            }


        }elseif ($bl == 133) {
            if ($item->getId() == 399 and $item->getName() == "§e☆ §f크기코인 §e☆§r") {
                if ($this->db[$name]["SIZE"] <= 1.9) {
                    $this->db[$name]["SIZE"] = $this->db[$name]["SIZE"] + 0.1;
                    $this->onsave();
                    $this->setsize($this->db[$name]["SIZE"], $pl);
                    $pl->getInventory()->removeItem(Item::get(399,0,1)->setCustomName("§e☆ §f크기코인 §e☆§r"));
                    $pl->sendPopup($this->sy."§l§f크기가 0.1 커졌습니다!");
                    return true;

                } else {

                    $pl->sendPopup($this->sy."§l§f더 커지면 안됩니다!");
                    return true;

                }

            }else{

                $pl->sendPopup($this->sy."§l§f크기코인을 손에 들고 이용해주세요!");
                return true;

            }
        }
    }

}