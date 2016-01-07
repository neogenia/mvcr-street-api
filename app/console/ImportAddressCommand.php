<?php

namespace Console;

use StreetApi\Services\ImportAddressService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ImportAddressCommand extends Command
{

	protected function configure()
	{
		$this->setName('import:address')
			->setDescription('Import new addresses')
			->addArgument('cityId', InputArgument::OPTIONAL);
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var ImportAddressService $importAddressService */
		$importAddressService = $this->getHelper('container')->getByType('StreetApi\Services\ImportAddressService');

		$cityId = $input->getArgument('cityId');

		$xmlFile = simplexml_load_file($importAddressService->getRootDir() . '/../adresy.xml');

		try {
			$output->writeLn('<info>Start importing addresses</info>');
			$totalCount = $xmlFile->count();
			$output->writeln(PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL);
			$progressBar = new ProgressBar($output, $totalCount);
			$progressBar->setFormat('%message%' . PHP_EOL . '%bar% %percent:3s% %' . PHP_EOL . 'count: %current%/%max%' . PHP_EOL . 'time:  %elapsed:6s%/%estimated:-6s%' . PHP_EOL);
			$progressBar->setBarCharacter('<info>■</info>');
			$progressBar->setEmptyBarCharacter(' ');
			$progressBar->setProgressCharacter('');
			$progressBar->setRedrawFrequency(ceil(($totalCount / 100)));
			$progressBar->start();

			$importAddressService->import($xmlFile, $progressBar, $cityId);

			$output->writeLn(PHP_EOL . '<info>Importing addresses finished</info>');
			return 0;
		} catch (\Exception $e) {
			$output->writeLn('<error>' . $e->getMessage() . '</error>');
			return 1;
		}

	}

}
