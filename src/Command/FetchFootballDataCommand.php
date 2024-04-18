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
            ->setHelp('This command allows you to fetch teams data from the Eredivisie league...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Fetching Football Data',
            '============',
            '',
        ]);

        $teamsData = $this->cache->get('football_teams', function (ItemInterface $item) {
            $item->expiresAfter(3600);  // Cache expires after one hour

            // Fetch the data from the API
            $response = $this->client->request(
                'GET',
                'https://api.football-data.org/v2/competitions/DED/teams',
                ['headers' => ['X-Auth-Token' => '1828402b90f84baa952ffca2fe9b3b53']]
            );

            if ($response->getStatusCode() !== 200) {
                throw new \RuntimeException('Failed to fetch data from API');
            }

            return $response->toArray();
        });

        // Output cached data or fetched data
        foreach ($teamsData['teams'] as $team) {
            $output->writeln($team['name']);
        }

        return Command::SUCCESS;
    }
}
