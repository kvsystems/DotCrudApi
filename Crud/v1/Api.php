<?php
namespace Dot\Crud\Running;

use Dot\Crud\System\Config;
use Dot\Crud\System\Database\GenericDB;
use Dot\Crud\System\Cache\CacheFactory;
use Dot\Crud\System\Column\ReflectionService;
use Dot\Crud\Running\Controller\Responder;
use Dot\Crud\Running\Middleware\Router\SimpleRouter;
use Dot\Crud\Running\Middleware\CorsMiddleware;
use Dot\Crud\Running\Record\RecordService;
use Dot\Crud\Running\Controller\RecordController;
use Dot\Crud\System\Column\DefinitionService;
use Dot\Crud\Running\Controller\ColumnController;
use Dot\Crud\Running\Controller\CacheController;
use Dot\Crud\System\Request;

class Api {

    const CORS_MIDDLEWARE           = 'cors';
    const FIREWALL_MIDDLEWARE       = 'firewall';
    const BASIC_AUTH_MIDDLEWARE     = 'basicAuth';
    const VALIDATION_MIDDLEWARE     = 'validation';
    const SANITATION_MIDDLEWARE     = 'sanitation';
    const AUTHORIZATION_MIDDLEWARE  = 'authorization';

    const RECORDS_CONTROLLER    = 'records';
    const COLUMNS_CONTROLLER    = 'columns';
    const CACHE_CONTROLLER      = 'cache';
    const OPEN_API_CONTROLLER   = 'openapi';

    private $_router    = null;
    private $_responder = null;
    private $_debug     = null;

    public function __construct(Config $config)   {
        $db = new GenericDB(
            $config->getDriver(),
            $config->getHost(),
            $config->getPort(),
            $config->getDatabase(),
            $config->getUsername(),
            $config->getPassword()
        );

        $cache = CacheFactory::create($config);
        $reflection = new ReflectionService($db, $cache, $config->getCacheTime());
        $responder = new Responder();
        $router = new SimpleRouter($responder, $cache, $config->getCacheTime());

        foreach ($config->getMiddleware() as $middleware => $properties) {
            switch ($middleware) {
                case self::CORS_MIDDLEWARE:
                    new CorsMiddleware($router, $responder, $properties);
                    break;
                case self::FIREWALL_MIDDLEWARE:
                    new FirewallMiddleware($router, $responder, $properties);
                    break;
                case self::BASIC_AUTH_MIDDLEWARE:
                    new BasicAuthMiddleware($router, $responder, $properties);
                    break;
                case self::VALIDATION_MIDDLEWARE:
                    new ValidationMiddleware($router, $responder, $properties);
                    break;
                case self::SANITATION_MIDDLEWARE:
                    new SanitationMiddleware($router, $responder, $properties);
                    break;
                case self::AUTHORIZATION_MIDDLEWARE:
                    new AuthorizationMiddleware($router, $responder, $properties);
                    break;
            }
        }

        foreach ($config->getControllers() as $controller) {
            switch ($controller) {
                case self::RECORDS_CONTROLLER:
                    $records = new RecordService($db, $reflection);
                    new RecordController($router, $responder, $records);
                    break;
                case self::COLUMNS_CONTROLLER:
                    $definition = new DefinitionService($db, $reflection);
                    new ColumnController($router, $responder, $reflection, $definition);
                    break;
                case self::CACHE_CONTROLLER:
                    new CacheController($router, $responder, $cache);
                    break;
            }
        }

        $this->_router = $router;
        $this->_responder = $responder;
        $this->_debug = $config->getDebug();
    }

    public function handle(Request $request = null)    {
        $response = null;
        try {
            $response = $this->_router->route($request);
        } catch (\Throwable $e) {
            if ($e instanceof \PDOException) {
                if (strpos(strtolower($e->getMessage()), 'duplicate') !== false) {
                    return $this->_responder->error(ErrorCode::DUPLICATE_KEY_EXCEPTION, '');
                }
                if (strpos(strtolower($e->getMessage()), 'default value') !== false) {
                    return $this->_responder->error(ErrorCode::DATA_INTEGRITY_VIOLATION, '');
                }
                if (strpos(strtolower($e->getMessage()), 'allow nulls') !== false) {
                    return $this->_responder->error(ErrorCode::DATA_INTEGRITY_VIOLATION, '');
                }
                if (strpos(strtolower($e->getMessage()), 'constraint') !== false) {
                    return $this->_responder->error(ErrorCode::DATA_INTEGRITY_VIOLATION, '');
                }
            }
            $response = $this->_responder->error(ErrorCode::ERROR_NOT_FOUND, $e->getMessage());
            if ($this->_debug) {
                $response->addHeader('X-Debug-Info', 'Exception in ' . $e->getFile() . ' on line ' . $e->getLine());
            }
        }
        return $response;
    }

}