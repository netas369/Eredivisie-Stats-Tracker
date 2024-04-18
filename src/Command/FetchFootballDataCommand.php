<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsCommand(
    name: 'app:fetch-football-data',
    description: 'Fetches football data from the API.'
)]
class FetchFootballDataCommand extends Command
{
    protected static $defaultName = 'app:fetch-football-data';

    private $client;
    private $cache;

    public function __construct(HttpClientInterface $client, CacheInterface $footballCache)
    {
        $this->client = $client;
        $this->cache = $footballCache;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Fetches football data from the API.')
            ->setHelp('This command allows you to fetch teams data from the Eredivisie league...')
            ->addArgument('teamId', InputArgument::OPTIONAL, 'The ID of the team to fetch matches for');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $teamId = $input->getArgument('teamId');

        if ($teamId) {
            $matchesData = $this->fetchAndCacheData(
                'football_team_matches_' . $teamId,
                "https://api.football-data.org/v4/teams/{$teamId}/matches/",
                $io
            );
            // Output the matches for the specific team
            $io->title('Match Details for Team ID: ' . $teamId);
            foreach ($matchesData['matches'] as $match) {
                $io->writeln($match['homeTeam']['name'] . ' vs ' . $match['awayTeam']['name'] . ' - ' . $match['utcDate']);
            }
        } else {
            $teamsData = $this->fetchAndCacheData(
                'football_teams',
                "https://api.football-data.org/v2/competitions/DED/teams",
                $io
            );
            // Output the list of teams
            $io->title('Teams in the Eredivisie League:');
            foreach ($teamsData['teams'] as $team) {
                $io->writeln($team['name']);
            }
        }

        return Command::SUCCESS;
    }

    private function fetchAndCacheData(string $cacheKey, string $url, SymfonyStyle $io): array
    {
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($url, $io) {
            $item->expiresAfter(3600); // Cache expires after one hour

            $io->comment('Fetching data from the API...');
            $response = $this->client->request(
                'GET',
                $url,
                ['headers' => ['X-Auth-Token' => '1828402b90f84baa952ffca2fe9b3b53']]
            );

            if ($response->getStatusCode() !== 200) {
                $io->error('Failed to fetch data from API: Status code ' . $response->getStatusCode());
                throw new \RuntimeException('Failed to fetch data from API: Status code ' . $response->getStatusCode());
            }

            $io->success('Data successfully fetched from the API.');
            return $response->toArray();
        });
    }
}
