<?php

namespace ImNotYourDev\PGToDiscord\Commands;

use ImNotYourDev\PGToDiscord\PGTD;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class DiscordCommand extends Command
{
    public function __construct()
    {
        $name = "discord";
        $description = "Discord main command";
        $usageMessage = "/discord <option>";
        $aliases = ["dc", "disc"];
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        switch ($this->getName()){
            case "help":
                //TODO: add help
                break;

            case "send":
                $message = implode(" ", $args);
                $data = array("user" => $sender->getName(), "message" => $message);
                PGTD::getInstance()->sendMessage($data, PGTD::TYPE_DIRECT);

                $sender->sendMessage(PGTD::getInstance()->settings["prefix"] . "Your Message sent to Discord!");
                break;

            case "enabled":
                if($args[1] == true or $args[1] == false){
                    PGTD::getInstance()->settings["enabled"] = $args[1];

                    if($args[1] == true){
                        $sender->sendMessage(PGTD::getInstance()->settings["prefix"] . "You enabled the Message sending to Discord!");
                        PGTD::getInstance()->sendMessage(array("message" => "Enabled Message sending to Discord! Welcome back world!"), PGTD::TYPE_PLUGIN);
                    }else{
                        $sender->sendMessage(PGTD::getInstance()->settings["prefix"] . "You enabled the Message sending to Discord!");
                        PGTD::getInstance()->sendMessage(array("message" => "Disabled Message sending to Discord! Bye world!"), PGTD::TYPE_PLUGIN);
                    }
                }
                break;
        }
    }
}