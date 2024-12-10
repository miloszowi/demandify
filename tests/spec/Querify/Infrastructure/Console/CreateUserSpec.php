<?php

declare(strict_types=1);

namespace spec\Querify\Infrastructure\Console;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Querify\Application\Command\RegisterUser\RegisterUser;
use Querify\Infrastructure\Console\CreateUser;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateUserSpec extends ObjectBehavior
{
    public function let(MessageBusInterface $messageBus): void
    {
        $this->beConstructedWith($messageBus);
    }

    public function it_is_initializable(MessageBusInterface $messageBus): void
    {
        $this->shouldHaveType(CreateUser::class);
    }

    public function it_should_configure_command(): void
    {
        $this->configure();
        $this->getName()->shouldBe('user:create');
        $this->getDescription()->shouldBe('Creates a new user.');
        $this->getHelp()->shouldBe('This command allows you to create a user/superuser.');
    }

    public function it_should_execute_user_creation(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $helper,
        MessageBusInterface $messageBus,
        HelperSet $helperSet
    ): void {
        $helperSet->get('question')->willReturn($helper);
        $this->setHelperSet($helperSet);
        $helper->ask($input, $output, Argument::type('object'))
            ->willReturn('example@local.host', 'Test User', 'ROLE_USER')
        ;

        $mockEnvelope = new Envelope(new \stdClass());
        $messageBus->dispatch(Argument::type(RegisterUser::class))
            ->shouldBeCalled()
            ->willReturn($mockEnvelope)
        ;

        $result = $this->execute($input, $output);

        $result->shouldBe(0);
    }
}
