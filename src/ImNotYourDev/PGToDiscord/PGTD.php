<?php

namespace ImNotYourDev\PGToDiscord;

use _64FF00\PurePerms\PurePerms;
use ImNotYourDev\PGToDiscord\Commands\DiscordCommand;
use ImNotYourDev\PGToDiscord\Tasks\sendToDiscordAsyncTask;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class PGTD extends PluginBase
{
    public static $instance;
    /** @var array $settings */
    public $settings = [];

    const TYPE_CHAT = 0;
    const TYPE_DIRECT = 1;
    const TYPE_REPORT = 3;
    const TYPE_PLUGIN = 4;

    public function onEnable()
    {
        self::$instance = $this;
        $this->saveResource("config.yml", false);
        $this->getSettings();
        if(!$this->validateSettings()){
            $this->getLogger()->error("Your config has an issue, please fix or delete ur customized config!");
            $this->setEnabled(false);
        }
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getCommandMap()->register("discord", new DiscordCommand());

        $this->sendMessage(array("message" => "Plugin Enabled! Welcome back world!"), self::TYPE_PLUGIN);
    }

    public function onDisable()
    {
        $this->sendMessage(array("message" => "Plugin Disabled! Bye world!"), self::TYPE_PLUGIN);
    }

    /**
     * @return PGTD
     */
    public static function getInstance(): PGTD
    {
        return self::$instance;
    }

    /**
     * get the settings out of config
     */
    public function getSettings()
    {
        $cfg = new Config($this->getDataFolder() . "config.yml");
        $this->settings = $cfg->get("settings", []);
    }

    /**
     * validate the settings, returns false if something is missing
     *
     * @return bool
     * TODO: add check for messages
     */
    public function validateSettings(): bool
    {
        if(!isset($this->settings["prefix"])){
            return false;
        }
        if(!isset($this->settings["messages"])){
            return false;
        }
        if(!isset($this->settings["webhooks"])){
            return false;
        }
        if(!isset($this->settings["enabled"])){
            return false;
        }
        if(!isset($this->settings["name"])){
            return false;
        }

        return true;
    }

    /**
     * @param array|null $data
     * @param int|null $type
     * TODO: maybe add check for wrong function use.
     */
    public function sendMessage(?array $data, ?int $type = self::TYPE_CHAT): void
    {
        /** @var PurePerms $pp */
        $pp = $this->getServer()->getPluginManager()->getPlugin("PurePerms");

        if($type == self::TYPE_CHAT){
            $player = $this->getServer()->getPlayer($data["user"]);
            $info = ["rank" => null, "player" => $player];
            if($player != null){
                $info["rank"] = $pp->getUserDataMgr()->getGroup($player);
            }

            $message = str_replace("{user}", $data["user"], $this->settings["messages"]["chat"]);
            $message = str_replace("{rank}", $info["rank"], $message);
            $message = str_replace("{message}", $data["message"], $message);
            $message = array("name", $this->settings["name"], "content" => $message);

            $this->getServer()->getAsyncPool()->submitTask(new sendToDiscordAsyncTask($message, $this->settings["webhooks"]));
        }elseif($type == self::TYPE_DIRECT){
            $player = $this->getServer()->getPlayer($data["user"]);
            $info = ["rank" => null, "player" => $player];
            if($player != null){
                $info["rank"] = $pp->getUserDataMgr()->getGroup($player);
            }

            $message = str_replace("{user}", $data["user"], $this->settings["messages"]["direct"]);
            $message = str_replace("{rank}", $info["rank"], $message);
            $message = str_replace("{message}", $data["message"], $message);
            $message = array("name", $this->settings["name"], "content" => $message);

            $this->getServer()->getAsyncPool()->submitTask(new sendToDiscordAsyncTask($message, $this->settings["webhooks"]));
        }elseif($type == self::TYPE_REPORT){
            $message = str_replace("{reporter}", $data["reporter"], $this->settings["messages"]["report"]);
            $message = str_replace("{reported}", $data["reported"], $message);
            $message = str_replace("{reason}", $data["reason"], $message);
            $message = array("name", $this->settings["name"], "content" => $message);

            $this->getServer()->getAsyncPool()->submitTask(new sendToDiscordAsyncTask($message, $this->settings["webhooks"]));
        }else{
            $message = array("name" => $this->settings["name"], "content" => $data["message"]);
            $this->getServer()->getAsyncPool()->submitTask(new sendToDiscordAsyncTask($message, $this->settings["webhooks"]));
        }
    }
}