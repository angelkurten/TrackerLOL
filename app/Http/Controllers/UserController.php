<?php

namespace App\Http\Controllers;

use App\Riot\LOL;

class UserController extends Controller
{

    private $user;
    private $totalGames = 3;
    private $api;

    /**
     * UserController constructor.
     * @throws \RiotAPI\Exceptions\SettingsException
     */
    public function __construct()
    {
        $this->api = new LOL();
    }

    /**
     * @param $user
     * @throws \RiotAPI\Exceptions\RequestException
     * @throws \RiotAPI\Exceptions\ServerException
     * @throws \RiotAPI\Exceptions\ServerLimitException
     */
    public function index($user){
        $gamers = ['Signífer', 'AngelKurten'];
        foreach ($gamers as $gamer){
            $this->user = $this->api->getSummonerByName($gamer);
            $games = $this->api->getRanked($this->user->accountId, $this->totalGames);
            $result = $this->getDataGames($games);
            $show[$this->user->accountId]['name'] = $this->user->name;
            $show[$this->user->accountId] += $this->generateAverage($result);
        }

        dd($show);
    }

    /**
     * @param $games
     * @return array
     */
    private function getDataGames($games): array
    {
        $result = [
            'duration' => 0,
            'totalCs' => 0,
            'csPerMinTotal' => 0,
            'deltas' => [],
            'gold' => 0,
            'wardsPlaced' => 0,
        ];

        foreach ($games as $game) {
            $pid = $this->api->getParticipantId($game->participantIdentities, $this->user);
            $data = $this->api->getDataUserGame($game, $pid);

            $result['duration'] += $game->gameDuration / 60;
            $result['totalCs'] += $data->stats->totalMinionsKilled + $data->stats->neutralMinionsKilledTeamJungle;
            $result['csPerMinTotal'] += number_format($result['totalCs'] / $result['duration'], 2);
            $result['deltas'] += $data->timeline->creepsPerMinDeltas;
            foreach ($data->timeline->creepsPerMinDeltas as $key => $delta) {
                if (array_key_exists($key, $result['deltas'])) {
                    $result['deltas'][$key] += $data->timeline->creepsPerMinDeltas[$key];
                } else {
                    $result['deltas'][$key] = $data->timeline->creepsPerMinDeltas[$key];
                }
            }
            $result['gold'] += $data->stats->goldEarned;
            $result['wardsPlaced'] += $data->stats->wardsPlaced;
        }

        return $result;
    }

    /**
     * @param $result
     * @return array
     */
    private function generateAverage($result): array
    {
        $parse = [];
        foreach ($result as $key => $value) {
            if ($key == 'deltas') {
                foreach ($value as $time => $delta) {
                    $parse['deltas'][$time] = number_format($delta / $this->totalGames, 2);
                }
            } else {
                $parse[$key] = number_format($value / $this->totalGames, 2);
            }
        }

        return $parse;
    }


}
