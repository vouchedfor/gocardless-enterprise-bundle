<?php

namespace Vf\Bundle\GocardlessEnterpriseBundle\Command;

use GoCardless\Enterprise\Exceptions\ApiException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;


class CustomersListCommand extends BaseCommand {



    protected function configure()
    {
        $this
            ->setName('gocardless-enterprise:customers:list')
            ->setDescription('List gocardless customers');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute( $input,  $output);

        $customers = $this->getGocardlessClient()->listCustomers();

        $this->dumpModelArray($customers);

        return;
    }

}