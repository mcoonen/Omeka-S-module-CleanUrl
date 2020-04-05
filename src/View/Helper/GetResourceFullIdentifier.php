<?php

namespace CleanUrl\View\Helper;

/*
 * Get resource full identifier
 *
 * @todo Use CleanRoute (but it's for the full identifier).
 *
 * @see Omeka\View\Helper\CleanUrl.php
 */

use const CleanUrl\SLUG_MAIN_SITE;
use const CleanUrl\SLUG_SITE;
use const CleanUrl\SLUG_SITE_DEFAULT;
use const CleanUrl\SLUGS_SITE;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Zend\Mvc\Application;
use Zend\View\Helper\AbstractHelper;

/**
 * @package Omeka\Plugins\CleanUrl\views\helpers
 */
class GetResourceFullIdentifier extends AbstractHelper
{
    protected $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Get clean url path of a resource in the default or specified format.
     *
     * @todo Replace by standard routing assemble.
     *
     * @param \Omeka\Api\Representation\AbstractResourceRepresentation|array $resource
     * @param string $siteSlug May be required on main public pages.
     * @param string $withBasePath Can be empty, "admin", "public" or "current".
     * If any, implies main path.
     * @param bool $withMainPath
     * @param bool $absoluteUrl If true, implies current / admin or public
     * path and main path.
     * @param string $format Format of the identifier (default one if empty).
     * @return string Full identifier of the resource if any, else empty string.
     */
    public function __invoke(
        $resource,
        $siteSlug = null,
        $withBasePath = 'current',
        $withMainPath = true,
        $absolute = false,
        $format = null
    ) {
        $view = $this->getView();

        if (is_array($resource)) {
            $resourceNames = [
                // Manage api names.
                'item_sets' => 'item_sets',
                'items' => 'items',
                'medias' => 'media',
                // Manage json ld types too.
                'o:ItemSet' => 'item_sets',
                'o:Item' => 'items',
                'o:Media' => 'media',
                // Manage controller names too.
                'item-set' => 'item_sets',
                'item' => 'items',
                'media' => 'media',
            ];
            if (!isset($resource['type'])
                || !isset($resource['id'])
                || !isset($resourceNames[$resource['type']])
            ) {
                return '';
            }

            try {
                $resource = $this->view->api()
                    ->read(
                        $resourceNames[$resource['type']],
                        ['id' => $resource['id']]
                    )
                    ->getContent();
            } catch (\Omeka\Api\Exception\NotFoundException $e) {
                return '';
            }
        }

        switch ($resource->resourceName()) {
            case 'item_sets':
                $identifier = $view->getResourceIdentifier($resource, true, true);
                if (!$identifier) {
                    return $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                }

                $generic = $view->setting('cleanurl_item_set_generic');
                return $this->_getUrlPath($siteSlug, $absolute, $withBasePath, $withMainPath) . $generic . $identifier;

            case 'items':
                if (empty($format)) {
                    $format = $view->setting('cleanurl_item_default');
                }
                // Else check if the format is allowed.
                elseif (!$this->_isFormatAllowed($format, 'items')) {
                    return $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                }

                $skipPrefixItem = !strpos($format, 'item_full');
                $identifier = $view->getResourceIdentifier($resource, true, $skipPrefixItem);
                if (!$identifier) {
                    return $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                }

                switch ($format) {
                    case 'generic_item':
                    case 'generic_item_full':
                        $generic = $view->setting('cleanurl_item_generic');
                        return $this->_getUrlPath($siteSlug, $absolute, $withBasePath, $withMainPath) . $generic . $identifier;

                    case 'item_set_item':
                    case 'item_set_item_full':
                        $itemSets = $resource->itemSets();
                        if (empty($itemSets)) {
                            $format = $this->_getGenericFormat('item');
                            return $format
                                ? $view->getResourceFullIdentifier($resource, $siteSlug, $withBasePath, $withMainPath, $absolute, $format)
                                : $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                        }

                        $itemSet = reset($itemSets);
                        $itemSetIdentifier = $view->getResourceIdentifier($itemSet, true, true);
                        if (!$itemSetIdentifier) {
                            $itemSetUndefined = $view->setting('cleanurl_item_item_set_undefined');
                            if ($itemSetUndefined !== 'parent_id') {
                                return $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                            }
                            $itemSetIdentifier = $itemSet->id();
                        }

                        return $this->_getUrlPath($siteSlug, $absolute, $withBasePath, $withMainPath) . $itemSetIdentifier . '/' . $identifier;

                    default:
                        break;
                }

                // Unmanaged format.
                return $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);

            case 'media':
                if (empty($format)) {
                    $format = $view->setting('cleanurl_media_default');
                }
                // Else check if the format is allowed.
                elseif (!$this->_isFormatAllowed($format, 'media')) {
                    return $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                }

                $skipPrefixMedia = !strpos($format, 'media_full');
                $identifier = $view->getResourceIdentifier($resource, true, $skipPrefixMedia);
                if (!$identifier) {
                    return $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                }

                switch ($format) {
                    case 'generic_media':
                    case 'generic_media_full':
                        $generic = $view->setting('cleanurl_media_generic');
                        return $this->_getUrlPath($siteSlug, $absolute, $withBasePath, $withMainPath) . $generic . $identifier;

                    case 'generic_item_media':
                    case 'generic_item_full_media':
                    case 'generic_item_media_full':
                    case 'generic_item_full_media_full':
                        $item = $resource->item();
                        $skipPrefixItem = !strpos($format, 'item_full');
                        $itemIdentifier = $view->getResourceIdentifier($item, true, $skipPrefixItem);
                        if (empty($itemIdentifier)) {
                            $itemUndefined = $view->setting('cleanurl_media_item_undefined');
                            if ($itemUndefined !== 'parent_id') {
                                return $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                            }
                            $itemIdentifier = $item->id();
                        }

                        $generic = $view->setting('cleanurl_media_generic');
                        return $this->_getUrlPath($siteSlug, $absolute, $withBasePath, $withMainPath) . $generic . $itemIdentifier . '/' . $identifier;

                    case 'item_set_media':
                    case 'item_set_media_full':
                        $item = $resource->item();
                        $itemSets = $item->itemSets();
                        if (empty($itemSets)) {
                            $format = $this->_getGenericFormat('media');
                            return $format
                                ? $view->getResourceFullIdentifier($resource, $siteSlug, $withBasePath, $withMainPath, $absolute, $format)
                                : $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                        }

                        $itemSet = reset($itemSets);
                        $itemSetIdentifier = $view->getResourceIdentifier($itemSet, true, true);
                        if (empty($itemSetIdentifier)) {
                            $itemSetUndefined = $view->setting('cleanurl_media_item_set_undefined');
                            if ($itemSetUndefined !== 'parent_id') {
                                return $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                            }
                            $itemSetIdentifier = $itemSet->id();
                        }
                        return $this->_getUrlPath($siteSlug, $absolute, $withBasePath, $withMainPath) . $itemSetIdentifier . '/' . $identifier;

                    case 'item_set_item_media':
                    case 'item_set_item_full_media':
                    case 'item_set_item_media_full':
                    case 'item_set_item_full_media_full':
                        $item = $resource->item();
                        $itemSets = $item->itemSets();
                        if (empty($itemSets)) {
                            $format = $this->_getGenericFormat('media');
                            return $format
                                ? $view->getResourceFullIdentifier($resource, $siteSlug, $withBasePath, $withMainPath, $absolute, $format)
                                : $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                        }

                        $itemSet = reset($itemSets);
                        $itemSetIdentifier = $view->getResourceIdentifier($itemSet, true, true);
                        if (empty($itemSetIdentifier)) {
                            $itemSetUndefined = $view->setting('cleanurl_media_item_set_undefined');
                            if ($itemSetUndefined !== 'parent_id') {
                                return $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                            }
                            $itemSetIdentifier = $itemSet->id();
                        }

                        $skipPrefixItem = !strpos($format, 'item_full');
                        $itemIdentifier = $view->getResourceIdentifier($item, true, $skipPrefixItem);
                        if (!$itemIdentifier) {
                            $itemUndefined = $view->setting('cleanurl_media_item_undefined');
                            if ($itemUndefined !== 'parent_id') {
                                return $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);
                            }
                            $itemIdentifier = $item->id();
                        }
                        return $this->_getUrlPath($siteSlug, $absolute, $withBasePath, $withMainPath) . $itemSetIdentifier . '/' . $itemIdentifier . '/' . $identifier;

                    default:
                        break;
                }

                // Unmanaged format.
                return $this->urlNoIdentifier($resource, $siteSlug, $absolute, $withBasePath, $withMainPath);

            default:
                break;
        }

        // This resource doesn't have a clean url.
        return '';
    }

    /**
     * Return beginning of the resource name if needed.
     *
     * @param string $siteSlug
     * @param bool $withBasePath Implies main path.
     * @param bool $withMainPath
     * @return string The string ends with '/'.
     */
    protected function _getUrlPath($siteSlug, $absolute, $withBasePath, $withMainPath)
    {
        if ($absolute) {
            $withBasePath = empty($withBasePath) ? 'current' : $withBasePath;
            $withMainPath = true;
        } elseif ($withBasePath) {
            $withMainPath = true;
        }

        if ($withBasePath == 'current') {
            $routeMatch = $this->application->getMvcEvent()->getRouteMatch();
            $withBasePath = $routeMatch->getParam('__ADMIN__') ? 'admin' : 'public';
        }

        switch ($withBasePath) {
            case 'public':
                if (strlen($siteSlug)) {
                    if (SLUG_MAIN_SITE && $siteSlug === SLUG_MAIN_SITE) {
                        $siteSlug = '';
                    }
                } else {
                    if (empty($routeMatch)) {
                        $routeMatch = $this->application->getMvcEvent()->getRouteMatch();
                    }
                    $siteSlug = $routeMatch->getParam('site-slug');
                    if (SLUG_MAIN_SITE && $siteSlug === SLUG_MAIN_SITE) {
                        $siteSlug = '';
                    }
                }
                if (mb_strlen($siteSlug)) {
                    // The check of "slugs_site" may avoid an issue when empty,
                    // after install or during/after upgrade.
                    $basePath = $this->view->basePath(
                        (mb_strlen(SLUGS_SITE) || mb_strlen(SLUG_SITE) ? SLUG_SITE : SLUG_SITE_DEFAULT) . $siteSlug
                    );
                } else {
                    $basePath = $this->view->basePath();
                }
                break;

            case 'admin':
                $basePath = $this->view->basePath('admin');
                break;

            default:
                $basePath = '';
        }

        $mainPath = $withMainPath ? $this->view->setting('cleanurl_main_path_full') : '';

        return ($absolute ? $this->view->serverUrl() : '') . $basePath . '/' . $mainPath;
    }

    /**
     * Check if a format is allowed for a resource type.
     *
     * @param string $format
     * @param string $resourceName
     * @return bool|null True if allowed, false if not, null if no format.
     */
    protected function _isFormatAllowed($format, $resourceName)
    {
        if (empty($format)) {
            return null;
        }

        switch ($resourceName) {
            case 'items':
                $allowedForItems = $this->view->setting('cleanurl_item_allowed');
                return in_array($format, $allowedForItems);

            case 'media':
                $allowedForMedia = $this->view->setting('cleanurl_media_allowed');
                return in_array($format, $allowedForMedia);

            default:
                return null;
        }
    }

    /**
     * Return the generic format, if exists, for items or media.
     *
     * @param string $resourceName
     * @return string|null
     */
    protected function _getGenericFormat($resourceName)
    {
        switch ($resourceName) {
            case 'items':
                $allowedForItems = $this->view->setting('cleanurl_item_allowed');
                if (in_array('generic_item', $allowedForItems)) {
                    return 'generic_item';
                }
                return in_array('generic_item_full', $allowedForItems)
                    ? 'generic_item_full'
                    : null;

            case 'media':
                $allowedForMedia = $this->view->setting('cleanurl_media_allowed');
                if (in_array('generic_item_media', $allowedForMedia)) {
                    return 'generic_item_media';
                }
                if (in_array('generic_item_full_media', $allowedForMedia)) {
                    return 'generic_item_full_media';
                }
                if (in_array('generic_item_media_full', $allowedForMedia)) {
                    return 'generic_item_media_full';
                }
                if (in_array('generic_item_full_media_full', $allowedForMedia)) {
                    return 'generic_item_full_media_full';
                }
                if (in_array('generic_media', $allowedForMedia)) {
                    return 'generic_media';
                }
                return in_array('generic_media_full', $allowedForMedia)
                    ? 'generic_media_full'
                    : null;

            default:
                return null;
        }
    }

    /**
     * Get an identifier when there is no identifier.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @param string $siteSlug
     * @param bool $absolute
     * @param string $withBasePath
     * @param string $withMainPath
     * @throws \Omeka\Mvc\Exception\RuntimeException
     * @return string
     */
    protected function urlNoIdentifier(AbstractResourceEntityRepresentation $resource, $siteSlug, $absolute, $withBasePath, $withMainPath)
    {
        switch ($this->view->setting('cleanurl_identifier_undefined')) {
            case 'main_generic':
                $genericKeys = [
                    'item' => 'cleanurl_item_generic',
                    'item-set' => 'cleanurl_item_set_generic',
                    'media' => 'cleanurl_media_generic',
                ];
                return $this->_getUrlPath($siteSlug, $absolute, $withBasePath, $withMainPath) . $genericKeys[$resource->getControllerName()] . $resource->id();
            case 'generic':
                $genericKeys = [
                    'item' => 'cleanurl_item_generic',
                    'item-set' => 'cleanurl_item_set_generic',
                    'media' => 'cleanurl_media_generic',
                ];
                return $this->_getUrlPath($siteSlug, $absolute, $withBasePath, false) . $genericKeys[$resource->getControllerName()] . $resource->id();
            case 'exception':
                $message = new \Omeka\Stdlib\Message('The "%1$s" #%2$d has no normalized identifier.', $resource->getControllerName(), $resource->id()); // @translate
                throw new \Omeka\Mvc\Exception\RuntimeException($message);
            case 'default':
            default:
                return $this->_getUrlPath($siteSlug, $absolute, $withBasePath, false) . $resource->getControllerName() . '/' . $resource->id();
        }
    }
}
