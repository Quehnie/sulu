<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\Media\SmartContent;

use JMS\Serializer\SerializerInterface;
use Sulu\Bundle\MediaBundle\Collection\Manager\CollectionManagerInterface;
use Sulu\Bundle\WebsiteBundle\ReferenceStore\ReferenceStoreInterface;
use Sulu\Component\Content\Compat\PropertyParameter;
use Sulu\Component\SmartContent\DatasourceItem;
use Sulu\Component\SmartContent\Orm\BaseDataProvider;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Media DataProvider for SmartContent.
 */
class MediaDataProvider extends BaseDataProvider
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var CollectionManagerInterface
     */
    private $collectionManager;

    public function __construct(
        DataProviderRepositoryInterface $repository,
        CollectionManagerInterface $collectionManager,
        SerializerInterface $serializer,
        RequestStack $requestStack,
        ReferenceStoreInterface $referenceStore
    ) {
        parent::__construct($repository, $serializer, $referenceStore);

        $this->configuration = self::createConfigurationBuilder()
            ->enableTags()
            ->enableCategories()
            ->enableLimit()
            ->enablePagination()
            ->enablePresentAs()
            ->enableAudienceTargeting()
            ->enableDatasource('collections', 'collections', 'column_list')
            ->enableSorting(
                [
                    ['column' => 'fileVersionMeta.title', 'title' => 'sulu_admin.title'],
                ]
            )
            ->getConfiguration();

        $this->requestStack = $requestStack;
        $this->collectionManager = $collectionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultPropertyParameter()
    {
        return [
            'mimetype_parameter' => new PropertyParameter('mimetype_parameter', 'mimetype', 'string'),
            'type_parameter' => new PropertyParameter('type_parameter', 'type', 'string'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function resolveDatasource($datasource, array $propertyParameter, array $options)
    {
        if (empty($datasource)) {
            return;
        }

        if ('root' === $datasource) {
            $title = 'smart-content.media.all-collections';

            return new DatasourceItem('root', $title, $title);
        }

        $entity = $this->collectionManager->getById($datasource, $options['locale']);

        return new DatasourceItem($entity->getId(), $entity->getTitle(), $entity->getTitle());
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions(
        array $propertyParameter,
        array $options = []
    ) {
        $request = $this->requestStack->getCurrentRequest();

        $queryOptions = [];

        if (array_key_exists('mimetype_parameter', $propertyParameter)) {
            $queryOptions['mimetype'] = $request->get($propertyParameter['mimetype_parameter']->getValue());
        }
        if (array_key_exists('type_parameter', $propertyParameter)) {
            $queryOptions['type'] = $request->get($propertyParameter['type_parameter']->getValue());
        }

        return array_merge($options, array_filter($queryOptions));
    }

    /**
     * {@inheritdoc}
     */
    protected function decorateDataItems(array $data)
    {
        return array_map(
            function($item) {
                return new MediaDataItem($item);
            },
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getSerializationContext()
    {
        $serializationContext = parent::getSerializationContext();

        $serializationContext->setGroups(['Default']);

        return $serializationContext;
    }
}
