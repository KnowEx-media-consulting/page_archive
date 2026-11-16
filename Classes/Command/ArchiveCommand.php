<?php

declare(strict_types=1);

namespace Knowex\PageArchive\Command;

use Knowex\PageArchive\Exception\ArchiveException;
use Knowex\PageArchive\Service\ArchiveService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Bootstrap;

#[AsCommand('archive:page', 'Archives a page including all subpages, or restores it with --restore.')]
final class ArchiveCommand extends Command
{
    public function __construct(private readonly ArchiveService $archiveService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('uid', InputArgument::REQUIRED, 'Uid of the page')
            ->addOption('restore', 'r', InputOption::VALUE_NONE, 'Restore the archived page instead of archiving it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // Der DataHandler braucht einen Backend-Benutzer.
        Bootstrap::initializeBackendAuthentication();

        $uid = (int)$input->getArgument('uid');
        try {
            if ($input->getOption('restore')) {
                $io->success(sprintf('%d page(s) restored.', $this->archiveService->restore($uid)));
            } else {
                $io->success(sprintf('%d page(s) archived.', $this->archiveService->archive($uid)));
            }
        } catch (ArchiveException $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
