<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use wataridori\ChatworkSDK\ChatworkSDK;
use wataridori\ChatworkSDK\ChatworkRoom;

class SendLoveMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:love-msg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send love message to recive nothing!';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $weatherInfor = $this->getWeatherForeCastInformationJSON();

        ChatworkSDK::setApiKey(config('services.chatwork.api_key'));
        $room = new ChatworkRoom(config('services.chatwork.room_id'));

        $room->sendMessageToAll($this->makeLoveMsg($weatherInfor));
    }

    public function getWeatherForeCastInformationJSON()
    {
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, "http://dataservice.accuweather.com/forecasts/v1/daily/1day/353412?apikey=kDJ6a6fH9jAPQUUeUSiqoSbA0Yf78AzN&language=vi&metric=true");

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $weatherInfor = json_decode(curl_exec($ch));

        // close curl resource to free up system resources
        curl_close($ch);

        return $weatherInfor;
    }

    public function makeLoveMsg($weatherInfor)
    {
        $loveMsg = "Dự báo thời tiết hôm nay có thể nắng, có thể mưa mà cũng có thể không nắng không mưa!!";

        if (property_exists($weatherInfor, 'Headline') && (property_exists($weatherInfor, 'DailyForecasts'))) {
            $header = $weatherInfor->Headline;
            $forecast = $weatherInfor->DailyForecasts;

            $text = $header->Text;
            $minTemperature = $forecast[0]->Temperature->Minimum->Value;
            $maxTemperature = $forecast[0]->Temperature->Maximum->Value;
            $dayWeather = $forecast[0]->Day->IconPhrase;
            $nightWeather = $forecast[0]->Night->IconPhrase;

            $loveMsg = <<<LOVEMSG
Thời tiết chung: {$text}
Nhiệt độ thấp nhất: {$minTemperature}
Nhiệt độ cao nhất: {$maxTemperature}
Nhiệt độ trong ngày: {$minTemperature} - {$maxTemperature}
Thời tiết ban ngày: {$dayWeather}
Thời tiết buổi tối: {$nightWeather}
LOVEMSG;
        }

        return $loveMsg;
    }
}
