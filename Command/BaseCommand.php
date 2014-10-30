<?php


namespace Vouchedfor\GocardlessEnterpriseBundle\Command;

use GoCardless\Enterprise\Exceptions\ApiException;
use GoCardless\Enterprise\Model\CreditorBankAccount;
use GoCardless\Enterprise\Model\Model;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;


abstract class BaseCommand extends ContainerAwareCommand
{

    const PARAM_PRIMARY_CREDITOR_ID = 'vf_gocardless_enterprise.primary_creditor_id';

    protected $input;
    protected $output;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

    }


    protected function ask(Question $question)
    {
        $helper = $this->getHelper('question');

        return $helper->ask($this->input, $this->output, $question);
    }

    protected function showGocardlessSetup()
    {
        $gcUsername = $this->getContainer()->getParameter('vf_gocardless_enterprise.username');
        $gcMode = $this->getContainer()->getParameter('vf_gocardless_enterprise.sandbox') ? 'sandbox data' : 'real data';
        $this->output->writeln( "" );
        $this->output->writeln( "Current Gocardless Envrionment: {$gcUsername} ({$gcMode})" );

    }


    protected function showPrimaryCreditor()
    {
        $output = $this->output;

        $output->writeln( "*******************************" );
        $output->writeln( "*      Primary Creditor       *" );
        $output->writeln( "*******************************" );
        $output->writeln( "" );

        try
        {
            $primaryCreditorId = $this->getContainer()->getParameter(self::PARAM_PRIMARY_CREDITOR_ID);
        }
        catch(InvalidArgumentException $e)
        {
            $output->writeln( "" );
            $output->writeln( "Symfony parameter '".self::PARAM_PRIMARY_CREDITOR_ID."' is not available, cannot continue with details of the primary creditor" );
            $output->writeln( "" );
            return false;
        }

        $output->writeln( "" );
        $output->writeln( "Symfony parameter '".self::PARAM_PRIMARY_CREDITOR_ID."' is set to '".$primaryCreditorId."'" );
        $output->writeln( "" );

        try
        {
            $primaryCreditor = $this->getGocardlessClient()->getCreditor($primaryCreditorId);
        }
        catch(ApiException $e)
        {
            $output->writeln( "" );
            $output->writeln( "There does not seem to be a valid creditor with ID '".$primaryCreditorId."'" );
            $output->writeln( "" );
            return false;
        }

        $output->writeln( "" );
        $output->writeln( "Details of the primary creditor:" );
        $output->writeln( "" );
        $this->showOneCreditor($primaryCreditor);

        return $primaryCreditor;

    }

    /**
     * @return \GoCardless\Enterprise\Client
     */
    protected function getGocardlessClient()
    {
        return $this->getContainer()->get('vouchedfor.gocardless_enterprise.client');
    }

    protected function showOneCreditor($creditor)
    {
        $output = $this->output;
        $output->writeln( "Creditor Id: ".$creditor->getId() );
        $output->writeln( "Creditor Name: ".$creditor->getName() );
        $output->writeln( "Address Line 1: ".$creditor->getAddressLine1() );
        $output->writeln( "Address Line 2: ".$creditor->getAddressLine2() );
        $output->writeln( "Address Line 3: ".$creditor->getAddressLine3() );
        $output->writeln( "City: ".$creditor->getCity() );
        $output->writeln( "Region: ".$creditor->getRegion() );
        $output->writeln( "Postal Code: ".$creditor->getPostalCode() );
        $output->writeln( "Country Code: ".$creditor->getCountryCode() );
        $output->writeln( "------------" );
    }

    protected function showCreditorBankAccount(CreditorBankAccount $account)
    {
        $output = $this->output;
        $output->writeln( "Creditor Bank Account Id: ".$account->getId() );
        $output->writeln( "Account holder name: ".$account->getAccountHolderName() );
        $output->writeln( "Account number ending: ".$account->getAccountNumberEnding() );
        $output->writeln( "Country code: ".$account->getCountryCode() );
        $output->writeln( "Currency: ".$account->getCurrency() );
        $output->writeln( "Bank Name: ".$account->getBankName() );
        $output->writeln( "------------" );

    }


    protected function dumpModel(Model $model)
    {
        print 'PHP class: '.get_class($model).PHP_EOL;
        print_r($model->toArray() );
    }

    protected function dumpModelArray($arr)
    {
        foreach ($arr as $model)
        {
            $this->dumpModel($model);
        }

    }


}