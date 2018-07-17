<?php

namespace App\Services;

use Psr\Log\LoggerInterface;

class MovieAPI
{
    private $url = 'http://api.themoviedb.org/3/';
    private $proxy = 'proxy-sh.ad.campus-eni.fr:8080';
    private $api_key = 'bfff8381b65e5601a54e534afd05b540';
    private $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
    }

    public function searchMovie(string $search)
    {
        try {
//            die("sdg");
            $res = $this->client->request('POST', 'https://api.themoviedb.org/3/search/movie', [
                'form_params' => [
                    'query' => $search,
                    'api_key' => $this->api_key,
                ],
                'curl'  => [
                    CURLOPT_PROXY => $this->proxy,
                ],
            ]);
//
//
//            throw new \Exception();
//
            $res = json_decode($res->getBody()->getContents())->{'results'};
            $res = $this->formatMovie($res);
            return $res;
//            return [];
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function formatMovie($movies)
    {
        $moviesList = [];

        foreach ($movies as $movie) {


            $res = $this->client->request('GET', 'https://api.themoviedb.org/3/movie/'.$movie->{'id'}.'?append_to_response=credits', [
                'form_params' => [
                    'api_key' => $this->api_key,
                ],
                'curl'  => [
                    CURLOPT_PROXY => $this->proxy,
                ],
            ]);
            $res = json_decode($res->getBody()->getContents());
            $director = (isset($res->{'credits'}->{'crew'}[0]->{'name'})) ? $res->{'credits'}->{'crew'}[0]->{'name'} : "X";
            $duration = ($res->{'runtime'}) ? $res->{'runtime'} : 0;
            $releaseDate = ($res->{'release_date'}) ? $res->{'release_date'} : '2000-01-01';
            $synopsis = ($res->{'overview'}) ? $res->{'overview'} : "overview";
            $picture = ($res->{'poster_path'}) ? $res->{'poster_path'} : null;

            $genres = [];
            foreach ($res->{'genres'} as $genre) {
                $genres[] = $genre->{'name'};
            }

            $movie = [
                'title' => $movie->{'title'},
                'director' => $director,
                'duration' => $duration,
                'releaseDate' => $releaseDate,
                'synopsis' => $synopsis,
                'picture' => $picture,
                'category' => $genres,
            ];
            $moviesList[] = $movie;
        }
        return json_encode($moviesList);
    }
}
