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


class RawRequestCommand extends BaseCommand {



    protected function configure()
    {
        $this
            ->setName('gocardless-enterprise:raw-request')
            ->setDescription('Run any API call. TODO - this feature is not complete');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute( $input,  $output);


        //TODO - just hard coded at the moment, these should come from command line input or something.
        $endpoint = 'roles';
        $body = '';
        $method = 'get';

        $responseContent = $this->getGocardlessClient()->rawRequest($endpoint, $body, $method);

        $output->writeln( '' );
        $output->write( $responseContent );
        $output->writeln( '' );

        return;
    }

}