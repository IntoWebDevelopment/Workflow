<?php namespace Cerbero\Workflow;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Cerbero\Workflow\Scaffolding\GeneratorInterface as Scaffolding;

class WorkflowCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'workflow';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Speed up the workflow to add new features.';

	/**
	 * @author	Andrea Marco Sartori
	 * @var		Cerbero\Workflow\Scaffolding\GeneratorInterface	$scaffolding	Scaffolding generator.
	 */
	protected $scaffolding;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(Scaffolding $scaffolding)
	{
		parent::__construct();

		$this->scaffolding = $scaffolding;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$workflow = $this->getWorkflow();

		$this->scaffolding->generate($workflow);

		$this->info("The workflow [{$workflow->name}] has been created successfully.");
	}

	/**
	 * Retrieve the workflow data.
	 *
	 * @author	Andrea Marco Sartori
	 * @return	array
	 */
	protected function getWorkflow()
	{
		$data = $this->argument() + $this->option();

		return with(new WorkflowDataTransfer($data))
						->setMethod($this->ask('Trigger method name: ', 'run'))
						->setDecorators($this->ask('Decorators: '));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'The name of the workflow.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}

}