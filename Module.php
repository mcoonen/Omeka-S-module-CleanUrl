<?php
namespace CleanUrl;

/*
 * Clean Url
 *
 * Allows to have URL like http://example.com/my_item_set/dc:identifier.
 *
 * @copyright Daniel Berthereau, 2012-2020
 * @copyright BibLibre, 2016-2017
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

require_once file_exists(OMEKA_PATH . '/config/clean_url.config.php')
    ? OMEKA_PATH . '/config/clean_url.config.php'
    : __DIR__ . '/config/clean_url.config.php';

use CleanUrl\Form\ConfigForm;
use CleanUrl\Service\ViewHelper\GetResourceTypeIdentifiersFactory;
use Generic\AbstractModule;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\MvcEvent;
use Zend\View\Renderer\PhpRenderer;

class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);
        $this->addAclRules();
        $this->addRoutes();
    }

    protected function postInstall()
    {
        $services = $this->getServiceLocator();
        $t = $services->get('MvcTranslator');

        $messenger = new \Omeka\Mvc\Controller\Plugin\Messenger;
        $messenger->addWarning($t->translate('Some settings may be configured in the file "config/clean_url.config.php" in the root of Omeka.')); // @translate

        $configPath = __DIR__ . '/config/clean_url.config.php';
        $omekaConfigPath = OMEKA_PATH . '/config/clean_url.config.php';
        if (file_exists($configPath) && !file_exists($omekaConfigPath)) {
            $result = @copy($configPath, $omekaConfigPath);
            if (!$result) {
                $messenger->addWarning($t->translate('Unable to copy the special config file "config/clean_url.config.php" in Omeka config directory.')); // @translate
            }
        }

        $this->cacheItemSetsRegex();
    }

    public function getConfigForm(PhpRenderer $renderer)
    {
        $services = $this->getServiceLocator();

        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');

        // TODO Clean filling of the config form.
        $data = [];
        $defaultSettings = $config[strtolower(__NAMESPACE__)]['config'];
        foreach ($defaultSettings as $name => $value) {
            $data['clean_url_identifiers'][$name] = $settings->get($name, $value);
            $data['clean_url_main_path'][$name] = $settings->get($name, $value);
            $data['clean_url_item_sets'][$name] = $settings->get($name, $value);
            $data['clean_url_items'][$name] = $settings->get($name, $value);
            $data['clean_url_medias'][$name] = $settings->get($name, $value);
            $data['clean_url_pages'][$name] = $settings->get($name, $value);
            $data['clean_url_admin'][$name] = $settings->get($name, $value);
        }

        $form = $services->get('FormElementManager')->get(ConfigForm::class);
        $form->init();
        $form->setData($data);

        $view = $renderer;
        $translate = $view->plugin('translate');
        $view->headStyle()->appendStyle('.inputs label { display: block; }');
        $form->prepare();

        $html = $translate('"CleanUrl" module allows to have clean, readable and search engine optimized Urls like http://example.com/my_item_set/item_identifier.') // @translate
            . '<br />'
            . sprintf($translate('See %s for more information.'), // @translate
                sprintf('<a href="https://github.com/Daniel-KM/Omeka-S-module-CleanUrl">%s</a>', 'Readme'))
            . '<br />'
            . sprintf($translate('%sNote%s: identifiers should never contain reserved characters such "/" or "%%".'), '<strong>', '</strong>') // @translate
            . '<br />'
            . sprintf($translate('%sNote%s: For a good seo, it‘s not recommended to have multiple urls for the same resource.'), '<strong>', '</strong>') // @translate
            . '<br />'
            . $translate('To keep the original routes, the main site slug must be set in the file "clean_url.config.php".') // @translate
            . $view->formCollection($form);
        return $html;
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $config = $services->get('Config');
        $settings = $services->get('Omeka\Settings');

        $params = $controller->getRequest()->getPost();

        $form = $services->get('FormElementManager')->get(ConfigForm::class);
        $form->init();
        $form->setData($params);
        if (!$form->isValid()) {
            $controller->messenger()->addErrors($form->getMessages());
            return false;
        }

        // TODO Normalize the filling of the config form.
        $params = array_merge(
            $params['clean_url_identifiers'],
            $params['clean_url_main_path'],
            $params['clean_url_item_sets'],
            $params['clean_url_items'],
            $params['clean_url_medias'],
            $params['clean_url_pages'],
            $params['clean_url_admin']
        );

        // TODO Move the post-checks into the form.

        // Sanitize first.
        $params['cleanurl_identifier_prefix'] = trim($params['cleanurl_identifier_prefix']);
        foreach ([
            'cleanurl_main_path',
            'cleanurl_item_set_generic',
            'cleanurl_item_generic',
            'cleanurl_media_generic',
        ] as $posted) {
            $value = trim(trim($params[$posted]), ' /');
            $params[$posted] = empty($value) ? '' : trim($value) . '/';
        }

        $params['cleanurl_identifier_property'] = (int) $params['cleanurl_identifier_property'];

        // The default url should be allowed for items and media.
        $params['cleanurl_item_allowed'][] = $params['cleanurl_item_default'];
        $params['cleanurl_item_allowed'] = array_values(array_unique($params['cleanurl_item_allowed']));
        $params['cleanurl_media_allowed'][] = $params['cleanurl_media_default'];
        $params['cleanurl_media_allowed'] = array_values(array_unique($params['cleanurl_media_allowed']));

        $params['cleanurl_page_slug'] = SLUG_PAGE;

        $defaultSettings = $config['cleanurl']['config'];
        $params = array_intersect_key($params, $defaultSettings);
        foreach ($params as $name => $value) {
            $settings->set($name, $value);
        }

        $this->cacheItemSetsRegex();
        return true;
    }

    /**
     * Add ACL rules for this module.
     */
    protected function addAclRules()
    {
        // Allow all access to the controller, because there will be a forward.
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $roles = $acl->getRoles();
        $acl
            ->allow(null, [Controller\Site\CleanUrlController::class])
            ->allow($roles, [Controller\Admin\CleanUrlController::class])
        ;
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $serviceLocator = $this->getServiceLocator();
        $settings = $serviceLocator->get('Omeka\Settings');
        if ($settings->get('cleanurl_display_admin_show_identifier')) {
            $sharedEventManager->attach(
                'Omeka\Controller\Admin\ItemSet',
                'view.show.sidebar',
                [$this, 'displayViewResourceIdentifier']
            );
            $sharedEventManager->attach(
                'Omeka\Controller\Admin\Item',
                'view.show.sidebar',
                [$this, 'displayViewResourceIdentifier']
            );
            $sharedEventManager->attach(
                'Omeka\Controller\Admin\Media',
                'view.show.sidebar',
                [$this, 'displayViewResourceIdentifier']
            );
        }
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.details',
            [$this, 'displayViewEntityIdentifier']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\ItemSet',
            'view.details',
            [$this, 'displayViewEntityIdentifier']
        );
        $sharedEventManager->attach(
            \Omeka\Api\Adapter\ItemSetAdapter::class,
            'api.create.post',
            [$this, 'afterSaveItemSet']
        );
        $sharedEventManager->attach(
            \Omeka\Api\Adapter\ItemSetAdapter::class,
            'api.update.post',
            [$this, 'afterSaveItemSet']
        );
        $sharedEventManager->attach(
            \Omeka\Api\Adapter\ItemSetAdapter::class,
            'api.delete.post',
            [$this, 'afterSaveItemSet']
        );
    }

    /**
     * Display an identifier.
     */
    public function displayViewResourceIdentifier(Event $event)
    {
        $resource = $event->getTarget()->resource;
        $this->displayResourceIdentifier($resource);
    }

    /**
     * Display an identifier.
     */
    public function displayViewEntityIdentifier(Event $event)
    {
        $resource = $event->getParam('entity');
        $this->displayResourceIdentifier($resource);
    }

    /**
     * Helper to display an identifier.
     *
     * @param \Omeka\Api\Representation\AbstractResourceRepresentation|Resource $resource
     */
    protected function displayResourceIdentifier($resource)
    {
        $services = $this->getServiceLocator();
        $translator = $services->get('MvcTranslator');
        $getResourceIdentifier = $services->get('ViewHelperManager')
            ->get('getResourceIdentifier');
        $identifier = $getResourceIdentifier($resource, false);

        echo '<div class="meta-group"><h4>'
            . $translator->translate('CleanUrl identifier') // @translate
            . '</h4><div class="value">'
            . ($identifier ?: '<em>' . $translator->translate('[none]') . '</em>')
            . '</div></div>';
    }

    /**
     * Defines public routes "main_path / my_item_set | generic / dcterms:identifier".
     *
     * @todo Rechecks performance of routes definition.
     */
    protected function addRoutes()
    {
        $services = $this->getServiceLocator();
        $router = $services->get('Router');
        if (!$router instanceof \Zend\Router\Http\TreeRouteStack) {
            return;
        }

        $settings = $services->get('Omeka\Settings');
        $basePath = $services->get('ViewHelperManager')->get('basePath');

        $router
            ->addRoute('clean-url', [
                'type' => \CleanUrl\Router\Http\CleanRoute::class,
                // Check clean url first.
                'priority' => 10,
                'options' => [
                    // TODO Save all these settings in one array.
                    'base_path' => $basePath(),
                    'settings' => [
                        'default_site' => $settings->get('default_site'),
                        'main_path' => $settings->get('cleanurl_main_path'),
                        'item_set_generic' => $settings->get('cleanurl_item_set_generic'),
                        'item_generic' => $settings->get('cleanurl_item_generic'),
                        'media_generic' => $settings->get('cleanurl_media_generic'),
                        'item_allowed' => $settings->get('cleanurl_item_allowed'),
                        'media_allowed' => $settings->get('cleanurl_media_allowed'),
                        'use_admin' => $settings->get('cleanurl_use_admin'),
                        'item_set_regex' => $settings->get('cleanurl_item_set_regex'),
                    ],
                    'defaults' => [
                        'controller' => 'CleanUrlController',
                        'action' => 'index',
                    ],
                ],
            ]);
    }

    /**
     * Process after saving or deleting an item set.
     *
     * @param Event $event
     */
    public function afterSaveItemSet(Event $event)
    {
        $this->cacheItemSetsRegex($this->getServiceLocator());
    }

    /**
     * Cache item set identifiers as string to speed up routing.
     */
    protected function cacheItemSetsRegex()
    {
        $services = $this->getServiceLocator();
        // Get all item set identifiers with one query.
        $viewHelpers = $services->get('ViewHelperManager');
        // The view helper is not available during intall, upgrade and tests.
        if ($viewHelpers->has('getResourceTypeIdentifiers')) {
            $getResourceTypeIdentifiers = $viewHelpers->get('getResourceTypeIdentifiers');
            $itemSetIdentifiers = $getResourceTypeIdentifiers('item_sets', false);
        } else {
            $getResourceTypeIdentifiers = $this->getViewHelperRTI($services);
            $itemSetIdentifiers = $getResourceTypeIdentifiers->__invoke('item_sets', false);
        }

        // To avoid issues with identifiers that contain another identifier,
        // for example "item_set_bis" contains "item_set", they are ordered
        // by reversed length.
        array_multisort(
            array_map('strlen', $itemSetIdentifiers),
            $itemSetIdentifiers
        );
        $itemSetIdentifiers = array_reverse($itemSetIdentifiers);

        $itemSetsRegex = array_map('preg_quote', $itemSetIdentifiers);
        // To avoid a bug with identifiers that contain a "/", that is not
        // escaped with preg_quote().
        $itemSetsRegex = str_replace('/', '\/', implode('|', $itemSetsRegex));

        $settings = $services->get('Omeka\Settings');
        $settings->set('cleanurl_item_set_regex', $itemSetsRegex);
    }

    /**
     * Get the view helper getResourceTypeIdentifiers with some params.
     *
     * @return \CleanUrl\View\Helper\GetResourceTypeIdentifiers
     */
    protected function getViewHelperRTI()
    {
        $services = $this->getServiceLocator();

        require_once __DIR__ . '/src/Service/ViewHelper/GetResourceTypeIdentifiersFactory.php';
        require_once __DIR__ . '/src/View/Helper/GetResourceTypeIdentifiers.php';

        $settings = $services->get('Omeka\Settings');
        $propertyId = (int) $settings->get('cleanurl_identifier_property');
        $prefix = $settings->get('cleanurl_identifier_prefix');

        $factory = new GetResourceTypeIdentifiersFactory();
        return $factory(
            $services,
            'getResourceTypeIdentifiers',
            [
                'propertyId' => $propertyId,
                'prefix' => $prefix,
            ]
        );
    }
}
