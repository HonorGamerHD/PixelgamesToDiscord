<?php

namespace ImNotYourDev\PGToDiscord\Tasks;

use ImNotYourDev\PGToDiscord\PGTD;
use pocketmine\scheduler\AsyncTask;

class sendToDiscordAsyncTask extends AsyncTask
{
    public $message;
    public $webhooks;

    public function __construct(?array $message, ?array $webhooks)
    {
        $this->message = $message;
        $this->webhooks = $webhooks;
    }

    public function onRun()
    {
        foreach($this->webhooks as $webhook){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $webhook);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->message));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($curl);
        }
    }
}