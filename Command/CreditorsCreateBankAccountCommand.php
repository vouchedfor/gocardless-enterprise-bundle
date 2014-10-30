<?php

namespace Vf\Bundle\GocardlessEnterpriseBundle\Command;

use GoCardless\Enterprise\Exceptions\ApiException;
use GoCardless\Enterprise\Model\Creditor;
use GoCardless\Enterprise\Model\CreditorBankAccount;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;


class CreditorsCreateBankAccountCommand extends BaseCommand {



    protected function configure()
    {
        $this
            ->setName('gocardless-enterprise:creditors:create-bank-account')
            ->setDescription('Interface to add new CreditorBankAccount to the primary Creditor');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute( $input,  $output);

        if (! $primaryCreditor = $this->showPrimaryCreditor() )
        {
            $output->writeln( "" );
            $output->writeln( "Please ensure the primary creditor is setup correctly before running this" );
            return;
        }


        if (isset($primaryCreditor->toArray()['links']['default_gbp_payout_account']) )
        {
            $defaultGdpAccount = $primaryCreditor->toArray()['links']['default_gbp_payout_account'];
        }
        else
        {
            $defaultGdpAccount = false;
        }


        if ($defaultGdpAccount)
        {
            $output->writeln( "" );
            $output->writeln( "This creditor has a default Gdp account set with Id '".$defaultGdpAccount."'" );
            $output->writeln( "" );
            $output->writeln( "Current account details" );

            $account = $this->getGocardlessClient()->getCreditorBankAccount($defaultGdpAccount);
            $this->showCreditorBankAccount($account);
        }
        else
        {
            $output->writeln( "" );
            $output->writeln( "This creditor does not have a default Gdp account set" );
        }


        $output->writeln( "" );
        $continue = $this->ask(new ConfirmationQuestion('Would you like to create a new default account?') );
        if (! $continue)
        {
            return;
        }

        $accountNumber = $this->ask( new Question('Bank account number:') );
        $sortCode = $this->ask( new Question('Sort Code:') );
        $accountHolderName = $this->ask( new Question('Account holder name:') );


        $creditorBankAccount = new CreditorBankAccount();
        $creditorBankAccount->setAccountNumber($accountNumber); //55779922
        $creditorBankAccount->setSortCode($sortCode); //200000
        $creditorBankAccount->setAccountHolderName($accountHolderName);
        $creditorBankAccount->setCountryCode('GB');
        $creditorBankAccount->setCreditor($primaryCreditor);

        $newAccount = $this->getGocardlessClient()->createCreditorBankAccount($creditorBankAccount, true);
        $output->writeln( "" );
        $output->writeln( "Details of the new account just created" );
        $output->writeln( "" );
        $this->showCreditorBankAccount($newAccount);

        return;
    }

}