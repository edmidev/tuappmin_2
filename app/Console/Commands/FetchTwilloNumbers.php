<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Twilio\Rest\Client;
use App\Models\TwilloNumber;


class FetchTwilloNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twillo:fetch-numbers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $sid = config('twillio.id');
        $token = config('twillio.token');
        $twilio = new Client($sid, $token);

        $twilloNumbers = $twilio->incomingPhoneNumbers
            ->read([], 20);

        foreach ($twilloNumbers as $number) {
            $n = TwilloNumber::where('sid', $number->sid)->first();
            if ($n) {
                $n->status = $number->status;
                $n->save();
                $n->updated_at = now();

                echo $number->phoneNumber . ' Actualizado con exito.' . PHP_EOL;
            } else {
                $n = new TwilloNumber();
                $n->sid = $number->sid;
                $n->phone_number = $number->phoneNumber;
                $n->status = $number->status;
                $n->save();

                echo $number->phoneNumber . ' Registrado con exito.' . PHP_EOL;
            }
        }
    }
}
