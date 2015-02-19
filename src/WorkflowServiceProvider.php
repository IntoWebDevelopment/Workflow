<?php namespace Cerbero\Workflow;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Cerbero\Workflow\Inflectors\Inflector;
use Cerbero\Workflow\WorkflowRunnerInterface;
use Cerbero\Workflow\Wrappers\SymfonyYamlParser;
use Cerbero\Workflow\Repositories\YamlPipelineRepository;
use Cerbero\Workflow\Wrappers\LaravelTraitNamespaceDetector;

/**
 * Workflow service provider.
 *
 * @author	Andrea Marco Sartori
 */
class WorkflowServiceProvider extends ServiceProvider {

	/**
	 * @author	Andrea Marco Sartori
	 * @var		array	$commands	List of registered commands.
	 */
	protected $commands = [
		'cerbero.workflow.create',
		'cerbero.workflow.read',
		'cerbero.workflow.update',
		'cerbero.workflow.delete',
	];

	/**
	 * Boot the package up.
	 *
	 * @author	Andrea Marco Sartori
	 * @return	void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__ . '/config/workflow.php' => config_path('workflow.php')
		]);

		$this->commands($this->commands);

		$facade = 'Cerbero\Workflow\Facades\Workflow';

		AliasLoader::getInstance()->alias('Workflow', $facade);
	}

	/**
	 * Register the services.
	 *
	 * @author	Andrea Marco Sartori
	 * @return	void
	 */
	public function register()
	{
		$this->registerPipelineRepository();

		$this->registerInflector();

		$this->registerWorkflow();

		$this->registerWorkflowRunnersHook();

		$this->registerCommands();
	}

	/**
	 * Register the pipeline repository.
	 *
	 * @author	Andrea Marco Sartori
	 * @return	void
	 */
	private function registerPipelineRepository()
	{
		$abstract = 'Cerbero\Workflow\Repositories\PipelineRepositoryInterface';

		$this->app->bind($abstract, function($app)
		{
			return new YamlPipelineRepository
			(
				new SymfonyYamlParser,

				new \Illuminate\Filesystem\Filesystem,

				config('workflow.path')
			);
		});
	}

	/**
	 * Register the inflector.
	 *
	 * @author	Andrea Marco Sartori
	 * @return	void
	 */
	private function registerInflector()
	{
		$abstract = 'Cerbero\Workflow\Inflectors\InflectorInterface';

		$this->app->bind($abstract, function()
		{
			return new Inflector(new LaravelTraitNamespaceDetector);
		});
	}

	/**
	 * Register the package main class.
	 *
	 * @author	Andrea Marco Sartori
	 * @return	void
	 */
	private function registerWorkflow()
	{
		$this->registerDispatcher();

		$this->app->bindShared('cerbero.workflow', function($app)
		{
			return $app['Cerbero\Workflow\Workflow'];
		});
	}

	/**
	 * Add an alias to the bus dipatcher.
	 *
	 * @author	Andrea Marco Sartori
	 * @return	void
	 */
	private function registerDispatcher()
	{
		$abstract = 'Cerbero\Workflow\Wrappers\PipingDispatcherInterface';

		$this->app->bind($abstract, 'Cerbero\Workflow\Wrappers\Dispatcher');
	}

	/**
	 * Register the hook for the workflow runners.
	 *
	 * @author	Andrea Marco Sartori
	 * @return	void
	 */
	private function registerWorkflowRunnersHook()
	{
		$this->app->afterResolving(function(WorkflowRunnerInterface $runner, $app)
		{
			$runner->setWorkflow($app['cerbero.workflow']);
		});
	}

	/**
	 * Register the console commands.
	 *
	 * @author	Andrea Marco Sartori
	 * @return	void
	 */
	private function registerCommands()
	{
		foreach ($this->commands as $command)
		{
			$name = ucfirst(last(explode('.', $command)));

			$this->app->bindShared($command, function($app) use($name)
			{
				return $app["Cerbero\Workflow\Console\Commands\\{$name}WorkflowCommand"];
			});
		}
	}

}