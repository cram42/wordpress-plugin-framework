<?php

namespace WPPluginFramework\Data;

use WPPluginFramework\{
    Logger,
    WPFParentObject
};
use WPPluginFramework\Events\IRESTInitEvent;

abstract class API extends WPFParentObject implements IRESTInitEvent
{
    #region Protected Properties

    protected string|null $api_root = null;
    protected int $api_version = 1;

    /**
     * Array of fully-qualified class names of RESTResource to load on startup.
     * @var array<string>
     */
    protected array $resources = [];

    #endregion
    #region Constructor

    public function __construct()
    {
        parent::__construct();
        $this->loadResources();
    }

    #endregion
    #region Public Methods

    public function getAPIRoot(): string
    {
        if (!$this->api_root) {
            $root = get_called_class();
            $root = preg_replace('/API$/', '', $root);
            $root = str_replace('\\', '/', $root);
            $root = strtolower($root);
            $this->api_root = $root;
        }

        return sprintf(
            '%s/v%d',
            rtrim($this->api_root, '/'),
            $this->api_version
        );
    }

    public function onRESTInitEvent(): void
    {
        Logger::debug('onRESTInitEvent()', get_class(), get_called_class());
        foreach ($this->getWPFChildren() as $resource) {

            if (!is_subclass_of($resource, __NAMESPACE__ . '\Resource')) {
                Logger::warning(
                    sprintf('Child class "%s" is not a subclass of Resource', get_class($resource)),
                    get_class(),
                    get_called_class()
                );
                continue;
            }

            Logger::debug(
                sprintf('Registering REST routes for "%s/%s")', $this->getAPIRoot(), $resource->getRESTEndpoint()),
                get_class(),
                get_called_class()
            );

            register_rest_route($this->getAPIRoot(), $resource->getRESTEndpoint(), [
                // READ
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => fn ($rq) => $resource->getAll(),
                    'permission_callback' => fn ($rq) => $resource->canGetAll(),
                ],

                // CREATE
                [
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => fn ($rq) => $resource->create($rq->get_params()),
                    'permission_callback' => fn ($rq) => $resource->canCreate($rq->get_params()),
                    'args'                => $resource->getRESTArgs(),
                ],
            ]);

            register_rest_route($this->getAPIRoot(), $resource->getRESTEndpoint() . '/(?P<id>\d+)', [
                // READ
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => fn ($rq) => $resource->get($rq->get_url_params()['id']),
                    'permission_callback' => fn ($rq) => $resource->canGet($rq->get_url_params()['id']),
                ],

                // EDIT
                [
                    'methods'             => \WP_REST_Server::EDITABLE,
                    'callback'            => fn ($rq) => $resource->edit($rq->get_url_params()['id'], $rq->get_params()),
                    'permission_callback' => fn ($rq) => $resource->canEdit($rq->get_url_params()['id'], $rq->get_params()),
                    'args'                => $resource->getRESTArgs(),
                ],

                // DELETE
                [
                    'methods'             => \WP_REST_Server::DELETABLE,
                    'callback'            => fn ($rq) => $resource->delete($rq->get_url_params()['id']),
                    'permission_callback' => fn ($rq) => $resource->canDelete($rq->get_url_params()['id']),
                ],
            ]);
        }
    }

    #endregion
    #region Private Methods

    /**
     * Load resource classes by name into WPF.
     * @return void
     */
    private function loadResources(): void
    {
        foreach ($this->resources as $resource) {
            // Ensure child class exists
            if (!class_exists($resource, true)) {
                $error = sprintf('Resource class does not exist: "%s"', $resource);
                Logger::error($error, get_class(), get_called_class());
                wp_die(Logger::error($error, get_class(), get_called_class()));
            }

            // Get running instance and add to WPF
            $instance = $resource::getInstance();
            if (is_subclass_of($instance, __NAMESPACE__ . '\Resource')) {
                $this->addWPFChild($instance);
            } else {
                Logger::warning(
                    sprintf('Child class "%s" is not a subclass of Resource', get_class($instance)),
                    get_class(),
                    get_called_class()
                );
            }
        }
    }

    #endregion
}
