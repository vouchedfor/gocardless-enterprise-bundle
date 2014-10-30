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


class CreditorsDiagnoseCommand extends BaseCommand {



    protected function configure()
    {
        $this
            ->setName('gocardless-enterprise:creditors:diagnose')
            ->setDescription('Dump some information about Gocardless setup in Symfony and the Creditors');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute( $input,  $output);

        $this->showGocardlessSetup();
        $this->showCreditors();
        $this->showPrimaryCreditor();

        return;
    }

    private function showCreditors()
    {
        $output = $this->output;
        $creditors = $this->getGocardlessClient()->listCreditors();

        $output->writeln( "*******************************" );
        $output->writeln( "*        All Creditors        *" );
        $output->writeln( "*******************************" );
        $output->writeln( "" );
        $output->writeln( "There are currently ".count($creditors)." creditors in this Gocardless Environment, listed below" );
        $output->writeln( "" );

        foreach ($creditors as $i => $creditor)
        {
            $this->showOneCreditor($creditor);
        }

    }

}