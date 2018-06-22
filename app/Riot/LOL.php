<?php

namespace App\Riot;

use Illuminate\Support\Collection;
use PhpParser\ErrorHandler\Collecting;
use RiotAPI\Objects;
use RiotAPI\RiotAPI;
use RiotAPI\Definitions\Region;


class LOL extends RiotAPI
{


    /**
     * LOL constructor.
     * @throws \RiotAPI\Exceptions\SettingsException
     */
    public function __construct()
    {

        parent::__construct([
            //  Your API key, you can get one at https://developer.riotgames.com/
            RiotAPI::SET_KEY    => 'RGAPI-f1d1a306-b2df-4410-823c-b6e285024059',
            //  Target region (you can change it during lifetime of the library instance)
            RiotAPI::SET_REGION => Region::LAMERICA_NORTH,
            RiotAPI::SET_STATICDATA_LINKING => true,
            RiotAPI::SET_CACHE_CALLS        => true,
            RiotAPI::RESOURCE_THIRD_PARTY_CODE
        ]);
    }

    /**
     * @param $user
     * @param $total
     * @return Collection
     * @throws \RiotAPI\Exceptions\RequestException
     * @throws \RiotAPI\Exceptions\ServerException
     * @throws \RiotAPI\Exceptions\ServerLimitException
     */
    public function getRanked($user, $total)
    {
        $games = $this->parseCollection($this->getMatchlistByAccount($user, [440, 420], null, null, null, null, null, $total)->matches);

        $details = new Collection();
        $games->each(function ($game) use ($details) {
            $details->push(
                $this->getMatch($game->gameId)
            );
        });

        return $details;
    }

    public function getParticipantId($participants, $user)
    {
        foreach ($participants as $participant){
            if($user->accountId == $participant->player->accountId or $user->accountId == $participant->player->currentAccountId){
                return $participant->participantId;
            }
        }
    }

    public function getDataUserGame($game, $pid)
    {
        foreach ($game->participants as $key => $data){
            if($data->participantId == $pid){
                return $data;
            }
        }

    }



    private function parseCollection($data)
    {
        return new Collection($data);
    }


}