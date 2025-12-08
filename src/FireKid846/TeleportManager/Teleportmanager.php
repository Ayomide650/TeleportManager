<?php

declare(strict_types=1);

namespace Firekid846\TeleportManager;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;
use pocketmine\world\Position;

class TeleportManager extends PluginBase {

    protected function onEnable(): void {
        $this->getLogger()->info(TF::GREEN . "TeleportManager by Firekid846 enabled!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "This command can only be used in-game!");
            return true;
        }

        switch ($command->getName()) {
            case "tp":
                return $this->handleTeleport($sender, $args);
            case "tphere":
                return $this->handleTeleportHere($sender, $args);
            case "tpall":
                return $this->handleTeleportAll($sender);
            case "tppos":
                return $this->handleTeleportPosition($sender, $args);
            case "tpworld":
                return $this->handleTeleportWorld($sender, $args);
        }

        return false;
    }

    private function handleTeleport(Player $sender, array $args): bool {
        if (!$sender->hasPermission("teleportmanager.tp")) {
            $sender->sendMessage(TF::RED . "You don't have permission to use this command!");
            return true;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TF::YELLOW . "Usage: /tp <player> or /tp <player1> <player2>");
            return true;
        }

        if (count($args) === 1) {
            $target = $this->getServer()->getPlayerByPrefix($args[0]);
            if ($target === null) {
                $sender->sendMessage(TF::RED . "Player not found!");
                return true;
            }
            $sender->teleport($target->getPosition());
            $sender->sendMessage(TF::GREEN . "Teleported to " . $target->getName());
        } else {
            $player1 = $this->getServer()->getPlayerByPrefix($args[0]);
            $player2 = $this->getServer()->getPlayerByPrefix($args[1]);
            
            if ($player1 === null || $player2 === null) {
                $sender->sendMessage(TF::RED . "One or both players not found!");
                return true;
            }
            
            $player1->teleport($player2->getPosition());
            $sender->sendMessage(TF::GREEN . "Teleported " . $player1->getName() . " to " . $player2->getName());
            $player1->sendMessage(TF::YELLOW . "You were teleported to " . $player2->getName() . " by " . $sender->getName());
        }

        return true;
    }

    private function handleTeleportHere(Player $sender, array $args): bool {
        if (!$sender->hasPermission("teleportmanager.tphere")) {
            $sender->sendMessage(TF::RED . "You don't have permission to use this command!");
            return true;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TF::YELLOW . "Usage: /tphere <player>");
            return true;
        }

        $target = $this->getServer()->getPlayerByPrefix($args[0]);
        if ($target === null) {
            $sender->sendMessage(TF::RED . "Player not found!");
            return true;
        }

        $target->teleport($sender->getPosition());
        $sender->sendMessage(TF::GREEN . "Teleported " . $target->getName() . " to you");
        $target->sendMessage(TF::YELLOW . "You were teleported to " . $sender->getName());

        return true;
    }

    private function handleTeleportAll(Player $sender): bool {
        if (!$sender->hasPermission("teleportmanager.tpall")) {
            $sender->sendMessage(TF::RED . "You don't have permission to use this command!");
            return true;
        }

        $count = 0;
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            if ($player->getName() !== $sender->getName()) {
                $player->teleport($sender->getPosition());
                $player->sendMessage(TF::YELLOW . "You were teleported to " . $sender->getName());
                $count++;
            }
        }

        $sender->sendMessage(TF::GREEN . "Teleported " . $count . " players to you");

        return true;
    }

    private function handleTeleportPosition(Player $sender, array $args): bool {
        if (!$sender->hasPermission("teleportmanager.tppos")) {
            $sender->sendMessage(TF::RED . "You don't have permission to use this command!");
            return true;
        }

        if (count($args) < 3) {
            $sender->sendMessage(TF::YELLOW . "Usage: /tppos <x> <y> <z> [world]");
            return true;
        }

        $x = (float)$args[0];
        $y = (float)$args[1];
        $z = (float)$args[2];
        
        $world = isset($args[3]) ? $this->getServer()->getWorldManager()->getWorldByName($args[3]) : $sender->getWorld();
        
        if ($world === null) {
            $sender->sendMessage(TF::RED . "World not found!");
            return true;
        }

        $sender->teleport(new Position($x, $y, $z, $world));
        $sender->sendMessage(TF::GREEN . "Teleported to X: $x, Y: $y, Z: $z");

        return true;
    }

    private function handleTeleportWorld(Player $sender, array $args): bool {
        if (!$sender->hasPermission("teleportmanager.tpworld")) {
            $sender->sendMessage(TF::RED . "You don't have permission to use this command!");
            return true;
        }

        if (count($args) < 1) {
            $worlds = [];
            foreach ($this->getServer()->getWorldManager()->getWorlds() as $world) {
                $worlds[] = $world->getFolderName();
            }
            $sender->sendMessage(TF::YELLOW . "Available worlds: " . implode(", ", $worlds));
            $sender->sendMessage(TF::YELLOW . "Usage: /tpworld <world>");
            return true;
        }

        $world = $this->getServer()->getWorldManager()->getWorldByName($args[0]);
        
        if ($world === null) {
            $sender->sendMessage(TF::RED . "World not found!");
            return true;
        }

        $sender->teleport($world->getSpawnLocation());
        $sender->sendMessage(TF::GREEN . "Teleported to world: " . $world->getFolderName());

        return true;
    }
}