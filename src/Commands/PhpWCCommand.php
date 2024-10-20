<?php

namespace Eldeeno\PhpWcTool\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PhpWCCommand extends Command
{

    protected function configure()
    {
        $this->setName('ccwc')
            ->setDescription('Custom wc tool for counting lines, words, characters, and bytes.')
            ->addArgument('file', InputArgument::OPTIONAL, 'The file to process') // Optional to allow for piped input
            ->addOption('lines', 'l', InputOption::VALUE_NONE, 'Print the newline counts')
            ->addOption('words', 'w', InputOption::VALUE_NONE, 'Print the word counts')
            ->addOption('chars', 'm', InputOption::VALUE_NONE, 'Print the character counts')
            ->addOption('bytes', 'c', InputOption::VALUE_NONE, 'Print the byte counts');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');
        $content = '';

        // Check if input is piped or a file is provided
        if ($file) {
            // Read from the provided file
            if (!file_exists($file)) {
                $io->error("The file '$file' does not exist.");
                return Command::FAILURE;
            }
            $content = file_get_contents($file);
        } elseif (!posix_isatty(STDIN)) {
            // Handle piped input (read from STDIN)
            $content = stream_get_contents(STDIN);
        } else {
            $io->error('No file provided, and no input piped.');
            return Command::FAILURE;
        }

        // Calculate the metrics
        $lines = substr_count($content, PHP_EOL);
        $words = str_word_count($content);
        $chars = mb_strlen($content);
        $bytes = strlen($content);

        $outputCount = false;

        // Output based on flags
        if ($input->getOption('lines')) {
            $io->text("  $lines");
            $outputCount = true;
        }
        if ($input->getOption('words')) {
            $io->text("  $words");
            $outputCount = true;
        }
        if ($input->getOption('chars')) {
            $io->text("  $chars");
            $outputCount = true;
        }
        if ($input->getOption('bytes')) {
            $io->text("  $bytes");
            $outputCount = true;
        }

        // If no specific flag is provided, display all counts by default
        if (!$outputCount) {
            $io->text("  $bytes $lines $words");
        }

        return Command::SUCCESS;
    }
}