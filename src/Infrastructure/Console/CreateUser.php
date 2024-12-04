<?php

declare(strict_types=1);

namespace Querify\Infrastructure\Console;

use Querify\Application\Command\RegisterUser\RegisterUser;
use Querify\Domain\UserRole;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUser extends Command
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->setName('user:create')
            ->setDescription('Creates a new user.')
            ->setHelp('This command allows you to create a user/superuser.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $this->getUserData($input, $output, 'Email');
        $password = $this->getPassword($input, $output);
        $firstName = $this->getUserData($input, $output, 'First Name');
        $lastName = $this->getUserData($input, $output, 'Last Name');
        $role = $this->getRole($input, $output);

        $this->messageBus->dispatch(
            new RegisterUser(
                email: $email,
                plainPassword: $password,
                firstName: $firstName,
                lastName: $lastName,
                roles: [$role]
            )
        );

        $output->writeln(
            \sprintf('Successfully created user "%s".', $email)
        );

        return Command::SUCCESS;
    }


    private function getUserData(InputInterface $input, OutputInterface $output, string $userData): string
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question(
            \sprintf('[%s]: ', $userData)
        );

        return $helper->ask($input, $output, $question);
    }

    private function getPassword(InputInterface $input, OutputInterface $output): string
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question('[Password]:');
        $question->setHidden(true);

        return $helper->ask($input, $output, $question);
    }

    private function getRole(InputInterface $input, OutputInterface $output): string
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select role: ',
            UserRole::asArray(),
            UserRole::ROLE_USER->value
        );

        $question->setErrorMessage('Role %s is invalid.');

        return $helper->ask($input, $output, $question);
    }
}